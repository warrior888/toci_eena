<?php 
    //ini_set('error_reporting', E_ALL);
    require_once 'conf.php';
    require_once 'bll/queries.php';
    require_once 'bll/FileManager.php';
    require_once 'ui/UtilsUI.php';
    require_once 'ui/HelpersUI.php';
    require_once 'Spreadsheet/Excel/Writer.php';
    
    require_once 'dal/DALDaneOsobowe.php';
    require_once 'dal/DALDaneDodatkowe.php';
    require_once 'adl/Candidate.php';
    require_once 'adl/Person.php';
    require_once 'ui/PartialsUI.php';
    require_once 'bll/BLLBalances.php';
    require_once 'bll/BLLViews.php';
    require_once 'bll/FileManager.php';
        
    $balances = new BLLBalances();
    
    $balances->SendFormerEmploymentsSummary(); // getActivesXls('2013-06-19');
    //$balances->Output('test');
    
    /*$testString = 'EaLa ma korta, kort ma trawe ....';
    $testarray = array('getting' => $testString, 'sraka' => '');
    
    var_dump(!empty($testString['dupa']));
    var_dump(empty($testString[56]));
    var_dump(isset($testString[5]));
    echo $testString['dupa'];
    echo $testarray['getting']['dfs']; 
    //notice Uninitialized string offset: 0 bo dfs rzutowane na 0, w srace pusty string
    echo $testarray['sraka']['dfs'];*/
    
    //fatal Cannot use string offset as an array - dfs po rzutowaniu na 0 daje offset stringu z msc 0, czyli literke, ktorej nie da sie juz dalej zaindexowac
    //echo $testarray['getting']['dfs'][0];
    
    //$partial = new Partials(new Person(null));
    //echo $partial->getAddUpdatePersonForm(array(Partials::FORM_PERSON_FIELD_INFO_SOURCE_ID => 1));
    
    /*$dal = new DALDaneOsobowe();
    var_dump($dal->getFormerEmployers(9466));
    
    $dal = new DALDaneInternet();
    var_dump($dal->getFormerEmployers(48328));*/
    
    //$dal = new DALDaneOsobowe();
    //$dal->setLangConfirmedList(array(67851, 67850), 1);
    
    //$dal = new DALDaneOsobowe();
    //$result = $dal->getLanguages(9466);
    
    //$dal = new DALDaneInternet();
    //$result = ($dal->getLanguagesCompensation(9466, 48328));
    
    /*foreach ($result[Model::RESULT_FIELD_DATA] as $row) {
        echo '<br /><br /><br />';
        var_dump($row);
    } */
    
    //$dal = new DALDaneOsobowe();
    //$dal->setSkillsList(9466, array(20, 21, 23, 27));
    
    //$dal = new Candidate(48328);
    //var_dump($dal->getCompensation(Candidate::COMPENSATION_TYPE_SKILLS, 9466));
    //$result = $dal->updatePersonCandidateData(9466, 48328, array('id_zawod', 'id_charakter', 'dupa'));
    //var_dump((bool)$result);
    
    //var_dump(LogManager::isDbException(new LogicNotFoundException('sraka', '', new DBQueryErrorException('dupa'))));
    
	//$conn = pg_pconnect('host=localhost dbname=eena user=postgres password=beatka');
    
//select dane_dodatkowe.id_osoba as id, dane_dodatkowe.id_osoba as osoba_id, dane_dodatkowe.wartosc as z_os_tow from dane_dodatkowe where id_dane_dodatkowe_lista = 3 and dane_dodatkowe.id_osoba in (2) and (wartosc = 'nie');
    //pg_query($conn, );
