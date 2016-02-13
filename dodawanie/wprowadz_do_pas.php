<?php
    require_once '../conf.php';
    CommonUtils::outputBufferingOn();
    CommonUtils::SessionStart();

    echo '<html>';
    HelpersUI::addHtmlBasicHeaders(HelpersUI::PATH_LEVEL_1);
    echo '<body>';
    
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
        include_once("../prawa_strona/f_image_operations.php");
        include_once("../dal/klient.php");
        include_once("../oblicz_date.php");
        require_once 'dal/DALZatrudnienie.php';
        require_once 'adl/User.php';
        require_once 'ui/HtmlControls.php';
        
        $db = new zatrudnienie();
        $klient = new klient();
        $oddzial = new oddzial();
        
        $controls = unserialize($_SESSION['controls']);
        $htmlControls = new HtmlControls();
        $id_osoba = Utils::PodajIdOsoba();
        
        $hidden_id = 'id_osoba';
        $wakat_const_pasywny = 1; 
        $insert = 'wprowadz';
        
        if (isset($_POST[$insert]))
        {
            $zapytanie = "select id from ".$db->tableName." where id_osoba = ".$_POST[$hidden_id]." and id_oddzial = ".$_POST['oddzial_id'].";";
            $resulttest = $controls->dalObj->pgQuery($zapytanie);
            if (pg_num_rows($resulttest) == 0)
            {
                $dalZatrudnienie = new DALZatrudnienie();
                $user = User::getInstance();
                
                $data = array(
                    Model::COLUMN_ZTR_ID_OSOBA         => (int)$_POST[ID_OSOBA],
                    Model::COLUMN_ZTR_ID_KLIENT        => (int)$_POST['klient_id'],
                    Model::COLUMN_ZTR_ID_ODDZIAL       => (int)$_POST['oddzial_id'],
                    Model::COLUMN_ZTR_ID_WAKAT         => (int)$wakat_const_pasywny,
                    Model::COLUMN_ZTR_ID_STATUS        => (int)ID_STATUS_PASYWNY,
                    Model::COLUMN_ZTR_DATA_WYJAZDU     => '2006-01-01',
                    Model::COLUMN_ZTR_ILOSC_TYG        => '6',
                    Model::COLUMN_ZTR_DATA_POWROTU     => '2006-02-14',
                    Model::COLUMN_ZTR_ID_DECYZJA       => ID_DECYZJA_UMOWIONY,
                    Model::COLUMN_ZTR_ID_MSC_ODJAZD    => ID_WLASNY_TRANSPORT,
                    Model::COLUMN_ZTR_ID_BILET         => ID_BRAK_BILET_PRZEWOZNIK,
                    Model::COLUMN_ZTR_ID_PRACOWNIK     => (int)$user->getUserId(),
                    Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_WYJAZD => null,
                );
                    
                $dalZatrudnienie->set($data);
                //echo $insert;
                echo "<script>window.close();</script>";
            }
            else
            {
                echo "Ta osoba jest pasywna u tego klienta.";
            }
        }
               
        $query = "select distinct ".$oddzial->tableName.".".$oddzial->tableId.", ".$oddzial->tableName.".id_klient, 
        ".$klient->tableName.".nazwa_alt || ', ' || ".$oddzial->tableName.".nazwa as nazwa
        from ".$oddzial->tableName."
        join ".$klient->tableName." on ".$oddzial->tableName.".id_klient = ".$klient->tableName.".".$klient->tableId." 
        where ".$oddzial->tableName.".id not in (select distinct id_oddzial from zatrudnienie where id_osoba = ".$id_osoba.") 
        order by nazwa asc;";
        
        $vacatsList = $controls->dalObj->PobierzDane($query);
	    
	    $addsList = array(
            'klient_id' => 'id_klient',
	    	'oddzial_id' => 'id_oddzial',
        );

        echo '<table><form method="POST" action="'.$_SERVER['PHP_SELF'].'"><tr><td>';
        echo $controls->AddSelectHelpHidden();
        echo $controls->AddHidden(ID_OSOBA, ID_OSOBA, $id_osoba);
        echo $htmlControls->_AddSelect("klient", "klient", $vacatsList, null, "oddzial_id", true, '', '', '', 
            $addsList, "");
            
        //echo $controls->AddSelectRandomQuery("lista_kl", "lista_kl", "", $query, "", "lista_kl_id", "klient", $oddzial->tableId, "");
        echo '</td><td>';
        echo $controls->AddSubmit($insert, $id_osoba, "Wprowad¼.", '');
        echo '</td></tr>';
        echo '</form></table>';
    }
    CommonUtils::sendOutputBuffer();
?>
</html>
