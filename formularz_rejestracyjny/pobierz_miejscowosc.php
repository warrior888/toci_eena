<?php
//    require_once '../dal.php';
    require_once '../conf.php';
    $dal = new dal(true);
if(!is_callable('iconv')) {
    function iconv ($inEnc, $outEnc, $string) {
        return $string;
    }
}

if(!is_callable('json_encode')) {
    function json_encode ($obj) {
        $obj = array_shift($obj);
        $result = '[{%s}]';
        $resAr = array();
        foreach ($obj as $key => $value) {
            $resAr[] = '"'.$key.'":"'.$value.'"';
        }
        
        return sprintf($result, implode(',', $resAr));
    }
}    
    if (isset ($_GET['kod'])) 
    {
        $zapytanie = 'select miejscowosc.id, miejscowosc.nazwa as miejscowosc from kod_pocztowy join miejscowosc on kod_pocztowy.id_miejscowosc = miejscowosc.id where kod = \''.$_GET['kod'].'\';';
        
        $wynik = $dal->PobierzDane($zapytanie);

        if (isset($wynik[0]))
        {
            //$wynik[0]['miejscowosc'] = iconv('ISO-8859-2', 'UTF-8', $wynik[0]['miejscowosc']);   
            header ('Content-type: application/json; charset=iso-8859-2'); 
            echo json_encode($wynik); 
        }
    }
    
?>