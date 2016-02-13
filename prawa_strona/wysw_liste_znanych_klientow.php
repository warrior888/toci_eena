<?php
    if (empty($_SESSION['uzytkownik']))
    {
        die();
    }
    else
    {               
        require_once 'dal/DALZatrudnienie.php';
        require_once 'adl/User.php';
            
        $insert = 'insert_pasywny';
               
        if (isset($_POST[$insert]))     //todo : add trigger logic and validation logic, both seem the same :P
        {
            $chosenStatus = (int)$_POST['status_wlzk_id'];
            $idDeparturePlan = isset($_POST['rozklad_jazdy_wlzk_id']) ? (int)$_POST['rozklad_jazdy_wlzk_id'] : null;
            $idDepartureReturnPlan = isset($_POST['rozklad_jazdy_powrot_wlzk_id']) ? (int)$_POST['rozklad_jazdy_powrot_wlzk_id'] : null;
            
            if ($chosenStatus == ID_STATUS_WYJEZDZAJACY && !$idDeparturePlan) {
                
                echo '<label class="error">B³±d. Ustalono status wyje¿d¿aj±cy bez podania odjazdu. </label><br />';
            } else if ($_POST['klient_wlzk_id'] > 0 && $_POST['oddzial_wlzk_id'] > 0 && $_POST['status_wlzk_id'] > 0 && strlen($_POST['data_wyjazdu_wlzk']) == 10 && $_POST['ilosc_tyg_wlzk'] > 0) {
                
                //jesli wystepuje juz jakies umowienie w przyszlosci formularz insertowy sie nie pojawi (pojawi sie update'owy)
                //wiec walidacja nie ma sensu :P
                $data = explode("-",$_POST['data_wyjazdu_wlzk']);
                $data_powrot = oblicz_date($data[0],$data[1],$data[2],$_POST['ilosc_tyg_wlzk']);

                if (ColisionDetection($_POST[ID_OSOBA], 0, $_POST['data_wyjazdu_wlzk'], $data_powrot, $db, $controls) == enum::$ALLOWDATA)
                {
                    $dalZatrudnienie = new DALZatrudnienie();
                    $user = User::getInstance();
        
                    $data = array(
                        Model::COLUMN_ZTR_ID_OSOBA         => (int)$_POST[ID_OSOBA],
                        Model::COLUMN_ZTR_ID_KLIENT        => (int)$_POST['klient_wlzk_id'],
                        Model::COLUMN_ZTR_ID_ODDZIAL       => (int)$_POST['oddzial_wlzk_id'],
                        Model::COLUMN_ZTR_ID_WAKAT         => (int)$wakat_const_pasywny,
                        Model::COLUMN_ZTR_ID_STATUS        => $chosenStatus,
                        Model::COLUMN_ZTR_DATA_WYJAZDU     => $_POST['data_wyjazdu_wlzk'],
                        Model::COLUMN_ZTR_ILOSC_TYG        => (int)$_POST['ilosc_tyg_wlzk'],
                        Model::COLUMN_ZTR_DATA_POWROTU     => $data_powrot,
                        Model::COLUMN_ZTR_ID_DECYZJA       => (int)$_POST['decyzja_wlzk_id'],
                        Model::COLUMN_ZTR_ID_MSC_ODJAZD    => isset($_POST['msc_odjazd_wlzk_id']) ? (int)$_POST['msc_odjazd_wlzk_id'] : null,
                        Model::COLUMN_ZTR_ID_MSC_POWROT    => isset($_POST['msc_powrot_powrot_wlzk_id']) ? (int)$_POST['msc_powrot_powrot_wlzk_id'] : null,
                        Model::COLUMN_ZTR_ID_BILET         => isset($_POST['bilet_wlzk_id']) ? (int)$_POST['bilet_wlzk_id'] : null,
                        Model::COLUMN_ZTR_ID_PRACOWNIK     => (int)$user->getUserId(),
                        Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_WYJAZD => $idDeparturePlan,
                        Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_POWROT => $idDepartureReturnPlan,
                        'id_miejsca_docelowe'              => (int)$_POST['destination_wysw_liste_znanych_klientow'],
                        'id_osoby_kontaktowe'              => (int)$_POST['contactPerson_wysw_liste_znanych_klientow'],
                        'id_forma_platnosci'               => (int)$_POST['forma_platnosci_ww_id'],
                        'id_ticket_state'                  => (int)$_POST['stan_realizacji_ww_id'],
                    );
                    
                    $dalZatrudnienie->set($data);
                    
                    TriggerLogic($query, $dzis, $_POST['data_wyjazdu_wlzk'], $data_powrot, $_POST['status_wlzk_id'], $_POST[ID_OSOBA], $_POST['msc_odjazd_wlzk_id'], $controls, $db, $wyodrMsc);
                    $result = $controls->dalObj->pgQuery($query);
                    //correct in edit ids of data wyjazdu and ilosc tygodni, ctrls, vals
                    //pass data wyjazdu and ilosc tygodni
                    echo 'Umówiono osobê.<script>PassValsToOpener("datawyjazd,tygodnie", "'.$_POST['data_wyjazdu_wlzk'].','.$_POST['ilosc_tyg_wlzk'].'"); window.close(); </script>';//
                    //die();
                }
                else
                {
                    echo 'B³±d spójno¶ci danych, nie umówiono (zatrudnienie zachodzi terminami z innym zatrudnieniem).';
                }
            }
            else
            {
                echo '<label class="error">Formularz niekompletny. ';
                
                if (!$_POST['ilosc_tyg_wlzk'] > 0)
                    echo 'Podaj Ilo¶æ tygodni. ';
                    
                if (!strlen($_POST['data_wyjazdu_wlzk']) == 10)
                    echo 'Podaj datê wyjazdu.';
                    
                echo '</label>';
            }
        }
        //klienci, gdzie osoba pracowala                
	    $query = "select distinct id_oddzial, id_oddzial as id, 
	    ".$db->tableName.".id_klient, ".$klient->tableName.".nazwa_alt || ', ' || ".$oddzial->tableName.".nazwa as nazwa,
	     ".$klient->tableName.".nazwa_alt || ', ' || ".$oddzial->tableName.".nazwa as klient  
        from ".$db->tableName."
        join ".$oddzial->tableName." on ".$db->tableName.".id_oddzial = ".$oddzial->tableName.".".$oddzial->tableId."
        join ".$klient->tableName." on ".$db->tableName.".id_klient = ".$klient->tableName.".".$klient->tableId."
        where id_oddzial in 
        (select distinct id_oddzial from ".$db->tableName." where id_osoba = '".$id_osoba."' and id_status in 
        (select ".$status->tableId." from ".$status->tableName." where nazwa in ('Pasywny','Aktywny')));";

	    $vacatsList = $controls->dalObj->PobierzDane($query);
	    
	    $addsList = array(
            'klient_wlzk_id' => 'id_klient',
	    	'oddzial_wlzk_id' => 'id_oddzial',
        );
        
        if (sizeof($vacatsList) == 0)
        {
            $experiencedEmploymentForm = '<br /><br />Brak danych (kleintów) formularza umawiania pasywnego kandydata.';
        }
        else 
        {
    	    $experiencedEmploymentForm = '<form method="POST" action="'.$_SERVER['PHP_SELF'].'" id="wysw_liste_znanych_klientow"><table><tr>
    	    Poni¿ej widnieje lista klientów, u których osoba jest pasywna. Do tych klientów mo¿na ponownie umówiæ osobê.</tr>
    	    <tr><td>Klient:</td><td>';
            $experiencedEmploymentForm .= $controls->AddSelectHelpHidden().$controls->AddHidden(ID_OSOBA, ID_OSOBA, $id_osoba);
            $experiencedEmploymentForm .= $htmlControls->_AddSelect("klient_wlzk", "klient_wlzk", $vacatsList, null, "oddzial_wlzk_id", true, '', '', '', 
                $addsList, 
                    "employmentHistory.setWakatDropdowns('wysw_liste_znanych_klientow'); " . 
                    "utils.resetSelect('id_przewoznik_wlzk', 'przewoznik_id_wlzk');");
    
            $query = "select ".$status->tableId.", nazwa from ".$status->tableName." where nazwa in ('Aktywny','Pasywny','Wyje¿d¿aj±cy') order by nazwa asc;";
            $experiencedEmploymentForm .= '</td></tr><tr><td>Status:</td><td>';
            $experiencedEmploymentForm .= $controls->AddSelectRandomQuery("id_status_wlzk", "id_status_wlzk", "", $query, "Pasywny", "status_wlzk_id", "nazwa", "id", "");
            $experiencedEmploymentForm .= '</td></tr><tr><td>Data wyjazdu:</td><td>';
            $experiencedEmploymentForm .= $controls->AddDateboxFuture("data_wyjazdu_wlzk", "data_wyjazdu_wlzk", "", 10, 10, 
            JsEvents::ONCHANGE.'="utils.resetSelect(\'id_przewoznik_wlzk\', \'przewoznik_id_wlzk\'); utils.getElementById(\'msc_odjazd_wlzk_container\').innerHTML = \'\';"');
            $experiencedEmploymentForm .= '</td></tr><tr><td>Ilo¶æ tygodni:</td><td>';
            $experiencedEmploymentForm .= $controls->AddNumberbox("ilosc_tyg_wlzk", "tygodnie", "",  2, 3, "sprawdz_tygodnie(this)");
            $experiencedEmploymentForm .= '</td></tr><tr><td>Decyzja:</td><td>';
            $experiencedEmploymentForm .= $controls->AddSelectRandomQuery("id_decyzja_wlzk", "id_decyzja_wlzk", "", "select id, nazwa from decyzja order by nazwa asc;", "Umówiony", "decyzja_wlzk_id", "nazwa", "id", "");
            
            $experiencedEmploymentForm .= '</td></tr><tr><td>Przewo¼nik:</td><td>';
            $experiencedEmploymentForm .= $controls->AddSelectRandomQuerySVbyId('przewoznik', 'id_przewoznik_wlzk', '', 
                "select null as id, '--------' as nazwa union select id, nazwa from przewoznik order by nazwa asc;", 
                null, 'przewoznik_id_wlzk', 'nazwa', 'id', 
                'getDepartureCityCombo(utils.getElementValue(\'data_wyjazdu_wlzk\'), utils.getElementValue(\'przewoznik_id_wlzk\'), \'msc_odjazd_wlzk_container\', \'wlzk\', \'msc_odjazd\', \'id_msc_odjazdu\');
                getTicketsCombo(utils.getElementValue(\'przewoznik_id_wlzk\'), \'wlzk\', \'bilet_wlzk_container\', 0);');
            $experiencedEmploymentForm .= '</td></tr>';
            $experiencedEmploymentForm .= '<tr><td>Miejscowo¶æ odjazdu:</td><td id="msc_odjazd_wlzk_container">';
            $experiencedEmploymentForm .='</td></tr>';

            //przy wrzucaniu na pasywnego nie ma mowy o decyzji, wszyscy sa umowieni, bo to nie dodawanie do wakatu
    
            $experiencedEmploymentForm .= '<tr><td>Rodzaj biletu:</td><td id="bilet_wlzk_container">';
            $experiencedEmploymentForm .= '</td></tr><tr><td>Forma p³atno¶ci:</td><td id="forma_platnosci_wlzk_container">';
            $experiencedEmploymentForm .= '</td></tr><tr><td>Stan realizacji:</td><td id="stan_realizacji_wlzk_container">';
            $experiencedEmploymentForm .= '</td></tr><tr><td>Przewo¼nik powrót:</td><td>';
            
            $experiencedEmploymentForm .= $controls->AddSelectRandomQuerySVbyId('przewoznik_powrot', 'id_przewoznik_powrot_wlzk', '', 
                "select null as id, '--------' as nazwa union select id, nazwa from przewoznik order by nazwa asc;", 
                null, 'przewoznik_id_wlzk', 'nazwa', 'id', 
                'getDepartureCityCombo(utils.getElementValue(\'data_wyjazdu_wlzk\'), utils.getElementValue(\'przewoznik_id_wlzk\'), \'msc_odjazd_powrot_wlzk_container\', \'powrot_wlzk\', \'msc_powrot\', \'id_msc_powrotu\', 1);');
            $experiencedEmploymentForm .= '</td></tr>';
            
            $experiencedEmploymentForm .= '<tr><td>Miejscowo¶æ powrotu:</td><td id="msc_odjazd_powrot_wlzk_container">';
            
            $experiencedEmploymentForm .= '<tr><td>Data zapisu:</td><td><input type="text" name="data_wpisu_wlzk" class="formfield" value="'.$dzis.'" READONLY>';
            $experiencedEmploymentForm .= '</td></tr><tr><td>Miejsce docelowe:</td><td id="msc_docelowe_container">';
            $experiencedEmploymentForm .= '</td></tr><tr><td>Osoba kontaktowa:</td><td id="osoba_kontaktowa_container">';
            $experiencedEmploymentForm .= '</td></tr><tr><td>';
            $experiencedEmploymentForm .= $controls->AddSubmit($insert, $id_osoba, "Umów.", "onclick='".ID_OSOBA.".value = this.id;'");
            $experiencedEmploymentForm .= '</td></tr></table></form>';
            
            $experiencedEmploymentForm .= $htmlControls->_AddNoPrivilegeSubmit('', '', 'Wewnetrzny opis pracy', '', '
            onclick="var url = \'wewn_opis_pracy.php?'.Model::COLUMN_OPR_ID.'=\' + utils.getElementById(\'oddzial_wlzk_id\').value;
            window.open(url, \'wewnetrzny_opis_pracy\', \'toolbar=no, scrollbars=yes, width=750,height=650\');"
            ', 'button');
            
             $experiencedEmploymentForm .= "\n\n\n<script>"
                    . "if (typeof employmentHistoryData == 'undefined') {  employmentHistoryData = {}; }"
                    . "employmentHistoryData.wysw_liste_znanych_klientow = {}; "
                    . "employmentHistoryData.wysw_liste_znanych_klientow.wakatDropdownsSelector = '#klient_wlzk';"
                    . "employmentHistoryData.wysw_liste_znanych_klientow.destinationId = " . ($row['id_miejsca_docelowe'] ? $row['id_miejsca_docelowe'] : 0) .";"
                    . "employmentHistoryData.wysw_liste_znanych_klientow.contactPerson = ". ($row['id_osoby_kontaktowe'] ? $row['id_osoby_kontaktowe'] : 0) .";"
            . "</script>\n\n";
            
        }
        
        echo $experiencedEmploymentForm;
    }