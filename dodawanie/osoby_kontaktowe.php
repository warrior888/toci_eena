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
        $hidden_id = 'id_adres';

        //remeber the firm id
        if (isset($_GET['id']))
        {
            $_SESSION['adr_msc_id'] = $_GET['id'];
        }
        if (isset($_POST['update_adres']))
        {
            $osoba = $_POST['osoba'];
            $testQuery = "select id from osoby_kontaktowe where osoby_kontaktowe.id != ".$_POST[$hidden_id]."
            and id_miejscowosc_biuro = ".$_SESSION['adr_msc_id']." 
            and lower(osoba) = lower('".$osoba."');";
            
            $result = $controls->dalObj->pgQuery($testQuery);
            if (pg_num_rows($result) == 0)
            {
                //for update of a referencing collumns we use id's that are hidden in hiddens :P
                $query = "update osoby_kontaktowe set osoba = '".$osoba."'
                where osoby_kontaktowe.id = ".(int)$_POST[$hidden_id].";";
                $result = $controls->dalObj->pgQuery($query);
            }
            else
            {
                echo 'Kolizja w adresie.';
            }
        }
        
        if (isset($_POST['edit_adres']))
        {
            $query = "select osoby_kontaktowe.id as id, osoby_kontaktowe.osoba 
            from osoby_kontaktowe
            where osoby_kontaktowe.id = ".(int)$_POST[$hidden_id]." order by osoby_kontaktowe.osoba asc;";          
            $result = $controls->dalObj->pgQuery($query);
            $row = pg_fetch_array($result);
            
            $osoba = $row['osoba'];
            
            echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST"><table>';
            echo $controls->AddHidden($hidden_id, $hidden_id, $_POST[$hidden_id]);
            echo '<tr><td>Osoba kontaktowa:</td><td>';
            echo $controls->AddTextbox("osoba", "osoba", $osoba, 45, 45, "");
            echo '</td></tr><tr><td>';
            echo $controls->AddSubmit("update_adres", $row['id'], "Aktualizuj.", "onClick='".$hidden_id.".value=this.id;'");
            echo '</td></tr></table></form>';
        }
        
        if (isset($_POST['erase_adres']))
        {
            $query = "delete from osoby_kontaktowe where osoby_kontaktowe.id = ".$_POST[$hidden_id].";";
            $result = $controls->dalObj->pgQuery($query);
        }
        
        if (isset($_POST['insert_adres']))
        {
            $osoba = $_POST['osoba'];
            $testQuery = "select id from osoby_kontaktowe where 
            id_miejscowosc_biuro = ".$_SESSION['adr_msc_id']." 
            and lower(osoba) = lower('".$osoba."');";
                        
            $result = $controls->dalObj->pgQuery($testQuery);
            if (pg_num_rows($result) == 0)
            {
                $query = "INSERT into osoby_kontaktowe values (nextval('osoby_kontaktowe_id_seq'), 
                ".$_SESSION['adr_msc_id'].", '".$_POST['osoba']."');";

                $result = $controls->dalObj->pgQuery($query);
                $query = "select currval('osoby_kontaktowe_id_seq');";
                $wynik = $controls->dalObj->pgQuery($query);
                $wiersz = pg_fetch_array($wynik);
                echo "Miejsce docelowe zosta³o zapisane w systemie pod numerem ".$wiersz[0].".";
            }
            else
            {
                echo 'Kolizja w nazwie.';
            }
        }
        
        //display
        //possibly is gonna be put into if
        if (!isset($_POST['edit_adres']))
        {
            $query = "select osoby_kontaktowe.id as id, osoby_kontaktowe.osoba
            from osoby_kontaktowe
            join miejscowosc_biuro on osoby_kontaktowe.id_miejscowosc_biuro = miejscowosc_biuro.id
            where osoby_kontaktowe.id_miejscowosc_biuro = ".$_SESSION['adr_msc_id']." order by osoby_kontaktowe.osoba asc;";
            
            //stara architektura - jak dotad nie ma lepsiejszego sposobu, przewiduje, ze klient no 1 jest systemowy
            //dodatkowo wakat no 1 tez jestsystemowy :P
            
            $result = $controls->dalObj->pgQuery($query);
            $ile = pg_num_rows($result);
            
            echo "<form action='".$_SERVER['PHP_SELF']."' method='POST'><table class='gridTable' border='0' cellspacing='0'>";
            echo $controls->AddHidden('id_adres', 'id_adres', ''); 
            echo valControl::_RowsCount($ile);
             
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
            $odlamki_nag = explode(",","Id, Osoba kontaktowa");
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
                    echo $controls->AddTableSubmit("edit_adres", $wiersz['id'], "Edytuj.", "onClick='id_adres.value=this.id;'");
                    echo '</td>';
                }
                //przycisk kasowania, zasada okreslania id osoby jak powyzej
                if (isset($_SESSION['kasowanie_rekordu']))
                {
                    echo '<td nowrap align="CENTER">';
                    echo $controls->AddTableSubmit("erase_adres", $wiersz['id'], "Kasuj.", "onClick='id_adres.value=this.id;'");
                    echo '</td>';
                }
                addRowsToTable($wiersz);
                echo '</tr>';
            }  
            echo '</table></form>';  
        
            echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST"><table>';
            echo '<tr><td>Osoba kontaktowa:</td><td>';
            echo $controls->AddTextbox("osoba", "osoba", "", 45, 45, "");
            echo '</td></tr><tr><td>';
            echo $controls->AddSubmit("insert_adres", "insert_adres", "Dodaj.", "");
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
