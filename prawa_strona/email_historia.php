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
        
	    $query = "select id,id_imie,nazwisko from dane_osobowe WHERE id = '".$id_osoba."';";
		$database = pg_connect($con_str);
		$wynik = pg_query($database, $query);
		if (pg_num_rows($wynik) == 0)
		{
			echo "Osoba w chwili obecnej nie znajduje siê ju¿ w bazie, musia³a dopiero co zostaæ usuniêta !?";
		}
		else
		{   
            $wiersz = pg_fetch_array($wynik);
            $zapytanie = "select nazwa from imiona where id = '".$wiersz['id_imie']."';";
	        $database = pg_connect($con_str);
	        $result = pg_query($database, $zapytanie);
	        $row = pg_fetch_array($result);
            echo("<form method=\"POST\" action='".$_SERVER['PHP_SELF']."'><table align = 'CENTER'>");
            echo $controls->AddHidden(ID_OSOBA, ID_OSOBA, $id_osoba); 
            echo("<tr>");
            echo("<td>Imie: </td><td>");
            echo $controls->AddTextbox('imie', 'id_imie', $row['nazwa'], '20', '20', 'readonly');
            echo("</td></tr><tr>");
            echo("<td>Nazwisko: </td><td>");
            echo $controls->AddTextbox('nazwisko', 'id_nazwisko', $wiersz['nazwisko'], '20', '20', 'readonly');
            echo("</td></tr></table></form>");
	        $zapytanie2 = "select uprawnienia.imie_nazwisko as konsultant, email_historia.data_wpisu as data, email_historia.tresc from email_historia join uprawnienia on uprawnienia.id = email_historia.id_konsultant where email_historia.id = '".$id_osoba."' order by email_historia.data_wpisu desc;";
	        $result2 = pg_query($database, $zapytanie2);
	        $ile = pg_num_rows($result2);
	        while ($row2 = pg_fetch_array($result2))
	        {
                $data[] = $row2['data'];
                $konsultant[] = $row2['konsultant'];
                $u[] = $row2['tresc'];
            }
	        echo("<form method=\"POST\" action='".$_SERVER['PHP_SELF']."'><table align = \"CENTER\"><tr>");
            echo $controls->AddHidden(ID_OSOBA, ID_OSOBA, $id_osoba); 
            echo $controls->AddHidden('id_ile','ile','');
	        if ($ile != 0)
            {
                for ($j = 0; $j < ($ile / 10); $j++)
                {
                    echo "<td>";
                    echo $controls->AddSubmit($j, 'id', ($j + 1), JsEvents::ONCLICK.'="ile.value = this.name;"');
                    echo "</td>";
                }
                echo("</tr></table>");
                if (isset($_POST['ile']))
                {
                    echo("<table align = \"CENTER\" border = \"1\">");
                    $p = $_POST['ile'] * 10;
                    $k = $_POST['ile'] * 10 + 10;
                    if ($k > $ile)
                    {
                        $k = $ile;
                    }
		    echo("<tr><td nowrap>Lp.</td><td nowrap>Data wpisu</td><td nowrap>Konsultant</td><td nowrap>Tresc</td></tr>");
                    for ($i = $p; $i < $k; $i++)
                    {
                        echo("<tr><td nowrap>".($i + 1)."</td><td nowrap>".$data[$i]."</td><td nowrap>".$konsultant[$i]."</td><td nowrap>".$u[$i]."</td></tr>");
                    }
                    echo("</table>");
                }
                else
                {
                    echo("<table align = \"CENTER\" border = \"1\">");
                    $p = $_POST['ile'] * 10;
                    $k = $_POST['ile'] * 10 + 10;
                    if ($k > $ile)
                    {
                        $k = $ile;
                    }
		    echo("<tr><td nowrap>Lp.</td><td nowrap>Data wpisu</td><td nowrap>Konsultant</td><td nowrap>Tresc</td></tr>");
                    for ($i = $p; $i < $k; $i++)
                    {
                        echo("<tr><td nowrap>".($i + 1)."</td><td nowrap>".$data[$i]."</td><td nowrap>".$konsultant[$i]."</td><td nowrap>".$u[$i]."</td></tr>");
                    }
                    echo("</table>");
                }
            }
	        echo("</form>");
	        echo "<table align = 'CENTER'><tr><td>";
            echo $controls->AddSubmit('Zamknij', 'id_Zamknij', 'Zamknij', JsEvents::ONCLICK.'="window.close();"');
            echo "</td></tr></table>";
        }
    }
    CommonUtils::sendOutputBuffer();
?>
</html>
