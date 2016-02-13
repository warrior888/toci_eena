<?php
    require_once '../conf.php';
    CommonUtils::outputBufferingOn();
    CommonUtils::SessionStart();

    echo '<html>';
    HelpersUI::addHtmlBasicHeaders(HelpersUI::PATH_LEVEL_1);
    echo '<body>';
//basic scipt idea is to input wages for people younger than 23 for a clients using this rule
//script enables input of the age and wage for it, then checks if there is no collision - 2 rows with the same age are unallowed
//and finally inserts data
//to update a wage for a certain age we choose an age from a combo, input new value (above from the form we can see curent wages for each age)
//and we can submit; the chosen age can also be deleted
//input ages and wages are validated by js
//the script doesn't:
//  - enforce insert all the ages from a range
//  - enforce growing wages parallely to a growing age
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
        $controls = new valControl();
        include_once("../prawa_strona/f_image_operations.php");
        include_once("../dal/klient.php");
        //read controls and database connection object from session
        //unserialize is necessary
        $controls = unserialize($_SESSION['controls']);
        //action buttons names
        $insert = 'insert_stawka';
        $update = 'update_stawka';
        $delete = 'erase_stawka';
        $edit = 'edit_stawka';
        //objects represent basic data for tables operated here like : table name, table id column
        $db =& new stawkiOddzial();
        $oddzial =& new oddzial();
        //hidden name that all id's for the table we operate on are put
        $hidden_id = 'id_stawka'; 
        //read the master table id that table we operate on reference from and thus belong to
        if (isset($_GET['id']))
        {
            $_SESSION['oddzial_id'] = $_GET['id'];
        }
        //delete one of the wage definitions
	    if (isset($_POST[$delete]) && $_POST['wiek_dropdown'])//jesli nacisnieto przycisk kasowania stawki
	    {
		    $query = "DELETE from ".$db->tableName." where ".$db->tableName.".".$db->tableId." = ".$_POST[$hidden_id].";";
		    $result = $controls->dalObj->pgQuery($query); 
	    }
        //update chosen age with the input wage
	    if (isset($_POST[$update]) && $_POST['wiek_dropdown'] && $_POST['stawka_update'])//jesli nacisnieto przycisk aktualizacji stawki
	    {
		    $query = "UPDATE ".$db->tableName." set wiek='".$_POST['wiek_dropdown']."', stawka='".$_POST['stawka_update']."' where ".$db->tableId."=".$_POST[$hidden_id].";";
		    $result = $controls->dalObj->pgQuery($query); 
	    }
	    if (isset($_POST[$insert]) && $_POST['os_wiek'] && $_POST['os_stawka'])//jesli nacisnieto przycisk wprowadzenia stawki
	    {
		    //sprawdzenie czy dana stawka figuruje pod danym klientem
		    $test_query = "select id from ".$db->tableName." where id_oddzial = ".$_SESSION['oddzial_id']." and wiek = '".$_POST['os_wiek']."';";
		    $res_test_query = $controls->dalObj->pgQuery($test_query); 
		    if(pg_num_rows($res_test_query) == 0)//jesli res_test_query ma 0 wierszy, czyli nie ma rekordu o takiej stawce
		    {
			    //wprowadzenie stawki z wiekiem
			    $query = "INSERT into ".$db->tableName." values (nextval('".$db->tableName."_".$db->tableId."_seq'), ".$_SESSION['oddzial_id'].", '".$_POST['os_wiek']."', '".$_POST['os_stawka']."');";
			    $result = $controls->dalObj->pgQuery($query); 
			    //echo $query;
		    }
		    else
		    {
			    //jesli ktos podaje wiek ktory juz figuruje i ma przyznana stawke
			    echo "Stawka dla takiego wieku jest ju¿ w systemie, mo¿na j± wyrzuciæ lub zaktualizowaæ<br>nie mo¿na jej wpisaæ po raz kolejny.";
		    }
	    }
	    //wczytanie do wgladu stawek juz dopisanych pod klientem
	    $query = "select ".$db->tableName.".".$db->tableId.",wiek,stawka from ".$db->tableName." where id_oddzial = ".$_SESSION['oddzial_id']." order by wiek asc;";
	    $result = $controls->dalObj->pgQuery($query); 
	    echo "<table>";
	    while ($row = pg_fetch_array($result))
	    {
		    echo "<tr><td>Wiek:</td><td>".$row['wiek'].", </td><td>Stawka:</td><td>".$row['stawka']."</td></tr>";
	    }
	    echo "</table><table>";
        //enable delete and update actions
	    $query = "select ".$db->tableId.", wiek from ".$db->tableName." where id_oddzial = ".$_SESSION['oddzial_id']."order by wiek asc;";
	    $result = $controls->dalObj->pgQuery($query); 
	    if (pg_num_rows($result) > 0)
	    {
		    echo "<form action='".$_SERVER['PHP_SELF']."' method='POST'>"; 
            echo $controls->AddHidden($hidden_id, $hidden_id, ''); 
		    echo "<table><tr><td><select name='wiek_dropdown' id='wiek_dropdown'>";
		    while ($row = pg_fetch_array($result))
		    {
			    echo "<option id='".$row[$db->tableId]."' value='".$row['wiek']."'>".$row['wiek'];
		    }
		    echo '</select></td><td>';
            echo $controls->AddTextbox("stawka_update", "stawka_update", "", 5, 4, "onChange='stawki_wiek(this);'");
            echo '</td><td>';
            echo $controls->AddSubmit($update, $update, "Aktualizuj.", "onClick='".$hidden_id.".value=wiek_dropdown.options[wiek_dropdown.selectedIndex].id;'");
            echo '</td>';
		    if ($_SESSION['kasowanie_rekordu'])
		    {
			    echo '<td>';
                echo $controls->AddSubmit($delete, $delete, "Kasuj.", "onClick='".$hidden_id.".value=wiek_dropdown.options[wiek_dropdown.selectedIndex].id;'");
                echo '</td>';
		    }
		    echo '</tr></table></form>';
	    }
	    echo '<table>';
	    echo "<form action='".$_SERVER['PHP_SELF']."' method='POST'>";
	    echo '<tr><td>Wiek: </td><td>';
        echo $controls->AddNumberbox("os_wiek", "os_wiek", "", 2, 4, "wiek_stawki(this);");
        echo '</td></tr><tr><td>Stawka: </td><td>';
        echo $controls->AddTextbox("os_stawka", "os_stawka", "", 5, 4, "onChange='stawki_wiek(this);'");
        echo '</td></tr><tr><td>';
        echo $controls->AddSubmit($insert, $insert, "Wprowad¼.", "");
        echo '</td></tr></form><tr><td>';
        echo $controls->AddSubmit("", "", "Zamknij.", "onClick='window.close();'");
        echo '</td></tr></table>';
    }
    CommonUtils::sendOutputBuffer();
?>
</html>
