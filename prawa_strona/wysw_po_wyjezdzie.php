<?php
    require_once 'dal/DALZatrudnienie.php';
    require_once 'adl/User.php';

    if (empty($_SESSION['uzytkownik']))
    {
        die();
    }
    else
    {
        $update = 'update_wpw';
        $delete = 'delete_wpw';
        
        $dalZatrudnienie = new DALZatrudnienie();
        
        if (isset($_POST[$delete]))
        {
            $idZatrudnienie = (int)$_POST[ID_ZATRUDNIENIE];
            $reason = $dalZatrudnienie->getUnsuitableReasonByEmpId($idZatrudnienie);
            
            if (is_null($reason))
            {
                $query = "delete from zatrudnienie_odjazd where id_zatrudnienie = ".$idZatrudnienie.";
                delete from ".$db->tableName." where ".$db->tableName.".".$db->tableId." = ".$idZatrudnienie.";";
                $result = $controls->dalObj->pgQuery($query);
                DeleteTriggerLogic($id_osoba, $controls);
                echo '<script>window.close();</script>';
            }
            else 
            {
                echo 'Tego rekordu ju¿ nie mo¿na skasowaæ, istnieje zapis o powodzie ustawienia statusu nieodpowiedniego.';
            }
        }
        if (isset($_POST[$update]))
        {
            $chosenStatus = (int)$_POST['status_wpw_id'];
            $idZatrudnienie = (int)$_POST[ID_ZATRUDNIENIE];
            $isUnsuitableReasonRequired = false;
            
            if ($chosenStatus == ID_STATUS_NIEODPOWIEDNI)
            {
                $reason = $dalZatrudnienie->getUnsuitableReasonByEmpId($idZatrudnienie);
                
                $isUnsuitableReasonRequired = is_null($reason) ? !(strlen($_POST['nieodpowiedni_powod']) > 10) : false;
            }
            
            if (!$isUnsuitableReasonRequired)
            {
                if (isset($_POST['msc_odjazd_wpw_id']))
                {
                    $idMscOdjazd = (int)$_POST['msc_odjazd_wpw_id'];
                    $idBilet = (int)$_POST['bilet_wpw_id'];
                    $idRozkladJazdy = !empty($_POST['rozklad_jazdy_wpw_id']) ? (int)$_POST['rozklad_jazdy_wpw_id'] : null;
                    $idDepartureReturnPlan = isset($_POST['rozklad_jazdy_powrot_wpw_id']) ? (int)$_POST['rozklad_jazdy_powrot_wpw_id'] : null;
                    
                    $user = User::getInstance();
                    
                    $data = array(
                        Model::COLUMN_ZTR_ID               => (int)$_POST[ID_ZATRUDNIENIE],
                        Model::COLUMN_ZTR_ID_STATUS        => $chosenStatus,
                        Model::COLUMN_ZTR_ILOSC_TYG        => (int)$_POST['ilosc_tyg_wpw'],
                        Model::COLUMN_ZTR_DATA_POWROTU     => $_POST['data_powrotu_wpw'],
                        Model::COLUMN_ZTR_ID_MSC_ODJAZD    => $idMscOdjazd,
                        Model::COLUMN_ZTR_ID_MSC_POWROT    => isset($_POST['msc_powrot_wpw_id']) ? (int)$_POST['msc_powrot_wpw_id'] : null,
                        Model::COLUMN_ZTR_ID_BILET         => $idBilet,
                        Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_WYJAZD => $idRozkladJazdy,
                        Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_POWROT => $idDepartureReturnPlan,
                        'id_miejsca_docelowe'              => (int)$_POST['destination_wysw_po_wyjezdzie'],
                        'id_osoby_kontaktowe'              => (int)$_POST['contactPerson_wysw_po_wyjezdzie'],
                    );
                    
                    if (strlen($_POST['nieodpowiedni_powod']) > 0)
                    {
                        $data[Model::COLUMN_NPO_POWOD] = array(
                            Model::COLUMN_NPO_POWOD            =>     $_POST['nieodpowiedni_powod'],
                            Model::COLUMN_ZTR_ID_OSOBA         =>     (int)$_POST[ID_OSOBA],
                            Model::COLUMN_ZTR_ID_PRACOWNIK     =>     (int)$user->getUserId(),
                            Model::COLUMN_ZTR_ID               =>     (int)$_POST[ID_ZATRUDNIENIE],
                        );
                    }
                    
                    if (!($idMscOdjazd > 0)) {
    
                        die('Nieprawid³owe miejsce odjazdu, nic nie zaktualizowano.');
                    }
    
                    if (ColisionDetection($id_osoba, $_POST[ID_ZATRUDNIENIE], $_POST['data_wyjazdu_wpw'], $_POST['data_powrotu_wpw'], $db, $controls) == enum::$ALLOWDATA)
                    {
                        if ($_POST['data_wyjazdu_wpw'] < $_POST['data_powrotu_wpw'])
                        {
                            $dalZatrudnienie->set($data);
                            TriggerLogic($query, $dzis, $_POST['data_wyjazdu_wpw'], $_POST['data_powrotu_wpw'], $_POST['status_wpw_id'], $id_osoba, $_POST['msc_odjazd_wpw_id'], $controls, $db, $wyodrMsc);
                            $result = $controls->dalObj->pgQuery($query);
                            
                            echo 'Zaktualizowano osobê.<script>window.close();</script>';
                            
                            //cut from the line above
                            //PassValsToOpener("datawyjazd,tygodnie", "'.$_POST['data_wyjazdu_wwkp'].','.$_POST['ilosc_tyg_wpw'].'");
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
                    echo 'Brakuje miejsca odjazdu, nic nie zaktualizowano.';
                }
            }
            else
            {
                echo 'Brakuje powodu ustalenia statusu nieodpowiedniego.';
            }
        }
        
        $zapytanie = "select imie, nazwisko from dane_osobowe 
        where dane_osobowe.id = '".$wiersz['id_osoba']."';";
        $wynik1 = $controls->dalObj->pgQuery($zapytanie);
        $wiersz1 = pg_fetch_array($wynik1);
        
        $zapytanie = "select ".$zatrudnienie_all_cols.", uprawnienia.imie_nazwisko, 
        ".$klient->tableName.".nazwa_alt || ', ' || ".$oddzial->tableName.".nazwa as ".$klient_const_name.", bilety.nazwa as bilet,
        ".$decyzja->tableName.".nazwa as decyzja from ".$db->tableName."         
        join uprawnienia on uprawnienia.id = ".$db->tableName.".id_pracownik 
        join ".$klient->tableName." on ".$klient->tableName.".".$klient->tableId." = ".$db->tableName.".id_klient
        join ".$oddzial->tableName." on ".$oddzial->tableName.".".$oddzial->tableId." = ".$db->tableName.".id_oddzial
        join ".$decyzja->tableName." on ".$decyzja->tableName.".".$decyzja->tableId." = ".$db->tableName.".id_decyzja
        join bilety on bilety.id = ".$db->tableName.".id_bilet
        where ".$db->tableName.".".$db->tableId." = ".$wiersz['id'].";";
        $result = $controls->dalObj->pgQuery($zapytanie);
        $row = pg_fetch_array($result);
        // 2 gets for almost the same data, nonsense, but don't want to break anything
        $resultNew = $dalZatrudnienie->get($wiersz['id']);
        $rowNew = $resultNew[Model::RESULT_FIELD_DATA][0];

        echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'?'.ID_OSOBA.'='.$id_osoba.'" id="klient_wpw"><table>';
        echo $controls->AddSelectHelpHidden();
        echo $controls->AddHidden(ID_OSOBA, ID_OSOBA, $id_osoba);
        echo $controls->AddHidden(ID_ZATRUDNIENIE, ID_ZATRUDNIENIE, $row['id']);
        
        
        echo '<tr><td>Nazwisko klienta:</td><td>'.$wiersz1['nazwisko'].'</td></tr>';
        echo '<tr><td>Imiê klienta:</td><td>'.$wiersz1['imie'].'</td></tr>';
        
	    echo '<tr><td>Klient:</td><td nowrap><input type="text" value="'.$row[$klient_const_name].'" class="formfield" size="50" READONLY>
        </td></tr><tr><td>Data wyjazdu:</td><td nowrap>
        <input type="text" name="data_wyjazdu_wpw" id="data_wyjazdu_wpw" value="'.$row['data_wyjazdu'].'" class="formfield" readonly>
        </td></tr><tr><td>Ilo¶c tygodni:</td><td>';
        echo $controls->AddNumberbox("ilosc_tyg_wpw", "tygodnie", $row['ilosc_tyg'],  2, 3, "sprawdz_tygodnie(this)");
        echo '</td></tr><tr><td>Data powrotu:</td><td>';
        echo $controls->AddDatebox("data_powrotu_wpw", "data_powrotu_wpw", $row['data_powrotu'], 10, 10);
        echo strftime('%A', strtotime($row['data_powrotu']));
        echo '</td></tr><tr><td>Decyzja:</td><td>'.$row['decyzja'].'</td></tr>';
	    echo '<tr><td>Status:</td><td>';
        $query = 'select '.$status->tableId.', nazwa from '.$status->tableName.' where id in ('.ID_STATUS_AKTYWNY.', '.ID_STATUS_NIEODPOWIEDNI.', '.ID_STATUS_PASYWNY.') order by nazwa asc;';
        
        $styleReason = $row['id_status'] == ID_STATUS_NIEODPOWIEDNI ? '' : 'display: none;';
        
        echo $controls->AddSelectRandomQuerySVbyId("id_status_wpw", "id_status_wpw", "", $query, $row['id_status'], 
        	"status_wpw_id", "nazwa", "id", 
        	"employmentHistory.addUnsuitableReasonHtml( 
        		'wierszNieodpowiedni', 
        		status_wpw_id.value, 
        		".ID_STATUS_NIEODPOWIEDNI.");");

        echo '</td></tr>
        <tr id="wierszNieodpowiedni" style="'.$styleReason.'"><td>Powód:</td><td>'.
        $htmlControls->_AddTextarea('nieodpowiedni_powod', 'id_nieodpowiedni_powod', '', 1000, 5, 50, '')
        .'</td></tr><tr><td>Przewo¼nik:</td><td>';
        
        $przewoznik_id = !empty($rowNew[Model::COLUMN_RJA_ID_PRZEWOZNIK_WYJAZD]) ? $rowNew[Model::COLUMN_RJA_ID_PRZEWOZNIK_WYJAZD] : null;
        $przewoznik_powrot_id = !empty($rowNew[Model::COLUMN_RJA_ID_PRZEWOZNIK_POWROT]) ? $rowNew[Model::COLUMN_RJA_ID_PRZEWOZNIK_POWROT] : null;
        $rozklad_jazdy_id = !empty($rowNew[Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_WYJAZD]) ? $rowNew[Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_WYJAZD] : null;
        $rozklad_jazdy_powrot_id = !empty($rowNew[Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_POWROT]) ? $rowNew[Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_POWROT] : null;
                        
        echo $controls->AddSelectRandomQuerySVbyId('przewoznik', 'id_przewoznik_wpw', '', 
            "select null as id, '--------' as nazwa union select id, nazwa from przewoznik order by nazwa asc;", 
            $przewoznik_id, 'przewoznik_id_wpw', 'nazwa', 'id', 
            'getDepartureCityCombo(utils.getElementValue(\'data_wyjazdu_wpw\'), utils.getElementValue(\'przewoznik_id_wpw\'), \'msc_odjazd_wpw_container\', \'wpw\', \'msc_odjazd\', \'id_msc_odjazd\', 0);
            getTicketsCombo(utils.getElementValue(\'przewoznik_id_wpw\'), \'wpw\', \'bilet_wpw_container\');');
        
        //przy wrzucaniu na pasywnego nie ma mowy o decyzji, wszyscy sa umowieni, bo to nie dodawanie do wakatu 
        echo '</td></tr></td></tr><tr><td>Miejscowo¶æ odjazdu:</td><td id="msc_odjazd_wpw_container">';
        // TODO !!Data powrotu ???? ln 123
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
        
            echo $controls->AddSelectRandomQuerySVbyId('rozklad_jazdy_wpw', 'id_rozklad_jazdy_wpw', '', $query, $rozklad_jazdy_id, 'rozklad_jazdy_wpw_id', 'nazwa', 'id', 'utils.setHiddenFromSelectLabel(this, \'msc_odjazd_wpw_id\');', 'id_msc_odjazdu', 'msc_odjazd_wpw_id');
        }
        
        
	    echo '</td></tr><tr><td>Rodzaj biletu:</td><td id="bilet_wpw_container">';
	    if ($przewoznik_id) {
	        
	        $query = 'select id, nazwa from bilety where id_przewoznik = '.$przewoznik_id.' order by nazwa asc;';
	        echo $controls->AddSelectRandomQuerySVbyId("id_bilet_wpw", "id_bilet_wpw", "", $query, $row['id_bilet'], "bilet_wpw_id", "nazwa", "id", "");
	    }
	    
	    echo '</td></tr><tr><td>Przewo¼nik powrót:</td><td>';
        echo $controls->AddSelectRandomQuerySVbyId('przewoznik_powrot', 'id_przewoznik_powrot_wpw', '', 
            "select null as id, '--------' as nazwa union select id, nazwa from przewoznik order by nazwa asc;", 
            $przewoznik_powrot_id, 'przewoznik_id_wpw', 'nazwa', 'id', 
            'getDepartureCityCombo(utils.getElementValue(\'data_wyjazdu_wpw\'), utils.getElementValue(\'przewoznik_id_wpw\'), \'msc_powrot_wpw_container\', \'powrot_wpw\', \'msc_powrot\', \'id_msc_powrot\', 1);');
        
        //przy wrzucaniu na pasywnego nie ma mowy o decyzji, wszyscy sa umowieni, bo to nie dodawanie do wakatu 
        echo '</td></tr></td></tr><tr><td>Miejscowo¶æ powrotu:</td><td id="msc_powrot_wpw_container">';
        // TODO !!Data powrotu ???? 
        if($rozklad_jazdy_powrot_id)
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
        
            echo $controls->AddSelectRandomQuerySVbyId('rozklad_jazdy_wpw', 'id_rozklad_jazdy_wpw', '', $query, $rozklad_jazdy_powrot_id, 'rozklad_jazdy_powrot_wpw_id', 'nazwa', 'id', 'utils.setHiddenFromSelectLabel(this, \'msc_odjazd_wpw_id\');', 'id_msc_powrotu', 'msc_powrot_wpw_id');
        }
	    
        echo '</td></tr><tr><td>Miejsce docelowe:</td><td id="msc_docelowe_container">';
        echo '</td></tr><tr><td>Osoba kontaktowa:</td><td id="osoba_kontaktowa_container">';
	    echo '</td></tr><tr><td>';
        echo $controls->AddSubmit($update, $row['id'], "Aktualizuj.", "onclick=\"utils.setHiddenOnLoad('destination_wysw_po_wyjezdzie,contactPerson_wysw_po_wyjezdzie', 'id_destination_wysw_po_wyjezdzie,id_contactPerson_wysw_po_wyjezdzie');\"");
        echo '</td>';
        
	    if ($wiersz[$status_const_name] != $status_const_akt)
	    {
		    echo '<td>';
            echo $controls->AddSubmit($delete, $row['id'], "Usuñ.", '');
            echo '</td>';
	    }

	    echo '</tr></table></form>';

        $msc_docelowe_id = ((int)$row['id_miejsca_docelowe']) > 0 ? $row['id_miejsca_docelowe'] : 0;
        $osoba_kontaktowa_id = ((int)$row['id_osoby_kontaktowe']) > 0 ? $row['id_osoby_kontaktowe']: 0;
        echo "\n\n\n<script>"
                    . "employmentHistory.getDestinations(".$row['id_oddzial'].", '#klient_wpw #msc_docelowe_container', ".$msc_docelowe_id.", 'wysw_po_wyjezdzie');"
                    . "employmentHistory.getContactPersons(".$row['id_oddzial'].", '#klient_wpw #osoba_kontaktowa_container', ".$osoba_kontaktowa_id.", 'wysw_po_wyjezdzie');"
                    
            . "</script>\n\n";
    }
