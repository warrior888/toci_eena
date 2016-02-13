<?php
    require_once '../conf.php';
    CommonUtils::outputBufferingOn();
    CommonUtils::SessionStart();

    echo '<html>';
    HelpersUI::addHtmlBasicHeaders(HelpersUI::PATH_LEVEL_1);
    echo '<body>';
   
    $controls = new valControl();
    if (empty($_SESSION['uzytkownik']))
    {
        require '../log_in.php';
        die();
    }
    else
    {
        require_once 'adl/Person.php';
        require_once 'ui/UtilsUI.php';

        if (isset($_GET['id_os'])) $_SESSION['id_os_zettel'] = $_GET['id_os'];
	    if(isset($_GET['edytuj_osobe']))
	    {
	        $id_osoba = (int)$_GET['id_os'];
            $person = new Person($id_osoba);
            
            try {
                $resultResponse = $person->getPersonData();
                $wiersz = $resultResponse[Model::RESULT_FIELD_DATA];
            } catch (ProjectLogicException $e) {
                //TODO any log ?
			    die('Osoby nie znaleziono, najpewniej niew³a¶ciwe ¿±danie.');
            }

                $addElements = $person->getLogicDaneDodatkowe()->getAdditionalsDictList(true);
                
			    echo '<table class="gridTable" border="0" cellspacing="0"><form name="osoba_edit" method="POST" action="../update/update_osoba.php">';
                //add hidden inputs for select js browsing
			    echo $controls->AddSelectHelpHidden();
                //hidden with person id
                echo $controls->AddHidden('id_id', 'id', $id_osoba);
                //fill combo with names, select method of controls object returns select filled with data returned by the query
                //selected option is the one passed to function as a last parameter

			    echo '<tr class="oddRow"><td>ID:</td><td>';
			    echo $id_osoba.'</td><td>&nbsp;</td><td>&nbsp;</td></tr>';
			    echo '<tr class="evenRow"><td>Imiê:</td>&nbsp;<td>';
                echo $controls->AddSelect("id_imie", "id_imie", "", "queryName", $wiersz['imie'], "imie_id", 'genericWidth');
			    echo '</td><td>';
                //button that serves window open js action -> the right side button column
                echo $controls->AddPopUpButton("Kwalifikacje", "kwalifikacje", "../prawa_strona/kwalifikacje.php?".ID_OSOBA.'='.$id_osoba, 1280, 760, 'genericWidth');
                echo '</td><td>&nbsp;</td></tr>
                
                <tr class="oddRow"><td>Nazwisko:</td><td>';
                echo $controls->AddCLTextbox("nazwisko", $wiersz['nazwisko'], 30, 30, 'genericWidth');
                echo '</td><td>';
                echo $controls->AddPopUpButton("Dokumenty", "dokumenty", "../prawa_strona/dokumenty.php?".ID_OSOBA.'='.$id_osoba, 380, 340, 'genericWidth');
                echo '</td><td>&nbsp;</td></tr>
                
                <tr class="evenRow"><td>P³eæ:</td><td>';
                echo $controls->AddSelect("id_plec", "id_plec", "", "queryGender", $wiersz['plec'], "plec_id", 'genericWidth');
                echo '</td><td>';
                
                //echo $controls->AddPopUpButton("Jezyki", "jezyki", "../prawa_strona/znane_jezyki.php?".ID_OSOBA.'='.$id_osoba, 700, 500, 'genericWidth');
                echo $controls->AddPopUpButton("Zatrudnienie", "Zatrudnienie", "../prawa_strona/historia_zatrudnienia.php?".ID_OSOBA.'='.$id_osoba, 700, 700, 'genericWidth');  
                echo '</td><td>';
                echo '</td></tr>';
                
                echo '<tr class="oddRow"><td>Data urodzenia:</td><td>';
                echo $controls->AddDatebox("data_urodzenia", "data_urodzenia", $wiersz['data_urodzenia'], 10, 10, 'genericWidth');
                echo '</td><td>';
                echo $controls->AddPopUpButton("Historia Zatrudnienia", "Zatrudnienie", "../prawa_strona/historia_zatrudnienia_wyswietl.php?".ID_OSOBA.'='.$id_osoba, 900, 700, 'genericWidth');
			    echo '</td><td>';
                
			    echo '</td></tr>
                
                <tr class="evenRow"><td>Miejsce urodzenia:</td><td>';                                                                     
                echo $controls->AddSelect("id_miejscowosc_ur", "id_miejscowosc_ur", "", "queryMsc", $wiersz['miejscowosc_ur'], "miejsce_ur_id", 'genericWidth');
			    echo '</td><td>';
                echo $controls->AddPopUpButton("Preferencje", "preferencje", "../prawa_strona/preferencje.php?".ID_OSOBA.'='.$id_osoba, 500, 700, 'genericWidth');
			    echo '</td><td>&nbsp;</td></tr>
                
                <tr class="oddRow"><td>Miejscowo¶æ:</td><td>';
                echo $controls->AddSelect("id_miejscowosc", "id_miejscowosc", "", "queryMsc", $wiersz['miejscowosc'], "miejscowosc_id", 'genericWidth');
			    echo '</td><td>';
                //echo $controls->AddPopUpButton("Ustalenia", "semantyka", "../prawa_strona/semantyka.php?".ID_OSOBA.'='.$id_osoba, 900, 680, 'genericWidth');
                echo $controls->AddPopUpButton("Dodatkowe Osoby", "dodatkowe", "../prawa_strona/dodatkowe_osoby.php?".ID_OSOBA.'='.$id_osoba, 500, 700, 'genericWidth'); 
			    echo '</td><td>&nbsp;</td></tr>
                
                <tr class="evenRow"><td>Ulica:</td><td>';
                echo $controls->AddTextbox("ulica", "ulica", $wiersz['ulica'], 50, 30, "onChange='sprawdz_ulica(this);'", 'genericWidth');
                echo '</td><td>';
                echo $controls->AddPopUpButton("Zadania Dnia", "zadania_dnia", "../prawa_strona/zadania_dnia.php?".ID_OSOBA.'='.$id_osoba, 900, 700, 'genericWidth');
                echo '</td><td>&nbsp;</td></tr>
                
                <tr class="oddRow"><td>Kod:</td><td>';
                echo $controls->AddPostCodebox("kod", $wiersz['kod'], 6, 6, 'genericWidth');
                echo '</td><td>';
                //echo $controls->AddPopUpButton("Prawo Jazdy", "prawo_jazdy", "../prawa_strona/prawo_jazdy.php?".ID_OSOBA.'='.$id_osoba, 500, 700, 'genericWidth');
                echo $controls->AddPopUpButton("Reklamacje", "reklamacje", "../prawa_strona/reklamacje.php?".ID_OSOBA.'='.$id_osoba, 1000, 700, 'genericWidth');
			    echo '</td><td>&nbsp;</td></tr>
                
                <tr class="evenRow"><td>Wykszta³cenie:</td><td>';
                echo $controls->AddSelect("wyksztalcenie", "wyksztalcenie", "", "queryEdu", $wiersz['wyksztalcenie'], "wyksztalcenie_id", 'genericWidth');
			    echo '</td><td>';
                //echo $controls->AddPopUpButton("Umiejêtno¶ci", "umiejetnosci", "../prawa_strona/umiejetnosci.php?".ID_OSOBA.'='.$id_osoba, 500, 700, 'genericWidth');
                echo $controls->AddPopUpButton("Korespondencje", "korespondencje", "../prawa_strona/korespondencje.php?".ID_OSOBA.'='.$id_osoba, 700, 700, 'genericWidth');
			    echo '</td><td>&nbsp;</td></tr>
                
                <tr class="oddRow"><td>Zawód:</td><td>';
                echo $controls->OccGroupControl("Wybierz", "wybor_gr", "grupa_zawodowa", "txt_gr_zaw", $wiersz['zawod'],"id_gr_zaw", "hid_gr_zaw", $wiersz['id_zawod'], "../prawa_strona/wybor_grupy_zaw.php", "Grupyzawodowe", 'genericWidth');
                echo '</td><td>';
                echo $controls->AddPopUpButton("Jarografy", "Jarografy", "../prawa_strona/jarograf.php?".ID_OSOBA.'='.$id_osoba, 1000, 700, 'genericWidth');
			    echo '</td><td>&nbsp;</td></tr>
                
                <tr class="evenRow"><td>Konsultant:</td><td>'.$wiersz['konsultant'].'</td><td>';
                echo $controls->AddPopUpButton("Wy¶lij Email", "WyslijEmail", "../prawa_strona/wyslij_email.php?".ID_OSOBA.'='.$id_osoba, 800, 700, 'genericWidth');
                
			    $zapytanieOstKontakt = "select max(data) as data from kontakt where id = '".$id_osoba."';";
			    $ostKontakt = $controls->dalObj->PobierzDane($zapytanieOstKontakt);
			    
			    echo '</td><td>&nbsp;</td></tr><tr class="oddRow"><td>Ostatni kontakt:</td><td>'.$ostKontakt[0]['data'].'</td><td>';
                echo $controls->AddPopUpButton("Historia", "Historia", "../prawa_strona/historia.php?".ID_OSOBA.'='.$id_osoba, 800, 700, 'genericWidth');
			    echo '</td></tr>
                
                <tr class="evenRow"><td>Kontakt:</td><td>';
                echo $controls->AddCheckbox('ost_kontakt', 'id_ost_kontakt', '', JsEvents::ONBLUR.'="blur();"');
                echo "&nbsp;";
			    echo "Status: ".$wiersz['status']."</td><td>";
                echo $controls->AddPopUpButton("Wy¶lij SMS", "Wy¶lijSMS", "../prawa_strona/wyslij_sms.php?".ID_OSOBA.'='.$id_osoba, 800, 700, 'genericWidth');
                echo '</td><td>&nbsp;</td></tr>';
                
			    echo '<tr class="oddRow"><td>Data zg³oszenia:</td><td>'.$wiersz['data_zgloszenia'].'</td><td>';
                //echo $controls->AddPopUpButton('Historia Email', "Email", "../prawa_strona/email_historia.php?".ID_OSOBA.'='.$id_osoba, 800, 700, 'genericWidth');
                echo $controls->AddPopUpButton("Historia SMS", "HistoriaSMS", "../prawa_strona/historia_sms.php?".ID_OSOBA.'='.$id_osoba, 800, 700, 'genericWidth');
                echo '</td><td>&nbsp;</td></tr>';
                
                echo '<tr class="evenRow"><td>Charakter pracy:</td><td>';
                echo $controls->AddSelect("id_charakter", "id_charakter", "", "queryChar", $wiersz['charakter'], "charakter_id", 'genericWidth') ."</td><td>";
			    echo $controls->AddPopUpButton("Dokumenty Skaner", "DodajZeskanowanyDokument", "../prawa_strona/dodawanie_skanow.php?".ID_OSOBA.'='.$id_osoba, 1000, 750, 'genericWidth');
			    echo '</td><td>&nbsp;</td></tr>';
                
			    echo '<tr class="oddRow"><td>Data wyjazdu:</td><td>';
                echo $controls->AddDatebox("data", "datawyjazd", $wiersz['data'], 10, 10, 'genericWidth');
			    echo '</td><td>';    
                
			    echo '</td><td>&nbsp;</td></tr>
                
                <tr class="evenRow"><td>Ilo¶æ tygodni:</td><td>';
                echo $controls->AddNumberbox("ilosc_tyg", "tygodnie", $wiersz['ilosc_tyg'], 2, 3, "sprawdz_tygodnie(this)", 'genericWidth');
                echo '</td><td>';
                echo $controls->AddPopUpButton("Ankieta", "Ankieta", "../prawa_strona/ankieta.php?".ID_OSOBA.'='.$id_osoba, 800, 700, 'genericWidth');
			    echo '</td><td>&nbsp;</td></tr>
                
                <tr class="oddRow"><td>Ankieta:</td><td>';
                echo $controls->AddSelect("id_ankieta", "id_ankieta", "", "queryAn", $wiersz['ankieta'], "ankieta_id", 'genericWidth');
                echo '</td><td>';    
                //echo $controls->AddPopUpButton("Poprzedni Pracodawca", "PoprzedniPracodawca", "../prawa_strona/poprzedni_pracodawca.php?".ID_OSOBA.'='.$id_osoba, 800, 700, 'genericWidth');
			    echo '</td><td>&nbsp;</td></tr>
                
                <tr class="evenRow"><td>¬ród³o informacji:</td><td>';
                echo $controls->AddSelectRandomQuery("id_zrodlo", "id_zrodlo", "", 
                'select id, nazwa from zrodlo where widoczne = true or id = (select id from zrodlo where nazwa = \''.$wiersz['zrodlo'].'\') order by nazwa;', 
                $wiersz['zrodlo'], "zrodlo_id", 'nazwa', 'id', '', null, '', '', 'genericWidth'); 			       
			    echo '</td><td>';
                
			    echo '</td><td>&nbsp;</td></tr>
                
                <tr class="oddRow"><td>Rozmiar obuwia:</td><td>';
                echo $controls->AddTextbox("nr_obuwia", "nr_obuwia", $wiersz['nr_obuwia'], 8, 8, "", 'genericWidth');
                echo '</td><td>';
                
                $nextEmployer = $person->getNextEmployer();
                if ($nextEmployer)
                echo $controls->AddPopUpButton("Umowa", "Umowa", "../prawa_strona/umowa.php?".ID_OSOBA.'='.$id_osoba, 800, 700, 'genericWidth');
                    
                echo '</td><td>&nbsp;</td></tr>';
                
                echo UtilsUI::formAdditionalData($controls, $addElements, $wiersz);
                
			    echo '<tr><td>';             
                echo $controls->AddSubmit("update_osoba", "update_osoba", "Aktualizuj", "", 'genericWidth');           
                
                echo '</td></tr></form>';
			    echo "<tr><td>";
                echo $controls->AddSubmit('odswiez', 'id_odswiez', 'Cofnij', JsEvents::ONCLICK.'="wroc();"', 'genericWidth');
			    echo "</td></tr></table>";   
		         
	    }  
	    if(isset($_GET['kasuj_osobe']) && isset($_SESSION['kasowanie_rekordu']))
	    {
		    $query = "DELETE FROM dane_osobowe WHERE id = '".$_GET['id_os']."';";
		    $database = pg_connect($con_str);
		    $wynik = pg_query($database, $query);
		    if($wynik){echo "Usuniêto osobê.";}
	    }

        $_GET['id_zettel'] = isset($_GET['id_zettel']) ? $_GET['id_zettel'] : null;
        $_GET['dodaj_zettel'] = isset($_GET['dodaj_zettel']) ? $_GET['dodaj_zettel'] : null;
	    if (($_GET['id_zettel'] != "") || isset($_POST['dodaj_zettel']))
	    {
            if (isset($_POST['tydzien']))
            {
                $database = pg_connect($con_str);
                $zapytanie = "select tydzien from zettel where id = '".$_SESSION['id_os_zettel']."' and tydzien = '".addslashes($_POST['tydzien'])."' and rok = '".date("Y")."';";
                $query = pg_query($database, $zapytanie);
                if (pg_num_rows($query) == 0)
                {
                    $zapytanie_insert = "insert into zettel values ('".$_SESSION['id_os_zettel']."', (select id from uprawnienia where nazwa_uzytkownika = '".$_SESSION['uzytkownik']."'), '".date("Y-m-d")."', '".addslashes($_POST['tydzien'])."', '".date("Y")."');";
                    $zapytanie_insert .= "insert into korespondencje values ('".$_SESSION['id_os_zettel']."', (select id from rodzaj_korespondencji where nazwa = 'Zettel'), (select id from uprawnienia where nazwa_uzytkownika = '".$_SESSION['uzytkownik']."'), '".date("Y-m-d")."');";
                    $query_insert = pg_query($database, $zapytanie_insert);
                    echo("<script>wroc();</script>");
                }
                else
                {
                    echo("<div align = 'CENTER'>Zettel z tygodnia {$_POST['tydzien']} jest ju¿ wpisany</div>");
                }
            }       
            echo("<form action = '".$_SERVER['PHP_SELF']."' method = 'POST'>");
            echo("<div align = 'CENTER'>Wpisz tydzieñ:</div>");
            echo '<div align = "CENTER">';
            echo $controls->AddNumberbox("tydzien", "tydzien", "", 2, 3, "sprawdz_ilosc_osob(this);");
            echo "</div><div align = 'CENTER'>";
            echo $controls->AddSubmit('dodaj_zettel', 'id_dodaj_zettel', 'Dodaj', JsEvents::ONCLICK.'="wroc();"');
            echo "</div><div align = 'CENTER'>";
            echo $controls->AddSubmit('cofnij', 'id_cofnij', 'Cofnij', JsEvents::ONCLICK.'="wroc();"');
            echo("</div></form>");
	    }                                                                                      
    
    }
    CommonUtils::sendOutputBuffer();
?>
</body>
</html>