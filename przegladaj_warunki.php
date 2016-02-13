<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="js/script.js"></script>

<link href="css/layout.css" rel="stylesheet" type="text/css">
</head>
<?php
    session_start();
    if (empty($_SESSION['uzytkownik']))
    {
        require("log_in.php");
    }
    else
    {
        include_once "vaElClass.php";
        require("naglowek.php");
	    require("conf.php");
	    $database = pg_connect($con_str);
        $controls = new valControl();
	    if(isset($_POST['kasuj_warunek']))
	    {
		    $query = "delete from warunki_zatrudnienia where id = ".$_POST['id_warunku'].";";
		    $result = pg_query($database,$query);
	    }
	    if(isset($_POST['warunek_update']))
	    {
		    $query = "select id from warunki_zatrudnienia where nazwa = '".$_POST['warunek_nazwa_update']."' and szczegoly = '".$_POST['warunek_szczegoly_update']."';";
		    $result = pg_query($database,$query);
		    if (pg_num_rows($result) == 0)
		    {
			    $query = "update warunki_zatrudnienia set nazwa = '".$_POST['warunek_nazwa_update']."', szczegoly = '".$_POST['warunek_szczegoly_update']."' where id = ".$_POST['id_warunku'].";";
			    $result = pg_query($database,$query);
		    }
		    else
		    {
			    echo "Podane warunki zatrudnienia są już zdefiniowane w systemie.";
		    }
	    }
	    if(isset($_POST['edit_warunek']))
	    {
		    $query = "select id,nazwa,szczegoly from warunki_zatrudnienia where id = ".$_POST['id_warunku'].";";
		    $result = pg_query($database,$query);
		    $row = pg_fetch_array($result);
		    echo "<table><form method='POST' action='".$_SERVER['PHP_SELF']."'>";
		    echo $controls->AddHidden('id_id_warunku', 'id_warunku', $row['id'] );
		    echo "<tr><td>Nazwa:</td><td>";
            echo $controls->AddTextbox('warunek_nazwa_update', 'id_warunek_nazwa_update', $row['nazwa'], '45', '25', ''); 
            echo "</td></tr>";
		    echo "<tr><td>Szczegóły:</td><td>";
            echo $controls->AddTextbox('warunek_szczegoly_update', 'id_warunek_szczegoly_update', $row['szczegoly'], '200', '50', ''); echo "</td></tr>";
		    echo "<tr><td>";
            echo $controls->AddSubmit('warunek_update', 'id_warunek_update', 'Potwierdź', '');
            echo "</td></tr>";
		    echo "</form></table>";
		    echo "<hr>";
	    }
	    if(isset($_POST['warunek_potwierdz']) && $_POST['warunek_nazwa'] && $_POST['warunek_szczegoly'])
	    {
		    $query = "select id from warunki_zatrudnienia where nazwa = '".$_POST['warunek_nazwa']."' and szczegoly = '".$_POST['warunek_szczegoly']."';";
		    $result = pg_query($database,$query);
		    if (pg_num_rows($result) == 0)
		    {
			    $query = "INSERT INTO warunki_zatrudnienia VALUES (nextval('warunki_zatrudnienia_id_seq'),'".$_POST['warunek_nazwa']."','".$_POST['warunek_szczegoly']."');";
			    $wynik = pg_query($database, $query);		
			    //echo $query;
		    }
		    else
		    {
			    echo "Podane warunki zatrudnienia są już zdefiniowane w systemie.";
		    }
	    }
	    $query = "select id,nazwa,szczegoly from warunki_zatrudnienia order by id asc;";
	    $result = pg_query($database, $query);
	    echo "<table class='gridTable' border='0' cellspacing='0'><form method='POST' action='".$_SERVER['PHP_SELF']."'><tr><th>ID</th><th>Nazwa</th><th>Szczegóły</th>";
	    if ($_SESSION['edycja_rekordu'] == 1)
	    {
		    echo "<th>Edycja</th>";
	    }
	    if ($_SESSION['kasowanie_rekordu'] == 1)
	    {
		    echo "<th>Kasowanie</th>";
	    }
	    echo "</tr>";
	    echo $controls->AddHidden('id_id_warunku', 'id_warunku', '');
        $count = 0;
        
	    while ($row = pg_fetch_array($result))
	    {
            $count++;
            $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
		    echo "<tr class='".$css."'><td>".$row['id']."</td><td>".$row['nazwa']."</td><td>".$row['szczegoly']."</td>";
		    if ($_SESSION['edycja_rekordu'] == 1)
		    {
			    echo "<td>";
                echo $controls->AddSubmit('edit_warunek', 'id_edit_warunek', 'Edytuj.', 'onclick="id_warunku.value='.$row['id'].';"');
                echo "</td>";
		    }
		    if ($_SESSION['kasowanie_rekordu'] == 1)
		    {
			    echo "<td>";
                echo $controls->AddSubmit('kasuj_warunek', 'id_kasuj_warunek', 'Kasuj.', 'onclick="id_warunku.value='.$row['id'].'";');
                echo "</td>";
		    }
		    echo "</tr>";
	    }
	    echo "</form></table>";
	    echo "<hr>";
	    echo "<table><form method='POST' action='".$_SERVER['PHP_SELF']."'>";
	    echo "<tr><td>Nazwa warunku:</td><td>";
        echo $controls->AddTextbox('warunek_nazwa', 'id_warunek_nazwa', '', '45', '25', '');
        echo "</td></tr>";
	    echo "<tr><td>Szczegóły warunku:</td><td>";
        echo $controls->AddTextbox('warunek_szczegoly', 'id_warunek_szczegoly', '', '200', '50', '');
        echo "</td></tr>";
	    echo "<tr><td>";
        echo $controls->AddSubmit('warunek_potwierdz', 'id_warunek_potwierdz', 'Potwierdź.', '');
        echo "</td></tr>";
	    echo "</form></table>";
        require("stopka.php");
    }
?>
</html>
