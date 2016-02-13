<?php
    require_once '../conf.php';
    CommonUtils::outputBufferingOn();
    CommonUtils::SessionStart();

    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
        $id_osoba = Utils::PodajIdOsoba();
        $controls = new valControl();
        $database = pg_connect($con_str);
        $query = "select imie, nazwisko from dane_osobowe WHERE id = ".$id_osoba.";";
		$wynik = pg_query($database, $query);
		if (pg_num_rows($wynik) == 0)
		{
            echo '<html>';
            HelpersUI::addHtmlBasicHeaders(HelpersUI::PATH_LEVEL_1);
            echo '<body>';
			echo "Osoba w chwili obecnej nie znajduje siê ju¿ w bazie, musia³a dopiero co zostaæ usuniêta !?";
		}
		else
		{
            $wiersz = pg_fetch_array($wynik);

            if (isset($_POST['id_id_usun_p']))
            {
			    $z = "delete from preferencje where id_klient = '".$_POST['id_id_usun_p']."' and id = '".$id_osoba."';";
	    		$result = pg_query($database, $z);
                header('Location: '.$_SERVER['PHP_SELF'].'?'.ID_OSOBA.'='.$id_osoba);
            }
            if (isset($_POST['id_id_usun_a']))
            {
			    $z = "delete from antypatie where id_klient = '".$_POST['id_id_usun_a']."' and id = '".$id_osoba."';";
	    		$result = pg_query($database, $z);
                header('Location: '.$_SERVER['PHP_SELF'].'?'.ID_OSOBA.'='.$id_osoba);
		    }

		    if (isset($_POST['klient_p_id']))
		    {
			    $z = "insert into preferencje values ('".$id_osoba."', ".$_POST['klient_p_id'].");";
	    		$result = pg_query($database, $z);
                header('Location: '.$_SERVER['PHP_SELF'].'?'.ID_OSOBA.'='.$id_osoba);
		    }

		    if (isset($_POST['klient_a_id']))
		    {
			    $z = "insert into antypatie values ('".$id_osoba."', ".$_POST['klient_a_id'].");";
	    		$result = pg_query($database, $z);
                header('Location: '.$_SERVER['PHP_SELF'].'?'.ID_OSOBA.'='.$id_osoba);
		    }
		
            echo '<html>';
            HelpersUI::addHtmlBasicHeaders(HelpersUI::PATH_LEVEL_1);
            echo '<body>';
        
		    $preferencje = $controls->dalObj->PobierzDane("select preferencje.id_klient as id, klient.nazwa as klient from preferencje join klient on preferencje.id_klient = klient.id where preferencje.id = '".$id_osoba."' order by klient.nazwa asc;");

            $j = array(1);
            if(is_array($preferencje))
	        foreach ($preferencje as $preferencja)
	        {
		        $j[] = $preferencja['id'];
	        }
	        $antypatie = $controls->dalObj->PobierzDane("select antypatie.id_klient as id, klient.nazwa as klient from antypatie join klient on antypatie.id_klient = klient.id where antypatie.id = '".$id_osoba."' order by klient.nazwa asc;");
            if(is_array($antypatie))
	        foreach ($antypatie as $antypatia)
	        {
		        $j[] = $antypatia['id'];
	        }
		    $temp = implode(',', $j);
		    if ($temp == "")
			    $temp = "''";
		    //echo("$temp");
		    $listaKlientow = $controls->dalObj->PobierzDane("select id, nazwa from klient where id not in (".$temp.") order by nazwa asc;");

            echo "<table>";
            echo '<tr><td>Imie: </td><td>';
            echo $controls->AddTextbox('imie', 'id_imie', $wiersz['imie'], '20', '20', 'readonly');
            echo '</td></tr><tr><td>Nazwisko: </td><td>';
            echo $controls->AddTextbox('nazwisko', 'id_nazwisko', $wiersz['nazwisko'], '20', '20', 'readonly');
	        echo '</td></tr><tr><td>Zainteresowany:</td></tr>';
            
            echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
            echo $controls->AddHidden('id_id_usun_p', 'id_id_usun_p', '');
            echo $controls->AddHidden(ID_OSOBA, ID_OSOBA, $id_osoba);
            if(is_array($preferencje))
	        foreach ($preferencje as $preferencja)
	        {
		        echo "<tr><td>".$preferencja['klient']."</td><td>";
                echo $controls->AddSubmit('usun_p', 'id_usun', 'Usuñ', JsEvents::ONCLICK.'="id_id_usun_p.value='.$preferencja['id'].';"');
                echo "</td></tr>";
	        }
            echo '</form><form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
            echo $controls->AddHidden(ID_OSOBA, ID_OSOBA, $id_osoba);
	        echo("<tr><td>");
            echo $controls->AddSelectWithData('klient_p', 'klient_p', '', $listaKlientow, null, 'klient_p_id', '');
	        echo("</td><td>");
            echo $controls->AddSubmit('dodaj_klienta_p', 'id_dodaj_klienta_p', 'Dodaj', 'onclick="//this.disabled = true; "');
	        echo("</td></tr>");
            echo '</form>';
            
	        echo ("<tr><td>Nie zainteresowany:</td></tr>");
            echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
            echo $controls->AddHidden(ID_OSOBA, ID_OSOBA, $id_osoba);
            echo $controls->AddHidden('id_id_usun_a', 'id_id_usun_a', '');
            if(is_array($antypatie))
	        foreach ($antypatie as $antypatia)
	        {
		        echo"<tr><td>".$antypatia['klient']."</td><td>";
                echo $controls->AddSubmit('usun_a', 'id_usun', 'Usuñ', JsEvents::ONCLICK.'="id_id_usun_a.value = '.$antypatia['id'].';"');
                echo "</td></tr>";
	        }
            echo '</form><form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
            echo $controls->AddHidden(ID_OSOBA, ID_OSOBA, $id_osoba);
	        echo("<tr><td>");
            echo $controls->AddSelectWithData('klient_a', 'klient_a', '', $listaKlientow, null, 'klient_a_id', '');
	        echo("</td><td>");
            echo $controls->AddSubmit('dodaj_klienta_a', 'id_dodaj_klienta_a', 'Dodaj', 'onclick="//this.disabled = true; "');
	        echo("</td></tr></form></table>");
	        echo("<table align = 'CENTER'><tr><td>");
            echo $controls->AddSubmit('Zamknij', 'id_Zamknij', 'Zamknij', JsEvents::ONCLICK.'="window.close();"');
	        echo("</td></tr></table>");
      }
    }
    CommonUtils::sendOutputBuffer();
?>
</html>
