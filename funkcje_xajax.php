<?php
    include_once ("xajax/xajax.inc.php");
    include_once ("../dal.php");
    function AutoFill($tabela, $kolumna, $kolumna_warunek, $warunek, $kolumna_sortowana, $kierunek_sortowania, $nazwa_komponentu, $wlasciwosc_komponentu)
    {
        require("../conf.php");
        $database = pg_connect($con_str);
        $zapytanie = "select $kolumna from $tabela where lower($kolumna_warunek) like lower('".$warunek."') order by $kolumna_sortowana $kierunek_sortowania;";
        $query = pg_query($database, $zapytanie);
        $row = pg_fetch_array($query);
        
        $objResponse = new xajaxResponse();
	    $objResponse->addAssign($nazwa_komponentu, $wlasciwosc_komponentu, $row["$kolumna"]);
        //$objResponse->addAssign($nazwa_komponentu, $wlasciwosc_komponentu, $zapytanie);
	
	    return $objResponse;
    }
    function test($nazwa_komponentu, $wlasciwosc_komponentu)
    {
        $objResponse = new xajaxResponse();
	    $objResponse->addAssign($nazwa_komponentu, $wlasciwosc_komponentu, "Mateusz");
        
        return $objResponse;
    }
    function GetCity($nazwa_komponentu, $wlasciwosc_komponentu, $postalCode)
    {
        if (strlen($postalCode) == 6)
        {
            require("socket.php");
            //pobor z bazy, jesli lipa z poczty
            $zapytanie = 'select miejscowosc.nazwa from kod_pocztowy join miejscowosc on kod_pocztowy.id_miejscowosc = miejscowosc.id where kod = \''.$postalCode.'\';';
            //echo $zapytanie;
            $dal = dal::getInstance();
            $wynik = $dal->PobierzDane($zapytanie);

            if (isset($wynik[0]))
            {
                $miejscowosc = $wynik[0]['nazwa'];
            }
            else
            {
                $miejscowosc = pobierz_miasto($postalCode);
                //dodac od razu do bazy
                $miejscowosc = str_replace("*", "", $miejscowosc);
                $msc_test = str_replace("-", "%", $miejscowosc);
                $id_miejscowosc = null;
                $test_msc = "select id from miejscowosc where lower(nazwa) like lower('".$msc_test."');";
                $resmsctest = $dal->PobierzDane($test_msc, $ilosc_wierszy);
                //$msc = "";
                if ($ilosc_wierszy == 0)
                {
                    $msc = "insert into miejscowosc values(nextval('miejscowosc_id_seq'), '".$miejscowosc."');";
                    $res_m = $dal->pgQuery($msc);
                }
                else
                {
                    $id_miejscowosc = $resmsctest[0]['id'];
                }
                if ($ilosc_wierszy == 1)
                {
                    $msc = "update miejscowosc set nazwa = '".$miejscowosc."' where lower(nazwa) like lower('".$msc_test."');";
                    $res_m = $dal->pgQuery($msc);
                }
                //sprawdzenie i dodanie kodu
                $test_kod = "select id from kod_pocztowy where kod = '".$postalCode."';";
                $reskodtest = $dal->PobierzDane($test_kod, $ilosc_wierszy);
                if ($ilosc_wierszy == 0)
                {
                    if ($id_miejscowosc == null)
                    {
                        $test_msc = "select id from miejscowosc where nazwa = '".$miejscowosc."';";
                        $resmsctest = $dal->PobierzDane($test_msc, $ilosc_wierszy);
                        $id_miejscowosc = $resmsctest[0]['id'];
                    }
                    //$msc = "insert into kod_pocztowy (kod, id_miejscowosc) values('".$postalCode."', ".$id_miejscowosc.");";
                    //$res_m = $dal->pgQuery($msc);
                }
            }
            
            $objResponse = new xajaxResponse();

	        $objResponse->addAssign($nazwa_komponentu, $wlasciwosc_komponentu, $miejscowosc);
            
            return $objResponse;
        }
        else
        {
            $objResponse = new xajaxResponse();

            $objResponse->addAssign($nazwa_komponentu, $wlasciwosc_komponentu, '');
            
            return $objResponse;
        }
    }
    function ClearSession($sessionId)
    {
        unset($_SESSION[$sessionId]);   
        
        $objResponse = new xajaxResponse();
        
        return $objResponse;               
    }

    // Instantiate the xajax object.  No parameters defaults requestURI to this page, method to POST, and debug to off
    $xajax = new xajax(); 

    //$xajax->debugOn(); // Uncomment this line to turn debugging on

    // Specify the PHP functions to wrap. The JavaScript wrappers will be named xajax_functionname
    $xajax->registerFunction("AutoFill");
    $xajax->registerFunction("test");
    $xajax->registerFunction("GetCity");
    $xajax->registerFunction("ClearSession");

    // Process any requests.  Because our requestURI is the same as our html page,
    // this must be called before any headers or HTML output have been sent
    $xajax->processRequests();  
?>