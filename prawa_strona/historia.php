<?php
    require_once '../conf.php';
    CommonUtils::outputBufferingOn();
    CommonUtils::SessionStart();

    echo '<html>';
    HelpersUI::addHtmlBasicHeaders(HelpersUI::PATH_LEVEL_1);
    echo '<body>';
    
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
        $controls = new valControl();
        
        $id_osoba = Utils::PodajIdOsoba();
        
	    $query = "select id, imie,nazwisko from dane_osobowe WHERE id = '".$id_osoba."';";
		$database = pg_connect($con_str);
		$wynik = pg_query($database, $query);
		if (pg_num_rows($wynik) == 0)
		{
			echo "Osoba w chwili obecnej nie znajduje siê ju¿ w bazie, musia³a dopiero co zostaæ usuniêta !?";
		}
		else
		{   
            $wiersz = pg_fetch_array($wynik);

            echo "<form method=\"POST\" action='".$_SERVER['PHP_SELF']."'><table class='gridTable' border='0' cellspacing='0' align='center'>";
            echo $controls->AddHidden(ID_OSOBA, ID_OSOBA, $id_osoba);
            echo("<tr>");
            echo("<td>Imie: </td><td>");
            echo $controls->AddTextbox('imie', 'id_imie', $wiersz['imie'], '20', '20', 'readonly');
            echo("</td></tr><tr>");
            echo("<td>Nazwisko: </td><td>");
            echo $controls->AddTextbox('nazwisko', 'id_nazwisko', $wiersz['nazwisko'], '20', '20', 'readonly');
            echo("</td></tr></table></form>");
	        $zapytanie2 = "select uprawnienia.imie_nazwisko as konsultant, kontakt_historia.data as data from kontakt_historia join uprawnienia on uprawnienia.id = kontakt_historia.id_konsultant where kontakt_historia.id = '".$id_osoba."' order by kontakt_historia.data desc;";
	        $result2 = pg_query($database, $zapytanie2);
	        echo("<table align = 'CENTER'><tr><td>");
	        echo("<table class='gridTable' border='0' cellspacing='0' align='center'>");
            echo '<tr><th colspan="3" align="center">Kontakty</th></tr>';
	        echo("<tr><th>Lp.</th><th>Konsultant</th><th>Data kontaktu</th></tr>");
	        $l = 1;
	        while ($row2 = pg_fetch_array($result2))
	        {
                $css = (($l % 2) == 0) ? 'oddRow' : 'evenRow';
		        echo("<tr class='".$css."'><td>".$l."</td><td>".$row2['konsultant']."</td><td>".$row2['data']."</td></tr>");
		        $l++;
	        }
	        echo("</table></td><td>");
	        echo("<table class='gridTable' border='0' cellspacing='0' align='center'>");
            echo '<tr><th colspan="4" align="center">Masowe zmiany statusów</th></tr>';
	        echo("<tr><th>Lp.</th><th>Konsultant</th><th>Data</th><th>Status</th></tr>");
	        $l = 1;
	        $zapytanie3 = "select uprawnienia.imie_nazwisko as konsultant, status_historia.czas as data, status.nazwa as status from status_historia join uprawnienia on uprawnienia.id = status_historia.id_konsultant join status on status.id = status_historia.id_status where status_historia.id = '".$id_osoba."' order by status_historia.czas desc;";
	        $result3 = pg_query($database, $zapytanie3);
	        while ($row3 = pg_fetch_array($result3))
	        {
                $css = (($l % 2) == 0) ? 'oddRow' : 'evenRow'; 
		        echo("<tr class='".$css."'><td>".$l."</td><td>".$row3['konsultant']."</td><td>".$row3['data']."</td><td>".$row3['status']."</td></tr>");
		        $l++;
	        }
	        echo("</table></td><td>");
	        
	        echo("</td></tr></table>");
	        echo "<table align = 'CENTER'><tr><td>";
            echo $controls->AddSubmit('Zamknij', 'id_Zamknij', 'Zamknij', JsEvents::ONCLICK.'="window.close();"');
            echo "</td></tr></table>";
        }
    }
    CommonUtils::sendOutputBuffer();
?>
</html>
