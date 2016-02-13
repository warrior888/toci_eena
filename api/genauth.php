<?php

    function getHash ($timestamp, $clientId, $secret, $queryString) {
        
        $verificationPattern = '%s%d%d%s';
        parse_str($queryString, $queryParams);
        
        $paramsOrder = array(
            'ext',
            'id',
            'departureDateFrom',
            'departureDateTo',
            'returnDateFrom',
            'returnDateTo',
            'personId',
            'travelerId',
            'birthDate',
            'surname',
            'name',
        );
        
        $paramValues = '';
        foreach ($paramsOrder as $orderItem) {
            
            if (isset($queryParams[$orderItem]))
                $paramValues .= $queryParams[$orderItem];
        }
        
        $fullStr = sprintf($verificationPattern, $paramValues, $timestamp, $clientId, $secret);
        //echo $fullStr."\n";
        $hash = md5($fullStr);
        
        return $hash;
    }
    //php genauth.php 1 dwhvgwrhewvbwrgver23y13i6njgklwrn3129rit3rjceauktrq3buralenekstg574t23u23irahioealsw "ext=xml&departureDateFrom=12"
    $clientId = 2; //(int)$argv[1];
    $secret = "cdkgjoweipgh4674393ugkdsngjldfgakdbgsidgjskdfhnsdfkbn35u768ugwriughskdvndsjlgh4t7y428"; //$argv[2]; // dwhvgwrhewvbwrgver23y13i6njgklwrn3129rit3rjceauktrq3buralenekstg574t23u23irahioealsw
    $queryString = "ext=json"; // &id=6926 //$argv[1]; //$argv[3]; // ext=xml&departureDateFrom=12

    $pattern = '%d/%d/%s';
    $timestamp = time();

    echo sprintf('x-Authorization: '.$pattern, $clientId, $timestamp, getHash($timestamp, $clientId, $secret, $queryString));
    echo "\n";