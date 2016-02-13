<?php session_start(); ?>
<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
  <script language="javascript" src="../js/validations.js"></script>
  <script language="javascript" src="../js/utils.js"></script>
<link href="../css/ankieta.css" rel="stylesheet" type="text/css">
</head>
<?php
ini_set('display_errors', 0);
    require("../naglowek.php");
	require("../conf.php");
    $controls = new valControl();

    $_POST['grupa_zawodowa'] = isset($_POST['grupa_zawodowa']) ? ($_POST['grupa_zawodowa']) : '';
    echo "<hr /><i>Znak % reprezentuje dowolną ilość znaków. W celu wyświetlenia wszystkich grup zawodowych należy przcisnąć Szukaj bez podawania jakichkolwiek kryteriów.</i><hr />";
    echo "<table border='0'><form method='POST' action='".$_SERVER['PHP_SELF']."'>";
    echo "<tr><td>".$controls->AddSeekTextbox("grupa_zawodowa", htmlspecialchars($_POST['grupa_zawodowa']), "grupa_Zawodowa", 30, 30)."</td><td>";
    echo $controls->AddSubmit('potw_gr_zaw', 'id_potw_gr_zaw', 'Szukaj', '');    
    echo "</td></tr></form></table>";
    
    echo "<table border='0'>";
        
    $Filter = "select id, nazwa from zawod where lower(nazwa) like lower('%".$controls->dalObj->escapeString($_POST['grupa_zawodowa'])."%') and widoczne = true order by nazwa asc;";
    $result = $controls->dalObj->PobierzDane($Filter, $ilosc_wierszy);
    if ($ilosc_wierszy < 1)
    {
        echo "Zapytanie nie zwrociło wyników. Rozszerz kryterium.";
    }
    $secret = $_SESSION['obfuscation_secret'];
    $idOccGroup = md5('id_employment_group'.$secret);
    $idOccGroupId = md5('id_employment_group_id'.$secret);
    
    foreach ($result as $row)
    {
        echo "<tr><td><input type='button' name='wybierz_zawod' value='Wybierz' class='formreset' 
        onclick='opener.window.document.getElementById(\"".$idOccGroupId."\").value = \"".$row['id']."\";
        opener.window.document.getElementById(\"".$idOccGroup."\").value = \"".$row['nazwa']."\";
        window.close();'></td><td>".$row['nazwa']."</td></tr>";
    }
    echo "</table>";

    require("../stopka.php");
?>
</html>
