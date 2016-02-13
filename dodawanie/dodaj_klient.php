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
        require_once '../dal/klient.php';
        $controls = new valControl();
        $db = new klient();

        if (isset($_POST['insert_klient']))
        {
            $testQuery = "select id from ".$db->tableName." where lower(nazwa) = lower('".$_POST['klient']."') or lower(nazwa_alt) = lower('".$_POST['klient_alt']."');";
            $result = $controls->dalObj->pgQuery($testQuery);
            if (pg_num_rows($result) == 0)
            {
                $query = "INSERT into ".$db->tableName." values (nextval('".$db->tableName."_".$db->tableId."_seq'), 
                '".$_POST['klient']."', '".$_POST['klient_alt']."', ".$_POST['panstwo_egz_id'].", ".$_POST['panstwo_pos_id'].", ".$_POST['firma_id'].", '".$_POST['adres']."');";
                //echo $query;
                $result = $controls->dalObj->pgQuery($query);
                $query = "select currval('".$db->tableName."_".$db->tableId."_seq');";
                $wynik = $controls->dalObj->pgQuery($query);
                $wiersz = pg_fetch_array($wynik);
                echo "Klient zosta³ zapisany w systemie pod numerem ".$wiersz[0].".";
            }
            else
            {
                echo 'Klient widnieje w systemie.';
            }
        }
        
	    echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST"><table>';
        echo $controls->AddSelectHelpHidden();
	    echo '<tr><td>Klient - pe³na nazwa:</td><td>';
        echo $controls->AddTextbox("klient", "klient", "", 45, 45, "onChange='sprawdz_klienta(this);'");
        echo '</td></tr><tr><td>Klient - skrót:</td><td>';
        echo $controls->AddTextbox("klient_alt", "klient_alt", "", 30, 30, "onChange='sprawdz_klienta(this);'"); 
	    echo '</td></tr><tr><td>Pañstwo - lokalizacja klienta:</td><td>';
        echo $controls->AddSelectRandomQuery("id_panstwo_egz", "id_panstwo_egz", "", "select id, nazwa from panstwo order by nazwa asc;", "", "panstwo_egz_id", "nazwa", "id", "");
        echo '</td></tr><tr><td>Pañstwo - po¶rednictwo:</td><td>';
        echo $controls->AddSelectRandomQuery("id_panstwo_pos", "id_panstwo_pos", "", "select id, nazwa from panstwo order by nazwa asc;", "", "panstwo_pos_id", "nazwa", "id", "");
        echo '</td></tr><tr><td>Firma obs³uguj±ca:</td><td>';
        echo $controls->AddSelectRandomQuery("id_firma", "id_firma", "", "select id, nazwa from firma order by nazwa asc;", "", "firma_id", "nazwa", "id", "");
        echo '</td></tr><tr><td>Adres klienta:</td><td>';
        echo $controls->AddTextbox("adres", "adres", "", 50, 50, ""); 
	    echo '</td></tr><tr><td>';
        echo $controls->AddSubmit("insert_klient", "insert_klient", "Dodaj.", "onClick='ClientRequiredFields(klient, klient_alt);'");
        echo '</td></tr></table></form>';
    }
    CommonUtils::sendOutputBuffer();
?>
</body>
</html>
