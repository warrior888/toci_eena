<?php
    require_once 'Model.php';

    class DALJarograf extends Model
    {
        
        public function getJarografs($personId) {
            
            $personId = $this->dal->escapeInt($personId);
            
            $query = "SELECT * FROM ". Model::TABLE_JAROGRAF 
            . ' JOIN '.Model::TABLE_KLIENT.' ON '.Model::TABLE_KLIENT .'.'.Model::COLUMN_KLN_ID.' = '.Model::TABLE_JAROGRAF.'.'.Model::COLUMN_JRG_ID_KLIENT
            .' WHERE '.Model::TABLE_JAROGRAF.'.'.Model::COLUMN_JRG_ID." = ".$personId.";";
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0) {
                return null;
            }
            
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        public function getClients($personId)
        {
            $personId = $this->dal->escapeInt($personId);
            
            $query = 'SELECT DISTINCT '. 
                            Model::TABLE_KLIENT .'.'. Model::COLUMN_KLN_ID . ', '.
                            Model::TABLE_KLIENT .'.'. Model::COLUMN_KLN_NAZWA . 
                       ' FROM '.Model::TABLE_KLIENT.'
                        JOIN '.Model::TABLE_ZATRUDNIENIE.' ON '. Model::TABLE_KLIENT .'.'. Model::COLUMN_KLN_ID.' = '. Model::TABLE_ZATRUDNIENIE .'.'. Model::COLUMN_ZTR_ID_KLIENT .'
                        WHERE '.Model::TABLE_ZATRUDNIENIE .'.'. Model::COLUMN_ZTR_ID_OSOBA ." = {$personId}
                        ORDER BY ". Model::TABLE_KLIENT .'.'. Model::COLUMN_KLN_NAZWA .' ASC;';
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0) {
                return null;
            }
            
            return $result;
        }
        
        public function deleteJarograf($filePath) {
            $query = "DELETE FROM ". Model::TABLE_JAROGRAF . " 
                    WHERE " . Model::COLUMN_JRG_PLIK ."= '".$filePath."';";
            return $this->dal->pgQuery($query);
        }
        
        public function setReceived($personId, $year, $date, $userId) {
            $personId = $this->dal->escapeInt($personId);
            $year     = $this->dal->escapeInt($year);  
            $date     = $this->dal->escapeString($date);
            $userId   = $this->dal->escapeInt($userId);
            
            $query = "INSERT INTO ".Model::TABLE_ODEBRANY."
                        VALUES ('".$personId."', '".$userId."', '".$date."', '".$year."');";
            return $this->dal->pgQuery($query);
        }
        
        public function checkReceived($personId, $year) {
            $personId = $this->dal->escapeInt($personId);
            $year     = $this->dal->escapeInt($year);
            
            $query = "SELECT ".Model::TABLE_UPRAWNIENIA.".".Model::COLUMN_UPR_IMIE_NAZWISKO.", ". Model::TABLE_ODEBRANY .".".Model::COLUMN_ODB_DATA ." 
                        FROM ".Model::TABLE_ODEBRANY." 
                        JOIN ".Model::TABLE_UPRAWNIENIA." ON ".Model::TABLE_UPRAWNIENIA.".".Model::COLUMN_UPR_ID." = ".Model::TABLE_ODEBRANY.".".Model::COLUMN_ODB_ID_KONSULTANT." 
                            AND ".Model::TABLE_ODEBRANY.".".Model::COLUMN_ODB_ID." = '".$personId."' 
                            AND ".Model::TABLE_ODEBRANY.".".Model::COLUMN_ODB_ROK." = '".$year."';";
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0) {
                return null;
            }
            
            return $this->formatDataOutput($result[0], $recordsCount);
        }
        
        public function getFile($personId, $year) {
            $personId = $this->dal->escapeInt($personId);
            $year     = $this->dal->escapeInt($year);
            
            $query = "SELECT ".Model::COLUMN_JRG_PLIK." FROM ".Model::TABLE_JAROGRAF." WHERE ".Model::COLUMN_JRG_ID." = '".$personId."' AND ".Model::COLUMN_JRG_ROK." = '".$year."';";
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0) {
                return null;
            }
            
            return $this->formatDataOutput($result, $recordsCount);
        }
    }