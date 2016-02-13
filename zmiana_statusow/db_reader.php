<?php
    class DbReader
    {
        private $db;
        private $dal;
        private $dataSet;
        private $statusArray;
        
        public function __construct()
        {
            $this->dal = dal::getInstance();
            $this->db = $this->dal->dbConnect();
        }   
        
        public function GetData($office)
        {
            $query = "SELECT * FROM dane_osobowe;";
            $this->statusArray = new StatusChangeDataArray();
    		
    		return $this->dataSet;
        }
        
        public function CheckInDb($sofi)
        {
            $result;
            
            $query = "SELECT dane_osobowe.id FROM dane_osobowe JOIN dokumenty ON dane_osobowe.id = dokumenty.id and dokumenty.nip = '$sofi';";            
            $data = $this->dal->pgQuery($query);
            
            if (pg_num_rows($data) == 0)
            {
                $result = null;
            }
            else
            {
                $row = pg_fetch_array($data);
                $result = $row['id'];
            }
            
            return $result;
        }
        
        public function UpdateDate($id, $new_date, $office)
        {            
            $query = "UPDATE zatrudnienie SET data_powrotu = '$new_date', id_status = (SELECT id FROM status WHERE nazwa = 'Aktywny') WHERE zatrudnienie.id_oddzial IN $office AND  id_osoba = '$id' AND data_wyjazdu = (SELECT MAX(z1.data_wyjazdu) FROM zatrudnienie z1 WHERE z1.data_wyjazdu <  '$new_date' AND z1.id_osoba = zatrudnienie.id_osoba);";
            $data = $this->dal->pgQuery($query);
            
            return pg_affected_rows($data);
        }
        
        public function UpdateStatus($id, $new_date, $office)
        {            
            $query = "UPDATE zatrudnienie SET id_status = (SELECT id FROM status WHERE nazwa = 'Aktywny') WHERE zatrudnienie.id_oddzial IN $office AND  id_osoba = '$id' AND data_wyjazdu = (SELECT MAX(z1.data_wyjazdu) FROM zatrudnienie z1 WHERE z1.data_wyjazdu <  '$new_date' AND z1.id_osoba = zatrudnienie.id_osoba);";
            $data = $this->dal->pgQuery($query);
            
            return pg_affected_rows($data);
        }
        
        public function GetOddzialy($office)
        {
            $result = "";
            $query = "SELECT oddzialy_klient.id FROM oddzialy_klient JOIN miejscowosc_biuro ON oddzialy_klient.id_biuro = miejscowosc_biuro.id JOIN msc_biura ON miejscowosc_biuro.id_msc_biuro = msc_biura.id WHERE msc_biura.nazwa = '$office'";
            $data = $this->dal->pgQuery($query);
            
            while ($row = pg_fetch_array($data))
            {
                $result .= $row['id'].",";
            }
            
            $result = substr($result, 0, strlen($result) - 1);
            
            return "(".$result.")";
        }
        
        public function GetZatrudnienieID($date, $office, $idFromExcel)
        {
            $result = "";
            $query = "SELECT id FROM zatrudnienie WHERE data_powrotu = '$date' AND id_oddzial IN $office AND id_status = (SELECT id FROM status WHERE nazwa = 'Aktywny') AND id_osoba NOT IN $idFromExcel;";
            $data = $this->dal->pgQuery($query);
            
            while ($row = pg_fetch_array($data))
            {
                $result .= $row['id'].",";
            }
            
            $result = substr($result, 0, strlen($result) - 1);
            
            return "(".$result.")";
        }
        
        public function GetZatrudnienieIdForStatus($office, $idFromExcel)
        {
            $result = "";
            $query = "SELECT id FROM zatrudnienie WHERE id_oddzial IN $office AND id_status = (SELECT id FROM status WHERE nazwa = 'Aktywny') AND id_osoba NOT IN $idFromExcel;";
            $data = $this->dal->pgQuery($query);
            
            while ($row = pg_fetch_array($data))
            {
                $result .= $row['id'].",";
            }
            
            $result = substr($result, 0, strlen($result) - 1);
            
            return "(".$result.")";
        }
        
        public function GetIdFromDb($date, $office, $idFromExcel)
        {
            $result = "";
            $query = "SELECT id_osoba FROM zatrudnienie WHERE data_powrotu = '$date' AND id_oddzial IN $office AND id_status = (SELECT id FROM status WHERE nazwa = 'Aktywny') AND id_osoba NOT IN $idFromExcel;";
            $data = $this->dal->pgQuery($query);
            
            while ($row = pg_fetch_array($data))
            {
                $result .= $row['id'].",";
            }
            
            $result = substr($result, 0, strlen($result) - 1);
            
            return "(".$result.")";
        }
        
        public function GetIdFromDbForStatus($office, $idFromExcel)
        {
            $result = "";
            $query = "SELECT id_osoba FROM zatrudnienie WHERE id_oddzial IN $office AND id_status = (SELECT id FROM status WHERE nazwa = 'Aktywny') AND id_osoba NOT IN $idFromExcel;";
            $data = $this->dal->pgQuery($query);
            
            while ($row = pg_fetch_array($data))
            {
                $result .= $row['id'].",";
            }
            
            $result = substr($result, 0, strlen($result) - 1);
            
            return "(".$result.")";
        }
        
        public function GetPeopleFromDb($tabZatrudnienieId)
        {
            $data = array();
            
            $query = "SELECT imiona.nazwa AS imie, dane_osobowe.nazwisko, dane_osobowe.data_urodzenia FROM dane_osobowe JOIN imiona ON imie.id = dane_osobowe.id_imie WHERE dane_osobowe.id IN $tabZatrudnienieId;";
            $data = $this->dal->pgQuery($query);
            
            while ($row = pg_fetch_array($data))
            {
                $data[] = $this->CreateRow($row);
            }
            
            return $data;
        }
        
        private function CreateRow($fetchArray)
        {
            $row = array();
            
            foreach($fetchArray as $key => $value)
            {
                $row[] = $value;   
            }
            
            return $row;
            
        }
        
        public function AddWeekToDb($tabId, $date)
        {
            $year = substr($date, 0, 4);
            $month = substr($date, 5, 2);
            $day = substr($date, 8, 2);
            $new_date = date("Y-m-d", mktime(0, 0, 0, $month, $day + 7, $year));
            
            $query = "UPDATE zatrudnienie SET data_powrotu = '$new_date' WHERE id IN $tabId;";
            $data = $this->dal->pgQuery($query);
        }
        
        public function ToPasywny($tabId)
        {            
            $query = "UPDATE zatrudnienie SET id_status = (SELECT id FROM status WHERE nazwa = 'Aktywny') WHERE id IN $tabId;";
            $data = $this->dal->pgQuery($query);
        }
    }
?>