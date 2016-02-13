<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="js/script.js"></script>
<link href="css/style.css" rel="stylesheet" type="text/css"></head>
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

	require("../oblicz_date.php");
	require("oblicz_wiek.php");
	$query = "select id,id_imie,nazwisko from dane_osobowe WHERE id = '".$_GET['id_os']."';";
		$database = pg_connect($con_str);
		@$dzis = date(Y."-".m."-".d);
		$wynik = pg_query($database, $query);
		if (pg_num_rows($wynik) == 0)
		{
			echo "Osoba w chwili obecnej nie znajduje się już w bazie, musiała dopiero co zostać usunięta !?";
		}
		else
		{   
			echo("<body onLoad = 'window.print();'>");
			
			$data = explode("-",$_GET['data_wyjazdu']);
			$tydzien_pozniej = oblicz_date_bez_przes($data[0],$data[1],$data[2],1);
			$zapytanie = "select imiona.nazwa as imie, d_o.nazwisko as nazwisko, dokumenty.pass_nr, bilety.cena, msc_odjazdu.nazwa as msc_odjazdu, msc_biura.nazwa as msc_biura from dane_osobowe d_o 
			join imiona on d_o.id_imie = imiona.id 
			join dokumenty on d_o.id = dokumenty.id 
			join zatrudnienie on d_o.id = zatrudnienie.id_osoba 
			join bilety on zatrudnienie.id_bilet = bilety.id 
			join msc_odjazdu on zatrudnienie.id_msc_odjazd = msc_odjazdu.id
			join oddzialy_klient on zatrudnienie.id_oddzial = oddzialy_klient.id
			JOIN miejscowosc_biuro ON oddzialy_klient.id_biuro = miejscowosc_biuro.id
	                JOIN msc_biura ON miejscowosc_biuro.id_msc_biuro = msc_biura.id
			where d_o.id = ".$_GET['id_os']." and zatrudnienie.data_wyjazdu = '".$_GET['data_wyjazdu']."';";
			//echo $zapytanie;
			$result = pg_query($database, $zapytanie);
			$row = pg_fetch_array($result);
			echo("<div align = 'LEFT'><h3>E&A sp. z o.o.</h3></div>");
			echo("<div align = 'RIGHT'><h3>Opole, dnia ".$dzis.".</h3></div>");
			echo("<br /><br /><h2><div align = 'CENTER'>Oświadczenie</div></h2>");
			echo("<br /><br /><div align = 'LEFT'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Ja, niżej podpisany(a) ".$row['imie']." ".$row['nazwisko']."
			legitymujący(a) się paszportem o nr ".$row['pass_nr']." zgadzam się na potrącenie mi kwoty ".$row['cena']." za bilet trasa
			".$row['msc_odjazdu']." - ".$row['msc_biura'].". Wyjazd ".$_GET['data_wyjazdu'].", odbiór paszportu w biurze ".$tydzien_pozniej.".</div>");
			echo("<br /><div align = 'LEFT'>Podpis osoby składającej oświadczenie:</div>");
			echo("<br /><div align = 'LEFT'>........................................................</div>");
			echo("<br /><div align = 'LEFT'>Podpis osoby przyjmującej oświadczenie:</div>");
			echo("<br /><div align = 'LEFT'>........................................................</div>");
			
			
			echo("</body>");
        	}
        require("../stopka.php");
    }
?>
</html>
