<?


array(2) 
{ 
    ["dane_osobowe"]=> 
    array(3) 
    { 
        ["show"]=> 
        array(2) 
        { 
            ["imie"]=> string(4) "imie" 
            ["nazwisko"]=> string(8) "nazwisko" 
        } 
        ["filter"]=> 
        array(1) 
        { 
            ["nazwisko"]=> string(1) "%" 
        } 
        ["missing"]=> 
        array(2) 
        { 
            ["data"]=> bool(true) 
            ["ilosc_tyg"]=> bool(true) 
        } 
    } 
    ["queries"]=> 
    array(1) 
    { 
        [10]=> 
        array(1) 
        { 
            ["queries_filter_show"]=> 
            array(1) 
            { 
                [0]=> array(2) 
                { 
                    ["query"]=> string(278) "select dane_osobowe.id, dane_osobowe.id as osoba_id, imiona.nazwa as imie, dane_osobowe.nazwisko from dane_osobowe join imiona on dane_osobowe.id_imie=imiona.id where (lower(dane_osobowe.nazwisko) like lower('%')) and dane_osobowe.data is null and dane_osobowe.ilosc_tyg is null" ["osoba_id"]=> string(15) "dane_osobowe.id" 
                } 
            } 
        } 
    } 
}


array(3) 
{ 
    ["dane_osobowe"]=> 
    array(3) 
    { 
        ["show"]=> 
        array(2) 
        { 
            ["imie"]=> string(4) "imie" 
            ["nazwisko"]=> string(8) "nazwisko" 
        } 
        ["filter"]=> 
        array(1) 
        { 
            ["nazwisko"]=> string(1) "%" 
        } 
        ["missing"]=> 
        array(2) 
        { 
            ["data"]=> bool(true) 
            ["ilosc_tyg"]=> bool(true) 
        } 
    } 
    ["telefon"]=> 
    array(2) 
    { 
        ["show"]=> 
        array(1) 
        { 
            ["telefon"]=> string(7) "telefon" 
        } 
        ["filter"]=> 
        array(1) 
        { 
            ["telefon"]=> string(1) "%" 
        } 
    } 
    ["queries"]=> 
    array(2) 
    { 
        [9]=> 
        array(1) 
        { 
            ["queries_filter_show"]=> 
            array(1) 
            { 
                [0]=> 
                array(2) 
                { 
                    ["query"]=> string(278) "select dane_osobowe.id, dane_osobowe.id as osoba_id, imiona.nazwa as imie, dane_osobowe.nazwisko from dane_osobowe join imiona on dane_osobowe.id_imie=imiona.id where (lower(dane_osobowe.nazwisko) like lower('%')) and dane_osobowe.data is null and dane_osobowe.ilosc_tyg is null" 
                    ["osoba_id"]=> string(15) "dane_osobowe.id" 
                } 
            } 
        } 
        [3]=> 
        array(1) 
        { 
            ["queries_filter_show"]=> 
            array(1) 
            { 
                [0]=> 
                array(2) 
                { 
                    ["query"]=> string(109) "select telefon.id, telefon.id as osoba_id, nazwa as telefon from telefon where (lower(nazwa) like lower('%'))" 
                    ["osoba_id"]=> string(10) "telefon.id" 
                } 
            } 
        } 
    } 
}  