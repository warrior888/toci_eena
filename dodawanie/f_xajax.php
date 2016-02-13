<?php
    include ("xajax/xajax.inc.php");
    set_include_path(get_include_path().
    PATH_SEPARATOR.'/'
    );
    
    
    function GetBranches($nazwa_komponentu, $wlasciwosc_komponentu, $id)
    {
        require_once("../vaElClass.php");
        
        $controls = new valControl();
        
        $objResponse = new xajaxResponse();
        $objResponse->setCharEncoding("ISO-8859-2");
	    $objResponse->addAssign($nazwa_komponentu, $wlasciwosc_komponentu, $controls->AddSelectRandomQuery("testcombo", "testc", "", "select id as nazwa from wakaty where id_klient = $id;", ""));
        
        return $objResponse;
    }
    //nazwa komponentu - html owa kontrolka na stronie, 
    //wlasciwosc komponentu - js property kontrolki jak np inner html
    //id_ref_name - nazwa w tabeli komorki z id, ktore ma referencje do tabeli nadrzednej - to jest kryterium wyboru pol z tabeli podrzednej
    //id_val - wartosc id z tabeli nadrzednej
    //table name - nazwa tabeli z ktorej robimy select do combo
    //combo name - nazwa powstajacego komba bedaca zarowno jego id
    //selHiddenId - id hiddena przyklejonego do selecta
    function GetComboContentById($nazwa_komponentu, $wlasciwosc_komponentu, $id_ref_name, $id_val, $tableName, $comboName, $selHiddenId)
    {
        require_once("../vaElClass.php");
        $controls = new valControl();
        
        $objResponse = new xajaxResponse();
        $objResponse->setCharEncoding("ISO-8859-2");
        $objResponse->addAssign($nazwa_komponentu, $wlasciwosc_komponentu, $controls->AddSelectRandomQuery($comboName, $comboName, "", "select id, nazwa from ".$tableName." where ".$id_ref_name." = ".$id_val.";", "", $selHiddenId, "nazwa", "id", ""));
        
        return $objResponse;
    }

    // Instantiate the xajax object.  No parameters defaults requestURI to this page, method to POST, and debug to off
    $xajax = new xajax(); 

    //$xajax->debugOn(); // Uncomment this line to turn debugging on

    // Specify the PHP functions to wrap. The JavaScript wrappers will be named xajax_functionname

    $xajax->registerFunction("GetBranches");
    $xajax->registerFunction("GetComboContentById");


    // Process any requests.  Because our requestURI is the same as our html page,
    // this must be called before any headers or HTML output have been sent
    $xajax->processRequests();  
?>