<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
<link href="../css/style.css" rel="stylesheet" type="text/css">
</head>
<?php
//ten skrypt mial zapewne nature zastosowania jednorazowa
    session_start();
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
        include("../edit/fillselect.php");
        include("../vaElClass.php");
        require("../naglowek.php");
	    require("../conf.php");
        $database = pg_connect($con_str);
	    $file = "scr_sql_zmiana_gr_zaw.sql";
        if (isset($_POST['podm_zawod']))
        {
            $mod_query = "update zawod set kod_grupy=(select kod_grupy from grupy_zawodowe where nazwa = '".$_POST['destOcc']."'), nazwa='".trim($_POST['destOcc'])."' where id = (select id from zawod where nazwa = '".$_POST['sourceOcc']."'); 
delete from grupy_zawodowe where nazwa = '".$_POST['destOcc']."';
";
            echo "<br>".$mod_query;
            $res = pg_query($database, $mod_query);
	    if ($res)
	    {
		$plik = fopen($file, "a");
		fputs($plik, $mod_query);
		fclose($plik);
	    }
        }
        if (isset($_POST['podm_zawod_u']))
        {
            $mod_query = "update dane_osobowe set id_zawod=(select id from zawod where nazwa = '".$_POST['destOccU']."') where id_zawod = (select id from zawod where nazwa = '".$_POST['sourceOccU']."'); 
delete from zawod where nazwa = '".$_POST['sourceOccU']."';
";
            echo "<br>".$mod_query;
            $res = pg_query($database, $mod_query);
	    if ($res)
	    {
		$plik = fopen($file, "a");
		fputs($plik, $mod_query);
		fclose($plik);
	    }
            //zastanowic sie czy wykonywac zapytania czy skonstruowac skrypt polecen sql i go uzyc na bazie - to 2 lepsze :)
        }
        $ctlObj = new valControl();
        $query = "select nazwa from zawod where kod_grupy = '0' order by nazwa asc;";
        $queryGrZaw = "select nazwa from grupy_zawodowe order by nazwa asc;";
        $result = pg_query($database, $query);
        $resultGrZaw = pg_query($database, $queryGrZaw);
        echo "<form method='POST' action='".$_SERVER['PHP_SELF']."'>";
        echo "<table><tr>";
        echo "<td><select name='sourceOcc' style='width: 400px;'>";
        fillselect($result, "");
        echo "</select></td>";
        echo "<td>";//<select name='destOcc'>";
        //fillselect($resultGrZaw, "");
        echo $ctlObj->OccGroupControl("Wybierz", "chooseOcc", "destOcc", "destOcc", "", "destOccId", "destOccId", "", "wybor_grupy_zaw.php", "Wymianagrup");
	    echo /*"</select>*/"</td></tr>";
	    echo "<tr><td><input type='submit' name='podm_zawod' value='Zamień'></td>";
        echo "</tr></table>";
        echo "</form>";
        echo "<hr>";
        
        echo "<form method='POST' action='".$_SERVER['PHP_SELF']."'>";
        echo "<table><tr>";
        echo "<td>ten wywal</td><td>zamien na</td></tr>";
        echo "<tr><td><select name='sourceOccU' style='width: 400px;'>";
        $result = pg_query($database, $query);
        fillselect($result, "");
        echo "</select></td>";
        echo "<td>";//<select name='destOccU'>";
        //$result = pg_query($database, $query);
        //fillselect($result, "");
        echo $ctlObj->OccGroupControl("Wybierz", "chooseOcc", "destOccU", "destOccU", "", "destOccIdU", "destOccIdU", "", "wybor_grupy_zaw_u.php", "Wymianagrup");
        echo /*"</select>*/"</td>";
        echo "<td><input type='submit' name='podm_zawod_u' value='Zamień'></td>";
        echo "</tr></table>";
        echo "</form>";
        //a tu piszemy cala reszte :P
        require("../stopka.php");
    }
?>
</html>
