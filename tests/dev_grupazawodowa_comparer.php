<?php

    $posNewCode = 0;
    $posOldCode = 1;
    $posNewName = 2;
    
    $content = file_get_contents('d:/projects/metadane/gr zawodowe.csv');
    $missingGroupsContent = file_get_contents('d:/projects/metadane/braki_klasyfikacja.txt');
    
    $missingGroupsRows = explode("\r\n", $missingGroupsContent);
    
    $missingGroups = array();
    // TODO to jest zle, przniesc do nowego pliku z lista koscielnego
    foreach ($missingGroupsRows as $missingGroupsRow)
    {
        list($missingCode, $missingGroup) = explode('	', $missingGroupsRow);
        //var_dump($missingCode, $missingGroup);
        $missingGroups[$missingCode] = $missingGroup;
    }
    
    //$missingGroupsByName = array_flip($missingGroups);
    
    $rows = explode("\r\n", $content);
    //var_dump($missingGroupsByName);
    $count = 0;
    foreach ($rows as $row)
    {
        $elements = explode(';', $row);
        
        if (isset($missingGroups[$elements[$posNewCode]]))
        {
            $count++;
            echo($elements[$posNewName])."\n";
        }
    }
    
    var_dump($count);
    
    //$common = array_intersect_key($newGroups, $missingGroups);
    
    //var_dump(sizeof($common));