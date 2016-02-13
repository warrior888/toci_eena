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
	if ($_GET['X-ERA-counter'])
	{
		echo "Na bramce pozostało ".$_GET['X-ERA-counter']." sms do wysłania.<br>";
	}
	if ($_GET['X-ERA-error'])
	{
		//echo "Na bramce pozostało ".$_GET['X-ERA-counter']." sms do wysłania.";
		switch($_GET['X-ERA-error'])
		{
			case 0:
				break;
			case 1:
				echo "Ma miejsce awaria systemu Era, musimy poczekać<br>";
				break;
			case 2:
				echo "Logowanie do serwisu Ery się nie powiodło, skontaktuj się z administartorem.<br>";
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
				echo "Telefon do odbiorcy okazał się niewłaściwy. To niemożliwe, skontaktuj się z administartorem.<br>";
				break;
			case 9:
				echo "Podano zbyt długą wiadomość.<br>";
				break;
			case 10:
				echo "Żetony się skończyły. Poszukaj tej czarnej nokii i doładuj ;).<br>";
				break;
		}
	}
	if ($_GET['X-ERA-error'] == 0)
	{
		$query = "select id from rodzaj_korespondencji where nazwa = 'SMS';";
		$result = pg_query($query);
		$row = pg_fetch_array($result);
		$query = "select id from uprawnienia where nazwa_uzytkownika = '".$_SESSION['uzytkownik']."';";
		$result = pg_query($query);
		$row2 = pg_fetch_array($result);
		$dzis = date(Y."-".m."-".d);
		$zapytanie = "insert into korespondencje values (".$_SESSION['id'].",".$row['id'].",".$row2['id'].",'".$dzis."');";
		//echo $zapytanie;
		$wynik = pg_query($database,$zapytanie);
	}
	//echo "Wysłanie SMS kosztowało ".$_SESSION['zetony']." zetony(ów).<br>";
	//echo "Wysłano SMS do ".$_SESSION['licznik_sms']." osób.<br>";
	echo "Koszt jednego SMS to ".$_GET['X-ERA-cost']." żetonów.<br>";
	echo "Na serwerze pozostało ".$_GET['X-ERA-tokens']." żetonów.<br>";
	//$_SESSION['zetony'] += $_GET['X-ERA-cost'];
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
