<?php session_start(); ?>
<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
  <script language="javascript" src="../js/utils.js"></script>
  <script language="javascript" src="../js/validations.js"></script>
<link href="../css/layout.css" rel="stylesheet" type="text/css">
</head>
<body onload="HideShowSections();">
<?php
    //include("dal.php");
    require("../conf.php");
    include("grid_class.php");
    include("excel_class.php");
    function drawGrid($DataSet, $drawByHeaders = false)
    {
        $gridObj = new SimpleGridView();
        $gridObj->SetTableStyle('class="gridTable" border="0" cellspacing="0" align="CENTER"');
        $gridObj->SetTrStyle('');
        $gridObj->SetTdStyle('nowrap align="CENTER"');
        $gridObj->SetEmptyString("--------");
        $gridObj->SetIdColumnVisible(true);
        $gridObj->SetDataSource($DataSet);
        $gridObj->DataBind($drawByHeaders);
    }
    function rewriteDatesToArray($resource, $elName)
    {
        $result = array();
        $tab_each_pair = array();
        $counter = 0;
        while ($row = pg_fetch_array($resource))
        {
             $tab_each_pair[0] = $tab_each_pair[1] = $row[$elName];
             $result[$counter] = $tab_each_pair;
             $counter++;
        }
        return $result;
    }
    if (empty($_SESSION['uzytkownik']))//(false)
    {
        require("log_in.php");
    }
    else
    {
        require("../naglowek.php");
	    
        define('QUERY_USERS_LIST', "select id, nazwa_uzytkownika from uprawnienia where nazwa_uzytkownika not in ('bartek','postgres','mateusz','dawid','admin') and aktywny = true order by nazwa_uzytkownika;");
        define('QUERY_FIRMS_LIST', "select id, nazwa from firma_filia order by nazwa;");
        //form combos radios and a lot of js
        if (isset($_POST['potwierdz']))
        {
            $statistics = new statystyka();
            $statData = new dalObjData();
            $queries = array();
            $queries = new queries();
            $dateFrom = $_POST['dateFrom'];
            $dateTo = $_POST['dateTo'];
                 
            //dates array structure is as follows : in each index table with 2 fields exist, easiest way to access is to taqke each 
            //dates array element into a variable and then request table index 0 and 1 from this variable
            
            $konsultantId = isset($_POST['konsultant_id']) ? (int)$_POST['konsultant_id'] : 0;
            
            $andConsultant = '';
            if ($konsultantId)
                $andConsultant = ' and uprawnienia.id = '.$konsultantId.' ';
                
            $statAkt = $statData->pgQuery("select id from decyzja where nazwa = 'Umówiony';");
            $rowAkt = pg_fetch_array($statAkt);
            $statusAkt = $rowAkt['id'];
                
            switch($_POST['state_id'])
            {
                ////first case in switch
                case "punkt 1":

                //send dates to class, retrieve and create queries object
                $datesArray = $statistics->AcceptPeriod($dateFrom, $dateTo, $_POST['statystyka']);
                //queries for activity of each person

                $mainQuery = "select count(dane_osobowe.id) as id, uprawnienia.imie_nazwisko as nazwa from dane_osobowe join uprawnienia on dane_osobowe.id_konsultant = uprawnienia.id 
                where data_zgloszenia between '_DATEFROM' and '_DATETO' and uprawnienia.aktywny = true 
                ".$andConsultant."
                group by uprawnienia.imie_nazwisko order by uprawnienia.imie_nazwisko;";
                
                $headAr = array('nazwa' => "Użytkownik");
                //$queries = ConstrQueryObj(1, QUERY_USERS_LIST, $datesArray, $subQueries, $headAr);
                $queries = queries::CreateFromMainQuery($mainQuery, $datesArray, $headAr);
                
                $statData->dbConnect();
                
                $statData->SetQueryObj($queries);
                $ds = $statData->PrepareNoMainQueryDataSet();
                $ds->SetHeaders($queries->GetHeaders());
                echo "Osoby zarejestrowane:<br />";
                drawGrid($ds, true);
                //create xls with stats
                $xlsObj = new MergeSheetsToXls('wynikiposzczegosob.xls');
                $xlsObj->AddSheet('zgloszenia', $ds, true);
                
                //umowieni statystyka
                $headAr = array('nazwa' => "Użytkownik");
                $mainQuery = "select count(zatrudnienie.id) as id, uprawnienia.imie_nazwisko as nazwa 
                from zatrudnienie join uprawnienia on zatrudnienie.id_pracownik = uprawnienia.id 
                where data_wpisu between '_DATEFROM' and '_DATETO' 
                ".$andConsultant."
                group by uprawnienia.imie_nazwisko order by uprawnienia.imie_nazwisko;";
                
                //$queries = ConstrQueryObj(1, QUERY_USERS_LIST, $datesArray, $subQueries, $headAr);
                $queries = queries::CreateFromMainQuery($mainQuery, $datesArray, $headAr);
                
                $statData->SetQueryObj($queries);
                $ds = $statData->PrepareNoMainQueryDataSet();
                $ds->SetHeaders($queries->GetHeaders());
                echo "<br /><hr /><br />";
                echo "Osoby umówione:<br />";
                drawGrid($ds, true);
                $xlsObj->AddSheet('umowieni', $ds, true);
                
                //kontakty
                $datesArray = $statistics->AcceptPeriodSpec($dateFrom, $dateTo, $_POST['statystyka']);
                $headAr = array('nazwa' => "Użytkownik");
                
                $mainQuery = "select count(kontakt_historia.id) as id, uprawnienia.imie_nazwisko as nazwa 
                from kontakt_historia join uprawnienia on kontakt_historia.id_konsultant = uprawnienia.id 
                where data between '_DATEFROM' and '_DATETO' 
                ".$andConsultant."
                group by uprawnienia.imie_nazwisko order by uprawnienia.imie_nazwisko;";
                
                //$queries = ConstrQueryObj(1, QUERY_USERS_LIST, $datesArray, $subQueries, $headAr);
                $queries = queries::CreateFromMainQuery($mainQuery, $datesArray, $headAr);
                
                $statData->SetQueryObj($queries);
                $ds = $statData->PrepareNoMainQueryDataSet();
                $ds->SetHeaders($queries->GetHeaders());
                echo "<br /><hr /><br />";
                echo "Osoby, z którymi podjęto kontakt:<br />";
                drawGrid($ds, true);
                $xlsObj->AddSheet('kontakty', $ds, true);
                
                //umowy
                $datesArray = $statistics->AcceptPeriod($dateFrom, $dateTo, $_POST['statystyka']);
                $headAr = array('nazwa' => "Użytkownik");
                
                $mainQuery = "select count(umowa_ewidencja.id) as id, uprawnienia.imie_nazwisko as nazwa 
                from umowa_ewidencja join uprawnienia on umowa_ewidencja.id_konsultant = uprawnienia.id 
                where data between '_DATEFROM' and '_DATETO' 
                ".$andConsultant."
                group by uprawnienia.imie_nazwisko order by uprawnienia.imie_nazwisko;";
                
                //$queries = ConstrQueryObj(1, QUERY_USERS_LIST, $datesArray, $subQueries, $headAr);
                $queries = queries::CreateFromMainQuery($mainQuery, $datesArray, $headAr);
                
                $statData->SetQueryObj($queries);
                $ds = $statData->PrepareNoMainQueryDataSet();
                $ds->SetHeaders($queries->GetHeaders());
                echo "<br /><hr /><br />";
                echo "Osoby, z którymi podpisano umowy:<br />";
                drawGrid($ds, true);
                $xlsObj->AddSheet('umowy', $ds, true);
                
                //nowe osoby w calych filiach
                $datesArray = $statistics->AcceptPeriod($dateFrom, $dateTo, $_POST['statystyka']);
                //queries for activity of each person
                $mainQuery = "select count(dane_osobowe.id) as id, firma_filia.nazwa 
                from dane_osobowe join uprawnienia on dane_osobowe.id_konsultant = uprawnienia.id
                join firma_filia on uprawnienia.id_firma_filia = firma_filia.id
                where data_zgloszenia between '_DATEFROM' and '_DATETO' 
                group by firma_filia.nazwa order by firma_filia.nazwa";
                //and id_konsultant in (select id from uprawnienia where id_firma_filia = '_ID');
                $headAr = array('nazwa' => "Filia");
                
                //$queries = ConstrQueryObj(1, QUERY_FIRMS_LIST, $datesArray, $subQueries, $headAr);
                $queries = queries::CreateFromMainQuery($mainQuery, $datesArray, $headAr);
                
                $statData->SetQueryObj($queries);
                $ds = $statData->PrepareNoMainQueryDataSet();
                $ds->SetHeaders($queries->GetHeaders());
                echo "<br /><hr /><br />";
                echo "Osoby zarejestrowane per filia:<br />";
                drawGrid($ds, true);
                $xlsObj->AddSheet('zgloszenia per filia', $ds, true);
                
                //umowieni statystyka per cala filia
                $headAr = array('nazwa' => "Filia");
                $mainQuery = "select count(zatrudnienie.id) as id, firma_filia.nazwa from zatrudnienie 
                join uprawnienia on zatrudnienie.id_pracownik = uprawnienia.id
                join firma_filia on uprawnienia.id_firma_filia = firma_filia.id
                where data_wpisu between '_DATEFROM' and '_DATETO' 
                group by firma_filia.nazwa order by firma_filia.nazwa;";
                    
                //$queries = ConstrQueryObj(1, QUERY_FIRMS_LIST, $datesArray, $subQueries, $headAr);
                $queries = queries::CreateFromMainQuery($mainQuery, $datesArray, $headAr);
                
                $statData->SetQueryObj($queries);
                $ds = $statData->PrepareNoMainQueryDataSet();
                $ds->SetHeaders($queries->GetHeaders());
                echo "<br /><hr /><br />";
                echo "Osoby umówione per filia:<br />";
                drawGrid($ds, true);
                $xlsObj->AddSheet('umowieni per filia', $ds, true);
                
                //kontakty
                $datesArray = $statistics->AcceptPeriodSpec($dateFrom, $dateTo, $_POST['statystyka']);
                $headAr = array('nazwa' => "Filia");
                $mainQuery = "select count(kontakt_historia.id) as id, firma_filia.nazwa from kontakt_historia 
                join uprawnienia on kontakt_historia.id_konsultant = uprawnienia.id
                join firma_filia on uprawnienia.id_firma_filia = firma_filia.id
                where data between '_DATEFROM' and '_DATETO' 
                group by firma_filia.nazwa order by firma_filia.nazwa;";
                
                //$queries = ConstrQueryObj(1, QUERY_FIRMS_LIST, $datesArray, $subQueries, $headAr);
                $queries = queries::CreateFromMainQuery($mainQuery, $datesArray, $headAr);
                
                $statData->SetQueryObj($queries);
                $ds = $statData->PrepareNoMainQueryDataSet();
                $ds->SetHeaders($queries->GetHeaders());
                echo "<br /><hr /><br />";
                echo "Osoby, z którymi podjęto kontakt per filia:<br />";
                drawGrid($ds, true);
                $xlsObj->AddSheet('kontakty per filia', $ds, true);
                
                //umowy
                $datesArray = $statistics->AcceptPeriod($dateFrom, $dateTo, $_POST['statystyka']);
                $headAr = array('nazwa' => "Filia");
                $mainQuery = "select count(umowa_ewidencja.id) as id, firma_filia.nazwa from umowa_ewidencja 
                join uprawnienia on umowa_ewidencja.id_konsultant = uprawnienia.id
                join firma_filia on uprawnienia.id_firma_filia = firma_filia.id
                where data between '_DATEFROM' and '_DATETO' 
                group by firma_filia.nazwa order by firma_filia.nazwa;";
                
                //$queries = ConstrQueryObj(1, QUERY_FIRMS_LIST, $datesArray, $subQueries, $headAr);
                $queries = queries::CreateFromMainQuery($mainQuery, $datesArray, $headAr);
                
                $statData->SetQueryObj($queries);
                $ds = $statData->PrepareNoMainQueryDataSet();
                $ds->SetHeaders($queries->GetHeaders());
                echo "<br /><hr /><br />";
                echo "Osoby, z którymi podpisano umowy per filia:<br />";
                drawGrid($ds, true);
                $xlsObj->AddSheet('umowy per filia', $ds, true);
                
                //section 5 taken from point 2 - actives per thursday per user

                $newDatesArray = $statistics->AcceptPeriod($dateFrom, $dateTo, "czwartek");
                
                $subQueries = "select count(zatrudnienie.id) as id, uprawnienia.imie_nazwisko as nazwa from zatrudnienie 
                join oddzialy_klient on oddzialy_klient.id = zatrudnienie.id_oddzial 
                JOIN miejscowosc_biuro ON oddzialy_klient.id_biuro = miejscowosc_biuro.id
                JOIN msc_biura ON miejscowosc_biuro.id_msc_biuro = msc_biura.id 
                join uprawnienia on uprawnienia.id = zatrudnienie.id_pracownik 
                where miejscowosc_biuro.id_msc_biuro = '_ID' and zatrudnienie.id_decyzja = ".$statusAkt." and data_wyjazdu <= '_DATEFROM' and data_powrotu >= '_DATETO' 
                and uprawnienia.aktywny = true 
                ".$andConsultant."
                group by uprawnienia.imie_nazwisko;";             
                $headAr = array("ID", 'Biuro', 'Dane');
                $headSubAr = array('nazwa' => 'Pracownik');
                $queries = ConstrQueryObj(true, "select id, nazwa from msc_biura order by nazwa;", $newDatesArray, $subQueries, $headAr, $headSubAr);
                //"select id, imie_nazwisko from uprawnienia where nazwa_uzytkownika not in ('bartek','postgres','mateusz','dawid','admin') and aktywny = true order by imie_nazwisko;"
                $statData->SetQueryObj($queries);
                $ds = $statData->PrepareDataSet("_ID");
                $ds->SetHeaders($queries->GetHeaders());
                $ds->SetSubHeaders($queries->GetSubHeaders());
                
                echo '<br /><hr /><br />';
                
                echo "Aktywni per konsultant :<br />"; //".$rowBiuro['nazwa']."
                drawGrid($ds);
                
                $xlsObj->AddSheet("Aktywni per Konsultant", $ds, true); //.$rowBiuro['nazwa']
                
                $newDatesArray = $statistics->AcceptPeriod($dateFrom, $dateTo, "czwartek");
                
                $mainQuery = "select count(zatrudnienie.id) as id, firma_filia.nazwa from zatrudnienie 
                join uprawnienia on uprawnienia.id = zatrudnienie.id_pracownik 
                join firma_filia on uprawnienia.id_firma_filia = firma_filia.id
                where zatrudnienie.id_decyzja = ".$statusAkt." and data_wyjazdu <= '_DATEFROM' and data_powrotu >= '_DATETO' 
                and uprawnienia.aktywny = true 
                group by firma_filia.nazwa;";
                             
                $headAr = array('nazwa' => 'Biuro');
                $queries = queries::CreateFromMainQuery($mainQuery, $newDatesArray, $headAr);
                //"select id, imie_nazwisko from uprawnienia where nazwa_uzytkownika not in ('bartek','postgres','mateusz','dawid','admin') and aktywny = true order by imie_nazwisko;"
                $statData->SetQueryObj($queries);
                $ds = $statData->PrepareNoMainQueryDataSet();
                $ds->SetHeaders($queries->GetHeaders());
                
                echo '<br /><hr /><br />';
                
                echo "Aktywni per filia :<br />"; //".$rowBiuro['nazwa']."
                drawGrid($ds, true);
                
                $xlsObj->AddSheet("Aktywni per filia", $ds, true);
                
                $xlsObj->SaveToDisc();
                echo '<br /><a href="wynikiposzczegosob.xls">Plik Excel z wynikami.</a>';			    
                break;
                
                
                ////second case in switch
                case "punkt 2":
                //bartus departures and active people
                
                $statData->dbConnect(); 
                $resDates = $statData->pgQuery("select distinct data_wyjazdu from zatrudnienie where data_wyjazdu between '".$dateFrom."' and '".$dateTo."';");
                // bug ! FIX it, jak nie ma dat to nie ma stat
                //$ownTransportIdList = $statData->PobierzDane('select distinct id_msc_odjazdu as id from rozklad_jazdy where id_przewoznik = (select id from przewoznik where lower(nazwa) = lower(\'Własny Transport\'));');
                
                $datesArray = rewriteDatesToArray($resDates, 'data_wyjazdu');
                //point active status and own transport information
                    
                $xlsObj = new MergeSheetsToXls('bartus.xls');
                $TransportTypesList = $statData->PobierzDane('select id, nazwa from przewoznik order by nazwa asc;');

                foreach ($TransportTypesList as $transportTypeItem) {

                    //section 1 : people who left with bartus
                    
                    $mainQuery = "select count(zatrudnienie.id) as id, msc_biura.nazwa from zatrudnienie 
                    join oddzialy_klient on oddzialy_klient.id = zatrudnienie.id_oddzial
                    -- join zatrudnienie_odjazd on zatrudnienie.id = zatrudnienie_odjazd.id_zatrudnienie
                    join rozklad_jazdy on zatrudnienie.id_rozklad_jazdy_wyjazd = rozklad_jazdy.id
                    JOIN miejscowosc_biuro ON oddzialy_klient.id_biuro = miejscowosc_biuro.id
                    JOIN msc_biura ON miejscowosc_biuro.id_msc_biuro = msc_biura.id
                    where zatrudnienie.id_decyzja = ".$statusAkt." and rozklad_jazdy.id_przewoznik = ".$transportTypeItem['id']." 
                    and data_wyjazdu = '_DATEFROM' 
                    group by msc_biura.nazwa order by msc_biura.nazwa ;";
                    
                    $headAr = array('nazwa' => "Wyjazdy ".$transportTypeItem['nazwa']);
                    //$queries = ConstrQueryObj(1, "select id, nazwa from msc_biura order by nazwa;", $datesArray, $subQueries, $headAr);
                    $queries = queries::CreateFromMainQuery($mainQuery, $datesArray, $headAr);
                    
                    $statData->SetQueryObj($queries);
                    $ds = $statData->PrepareNoMainQueryDataSet();
                    $ds->SetHeaders($queries->GetHeaders());
                    echo "Wyjazdy ".$transportTypeItem['nazwa'].":<br />";
                    drawGrid($ds, true);
                    echo '<br /><hr /><br />';
                    //create xls with stats
                    
                    $xlsObj->AddSheet('wyjazdy'.$transportTypeItem['nazwa'], $ds, true);
                } 
                
                //section relative to 1
                $resDates = $statData->pgQuery("select distinct data_powrotu from zatrudnienie where data_powrotu between '".$dateFrom."' and '".$dateTo."';");
                $datesArray = rewriteDatesToArray($resDates, 'data_powrotu');
                //point active status and own transport information


                foreach ($TransportTypesList as $transportTypeItem) {
                    
                    //section 1.1 : people who are returning with bartus
                    $mainQuery = "select msc_biura.nazwa, count(zatrudnienie.id) as id from zatrudnienie 
                    join oddzialy_klient on oddzialy_klient.id = zatrudnienie.id_oddzial
                    --join zatrudnienie_odjazd on zatrudnienie.id = zatrudnienie_odjazd.id_zatrudnienie
                        join rozklad_jazdy on zatrudnienie.id_rozklad_jazdy_wyjazd = rozklad_jazdy.id 
                    JOIN miejscowosc_biuro ON oddzialy_klient.id_biuro = miejscowosc_biuro.id
                    JOIN msc_biura ON miejscowosc_biuro.id_msc_biuro = msc_biura.id where rozklad_jazdy.id_przewoznik = ".$transportTypeItem['id']." 
                    and data_powrotu = '_DATEFROM' 
                    group by msc_biura.nazwa order by msc_biura.nazwa;";
                    
                    $headAr = array('nazwa' => "Powroty ".$transportTypeItem['nazwa']);
                    //$queries = ConstrQueryObj(1, "select id, nazwa from msc_biura order by nazwa;", $datesArray, $subQueries, $headAr);
                    $queries = queries::CreateFromMainQuery($mainQuery, $datesArray, $headAr);
                    
                    $statData->SetQueryObj($queries);
                    //$ds = $statData->PrepareDataSet('_ID');
                    $ds = $statData->PrepareNoMainQueryDataSet();
                    $ds->SetHeaders($queries->GetHeaders());
                    echo "Powroty ".$transportTypeItem['nazwa'].":<br />";
                    drawGrid($ds, true);
                    echo '<br /><hr /><br />';
                    //create new xls sheet with data
                    $xlsObj->AddSheet('powroty'.$transportTypeItem['nazwa'], $ds, true);
                }
                
                //section 3: actives per each thursday
                $newDatesArray = $statistics->AcceptPeriod($dateFrom, $dateTo, "czwartek");
                                
                $mainQuery = "select msc_biura.nazwa , count(zatrudnienie.id) as id from zatrudnienie 
                join oddzialy_klient on oddzialy_klient.id = zatrudnienie.id_oddzial 
                JOIN miejscowosc_biuro ON oddzialy_klient.id_biuro = miejscowosc_biuro.id
                JOIN msc_biura ON miejscowosc_biuro.id_msc_biuro = msc_biura.id where zatrudnienie.id_decyzja = ".$statusAkt." and data_wyjazdu <= '_DATEFROM' 
                and data_powrotu >= '_DATETO' 
                group by msc_biura.nazwa order by msc_biura.nazwa;";
                
                $headAr = array('Biuro');
                //$queries = ConstrQueryObj(true, "select id, nazwa from msc_biura order by nazwa;", $newDatesArray, $subQuery, $headAr); //
                $queries = queries::CreateFromMainQuery($mainQuery, $newDatesArray, $headAr);
                
                $statData->SetQueryObj($queries);
                $ds = $statData->PrepareNoMainQueryDataSet();
                $ds->SetHeaders($queries->GetHeaders());
                echo "Aktywni:<br />";
                drawGrid($ds);
                echo '<br /><hr /><br />';
                $xlsObj->AddSheet('Aktywni', $ds);
                
                //section 4 actives per thursday per client
                $subQueries = "select count(zatrudnienie.id) as id, klient.nazwa from zatrudnienie 
                join oddzialy_klient on oddzialy_klient.id = zatrudnienie.id_oddzial 
                    join klient on oddzialy_klient.id_klient = klient.id
                JOIN miejscowosc_biuro ON oddzialy_klient.id_biuro = miejscowosc_biuro.id
                where zatrudnienie.id_decyzja = ".$statusAkt." and data_wyjazdu <= '_DATEFROM' and data_powrotu >= '_DATETO' and miejscowosc_biuro.id_msc_biuro = '_ID' 
                group by klient.nazwa order by klient.nazwa;";
                //$statData->dbConnect();
                //$biura = $statData->pgQuery("select id, nazwa from msc_biura order by nazwa;");
                //while ($rowBiuro = pg_fetch_array($biura))
                //{             
                
                //"select distinct klient.id as id, klient.nazwa as nazwa from oddzialy_klient join klient on oddzialy_klient.id_klient = klient.id JOIN miejscowosc_biuro ON oddzialy_klient.id_biuro = miejscowosc_biuro.id where miejscowosc_biuro.id_msc_biuro = ".$rowBiuro['id']." order by nazwa;"
                    $headAr = array('ID', 'Miejscowość', 'Dane');
                    $headSubAr = array('nazwa' => 'Klient');
                    $queries = ConstrQueryObj(true, "select id, nazwa from msc_biura order by nazwa;", $newDatesArray, $subQueries, $headAr, $headSubAr);
                    
                    $statData->SetQueryObj($queries);
                    $ds = $statData->PrepareDataSet("_ID");
                    $ds->SetHeaders($queries->GetHeaders());
                    $ds->SetSubHeaders($queries->GetSubHeaders());
                    echo "Aktywni per klient:<br />";
                    drawGrid($ds);
                    echo '<br /><hr /><br />';
                    $xlsObj->AddSheet("Aktywni per klient", $ds, true); //$rowBiuro['nazwa']
                //}

                $xlsObj->SaveToDisc();
                
                echo '<br /><a href="bartus.xls">Plik Excel z wynikami.</a>';
                break;
                
                /////third case in switch
                case "punkt 3":
                
                
                $datesArray = $statistics->AcceptPeriod($dateFrom, $dateTo, $_POST['statystyka']);
                //source of information
                $subQueries = "select count(id) as id from dane_osobowe where data_zgloszenia between '_DATEFROM' and '_DATETO' and id_zrodlo = '_ID';";

                $headAr = array(0 => "ID", 1 => "Źródło informacji");
                $queries = ConstrQueryObj(1, "select id, nazwa from zrodlo order by nazwa;", $datesArray, $subQueries, $headAr);
                $statData->dbConnect();
                $statData->SetQueryObj($queries);
                $ds = $statData->PrepareDataSet("_ID");
                $ds->SetHeaders($queries->GetHeaders());
                echo "Źródła informacji:<br />";
                drawGrid($ds);
                //create xls with stats
                $xlsObj = new MergeSheetsToXls('zrodlainformacji.xls');
                $xlsObj->AddSheet('informacje', $ds);
                $xlsObj->SaveToDisc();
                echo '<br /><a href="zrodlainformacji.xls">Plik Excel z wynikami.</a>';
                break;
                
                case "punkt 4":
                
                $xlsObj = new MergeSheetsToXls('przewoznicy_wyjazd.xls');
                $datesList = array(array($dateFrom, $dateTo));
                
                $przewoznikId = (int)$_POST['przewoznik_id'];
                $konsultantId = isset($_POST['konsultant_id']) ? (int)$_POST['konsultant_id'] : 0;
            
                $andConsultant = '';
                if ($konsultantId)
                    $andConsultant = ' and id_pracownik = '.$konsultantId.' ';
                
                $mainQuery = "select * from zestawienie_wyjazd where data_wyjazdu between '_DATEFROM' and '_DATETO' and id_przewoznik = ".$przewoznikId.$andConsultant;
                
                $headAr = array('nazwa' => 'ID', 'imie' => 'Imię', 'nazwisko' => 'Nazwisko', 'data_urodzenia' => 'Data urodzenia', 'data_wyjazdu' => 'Data wyjazdu', 
                'msc_odjazd' => 'Miejsce odjazdu', 'msc_biuro' => 'Biuro', 'imie_nazwisko' => 'Konsultant');
                //$queries = ConstrQueryObj(1, "select id, nazwa from msc_biura order by nazwa;", $datesArray, $subQueries, $headAr);
                $queries = queries::CreateFromMainQuery($mainQuery, $datesList, $headAr);
                
                $statData->SetQueryObj($queries);
                //$ds = $statData->PrepareDataSet('_ID');
                $ds = $statData->PrepareNoMainQueryDataSet();
                $ds->SetHeaders($queries->GetHeaders());
                echo "Zestawienie wyjazdów ".$_POST['przewoznik'].":<br />"; //todo escape
                drawGrid($ds, true);
                echo '<br /><hr /><br />';
                //create new xls sheet with data
                $xlsObj->AddSheet('zestawienie wyjazdów '.$_POST['przewoznik'], $ds, true);
                $xlsObj->SaveToDisc();
                echo '<br /><a href="przewoznicy_wyjazd.xls">Plik Excel z wynikami.</a>';
                break;    
                
                case "punkt 5":
                
                $xlsObj = new MergeSheetsToXls('przewoznicy_powrot.xls');
                $datesList = array(array($dateFrom, $dateTo));
                
                $przewoznikId = (int)$_POST['przewoznik_id'];
                $konsultantId = isset($_POST['konsultant_id']) ? (int)$_POST['konsultant_id'] : 0;
            
                $andConsultant = '';
                if ($konsultantId)
                    $andConsultant = ' and id_pracownik = '.$konsultantId.' ';
                
                $mainQuery = "select * from zestawienie_powrot where data_powrotu between '_DATEFROM' and '_DATETO' and id_przewoznik = ".$przewoznikId.$andConsultant;
                
                $headAr = array('nazwa' => 'ID', 'imie' => 'Imię', 'nazwisko' => 'Nazwisko', 'data_urodzenia' => 'Data urodzenia', 'data_powrotu' => 'Data powrotu', 
                'msc_powrot' => 'Miejsce powrotu', 'msc_biuro' => 'Biuro', 'imie_nazwisko' => 'Konsultant');
                //$queries = ConstrQueryObj(1, "select id, nazwa from msc_biura order by nazwa;", $datesArray, $subQueries, $headAr);
                $queries = queries::CreateFromMainQuery($mainQuery, $datesList, $headAr);
                
                $statData->SetQueryObj($queries);
                //$ds = $statData->PrepareDataSet('_ID');
                $ds = $statData->PrepareNoMainQueryDataSet();
                $ds->SetHeaders($queries->GetHeaders());
                echo "Zestawienie powrotów ".$_POST['przewoznik'].":<br />"; //todo escape
                drawGrid($ds, true);
                echo '<br /><hr /><br />';
                //create new xls sheet with data
                $xlsObj->AddSheet('zestawienie powrotów '.$_POST['przewoznik'], $ds, true);
                $xlsObj->SaveToDisc();
                echo '<br /><a href="przewoznicy_powrot.xls">Plik Excel z wynikami.</a>';
                break;  
            }
        }
        else
        {
            $htmlControls = new HtmlControls();
            echo "<form method='POST' action='".$_SERVER['PHP_SELF']."'>
            <div id='radiodiv' align='center'>
            <div id='selectdiv'>";
            
            $selectData = array(
                array('id' => '----', 'nazwa' => '----'),
                array('id' => 'punkt 1', 'nazwa' => 'Wyniki pracy poszczególnych osób'),
                array('id' => 'punkt 2', 'nazwa' => 'Wyniki aktywności spółki'),
                array('id' => 'punkt 3', 'nazwa' => 'Wyniki źródła informacji spółki'),
                array('id' => 'punkt 4', 'nazwa' => 'Zestawienie wyjazdów'),
                array('id' => 'punkt 5', 'nazwa' => 'Zestawienie powrotów'),
            );
            
            echo $htmlControls->_AddSelect('statelem', 'statelem', $selectData, null, 'state_id', false, '', '', '', array(), 'HideShowSections();');
            
            $dal = dal::getInstance();
            $users = $dal->PobierzDane('select id, imie_nazwisko as nazwa from uprawnienia where aktywny = true order by imie_nazwisko;');
            
            $travelers = $dal->PobierzDane('select id, nazwa from przewoznik order by nazwa;');
            
            array_unshift($users, array('id' => 0, 'nazwa' => '----'));
            
            echo '<br />'.$htmlControls->_AddSelect('konsultant', 'konsultant', $users, User::getInstance()->getUserId(), 'konsultant_id', true);
            
            echo '<br />'.$htmlControls->_AddSelect('przewoznik', 'przewoznik', $travelers, null, 'przewoznik_id', true, '', 'style="display: none;"');
            
            echo "</div>
                <table id='periodOptions'><tr>
                <td id='opt1'><input type='radio' name='statystyka' value='dzien'>Dzień</td><td id='opt2'>
                <input type='radio' name='statystyka' value='tydzien' checked>Tydzień</td><td id='opt3'>
                <input type='radio' name='statystyka' value='miesiac'>Miesiąc</td><td id='opt4'>
                <input type='radio' name='statystyka' value='rok'>Rok</td>
                </tr></table>
            </div>
            <div align='center'><table><tr><td>Zakres od: </td><td>".$htmlControls->_AddDatebox("dateFrom", "dateFrom", "", 10, 10)."</td><td>Zakres do: </td><td>".
            $htmlControls->_AddDatebox("dateTo", "dateTo", "", 10, 10)."</td></tr></table></div>
            <div align='center'><input type='submit' class='formreset' id='potwierdz' name='potwierdz' value='Potwierdź' onclick='return checkdates(event);'></div></form>"; 
            
            echo '<form method="POST" action="formularz.php"><table><tr><td>';
            $year = date('Y') - 1;
            echo $htmlControls->_AddNumberbox("year", "year", $year, 4, 4, '');
            echo '</td><td>';
            echo $htmlControls->_AddNoPrivilegeSubmit('marszalek', 'marszalek', 'Statystyka roczna.', '', '');
            echo '</td></tr></table></form>';
        }
        require("../stopka.php");
    }
?>
</body>
</html>                            
