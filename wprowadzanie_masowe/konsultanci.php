<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
<link href="../css/style.css" rel="stylesheet" type="text/css">
</head>
<?php
    @session_start();
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
        require("../naglowek.php");
	    require("../conf.php");
        $database = pg_connect($con_str);
        if (isset($_POST['confirm']))
        {
            $count = "select count(id) as ilosc from dane_osobowe where id_konsultant = (select id from uprawnienia where nazwa_uzytkownika = '".$_POST['uz_wej']."');";
            $countresult = pg_query($database, $count);
            $countrow = pg_fetch_array($countresult);
            $zapytanie = "update dane_osobowe set id_konsultant = (select id from uprawnienia where nazwa_uzytkownika = '".$_POST['uz_wyj']."') where id_konsultant = (select id from uprawnienia where nazwa_uzytkownika = '".$_POST['uz_wej']."');";
            $result = pg_query($database, $zapytanie); 
            echo "Ilość osób objętych operacją: ".$countrow['ilosc'];
        }
        $userlist = "select id, nazwa_uzytkownika from uprawnienia where nazwa_uzytkownika != 'postgres';";
        $resuserlist = pg_query($database, $userlist);
        echo "<table>";
        while ($userrow = pg_fetch_array($resuserlist))
        {
            $peoplecount = "select count(id) as ilosc from dane_osobowe where id_konsultant = ".$userrow['id'].";";
            $respeoplecount = pg_query($database, $peoplecount);
            $peoplerow = pg_fetch_array($respeoplecount);
            echo "<tr><td>".$userrow['nazwa_uzytkownika']."</td><td>".$peoplerow['ilosc']."</td></tr>";
        }
        echo "</table>";
        echo "<table><form method = 'POST' action='".$_SERVER['PHP_SELF']."'>";
        echo "<tr><td>Użytkownik wykluczany:</td><td><select name='uz_wej'><option>--------</option>";
        $userlist = "select id, nazwa_uzytkownika from uprawnienia where nazwa_uzytkownika != 'postgres' order by nazwa_uzytkownika;";
        $resuserlist = pg_query($database, $userlist);
        while ($userrow = pg_fetch_array($resuserlist))
        {
            echo "<option>".$userrow['nazwa_uzytkownika']."</option>";
        }
        echo "</td><td>Użytkownik przejmujący:</td><td><select name='uz_wyj'><option>--------</option>";
        $userlist = "select id, nazwa_uzytkownika from uprawnienia where nazwa_uzytkownika != 'postgres' order by nazwa_uzytkownika;";
        $resuserlist = pg_query($database, $userlist);
        while ($userrow = pg_fetch_array($resuserlist))
        {
            echo "<option>".$userrow['nazwa_uzytkownika']."</option>";
        }
        echo "</td><td><input type='submit' name='confirm' value='Zamień'></td></tr>";
	echo "</form></table>";
        //echo "<form method='POST' action='wymiana_zawodow.php'><input type='submit' name='przetw_zaw' value='Przetwarzanie zawodów'></form>";
        require("../stopka.php");
    }
?>
</html>
