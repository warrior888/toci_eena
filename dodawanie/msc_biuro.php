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

        //object of a class responsible for controls like number, text, date, etc
        $controls = unserialize($_SESSION['controls']);
        $hidden_id = 'id_miejscowosc';
        //dal object inside those controls handles db connection
        //bussines logic layer classes responsible for table name and primary key info
        $db = new miejscowoscBiuro();
        $firma = new firma();
        $msc = new mscBiuro();
        //remeber the firm id
        if (isset($_GET['id']))
        {
            $_SESSION['obs_firma_id'] = $_GET['id'];
        }
        //todo - define adresy biur site, add, edit, delete
        //no update
        if (isset($_POST['update_miejscowosc']))
        {
            $testQuery = "select id from ".$db->tableName." where ".$db->tableName.".".$db->tableId." != ".$_POST[$hidden_id]."
            and id_firma = ".$_SESSION['obs_firma_id']." 
            and id_msc_biuro = ".$_POST['miejscowosc_id'].";";
            
            $result = $controls->dalObj->pgQuery($testQuery);
            if (pg_num_rows($result) == 0)
            {
                //for update of a referencing collumns we use id's that are hidden in hiddens :P
                $query = "update ".$db->tableName." set id_msc_biuro = ".$_POST['miejscowosc_id']."
                where ".$db->tableName.".".$db->tableId." = ".$_POST[$hidden_id].";";
                $result = $controls->dalObj->pgQuery($query);
            }
            else
            {
                echo 'Kolizja w nazwie miejscowo¶ci.';
            }
        }
        if (isset($_POST['edit_miejscowosc']))
        {
            $query = "select ".$db->tableName.".".$db->tableId." as id,".$msc->tableName.".nazwa as msc 
            from ".$db->tableName."
            join ".$msc->tableName." on ".$db->tableName.".id_msc_biuro = ".$msc->tableName.".".$msc->tableId."
            where ".$db->tableName.".".$db->tableId." = ".$_POST[$hidden_id]." order by ".$msc->tableName.".nazwa asc;";          
            $result = $controls->dalObj->pgQuery($query);
            $row = pg_fetch_array($result);
            
            echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST"><table>';
            echo $controls->AddHidden('$hidden_id', $hidden_id, '');
            echo '<tr><td>Miejscowo¶æ:</td><td>';
            $query = "select ".$msc->tableId.", nazwa from ".$msc->tableName." order by nazwa asc;";
            echo $controls->AddSelectRandomQuery("miejscowosc", "miejscowosc", "", $query, $row['msc'], "miejscowosc_id", "nazwa", $msc->tableId, "");
            
            echo '</td></tr><tr><td>';
            echo $controls->AddPopUpButton("Adresy Biur", "adresy", "adres_biuro_msc.php?id=".$_POST[$hidden_id]."", 500, 500);
            echo '</td></tr><tr><td>';
            echo $controls->AddPopUpButton("Miejsca docelowe", "miejsca", "miejsca_docelowe.php?id=".$_POST[$hidden_id]."", 500, 500);
            echo '</td></tr><tr><td>';
            echo $controls->AddPopUpButton("Osoby kontaktowe", "osoby", "osoby_kontaktowe.php?id=".$_POST[$hidden_id]."", 500, 500);
            echo '</td></tr><tr><td>';
            echo $controls->AddSubmit("update_miejscowosc", $row['id'], "Aktualizuj.", "onClick='".$hidden_id.".value=this.id; utils.setHiddenOnLoad(\"miejscowosc_id\", \"miejscowosc\");'");  //"onclick=''"
            echo '</td></tr></table></form>';
        }
        if (isset($_POST['erase_miejscowosc']))
        {
            $query = "delete from ".$db->tableName." where ".$db->tableName.".".$db->tableId." = ".$_POST[$hidden_id].";";
            $result = $controls->dalObj->pgQuery($query);
        }
        if (isset($_POST['insert_miejscowosc']))
        {
            $testQuery = "select id from ".$db->tableName." where 
            id_firma = ".$_SESSION['obs_firma_id']." 
            and id_msc_biuro = ".$_POST['miejscowosc_id'].";";

            $result = $controls->dalObj->pgQuery($testQuery);
            if (pg_num_rows($result) == 0)
            {
                $query = "INSERT into ".$db->tableName." values (nextval('".$db->tableName."_".$db->tableId."_seq'), 
                ".$_SESSION['obs_firma_id'].", ".$_POST['miejscowosc_id'].");";
                $result = $controls->dalObj->pgQuery($query);
                $query = "select currval('".$db->tableName."_".$db->tableId."_seq');";
                $wynik = $controls->dalObj->pgQuery($query);
                $wiersz = pg_fetch_array($wynik);
                echo "Miejscowo¶æ zosta³a zapisana w systemie pod numerem ".$wiersz[0].".";
            }
            else
            {
                echo 'Kolizja w nazwie miejscowo¶ci.';
            }
        }
        
        //display
        //possibly is gonna be put into if
        if (!isset($_POST['edit_miejscowosc']))
        {
            $query = "select ".$db->tableName.".".$db->tableId." as id,".$msc->tableName.".nazwa as msc,".$firma->tableName.".nazwa
            from ".$db->tableName." 
            join ".$firma->tableName." on ".$db->tableName.".id_firma = ".$firma->tableName.".".$firma->tableId."
            join ".$msc->tableName." on ".$db->tableName.".id_msc_biuro = ".$msc->tableName.".".$msc->tableId."
            where ".$db->tableName.".id_firma = ".$_SESSION['obs_firma_id']." order by ".$msc->tableName.".nazwa asc;";
            
            //stara architektura - jak dotad nie ma lepsiejszego sposobu, przewiduje, ze klient no 1 jest systemowy
            //dodatkowo wakat no 1 tez jestsystemowy :P

            $result = $controls->dalObj->pgQuery($query);
            $ile = pg_num_rows($result);
            
            echo "<form action='".$_SERVER['PHP_SELF']."' method='POST'><table class='gridTable' border='0' cellspacing='0'>";
            echo valControl::_RowsCount($ile);
            echo $controls->AddHidden($hidden_id, $hidden_id, '');
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
            $odlamki_nag = explode(",","Id, Miejscowo¶æ, Firma");
            setHeadingRow($odlamki_nag);
            echo '</tr>';
            $count = 0;
            while ($wiersz = pg_fetch_assoc($result))
            {
                $count++;
                $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
                echo "<tr class='".$css."'>";
                if (isset($_SESSION['edycja_rekordu']))
                {
                    echo '<td nowrap align="CENTER">';
                    echo $controls->AddTableSubmit("edit_miejscowosc", $wiersz['id'], "Edytuj.", "onClick='".$hidden_id.".value=this.id;'");
                    echo '</td>';
                }
                //przycisk kasowania, zasada okreslania id osoby jak powyzej
                if (isset($_SESSION['kasowanie_rekordu']))
                {
                    echo '<td nowrap align="CENTER">';
                    echo $controls->AddTableSubmit("erase_miejscowosc", $wiersz['id'], "Kasuj.", "onClick='".$hidden_id.".value=this.id;'");
                    echo '</td>';
                }
                addRowsToTable($wiersz);
                echo '</tr>';
            }  
            echo '</table></form>';  
        
            echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST"><table>';
            echo $controls->AddSelectHelpHidden();
            echo '<tr><td>Miejscowo¶æ:</td><td>';
            $query = "select ".$msc->tableId.", nazwa from ".$msc->tableName." order by nazwa asc;";
            echo $controls->AddSelectRandomQuery("miejscowosc", "miejscowosc", "", $query, "", "miejscowosc_id", "nazwa", $msc->tableId, "");
            echo '</td></tr><tr><td>';
            echo $controls->AddSubmit("insert_miejscowosc", "insert_klient", "Dodaj.", "onclick='utils.setHiddenOnLoad(\"miejscowosc_id\", \"miejscowosc\");'");
            echo '</td></tr></table></form>';
        }
        //add a logic here
        //add grid for existing office places and adding new one, provide addresses button, with addresses the same:
        //grid with known, and new ones option, erase possible, that's all
        
        //create class diagram
        
        //biuro has n places, places has n addresses, after place add address add should be available
    }
    CommonUtils::sendOutputBuffer();
?>
</body>
</html>
