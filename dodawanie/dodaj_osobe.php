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
        require_once '../ui/UtilsUI.php';
        require_once '../bll/queries.php';
        
        $controls = unserialize($_SESSION['controls']);
        $base = new QueriesBase();

        $addElements = $base->getAdditionalColumns();
        
	    echo "<form action='../insert/insert.php' method='POST'>";

        echo $controls->AddSelectHelpHidden();
        echo '<table class="gridTable" cellspacing="0"><tr class="oddRow"><td>Imiê:</td><td>';
        echo $controls->AddSelect("imie", "imie", "", "queryName", "", "imie_id", 'genericWidth');
	    echo '</td></tr><tr class="evenRow"><td>Nazwisko:</td><td>';
        echo $controls->AddCLTextbox("nazwisko", "", 20, 20, 'genericWidth');
        echo '</td></tr><tr class="oddRow"><td>P³eæ:</td><td>';
        echo $controls->AddSelect("plec", "plec", "", "queryGender", "", "plec_id", 'genericWidth');
	    echo '</td></tr><tr class="evenRow"><td>Data urodzenia:</td><td>';
        echo $controls->AddDatebox("data_urodzenia", "data_urodzenia", "", 10, 10, 'genericWidth');
        echo '</td></tr><tr class="oddRow"><td>Miejsce urodzenia:</td><td>';
        echo $controls->AddSelect("miejsce_ur", "miejsce_ur", "", "queryMsc", "", "miejsce_ur_id", 'genericWidth');
	    echo '</td></tr><tr class="evenRow"><td>Miejscowo¶æ:</td><td>';
        echo $controls->AddSelect("miejscowosc", "miejscowosc", "", "queryMsc", "", "miejscowosc_id", 'genericWidth');
	    echo '</td></tr><tr class="oddRow"><td>Ulica:</td><td>';
        echo $controls->AddTextbox("ulica", "ulica", "", 50, 30, "onChange='sprawdz_ulica(this);'", 'genericWidth');
        echo '</td></tr><tr class="evenRow"><td>Kod:</td><td>';
        echo $controls->AddPostCodebox("kod", "", 6, 6, 'genericWidth');
        echo '</td></tr><tr class="oddRow"><td>Wykszta³cenie:</td><td>';
        echo $controls->AddSelect("wyksztalcenie", "wyksztalcenie", "", "queryEdu", "", "wyksztalcenie_id", 'genericWidth');
        
	    echo '</td></tr><tr class="evenRow"><td>Zawód:</td><td>';
        echo $controls->OccGroupControl("Wybierz", "wybor_gr", "grupa_zawodowa", "txt_gr_zaw", "","id_gr_zaw", "hid_gr_zaw", "", "../prawa_strona/wybor_grupy_zaw.php", "Grupyzawodowe", 'genericWidth');
        echo '</td></tr>';
	    
	    $zapytanie = "select imie_nazwisko from uprawnienia where nazwa_uzytkownika = '".$_SESSION['uzytkownik']."';";
	    //$wynik = pg_query($database,$zapytanie);
        $wynik = $controls->dalObj->pgQuery($zapytanie);
	    $wiersz = pg_fetch_array($wynik);
	    echo '<tr class="oddRow"><td>Konsultant:</td><td>'.$wiersz['imie_nazwisko'].'</td></tr>
        <tr class="evenRow"><td>Kontakt:</td><td>
        <input type="checkbox" name="ost_kontakt" onClick="blur()" CHECKED></td></tr>
        <tr class="oddRow"><td>Data zg³oszenia:</td><td>
        <input type="text" name="data_zgloszenia" value="'.$dzis.'" readonly="readonly" class="formfield genericWidth"></td></tr>
        <tr class="evenRow"><td>Charakter pracy:</td><td>';
        echo $controls->AddSelect("charakter", "charakter", "", "queryChar", "Sta³a", "charakter_id", 'genericWidth');
	    echo '</td></tr><tr class="oddRow"><td>Data wyjazdu:</td><td>';
        echo $controls->AddDateboxFuture("data_wyjazdu", "data_wyjazdu", "", 10, 10, '', 'genericWidth');
        echo '</td></tr><tr class="evenRow"><td>Ilo¶æ tygodni:</td><td>';
        echo $controls->AddNumberbox("ilosc_tygodni", "ilosc_tyg", "", 2, 3, "sprawdz_tygodnie(this)", 'genericWidth');
        echo '</td></tr><tr class="oddRow"><td>Ankieta:</td><td>';
        echo $controls->AddSelect("ankieta", "ankieta", "", "queryAn", "Biuro", "ankieta_id", 'genericWidth');
        
	    echo '</td></tr><tr class="evenRow"><td>¬ród³o informacji:</td><td>';
        echo $controls->AddSelect("zrodlo", "zrodlo", "", "querySrc", "Znajomi", "zrodlo_id", 'genericWidth');
        
	    echo '</td></tr><tr class="oddRow"><td>Numer obuwia:</td><td>';
        echo $controls->AddTextbox("nr_obuwia", "nr_obuwia", "", 8, 8, "", 'genericWidth');
	    echo '</td></tr>';
        
        echo UtilsUI::formAdditionalData($controls, $addElements);
        
        echo '<tr><td>';
        echo $controls->AddSubmit('insert', 'id_insert', 'Wprowad¼', '', 'genericWidth');
        echo '</td></tr></table></form>';
        //echo '<script>alert(document.getElementById("imie").options[document.getElementById("imie").selectedIndex].id); alert(document.getElementById("imie").options[document.getElementById("imie").selectedIndex].value); alert(document.getElementById("imie").options[document.getElementById("imie").selectedIndex].innerHTML); alert(document.getElementById("imie").options[document.getElementById("imie").selectedIndex].name);</script>';
    }
    CommonUtils::sendOutputBuffer();
?>
</body>
</html>