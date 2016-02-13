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
        $hidden_id = 'id_firma';
        //dal object inside those controls handles db connection

        //bussines logic layer classes responsible for table name and primary key info
        $db = new firma();
                
        if (isset($_POST['update_firma']))
        {
            $testQuery = "select id from ".$db->tableName." where ".$db->tableName.".".$db->tableId." != ".$_POST[$hidden_id]." 
            and lower(nazwa) = lower('".$_POST['firma']."');";
            $result = $controls->dalObj->pgQuery($testQuery);
            if (pg_num_rows($result) == 0)
            {
                //for update of a referencing collumns we use id's that are hidden in hiddens :P
                $query = "update ".$db->tableName." set nazwa = '".$_POST['firma']."'
                where ".$db->tableName.".".$db->tableId." = ".$_POST[$hidden_id].";";
                $result = $controls->dalObj->pgQuery($query);
            }
            else
            {
                echo 'Kolizja w nazwie firmy.';
            }
        }
        if (isset($_POST['edit_firma']))
        {
            $query = "select ".$db->tableName.".".$db->tableId." as id,".$db->tableName.".nazwa
            from ".$db->tableName."
            where ".$db->tableName.".".$db->tableId." = ".$_POST[$hidden_id].";";
            $result = $controls->dalObj->pgQuery($query);
            $row = pg_fetch_array($result);
            echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST"><table>';
            echo $controls->AddHidden('$hidden_id', $hidden_id, ''); 
            echo $controls->AddSelectHelpHidden();
            echo '<tr><td>Firma:</td><td>';
            echo $controls->AddTextbox("firma", "firma", $row['nazwa'], 25, 25, "onChange='sprawdz_klienta(this);'");
            echo '</td></tr><tr><td>';
            echo $controls->AddPopUpButton("Miejscowo¶ci", "miejscowosci", "msc_biuro.php?id=".$row['id']."", 800, 700);
            
            echo '</td></tr><tr><td>';
            echo $controls->AddSubmit("update_firma", $row['id'], "Aktualizuj.", "onClick='".$hidden_id.".value=this.id;'");
            echo '</td></tr></table></form>';
        }
        if (isset($_POST['erase_firma']))
        {
            $query = "delete from ".$db->tableName." where ".$db->tableName.".".$db->tableId." = ".$_POST[$hidden_id].";";
            $result = $controls->dalObj->pgQuery($query);
        }
        if (!isset($_POST['edit_firma']))
        {
            $query = "select ".$db->tableName.".".$db->tableId." as id,".$db->tableName.".nazwa from ".$db->tableName.";";

            //stara architektura - jak dotad nie ma lepsiejszego sposobu, przewiduje, ze klient no 1 jest systemowy
            //dodatkowo wakat no 1 tez jestsystemowy :P
            
            $result = $controls->dalObj->pgQuery($query);
            $ile = pg_num_rows($result);
            
            echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST"><table class="gridTable" border="0" cellspacing="0">';
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
            $odlamki_nag = explode(",","Id, Nazwa");
            setHeadingRow($odlamki_nag);
            echo '</tr>';
            while ($wiersz = pg_fetch_assoc($result))
            {
                 echo "<tr class='oddRow'>";
                 if (isset($_SESSION['edycja_rekordu']))
                {
                    echo '<td nowrap align = "CENTER">';
                    echo $controls->AddTableSubmit("edit_firma", $wiersz['id'], "Edytuj.", "onClick='".$hidden_id.".value=this.id;'");
                    echo '</td>';
                }
                //przycisk kasowania, zasada okreslania id osoby jak powyzej
                if (isset($_SESSION['kasowanie_rekordu']))
                {
                    echo '<td nowrap align = "CENTER">';
                    echo $controls->AddTableSubmit("erase_firma", $wiersz['id'], "Kasuj.", "onClick='".$hidden_id.".value=this.id;'");
                    
                    echo '</td>';
                }
                 addRowsToTable($wiersz);
                 echo '</tr>';
            }  
            echo '</table></form>';
        }
    }
    CommonUtils::sendOutputBuffer();
?>
</body>
</html>