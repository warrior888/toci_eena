<?php 
    require_once 'conf.php';
    require_once 'dal.php';

    $handle = fopen('ids.txt', 'r');
    $dal = dal::getInstance(); 
    
    while($line = fgets($handle))
    {
        $items = explode("\t", $line);
        $currentId = (int)$items[5];
        $query = 'update dane_osobowe set id_konsultant = 21 where id = '.$currentId.';';
        $dal->pgQuery($query);
    }
    
    fclose($handle);