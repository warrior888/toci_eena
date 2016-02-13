<?php
    session_start();
    $cssFile = 'layout';
    if(false !== stripos($_SERVER['HTTP_USER_AGENT'], 'MSIE'))
        $cssFile .= '_ie'; 
        
?>
<html><head><title>E&A - Baza Danych</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2">
<link href="css/<?php echo $cssFile; ?>.css" rel="stylesheet" type="text/css">
  <script language="javascript" src="js/script.js"></script>
  <script language="javascript" src="js/utils.js"></script>
  <script language="javascript" src="js/validations.js"></script>
  <script>
	function sprzataj(s)
	{
		//alert(s.name);
		var n = document.getElementById("nazwisko");
		var d = document.getElementById("data");
		var k = document.getElementById("kwerenda");
		var wi = document.getElementById("widok");
		var wa = document.getElementById("wakat");
		if (s.name == "szukaj")
		{
			k.selectedIndex = 0;
			wi.selectedIndex = 0;
			wa.selectedIndex = 0;
			s.click();
		}
		if (s.name == "kwerendy_form")
		{
			n.value = "";
			d.value = "";
			wi.selectedIndex = 0;
			wa.selectedIndex = 0;
			s.submit();
		}
		if (s.name == "widok_forma")
		{
			n.value = "";
			d.value = "";
			k.selectedIndex = 0;
			wa.selectedIndex = 0;
			s.submit();
		}
		if (s.name == "wakat_forma")
		{
			n.value = "";
			d.value = "";
			wi.selectedIndex = 0;
			k.selectedIndex = 0;
			s.submit();
		}
		
	}	

  </script>
  
  </head>
