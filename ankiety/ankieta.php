<?php
    //session_name('EENA_REGISTRATION_FORM');
    session_start();
?>
<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
  <script language="javascript" src="jquery.js"></script>
  <script language="javascript" src="../js/validations.js"></script>
  <script language="javascript" src="../js/utils.js"></script>
  <script language="javascript" src="utils.js"></script>
  <link href="style_form.css" rel="stylesheet" type="text/css">
</head>
<body>
<div id="popup" style="display: none;"></div>
<?php
//<link href="../css/ankieta.css" rel="stylesheet" type="text/css"> 
    //require("../naglowek.php");
    //ini_set ('display_erros', 1);
    require("../conf.php");
    include_once("../vaElClass.php");
    include_once("../bll/definicjeKlas.php");
    include_once("../bll/queries.php");
    require_once '../ui/UtilsUI.php';
    $database = pg_connect($con_str);
    $controls = new valControl();
    $popPrac = new PoprzedniPracodawca();
    $pracCollection = new PoprzedniPracodawcaCollection();
    
    $qBase = new QueriesBase(
        array (
            QueriesBase::CONF_ADD_COLUMNS => 'dod_kolumny_ankieta',
            QueriesBase::CONF_ADD_QUERY_DATA => 'select id, nazwa, nazwa_wyswietlana, id_typ, edycja from dane_dodatkowe_lista where id in (select id_dane_dodatkowe_lista from dane_dodatkowe_internet_lista) order by nazwa;',
            QueriesBase::CONF_DATA_TABLE => 'dane_dodatkowe_ankieta',
            QueriesBase::CONF_DATA_TABLE_FKEY => 'id_dane_dodatkowe_internet_lista',
        )
    );
    
    $dzis = date('Y-m-d');
    
    if($_SERVER['REQUEST_METHOD'] == 'GET') 
    {
        $_SESSION = array();
    }
    
	if (isset($_POST['add']))
	{
        if ($_POST['Rwyjazd'] > $dzis)
        {
            $seqquery = "select nextval('dane_internet_id_seq') as val;";
            $resseqquery = $controls->dalObj->pgQuery($seqquery);
            $rowseq = pg_fetch_assoc($resseqquery);
            $zapytanie_insert = "insert into dane_internet values (".$rowseq['val'].", 
            (select id from imiona where nazwa = '".$_POST['imie']."'),
            '".trim($_POST['Rname'])."',
            (select id from plec where nazwa = '".$_POST['Rsex']."'),
            '".trim($_POST['Rbirthdate'])."',
            ".$_POST['Rbirthplace_id'].",
            ".$_POST['Rcity_id'].",
            '".trim($_POST['Rstreet'])."',
            '".trim($_POST['RpostalCode'])."',
            '".trim($_POST['tel_stac'])."',
            '".trim($_POST['tel_kom'])."',
            '".trim($_POST['e-mail'])."',
            (select id from wyksztalcenie where nazwa = '".$_POST['wyksztalcenie']."'),
            '".trim($_POST['id_gr_zaw'])."',
            '".date("Y-m-d")."',
            (select id from charakter where nazwa = '".$_POST['charakter']."'),
            '".trim($_POST['Rwyjazd'])."',
            '".trim($_POST['Rtygodnie'])."',
            (select id from ankieta where nazwa = 'Internet'),        
            (select id from zrodlo where nazwa = '".$_POST['zrodlo']."'),
            2);";
            $query_insert = $controls->dalObj->pgQuery($zapytanie_insert);
            if ($query_insert)
            {
                if (isset($_SESSION['nie_znam_jezykow']))
                    $_POST['zna_jezyk'] = 'nie';
                if (isset($_SESSION['nie_mam_prawa_jazdy']))
                    $_POST['posiada_pr_j'] = 'nie';
                    
                $qBase->setAdditionalColumnsData($rowseq['val'], $_POST);
                $tab_prawo_jazdy = explode(",", $_POST['id_prawo_jazdy']);
                if (strlen($_POST['id_prawo_jazdy']) > 0)
                {
                    $zapytanie_prawo_jazdy = "";
                    for ($i = 0; $i < count($tab_prawo_jazdy); $i++)
                    {
                        $zapytanie_prawo_jazdy .= "insert into prawo_jazdy_internet values (".$rowseq['val'].", '".$tab_prawo_jazdy[$i]."');";
                    } 
                    $query_prawo_jazdy = $controls->dalObj->pgQuery($zapytanie_prawo_jazdy);  
                }
                $tab_jezyk = explode(",", $_POST['id_jezyk_obcy']);
                $tab_poziom = explode(",", $_POST['id_poziom_jezyk_obcy']);
                if (strlen($_POST['id_jezyk_obcy']) > 0)
                {
                    $zapytanie_jezyk = "";
                    for ($i = 0; $i < count($tab_jezyk); $i++)
                    {
                        $zapytanie_jezyk .= "insert into jezyki_internet values (".$rowseq['val'].", '".$tab_jezyk[$i]."', '".$tab_poziom[$i]."');";
                    } 
                    $query_jezyk = $controls->dalObj->pgQuery($zapytanie_jezyk);  
                }
                if (isset($_SESSION['pracCollection']))
                {
                    $pracCollection = unserialize($_SESSION['pracCollection']);
                    $collection = $pracCollection->GetCollection();
                    if (count($collection) > 0)
                    {
                        $zapytanie_pop_prac = "";
                        $i = 0;
                        for($i = 0; $i < count($collection); $i++)
                        {
                            $popPrac = $collection[$i];
                            $zapytanie_pop_prac .= "insert into poprzedni_pracodawca_ankieta (id, nazwa, id_grupa_zawodowa) values (".$rowseq['val'].", '".$popPrac->EmpName."', ".$popPrac->OccId.");";
                        }
                        $result_prac = $controls->dalObj->pgQuery($zapytanie_pop_prac);
                    }
                    unset($_SESSION['pracCollection']);
                }
                if (isset($_SESSION['dodUmCollection']))
                {
                    $dodUmCollection = unserialize($_SESSION['dodUmCollection']);
                    $collection = $dodUmCollection->GetCollection();
                    if (count($collection) > 0)
                    {
                        $zapytanie_dod_um = "";
                        $i = 0;
                        for($i = 0; $i < count($collection); $i++)
                        {
                            $dodUm = $collection[$i];
                            $zapytanie_dod_um .= "insert into umiejetnosci_osob_internet values (nextval('umiejetnosci_osob_internet_id_wiersz_seq'), ".$rowseq['val'].", ".$dodUm->dodUmId.");";
                        }
                        $result_dod_um = $controls->dalObj->pgQuery($zapytanie_dod_um);
                    }
                    unset($_SESSION['dodUmCollection']);
                }
                
                echo 'Dziękujemy za wypełnienie ankiety. Gbr. Huybregts <br /><a href="'.$_SERVER['PHP_SELF'].'">Powrót</a>';
            }
        }
	}
    $id_prawo_jazdy = null;
    $imie = null;
    $plec = null;
    $id_jezyk_obcy = null;
    $id_poziom_jezyk_obcy = null;
    $Rtxt_gr_zaw = null;
    $id_gr_zaw = null;
    $Rname = null;
    $Rbirthdate = null;
    $Rstreet = null;
    $RpostalCode = null;
    $Rcity = null;
    $tel_stac = null;
    $tel_kom = null;
    $e_mail = null;
    $Rwyjazd = null;
    $Rtygodnie = null;
    $zrodlo = null;
    $wyksztalcenie = null;
    $charakter = null;
    
    if (isset($_POST['id_prawo_jazdy']))
    {
        $id_prawo_jazdy = $_POST['id_prawo_jazdy'];
        $id_jezyk_obcy = $_POST['id_jezyk_obcy'];
        $imie = $_POST['imie'];
        $plec = $_POST['Rsex'];
        $id_poziom_jezyk_obcy = $_POST['id_poziom_jezyk_obcy'];
        $Rtxt_gr_zaw = $_POST['Rtxt_gr_zaw'];
        $id_gr_zaw = $_POST['id_gr_zaw'];
        $Rname = $_POST['Rname'];
        $Rbirthdate = $_POST['Rbirthdate'];
        $Rstreet = $_POST['Rstreet'];
        $RpostalCode = $_POST['RpostalCode'];
        $Rcity = $_POST['Rcity'];
        $tel_stac = $_POST['tel_stac'];
        $tel_kom = $_POST['tel_kom'];
        $e_mail = $_POST['e-mail'];
        $Rwyjazd = $_POST['Rwyjazd'];
        $Rtygodnie = $_POST['Rtygodnie'];
        $zrodlo = $_POST['zrodlo'];
        $wyksztalcenie = $_POST['wyksztalcenie'];
        $charakter = $_POST['charakter'];
    }
	if (!isset($_POST['add']))
	{
    echo '<form method = "POST" action = "'.$_SERVER['PHP_SELF'].'">';
    echo $controls->AddHidden('id_prawo_jazdy', 'id_prawo_jazdy', $id_prawo_jazdy);
    echo $controls->AddHidden('id_jezyk_obcy', 'id_jezyk_obcy', $id_jezyk_obcy);
    echo $controls->AddHidden('id_poziom_jezyk_obcy', 'id_poziom_jezyk_obcy', $id_poziom_jezyk_obcy);
    echo $controls->AddSelectHelpHidden();
    echo $controls->AddHidden('testPC', 'testPC', '');
    ?>
    
    <div align="center" class="text_header">
    Osoby zainteresowane naszą ofertą prosimy o wypełnienie formularza rejestracyjnego.<br />
    Uprzejmie informujemy, że będziemy się kontaktować tylko z wybranymi kandydatami.
     </div><br/>
    <table align="center"><tr><td>
		<table align="center">
        <tr>
            <td><span>Nazwisko</span></td><td align="right">
            <?php
                echo $controls->AddAnkietaTextBox("Rname", $Rname, 30, 35, 'sprawdz_nazwisko(this);', "onchange='checkName(); checkAll();' title='Podaj nazwisko.' tabindex='1'", "required");
            ?>
            </td>
            <td class="prawy_td"><span>Telefon komórkowy</span></td><td align='right'>
            <?php
                echo $controls->AddAnkietaNumberbox("tel_kom","tel_kom", $tel_kom, 9, 9, "telefon_kom(this);", 'onchange = "checkName(); checkAll();" tabindex="10"', "psrequired", "Telefon komórkowy w formacie 9 cyfr.");
            ?>
            </td>
        </tr><tr>
            <td><span>Imię</span></td><td align="right">
            <?php
                echo $controls->AddSelectRandomQuery('imie', 'id_imie', 'title="Wprowadzanie kolejnych liter imienia przyspiesza jego wybór." tabindex="2"', 'select id, nazwa from imiona order by nazwa asc;', $imie, 'imie_id');
            ?>    
            </td>
            <td class="prawy_td"><span>E-mail</span></td><td align='right'>
            <?php 
                echo $controls->AddAnkietaEmailbox('e-mail', $e_mail, 35, 30, '', 
                'onchange="checkName(); checkAll();" title="Podaj poprawny adres e-mail." tabindex="11"', 'psrequired');
            ?>
            </td>
        </tr>
        <tr>
            <td><span>Płeć</span></td><td align="right">
            <?php
                echo $controls->AddSelectRandomQuery('Rsex', 'id_Rsex', 'title="Wybierz płeć." tabindex="3"', 'select id, nazwa from plec order by nazwa asc;', $plec, 'Rsex_id');
            ?> 
            </td>
            <td class="prawy_td"><span>Ewentualny termin wyjazdu (RRRR-MM-DD)</span></td><td align='right'>
            <?php
                echo $controls->AddAnkietaDatebox("Rwyjazd", "Rwyjazd", $Rwyjazd, 10, 10, 
                "onchange = 'checkName(); checkAll();' onblur='CheckLength(this);DateTomorrow(this, this.value);' tabindex='12' title='Podaj datę w formacie RRRR-MM-DD.'", "required");
            ?>
            </td>
        </tr>
        <tr>
            <td><span>Data urodzenia (RRRR-MM-DD)</span></td><td align='right'>
            <?php
                echo $controls->AddAnkietaDatebox("Rbirthdate", "Rbirthdate", $Rbirthdate, 10, 10, 
                "onchange='checkName(); checkAll();' onblur='CheckLength(this); DateYesterday(this, this.value);' tabindex='4' title='Podaj datę w formacie RRRR-MM-DD.'", "required");
            ?>
            </td>
            <td class="prawy_td"><span>Ilość tygodni</span></td><td align='right'>
            <?php
                echo $controls->AddAnkietaNumberbox("Rtygodnie","Rtygodnie", $Rtygodnie, 2, 3, "sprawdz_tygodnie(this);", "onchange='checkName(); checkAll();' tabindex='13'", "required", "Długość pierwszego pobytu w pracy za granicą.");
            ?>
            </td>
        </tr>
        <tr>
            <td><span>Miejsce urodzenia</span></td><td align="right">
            <?php
                echo $controls->AddSelectRandomQuery('Rbirthplace', 'id_Rbirthplace', 'tabindex="5" title="Wprowadzanie kolejnych liter miejscowości przyspiesza jej wybór."', 'select id, nazwa from miejscowosc order by nazwa asc;', $Rbirthdate, 'Rbirthplace_id');
            ?>
            </td>
            <td class="prawy_td"><span>Źródło informacji o firmie</span></td><td align="right">
            <?php
                echo $controls->AddSelectRandomQuery('zrodlo', 'id_zrodlo', 'title="Źródło informacji o firmie E&A." tabindex="14"', 'select id, nazwa from zrodlo where widoczne = true order by nazwa asc;', $zrodlo, 'zrodlo_id');
            ?>
            </td>
        </tr>
        <tr>
            <td><span>Ulica, numer domu</span></td><td align='right'>
                <?php 
                    echo $controls->AddAnkietaTextInput('Rstreet', $Rstreet, 60, 30, '', 
                    'onchange="checkName(); checkAll();" title="Podaj ulicę wraz z numerem domu i ewentualnego mieszkania." tabindex="6"', 'required');
                ?>
            </td>
            <td class="prawy_td"><span>Wykształcenie</span></td><td align="right">
            <?php
                echo $controls->AddSelectRandomQuery('wyksztalcenie', 'id_wyksztalcenie', 'title="Wybierz wykształcenie." tabindex="15"', 
                'select id, nazwa from wyksztalcenie order by nazwa asc;', $wyksztalcenie, 'wyksztalcenie_id');
            ?>
            </td>
        </tr>
        <tr>
            <td><span>Kod pocztowy</span></td><td align='right'>
            <?php
                echo $controls->AddHidden('id_Rcity_id', 'Rcity_id', '');
                echo $controls->AddAnkietaPostCodebox("RpostalCode", $RpostalCode, 6, 6, 'onchange = "checkName(); checkAll(); GetCity(this);" tabindex="7"', "required", "Podaj pięć cyfr kodu pocztowego w formacie xx-xxx.");
            ?>
            </td>
            <td class="prawy_td"><span>Grupa zawodowa</span></td><td>
            <?php 
                echo $controls->OccGroupControlAnkieta("Wybierz", "wybor_gr", "Rtxt_gr_zaw", "Rtxt_gr_zaw", $Rtxt_gr_zaw,"id_gr_zaw", "hid_gr_zaw", $id_gr_zaw, "wybor_grupy_zaw.php", 
                "Grupyzawodowe", 'Naciśnij WYBIERZ, by określić swoją przynależność do grupy zawodowej.', 16); 
            ?>
            </td>
        </tr>
        <tr>
            <td><span>Miejscowość</span></td><td align='right'>
            <?php  
	            echo $controls->AddAnkietaTextbox('Rcity', $Rcity, 40, 30, '', 'title="Miejscowość pobiera się automatycznie po podaniu kodu pocztowego." tabindex="8" READONLY', 'required');
            ?>
            </td>
            <td class="prawy_td"><span>Charakter pracy</span></td><td align="right">
            <?php
                echo $controls->AddSelectRandomQuery('charakter', 'id_charakter', 'title="Informacja dla agencji, w jakim okresie w roku osoba jest skłonna wyjechać." tabindex="17"', 
                'select id, nazwa from charakter where lower(nazwa) not like lower (\'nie%\') order by nazwa asc;', $charakter, 'charakter_id');
            ?>
            </td>
        </tr>
        <tr>
            <td><span>Telefon stacjonarny</span></td><td align='right'>
            <?php
                echo $controls->AddAnkietaNumberbox("tel_stac","tel_stac", $tel_stac, 9, 9, "telefon_stacj(this);", "onchange = 'checkName(); checkAll();' tabindex='9'", "psrequired", "Telefon stacjonarny w formacie 9 cyfr.(bez 0 z przodu)");
            ?>
            </td>
            
        </tr>
<?php
    
    $addElements = $qBase->getAdditionalColumns();
    echo UtilsUI::formAdditionalData($controls, $addElements);
        echo "<tr>
        <td><input type='button' class='popbutton' name = 'btnJezykiObce' value = 'Wybierz języki obce' onclick = \"FillLanguages();\" tabindex='18' /></td>
        <td align = 'right'><span><div id = 'jezykiObce'></div></span></td>
        
        <td class='prawy_td'><input type = 'button' class='popbutton' name = 'btnPrawoJazdy' value = 'Wybierz prawo jazdy' onclick = \"FillDriversLicense();\" tabindex='19' /></td>
        <td align='right'><span><div id = 'prawoJazdy'></div></span></td>
        </tr>
        <tr>
        <td><input type='button' class='popbutton' name = 'btnPoprzedniPracodawca' value = 'Podaj historię zatrudnienia'  onclick = \"FillFormerEmployees();\" tabindex='20'/></td>
        <td align='right'><span><div id = 'poprzedniPracodawca'></div></span></td>
        
        <td class='prawy_td'><input type = 'button' class='popbutton' name = 'btnUmiejetnosci' value = 'Podaj dodatkowe umiejętności'  onclick = \"FillAdditionalSkills();\" tabindex='21'/></td>
        <td align='right'><span><div id = 'DUmiejetnosci'></div></span></td>
        </tr>";
    //onmouseover='showHint(\"<i>Naciśnij, aby określić poprzednio wykonywane prace.</i>\");' onmouseout='showHint(\"\");'
    //onmouseover='showHint(\"<i>Naciśnij, aby wybrać posiadane kategorie.</i>\");' onmouseout='showHint(\"\");'
    //onmouseover='showHint(\"<i>Naciśnij, aby wskazać znane języki.</i>\");' onmouseout='showHint(\"\");'
    //onmouseover='showHint(\"<i>Naciśnij, aby określić dodatkowe umiejętności i uprawnienia.</i>\");' onmouseout='showHint(\"\");'
    ?>
       
</table>
<table align='center'>
<tr><td>
<div style="width:600px; text-align:justify;"><h5>
    Wyrażam zgodę na przetwarzanie moich danych osobowych zawartych w niniejszym formularzu <br />
    dla potrzeb realizacji procesu rekrutacji zgodnie z Ustawą z dnia 29 sierpnia 1997 r. o ochronie danych osobowych (Dz. U. Nr 133, poz. 883 z późn. zm.). <br />
    Jednocześnie potwierdzam, iż zostałem poinformowany o przysługującym mi prawie do wglądu, poprawiania i usunięcia moich danych osobowych.</h5></div>
    </td>
    <td style="text-align:center;"><input name="zgoda" id="zgoda" onclick="blur();" onchange="checkAll();" type="checkbox">
</td></tr>


<tr><td align="center" style="width:600px; text-align:justify;">
Pole wyślij jest nieaktywne dopóki wszystkie wymagane (czerwone) pola nie są zapełnione. <br> Dodatkowo konieczne jest uzupełnienie przynajmniej jednego pola zielonego, oraz zgoda na przetwarzanie danych osobowych.</td>
<td align="center"><input name="add" id="add" value="Wyślij" type="submit" class="submit" disabled></td></tr>

</table></td><td valign="top"><br /><br />
<table><tr><td id="popupcontainer"></td></tr>
<tr><td id="HintContainer"></td></tr>
</table>
</td></tr></table>
</form>
<?
	}
    //require("../stopka.php");
?>
</body>
</html>