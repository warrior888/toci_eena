<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
<link href="../css/ankieta.css" rel="stylesheet" type="text/css">
</head>
<?php
    session_start();
    include_once("../vaElClass.php");
    require("../naglowek.php");
	require("../conf.php");
    $controls = new valControl();
    if (isset($_POST['potw_gr_zaw']))
    {
        echo "<table border='0'>";
        $database = pg_connect($con_str);
        $Filter = "select id, nazwa from miejscowosc where lower(nazwa) like lower('%".$_POST['grupa_zawodowa']."%') order by nazwa asc;";
        $result = pg_query($database, $Filter);
        if (pg_num_rows($result) == 0)
        {
            echo "Zapytanie nie zwrociło wyników. Rozszerz kryterium.";
        }
        while ($row = pg_fetch_array($result))
        {
            echo '<tr><td><input type="button" name="wybierz_zawod" value="Wybierz" 
            onclick="parent.document.getElementById(\'Rcity\').value = \''.$row['nazwa'].'\'; parent.document.getElementById(\'id_Rcity_id\').value = \''.$row['id'].'\'; parent.document.getElementById(\'popup\').style.display = \'none\';"></td><td>'.$row['nazwa'].'</td></tr>';
        }
        echo "</table>";
    }
    $grupa_zawodowa = null;
    if (isset($_POST['grupa_zawodowa']))
        $grupa_zawodowa = $_POST['grupa_zawodowa'];
    echo "W chwili obecnej pobranie miejscowości wydaje się niemożliwe. Poniższy formularz pozwoli dokonać wyboru spośród miejscowości z bazy danych E&A. W celu wyświetlenia wszystkich miejscowości należy przcisnąć Szukaj bez podawania jakichkolwiek kryteriów.<hr />";
    echo "<table border='0'><form method='POST' action='".$_SERVER['PHP_SELF']."'>";
    echo "<tr><td>".$controls->AddSeekTextbox("grupa_zawodowa", $grupa_zawodowa, "grupa_Zawodowa", 30, 30)."</td><td>";
    echo $controls->AddSubmit('potw_gr_zaw', 'id_potw_gr_zaw', 'Szukaj', '');
    echo '</td><td>';    
    echo $controls->AddSubmit('zamknij', 'id_zamknij', 'Zamknij okno', JsEvents::ONCLICK.'="parent.document.getElementById(\'popup\').style.display = \'none\'; return false;"');    
    echo "</td></tr></form></table>";
    require("../stopka.php");
?>
</html>
