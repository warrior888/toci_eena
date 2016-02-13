<?php session_start(); ?>
<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
<link href="../css/ankieta.css" rel="stylesheet" type="text/css">
</head>
<?php
ini_set('display_errors', 0);
        require_once '../vaElClass.php';
        require_once '../naglowek.php';
	    require_once '../conf.php';
        
        $tablesMapping = array (
            1 => 'zawod',
            //??
        );
        
        if (isset($_GET['table']))
        {
            $tableId = (int)$_GET['table'];
            $_SESSION['table'] = isset($tablesMapping[$tableId]) ? $tablesMapping[$tableId] : current($tablesMapping);
            $_SESSION['hid'] = htmlspecialchars($_GET['hidId']);
            $_SESSION['txt'] = htmlspecialchars($_GET['txtId']);
        }
        
        if (!isset($_SESSION['table']))
        {
            $_SESSION['table'] = current($tablesMapping);
        }

        $controls = new valControl();
        
        $_POST['dana'] = isset($_POST['dana']) ? htmlspecialchars($_POST['dana']) : ''; 
        echo "<table border='0'><form method='POST' action='".$_SERVER['PHP_SELF']."'>";
        $controls->AddHiddenCtrlConfig("table", "hid", "txt", $_SESSION['table'], '', '');
        echo "<tr><td>".$controls->AddSeekTextbox("dana", $_POST['dana'], "dana", 30, 30)."</td><td>";
        echo $controls->AddSubmit('potwierdz', 'id_potwierdz', 'Szukaj.', '');
        echo "</td></tr></form></table>";
        require("../stopka.php");
        
        echo "<table border='0'>";
        if ($_SESSION['table'] == 'zawod')
        {
            $Filter = "select id, nazwa from ".$_SESSION['table']." where lower(nazwa) like lower('%".$controls->dalObj->escapeString($_POST['dana'])."%') and widoczne = true order by nazwa asc;";
        }
        else
        {
            $Filter = "select id, nazwa from ".$_SESSION['table']." where lower(nazwa) like lower('%".$controls->dalObj->escapeString($_POST['dana'])."%') order by nazwa asc;";
        }
        $result = $controls->dalObj->PobierzDane($Filter, $ilosc_wierszy); 
        if ($ilosc_wierszy < 1)
        {
            echo "Zapytanie nie zwrociło wyników. Rozszerz kryterium.";
        }
        foreach ($result as $row)
        {
            echo "<tr><td><input type='button' name='wybierz_zawod' class='formreset' value='Wybierz' 
            onclick='opener.window.document.getElementById(\"".$_SESSION['hid']."\").value = \"".$row['id']."\";
            opener.window.document.getElementById(\"".$_SESSION['txt']."\").value = \"".$row['nazwa']."\";
            window.close();'></td><td>".$row['nazwa']."</td></tr>";
        }
        echo "</table>";
?>
</html>