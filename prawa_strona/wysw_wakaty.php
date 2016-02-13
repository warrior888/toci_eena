<?php
    require_once 'dal/DALZatrudnienie.php';
    require_once 'adl/User.php';
    
    if (empty($_SESSION['uzytkownik']))
    {
        die();
    }
    else
    {        
        $sepChar = ',';
        
        $insert = 'insert_to_wakat';
        $errMsgs = array();
        
        if (isset($_POST[$insert]))
        {
            $regexpValidations = array(
            
                ID_OSOBA                    => '/^[1-9]{1}[0-9]*$/',
                'oddzial_id_wakat_ww'       => '/^[1-9]{1}[0-9]*$/',
                'ilosc_tyg_ww'              => '/^[1-9]{1}[0-9]*$/',
                'klient_id_wakat_ww'        => '/^[1-9]{1}[0-9]*$/',
                'wakat_ww_id'               => '/^[1-9]{1}[0-9]*$/',
                'status_ww_id'              => '/^[1-9]{1}[0-9]*$/',
                'decyzja_ww_id'             => '/^[1-9]{1}[0-9]*$/',
                'bilet_ww_id'               => '/^[1-9]{1}[0-9]*$/',
            );
            
            $requiredFields = array (
            
                ID_OSOBA                    => 1,
                'oddzial_id_wakat_ww'       => 1,
                'ilosc_tyg_ww'              => 1,
                'klient_id_wakat_ww'        => 1,
                'wakat_ww_id'               => 1,
                'status_ww_id'              => 1,
                'decyzja_ww_id'             => 1,
                //'bilet_ww_id'               => 1,
            );
            
            $validator = new FormValidator($regexpValidations, array(), $requiredFields);
            $validationResult = $validator->validate($_POST);
            
            $chosenStatus = (int)$_POST['status_ww_id'];
            $idDeparturePlan = isset($_POST['rozklad_jazdy_ww_id']) ? (int)$_POST['rozklad_jazdy_ww_id'] : null;
            $idDepartureReturnPlan = isset($_POST['rozklad_jazdy_powrot_ww_id']) ? (int)$_POST['rozklad_jazdy_powrot_ww_id'] : null;
            
            if (isset($validationResult[FormValidator::VALIDATION_ERRORS]) && sizeof($validationResult[FormValidator::VALIDATION_ERRORS]) > 0) {
                
                echo '<br /><label class="error">Formularz niekompletny lub niepoprawnie wype³niony.</label><br />';
                $errMsgs = $validationResult[FormValidator::VALIDATION_ERRORS];
            } else if ($chosenStatus == ID_STATUS_WYJEZDZAJACY && !$idDeparturePlan) {
                
                echo '<label class="error">B³±d. Ustalono status wyje¿d¿aj±cy bez podania odjazdu. </label><br />';
            } else {
            
                $odlamki = explode($sepChar, $_POST['wakaty']);
                //w odlamki [0] data wyjazdu
                $data = explode("-",$odlamki[0]);
                $dataWyjazd = $odlamki[0];
                $data_powrot = oblicz_date($data[0],$data[1],$data[2],$_POST['ilosc_tyg_ww']);

                if (ColisionDetection($_POST[ID_OSOBA], 0, $odlamki[0], $data_powrot, $db, $controls) == enum::$ALLOWDATA)
                {                    
                    $dalZatrudnienie = new DALZatrudnienie();
                    $user = User::getInstance();
                    
                    $data = array(
                        Model::COLUMN_ZTR_ID_OSOBA         => (int)$_POST[ID_OSOBA],
                        Model::COLUMN_ZTR_ID_KLIENT        => (int)$_POST['klient_id_wakat_ww'],
                        Model::COLUMN_ZTR_ID_ODDZIAL       => (int)$_POST['oddzial_id_wakat_ww'],
                        Model::COLUMN_ZTR_ID_WAKAT         => (int)$_POST['wakat_ww_id'],
                        Model::COLUMN_ZTR_ID_STATUS        => $chosenStatus,
                        Model::COLUMN_ZTR_DATA_WYJAZDU     => $dataWyjazd,
                        Model::COLUMN_ZTR_ILOSC_TYG        => (int)$_POST['ilosc_tyg_ww'],
                        Model::COLUMN_ZTR_DATA_POWROTU     => $data_powrot,
                        Model::COLUMN_ZTR_ID_DECYZJA       => (int)$_POST['decyzja_ww_id'],
                        Model::COLUMN_ZTR_ID_MSC_ODJAZD    => isset($_POST['msc_odjazd_ww_id']) ? (int)$_POST['msc_odjazd_ww_id'] : null,
                        Model::COLUMN_ZTR_ID_BILET         => isset($_POST['bilet_ww_id']) ? (int)$_POST['bilet_ww_id'] : null,
                        Model::COLUMN_ZTR_ID_MSC_POWROT    => isset($_POST['msc_powrot_powrot_ww_id']) ? (int)$_POST['msc_powrot_powrot_ww_id'] : null,
                        Model::COLUMN_ZTR_ID_PRACOWNIK     => (int)$user->getUserId(),
                        Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_WYJAZD => $idDeparturePlan,
                        Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_POWROT => $idDepartureReturnPlan,
                        'id_miejsca_docelowe'              => (int)$_POST['destination_wysw_wakaty'],
                        'id_osoby_kontaktowe'              => (int)$_POST['contactPerson_wysw_wakaty'],
                        'id_forma_platnosci'               => (int)$_POST['forma_platnosci_ww_id'],
                        'id_ticket_state'                  => (int)$_POST['stan_realizacji_ww_id'],
                    );
                    
                    $dalZatrudnienie->set($data);
                        
                    $query = "update ".$daneOs->tableName." set data='".$odlamki[0]."', ilosc_tyg=".(int)$_POST['ilosc_tyg_ww']." where id = ".$_POST[ID_OSOBA].";";

                    TriggerLogic($query, $dzis, $odlamki[0], $data_powrot, $_POST['status_ww_id'], $_POST[ID_OSOBA], $_POST['msc_odjazd_ww_id'], $controls, $db, $wyodrMsc);
                    $result = $controls->dalObj->pgQuery($query);
                    //correct in edit ids of data wyjazdu and ilosc tygodni, ctrls, vals
                    //pass data wyjazdu and ilosc tygodni
                    echo 'Umówiono osobê.<script>PassValsToOpener("datawyjazd,tygodnie", "'.$odlamki[0].','.$_POST['ilosc_tyg_ww'].'"); window.close();</script>';//
                    //die();
                }
                else
                {
                    echo 'B³±d spójno¶ci danych, nie wprowadzono do wakatu (zatrudnienie zachodzi terminami z innym zatrudnieniem).';
                }
            }
        }
        
        $htmlControls = new HtmlControls($errMsgs);
        
        ////na zdarzenie zmiany daty (wakatu, itp) trzeba bezwzglednie czyscic msc_odjazd_container !!!!
	    $employmentFormHtml = '<form method="POST" action="'.$_SERVER['PHP_SELF'].'" id="wysw_wakaty"><table><tr><td>Wakat:</td><td>';
	    $query = "select ".$wakat->tableName.".".$wakat->tableId.", ".$wakat->tableName.".id_klient, id_oddzial, data_wyjazdu, 
        data_wyjazdu || ', ' || ".$klient->tableName.".nazwa_alt || ', ' || ".$oddzial->tableName.".nazwa as ".$wakat_const_name."         
        from ".$wakat->tableName."
        join ".$oddzial->tableName." on ".$wakat->tableName.".id_oddzial = ".$oddzial->tableName.".".$oddzial->tableId."
        join ".$klient->tableName." on ".$wakat->tableName.".id_klient = ".$klient->tableName.".".$klient->tableId."
        where data_wyjazdu >= '".$dzis."' order by data_wyjazdu asc;";

        $employmentFormHtml .= $controls->AddHidden(ID_OSOBA, ID_OSOBA, $id_osoba);
        $employmentFormHtml .= $controls->AddSelectHelpHidden();
            
        $vacatsList = $controls->dalObj->PobierzDane($query);
        $addsList = array(
            'data_wyjazd_wakat' => 'data_wyjazdu',
            'klient_id_wakat_ww' => 'id_klient',
            'oddzial_id_wakat_ww' => 'id_oddzial',
        );
        
        if (sizeof($vacatsList) < 1)
        {
            $employmentFormHtml = '<br /><br />Nie ma nowych wakatów. Nie ma mo¿liwoœci umówienia kogokolwiek.';
        }
        else 
        {
            $employmentFormHtml .= $htmlControls->_AddSelect("wakaty", "wakaty", $vacatsList, null, "wakat_ww_id", true, '', '', '', $addsList, 
            "employmentHistory.setWakatDropdowns('wysw_wakaty'); utils.resetSelect('id_przewoznik_ww', 'przewoznik_id_ww'); utils.getElementById('msc_odjazd_ww_container').innerHTML = '';");
            
    	    $employmentFormHtml .= '</td></tr><tr><td>Status:</td><td>';
            $query = 'select '.$status->tableId.', nazwa from '.$status->tableName.' where id in ('.ID_STATUS_NOWY.', '.ID_STATUS_AKTYWNY.', '.ID_STATUS_WYJEZDZAJACY.') order by nazwa asc;';
            $employmentFormHtml .= $controls->AddSelectRandomQuery("id_status_ww", "id_status_ww", "", $query, "Nowy", "status_ww_id", "nazwa", "id", "", "");
            $employmentFormHtml .= '</td></tr><tr><td>Ilo¶æ tygodni:</td><td>';
            $employmentFormHtml .= $htmlControls->_AddNumberbox("ilosc_tyg_ww", "tygodnie", '',  2, 3, 'onblur="sprawdz_tygodnie(this);"');
            $employmentFormHtml .= '</td></tr><tr><td>Decyzja:</td><td>';
            $employmentFormHtml .= $controls->AddSelectRandomQuery("id_decyzja_ww", "id_decyzja_ww", "", "select id, nazwa from decyzja order by nazwa asc;", "Umówiony", "decyzja_ww_id", "nazwa", "id", "");
    	    $employmentFormHtml .= '</td></tr><tr><td>Przewo¼nik:</td><td>';
    
            $employmentFormHtml .= $controls->AddSelectRandomQuerySVbyId('przewoznik', 'id_przewoznik_ww', '', 
                "select null as id, '--------' as nazwa union select id, nazwa from przewoznik order by nazwa asc;", 
                null, 'przewoznik_id_ww', 'nazwa', 'id', 
                'getDepartureCityCombo(utils.getElementValue(\'data_wyjazd_wakat\'), utils.getElementValue(\'przewoznik_id_ww\'), \'msc_odjazd_ww_container\', \'ww\', \'msc_odjazd\', \'id_msc_odjazdu\', 0);
                getTicketsCombo(utils.getElementValue(\'przewoznik_id_ww\'), \'ww\', \'bilet_ww_container\');');
            $employmentFormHtml .= '</td></tr><tr><td>Miejscowo¶æ odjazdu:</td><td id="msc_odjazd_ww_container">';
            
            $employmentFormHtml .= '</td></tr><tr><td>Rodzaj biletu:</td><td id="bilet_ww_container">';
            $employmentFormHtml .= '</td></tr><tr><td>Forma p³atno¶ci:</td><td id="forma_platnosci_ww_container">';
            $employmentFormHtml .= '</td></tr><tr><td>Stan realizacji:</td><td id="stan_realizacji_ww_container">';
            $employmentFormHtml .= '</td></tr><tr><td>Przewo¼nik powrotny:</td><td>';
            $employmentFormHtml .= $controls->AddSelectRandomQuerySVbyId('przewoznik_powrot', 'id_przewoznik_powrot_ww', '', 
                "select null as id, '--------' as nazwa union select id, nazwa from przewoznik order by nazwa asc;", 
                null, 'przewoznik_powrot_id_ww', 'nazwa', 'id', 
                'getDepartureCityCombo(utils.getElementValue(\'data_wyjazd_wakat\'), utils.getElementValue(\'przewoznik_powrot_id_ww\'), \'msc_powrot_ww_container\', \'powrot_ww\', \'msc_powrot\', \'id_msc_powrotu\', 1);');
            $employmentFormHtml .= '</td></tr><tr><td>Miejscowo¶æ powrotu:</td><td id="msc_powrot_ww_container">';
            $employmentFormHtml .= '</td></tr><tr><td>Miejsce docelowe:</td><td id="msc_docelowe_container">';
            $employmentFormHtml .= '</td></tr><tr><td>Osoba kontaktowa:</td><td id="osoba_kontaktowa_container">';
            $employmentFormHtml .= '</td></tr><tr><td>Data zapisu:</td><td><input type="text" name="data_wpisu_ww" class="formfield" value="'.$dzis.'" READONLY></td></tr><tr><td>';
            $employmentFormHtml .= $controls->AddSubmit($insert, $id_osoba, "Umów.", "onclick='".ID_OSOBA.".value = this.id;'");
            $employmentFormHtml .= '</td></tr></table></form>';
            
            $employmentFormHtml .= $htmlControls->_AddNoPrivilegeSubmit('', '', 'Wewnetrzny opis pracy', '', '
            onclick="var url = \'wewn_opis_pracy.php?'.Model::COLUMN_OPR_ID.'=\' + utils.getElementById(\'oddzial_id_wakat_ww\').value;
            window.open(url, \'wewnetrzny_opis_pracy\', \'toolbar=no, scrollbars=yes, width=750,height=650\');"
            ', 'button');
        }
        
        echo "\n\n\n<script>"
                . "if (typeof employmentHistoryData == 'undefined') {  employmentHistoryData = {}; }"
                . "employmentHistoryData.wysw_wakaty = {}; "
                . "employmentHistoryData.wysw_wakaty.wakatDropdownsSelector = '#wakaty';"
                . "employmentHistoryData.wysw_wakaty.destinationId = 0;"
                . "employmentHistoryData.wysw_wakaty.contactPerson = 0;"
        . "</script>\n\n";
        
        echo $employmentFormHtml;
    }
