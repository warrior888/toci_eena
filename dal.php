<?php
    //include('/home/azg/public_html/conf.php');
    include_once('conf.php');
    include_once($path."statystyka/date_class.php");
    require_once 'bll/LogManager.php';
    require_once 'bll/SessionManager.php';
    
    //converting to singleton - the only reasonable option; construct public - backward compatibility
    class dal
    {
        const STATUS_ID_AKTYWNY   = 1;
        const STATUS_ID_PASYWNY   = 4;
        
        const SESSION_FIELD_USER    = 'user';
        
        protected $database;            
        protected $con_str = DATABASE_CONNECTION_STRING;
        protected $userData;
        
        public $result;
        private $queries = array("queryName" => 'select id, nazwa from imiona order by nazwa asc;', 
                            "queryGender" => 'select id, nazwa from plec order by nazwa asc;',
                            "queryMsc" => 'select id, nazwa from miejscowosc order by nazwa asc;',
                            "queryEdu" => 'select id, nazwa from wyksztalcenie order by nazwa asc;',
                            "queryChar" => 'select id, nazwa from charakter order by nazwa asc;',
                            "queryPas" => 'select id, nazwa from paszport order by nazwa asc;',
                            "queryAn" => 'select id, nazwa from ankieta order by nazwa asc;',
                            "querySrc" => 'select id, nazwa from zrodlo where widoczne = true order by nazwa asc;');
        
        protected $currentQuery = '';
        private $dontUseEnc = false;
        
        private static $instances = array();
        
        public static function getInstance ($dontUseEnc = false) 
        {
            $index = 'LATIN2';
            if ($dontUseEnc)
                $index = 'UTF8';
            
            if (isset(self::$instances[$index]))
            {
                return self::$instances[$index];
            }
            
            self::$instances[$index] = new dal($dontUseEnc);
            
            return self::$instances[$index];
        }
        
        public function __construct ($dontUseEnc = false)
        {
            $this->dontUseEnc = $dontUseEnc;
        }
        
        public function dbConnect()
        {
            if (!isset($this->database))
            {
                $this->database = pg_pconnect($this->con_str);
                if (!$this->dontUseEnc)
                    pg_set_client_encoding ($this->database, 'LATIN2');
            }
            return $this->database;
        }
        public function dbClose()
        {
            pg_close($this->database);
        }
        public function dbQuery($query, $getAsArray = false)
        {
            $this->result = pg_query($this->dbConnect(), $this->queries["$query"]);
            if ($getAsArray)   
                $this->result = pg_fetch_all($this->result);
            return $this->result;
        }
        public function pgQuery($query)
        {
            //$h = fopen('/var/www/html/eena/devel/query', 'a');
            //fputs($h, $query."\n");  
            //echo $query."<br>";
            $this->trySaveAuditLog($query);
            //$time = microtime(true);
            set_error_handler(array($this, 'errorHandler'), E_WARNING);
            $this->currentQuery = $query;
            $this->result = pg_query($this->dbConnect(), $query);
            $this->currentQuery = '';
            restore_error_handler();
            //$end = microtime(true);
            //$res = $end - $time;
            //fputs($h, "pgQuery in ".$res."\n");
            //fclose($h); 
            return $this->result;
        }
        public function PobierzDane($zapytanie, &$ilosc_wierszy = 0)
        {
            //$h = fopen('/var/www/html/eena/devel/query', 'a');
            //fputs($h, $zapytanie."\n");
            set_error_handler(array($this, 'errorHandler'), E_WARNING);
            $this->currentQuery = $zapytanie;
            $result = pg_query($this->dbConnect(), $zapytanie);
            $this->currentQuery = '';
            //fputs($h, "poszlo\n");
            $newArray = pg_fetch_all($result);

            restore_error_handler();
            $ilosc_wierszy = $newArray ? sizeof($newArray) : 0;
            //fclose($h);
            if($newArray)
            {
                return $newArray;
            }            
            else
            {
                return null;
            }
        }
        public function __wakeup ()
        {
            $this->database = null;
            $this->dbConnect();
        }
        public function escapeString ($string)
        {
            if ($string === null) {
                return null;
            }    
            return pg_escape_string($string);
        }
        public function escapeInt($int) 
        {
            return (int)$int;
        }
        
        public function escapeInt64($int) {
            
            if (preg_match('/^[0-9]+$/', $int))
                return $int;
                
            return 0;
        }
        
        public function escapeBool ($bool)
        {
            if ($bool)
                return 'true';
                
            return 'false';
        }
        
        public function errorHandler ($errno, $errstr, $errfile, $errline, $errcontext) {
            
            if (false === pg_connection_status($this->database))
                throw new DBException('Database error running query - db down: '.$errstr);
            else {
                $errstr .= "\n\n pg_last_error:\n " . pg_last_error();
                LogManager::log($errno, 'DB error: '.$errstr.' in '.$errfile.' on line '.$errline.', sql: '.$this->currentQuery); //.', trace: '.var_export(debug_backtrace(), true)
                throw new DBQueryErrorException('SQL query failure: '.$errstr.' in '.$errfile.' on line '.$errline.', sql: '.$this->currentQuery);
            }
        }
        
        private function trySaveAuditLog($query) {
            
            if (stripos($query, 'insert') !== false || stripos($query, 'update') !== false || stripos($query, 'delete') !== false)
            {
                $_query = $this->escapeString($query);
                
                if (!isset($this->userData[Model::COLUMN_UPR_ID]))
                {
                    $sessionData = SessionManager::get(self::SESSION_FIELD_USER);
                
                    if ($sessionData)
                        $this->userData = $sessionData;
                }
                
                if (isset($this->userData[Model::COLUMN_UPR_ID]))
                {
                    $logEntry = 'insert into audyt_log (id_uprawnienia, zapytanie) values 
                    	('.$this->userData[Model::COLUMN_UPR_ID].', \''.$_query.'\')';
                    $result = pg_query($this->dbConnect(), $logEntry);
                }
            }
        }
    }
    class dalObjData extends dal
    {
        const FIELD_ID      = 'id';
        const FIELD_NAZWA   = 'nazwa';
        
        private $queryObj;
        private $dataSet;
        private $mainQueryRow;
        
        public function SetQueryObj($queryObj)
        {
            $this->queryObj = new queries();
            $this->queryObj = $queryObj;
            $this->dataSet = new DataSet();
        }
        public function GetDataSet()
        {
            return $this->dataSet;
        }
        //code interpreting queries and creating an array with nested rows of data
        //functions must return dataset datattype
        public function PrepareDataSet($strToSearch)
        {
            //pgquery is a query function with the query coming in the parameter
            $result = $this->pgQuery($this->queryObj->GetMainQuery());
            
            // iterate over a results of a query; the results will be one by one applied to another query, that will produce stats
            while ($this->mainQueryRow = pg_fetch_array($result))
            {
                $iter = 0; //iteration counter
                $iMainCols = 0; //resulting columns count indexer
                $iSubquery = 0; //index of subqueries array    <- jechac od jedynki
                $resultingRow = array();        
                $boolSubItem = true;
                $ponPetla = 0;
                $tempCounter = sizeof($this->mainQueryRow) / 2; // logic later relies on indexing by numbers, so leaving stupid fetch up there
                /*while (isset($this->mainQueryRow[$tempCounter]))
                {
                    $tempCounter++;
                } */
                
                // a table designed for merging data that are in the form of table put to one row
                // ex structure: (where the values x are merged results from queries)
                /*
                    array (
                        n1 =>  array (nazwa => n1, value1, value2, value3 ....)
                        n2 => array (nazwa => n2, value1, value2, value3 ....)
                        ...
                    )
                */
                $resSubTable = array();
                // a main results array needs to have at most one item that might be a nested array (otherwise dunno how to display)
                // variable responsibility is to preserve the row column place where we have a nested array
                $mainColsGrouped = null;
                        
                while ($iter <= $tempCounter)   //combine main data with subqueries data
                {   //add to resulting row the query result data
                    if ($boolSubItem)
                    if ($tableSubQuery = $this->queryObj->GetSubQuery($iSubquery))
                    {
                        $colIndex = $tableSubQuery["columnPlace"];
                        $iSubquery++;
                    }
                    if ($colIndex == $ponPetla) //if a subquery occurs, a table with multiple data has to be created
                    {
                        $boolSubItem = true;
                        $subQuery = $tableSubQuery["query"];
                        $subQuery = str_replace($strToSearch, $this->mainQueryRow[$tableSubQuery["mainQueryCol"]], $subQuery);
                        $resSubQuery = $this->pgQuery($subQuery);
                        
                        $i = 0;
                        while ($rowSubQuery = pg_fetch_assoc($resSubQuery))
                        {
                            if (sizeof($rowSubQuery) > 1)
                            {
                                // subquery produces more data than just one string so a whole row is put into a table item
                                // multiple items result set occured (j.w.), preserve the location for it; 
                                if ($mainColsGrouped === null)
                                    $mainColsGrouped = $iMainCols;
                                
                                // refer to $resSubTable structure, getting a name of a key, if there is already a value we need to add a coming number
                                $subqueryIndex = $rowSubQuery[self::FIELD_NAZWA];
                                if (isset($resSubTable[$subqueryIndex]))
                                {
                                    // there is already a value, expanding it with new stat value
                                    $subTableItem = $resSubTable[$subqueryIndex];
                                }
                                else
                                {
                                    // create an entry
                                    $subTableItem = array(self::FIELD_NAZWA => $subqueryIndex);
                                }
                                // add a current stat value to an entry (regardless of if just created or not)
                                $subTableItem[$tableSubQuery['columnHeader']] = $rowSubQuery[self::FIELD_ID];
                                
                                // set a modified item to a subtable row
                                $resSubTable[$subqueryIndex] = $subTableItem;
                                
                                $resultingRow[$mainColsGrouped] = $resSubTable;
                            }
                            else
                            {
                                $resultingRow[$iMainCols] = current($rowSubQuery); 
                            }
                            $i++;
                        }
                        
                        $colIndex = -1;
                    }
                    else
                    {
                        $boolSubItem = false;
                        if (isset($this->mainQueryRow[$iter]))
                        {
                            $resultingRow[$iMainCols] = $this->mainQueryRow[$iter];
                        }
                        $iter++;
                    }
                    $iMainCols++;
                    $ponPetla++;
                }  
                $this->dataSet->AddRow($resultingRow);
            }
            return $this->dataSet;
        }
        
        public function PrepareNoMainQueryDataSet ()
        {
            $iSubquery = 0;
            $headers = $this->queryObj->GetHeaders();
            
            while ($tableSubQuery = $this->queryObj->GetSubQuery($iSubquery))
            {
                $query = $tableSubQuery['query'];
                
                $result = $this->PobierzDane($query, $iloscWierszy);
                
                if ($iloscWierszy > 0) {
                    
                    $headers[$tableSubQuery['columnHeader']] = $tableSubQuery['columnHeader'];
                
                    foreach ($result as $row)
                    {
                        $this->dataSet->AddColumn($row, $row['nazwa'], $tableSubQuery['mainQueryCol'], $tableSubQuery['columnHeader']);
                    }
                }
                
                $iSubquery++;
            }
            
            $this->queryObj->SetHeaders($headers);
            
            return $this->dataSet;
        }
    }
    class DataSet
    {
        private $headers = array();
        private $subHeaders = array();
        private $data = array();
        private $dataIndexer = 0;
        public function GetHeaders()
        {
            return $this->headers;
        }
        public function GetSubHeaders()
        {
            return $this->subHeaders;
        } 
        public function GetData()
        {
            return $this->data;
        }
        public function SetHeaders($headers) //only one headers array is possible
        {
            $this->headers = $headers;
        }
        public function SetSubHeaders($headers) 
        {
            $this->subHeaders = $headers;
        }
        public function AddRow($row, $key = '') //row is an array of data which may be nested table in each index
        {
            if ($key)
            {
                $this->data[$key] = $row;
            }
            else
            {
                $this->data[$this->dataIndexer] = $row;
                $this->dataIndexer++;
            }
        }
        
        /**
        * @desc Add a column item to a data set row. In case a row pointed by key not found, create one from incoming row
        */
        public function AddColumn($row, $key, $rowItem, $columnName)
        {
            if (isset($this->data[$key]) && is_array($this->data[$key]))
            {
                // add a next column
                $this->data[$key][$columnName] = $row[$rowItem];
            }
            else
            {
                // there is yet no row for a column added, so create a row
                $row[$columnName] = $row[$rowItem];
                unset($row[$rowItem]);
                $this->AddRow($row, $key);
            }
            
            return false;
        }
        public function SetData($data)
        {
            $this->data = $data;
        }
    }
    class queries    //in case of statistics according to each subpoint a different query is main query and different subqueries are a set of queries
    {
        protected $mainVisibleColumns = array(); //may be usefull, consider using
        protected $mainQuery;
        protected $listQueries = array();
        protected $pointer = 0;
        protected $headers = array();
        protected $subHeaders = array();
        public function AddMainQuery($query)      //function will set a main query to a given, only one main query is allowed
        {
            $this->mainQuery = $query;
        }
        public function AddSubQuery($query, $refMainQueryEl, $placeInAr, $visibleColumns, $colHeader = null) //query is a db select, ref... is a data element of a main query returned by the query
        { //place is a number in a array where a table of the subquery elements should be placed, visible columns - table in the 4 table element
            //add to specific table index a new table with 4 values where at least one may be another table
            $this->listQueries[$this->pointer] = array("query" => $query, "mainQueryCol" => $refMainQueryEl, "columnPlace" => $placeInAr, "visCols" => $visibleColumns, 'columnHeader' => $colHeader);
            $this->pointer++;
        }
        public function GetMainQuery()
        {
            return $this->mainQuery;
        }
        public function GetSubQuery($queryNum)
        {
            if (isset($this->listQueries[$queryNum]))
            {
                return $this->listQueries[$queryNum];
            }
            else
            {
                return false;
            }
        }
        public function GetHeaders()
        {
            return $this->headers;
        }
        public function SetHeaders($headers) //only one headers array is possible
        {
            $this->headers = $headers;
        }
        public function GetSubHeaders()
        {
            return $this->subHeaders;
        }
        public function SetSubHeaders($headers) 
        {
            $this->subHeaders = $headers;
        }
        
        public static function CreateFromMainQuery($mainQuery, $dates, $mainQueryHeaders)
        {
            $queryObj = new self();

            foreach ($dates as $date)
            {
                $res = str_replace("_DATEFROM", $date[0], $mainQuery);
                $res = str_replace("_DATETO", $date[1], $res);

                //$mainQueryHeaders[] = $date[0];
                
                $queryObj->AddSubQuery($res, 'id', 0, "", $date[0]);
            }
            $queryObj->SetHeaders($mainQueryHeaders);
            return $queryObj;
        }
    }
    
    class statystyka       //interpret a form of statistics and fill a queries object
    {
        private $tab_each_pair = array();
        private $datesSet = array();
        private $counter = 0;
        public function AcceptPeriod($dateFrom, $dateTo, $interval)
        {
            $this->datesSet = array();
            $this->counter = 0;
            DateConverter::ConvertDate(array($dateFrom, $dateTo), $interval);
            DateConverter::Reset(); 
            while (DateConverter::MoveNext())
            {
                $this->tab_each_pair[0] = DateConverter::GetDateFrom();
                $this->tab_each_pair[1] = DateConverter::GetDateUntil();
                $this->datesSet[$this->counter] = $this->tab_each_pair;
                $this->counter++;
            }
            DateConverter::Reset();
            return $this->datesSet;
        }
        public function AcceptPeriodSpec($dateFrom, $dateTo, $interval)
        {
            $this->datesSet = array();
            $this->counter = 0;
            DateConverter::ConvertDate(array($dateFrom, $dateTo), $interval);
            DateConverter::AddOneDayToDateUntil();
            DateConverter::Reset(); 
            while (DateConverter::MoveNext())
            {
                $this->tab_each_pair[0] = DateConverter::GetDateFrom();
                $this->tab_each_pair[1] = DateConverter::GetDateUntil();
                $this->datesSet[$this->counter] = $this->tab_each_pair;
                $this->counter++;
            }
            DateConverter::Reset();
            return $this->datesSet;
        }
    }
    
    /**
    * @desc 
    * @param used
    * @param string a query producing a set of (most often id) data to be used in subqueries as a criteria
    * @param array a list of dates intervals to operate on with subqueries
    * @param string a query that produces stats data
    * @param array list of headers for data
    */
    function ConstrQueryObj($groupBy, $mainQuery, $dates, $subQueries, $mainQueryHeaders, $subHeaders = array())
    {
        //$headers = array();
        $queryObj = new queries();
        /*$licz = 0;
        for ($licz = 0; $licz < count($mainQueryHeaders); $licz++)
        {
            $headers[$licz] = $mainQueryHeaders[$licz];
        } */
        $i = 0;
        if ($mainQuery)
            $queryObj->AddMainQuery($mainQuery);
            
        $j = 0;
        while(isset($dates[$j]))
        {
            $temp = $dates[$j];
            $res = str_replace("_DATEFROM", $temp[0], $subQueries);
            $res = str_replace("_DATETO", $temp[1], $res);
            if (true === $groupBy)
            {
                $subHeaders[$temp[0]] = $temp[0];
            }
            else
            {
                $mainQueryHeaders[] = $temp[0];
            }
            //$licz++;
            $s = $j + 2;
            $queryObj->AddSubQuery($res, "id", $s, "", $temp[0]);
            $j++;
        }
        $queryObj->SetHeaders($mainQueryHeaders);
        $queryObj->SetSubHeaders($subHeaders);
        return $queryObj;
    }
?>