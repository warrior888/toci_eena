<?php 

    require_once '../dal.php';
    require_once '../dal/DALDaneOsobowe.php';

    $handle = fopen('id_missingcontact_list.txt', 'r');
    $dal = dal::getInstance();
    $dalDaneOs = new DALDaneOsobowe();
    $i = 0;
    
    while ($line = fgets($handle))
    {
        $id = (int)$line;
        
        if ($id) {
            
            $query = 'select * from audyt_log where zapytanie like \'%telefon_kom%'.$id.'%\';';
            $result = $dal->PobierzDane($query);
            $zapytanie = $result[0]['zapytanie'];
            echo $zapytanie;
            if ($zapytanie)
            {
                sscanf($zapytanie, "update telefon_kom set nazwa = '%d' where id = %d", $tel, $retId);
                $_tel = (int)$tel;
                $_retId = (int)$retId;
                
                $dalDaneOs->setCell($_retId, $_tel);
            }
            
            $i++;
            //todo select, scanf, insert through dal logic ofc
        }
    }