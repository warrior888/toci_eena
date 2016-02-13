<?
    //include("../conf.php");
    //include("../statystyka/date_class.php");
    //include("../dal.php");
    class klient //extends dal
    {
        public $tableName = "klient";
        public $tableId = "id";
    }
    class panstwo
    {
        public $tableName = "panstwo";
        public $tableId = "id";
    }
    class firma
    {
        public $tableName = "firma";
        public $tableId = "id";
    }
    class oddzial
    {
        public $tableName = "oddzialy_klient";
        public $tableId = "id";
    }
    class adresBiuro
    {
        public $tableName = "adres_biuro";
        public $tableId = "id";
    }
    class miejscowoscBiuro
    {
        public $tableName = "miejscowosc_biuro";
        public $tableId = "id";
    }
    class mscBiuro
    {
        public $tableName = "msc_biura";
        public $tableId = "id";
    }
    class warunkiOddzial
    {
        public $tableName = "warunki_oddzial";
        public $tableId = "id";
    }
    class warunkiZatrudnienia
    {
        public $tableName = "warunki_zatrudnienia";
        public $tableId = "id";
    }
    class stawkiOddzial
    {
        public $tableName = "oddzial_stawki";
        public $tableId = "id";
    }
    class grupyZawodowe
    {
        public $tableName = "zawod";
        public $tableId = "id";
    }
    class wakaty
    {
        public $tableName = "wakat";
        public $tableId = "id";
    }
    class daneOsobowe
    {
        public $tableName = "dane_osobowe";
        public $tableId = "id";
    }
    class zatrudnienie
    {
        public $tableName = "zatrudnienie";
        public $tableId = "id";
    }
    class status
    {
        public $tableName = "status";
        public $tableId = "id";
    }
    class charakter
    {
        public $tableName = "charakter";
        public $tableId = "id";
    }
    class decyzja
    {
        public $tableName = "decyzja";
        public $tableId = "id";
    }
    class mscPowrotWyodr
    {
        public $tableName = "wyodr_msc_powrot";
        public $tableId = "id"; 
    }
    class enum
    {
        public static $DONTALLOWDATA = 1;
        public static $ALLOWDATA = 2;
    }
?>