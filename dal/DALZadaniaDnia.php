<?

    require_once 'dal/Model.php';
    
    class DALZadaniaDnia extends Model {
        
        public function __construct () {
            
            parent::__construct();
            $this->dal = dal::getInstance();
        }
        
        /**
         * get daily tasks view contents per params criteria
         * 
         * @param array $params
         */
        public function getDailyTasks ($params)
        {
            $query = 'select * from '.Model::VIEW_ZADANIA_DNIA.' where '.Model::COLUMN_ZDN_ACTIVE.' = true';
            
            // active true always (?)
            
            $dates = $params[Model::COLUMN_ZDN_DATA];
            
            $_idKonsultant = isset($params[Model::COLUMN_ZDN_ID_KONSULTANT]) ? $this->dal->escapeInt($params[Model::COLUMN_ZDN_ID_KONSULTANT]) : null;
            $_dateFrom = isset($dates[0]) ? $this->dal->escapeString($dates[0]) : null;
            $_dateTo = isset($dates[1]) ? $this->dal->escapeString($dates[1]) : null;
            
            $whereClauses = array();
            
            if ($_idKonsultant) 
            {
                $whereClauses[] = Model::COLUMN_ZDN_ID_KONSULTANT.' = '.$_idKonsultant;
            }
            
            if ($_dateFrom xor $_dateTo)
            {
                $whereClauses[] = Model::COLUMN_ZDN_DATA.' = \''.($_dateFrom ? $_dateFrom : $_dateTo).'\'';
            }
            else if ($_dateFrom)
            {
                $whereClauses[] = Model::COLUMN_ZDN_DATA.' between \''.$_dateFrom.'\' and \''.$_dateTo.'\'';
            }
            
            if (sizeof($whereClauses))
            {
                $query .= ' and ' . implode(' and ', $whereClauses);
            }
            
            $results = $this->dal->PobierzDane($query, $rowsCount);
            return $this->formatDataOutput($results, $rowsCount);
        }
        
        public function getDailyTasksFilters($userId)
        {
            $_userId = $this->dal->escapeInt($userId);
            
            $query = 'select * from '.Model::TABLE_ZADANIA_DNIA_KONSULTANT.' where '.Model::COLUMN_ZDK_ID_UPRAWNIENIA.' = '.$_userId;
            
            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            if ($rowsCount > 0)
            {
                $result[0][Model::COLUMN_ZDK_DANE_ZAPYTANIA] = unserialize($result[0][Model::COLUMN_ZDK_DANE_ZAPYTANIA]); // unescape ?
                return $this->formatDataOutput($result[0], $rowsCount);
            }
            
            return null;
        }
        
        public function setDailyTasksFilters($params)
        {
            $_data[Model::COLUMN_ZDK_ID_UPRAWNIENIA] = $_idKonsultant = $this->dal->escapeInt($params[Model::COLUMN_ZDK_ID_UPRAWNIENIA]);
            $_data[Model::COLUMN_ZDK_DANE_ZAPYTANIA] = $this->dal->escapeString(serialize($params[Model::COLUMN_ZDK_DANE_ZAPYTANIA]));
            
            $result = $this->getDailyTasksFilters($_idKonsultant);
            
            $isUpdate = !is_null($result);

            if ($isUpdate) 
            {
                $query = 'update '.Model::TABLE_ZADANIA_DNIA_KONSULTANT.' set '.$this->createSetClause($_data).' where '.
                Model::COLUMN_ZDK_ID_UPRAWNIENIA.' = '.$_idKonsultant;
            }
            else
            {
                $query = 'insert into '.Model::TABLE_ZADANIA_DNIA_KONSULTANT.$this->createInsertClause($_data);
            }

            return $this->dal->pgQuery($query);
        }
        
        public function setTask($personId, $params) {
            if(isset($params[Model::COLUMN_ZDN_ROW_ID]) && $params[Model::COLUMN_ZDN_ROW_ID] > 0) {
                $_data[Model::COLUMN_ZDN_ACTIVE]        = $this->dal->escapeBool($params[Model::COLUMN_ZDN_ACTIVE]);
                
                $query = 'UPDATE '.Model::TABLE_ZADANIA.' SET '.$this->createSetClause($_data).' WHERE '.
                Model::COLUMN_ZDN_ROW_ID.' = '.$this->dal->escapeInt($params[Model::COLUMN_ZDN_ROW_ID]);
            } else {
                $_data[Model::COLUMN_ZDN_ID]            = $this->dal->escapeInt($personId);
                $_data[Model::COLUMN_ZDN_ID_KONSULTANT] = $this->dal->escapeInt($params[Model::COLUMN_ZDN_ID_KONSULTANT]);
                $_data[Model::COLUMN_ZDN_INSERT_DATA]   = $this->dal->escapeString($params[Model::COLUMN_ZDN_INSERT_DATA]);
                $_data[Model::COLUMN_ZDN_DATA]          = $this->dal->escapeString($params[Model::COLUMN_ZDN_DATA]);
                $_data[Model::COLUMN_ZDN_PROBLEM]       = $this->dal->escapeString($params[Model::COLUMN_ZDN_PROBLEM]);
                $_data[Model::COLUMN_ZDN_ACTIVE]        = $this->dal->escapeBool($params[Model::COLUMN_ZDN_ACTIVE]);
                
                $query = 'INSERT INTO '.Model::TABLE_ZADANIA.$this->createInsertClause($_data);
            }
            
            $result = $this->dal->pgQuery($query);
            $affRows = pg_affected_rows($result);
            if ($affRows > 1) {
                LogManager::log(LOG_WARNING, '['.__CLASS__.'] Query affected '.$affRows.' in setTask : '.$query);
            }
            
            return (bool)$affRows;
        }
        
        public function getTaskList ($personId, $active = false) {
        
            $_personId = $this->dal->escapeInt($personId);
        
            $query = 'SELECT *
                      FROM  '.Model::TABLE_ZADANIA.'
                      JOIN  '.Model::TABLE_UPRAWNIENIA.' ON '.Model::TABLE_UPRAWNIENIA.'.'.Model::COLUMN_UPR_ID.' = '.Model::TABLE_ZADANIA.'.'.Model::COLUMN_ZDN_ID_KONSULTANT.'
                      WHERE '.Model::TABLE_ZADANIA.'.'.Model::COLUMN_ZDN_ID.' = '.$_personId.'
                              AND '. Model::TABLE_ZADANIA.'.'.Model::COLUMN_ZDN_ACTIVE.' = \''. (int)$active .'\'
                      ORDER BY '.Model::TABLE_ZADANIA.'.'.Model::COLUMN_ZDN_INSERT_DATA.' DESC';
        
            $result = $this->dal->PobierzDane($query, $recordsCount);
        
            if ($recordsCount == 0)
                return null;
        
            return $this->formatDataOutput($result, $recordsCount);
        }
    }