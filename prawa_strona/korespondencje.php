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
        $id_osoba = Utils::PodajIdOsoba();
        $controls = new valControl();
        
        $query = "select id,id_imie,nazwisko from dane_osobowe WHERE id = '".$id_osoba."';";
		$database = pg_connect($con_str);
		$wynik = pg_query($database, $query);
		if (pg_num_rows($wynik) == 0)
		{
			echo "Osoba w chwili obecnej nie znajduje siê ju¿ w bazie, musia³a dopiero co zostaæ usuniêta !?";
		}
		else
		{   
	        $zap_insert = "select nazwa from rodzaj_korespondencji;";
            $q_insert = pg_query($database, $zap_insert);
            while ($r_insert = pg_fetch_array($q_insert))
            {
                if (isset($_POST[$r_insert['nazwa']]))
                {
                    if (strtolower($r_insert['nazwa']) != "zettel")
                    {
                        $zapytanie_insert = "insert into korespondencje values ('".$id_osoba."', (select id from rodzaj_korespondencji where nazwa = '".$r_insert['nazwa']."'), (select id from uprawnienia where nazwa_uzytkownika = '".$_SESSION['uzytkownik']."'), '".date("Y-m-d")."');";
                        $query_insert = pg_query($database, $zapytanie_insert);
                    } 
                    else
                    {
                        if (trim($_POST['tydz']) != "")
                        {
                            $database = pg_connect($con_str);
                            $zapytanie = "select tydzien from zettel where id = '".$id_osoba."' and tydzien = '".addslashes($_POST['tydz'])."' and rok = '".date("Y")."';";
                            $query = pg_query($database, $zapytanie);
                            if (pg_num_rows($query) == 0)
                            {
                                $zapytanie_insert = "insert into zettel values ('".$id_osoba."', (select id from uprawnienia where nazwa_uzytkownika = '".$_SESSION['uzytkownik']."'), '".date("Y-m-d")."', '".addslashes($_POST['tydz'])."', '".date("Y")."');";
                                $zapytanie_insert .= "insert into korespondencje values ('".$id_osoba."', (select id from rodzaj_korespondencji where nazwa = '".$r_insert['nazwa']."'), (select id from uprawnienia where nazwa_uzytkownika = '".$_SESSION['uzytkownik']."'), '".date("Y-m-d")."');";
                                $query_insert = pg_query($database, $zapytanie_insert);
                                //echo("<script>wroc();</script>");
                            }
                            else
                            {
                                echo("<div align = 'CENTER'>Zettel z tygodnia {$_POST['tydz']} jest ju¿ wpisany</div>");
                            }
                        }   
                    }
                }   
            }
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
            echo("<td>Nazwisko: </td><td> ");
            echo $controls->AddTextbox('nazwisko', 'id_nazwisko', $wiersz['nazwisko'], '20', '20', 'readonly');
            echo("</td></tr></table>");
	        $zapytanie3 = "select nazwa from rodzaj_korespondencji order by nazwa asc;";
	        $result3 = pg_query($database, $zapytanie3);
	        echo '<table class="gridTable" border="0" cellspacing="0">';
	        while ($row3 = pg_fetch_array($result3))
	        {
                if (strtolower($row3['nazwa']) != "zettel")
                {
		            echo "<tr><td>"; 
                    echo $controls->AddCheckbox($row3['nazwa'], 'id', '', JsEvents::ONCLICK.'="this.form.submit();"');
                    echo "</td><td>".$row3['nazwa']."</td></tr>";
                }
                else
                {
                    echo "<tr><td>";
                    echo $controls->AddCheckbox($row3['nazwa'], 'id', '', JsEvents::ONCLICK.'="this.form.submit();"');
                    echo "</td><td>".$row3['nazwa']."</td><td>Tydzieñ: ";
                    echo $controls->AddTextbox('tydz', 'id_tydz', '', '20', '20', '');
                    echo "</td></tr>";
                }
	        }
	        echo("</table>");
	        $zapytanie1 = "select imie_nazwisko from uprawnienia where nazwa_uzytkownika = '".$_SESSION['uzytkownik']."';";
	        $result1 = pg_query($database, $zapytanie1);
	        $row1 = pg_fetch_array($result1);
            echo("<table align = \"CENTER\"><tr><td>Konsultant: ".$row1['imie_nazwisko']."</td></tr></table>");
            echo("<table align = \"CENTER\"><tr><td>Data wpisu: ".$dzis."</td></tr>");
            echo("</table></form>");
            
	        $zapytanie2 = "select rodzaj_korespondencji.nazwa as korespondencja, uprawnienia.imie_nazwisko as konsultant, korespondencje.data_korespondencji as data from korespondencje join rodzaj_korespondencji on rodzaj_korespondencji.id = korespondencje.id_korespondencji join uprawnienia on uprawnienia.id = korespondencje.id_konsultant where korespondencje.id = '".$id_osoba."' order by korespondencje.data_korespondencji desc;";
	        $result2 = pg_query($database, $zapytanie2);
	        $ile = pg_num_rows($result2);
	        while ($row2 = pg_fetch_array($result2))
	        {
                $data[] = $row2['data'];
                $konsultant[] = $row2['konsultant'];
                $u[] = $row2['korespondencja'];
            }
	        echo("<form method=\"POST\" action='".$_SERVER['PHP_SELF']."'><table align = \"CENTER\"><tr>");
            echo $controls->AddHidden(ID_OSOBA, ID_OSOBA, $id_osoba);
            echo $controls->AddHidden('id_ile', 'ile', '');
	        if ($ile != 0)
            {
                for ($j = 0; $j < ($ile / 10); $j++)
                {
                    echo "<td>";
                    echo $controls->AddSubmit($j, 'id', ($j + 1), JsEvents::ONCLICK.'="ile.value = this.name;"');
                    echo "</td>";
                }
                echo("</tr></table></form>");
                echo '<table class="gridTable" border="0" cellspacing="0">';
                echo '<tr><th nowrap>Lp.</th><th nowrap>Data korespondencji</th><th nowrap>Konsultant</th><th nowrap>Rodzaj korespondencji</th></tr>'; 
                if (isset($_POST['ile']))
                {
                    $p = $_POST['ile'] * 10;
                    $k = $_POST['ile'] * 10 + 10;
                }
                else
                {
                    $p = 0;
                    $k = 10;
                }
                
                if ($k > $ile)
                {
                    $k = $ile;
                }
		        
                for ($i = $p; $i < $k; $i++)
                {
                    $css = (($i % 2) == 0) ? 'oddRow' : 'evenRow';
                    echo("<tr class='".$css."'><td nowrap>".($i + 1)."</td><td nowrap>".$data[$i]."</td><td nowrap>".$konsultant[$i]."</td><td nowrap>".$u[$i]."</td></tr>");
                }
                echo '</table>';
                
            }
            unset($data);
            unset($konsultant);
            unset($u);
            $zapytanie2 = "select zettel.id, uprawnienia.imie_nazwisko as konsultant, zettel.data_korespondencji as data, zettel.tydzien from zettel join uprawnienia on zettel.id_konsultant = uprawnienia.id where zettel.id = '".$id_osoba."' order by zettel.data_korespondencji desc;";
	        $result2 = pg_query($database, $zapytanie2);
	        $ile = pg_num_rows($result2);
	        while ($row2 = pg_fetch_array($result2))
	        {
                $data[] = $row2['data'];
                $konsultant[] = $row2['konsultant'];
                $u[] = $row2['tydzien'];
            }
	        echo("<form method=\"POST\" action='".$_SERVER['PHP_SELF']."'><table align = \"CENTER\"><tr>");
            echo $controls->AddHidden(ID_OSOBA, ID_OSOBA, $id_osoba);
            echo $controls->AddHidden('id_ile', 'ile', '');
	        if ($ile != 0)
            {
                for ($j = 0; $j < ($ile / 10); $j++)
                {
                    echo "<td>";
                    echo $controls->AddSubmit($j, 'id', ($j + 1), JsEvents::ONCLICK.'="ile.value = this.name;"');
                    echo "<td>";
                }
                echo("</tr></table></form>");
                echo '<table class="gridTable" border="0" cellspacing="0">';
                echo '<tr><th nowrap>Lp.</th><th nowrap>Data korespondencji</th><th nowrap>Konsultant</th><th nowrap>Tydzieñ</th></tr>';
                if (isset($_POST['ile']))
                {

                    $p = $_POST['ile'] * 10;
                    $k = $_POST['ile'] * 10 + 10;
                }
                else
                {
                    $p = 0;
                    $k = 10;
                }
                
                if ($k > $ile)
                {
                    $k = $ile;
                }
		        
                for ($i = $p; $i < $k; $i++)
                {
                    $css = (($i % 2) == 0) ? 'oddRow' : 'evenRow';
                    echo("<tr class='".$css."'><td nowrap>".($i + 1)."</td><td nowrap>".$data[$i]."</td><td nowrap>".$konsultant[$i]."</td><td nowrap>".$u[$i]."</td></tr>");
                }
                echo("</table>");
            }
	        echo "<table align='CENTER'><tr><td>";
            echo $controls->AddSubmit('Zamknij', 'id_Zamknij', 'Zamknij', JsEvents::ONCLICK.'="window.close();"');
            echo "</td></tr></table>";
        }
    }
    CommonUtils::sendOutputBuffer();
?>
</html>