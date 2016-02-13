<?php
    require_once '../conf.php';
    CommonUtils::outputBufferingOn();
    CommonUtils::SessionStart();

    echo '<html>';
    HelpersUI::addHtmlBasicHeaders(HelpersUI::PATH_LEVEL_1);
    echo '<script src="../js/custom/employmentHistory.js"></script>';
    echo '<body>';
    
    include_once("../vaElClass.php");
    require_once 'dal/DALZatrudnienie.php';
    require_once 'adl/User.php';
    require_once 'ui/HtmlControls.php';
    
    $controls = new valControl();
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
        include_once("../prawa_strona/f_image_operations.php");
        include_once("../dal/klient.php");
        include_once("../oblicz_date.php");
        //do poprawy
 
        $zatrudnienieDb = new zatrudnienie();
        $klient = new klient();
        $oddzial = new oddzial();
        $wyodrMsc = new mscPowrotWyodr();
        
        $controls = unserialize($_SESSION['controls']);
        $htmlControls = new HtmlControls(); 
        
        $hidden_id = 'id_wakat';
        $wakat_const_pasywny = 1; 
        $update = 'aktualizuj';
        
        $id_osoba = Utils::PodajIdOsoba();
        $dalZatrudnienie = new DALZatrudnienie();
        
        if (isset($_POST[$update]))
        {
            $chosenStatus = (int)$_POST['status_wpw_id'];
            $idZatrudnienie = (int)$_POST[ID_ZATRUDNIENIE];
            $idMscOdjazd = (int)$_POST['msc_odjazd_wpw_id'];
            $isUnsuitableReasonRequired = false;
            
            if ($chosenStatus == ID_STATUS_NIEODPOWIEDNI)
            {
                $reason = $dalZatrudnienie->getUnsuitableReasonByEmpId($idZatrudnienie);
                
                $isUnsuitableReasonRequired = is_null($reason) ? !(strlen($_POST['nieodpowiedni_powod']) > 10) : false;
            }
            
            if (!$isUnsuitableReasonRequired)
            {
                $user = User::getInstance();
                        
                $data = array(
                    Model::COLUMN_ZTR_ID               => (int)$_POST[ID_ZATRUDNIENIE],
                    Model::COLUMN_ZTR_ID_STATUS        => $chosenStatus,
                    Model::COLUMN_ZTR_ILOSC_TYG        => (int)$_POST['ilosc_tyg_wpw'],
                    Model::COLUMN_ZTR_DATA_POWROTU     => $_POST['data_powrotu_wpw'],
                    Model::COLUMN_ZTR_ID_MSC_ODJAZD    => $idMscOdjazd,
                    //Model::COLUMN_ZTR_ID_BILET         => $idBilet,
                    //Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY => $idRozkladJazdy,
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
                        
                //$query = "update ".$zatrudnienieDb->tableName." set id_status = ".$_POST['status_wpw_id'].", data_powrotu = '".$_POST['data_powrotu_wpw']."',
                //ilosc_tyg = '".$_POST['ilosc_tyg_wpw']."', id_msc_odjazd = ".$_POST['msc_odjazd_wpw_id']." where id = ".$_POST[$hidden_id].";";
                
                if (ColisionDetection($id_osoba, $_POST[$hidden_id], $_POST['data_wyjazdu_wpw'], $_POST['data_powrotu_wpw'], $zatrudnienieDb, $controls) == enum::$ALLOWDATA)
                {
                    if ($_POST['data_wyjazdu_wpw'] < $_POST['data_powrotu_wpw'])
                    {
                        $dalZatrudnienie->set($data);
                        TriggerLogic($query, $dzis, $_POST['data_wyjazdu_wpw'], $_POST['data_powrotu_wpw'], $_POST['status_wpw_id'], $id_osoba, $_POST['msc_odjazd_wpw_id'], $controls, $zatrudnienieDb, $wyodrMsc);
                        $result = $controls->dalObj->pgQuery($query);
                        echo 'Zaktualizowano osobê.<script>window.close();</script>';
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
                echo 'Brakuje powodu ustalenia statusu nieodpowiedniego, lub powód jest za krótki (min 11 znaków).';
            }
        }
        
	    $zapytanie = "select imiona.nazwa as imie, nazwisko from dane_osobowe join imiona on dane_osobowe.id_imie = imiona.id where dane_osobowe.id = '".$id_osoba."';";
	    $result = $controls->dalObj->pgQuery($zapytanie);
	    $wiersz1 = pg_fetch_array($result);
        $query = "select ".$zatrudnienieDb->tableName.".".$zatrudnienieDb->tableId.", ".$klient->tableName.".nazwa_alt || ', ' || ".$oddzial->tableName.".nazwa as klient,
        data_wyjazdu, data_powrotu, ilosc_tyg, uprawnienia.nazwa_uzytkownika as uzytkownik, 
        id_msc_odjazd, id_status from ".$zatrudnienieDb->tableName." 
        join ".$klient->tableName." on ".$klient->tableName.".".$klient->tableId." = ".$zatrudnienieDb->tableName.".id_klient 
        join ".$oddzial->tableName." on ".$oddzial->tableName.".".$oddzial->tableId." = ".$zatrudnienieDb->tableName.".id_oddzial
        join uprawnienia on uprawnienia.id = ".$zatrudnienieDb->tableName.".id_pracownik 
        where ".$zatrudnienieDb->tableName.".id_osoba = '".$id_osoba."' and data_wyjazdu != '2006-01-01' and data_powrotu < '".$dzis."' order by data_powrotu desc limit 1;";
        $QueryResult = $controls->dalObj->pgQuery($query);
        $wiersz = pg_fetch_array($QueryResult);
        if (pg_num_rows($QueryResult) > 0)
        {
	        echo '<table><form method="POST" action="'.$_SERVER['PHP_SELF'].'"><tr><td>Nazwisko klienta:</td><td>';
            echo $controls->AddSelectHelpHidden();
            echo $controls->AddHidden($hidden_id, $hidden_id, ''); 
            echo $controls->AddHidden(ID_OSOBA, ID_OSOBA, $id_osoba);
            echo $controls->AddHidden(ID_ZATRUDNIENIE, ID_ZATRUDNIENIE, $wiersz[$zatrudnienieDb->tableId]);
            echo $wiersz1['nazwisko'];
            echo '</td></tr><tr><td>Imiê klienta:</td><td>';
            echo $wiersz1['imie'];
            echo '</td></tr><tr><td>Klient:</td><td>';
            echo $controls->AddTextbox('klient_wpw', 'id_klient_wpw', $wiersz['klient'], '20', '50', 'readonly'); 
            echo '</td></tr><tr><td>Data wyjazdu:</td><td>';
            echo $controls->AddTextbox('data_wyjazdu_wpw', 'id_data_wyjazdu_wpw', $wiersz['data_wyjazdu'], '20', '10', 'readonly');
            echo '</td></tr><tr><td>Ilo¶c tygodni:</td><td>';
            echo $controls->AddNumberbox("ilosc_tyg_wpw", "tygodnie", $wiersz['ilosc_tyg'],  2, 3, "sprawdz_tygodnie(this)");
            echo '</td></tr><tr><td>Data powrotu:</td><td>';
            echo $controls->AddDatebox("data_powrotu_wpw", "data_powrotu_wpw", $wiersz['data_powrotu'], 10, 10);
            echo '</td></tr><tr><td>Status:</td><td>';
            $query = 'select id, nazwa from status where id in ('.ID_STATUS_AKTYWNY.', '.ID_STATUS_NIEODPOWIEDNI.', '.ID_STATUS_PASYWNY.') order by nazwa asc;';
            
            $styleReason = $wiersz['id_status'] == ID_STATUS_NIEODPOWIEDNI ? '' : 'display: none;';
        
            echo $controls->AddSelectRandomQuerySVbyId("id_status_wpw", "id_status_wpw", "", $query, $wiersz['id_status'], 
            	"status_wpw_id", "nazwa", "id", 
            	"employmentHistory.addUnsuitableReasonHtml( 
            		'wierszNieodpowiedni', 
            		status_wpw_id.value, 
            		".ID_STATUS_NIEODPOWIEDNI.");");
    
            echo '</td></tr>
            <tr id="wierszNieodpowiedni" style="'.$styleReason.'"><td>Powód:</td><td>'.
            $htmlControls->_AddTextarea('nieodpowiedni_powod', 'id_nieodpowiedni_powod', '', 1000, 5, 50, '');
            //.'</td></tr><tr><td>Przewo¼nik:</td><td>';
            
            //echo $controls->AddSelectRandomQuerySVbyId("id_status_wpw", "status_wpw", "", $query, $wiersz['id_status'], "status_wpw_id", "nazwa", "id", "");
	        echo '</td></tr><tr><td>Miejscowo¶æ odjazdu:</td><td>';
            $query = "select id, nazwa from msc_odjazdu order by nazwa asc;";
            echo $controls->AddSelectRandomQuerySVbyId("id_msc_odjazd_wpw", "id_msc_odjazd_wpw", "", $query, $wiersz['id_msc_odjazd'], "msc_odjazd_wpw_id", "nazwa", "id", "");
	        echo '</td></tr><tr><td>';
            echo $controls->AddSubmit($update, $wiersz['id'], "Aktualizuj.", "onclick='".$hidden_id.".value = this.id;'");
            echo '</td></tr></form></table>';
	    }
        else
        {
            echo "Ta osoba nie ma wakatu, który móg³by zostaæ objêty t± edycj±.";
        }
    }
    CommonUtils::sendOutputBuffer();
?>
