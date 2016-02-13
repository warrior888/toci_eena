<?

    $posNewCode = 0;
    $posOldCode = 1;
    $posNewName = 2;
    
    $content = file_get_contents('gr zawodowe.csv');
    $newGroupsContent = file_get_contents('klasyfikacja.txt');
    
    $newGroupsRows = explode("\r\n", $newGroupsContent);
    $newGroups = array();
    $prevCode = 0;
    
    foreach ($newGroupsRows as $newGroupsRow)
    {
        $code = substr($newGroupsRow, 0, 4);
        $rest = substr($newGroupsRow, 4);
        
        $code = (int)$code;
        
        if ($code > 0)
        {
            $newGroups[$code] = trim($rest);
        }
        else
        {
            if (!isset($newGroups[$prevCode]))
            {
                continue;
            }
            $newGroups[$prevCode] .= ' '.trim($newGroupsRow);
        }
        
        $prevCode = $code;
    }
    
    $rows = explode("\r\n", $content);
    
    $results = fopen('results.txt', 'w+');
    
    foreach ($rows as $row)
    {
        $elements = explode(';', $row);
        
        if (isset($elements[$posOldCode], $elements[$posNewCode], $elements[$posNewName], $newGroups[$elements[$posNewCode]]))
        {
            // dodac zmiane kodu, ogolnie update owac
            $query = 'update zawod set kod_grupy_2011 = kod_grupy, kod_grupy = \''.$elements[$posNewCode].'\', nazwa_2011 = nazwa, nazwa = \''.$newGroups[$elements[$posNewCode]].'\' where kod_grupy = \''.$elements[$posOldCode].'\';';
            if (8343  == $elements[$posNewCode])
            echo $query."\n";
            
            fputs($results, $query."\r\n");
        }
    }