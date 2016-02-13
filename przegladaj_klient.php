<?php
    require_once 'conf.php';
    CommonUtils::outputBufferingOn();
    CommonUtils::SessionStart();

    echo '<html>';
    HelpersUI::addHtmlBasicHeaders();
    echo '<body>';
    
    if (empty($_SESSION['uzytkownik']))
    {
        require("log_in.php");
    }
    else //if (isset($_SESSION['zmiana_uprawnien']))
    {
        include("prawa_strona/f_image_operations.php");
        include("dal/klient.php");
        require_once 'dal/DALKlient.php';
        //object of a class responsible for controls like number, text, date, etc
        $controls = new valControl();
        //bussines logic layer classes responsible for table name and primary key info
        $db = new klient();
        $refTabP = new panstwo();
        $refTabF = new firma();
        
        if (isset($_POST['update_klient']))
        {
            //we find out if there is another record than the one of a client being updated, that would posses the
            //same name or short name
            $testQuery = "select id from ".$db->tableName." where ".$db->tableName.".".$db->tableId." != ".$_POST['id_kl']." 
            and (lower(nazwa) = lower('".$_POST['klient']."') or lower(nazwa_alt) = lower('".$_POST['klient_alt']."'));";
            $result = $controls->dalObj->pgQuery($testQuery);
            if (pg_num_rows($result) == 0)
            {
                //for update of a referencing collumns we use id's that are hidden in hiddens :P
                $dal = new DALKlient();
                
                $updateTable = array(
                    Model::COLUMN_KLN_ID                => $_POST['id_kl'],
                    Model::COLUMN_KLN_ADRES             => $_POST['adres'],
                    Model::COLUMN_KLN_ID_FIRMA          => $_POST['firma_id'],
                    Model::COLUMN_KLN_ID_PANSTWO_EGZ    => $_POST['panstwo_egz_id'],
                    Model::COLUMN_KLN_ID_PANSTWO_POS    => $_POST['panstwo_pos_id'],
                    Model::COLUMN_KLN_NAZWA             => $_POST['klient'],
                    Model::COLUMN_KLN_NAZWA_ALT         => $_POST['klient_alt'],
                );
                
                $result = $dal->set($updateTable);
            }
            else
            {
                echo 'Kolizja w nazwie klienta lub w nazwie skróconej klienta.';
            }
        }
        if (isset($_POST['edit_klient']))
        {
            $query = "select ".$db->tableName.".".$db->tableId." as id,".$db->tableName.".nazwa as nazwa,".$db->tableName.".nazwa_alt as nazwa_alt,
            p1.nazwa as panstwo_egz,p2.nazwa as panstwo_pos,".$db->tableName.".id_panstwo_egz,".$db->tableName.".id_panstwo_pos,
            ".$db->tableName.".id_firma, ".$db->tableName.".adres, ".$refTabF->tableName.".nazwa as firma from ".$db->tableName."
            join ".$refTabP->tableName." p1 on p1.".$refTabP->tableId." = ".$db->tableName.".id_panstwo_egz
            join ".$refTabP->tableName." p2 on p2.".$refTabP->tableId." = ".$db->tableName.".id_panstwo_pos
            join ".$refTabF->tableName." on ".$refTabF->tableName.".".$refTabF->tableId." = ".$db->tableName.".id_firma
            where ".$db->tableName.".".$db->tableId." = ".$_POST['id_kl'].";";
            $result = $controls->dalObj->pgQuery($query);
            $row = pg_fetch_array($result);
            echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST"><table>';
            echo $controls->AddHidden('id_kl', 'id_kl', '');
            echo $controls->AddSelectHelpHidden();
            echo '<tr><td>Klient - pe³na nazwa:</td><td>';
            echo $controls->AddTextbox("klient", "klient", $row['nazwa'], 45, 45, "onChange='sprawdz_klienta(this);'");
            echo '</td><tr><td>Klient - skrót:</td><td>';
            echo $controls->AddTextbox("klient_alt", "klient_alt", $row['nazwa_alt'], 30, 30, "onChange='sprawdz_klienta(this);'"); 
            echo '</td></tr><tr><td>Pañstwo - lokalizacja klienta:</td><td>';
            echo $controls->AddSelectRandomQuery("id_panstwo_egz", "id_panstwo_egz", "", "select id, nazwa from panstwo order by nazwa asc;", $row['panstwo_egz'], "panstwo_egz_id", "nazwa", "id", "");
            echo '</td></tr><tr><td>Pañstwo - po¶rednictwo:</td><td>';
            echo $controls->AddSelectRandomQuery("id_panstwo_pos", "id_panstwo_pos", "", "select id, nazwa from panstwo order by nazwa asc;", $row['panstwo_pos'], "panstwo_pos_id", "nazwa", "id", "");
            echo '</td></tr><tr><td>Firma obs³uguj±ca:</td><td>';
            echo $controls->AddSelectRandomQuery("id_firma", "id_firma", "", "select id, nazwa from firma order by nazwa asc;", $row['firma'], "firma_id", "nazwa", "id", "");
            echo '</td></tr><tr><td>Adres klienta:</td><td>';
            echo $controls->AddTextbox("adres", "adres", $row['adres'], 50, 50, "");
            echo '</td></tr><tr><td>';
            echo $controls->AddPopUpButton("Oddzia³y", "oddzialy", "dodawanie/oddzialy_klient.php?id=".$_POST['id_kl']."", 800, 700);
            echo '</td></tr><tr><td>';
            echo $controls->AddSubmit("update_klient", $row['id'], "Aktualizuj.", "onClick='ClientRequiredFields(klient, klient_alt); id_kl.value=this.id;'");
            echo '</td></tr></table></form>';
        }
        if (isset($_POST['erase_klient']))
        {
            $query = "delete from ".$db->tableName." where ".$db->tableName.".".$db->tableId." = ".$_POST['id_kl'].";";
            $result = $controls->dalObj->pgQuery($query);
        }
        if (isset($_POST['seek_klient']))
        {
	        $query = "select ".$db->tableName.".".$db->tableId." as id,".$db->tableName.".nazwa,".$db->tableName.".nazwa_alt,p1.nazwa as kraj_praca,p2.nazwa as kraj_firma,
            ".$refTabF->tableName.".nazwa as firma from ".$db->tableName."
            join ".$refTabP->tableName." p1 on p1.".$refTabP->tableId." = ".$db->tableName.".id_panstwo_egz
            join ".$refTabP->tableName." p2 on p2.".$refTabP->tableId." = ".$db->tableName.".id_panstwo_pos
            join ".$refTabF->tableName." on ".$refTabF->tableName.".".$refTabF->tableId." = ".$db->tableName.".id_firma
            where ".$db->tableName.".".$db->tableId." != 1 
            and lower(".$db->tableName.".nazwa) like lower('%".$_POST['klient']."%') order by ".$db->tableName.".nazwa asc;";
            //stara architektura - jak dotad nie ma lepsiejszego sposobu, przewiduje, ze klient no 1 jest systemowy
            //dodatkowo wakat no 1 tez jestsystemowy :P
            
            $result = $controls->dalObj->pgQuery($query);
            $ile = pg_num_rows($result);
            
            echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST"><table class="gridTable" border="0" cellspacing="0">';
            echo valControl::_RowsCount($ile);
            echo $controls->AddHidden('id_kl', 'id_kl', '');
            echo '<tr>';
            if (isset($_SESSION['edycja_rekordu']))
            {
                echo "<th>Edycja</th>";
            }
            //naglowek kasowania
            if (isset($_SESSION['kasowanie_rekordu']))
            {
                echo "<th>Kasowanie</th>";
            }
            $odlamki_nag = explode(",","Id, Nazwa, Zagr. skrót, Pañstwo klienta, Pañstwo po¶rednictwa, Firma obs³uguj±ca");
            setHeadingRow($odlamki_nag);
            echo '</tr>';
            $count = 0;
            while ($wiersz = pg_fetch_assoc($result))
            {
                $count++;
                $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
                 echo '<tr class="'.$css.'">';
                 if (isset($_SESSION['edycja_rekordu']))
                {
                    echo '<td nowrap align="center">';
                    echo $controls->AddTableSubmit("edit_klient", $wiersz['id'], "Edytuj.", "onClick='id_kl.value=this.id;'");
                    echo '</td>';
                }
                //przycisk kasowania, zasada okreslania id osoby jak powyzej
                if (isset($_SESSION['kasowanie_rekordu']))
                {
                    echo '<td nowrap align="center">';
                    echo $controls->AddTableSubmit("erase_klient", $wiersz['id'], "Kasuj.", "onClick='id_kl.value=this.id;'");
                    
                    echo '</td>';
                }

                 addRowsToTable($wiersz);
                 echo '</tr>';
            }  
            echo '</table></form>';      
        }

        if (!isset($_POST['edit_klient']))
        {
            echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST"><table>';
            echo '<tr><td>Klient:</td><td>';
            echo $controls->AddSeekTextbox("klient", "", "klient", 25, 25);
            echo '</td><td>';
            echo $controls->AddSubmit("seek_klient", "seek_klient", "Szukaj.", "");
            echo '</td></tr></table></form>';
            
            echo '<form action="przegladaj_warunki.php" method="POST"><table>';
            echo '<tr><td>';
            echo $controls->AddSubmit("warunki", "warunki", "Warunki zatrudnienia.", "");
            echo '</td></tr></table></form>';
        }
    }
    CommonUtils::sendOutputBuffer();
?>
</body>
</html>
