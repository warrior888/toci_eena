<?php
    require_once ("../dal.php");
    
    class YearBalance
    {
        //consts : allows to enter new ids to query before run
        protected $ID = "_ID";
        //year will change each year :P, those two are variable in fillQuery below
        protected $YEAR = "_YEAR";
        protected $OCC = "_OCC";
        protected $WOMAN = "Kobieta";
        protected $Quater = 90;
        protected $OverYear = 366;
        protected $headers = array("Nazwa pañstwa", "nazwa grupy elementarnej zawodów", "cztero-cyfrowy symbol grupy elementarnej zawodów", "ogó³em", "w tym kobiety", "do 3 miesiêcy ogó³em", "w tym kobiety", "od 3 do 12 miesiêcy ogó³em", "w tym kobiety", "powy¿ej 12 miesiêcy ogó³em", "w tym kobiety");
        
        protected $DesYear = "";
        protected $BalanceData = array();
        protected $dataIndexer = 0;
        
        protected $CountriesTable = array();
        protected $StatisticData = array();
        protected $essentialQuery = "select distinct id_panstwo_pos as panstwo_id, panstwo.nazwa as panstwo from klient join panstwo on panstwo.id = klient.id_panstwo_pos;";
        protected $fillQuery = array();
        protected $DataQuery = "";
        protected $dbComm;
        protected $DsUnit;
        
        public function SetYear($year)
        {
            $this->DesYear = $year;
            $this->ReplaceYear();
            
        }
        protected function ReplaceYear()
        {
            $this->dbComm = dal::getInstance();
            
            $i = 0;
            //general query, 1 column, ogolem
            $this->fillQuery[$i++] = "select sum(zatr_stanowisko.people), zatr_stanowisko.stanowisko as zawod from 
            (select count(distinct zatrudnienie.id_osoba) as people, id_oddzial, stanowisko from zatrudnienie 
            join oddzialy_klient on oddzialy_klient.id = zatrudnienie.id_oddzial
            join umowa_ewidencja on zatrudnienie.id = umowa_ewidencja.id_wakat 
            where id_decyzja = (select id from decyzja where nazwa = 'Umówiony') 
            and data_wyjazdu between '".$this->YEAR."-01-01' and '".$this->YEAR."-12-31' and stanowisko in 
            (select distinct stanowisko from oddzialy_klient join zawod on zawod.id = oddzialy_klient.stanowisko where stanowisko != 1 and id_klient in 
            (select id from klient where id_panstwo_pos = ".$this->ID."))
            and zatrudnienie.id_klient in (select id from klient where id_panstwo_pos = ".$this->ID.") 
            group by id_oddzial, stanowisko) as zatr_stanowisko group by zatr_stanowisko.stanowisko;";
            //2 column, ogolem w tym kobiety
            $this->fillQuery[$i++] = "select sum(zatr_stanowisko.people), zatr_stanowisko.stanowisko as zawod from 
            (select count(distinct zatrudnienie.id_osoba) as people, id_oddzial, stanowisko from zatrudnienie 
            join oddzialy_klient on oddzialy_klient.id = zatrudnienie.id_oddzial 
            join umowa_ewidencja on zatrudnienie.id = umowa_ewidencja.id_wakat 
            join dane_osobowe d_o on d_o.id = zatrudnienie.id_osoba
            where d_o.id_plec = (select id from plec where nazwa = '".$this->WOMAN."') and
            id_decyzja = (select id from decyzja where nazwa = 'Umówiony') 
            and data_wyjazdu between '".$this->YEAR."-01-01' and '".$this->YEAR."-12-31' and stanowisko in 
            (select distinct stanowisko from oddzialy_klient join zawod on zawod.id = oddzialy_klient.stanowisko where stanowisko = ".$this->OCC." and id_klient in 
            (select id from klient where id_panstwo_pos = ".$this->ID."))
            and zatrudnienie.id_klient in (select id from klient where id_panstwo_pos = ".$this->ID.") 
            group by id_oddzial, stanowisko) as zatr_stanowisko group by zatr_stanowisko.stanowisko;";
            //do 3 miesiecy razem
            $this->fillQuery[$i++] = "select sum(zatr_stanowisko.people), zatr_stanowisko.stanowisko as zawod from 
            (select count(distinct zatrudnienie.id_osoba) as people, id_oddzial, stanowisko from zatrudnienie 
            join oddzialy_klient on oddzialy_klient.id = zatrudnienie.id_oddzial 
            join umowa_ewidencja on zatrudnienie.id = umowa_ewidencja.id_wakat  
            where id_decyzja = (select id from decyzja where nazwa = 'Umówiony') 
            and data_wyjazdu between '".$this->YEAR."-01-01' and '".$this->YEAR."-12-31' and data_powrotu - data_wyjazdu <= ".$this->Quater." and stanowisko in 
            (select distinct stanowisko from oddzialy_klient join zawod on zawod.id = oddzialy_klient.stanowisko where stanowisko = ".$this->OCC." and id_klient in 
            (select id from klient where id_panstwo_pos = ".$this->ID."))
            and zatrudnienie.id_klient in (select id from klient where id_panstwo_pos = ".$this->ID.") 
            group by id_oddzial, stanowisko) as zatr_stanowisko group by zatr_stanowisko.stanowisko;";
            //do 3 miesiecy w tym kobiety
            $this->fillQuery[$i++] = "select sum(zatr_stanowisko.people), zatr_stanowisko.stanowisko as zawod from 
            (select count(distinct zatrudnienie.id_osoba) as people, id_oddzial, stanowisko from zatrudnienie 
            join oddzialy_klient on oddzialy_klient.id = zatrudnienie.id_oddzial 
            join umowa_ewidencja on zatrudnienie.id = umowa_ewidencja.id_wakat 
            join dane_osobowe d_o on d_o.id = zatrudnienie.id_osoba
            where d_o.id_plec = (select id from plec where nazwa = '".$this->WOMAN."') and 
            id_decyzja = (select id from decyzja where nazwa = 'Umówiony') 
            and data_wyjazdu between '".$this->YEAR."-01-01' and '".$this->YEAR."-12-31' and data_powrotu - data_wyjazdu <= ".$this->Quater." and stanowisko in 
            (select distinct stanowisko from oddzialy_klient join zawod on zawod.id = oddzialy_klient.stanowisko where stanowisko = ".$this->OCC." and id_klient in 
            (select id from klient where id_panstwo_pos = ".$this->ID."))
            and zatrudnienie.id_klient in (select id from klient where id_panstwo_pos = ".$this->ID.") 
            group by id_oddzial, stanowisko) as zatr_stanowisko group by zatr_stanowisko.stanowisko;";
            //od 3 do 12 miesiecy razem
            $this->fillQuery[$i++] = "select sum(zatr_stanowisko.people), zatr_stanowisko.stanowisko as zawod from 
            (select count(distinct zatrudnienie.id_osoba) as people, id_oddzial, stanowisko from zatrudnienie 
            join oddzialy_klient on oddzialy_klient.id = zatrudnienie.id_oddzial 
            join umowa_ewidencja on zatrudnienie.id = umowa_ewidencja.id_wakat 
            where id_decyzja = (select id from decyzja where nazwa = 'Umówiony') 
            and data_wyjazdu between '".$this->YEAR."-01-01' and '".$this->YEAR."-12-31' and data_powrotu - data_wyjazdu > ".$this->Quater." and data_powrotu - data_wyjazdu < ".$this->OverYear." and stanowisko in 
            (select distinct stanowisko from oddzialy_klient join zawod on zawod.id = oddzialy_klient.stanowisko where stanowisko = ".$this->OCC." and id_klient in 
            (select id from klient where id_panstwo_pos = ".$this->ID."))
            and zatrudnienie.id_klient in (select id from klient where id_panstwo_pos = ".$this->ID.") 
            group by id_oddzial, stanowisko) as zatr_stanowisko group by zatr_stanowisko.stanowisko;";
            //od 3 do 12 miesiecy w tym kobiety
            $this->fillQuery[$i++] = "select sum(zatr_stanowisko.people), zatr_stanowisko.stanowisko as zawod from 
            (select count(distinct zatrudnienie.id_osoba) as people, id_oddzial, stanowisko from zatrudnienie 
            join oddzialy_klient on oddzialy_klient.id = zatrudnienie.id_oddzial 
            join umowa_ewidencja on zatrudnienie.id = umowa_ewidencja.id_wakat 
            join dane_osobowe d_o on d_o.id = zatrudnienie.id_osoba
            where d_o.id_plec = (select id from plec where nazwa = '".$this->WOMAN."') and 
            id_decyzja = (select id from decyzja where nazwa = 'Umówiony') 
            and data_wyjazdu between '".$this->YEAR."-01-01' and '".$this->YEAR."-12-31' and data_powrotu - data_wyjazdu > ".$this->Quater." and data_powrotu - data_wyjazdu < ".$this->OverYear." and stanowisko in 
            (select distinct stanowisko from oddzialy_klient join zawod on zawod.id = oddzialy_klient.stanowisko where stanowisko = ".$this->OCC." and id_klient in 
            (select id from klient where id_panstwo_pos = ".$this->ID."))
            and zatrudnienie.id_klient in (select id from klient where id_panstwo_pos = ".$this->ID.") 
            group by id_oddzial, stanowisko) as zatr_stanowisko group by zatr_stanowisko.stanowisko;";
            //powyzej 12 miesiecy razem
            $this->fillQuery[$i++] = "select sum(zatr_stanowisko.people), zatr_stanowisko.stanowisko as zawod from 
            (select count(distinct zatrudnienie.id_osoba) as people, id_oddzial, stanowisko from zatrudnienie 
            join oddzialy_klient on oddzialy_klient.id = zatrudnienie.id_oddzial 
            join umowa_ewidencja on zatrudnienie.id = umowa_ewidencja.id_wakat 
            where id_decyzja = (select id from decyzja where nazwa = 'Umówiony') 
            and data_wyjazdu between '".$this->YEAR."-01-01' and '".$this->YEAR."-12-31' and data_powrotu - data_wyjazdu >= ".$this->OverYear." and stanowisko in 
            (select distinct stanowisko from oddzialy_klient join zawod on zawod.id = oddzialy_klient.stanowisko where stanowisko = ".$this->OCC." and id_klient in 
            (select id from klient where id_panstwo_pos = ".$this->ID."))
            and zatrudnienie.id_klient in (select id from klient where id_panstwo_pos = ".$this->ID.") 
            group by id_oddzial, stanowisko) as zatr_stanowisko group by zatr_stanowisko.stanowisko;";
            //powyzej 12 miesiecy w tym kobiety
            $this->fillQuery[$i++] = "select sum(zatr_stanowisko.people), zatr_stanowisko.stanowisko as zawod from 
            (select count(distinct zatrudnienie.id_osoba) as people, id_oddzial, stanowisko from zatrudnienie 
            join oddzialy_klient on oddzialy_klient.id = zatrudnienie.id_oddzial 
            join umowa_ewidencja on zatrudnienie.id = umowa_ewidencja.id_wakat 
            join dane_osobowe d_o on d_o.id = zatrudnienie.id_osoba
            where d_o.id_plec = (select id from plec where nazwa = '".$this->WOMAN."') and 
            id_decyzja = (select id from decyzja where nazwa = 'Umówiony') 
            and data_wyjazdu between '".$this->YEAR."-01-01' and '".$this->YEAR."-12-31' and data_powrotu - data_wyjazdu >= ".$this->OverYear." and stanowisko in 
            (select distinct stanowisko from oddzialy_klient join zawod on zawod.id = oddzialy_klient.stanowisko where stanowisko = ".$this->OCC." and id_klient in 
            (select id from klient where id_panstwo_pos = ".$this->ID."))
            and zatrudnienie.id_klient in (select id from klient where id_panstwo_pos = ".$this->ID.") 
            group by id_oddzial, stanowisko) as zatr_stanowisko group by zatr_stanowisko.stanowisko;";
            $this->DataQuery = "select nazwa, kod_grupy from zawod where id = ".$this->ID.";";
            $i = 0;
            while (isset($this->fillQuery[$i]))
            {
                $this->fillQuery[$i] = str_replace($this->YEAR, $this->DesYear, $this->fillQuery[$i]);
                $i++;
            }
        }
        protected function GetCountries()
        {
            $result = $this->dbComm->pgQuery($this->essentialQuery);
            $i = 0;
            while ($row = pg_fetch_array($result))
            {
                $this->CountriesTable[$i] = array($row['panstwo_id'], $row['panstwo']);
                $i++;
            }
        }
        protected function PrepareOneDataSet($CountryId, $CountryName)
        {
            //index of row with data
            $i = 0;
            //index of table with queries
            $j = 0;
            $tableRes = array();
            $tableData = array();
            $this->DsUnit = new DataSet();
            $this->DsUnit->SetHeaders($this->headers);
            
            
            $query = str_replace($this->ID, $CountryId, $this->fillQuery[0]);
            $tableRes = $this->dbComm->pgQuery($query); 
            while($rowAb = pg_fetch_array($tableRes))
            {
                $tableData[$i++] = $CountryName;
                //fill preliminaries, then the rest of counts
                $query = str_replace($this->ID, $rowAb['zawod'], $this->DataQuery);
                $resultBelow = $this->dbComm->pgQuery($query);
                $rowBelow = pg_fetch_array($resultBelow);
                $tableData[$i++] = $rowBelow['nazwa'];
                $tableData[$i++] = $rowBelow['kod_grupy'];
                $tableData[$i++] = $rowAb['sum'];
                $j = 1;
                
                while(isset($this->fillQuery[$j]))
                {
                    $query = str_replace($this->ID, $CountryId, $this->fillQuery[$j]);
                    $query = str_replace($this->OCC, $rowAb['zawod'], $query);
                    $resQ = $this->dbComm->pgQuery($query);
                    $row = pg_fetch_array($resQ);
                    $tableData[$i++] = $row['sum'];
                    $j++;
                }
                $this->DsUnit->AddRow($tableData);
                $tableData = array();
                $i = 1;
            }
        }
        public function GetData()
        {
            $resultTable = array();
            $i = 0;
            $this->GetCountries();
            while(isset($this->CountriesTable[$i]))
            {
                $arr = $this->CountriesTable[$i];
                $this->PrepareOneDataSet($arr[0], $arr[1]);
                $this->StatisticData[$i] = array($this->DsUnit, $arr[1]);
                $i++;
            }
            return $this->StatisticData;
        }
    }
    class GeneralBalance
    {
        //consts : allows to enter new ids to query before run
        protected $ID = "_ID";
        //year will change each year :P, those two are variable in fillQuery below
        protected $YEAR = "_YEAR";
        protected $WOMAN = "Kobieta";
        protected $Quater = 25;
        protected $Half = 50;
        protected $daysYear = 365;
        protected $headers = array("Dziedzina", "Ogó³em", "w tym kobiety", "do 25 roku ¿ycia", "pomiêdzy 25 a 50 rokiem ¿ycia", "powy¿ej 50 roku ¿ycia");
        protected $rows = array("Liczba osób zapisanych", "liczba osób zatrudnionych", "praca na podstawie stosunku pracy");
        
        protected $StatisticData = array(); 
        
        protected $fillQuery = array();
        protected $DataQuery = "";
        protected $dbComm;
        protected $DsUnit;
        
        protected function Queries($year)
        {
            //Liczba osób, które zosta³y wpisane do prowadzonej przez agencjê ewidencji osób poszukuj¹cych zatrudnienia lub innej pracy zarobkowej
            $dzis = date('Y'."-".'m'."-".'d');
            $indexer = 0;
            $i = 0;
            $queries = array();
            $queries[$i++] = "select count(id) as ilosc from dane_osobowe where data_zgloszenia between '".$year."-01-01' and '".$year."-12-31';";
            $queries[$i++] = "select count(id) as ilosc from dane_osobowe where data_zgloszenia between '".$year."-01-01' and '".$year."-12-31' and id_plec = (select id from plec where nazwa = '".$this->WOMAN."');";
            $queries[$i++] = "select count(id) as ilosc from dane_osobowe where data_zgloszenia between '".$year."-01-01' and '".$year."-12-31' and ('".$dzis."'::DATE - data_urodzenia) / ".$this->daysYear." < ".$this->Quater.";";
            $queries[$i++] = "select count(id) as ilosc from dane_osobowe where data_zgloszenia between '".$year."-01-01' and '".$year."-12-31' and ('".$dzis."'::DATE - data_urodzenia) / ".$this->daysYear." between ".$this->Quater." and ".$this->Half.";";
            $queries[$i++] = "select count(id) as ilosc from dane_osobowe where data_zgloszenia between '".$year."-01-01' and '".$year."-12-31' and ('".$dzis."'::DATE - data_urodzenia) / ".$this->daysYear." > ".$this->Half.";";
            $this->fillQuery[$indexer++] = $queries;
            
            //Liczba osób, które podjê³y zatrudnienie lub inn¹ pracê zarobkow¹ za poœrednictwem agencji
            $i = 0;
            $queries = array();
            $queries[$i++] = "select count(distinct id_osoba) as ilosc from zatrudnienie 
            join klient on klient.id = zatrudnienie.id_klient 
            where data_wpisu between '".$year."-01-01' and '".$year."-12-31' and klient.id_panstwo_pos = (select id from panstwo where nazwa = 'Polska');";
            $queries[$i++] = "select count(distinct id_osoba) as ilosc from zatrudnienie 
            join dane_osobowe on zatrudnienie.id_osoba = dane_osobowe.id 
            join klient on klient.id = zatrudnienie.id_klient 
            where zatrudnienie.data_wpisu between '".$year."-01-01' and '".$year."-12-31' and dane_osobowe.id_plec = (select id from plec where nazwa = '".$this->WOMAN."') 
            and klient.id_panstwo_pos = (select id from panstwo where nazwa = 'Polska');";
            $queries[$i++] = "select count(distinct id_osoba) as ilosc from zatrudnienie 
            join dane_osobowe on zatrudnienie.id_osoba = dane_osobowe.id 
            join klient on klient.id = zatrudnienie.id_klient 
            where data_zgloszenia between '".$year."-01-01' and '".$year."-12-31' and ('".$dzis."'::DATE - data_urodzenia) / ".$this->daysYear." < ".$this->Quater." 
            and klient.id_panstwo_pos = (select id from panstwo where nazwa = 'Polska');";
            $queries[$i++] = "select count(distinct id_osoba) as ilosc from zatrudnienie 
            join dane_osobowe on zatrudnienie.id_osoba = dane_osobowe.id 
            join klient on klient.id = zatrudnienie.id_klient 
            where data_zgloszenia between '".$year."-01-01' and '".$year."-12-31' and ('".$dzis."'::DATE - data_urodzenia) / ".$this->daysYear." between ".$this->Quater." and ".$this->Half." 
            and klient.id_panstwo_pos = (select id from panstwo where nazwa = 'Polska');";
            $queries[$i++] = "select count(distinct id_osoba) as ilosc from zatrudnienie 
            join dane_osobowe on zatrudnienie.id_osoba = dane_osobowe.id 
            join klient on klient.id = zatrudnienie.id_klient 
            where data_zgloszenia between '".$year."-01-01' and '".$year."-12-31' and ('".$dzis."'::DATE - data_urodzenia) / ".$this->daysYear." > ".$this->Half." 
            and klient.id_panstwo_pos = (select id from panstwo where nazwa = 'Polska');";
            $this->fillQuery[$indexer++] = $queries;
            
            //w tym liczba osób, które podjê³y pracê na podstawie stosunku pracy
            $i = 0;
            $queries = array();
            $queries[$i++] = "select count(distinct id_osoba) as ilosc from zatrudnienie 
            join klient on klient.id = zatrudnienie.id_klient 
            where data_wpisu between '".$year."-01-01' and '".$year."-12-31' and klient.id_panstwo_pos = (select id from panstwo where nazwa = 'Polska');";
            $queries[$i++] = "select count(distinct id_osoba) as ilosc from zatrudnienie join dane_osobowe on zatrudnienie.id_osoba = dane_osobowe.id
            join klient on klient.id = zatrudnienie.id_klient  
            where zatrudnienie.data_wpisu between '".$year."-01-01' and '".$year."-12-31' and dane_osobowe.id_plec = (select id from plec where nazwa = '".$this->WOMAN."') 
            and klient.id_panstwo_pos = (select id from panstwo where nazwa = 'Polska');";
            $queries[$i++] = "select count(distinct id_osoba) as ilosc from zatrudnienie join dane_osobowe on zatrudnienie.id_osoba = dane_osobowe.id 
            join klient on klient.id = zatrudnienie.id_klient 
            where data_zgloszenia between '".$year."-01-01' and '".$year."-12-31' and ('".$dzis."'::DATE - data_urodzenia) / ".$this->daysYear." < ".$this->Quater." 
            and klient.id_panstwo_pos = (select id from panstwo where nazwa = 'Polska');";
            $queries[$i++] = "select count(distinct id_osoba) as ilosc from zatrudnienie join dane_osobowe on zatrudnienie.id_osoba = dane_osobowe.id 
            join klient on klient.id = zatrudnienie.id_klient 
            where data_zgloszenia between '".$year."-01-01' and '".$year."-12-31' and ('".$dzis."'::DATE - data_urodzenia) / ".$this->daysYear." between ".$this->Quater." and ".$this->Half." 
            and klient.id_panstwo_pos = (select id from panstwo where nazwa = 'Polska');";
            $queries[$i++] = "select count(distinct id_osoba) as ilosc from zatrudnienie join dane_osobowe on zatrudnienie.id_osoba = dane_osobowe.id 
            join klient on klient.id = zatrudnienie.id_klient 
            where data_zgloszenia between '".$year."-01-01' and '".$year."-12-31' and ('".$dzis."'::DATE - data_urodzenia) / ".$this->daysYear." > ".$this->Half." 
            and klient.id_panstwo_pos = (select id from panstwo where nazwa = 'Polska');";
            $this->fillQuery[$indexer++] = $queries;
            
            $this->dbComm = dal::getInstance(); 
        }
        protected function PrepareOneDataSetRow($year, $index)
        {
            //$this->Queries($year);
            $results = array();
            
            $queries = $this->fillQuery[$index];
            $i = 0;
            $results[$i++] = $this->rows[$index];
            
            $j = 0;
            while(isset($queries[$j]))
            {
                $resQuery = $this->dbComm->pgQuery($queries[$j]);
                $rowQuery = pg_fetch_array($resQuery);
                $results[$i++] = $rowQuery['ilosc'];
                $j++;
            }
            $this->DsUnit->AddRow($results);
        }
        public function GetData($year)
        {
            $i = 0;
            $this->DsUnit = new DataSet();
            $this->DsUnit->SetHeaders($this->headers);
            $this->Queries($year);
            while(isset($this->fillQuery[$i]))
            {
                $this->PrepareOneDataSetRow($year, $i);
                $i++;
            }
            $this->StatisticData[0] = array($this->DsUnit, "Punkt 1.1");
            return $this->StatisticData[0];
        }
    }
?>