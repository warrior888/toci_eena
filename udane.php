<META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
<link href="css/style.css" rel="stylesheet" type="text/css">
<?php
    @session_start();
    //if (empty($_SESSION['uzytkownik']))
    //{
    //    require("log_in.php");
    //}
    //else
    //{
	//require("naglowek.php");
	require("conf.php");
        //a tu piszemy cala reszte :P
        //require("stopka.php");
	//echo "Ogolnie rzecz biorac tu jestem.";
        //for ($i = 0; $i < count($tab) - 1; $i++)
        //{
        $database = pg_connect($con_str);
        $tab = explode("|", $_SESSION['edycja_masowa']);
	if($tab[$_SESSION['licznik_sms']] != "")
	{
 	       $zapytanie = "select nazwa from telefon_kom where id = '".$tab[$_SESSION['licznik_sms']]."';";
	       //echo $_SESSION['licznik_sms'];
	       //echo $zapytanie;
               $wynik = pg_query($database, $zapytanie);
               $w = pg_fetch_array($wynik);
               if ($w['nazwa'] != "")
               {
			//echo "Jestem i tu.";
			$telefon = "48".$w['nazwa'];
			$platny = "omnix";
			//$platny = "omnix";
			$location = "http://www.eraomnix.pl/msg/api/do/tinker/".$platny."?message=".$_SESSION['tekst1']."&number=".$telefon."&password=eena34&login=48660496275&failure=http://".$_SERVER['SERVER_NAME']."/udane.php&success=http://".$_SERVER['SERVER_NAME']."/udane.php&mms=false";
			$slij = "Location: ".$location."";
			@header($slij);
		}
		$_SESSION['licznik_sms']++;
	}
	if (isset($_GET['X-ERA-counter']))
	{
		echo "Na bramce pozostało ".$_GET['X-ERA-counter']." sms do wysłania.<br>";
	}
	if (isset($_GET['X-ERA-error']))
	{
		//echo "Na bramce pozostało ".$_GET['X-ERA-counter']." sms do wysłania.";
		switch($_GET['X-ERA-error'])
		{
			case 1:
				echo "Ma miejsce awaria systemu Era, musimy poczekać<br>";
				break;
			case 2:
				echo "Logowanie do serwisu Ery się nie powiodło, skontaktuj się z administratorem.<br>";
				break;
			case 3:
				echo "Dostęp do bramki jest zablokowany.<br>";
				break;
			case 5:
				echo "Wystąpił błąd w zleceniu przesyłki - to niemożliwe, skontaktuj się z administratorem.<br>";
				break;
			case 7:
				echo "Limit został wyczerpany<br>";
				break;
			case 8:
				echo "Telefon do odbiorcy okazał się niewłaściwy. To niemożliwe, skontaktuj się z administratorem.<br>";
				break;
			case 9:
				echo "Podano zbyt długą wiadomość.<br>";
				break;
			case 10:
				echo "Żetony się skończyły. Poszukaj tej czarnej nokii i doładuj ;).<br>";
				break;
		}
	}
	$_SESSION['zetony'] = $_SESSION['licznik_sms'] * $_GET['X-ERA-cost'];
	echo "Wysłanie SMS kosztowało ".$_SESSION['zetony']." zetony(ów).<br>";
	echo "Wysłano SMS do ".$_SESSION['licznik_sms']." osób.<br>";
	echo "Koszt jednego SMS to ".$_GET['X-ERA-cost']." żetonów.<br>";
	echo "Na serwerze pozostało ".$_GET['X-ERA-tokens']." żetonów.<br>";
	//echo "<br>";
	//echo "Error: ".$_GET['X-ERA-error'];
	//echo "<br>";
	//echo "Counter: ".$_GET['X-ERA-counter'];
	//echo "<br>";
	//echo $_GET['X-ERA-tokens'];
	//echo "<br>";
	//echo $_GET['X-ERA-cost'];
	//echo "<br>";
   // }
?>
