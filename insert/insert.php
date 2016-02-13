<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <link href="../css/style.css" rel="stylesheet" type="text/css">
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
        require_once '../bll/queries.php';
        require_once '../bll/additionals.php';

	    if($_POST['insert'])
	    {
		    $czy_wpisac = 1;
            $base = new QueriesBase();
            $addElements = $base->getAdditionalColumns();
            
            $_POST['ilosc_tygodni'] = !empty($_POST['ilosc_tygodni']) ? "'".$_POST['ilosc_tygodni']."'" : 'null';
            
		    $query = "INSERT INTO dane_osobowe VALUES (nextval('dane_osobowe_id_seq'),".$_POST['imie_id'].",
            '".$_POST['nazwisko']."',".$_POST['plec_id'].",'".$_POST['data_urodzenia']."',".$_POST['miejsce_ur_id'].",
            ".$_POST['miejscowosc_id'].",'".$_POST['ulica']."','".$_POST['kod']."',".$_POST['wyksztalcenie_id'].",
            ".$_POST['id_gr_zaw'].",(select id from uprawnienia where nazwa_uzytkownika = '".$_SESSION['uzytkownik']."'),
            '".$_POST['data_zgloszenia']."',".$_POST['charakter_id'].",'".$_POST['data_wyjazdu']."',".$_POST['ilosc_tygodni'].",
            ".$_POST['ankieta_id'].",".$_POST['zrodlo_id'].",'".$_POST['nr_obuwia']."');";
		    $database = pg_connect($con_str);
            
		    
		    if(!$_POST['nazwisko'] || !$_POST['data_urodzenia'] || !$_POST['ulica'] || !$_POST['kod'] || !$_POST['id_gr_zaw'] || !$_POST['ilosc_tygodni'] || ($dzis > $_POST['data_wyjazdu']))
            {
                $czy_wpisac = 0;
            }
		    
		    $zapytanie = "select id from uprawnienia where nazwa_uzytkownika = '".$_SESSION['uzytkownik']."'";
		    $wynik = pg_query($database,$zapytanie);
		    $wiersz=pg_fetch_array ($wynik);
		    
		    $zmienna_uzytkowa = $wiersz['id'];
               
		    if ($czy_wpisac == 0)
		    {
			    echo "Dane są niepoprawne ! Zweryfikuj dane i spróbuj wprowadzić osobę poprawnie.";
		    }
		    else
		    {   
			    $wynik = pg_query($database, $query);
			    //echo $query;
			    $query = "select currval('dane_osobowe_id_seq');";
			    $wynik = pg_query($database, $query);
			    $wiersz = pg_fetch_array($wynik);
			    echo "Osoba została zapisana w systemie pod numerem ".$wiersz[0].".";
                $base->setAdditionalColumnsData($wiersz[0], $_POST);
                
                $addInfo = new AdditionalBool($wiersz[0]);
                //dodanie informacji, czy osoba ma osobe tow
                $addInfo->setCompanyInformation();
                
			    if ($_POST['ost_kontakt'])
			    {
				    @$dzis_czas = date("Y-m-d H:i:s");
				    $pytanie = "INSERT INTO kontakt VALUES('".$wiersz[0]."','".$dzis."','".$zmienna_uzytkowa."');";
				    $wynik = pg_query($database,$pytanie);
				    $pytanie1D = "INSERT INTO kontakt_historia VALUES('".$wiersz[0]."','".$dzis_czas."','".$zmienna_uzytkowa."');";
				    $wynik1D = pg_query($database,$pytanie1D);
				    $frage = "select id from status where nazwa = 'Nowy';";
				    $solve = pg_query($database,$frage);
				    $row = pg_fetch_array($solve);
				    $pytanie = "INSERT INTO stat VALUES('".$wiersz[0]."','".$row['id']."');";
				    $wynik = pg_query($database,$pytanie);
			    }
                $location = 'Location: ../edit/przetwarzaj_dane_osobowe.php?id_os='.$wiersz[0].'&edytuj_osobe=1';
			    header($location);
		    }
	    }
        require("../stopka.php");
    }
?>
</html>
