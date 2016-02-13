<?php
    require_once '../conf.php';
    
    class LanguagesView extends View 
    {
        public function __construct ()
        {
            $this->actionList = array(
                //self::FORM_RM_SOFFI_INFO      => User::PRIV_EDYCJA_REKORDU,
                
                
            );
            
            parent::__construct();
            $this->utilsUI = new UtilsUI('', 'id_os');
            $this->id_osoba = Utils::PodajIdOsoba();
            $this->person = new Person($this->id_osoba);
            $this->partials = new Partials($this->person);
            //$this->dal = new DALZnaneJezyki();
            $this->addInfo = new AdditionalBool($this->id_osoba);
            $this->bllDicts = new BLLDaneSlownikowe();
            //$this->htmlControls = new HtmlControls();
        }
    }
    
    //CommonUtils::outputBufferingOn();
    //CommonUtils::SessionStart();

    //echo '<html>';
    //HelpersUI::addHtmlBasicHeaders(HelpersUI::PATH_LEVEL_1);
    //echo '<body>';
    
    $languagesHtml = '';
    
    //wykonac migracje z tabeli osoby_bez_jezykow z informacja, ze osoba nie ma jezyka, tabele wywalic, wadliwa i niuzywana logika.
    //sprawdzic na produkcji, sql zaba !!!!
    if (empty($_SESSION['uzytkownik']))
    {
        require '../log_in.php';
    }
    else
    {
        require_once '../bll/additionals.php';
        $controls = new valControl();
        
        $id_osoba = Utils::PodajIdOsoba();
        $addInfo = new AdditionalBool($id_osoba);

        $query = "select imie, nazwisko from dane_osobowe WHERE dane_osobowe.id = ".$id_osoba.";";
		$wynik = $controls->dalObj->PobierzDane($query, $ilosc_r_osoba);
        
		if ($ilosc_r_osoba < 1)
		{
			$languagesHtml .= "Osoba w chwili obecnej nie znajduje siê ju¿ w bazie, musia³a dopiero co zostaæ usuniêta !?";
		}
		else
		{
            if (isset($_POST['rm_dl_info']))
            {
                $addInfo->setForeignLanguageInformation(false);
                return View::postSuccessfull($_SERVER['REQUEST_URI']);
            }
            if (isset($_POST['add_dl_info']))
            {
                $addInfo->setForeignLanguageInformation(true);
                return View::postSuccessfull($_SERVER['REQUEST_URI']);
            }
            if (isset($_POST['potwierdz_zj']))
            {
                $controls->dalObj->pgQuery("INSERT INTO zatwierdzone_jezyki VALUES (nextval('zatwierdzone_jezyki_id_seq'), {$_POST['znany_jezyk_id']}, (SELECT id FROM uprawnienia WHERE nazwa_uzytkownika = '".$_SESSION['uzytkownik']."'), '".date("Y-m-d H:i:s")."');");
                return View::postSuccessfull($_SERVER['REQUEST_URI']);
            }
	        if (isset($_POST['dodaj_jezyk']))
	        {
		        if (($_POST['jezyk_dod'] != "--------") && ($_POST['poziom_dod'] != "--------"))
		        {
			        $controls->dalObj->pgQuery("insert into znane_jezyki values ('".$id_osoba."', ".$_POST['jezyk_dod_id'].", ".$_POST['poziom_dod_id'].");");
		        }
		        
		        return View::postSuccessfull($_SERVER['REQUEST_URI']);
	        }
	        
	        if (isset($_POST['aktualizuj_jezyki']))
	        {
		        $znane_jezyki = $controls->dalObj->PobierzDane("select znane_jezyki.id_znany_jezyk, jezyki.nazwa as jezyk, jezyki.id as id_jezyk, poziomy.id as id_poziom, poziomy.nazwa as poziom from znane_jezyki join jezyki on znane_jezyki.id_jezyk = jezyki.id join poziomy on znane_jezyki.id_poziom = poziomy.id where znane_jezyki.id = '".$id_osoba."' order by jezyki.nazwa asc;");

		        foreach ($znane_jezyki as $znany_jezyk)
		        {
			        if ($_POST[$znany_jezyk['jezyk']] == "--------")
			        {
                        $zapytanko = "DELETE FROM zatwierdzone_jezyki WHERE id_znany_jezyk = ".$znany_jezyk['id_znany_jezyk'].";";
				        $zapytanko .= "delete from znane_jezyki where id = '".$id_osoba."' and id_jezyk = ".$znany_jezyk['id_jezyk'].";";
				        $kwerenda = $controls->dalObj->pgQuery($zapytanko);
			        }
			        else if ($_POST[$znany_jezyk['jezyk']] != $znany_jezyk['poziom'])
			        {
				        $zapytanko = "update znane_jezyki set id_poziom = ".$_POST[$znany_jezyk['jezyk'].'_id']." where id = ".$id_osoba." and id_jezyk = ".$znany_jezyk['id_jezyk'].";";
                        $zapytanko .= "DELETE FROM zatwierdzone_jezyki WHERE id_znany_jezyk = ".$znany_jezyk['id_znany_jezyk'].";";
				        $kwerenda = $controls->dalObj->pgQuery($zapytanko);
			        }
		        }
		        
		        return View::postSuccessfull($_SERVER['REQUEST_URI']);
	        }
            
            $zapytanie = "select znane_jezyki.id_znany_jezyk as id, znane_jezyki.id_jezyk, jezyki.nazwa as jezyk, poziomy.id as id_poziom, poziomy.nazwa as poziom 
            from znane_jezyki 
            join jezyki on znane_jezyki.id_jezyk = jezyki.id 
            join poziomy on znane_jezyki.id_poziom = poziomy.id 
            where znane_jezyki.id = '".$id_osoba."' order by jezyki.nazwa asc;";
            $znane_jezyki = $controls->dalObj->PobierzDane($zapytanie, $ilosc_zn_jez);
            $hasFL = $addInfo->getForeignLanguageInformation();
            
            if ($ilosc_zn_jez > 0 || $hasFL)
            {
                $languagesHtml .= 'Jezyki: (zna jêzyki obce)<br />';
            }
            else
            {
                $languagesHtml .= 'Jezyki: (nie zna jêzyków obcych - '.($hasFL === null ? 'Nie podano' : 'Podano').')<br />';
            }            
            
            $languagesHtml .= ("<form method=\"POST\" action='".$_SERVER['REQUEST_URI']."' name = 'jezyki_form'><input type = 'hidden' name = 'znany_jezyk_id' /><table>");
            $languagesHtml .= $controls->AddHidden(ID_OSOBA, ID_OSOBA, $id_osoba);
            $languagesHtml .= $controls->AddSelectHelpHidden();
            
            $znane = array();
            $poziomy = $controls->dalObj->PobierzDane('select id, nazwa from poziomy;');
            
            if ($ilosc_zn_jez > 0)
            {
                array_unshift($poziomy, array ('id' => 'null', 'nazwa' => '--------'));
                foreach ($znane_jezyki as $znany_jezyk)
                {
                    $znane[] = $znany_jezyk['id_jezyk'];
                    $languagesHtml .= '<tr><td>'.$znany_jezyk['jezyk'].':</td><td>';
                    $languagesHtml .= $controls->_AddSelectData($znany_jezyk['jezyk'], $znany_jezyk['id'], '', $poziomy, $znany_jezyk['id_poziom'], $znany_jezyk['jezyk'].'_id');
                    $languagesHtml .= '</td><td nowrap>';
                    $zapytanie_potwierdzony_jezyk = $controls->dalObj->PobierzDane("SELECT zatwierdzone_jezyki.data, uprawnienia.imie_nazwisko as uzytkownik FROM zatwierdzone_jezyki JOIN uprawnienia ON uprawnienia.id = zatwierdzone_jezyki.id_konsultant WHERE id_znany_jezyk = '".$znany_jezyk['id']."'", $ilosc_r_pot_jezyk);
                    
                    if ($ilosc_r_pot_jezyk > 0)
                    {
                        $row_potwierdzony_jezyk = $zapytanie_potwierdzony_jezyk[0];
                        $languagesHtml .= ("Potwierdzony <input type = 'checkbox' name = 'potwierdz' disabled checked /></td><td nowrap>".$row_potwierdzony_jezyk['data']."</td><td nowrap>".$row_potwierdzony_jezyk['uzytkownik']."");
                    }
                    else
                    {
                        $languagesHtml .= ("Niepowierdzony <input type = 'checkbox' name = 'potwierdz_zj' value = '".$znany_jezyk['id']."' onClick = 'znany_jezyk_id.value = this.value; jezyki_form.submit();' />");
                    }
                    $languagesHtml .= '</td></tr>';
                }
                array_shift($poziomy);
                
                if (!$hasFL)
                {
                    $addInfo->setForeignLanguageInformation(true);
                    $hasFL = true;
                }
            }
            else 
            {
                if ($hasFL)
                {
                    //enable ze nie ma
                    $languagesHtml .= '<tr><td>'.$controls->AddSubmit('rm_dl_info', 'id_rm_dl_info', 'Nie zna jêzyków', '').'</td></tr>';
                }
                else
                {
                    //enable ze ma
                    $languagesHtml .= '<tr><td>'.$controls->AddSubmit('add_dl_info', 'id_add_dl_info', 'Zna jêzyk obcy', '').'</td></tr>';
                }
            }
            
            $where_clause_jezyki = '';
            if (sizeof($znane))
            {
                $temp = implode(',', $znane);
                $where_clause_jezyki = 'where id not in ('.$temp.')';
            }
            
		    $wybor = $controls->dalObj->PobierzDane('select id, nazwa from jezyki '.$where_clause_jezyki.' order by nazwa asc;', $iloscJezykow);
            
            if ($iloscJezykow > 0) {
                
                $languagesHtml .= '<tr><td>';
                $languagesHtml .= $controls->_AddSelectData('jezyk_dod', 'id_jezyk_dod', '', $wybor, null, 'jezyk_dod_id');

		        $languagesHtml .= '</td><td>';
                $languagesHtml .= $controls->_AddSelectData('poziom_dod', 'id_poziom_dod', '', $poziomy, null, 'poziom_dod_id');

		        $languagesHtml .= '</td><td>';
                $languagesHtml .= $controls->AddSubmit('dodaj_jezyk', 'dodaj_jezyk', 'Dodaj.', '', '');
		        $languagesHtml .= '</td></tr>';
            }
            
            $languagesHtml .= '</table><table><tr><td>';
            $languagesHtml .= $controls->AddSubmit('aktualizuj_jezyki', 'aktualizuj_jezyki', 'Aktualizuj.', '', '');
            $languagesHtml .= '</td></tr><tr><td>';
            //$languagesHtml .= $controls->AddSubmit('', '', 'Zamknij', 'onclick=window.close();', '');
            $languagesHtml .= '</td></tr></form></table>';
        }
    }
    
    //CommonUtils::sendOutputBuffer();