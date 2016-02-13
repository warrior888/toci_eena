<meta http-equiv="Content-Type" content="text/html; charset=Windows-1250">

<?php 
    //header('Content-encoding: ISO-8859-2');
    //header('Content-type: text/html; iso-8859-2');

    $newGroupsContent = file_get_contents('d:/projects/metadane/klasyfikacja_ansi.txt');
    
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
    
    /*$newCodesSet = array();
    foreach ($newGroups as $code => $txtContent) 
    {
        $newCodesSet[] = '(\''.$code.'\', \''.$txtContent.'\')'; //iconv('ISO_8859-2', 'UTF-8', 
    }

    echo 'select k1.kod, k1.nazwa from (
    	values
    	'.implode(',', $newCodesSet).'
    ) as k1 (kod, nazwa) left join zawod on zawod.kod_grupy = k1.kod where zawod.kod_grupy is null;';
    */
    
    /*
     
      select k1.nazwa, k1.kod from 
      (
      	values
      	('dsad', '7212'),
      	('tyrjyj', '6549')
      ) as k1 (nazwa, kod) left join zawod on zawod.kod_grupy = k1.kod where zawod.kod_grupy is null;
     
     */