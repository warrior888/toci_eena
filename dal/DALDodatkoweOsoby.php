<?

    require_once 'dal/Model.php';
    
    class DALDodatkoweOsoby extends Model {
        
        public function __construct () {
            
            parent::__construct();
            $this->dal = dal::getInstance();
        }
        
        public function addPerson($personId, $params) {
            
            $_data[Model::COLUMN_DODOS_ID]           = $this->dal->escapeInt($personId);
            $_data[Model::COLUMN_DODOS_ID_OSOBY_DOD] = $this->dal->escapeInt($params[Model::COLUMN_DODOS_ID_OSOBY_DOD]);
            
            $query = 'INSERT INTO '.Model::TABLE_DODATKOWE_OSOBY . $this->createInsertClause($_data);
            
            $result = $this->dal->pgQuery($query);
            $affRows = pg_affected_rows($result);
            if ($affRows > 1) {
                LogManager::log(LOG_WARNING, '['.__CLASS__.'] Query affected '.$affRows.' in addPerson : '.$query);
            }
            
            return (bool)$affRows;
        }
        
        public function removePerson($personId, $params) {
        
            $personId      = $this->dal->escapeInt($personId);
            $idAddedPerson = $this->dal->escapeInt($params[Model::COLUMN_DODOS_ID_OSOBY_DOD]);
        
            $query = 'DELETE FROM '.Model::TABLE_DODATKOWE_OSOBY . 
                     ' WHERE '. Model::COLUMN_DODOS_ID .' = '. $personId .
                     ' AND ' .Model::COLUMN_DODOS_ID_OSOBY_DOD .' = '. $idAddedPerson;
        
            $result = $this->dal->pgQuery($query);
            $affRows = pg_affected_rows($result);
            if ($affRows > 1) {
                LogManager::log(LOG_WARNING, '['.__CLASS__.'] Query affected '.$affRows.' in removePerson : '.$query);
            }
        
            return (bool)$affRows;
        }
        
        public function getPersons ($personId) {
        
            $_personId = $this->dal->escapeInt($personId);
            
            $query = 'SELECT '. implode(array(Model::TABLE_IMIONA .'.'. Model::COLUMN_DICT_NAZWA, 
                                              Model::TABLE_DANE_OSOBOWE .'.'. Model::COLUMN_DIN_NAZWISKO, 
                                              Model::TABLE_DANE_OSOBOWE .'.'. Model::COLUMN_DIN_DATA_URODZENIA, 
                                              Model::TABLE_DODATKOWE_OSOBY .'.'. Model::COLUMN_DODOS_ID_OSOBY_DOD), ", ").'
                        FROM '.Model::TABLE_DODATKOWE_OSOBY.'
                        JOIN '.Model::TABLE_DANE_OSOBOWE.' ON '. Model::TABLE_DODATKOWE_OSOBY .'.'. Model::COLUMN_DODOS_ID_OSOBY_DOD.' = '. Model::TABLE_DANE_OSOBOWE .'.'. Model::COLUMN_DOS_ID .'
                        JOIN '.Model::TABLE_IMIONA .' ON '.Model::TABLE_IMIONA .'.'. Model::COLUMN_DICT_ID.' = '. Model::TABLE_DANE_OSOBOWE .'.'. Model::COLUMN_DOS_ID_IMIE .' 
                        WHERE '.Model::TABLE_DODATKOWE_OSOBY .'.'. Model::COLUMN_DODOS_ID ." = {$personId}
                        ORDER BY ". Model::TABLE_DANE_OSOBOWE .'.'. Model::COLUMN_DOS_ID .' ASC;';
        
            $result = $this->dal->PobierzDane($query, $recordsCount);
        
            if ($recordsCount == 0)
                return null;
        
            return $this->formatDataOutput($result, $recordsCount);
        }
    }