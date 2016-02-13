<?php
    require_once 'Model.php';

    class DALReklamacje extends Model
    {
        public function getComplaint($complaintId) {
            $complaintId = $this->dal->escapeInt($complaintId);
            
            $query = "SELECT * FROM ". Model::TABLE_REKLAMACJE . " WHERE ".
                    Model::COLUMN_REK_ID_REKLAMACJE . " = $complaintId";
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
        
            if ($recordsCount == 0)
                return null;
        
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        public function getComplaints($personId, $active = false)
        {
            $personId = (int) $personId;
            
            $activeStr = $active ? 'IS NULL' : 'IS NOT NULL';
            $query = 'SELECT r.'.Model::COLUMN_REK_ID_REKLAMACJE.', '
                        . 'r.'.Model::COLUMN_REK_DATA.', '
                        . 'u.'.Model::COLUMN_UPR_IMIE_NAZWISKO.', '
                        . 'r.'.Model::COLUMN_REK_PROBLEM.',  '
                        . 'b.'.Model::COLUMN_BIURA_NAME.', '
                        . 'R.'.Model::COLUMN_REK_ODP.' '
                    . 'FROM '.Model::TABLE_REKLAMACJE.' AS r '
                    . 'JOIN '.Model::TABLE_UPRAWNIENIA.' AS u ON r.'.Model::COLUMN_REK_ID_KONSULT.' = u.'.Model::COLUMN_UPR_ID.' '
                    . 'JOIN '.Model::TABLE_BIURA.' AS b ON b.'.Model::COLUMN_BIURA_ID.' = r.'.Model::COLUMN_REK_ID_BIURA.' '
                    . 'WHERE r.'.Model::COLUMN_REK_ID.' = '.$personId.' AND r.'.Model::COLUMN_REK_ODP." $activeStr "
                    . 'ORDER BY r.'.Model::COLUMN_REK_DATA.' DESC, '.Model::COLUMN_REK_ID_REKLAMACJE.' DESC ';

            $result = $this->dal->PobierzDane($query, $recordsCount);
        
            if ($recordsCount == 0)
                return null;
        
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        public function addComplaint($personId, $date, $problem, $id_konsult, $id_biuro) {
            $personId = $this->dal->escapeInt($personId);
            $date     = $this->dal->escapeString($date);
            $problem  = $this->dal->escapeString($problem);
            $id_konsult = $this->dal->escapeInt($id_konsult);
            $id_biuro = $this->dal->escapeInt($id_biuro);
            
            if(trim($problem) == '') {
                LogManager::log(LOG_WARNING, 'Problem jest pusty');
                return false;
            }
            
            $query = "INSERT INTO ".Model::TABLE_REKLAMACJE." (" .
                    Model::COLUMN_REK_ID. ", ".
                    Model::COLUMN_REK_DATA. ", ".
                    Model::COLUMN_REK_PROBLEM. ", ".
                    Model::COLUMN_REK_ID_KONSULT. ", ".
                    Model::COLUMN_REK_ID_BIURA. ") VALUES (
                    $personId, '$date', '$problem', $id_konsult, $id_biuro)";
            return $this->dal->pgQuery($query);
        }
        
        public function addAnswer($complaintId, $answer) {
            $complaintId = $this->dal->escapeInt($complaintId);
            $answer     = $this->dal->escapeString($answer);
            
            $query = "UPDATE ".Model::TABLE_REKLAMACJE." SET ".
                    Model::COLUMN_REK_ODP . " = '$answer' WHERE ".
                    Model::COLUMN_REK_ID_REKLAMACJE . " = $complaintId ";
            return $this->dal->pgQuery($query);
        }
    }