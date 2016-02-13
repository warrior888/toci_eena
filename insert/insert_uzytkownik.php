<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript1.3" src="../js/script.js"></script>
</head>
<?php
    session_start();
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
        require("../naglowek.php");
	    require("../conf.php");
        //a tu piszemy cala reszte :P
	    if(isset($_POST['dodaj_uzytkownik']) && isset($_SESSION['zmiana_uprawnien']))
	    {
		    $_POST['haslo'] = md5($_POST['haslo']);
		    $query = "INSERT INTO uprawnienia (id, nazwa_uzytkownika, imie_nazwisko, haslo, wygasa) 
            VALUES (nextval('uprawnienia_id_seq'),'".$_POST['login']."','".$_POST['imie_nazwisko']."','".$_POST['haslo']."','".date('Y-m-d', time() + (24 * 60 * 60))."');";
		    $database = pg_connect($con_str);
		    $wynik = pg_query($database,$query);
		    //echo $query;
		    $query = "select currval('uprawnienia_id_seq');";
		    $wynik = pg_query($database, $query);
		    $wiersz = pg_fetch_array($wynik);
		    echo "Użytkownik został zapisany w systemie pod numerem ".$wiersz[0].".";
	    }
        require("../stopka.php");
    }
?>
</html>
