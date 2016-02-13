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
        $controls = unserialize($_SESSION['controls']);
        
        $insert = 'insert_oddzial';
        $delete = 'erase_warunek';
        
        $db = new warunkiOddzial();
        $war_zatr = new warunkiZatrudnienia();
        $oddzial = new oddzial();
        
        $hidden_id = 'id_warunek';
        
        if (isset($_GET['id']))
        {
            $_SESSION['oddzial_id'] = $_GET['id'];
        }
        
	    if (isset($_POST[$delete]))
	    {
		    $query = "delete from ".$db->tableName." where ".$db->tableId." = ".$_POST[$hidden_id].";";
		    $result = $controls->dalObj->pgQuery($query);
	    }
	    if (isset($_POST[$insert]))        
	    {
		    //$odlamki = explode(":",$_POST['warunki_zatrudnienia']);
		    $query = "select ".$db->tableName.".id_warunek from ".$war_zatr->tableName." join ".$db->tableName." on ".$war_zatr->tableName.".id = ".$db->tableName.".id_warunek where ".$war_zatr->tableName.".".$war_zatr->tableId." = ".$_POST['warunki_zatrudnienia_id']." and ".$db->tableName.".id_oddzial = ".$_SESSION['oddzial_id'].";";
		    $result = $controls->dalObj->pgQuery($query);
		    if (pg_num_rows($result) == 0)
		    {
			    $query = "INSERT into ".$db->tableName." values (nextval('".$db->tableName."_".$db->tableId."_seq'), ".$_SESSION['oddzial_id'].", ".$_POST['warunki_zatrudnienia_id'].");";
			    $result = $controls->dalObj->pgQuery($query);
		    }
		    else
		    {
			    echo "Taki warunek zatrudnienia ju¿ figuruje dla oddzia³u.";
		    }
	    }
	    $query = "select ".$db->tableName.".".$db->tableId.", ".$war_zatr->tableName.".nazwa, ".$war_zatr->tableName.".szczegoly from ".$war_zatr->tableName." 
        join ".$db->tableName." on
	    ".$war_zatr->tableName.".id = ".$db->tableName.".id_warunek where ".$db->tableName.".id_oddzial = ".$_SESSION['oddzial_id'].";";
	    $result = $controls->dalObj->pgQuery($query);
	    echo "<table class='gridTable' border='0' cellspacing='0'><form method='POST' action='".$_SERVER['PHP_SELF']."'>";
	    echo $controls->AddHidden($hidden_id, $hidden_id, '');
        $count = 0;
	    while ($row = pg_fetch_array($result))
	    {
            $count++;
            $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
		    echo "<tr class='".$css."'><td>".$row['nazwa']."</td><td>".$row['szczegoly']."</td>";
		    if ($_SESSION['edycja_rekordu'])
		    {
			    echo '<td>';
                echo $controls->AddTableSubmit($delete, $row['id'], "Kasuj.", "onClick='".$hidden_id.".value=this.id;'");
                
                echo '</td>';
		    }
		    echo "</tr>";
	    }
	    echo "</form></table>";
	    $query = "select ".$war_zatr->tableId." as id, nazwa || ' : ' || szczegoly as nazwa from ".$war_zatr->tableName." where id not in (select id_warunek from ".$db->tableName." where id_oddzial = ".$_SESSION['oddzial_id'].");";

	    echo "<table><form method='POST' action='".$_SERVER['PHP_SELF']."'><tr><td>";
        echo $controls->AddSelectRandomQuery('warunki_zatrudnienia', 'id_warunki_zatrudnienia', '', $query, '', 'warunki_zatrudnienia_id');

	    echo '</td><td>';
        echo $controls->AddSubmit($insert, $insert, "Dodaj.", ""); 
        
        echo '</td></tr>';
        echo '<tr><td>';
        echo $controls->AddSubmit("", "", "Zamknij.", "onClick='window.close();'");
        echo '</td></tr>';
	    echo "</form></table>";
    }
    CommonUtils::sendOutputBuffer();
?>
</html>
