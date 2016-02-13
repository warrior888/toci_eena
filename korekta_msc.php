<?php

/*
update dane_osobowe set id_miejscowosc_ur = 1949 where id_miejscowosc_ur in (2463);
update dane_osobowe set id_miejscowosc = 1949 where id_miejscowosc in (2463);
update kod_pocztowy set id_miejscowosc = 1949 where id_miejscowosc in (2463);


delete from miejscowosc where id in (2463);
*/

require_once 'conf.php';
require_once 'dal.php';

$dal = dal::getInstance();

$result = $dal->PobierzDane('select id, nazwa from miejscowosc where nazwa in (select nazwa from miejscowosc group by nazwa having count(nazwa) > 1) order by nazwa,id;');

$data = array();
foreach ($result as $row) {
    $data[$row['nazwa']][] = $row['id'];
}
//2238
//select * from miejscowosc m1 where (select count(nazwa) from miejscowosc where nazwa like 'M.%' || m1.nazwa) > 0;
foreach ($data as $row) {
    
    $id = array_shift($row);
    $restIds = implode(',', $row);
    echo sprintf('update dane_osobowe set id_miejscowosc_ur = %s where id_miejscowosc_ur in (%s);', $id, $restIds)."<br />";
    echo sprintf('update dane_osobowe set id_miejscowosc = %s where id_miejscowosc in (%s);', $id, $restIds)."<br />";
    echo sprintf('update kod_pocztowy set id_miejscowosc = %s where id_miejscowosc in (%s);', $id, $restIds)."<br />";
    echo sprintf('delete from miejscowosc where id in (%s);', $restIds)."<br />";
}