<html>
<link href="css/style.css" rel="stylesheet" type="text/css">
<head>
 <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
 <meta http-equiv="Expires" content="0"/>
 <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate"/>
 <meta http-equiv="Cache-Control" content="post-check=0, pre-check=0"/>
 <meta http-equiv="Pragma" content="no-cache"/>
</head>
<?
	session_start();
    require("conf.php");
	include_once "Spreadsheet/Excel/Writer.php";
	include_once "Spreadsheet/Excel/reader.php";
	include "generuj_excel.php";
	
	//echo $_SESSION['edycja_masowa'];
	$nr_id = substr(str_replace("|",",",$_SESSION['edycja_masowa']),0,strlen($_SESSION['edycja_masowa'])-1);
	if (substr($nr_id, 0, 1) == ",")
	{
		$nr_id = substr($nr_id, 1, strlen($nr_id));
	}
	//echo $nr_id[0];
	$xls = new Spreadsheet_Excel_Writer("korespondencja.xls");
	//$xls->setOutputEncoding('CP1251');
	$format =& $xls->addFormat();
	$format->setBold();
	$format->setColor("blue");
	$sheet =& $xls->addWorksheet('korespondencja');
	//$sheet->setInputEncoding('UTF-8');
	//$sheet->setColumn(0, 0, 12, 0, 0, 0);
	$zapytanie = "select imiona.nazwa, d_o.nazwisko, d_o.ulica, d_o.kod, miejscowosc.nazwa from dane_osobowe d_o join imiona on d_o.id_imie = imiona.id join miejscowosc on d_o.id_miejscowosc = miejscowosc.id where d_o.id in (".$nr_id.") order by d_o.id asc;";
	//create_excel ($IloscKolumn,$nr_id[],$query,$sheet);
	$nag[0] = "Imię";
	$nag[1] = "Nazwisko";
	$nag[2] = "Ulica";
	$nag[4] = "Miejscowo".chr(156)."ć";
	$nag[3] = "Kod";
	create_excel (5,$zapytanie,$sheet, $nag);
	$xls->close();
	echo "<a href='korespondencja.xls'>Pobierz plik xls</a>";
?>
</html>
