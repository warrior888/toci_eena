<?php

    session_start();
    
    if (empty($_SESSION['uzytkownik'])) {
        header('HTTP/1.1 401');
        die();
    }
    
    require_once '../vaElClass.php';
        
    function GetComboContentById($id_ref_name, $id_val, $tableName, $comboName, $selHiddenId)
    {
        $controls = new valControl();
        $html = $controls->AddSelectRandomQuery($comboName, $comboName, "", "select id, nazwa from ".$tableName." where ".$id_ref_name." = ".$id_val.";", "", $selHiddenId, "nazwa", "id", "");
        
        return $html;
    }
    
    if (isset($_GET['clientId'])) {
        
        header('Content-type: text/html; charset=iso-8859-2');
        echo GetComboContentById('id_klient', $_GET['clientId'], 'oddzialy_klient', 'oddzialy_klient', 'oddzial_id');
    }