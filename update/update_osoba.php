<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
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
	    if ($_POST['update_osoba'])
	    {
            if ($_POST['data'] == "") $_POST['data'] = "null";
            else $_POST['data'] = "'".$_POST['data']."'";
            $dal = dal::getInstance();
            $base = new QueriesBase();
            $addElements = $base->getAdditionalColumns();
            $addElementsData = $base->getAdditionalColumnsData($_POST['id']);
            
            $_POST['ilosc_tyg'] = !empty($_POST['ilosc_tyg']) ? "'".$_POST['ilosc_tyg']."'" : 'null';
            
		    $query = "UPDATE dane_osobowe SET id_imie=".$_POST['imie_id'].",nazwisko='".$_POST['nazwisko']."',id_plec=".$_POST['plec_id'].", 
            data_urodzenia='".$_POST['data_urodzenia']."',id_miejscowosc_ur=".$_POST['miejsce_ur_id'].",id_miejscowosc=".$_POST['miejscowosc_id'].",
            ulica='".$_POST['ulica']."',kod='".$_POST['kod']."',id_wyksztalcenie=".$_POST['wyksztalcenie_id'].", id_zawod=".$_POST['id_gr_zaw'].", 
            id_charakter=".$_POST['charakter_id'].",data=".$_POST['data'].",ilosc_tyg=".$_POST['ilosc_tyg'].",
            id_ankieta=".$_POST['ankieta_id'].",id_zrodlo=".$_POST['zrodlo_id'].", nr_obuwia='".$_POST['nr_obuwia']."' where id = ".$_POST['id'].";"; 
            
            $base->setAdditionalColumnsData($_POST['id'], $_POST);
		    
		    $result2 = $dal->pgQuery($query);
		    $zapytanie = "select id from uprawnienia where nazwa_uzytkownika = '".$_SESSION['uzytkownik']."';";
		    $result = $dal->pgQuery($zapytanie);
		    $row = pg_fetch_array($result);
            
            $addInfo = new AdditionalBool($_POST['id']);
            //dodanie informacji, czy osoba ma osobe tow
            $addInfo->setCompanyInformation();
            
		    $dzis_czas = date("Y-m-d H:i:s");

		    if (isset($_POST['ost_kontakt']))
		    {
			    $pytanie = "update kontakt set data = '".$dzis."', id_konsultant = '".$row['id']."' where id = ".$_POST['id'].";";
			    $Quest = "select id from kontakt where id = ".$_POST['id'].";";
			    $ResQuest = $dal->pgQuery($Quest);
			    if (pg_num_rows($ResQuest) == 0)
			    {
				    $pytanie = "INSERT INTO kontakt VALUES('".$_POST['id']."','".$dzis."','".$row['id']."');";
			    }
			    //$pytanie = "INSERT INTO kontakt VALUES('".$_POST['id']."','".$dzis."','".$row['id']."');";
			    $wynik = $dal->pgQuery($pytanie);
			    $pytanie2 = "select * from kontakt_historia where id = '".$_POST['id']."';";
			    $wynik2 = $dal->pgQuery($pytanie2);
			    if (pg_num_rows($wynik2) < 10) // ????????
			    {
				    $pytanie1 = "INSERT INTO kontakt_historia VALUES('".$_POST['id']."','".$dzis_czas."','".$row['id']."');";
				    $wynik1 = $dal->pgQuery($pytanie1);
			    }
			    else
			    {
				    $pytanie3 = "select min(data) as czas from kontakt_historia where id = '".$_POST['id']."';";
				    $wynik3 = $dal->pgQuery($pytanie3);
				    $w = pg_fetch_array($wynik3);
				    $pytanie4 = "delete from kontakt_historia where id = '".$_POST['id']."' and data = '".$w['czas']."';";
				    $wynik4 = $dal->pgQuery($pytanie4);
				    $pytanie1 = "INSERT INTO kontakt_historia VALUES('".$_POST['id']."','".$dzis_czas."','".$row['id']."');";
				    $wynik1 = $dal->pgQuery($pytanie1);
			    }
		    }

		    echo '<script>wroc();</script>';
	    }
        require("../stopka.php");
    }
?>
</html>
