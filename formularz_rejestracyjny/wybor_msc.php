<?php session_start(); ?>

<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
  <script language="javascript" src="../js/validations.js"></script>
  <script language="javascript" src="../js/utils.js"></script>
  <script language="javascript" src="utils.js"></script>
<link href="../css/ankieta.css" rel="stylesheet" type="text/css">
</head>
<?php
ini_set('display_errors', 0);    
    include_once("../vaElClass.php");
    require("../naglowek.php");
	require("../conf.php");
    $controls = new valControl();
    if (isset($_POST['potw_gr_zaw']))
    {
        $database = pg_connect($con_str);
        $Filter = "select id, nazwa from miejscowosc where lower(nazwa) like lower('%".pg_escape_string($_POST['grupa_zawodowa'])."%') order by nazwa asc;";
        $result = pg_query($database, $Filter);
        if (pg_num_rows($result) == 0)
        {
            echo "Zapytanie nie zwrociło wyników. Rozszerz kryterium.<br /><br />";
        }
        
        $secret = $_SESSION['obfuscation_secret'];
        $idCity = md5('id_city'.$secret);
        $citySpan = md5('span_city'.$secret);
        $idCityId = md5('id_city_id'.$secret);
    
        echo "<table border='0'>";
        while ($row = pg_fetch_array($result))
        {
            echo '<tr><td><input type="button" name="wybierz_zawod" class="formreset" value="Wybierz" 
            onclick="parent.document.getElementById(\''.$idCity.'\').value = \''.$row['nazwa'].'\'; 
            parent.document.getElementById(\''.$citySpan.'\').innerHTML = \''.$row['nazwa'].'\'; 
            parent.document.getElementById(\''.$idCityId.'\').value = \''.$row['id'].'\'; 
            parent.document.getElementById(\'popup\').style.display = \'none\';"></td><td>'.$row['nazwa'].'</td></tr>';
        }
        echo "</table>";
    }
    $grupa_zawodowa = null;
    if (isset($_POST['grupa_zawodowa']))
        $grupa_zawodowa = htmlspecialchars($_POST['grupa_zawodowa']);
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