<body class="clearBgnd">
<?php
    require_once 'vaElClass.php';
    require_once 'dal/klient.php';  
    
    $controls = new valControl();
    $wakat = new wakaty();
    $klient = new klient();
    $oddzial = new oddzial(); 
    
    if (empty($_SESSION['uzytkownik']))
    {
        //require("log_in.php");
    }
    else
    {
        require("conf.php");
	    $database = pg_connect($con_str);
        echo '<div class="topBgnd"></div><hr class="topHr"/>';
	    echo "<table class='topTable' border='0' align='top' valign='top'><form name = 'szukaj' id = 'szukaj_form' action='szukaj.php' method='POST' target='center'>";
	    echo "<tr>";
	    $qwerty = "select id from uprawnienia where nazwa_uzytkownika = '".$_SESSION['uzytkownik']."';";
	    $solution = pg_query($database,$qwerty);
	    $rov = pg_fetch_array($solution);
	    echo "<td valign='top'>".$controls->AddSeekTextbox("nazwisko", "", "nazwisko", 20, 20)."</td>";

	    echo "<td valign='top'>".$controls->AddDatebox("data", "data", "", 10, 10)."";

	    echo "</td><td valign='top'>";
        echo $controls->AddSubmit('szukaj', 'szukaj_button', 'Szukaj', JsEvents::ONCLICK.'="sprzataj(this);"');
        echo "</form></td><td valign='top'><form method='POST' action='zapytania/zapytania.php' target='center' name='kwerendy_form' id ='kwerendy'>";
        echo $controls->AddHidden('id_id_kwerendy', 'id_kwerendy', '');  
        echo $controls->AddHidden('hidden_kier_sort_osoba', 'hidden_kier_sort_osoba', '');  
        echo $controls->AddHidden('hidden_kol_sort_osoba', 'hidden_kol_sort_osoba', '');  
        $zapytanie = "select id, nazwa from kwerendy where valid != '0' and id_uzytkownik = ".$rov['id']." order by nazwa asc;";
        $wynik = pg_query($database, $zapytanie);
	    echo "<span class='selectContainer'><select name='kwerenda' id='kwerenda' onChange='sprzataj(this.form);'><option>--------</option>";
        echo("<optgroup label=\"Własne\" title=\"Własne\">");

	    while ($wiersz = pg_fetch_array($wynik))
	    {
		    if ($_SESSION['kwerenda'])
		    {
			    if($_SESSION['kwerenda'] == $wiersz['id'])
			    {
				    echo "<option selected value='".$wiersz['id']."'>".$wiersz['nazwa']."</option>";
			    }
			    else
			    {
				    echo "<option value='".$wiersz['id']."'>".$wiersz['nazwa']."</option>";
			    }
		    }
		    else
		    {
			    echo "<option value='".$wiersz['id']."'>".$wiersz['nazwa']."</option>";
		    }
	    }
        echo("</optgroup>");
        echo("<optgroup label=\"Globalne\" title=\"Globalne\">");
        $zapytanie = "select id, nazwa from kwerendy where valid != '0' and id_uzytkownik = (select id from uprawnienia where nazwa_uzytkownika = 'postgres') order by nazwa asc;";
	    $wynik = pg_query($database,$zapytanie);
	    while ($wiersz = pg_fetch_array($wynik))
	    {
		    if ($_SESSION['kwerenda'])
		    {
			    if($_SESSION['kwerenda'] == $wiersz['id'])
			    {
				    echo "<option selected value='".$wiersz['id']."'>".$wiersz['nazwa']."</option>";
			    }
			    else
			    {
				    echo "<option value='".$wiersz['id']."'>".$wiersz['nazwa']."</option>";
			    }
		    }
		    else
		    {
			    echo "<option value='".$wiersz['id']."'>".$wiersz['nazwa']."</option>";
		    }
	    }
        echo("</optgroup>");
	    echo "</select></span></td></form>";
	    echo "<td valign='top'><form method='POST' action='zapytania/edit_zapytan.php' target='center'>";
        echo $controls->AddHidden('id_ed_id_kwerendy', 'id_kwerendy', '');
	    if (isset($_SESSION['edycja_rekordu']))
	    {
		    echo $controls->AddSubmit('edycja_zapytan', 'id_edycja_zapytan', 'Edycja', ''); 
	    }

	    echo "</form></td><td valign='top'><form method='POST' action='zapytania/tworzenie_zapytan.php' target='center'>";
	    if (isset($_SESSION['dodawanie_kwerendy']))
	    {
            echo $controls->AddSubmit('tworzenie_zapytan', 'id_tworzenie_zapytan', 'Nowe pytanie', '');
	    }
	    echo "</form></td><form name = 'widok_forma' id = 'widoki_select' method='GET' action='widoki/widok.php' target='center'><td valign='top'> ";
        echo $controls->AddHidden('id_id_widoku', 'id_widoku', '');
	    echo "<span class='selectContainer'><select name='widok' id = 'widok' onChange='sprzataj(this.form);'><option>--------";
	    $zapytanie = "select id, nazwa from widoki order by nazwa asc;";
	    $wynik = pg_query($database,$zapytanie);
	    while ($wiersz = pg_fetch_array($wynik))
	    {
		    echo "<option value='".$wiersz['id']."'>".$wiersz['nazwa']."</option>";
	    }
	    echo "</select></span></td><td valign='top'>";
	    if (isset($_SESSION['edycja_rekordu']))
	    {
		    echo $controls->AddSubmit('edycja_widokow', 'id_edycja_widokow', 'Edycja', '');
	    }
	    echo "</td></form>";
	//</form></td>
	echo "<td valign='top'>";
	echo "<form name = 'wakat_forma' id = 'wakaty_select' method='GET' action='wakaty.php' target='center'>";
    echo $controls->AddHidden('id_id_wakatu', 'id_wakatu', '');
	echo "<span class='selectContainer'><select name='wakat' id = 'wakat' onChange='sprzataj(this.form);'><option>--------";
	//$zapytanie = "select wakaty.id, klienci.nazwa, wakaty.data_wyjazdu, wakaty.ilosc_kobiet, wakaty.ilosc_mezczyzn, wakaty.ilosc_tyg, wakaty.dokladny from wakaty join klienci on wakaty.id_klient = klienci.id where wakaty.data_wyjazdu >= '".date("Y-m-d")."' order by wakaty.data_wyjazdu asc;";
    $zapytanie = "SELECT ".$wakat->tableName.".".$wakat->tableId.", 
    (".$klient->tableName.".nazwa_alt || ', ') || ".$oddzial->tableName.".nazwa AS nazwa, 
    ".$wakat->tableName.".data_wyjazdu, ".$wakat->tableName.".ilosc_kobiet, 
    ".$wakat->tableName.".ilosc_mezczyzn, ".$wakat->tableName.".ilosc_tyg, ".$wakat->tableName.".dokladny 
    FROM ".$wakat->tableName." 
    JOIN ".$klient->tableName." ON ".$wakat->tableName.".id_klient = ".$klient->tableName.".id 
    JOIN ".$oddzial->tableName." ON ".$oddzial->tableName.".id = ".$wakat->tableName.".id_oddzial 
    WHERE ".$wakat->tableName.".data_wyjazdu >= '".date("Y-m-d")."' 
    order by ".$wakat->tableName.".data_wyjazdu ASC;";
	$wynik = pg_query($database,$zapytanie);
	while ($wiersz = pg_fetch_array($wynik))
	{
		$Niekoniecznie = "";
        if ($wiersz['dokladny'] == "t")
        {
            $Niekoniecznie= ", N";
        }
		echo "<option value='".$wiersz['id']."'>".$wiersz['data_wyjazdu'].", ".$wiersz['nazwa'].", ";
		if ($wiersz['ilosc_kobiet'] > 0)
		{
			echo "".$wiersz['ilosc_kobiet']."K, ";
		}
		if ($wiersz['ilosc_mezczyzn'] > 0)
		{
			echo "".$wiersz['ilosc_mezczyzn']."M, ";
		}
		echo "".$wiersz['ilosc_tyg']."Tyg".$Niekoniecznie."</option>";
	}
	echo "</select></span></td>&nbsp;&nbsp;
	<td valign='top'></form></td>
	</form></td></tr></table>
	";
    }
	//require("stopka.php");
?>
</body>
</html>
