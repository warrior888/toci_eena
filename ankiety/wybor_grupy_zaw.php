<?php
    session_start();
?>
<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
  <link href="style_form_box.css" rel="stylesheet" type="text/css">
</head>
<?php
//<link href="../css/ankieta.css" rel="stylesheet" type="text/css">
    
    require("../naglowek.php");
	require("../conf.php");
    $controls = new valControl();

    $_POST['grupa_zawodowa'] = isset($_POST['grupa_zawodowa']) ? $_POST['grupa_zawodowa'] : '';
    echo "<hr /><i>Znak % reprezentuje dowolną ilość znaków. W celu wyświetlenia wszystkich grup zawodowych należy przcisnąć Szukaj bez podawania jakichkolwiek kryteriów.</i><hr />";
    echo "<table border='0'><form method='POST' action='".$_SERVER['PHP_SELF']."'>";
    echo "<tr><td>".$controls->AddSeekTextbox("grupa_zawodowa", $_POST['grupa_zawodowa'], "grupa_Zawodowa", 30, 30)."</td><td>";
    echo $controls->AddSubmit('potw_gr_zaw', 'id_potw_gr_zaw', 'Szukaj', '');    
    echo "</td></tr></form></table>";
    
    echo '<table border="0" class="buttons">';
        
    $Filter = "select id, nazwa from zawod where lower(nazwa) like lower('%".$_POST['grupa_zawodowa']."%') and widoczne = true order by nazwa asc;";
    $result = $controls->dalObj->PobierzDane($Filter, $ilosc_wierszy);
    if ($ilosc_wierszy < 1)
    {
        echo "Zapytanie nie zwrociło wyników. Rozszerz kryterium.";
    }
    foreach ($result as $row)
    {
        echo "<tr><td><input type='button' name='wybierz_zawod' value='Wybierz' 
        onclick='opener.window.document.getElementById(\"hid_gr_zaw\").value = \"".$row['id']."\";
        opener.window.document.getElementById(\"Rtxt_gr_zaw\").value = \"".$row['nazwa']."\";
        window.close();'></td><td>".$row['nazwa']."</td></tr>";
    }
    echo "</table>";

    require("../stopka.php");
?>
</html>
