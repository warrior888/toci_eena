<?php
    require_once 'dal/DALZatrudnienie.php';
    require_once 'adl/User.php';

    if (empty($_SESSION['uzytkownik']))
    {
        die();
    }
    else
    {
        $dalZatrudnienie = new DALZatrudnienie();
        $user = User::getInstance();
        
        if (isset($_POST[$delete]))
        {
            $query = "delete from zatrudnienie_odjazd where id_zatrudnienie = ".$_POST[ID_ZATRUDNIENIE].";
            delete from ".$db->tableName." where ".$db->tableName.".".$db->tableId." = ".$_POST[ID_ZATRUDNIENIE].";";
            $result = $controls->dalObj->pgQuery($query);
            DeleteTriggerLogic($id_osoba, $controls);
            echo '<script>window.close();</script>';
        }
        if (isset($_POST[$update]))  //dodac logike js ktora przyjmuje date wyjazdu w parametrze js, i wymusza ustalenie daty powrotu dalszej niz data wyjazdu
        {
            $chosenStatus = (int)$_POST['status_www_id'];
            $idDeparturePlan = isset($_POST['rozklad_jazdy_www_id']) ? (int)$_POST['rozklad_jazdy_www_id'] : null;
            $idDepartureReturnPlan = isset($_POST['rozklad_jazdy_powrot_www_id']) ? (int)$_POST['rozklad_jazdy_powrot_www_id'] : null;

            if ($chosenStatus == ID_STATUS_WYJEZDZAJACY && !$idDeparturePlan) {
                
                echo '<label class="error">B³±d. Ustalono status wyje¿d¿aj±cy bez podania odjazdu. </label><br />';
            } else if ($_POST['wakat_www_id'] > 0 && $chosenStatus > 0 && $_POST['ilosc_tyg_www'] > 0 && strlen($_POST['data_powrotu_www']) == 10 && $_POST['decyzja_www_id'] > 0) {
                
                $supportQuery = "select id_klient, id_oddzial, data_wyjazdu from ".$wakat->tableName." where ".$wakat->tableId." = ".(int)$_POST['wakat_www_id'].";";
                $result = $controls->dalObj->pgQuery($supportQuery);
                $row = pg_fetch_array($result);
                
                $klientId = $row['id_klient'];
                $oddzialId = $row['id_oddzial'];
                $dataWyjazd = $row['data_wyjazdu'];
                
                $data = array(
                    Model::COLUMN_ZTR_ID               => (int)$_POST[ID_ZATRUDNIENIE],
                    Model::COLUMN_ZTR_ID_OSOBA         => (int)$_POST[ID_OSOBA],
                    Model::COLUMN_ZTR_ID_KLIENT        => (int)$klientId,
                    Model::COLUMN_ZTR_ID_ODDZIAL       => (int)$oddzialId,
                    Model::COLUMN_ZTR_ID_WAKAT         => (int)$_POST['wakat_www_id'],
                    Model::COLUMN_ZTR_ID_STATUS        => $chosenStatus,
                    Model::COLUMN_ZTR_DATA_WYJAZDU     => $dataWyjazd,
                    Model::COLUMN_ZTR_ILOSC_TYG        => (int)$_POST['ilosc_tyg_www'],
                    Model::COLUMN_ZTR_DATA_POWROTU     => $_POST['data_powrotu_www'],
                    Model::COLUMN_ZTR_ID_DECYZJA       => (int)$_POST['decyzja_www_id'],
                    Model::COLUMN_ZTR_ID_MSC_ODJAZD    => isset($_POST['msc_odjazd_www_id']) ? (int)$_POST['msc_odjazd_www_id'] : null,
                    Model::COLUMN_ZTR_ID_MSC_POWROT    => isset($_POST['msc_powrot_www_id']) ? (int)$_POST['msc_powrot_www_id'] : null,
                    Model::COLUMN_ZTR_ID_BILET         => isset($_POST['bilet_www_id']) ? (int)$_POST['bilet_www_id'] : null,
                    Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_WYJAZD => $idDeparturePlan,
                    Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_POWROT => $idDepartureReturnPlan,
                    'id_miejsca_docelowe'              => (int)$_POST['destination_wysw_wyb_wakat'],
                    'id_osoby_kontaktowe'              => (int)$_POST['contactPerson_wysw_wyb_wakat'],
                    'id_forma_platnosci'               => (int)$_POST['id_forma_platnosci_www_id'],
                    'id_ticket_state'                  => (int)$_POST['id_stan_realizacji_www_id'],
                );

                $dalZatrudnienie->set($data);
                
                $query = "update ".$daneOs->tableName." SET  data='".$row['data_wyjazdu']."', ilosc_tyg='".(int)$_POST['ilosc_tyg_www']."' 
                where ".$daneOs->tableId." = ".$id_osoba.";";
                
                if (ColisionDetection($id_osoba, $_POST[ID_ZATRUDNIENIE], $row['data_wyjazdu'], $_POST['data_powrotu_www'], $db, $controls) == enum::$ALLOWDATA)
                {            
                    if ($row['data_wyjazdu'] < $_POST['data_powrotu_www'])
                    {
                        TriggerLogic($query, $dzis, $row['data_wyjazdu'], $_POST['data_powrotu_www'], $_POST['status_www_id'], $id_osoba, isset($_POST['msc_odjazd_www_id']) ? $_POST['msc_odjazd_www_id'] : null, $controls, $db, $wyodrMsc);
                        $result = $controls->dalObj->pgQuery($query);
                        
                        echo 'Umówiono osobê.<script>PassValsToOpener("datawyjazd,tygodnie", "'.$row['data_wyjazdu'].','.$_POST['ilosc_tyg_www'].'"); 
                        window.close();</script>'; 
                         
                        //die();
                    }
                    else
                    {
                        echo 'B³±d spójno¶ci danych, nie uaktualniono (data wyjazdu nie mo¿e byæ wiêksza od daty powrotu).';
                    }
                }
                else
                {
                    echo 'Zatrudnienie zachodzi terminami z innym zatrudnieniem.';
                }
            }
            else
            {
                echo '<label class="error">Formularz niekompletny. ';
                
                if (!$_POST['ilosc_tyg_www'] > 0)
                    echo 'Podaj Ilo¶æ tygodni. ';
                    
                if (!strlen($_POST['data_powrotu_www']) == 10)
                    echo 'Podaj datê powrotu.';
                    
                echo '</label>';
            }
        }
	    $zapytanie = "select imie, nazwisko from dane_osobowe where dane_osobowe.id = '".$wiersz['id_osoba']."';";
        $wynik1 = $controls->dalObj->pgQuery($zapytanie);
	    $wiersz1 = pg_fetch_array($wynik1);
	    
	    $result = $dalZatrudnienie->get($wiersz['id']);
	    $row = $result[Model::RESULT_FIELD_DATA][0];
        
	    echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'" id="wysw_wyb_wakat"><table>';
        echo $controls->AddHidden(ID_OSOBA, ID_OSOBA, $id_osoba); 
        echo $controls->AddHidden(ID_ZATRUDNIENIE, ID_ZATRUDNIENIE, $row['id']); 
        echo $controls->AddSelectHelpHidden();
	    //<input type='hidden' name='data_ukryta_www' value='".$wiersz['data_wyjazdu']."'>
	    //<input type='hidden' name='id_klient_ukryty_www' value='".$wiersz['id_klient']."'>
	    echo '<tr><td>Nazwisko klienta:</td><td>'.$wiersz1['nazwisko'].'</td></tr>';
	    echo '<tr><td>Imiê klienta:</td><td>'.$wiersz1['imie'].'</td></tr>';
        //zapytanie wyciaga aktualne dane odnosnie konkretnego umowienia z zatrudnienia
        
	    echo "<tr><td>Konsultant:</td><td>".$row['imie_nazwisko']."</td></tr>";
	    echo "<tr><td>Data zapisu:</td><td>".$row['data_wpisu']."</td></tr>";
        //wszystkie wakaty
        $query = "select ".$wakat->tableName.".".$wakat->tableId.", ".$wakat->tableName.".id_klient, id_oddzial, data_wyjazdu, 
        data_wyjazdu || ', ' || ".$klient->tableName.".nazwa_alt || ', ' || ".$oddzial->tableName.".nazwa as ".$wakat_const_name."         
        from ".$wakat->tableName."
        join ".$oddzial->tableName." on ".$wakat->tableName.".id_oddzial = ".$oddzial->tableName.".".$oddzial->tableId."
        join ".$klient->tableName." on ".$wakat->tableName.".id_klient = ".$klient->tableName.".".$klient->tableId."
        where data_wyjazdu >= '".$dzis."' order by data_wyjazdu asc;";
        
        $addsList = array(
            'data_wyjazd_wakat' => 'data_wyjazdu',
            'oddzial_id_www' => 'id_oddzial',
        );
        
        $vacatsList = $controls->dalObj->PobierzDane($query); 
        
	    echo '<tr><td>Wakat:</td><td>';
        echo $htmlControls->_AddSelect("id_wakat_www", "id_wakat_www", $vacatsList, $row['id_wakat'], "wakat_www_id", true, '', '', '', $addsList, 
            "employmentHistory.setWakatDropdowns('wysw_wyb_wakat'); "
                . "utils.resetSelect('id_przewoznik_www', 'przewoznik_id_www'); "
                . "utils.getElementById('msc_odjazd_www_container').innerHTML = '';");
        
        //echo $controls->AddSelectRandomQuerySVbyId("id_wakat_www", "id_wakat_www", "", $query, $row['id_wakat'], "wakat_www_id", $wakat_const_name, $wakat->tableId, 
        //    "utils.setHiddenFromSelectLabel(this, 'data_wyjazd_wakat');", "data_wyjazdu", 'data_wyjazd_wakat', 
        //    'utils.resetSelect("id_przewoznik_www", "przewoznik_id_www"); utils.getElementById("msc_odjazd_www_container").innerHTML = "";');
        echo '</td></tr><tr><td>Status:</td><td>';
        $query = 'select '.$status->tableId.', nazwa from '.$status->tableName.' where id in ('.ID_STATUS_NOWY.', '.ID_STATUS_AKTYWNY.', '.ID_STATUS_WYJEZDZAJACY.') order by nazwa asc;';
        echo $controls->AddSelectRandomQuerySVbyId("id_status_www", "id_status_www", "", $query, $row['id_status'], "status_www_id", "nazwa", $status->tableId, "");
	    echo '</td></tr><tr><td>Ilo¶æ tygodni:</td><td>';
        echo $controls->AddNumberbox("ilosc_tyg_www", "ilosc_tyg_www", $row['ilosc_tyg'],  2, 3, "sprawdz_tygodnie(this)");
        echo '</td></tr><tr><td>Data powrotu:</td><td>';
        echo $controls->AddDateboxFuture("data_powrotu_www", "data_powrotu_www", $row['data_powrotu'], 10, 10);
        echo strftime('%A', strtotime($row['data_powrotu']));
        echo '<tr><td>Decyzja:</td><td>';
        $query = "select ".$decyzja->tableId.", nazwa from ".$decyzja->tableName." order by nazwa asc;";
        echo $controls->AddSelectRandomQuerySVbyId("id_decyzja_www", "id_decyzja_www", "", $query, $row['id_decyzja'], "decyzja_www_id", "nazwa", $decyzja->tableId, "");
        
        echo '</td></tr><tr><td>Przewo¼nik:</td><td>';
        // de facto jak jest jedno jest i drugie
        $przewoznik_id = !empty($row[Model::COLUMN_RJA_ID_PRZEWOZNIK_WYJAZD]) ? $row[Model::COLUMN_RJA_ID_PRZEWOZNIK_WYJAZD] : null;
        $przewoznik_powrot_id = !empty($row[Model::COLUMN_RJA_ID_PRZEWOZNIK_POWROT]) ? $row[Model::COLUMN_RJA_ID_PRZEWOZNIK_POWROT] : null;
        $rozklad_jazdy_id = !empty($row[Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_WYJAZD]) ? $row[Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_WYJAZD] : null;
        $rozklad_jazdy_powrot_id = !empty($row[Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_POWROT]) ? $row[Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_POWROT] : null;
        
        echo $controls->AddSelectRandomQuerySVbyId('przewoznik', 'id_przewoznik_www', '', 
            "select null as id, '--------' as nazwa union select id, nazwa from przewoznik order by nazwa asc;", 
            $przewoznik_id, 'przewoznik_id_www', 'nazwa', 'id', 
            'getDepartureCityCombo(utils.getElementValue(\'data_wyjazd_wakat\'), utils.getElementValue(\'przewoznik_id_www\'), \'msc_odjazd_www_container\', \'www\', \'msc_odjazd\', \'id_msc_odjazdu\', 0);
            getTicketsCombo(utils.getElementValue(\'przewoznik_id_www\'), \'www\', \'bilet_www_container\');');
            
	    echo '</td></tr><tr><td>Miejscowo¶æ odjazdu:</td><td id="msc_odjazd_www_container">';
	    
        if($rozklad_jazdy_id)
        {
            $dzien = strftime('%w', strtotime($row['data_wyjazdu']));
            if ($przewoznik_id == PRZEWOZNIK_BARTUS_ID) //bartus
            {
                if ($dzien < CZWARTEK_ID)
                    $dzien = PONIEDZIALEK_ID;
                else
                    $dzien = PIATEK_ID;
            }

            if ($przewoznik_id == PRZEWOZNIK_SOLTYSIK_ID)
            {
                if ($dzien != 0 && $dzien != PONIEDZIALEK_ID && $dzien != PIATEK_ID)
                {
                    if ($dzien < CZWARTEK_ID)
                        $dzien = PONIEDZIALEK_ID;
                    else
                        $dzien = PIATEK_ID;
                }
            }
            
            $query = 'select rozklad_jazdy.id,  rozklad_jazdy.id_msc_odjazdu, msc_odjazdu.nazwa || \', \' || rozklad_jazdy.godzina || \', \' || rozklad_jazdy.przystanek as nazwa 
            from rozklad_jazdy join msc_odjazdu on rozklad_jazdy.id_msc_odjazdu = msc_odjazdu.id where rozklad_jazdy.id_przewoznik = '.$przewoznik_id.
            ' and rozklad_jazdy.dzien = '.$dzien.' and active = true order by nazwa asc; ';
            
            // stanowi rownoznacznie o msc odjazdu
            echo $controls->AddSelectRandomQuerySVbyId('rozklad_jazdy_www', 'id_rozklad_jazdy_www', '', $query, $rozklad_jazdy_id, 'rozklad_jazdy_www_id', 'nazwa', 'id', 'utils.setHiddenFromSelectLabel(this, \'msc_odjazd_www_id\');', 'id_msc_odjazdu', 'msc_odjazd_www_id');
        }
        
	    echo '</td></tr><tr><td>Rodzaj biletu:</td><td id="bilet_www_container">';
	    if ($przewoznik_id > 0)
	    {
            $query = 'select id, nazwa from bilety where id_przewoznik = '.$przewoznik_id.' order by nazwa asc;';
            echo $controls->AddSelectRandomQuerySVbyId("id_bilet_www", "id_bilet_www", "", $query, $row['id_bilet'], "bilet_www_id", "nazwa", "id", "");
	    }
	    echo '</td></tr><tr><td>Forma p³atno¶ci:</td><td id="forma_platnosci_www_container">';
        
        $query = 'select 0 AS id, \'--------\' AS nazwa UNION select id, nazwa from forma_platnosci order by nazwa asc';
        echo $controls->AddSelectRandomQuerySVbyId("id_forma_platnosci_www", "id_forma_platnosci_www", "", $query, $row['id_forma_platnosci'], "id_forma_platnosci_www_id", "nazwa", "id", "");
	    
        echo '</td></tr><tr><td>Stan realizacji:</td><td id="stan_realizacji_www_container">';
        
        $query = "select 0 AS id, '--------' AS nazwa UNION select id, nazwa from stan_realizacji order by nazwa asc";
        echo $controls->AddSelectRandomQuerySVbyId("id_stan_realizacji_www", "id_stan_realizacji_www", "", $query, $row['id_ticket_state'], "id_stan_realizacji_www_id", "nazwa", "id", "");
	    
        
	    echo '</td></tr><tr><td>Przewo¼nik powrót:</td><td>';
	    echo $controls->AddSelectRandomQuerySVbyId('przewoznik', 'id_przewoznik_powrot_www', '', 
            "select null as id, '--------' as nazwa union select id, nazwa from przewoznik order by nazwa asc;", 
            $przewoznik_powrot_id, 'przewoznik_id_www', 'nazwa', 'id', 
            'getDepartureCityCombo(utils.getElementValue(\'data_wyjazd_wakat\'), utils.getElementValue(\'przewoznik_id_www\'), \'msc_powrot_www_container\', \'powrot_www\', \'msc_powrot\', \'id_msc_powrotu\', 1);');
            
	    echo '</td></tr><tr><td>Miejscowo¶æ powrotu:</td><td id="msc_powrot_www_container">';
	    
	    if ($rozklad_jazdy_powrot_id)
	    {
	        $dzien = strftime('%w', strtotime($row['data_wyjazdu']));
            if ($przewoznik_powrot_id == PRZEWOZNIK_BARTUS_ID) //bartus
            {
                if ($dzien < CZWARTEK_ID)
                    $dzien = PONIEDZIALEK_ID;
                else
                    $dzien = PIATEK_ID;
            }

            if ($przewoznik_powrot_id == PRZEWOZNIK_SOLTYSIK_ID)
            {
                if ($dzien != PONIEDZIALEK_ID && $dzien != PIATEK_ID)
                {
                    if ($dzien < CZWARTEK_ID)
                        $dzien = PONIEDZIALEK_ID;
                    else
                        $dzien = PIATEK_ID;
                }
            }
            
            $query = 'select rozklad_jazdy.id,  rozklad_jazdy.id_msc_odjazdu as id_msc_powrotu, msc_odjazdu.nazwa || \', \' || rozklad_jazdy.godzina || \', \' || rozklad_jazdy.przystanek as nazwa 
            from rozklad_jazdy join msc_odjazdu on rozklad_jazdy.id_msc_odjazdu = msc_odjazdu.id where rozklad_jazdy.id_przewoznik = '.$przewoznik_powrot_id.
            ' and rozklad_jazdy.dzien = '.$dzien.' and active = true order by nazwa asc; ';
            
            // stanowi rownoznacznie o msc odjazdu
            echo $controls->AddSelectRandomQuerySVbyId('rozklad_jazdy_powrot_www', 'id_rozklad_jazdy_powrot_www', '', 
                $query, $rozklad_jazdy_powrot_id, 'rozklad_jazdy_powrot_www_id', 'nazwa', 'id', 'utils.setHiddenFromSelectLabel(this, \'msc_powrot_www_id\');', 'id_msc_powrotu', 'msc_powrot_www_id');
        }
	    
        echo '</td></tr><tr><td>Miejsce docelowe:</td><td id="msc_docelowe_container">';
        echo '</td></tr><tr><td>Osoba kontaktowa:</td><td id="osoba_kontaktowa_container">';
	    echo '</td></tr><tr><td colspan="2">';
        echo $controls->AddSubmit($update, $row['id'], "Aktualizuj.", "onclick=\"utils.setHiddenOnLoad('destination_wysw_wyb_wakat,contactPerson_wysw_wyb_wakat', 'id_destination_wysw_wyb_wakat,id_contactPerson_wysw_wyb_wakat');\"");
        echo $controls->AddSubmit($delete, $row['id'], "Usuñ.", '');
        echo "<button class=\"formreset\" onclick=\"window.open('/prawa_strona/bilety.php?id={$wiersz['id']}')\">Poka¿ bilet</button></td></tr></table></form>";

        echo "\n\n\n<script>"
                . "if (typeof employmentHistoryData == 'undefined') {  employmentHistoryData = {}; }"
                . "employmentHistoryData.wysw_wyb_wakat = {}; "
                . "employmentHistoryData.wysw_wyb_wakat.wakatDropdownsSelector = '#id_wakat_www';"
                . "employmentHistoryData.wysw_wyb_wakat.destinationId = " . ($row['id_miejsca_docelowe'] ? $row['id_miejsca_docelowe'] : 0) .";"
                . "employmentHistoryData.wysw_wyb_wakat.contactPerson = ". ($row['id_osoby_kontaktowe'] ? $row['id_osoby_kontaktowe'] : 0) .";"
        . "</script>\n\n";

        echo $htmlControls->_AddNoPrivilegeSubmit('', '', 'Wewnetrzny opis pracy', '', '
        onclick="var url = \'wewn_opis_pracy.php?'.Model::COLUMN_OPR_ID.'=\' + utils.getElementById(\'oddzial_id_www\').value;
        window.open(url, \'wewnetrzny_opis_pracy\', \'toolbar=no, scrollbars=yes, width=750,height=650\');"
        ', 'button');
    }
