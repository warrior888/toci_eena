<?php session_start(); ?>
<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
<link href="../css/layout.css" rel="stylesheet" type="text/css">
</head>
<?php
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
        
    require("../naglowek.php");
    require '../conf.php';
    include("../prawa_strona/f_image_operations.php");
        require_once 'adl/Person.php';
        
    $controls = new valControl();
	$database = pg_connect($con_str);
    if (isset($_POST['kasuj']))
    {
        $delete_q = "delete from dane_internet where id = ".$_POST['id_os_int'].";";
        $res = pg_query($database, $delete_q);
    }
	if (isset($_POST['wprowadz']))
	{
		$testquery = "select id from dane_osobowe where data_urodzenia = (select data_urodzenia from dane_internet where id = ".$_POST['id_os_int'].") and lower(nazwisko) = (select lower(nazwisko) from dane_internet where id = ".$_POST['id_os_int'].") and id_imie = (select id_imie from dane_internet where id = ".$_POST['id_os_int'].");";
		$res = pg_query($database, $testquery);
		if (pg_num_rows($res) > 0)
		{
			echo "Kandydat jest już w systemie :P.";
		}
		else
		{
            $idOsoba = (int)$_POST['id_os_int'];
            
            $person = new Person(null);
            $candidate = new Candidate($idOsoba);
            
            $result = $person->setPersonFromCandidate($candidate, User::getInstance()->getUserId(), isset($_POST['sms'.$idOsoba]));
            
            if ($result)
				echo 'Operacja powiodła się. <br />';
			
            if ($person->IsSmsSent())
                echo 'Wysłano sms. <br />';
		}
	}
    if (isset($_POST['edytuj']))
    {
        $zapytanie = "select * from osoba_internet_pokaz where id = ".$_POST['id_os_int'].";";
        $respokaz = pg_query($database, $zapytanie);
        $rowpokaz = pg_fetch_array($respokaz);
        $odlamki_nag = explode(",","Id:, Imię:, Nazwisko:, Płeć:, Data urodzenia:, Miejsce ur.:, Miejscowość:, Ulica:, Kod:, Telefon:, Komórka:, Inny telefon:, E-mail:, Wykształcenie:, Zawód:, Data zgłoszenia:, Charakter pracy:, Termin wyjazdu:, Ilość tygodni:, Informacja:");
        $i = 0;
        echo "<table><tr>"; 
        echo "<td><table>";
        while(isset($rowpokaz[$i]))
        {
            echo "<tr><td>".$odlamki_nag[$i]."</td><td>".$rowpokaz[$i]."</td></tr>";
            $i++;
        }
        echo "</table></td>";
        $zapytanie = "select jezyk, poziom from osoba_internet_jezyk where id = ".$_POST['id_os_int'].";";
        $respokaz = pg_query($database, $zapytanie);

        $odlamki_nag = explode(",","Język:, Poziom:");
        echo "<td valign='top'><table>";
        while ($rowpokaz = pg_fetch_array($respokaz))
        {
            $i = 0;
            while(isset($rowpokaz[$i]))
            {
                echo "<tr><td>".$odlamki_nag[$i]."</td><td>".$rowpokaz[$i]."</td></tr>";
                $i++;
            }
        }
        echo "</table></td>";
        $zapytanie = "select prawko from osoba_internet_prawko where id = ".$_POST['id_os_int'].";";
        $respokaz = pg_query($database, $zapytanie);

        $odlamki_nag = explode(",","Kategoria:");
        echo "<td valign='top'><table>";
        while ($rowpokaz = pg_fetch_array($respokaz))
        {
            $i = 0;
            while(isset($rowpokaz[$i]))
            {
                echo "<tr><td>".$odlamki_nag[$i]."</td><td>".$rowpokaz[$i]."</td></tr>";
                $i++;
            }
        }
        echo "</table></td>";
        $zapytanie = "select nazwa from poprzedni_pracodawca_ankieta where id = ".$_POST['id_os_int'].";";
        $respokaz = pg_query($database, $zapytanie);

        $odlamki_nag = explode(",","Poprzedni pracodawca:");
        echo "<td valign='top'><table>";
        while ($rowpokaz = pg_fetch_array($respokaz))
        {
            $i = 0;
            while(isset($rowpokaz[$i]))
            {
                echo "<tr><td>".$odlamki_nag[$i]."</td><td>".$rowpokaz[$i]."</td></tr>";
                $i++;
            }
        }
        echo "</table></td>";
        echo "</tr></table>";
        $zapytanie = "select umiejetnosc.nazwa from umiejetnosci_osob_internet join umiejetnosc on umiejetnosc.id = umiejetnosci_osob_internet.id_umiejetnosc where umiejetnosci_osob_internet.id = ".$_POST['id_os_int'].";";
        $respokaz = pg_query($database, $zapytanie);

        $odlamki_nag = explode(",","Dodatkowe umietętności:");
        echo "<td valign='top'><table>";
        while ($rowpokaz = pg_fetch_array($respokaz))
        {
            $i = 0;
            while(isset($rowpokaz[$i]))
            {
                echo "<tr><td>".$odlamki_nag[$i]."</td><td>".$rowpokaz[$i]."</td></tr>";
                $i++;
            }
        }
        echo "</table></td>";
        echo "</tr></table>";
    }
	else
	{
		$zapytanie = "
        select o_int1.*, kod_filia.nazwa as button_label, 
        (select id from dane_osobowe where data_urodzenia = o_int1.data_urodzenia and lower(nazwisko) = lower(o_int1.nazwisko) and id_imie = o_int1.id_imie limit 1) as powtorka
        from osoba_internet o_int1 left join kod_filia on o_int1.kod = kod_filia.kod 
        order by id;";

		echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'"><table class="gridTable" border="0" cellspacing="0">';
		$result = $controls->dalObj->PobierzDane($zapytanie);

        $odlamki_nag = explode(",", "Wpisz,Kasuj,Pokaż,ID,Data zgłoszenia,Imię,Nazwisko,Płeć,Data urodzenia,Miejscowość,Ulica,Kod,Wykształcenie,Zawód,Charakter,Data wyjazdu,Ilość tygodni,Źródło informacji");
        $kolumny_kolejnosc = array ( 0 => 'id', 1 => 'data_zgloszenia', 2 => 'imie', 3 => 'nazwisko', 4 => 'plec', 5 => 'data_urodzenia', 6 => 'miejscowosc', 7 => 'ulica', 8 => 'kod', 9 => 'wyksztalcenie', 10 => 'zawod', 12 => 'charakter', 13 => 'data', 14 => 'ilosc_tyg', 15 => 'zrodlo');
        setHeadingRow($odlamki_nag);
        echo $controls->AddHidden('id_os_int', 'id_os_int', '');
        $count = 0;
		foreach($result as $row)
		{
            $count++;
            $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
                
            $powtorka = (int)$row['powtorka'];
            if ($powtorka > 0)
                $css = 'markedRow'; //'#cc8888';//cc6666
            
            $buttonLabel = 'Wprowadź';
            $sms = false;
            if ($row['button_label']) {
                
                $sms = true;
                $buttonLabel = $row['button_label'];
            }
            if ($row['source'] == 2) {
                
                $sms = false;
                $buttonLabel = 'Huybregts';
            }
		    if ($row['source'] == 4) {
                
                $sms = true;
                $buttonLabel = 'Adwords';
            }
            if ($powtorka) {
                
                $sms = false;
                $buttonLabel = 'Porównanie';
            }
                
			$i = 0;
			echo '<tr class="'.$css.'"><td nowrap="">';
            if ($powtorka)
                echo $controls->AddPopUpButton($buttonLabel, 'zestawienie', '../porownanie_rejestracja.php?id_dane_osobowe='.$powtorka.'&id_dane_internet='.$row['id'], 800, 800, '');
            else if ($row['source'] == 3)
                echo '<a href="/main/osoba.php?newId='.$row['id'].'">StartPraca</a>';
            else {
                echo $controls->AddTableSubmit("wprowadz", $row['id'], $buttonLabel.'.', "onClick='id_os_int.value=this.id;'");
                if (true === $sms) {
                    
                    echo $controls->AddCheckbox('sms'.$row['id'], 'sms'.$row['id'], true, '', 'Sms', 'sms');
                }
            }
            echo '</td><td>';
            echo $controls->AddTableSubmit("kasuj", $row['id'], "Kasuj.", "onClick='id_os_int.value=this.id;'");

            echo '</td><td>';
            echo $controls->AddTableSubmit("edytuj", $row['id'], "Pokaż.", "onClick='id_os_int.value=this.id;'");

            echo '</td>';
            foreach ($kolumny_kolejnosc as $kolumna)
            {
                echo '<td nowrap>'.$row[$kolumna].'</td>';
            }
			echo "</tr>";
		}
		echo '</table></form>';
	}
        require("../stopka.php");
    }
?>
</html>