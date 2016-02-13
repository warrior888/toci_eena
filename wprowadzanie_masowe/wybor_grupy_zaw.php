<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
<link href="../css/style.css" rel="stylesheet" type="text/css">
</head>
<?php
    @session_start();
    include_once("../vaElClass.php");
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
        require("../naglowek.php");
	    require("../conf.php");
        $controls =& new valControl();
        if (isset($_POST['potw_gr_zaw']))
        {
            echo "<table border='0'>";
            $database = pg_connect($con_str);
            $Filter = "select id, nazwa from grupy_zawodowe where lower(nazwa) like lower('%".$_POST['grupa_zawodowa']."%') order by nazwa asc;";
            $result = pg_query($database, $Filter);
            if (pg_num_rows($result) == 0)
            {
                echo "Zapytanie nie zwrociło wyników. Rozszerz kryterium.";
            }
            while ($row = pg_fetch_array($result))
            {
                echo "<tr><td><input type='button' name='wybierz_zawod' value='Wybierz' 
                onclick='opener.window.document.getElementById(\"destOccId\").value = \"".$row['id']."\";
                opener.window.document.getElementById(\"destOcc\").value = \"".$row['nazwa']."\";
                window.close();'></td><td>".$row['nazwa']."</td></tr>";
            }
            echo "</table>";
        }
        echo "<table border='0'><form method='POST' action='".$_SERVER['PHP_SELF']."'>";
        if (!isset($_POST['grupa_zawodowa']))
        {
            $_POST['grupa_zawodowa'] = "";
        }
        echo "<tr><td>".$controls->AddSeekTextbox("grupa_zawodowa", $_POST['grupa_zawodowa'], "grupa_Zawodowa", 30, 30)."</td>
        <td><input type='submit' name='potw_gr_zaw' value='Szukaj'></td></tr></form>";
        
        echo "</table>";
        require("../stopka.php");
    }
?>
</html>