<?php

    require_once '../conf.php';
    require_once 'dal.php';
    require_once 'dal/Model.php';
    require_once 'bll/BLLDaneInternet.php';
    
    $dal = dal::getInstance();
    $bllDaneInternet = new BLLDaneInternet();
    
    $result = $dal->PobierzDane("select msg from log_manager where msg like '%Request invalid, missing or invalid session id, env:%';");
    
    $map = array(
    
        1 => Model::COLUMN_DIN_ID_MIEJSCOWOSC,
        2 => Model::COLUMN_DIN_NAZWISKO,
        3 => Model::COLUMN_DIN_KOMORKA,
        4 => Model::COLUMN_DIN_IMIE,
        5 => Model::COLUMN_DIN_ID_IMIE,
        6 => Model::COLUMN_DIN_EMAIL,
        7 => Model::COLUMN_DIN_PLEC,
        8 => Model::COLUMN_DIN_ID_PLEC,
        9 => Model::COLUMN_DIN_DATA, //_ZGLOSZENIA skopiowac ?
        10 => Model::COLUMN_DIN_DATA_URODZENIA, 
        11 => Model::COLUMN_DIN_ILOSC_TYG, 
        12 => Model::COLUMN_DIN_MIEJSCOWOSC_UR, 
        13 => Model::COLUMN_DIN_ID_MIEJSCOWOSC_UR, 
        14 => Model::COLUMN_DIN_ZRODLO, 
        15 => Model::COLUMN_DIN_ID_ZRODLO, 
        16 => Model::COLUMN_DIN_ULICA, 
        17 => Model::COLUMN_DIN_WYKSZTALCENIE, 
        18 => Model::COLUMN_DIN_ID_WYKSZTALCENIE, 
        19 => Model::COLUMN_DIN_KOD, 
        20 => Model::COLUMN_DIN_ID_ZAWOD, 
        21 => Model::COLUMN_DIN_ZAWOD, 
        22 => Model::COLUMN_DIN_MIEJSCOWOSC, 
        23 => Model::COLUMN_DIN_ID_MIEJSCOWOSC, 
        24 => Model::COLUMN_DIN_CHARAKTER, 
        25 => Model::COLUMN_DIN_ID_CHARAKTER, 
        27 => 'wzrost', 
    );
    
    $resultsNumber = 0;
    $errorsNumber = 0;
    $total = 0;
    
    foreach ($result as $row)
    {
        $msg = $row['msg'];
        $start = strpos($msg, 'array (');
        $end = strpos($msg, ')');
        $candidateRawData = substr($msg, $start, $end + 1 - $start);
        
        eval('$table = '.$candidateRawData . ';');
//        var_dump($table);
        
        $i = 2;
        $candidate = array();
        $canAdd = true;
        array_shift($table);
        $nameOrCityId = array_shift($table);
        
        $val = (int)$nameOrCityId;
        
        if ($val > 0) 
        {
            var_dump($nameOrCityId);
            continue;
        }
        
        foreach ($table as $data) {
            
            if (isset($map[$i])) {
                $candidate[$map[$i]] = stripslashes($data);
                //$canAdd = $canAdd && !empty($data);
            }
                
            $i++;
            //var_dump($candidate);
        }
        
        $total++;

        $canAdd = $canAdd && !empty($candidate[Model::COLUMN_DIN_DATA_URODZENIA]);
        if (!isset($candidate[Model::COLUMN_DIN_ID_ZAWOD]) || !$candidate[Model::COLUMN_DIN_ID_ZAWOD])
        {
            $candidate[Model::COLUMN_DIN_ID_ZAWOD] = 1;
        }
        if ($canAdd && count($candidate)) {
            
            //echo $candidate[Model::COLUMN_DIN_NAZWISKO].', '.$candidate[Model::COLUMN_DIN_DATA_URODZENIA];
            var_dump($candidate);
            $resultsCount = 0;
            try {
                $dal->PobierzDane('select * from dane_internet where data_urodzenia = \''.$candidate[Model::COLUMN_DIN_DATA_URODZENIA].'\' and lower(nazwisko) like lower(\''.$candidate[Model::COLUMN_DIN_NAZWISKO].'%\') ', $resultsCount);
            } catch (Exception $e) {
                var_dump($e->getMessage());
                $errorsNumber++;
                continue;
            }
            
            if ($resultsCount == 0)
            {
                try {
                    
                    $bllDaneInternet->set($candidate);
                    $resultsNumber++;
                } catch (Exception $e) {
                    $errorsNumber++;
                }
            }
        }
        
        var_dump($canAdd);
        echo "\n";
    }
    
    echo 'Success: '.$resultsNumber;
    echo 'Error: '.$errorsNumber;
    echo 'total: '.$total;
    