//test 1 - pobranie wielu danych, selecty rozlaczone ; - fail, nie ma multi query, sraka
   /* $query = 'select dane_dodatkowe.id_osoba as id, dane_dodatkowe.id_osoba as osoba_id, dane_dodatkowe.wartosc as z_os_tow from dane_dodatkowe where id_dane_dodatkowe_lista = 3 and dane_dodatkowe.id_osoba in (2) and (wartosc = \'nie\');
    select dane_dodatkowe.id_osoba as id, dane_dodatkowe.id_osoba as osoba_id, dane_dodatkowe.wartosc as z_os_tow from dane_dodatkowe where id_dane_dodatkowe_lista = 3 and dane_dodatkowe.id_osoba in (3) and (wartosc = \'nie\')';
    $result = pg_query($conn, $query);
    
    $rows = pg_fetch_all($result);
    var_dump($rows);*/
    
    //test 2 - wygenerowanie n selectow i pomiar czasu odpalenia, to samo z jednym duzym
    /*$start = 41;
    $incby = 2;
    $count = 5000;
    $i = 0;
    $ids = array();
    $query2Pattern = 'select dane_dodatkowe.id_osoba as id, dane_dodatkowe.id_osoba as osoba_id, dane_dodatkowe.wartosc as z_os_tow 
    from dane_dodatkowe where id_dane_dodatkowe_lista = 3 and dane_dodatkowe.id_osoba = %s and (wartosc like \'nie\'); ';
    $query2 = '';
    //$query2 = array();
    
    while ($i < $count)
    {
        $start += $incby;
        $ids[] = $start;
        $i++;
        $query2 .= sprintf($query2Pattern, $start);
    }
    $query1 = 'select dane_dodatkowe.id_osoba as id, dane_dodatkowe.id_osoba as osoba_id, dane_dodatkowe.wartosc as z_os_tow 
    from dane_dodatkowe where id_dane_dodatkowe_lista = 3 and dane_dodatkowe.id_osoba in ('.implode(',', $ids).') and (wartosc like \'nie\');';
    $sT = microtime(true);
    $result = pg_query($conn, $query1);
    $eT = microtime(true);
    $rT = $eT - $sT;
    echo 'czas in\'a : '.$rT."<br />";
    
    $sT2 = microtime(true);
    $result = pg_query($conn, $query2);
    //foreach ($query2 as $oneQuery)
    //    $result = pg_query($conn, $oneQuery);
    $eT2 = microtime(true);
    $rT2 = $eT2 - $sT2;
    echo 'czas wielu pojedynczych : '.$rT2."<br />";
    //test 3 - join, na db, podobne zalozenie - komorka podaje ponad 50 000 rekordow, zamieszanie tego ze spora iloscia z innej tabeli
    
    $zapytanie = 'select * from (select telefon_kom.id, telefon_kom.id as osoba_id, nazwa as komorka from telefon_kom where (lower(nazwa) like lower(\'%\'))) as a1 
    join (select dane_dodatkowe.id_osoba as id, dane_dodatkowe.id_osoba as osoba_id, dane_dodatkowe.wartosc as z_os_tow from dane_dodatkowe where id_dane_dodatkowe_lista = 3 and (wartosc like \'nie\')) as a2 on a1.osoba_id = a2.osoba_id;';
    
    $sT3 = microtime(true);
    $result = pg_query($conn, $zapytanie);
    $eT3 = microtime(true);
    $rT3 = $eT3 - $sT3;
    echo 'czas join\'a : '.$rT3."<br />";
    
    ///test nadplanowy: tlustszy join
    $zapytanie = 'select * from (select telefon_kom.id, telefon_kom.id as osoba_id, nazwa as komorka from telefon_kom where (lower(nazwa) like lower(\'%\'))) as a1 join (select dane_dodatkowe.id_osoba as id, dane_dodatkowe.id_osoba as osoba_id, dane_dodatkowe.wartosc as z_os_tow from dane_dodatkowe where id_dane_dodatkowe_lista = 3 and (wartosc like \'nie\')) as a2 on a1.osoba_id = a2.osoba_id join (select dane_dodatkowe.id_osoba as id, dane_dodatkowe.id_osoba as osoba_id, dane_dodatkowe.wartosc as soffi from dane_dodatkowe where id_dane_dodatkowe_lista = 2 and (wartosc like \'nie\')) as a3 on a1.osoba_id = a3.osoba_id join (select telefon.id as osoba_id from telefon where (lower(nazwa) like lower(\'%\'))) as a4 on a1.osoba_id = a4.osoba_id;';
    
    $sT4 = microtime(true);
    $result = pg_query($conn, $zapytanie);
    $eT4 = microtime(true);
    $rT4 = $eT4 - $sT4;
    echo 'czas grubszego join\'a : '.$rT4."<br />";   */
    
    
    //test strftime :)
    
    //echo strftime('%u', strtotime('2004-01-13'));