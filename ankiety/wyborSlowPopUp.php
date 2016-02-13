<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
<link href="../css/ankieta.css" rel="stylesheet" type="text/css">
</head>
<?php
    session_start();
    require("../conf.php");
    include_once("../vaElClass.php");
        require("../naglowek.php");
	    
        if (isset($_GET['table']))
        {
            $_SESSION['table'] = $_GET['table'];
            $_SESSION['hid'] = $_GET['hidId'];
            $_SESSION['txt'] = $_GET['txtId'];
        }

        $controls = new valControl();
        
        $_POST['dana'] = isset($_POST['dana']) ? $_POST['dana'] : ''; 
        echo "<table border='0'><form method='POST' action='".$_SERVER['PHP_SELF']."'>";
        $controls->AddHiddenCtrlConfig("table", "hid", "txt", $_SESSION['table'], '', '');
        echo "<tr><td>".$controls->AddSeekTextbox("dana", $_POST['dana'], "dana", 30, 30)."</td><td>";
        echo $controls->AddSubmit('potwierdz', 'id_potwierdz', 'Szukaj.', '');
        echo "</td></tr></form></table>";
        require("../stopka.php");
        
        echo "<table border='0'>";
        if ($_SESSION['table'] == 'zawod')
        {
            $Filter = "select id, nazwa from ".$_SESSION['table']." where lower(nazwa) like lower('%".$_POST['dana']."%') and widoczne = true order by nazwa asc;";
        }
        else
        {
            $Filter = "select id, nazwa from ".$_SESSION['table']." where lower(nazwa) like lower('%".$_POST['dana']."%') order by nazwa asc;";
        }
        $result = $controls->dalObj->PobierzDane($Filter, $ilosc_wierszy); 
        if ($ilosc_wierszy < 1)
        {
            echo "Zapytanie nie zwrociło wyników. Rozszerz kryterium.";
        }
        foreach ($result as $row)
        {
            echo "<tr><td><input type='button' name='wybierz_zawod' value='Wybierz' 
            onclick='opener.window.document.getElementById(\"".$_SESSION['hid']."\").value = \"".$row['id']."\";
            opener.window.document.getElementById(\"".$_SESSION['txt']."\").value = \"".$row['nazwa']."\";
            window.close();'></td><td>".$row['nazwa']."</td></tr>";
        }
        echo "</table>";
?>
</html>