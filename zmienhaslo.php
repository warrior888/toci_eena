<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript1.3" src="js/script.js"></script>
<link href="css/style.css" rel="stylesheet" type="text/css"></head>
</head>
<?php
    @session_start();
    if (empty($_SESSION['uzytkownik']))
    {
        require("log_in.php");
    }
    else if (isset($_SESSION['zmiana_uprawnien']))
    {
        require("naglowek.php");
	    require("conf.php");

	    if ($_POST['potwzmhasla'])
	    {
		    if(($_POST['haslo1'] == $_POST['haslo2']) && strlen($_POST['haslo1']) > 7)
		    {
                if ((strlen($_POST['user']) > 0) && (strlen($_POST['name']) > 0))
                {
			        $_POST['haslo1'] = md5($_POST['haslo1']);
                    $expiresAt = date('Y-m-d', time() + (24 * 60 * 60));
			        $zapytanie = "UPDATE uprawnienia SET haslo = '".$_POST['haslo1']."', nazwa_uzytkownika = '".addslashes($_POST['user'])."', imie_nazwisko = '".addslashes($_POST['name'])."', wygasa = '".$expiresAt."' where id = '".(int)$_POST['id']."';";
			        $database = pg_connect($con_str);
			        $wynik = pg_query($database, $zapytanie);
			        echo "Zmiana powiodła się.";
                }
		    }
		    else
		    {
			    echo "Hasła się nie zgadzają lub są krótsze niż 8 znaków.";
		    }
	    }
        require("stopka.php");
    }
?>
</html>