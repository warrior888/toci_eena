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
		echo "Na bramce pozosta�o ".$_GET['X-ERA-counter']." sms do wys�ania.<br>";
	}
	if (isset($_GET['X-ERA-error']))
	{
		//echo "Na bramce pozosta�o ".$_GET['X-ERA-counter']." sms do wys�ania.";
		switch($_GET['X-ERA-error'])
		{
			case 1:
				echo "Ma miejsce awaria systemu Era, musimy poczeka�<br>";
				break;
			case 2:
				echo "Logowanie do serwisu Ery si� nie powiod�o, skontaktuj si� z administratorem.<br>";
				break;
			case 3:
				echo "Dost�p do bramki jest zablokowany.<br>";
				break;
			case 5:
				echo "Wyst�pi� b��d w zleceniu przesy�ki - to niemo�liwe, skontaktuj si� z administratorem.<br>";
				break;
			case 7:
				echo "Limit zosta� wyczerpany<br>";
				break;
			case 8:
				echo "Telefon do odbiorcy okaza� si� niew�a�ciwy. To niemo�liwe, skontaktuj si� z administratorem.<br>";
				break;
			case 9:
				echo "Podano zbyt d�ug� wiadomo��.<br>";
				break;
			case 10:
				echo "�etony si� sko�czy�y. Poszukaj tej czarnej nokii i do�aduj ;).<br>";
				break;
		}
	}
	$_SESSION['zetony'] = $_SESSION['licznik_sms'] * $_GET['X-ERA-cost'];
	echo "Wys�anie SMS kosztowa�o ".$_SESSION['zetony']." zetony(�w).<br>";
	echo "Wys�ano SMS do ".$_SESSION['licznik_sms']." os�b.<br>";
	echo "Koszt jednego SMS to ".$_GET['X-ERA-cost']." �eton�w.<br>";
	echo "Na serwerze pozosta�o ".$_GET['X-ERA-tokens']." �eton�w.<br>";
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
