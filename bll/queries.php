<?php
    require_once 'utilsbll.php';
    require_once 'dal.php';
    require_once 'BLLDaneOsobowe.php';
    setlocale(LC_ALL, "pl_PL");
    set_time_limit(30); //script cannot run for longer than 30 secs
    
    function own_array_multisort ($column, $direction, $table)
    {
        $start = microtime(true);
        uasort($column, 'strcoll');
        
        if ($direction === SORT_DESC)
        {
            $column = array_reverse($column, true);
        }
        $result = array();
        foreach ($column as $key => $value)
        {                    
            $result[$key] = $table[$key];
        }
        $end = microtime(true);
        $diff = $end - $start;
        echo '<br />Czas sortowania: ' . $diff .'<br />';
        return $result;
    }
//to remove
if(!is_callable('array_intersect_key')) {
    function array_intersect_key ($baseArray, $anotherArray)
    {
        $newArray = array();
        foreach ($anotherArray as $key => $value)
        {
            if (isset($baseArray[$key]))
                $newArray[$key] = $baseArray[$key];
        }
        return $newArray;
    }
}
    
    class QueriesBase 
    {
        const COLUMN_OSOBA_ID              = 'osoba_id';
        
        const COLUMN_ZNA_JEZYK             = 'zna_jezyk';
        const COLUMN_Z_OS_TOW              = 'z_os_tow';
        const COLUMN_POSIADA_PR_J          = 'posiada_pr_j';
        const COLUMN_JEZYK                 = 'jezyk';
        const COLUMN_POZIOM                = 'poziom';
        const COLUMN_ZATWIERDZONY_JEZYK    = 'zatwierdzony_jezyk';
        const COLUMN_PRAWO_JAZDY           = 'prawo_jazdy';
        const COLUMN_DODATKOWA_OSOBA       = 'dodatkowa_osoba';
        const COLUMN_PLEC_DODATKOWA_OSOBA  = 'plec_dodatkowa_osoba';
        
        const TABLE_DANE_DODATKOWE         = 'dane_dodatkowe';
        const TABLE_ZNANE_JEZYKI           = 'znane_jezyki';
        const TABLE_POS_PRAWO_JAZDY        = 'pos_prawo_jazdy';
        const TABLE_DODATKOWE_OSOBY        = 'dodatkowe_osoby';
        
        //Redundant with dal model
        const VALIDATION_BOOL              = 1;
        const VALIDATION_INT               = 2;
        const VALIDATION_STRING            = 3;
        const VALIDATION_DATE              = 4;
        const VALIDATION_DATERANGE         = 5;
        
        const CONF_ADD_COLUMNS     = 'add_cols';
        const CONF_ADD_QUERY_DATA  = 'add_q_data';
        const CONF_DATA_TABLE      = 'data_table';
        const CONF_DATA_TABLE_FKEY = 'data_table_fkey';
        
        //klucze cache danych dodatkowych w systemie i dla ankiety
        const KEY_CONF_D_D_ANKIETA = 'dod_kolumny_ankieta';
        const KEY_CONF_D_D = 'dod_kolumny_wyszukiwanie';
        
        const ID_TABELA_DANE_DODATKOWE = 20;
        
        const SEARCH_COLUMNS_CACHE_KEY = 'kolumny_wyszukiwanie';
        const TABLE_RANKS_CACHE_KEY    = 'tabele_rangi';
        
        const ROZMOWA                    = 'rozmowa';
        const WARTOSC                    = 'wartosc';
        
        private $searchAddColumnsCacheKey = QueriesBase::KEY_CONF_D_D; //nazwa zjebana, to nie sa kolumny do wyszukiwania tylko dodatkowych danych po prostu
        private $queryAddData             = 'select id, nazwa, nazwa_wyswietlana, id_typ, edycja from dane_dodatkowe_lista order by nazwa;';
        private $dataTable                = 'dane_dodatkowe';
        private $dataTableFKey            = 'id_dane_dodatkowe_lista';
        private $isRegForm                = false;
        
        protected $dal;
        protected $searchAddColumns; //additional columns
        protected $searchAddColumnsData = null; //dane dodatkowe konkretnej edytowanej osoby 
        
        //ogranac sie ze to ankieta
        public function __construct($configuration = null, $isRegForm = false)
        {
            $this->dal = dal::getInstance();
            $this->bllDaneOsobowe = new BLLDaneOsobowe();
            $this->isRegForm = $isRegForm;
            
            if (is_array($configuration)) 
            {
                $this->searchAddColumnsCacheKey = $configuration[self::CONF_ADD_COLUMNS];
                $this->queryAddData = $configuration[self::CONF_ADD_QUERY_DATA];
                $this->dataTable = $configuration[self::CONF_DATA_TABLE];
                $this->dataTableFKey = $configuration[self::CONF_DATA_TABLE_FKEY];
            }
            
            if (!($this->searchAddColumns = PermanentCache::get($this->searchAddColumnsCacheKey)))
            {
                $this->getQueryAddData();
            }
        }
        
        private function getQueryAddData() 
        {
            $this->searchAddColumns = $this->dal->PobierzDane($this->queryAddData);
            $this->searchAddColumns = $this->remapId($this->searchAddColumns, 'nazwa');
            PermanentCache::set($this->searchAddColumnsCacheKey, $this->searchAddColumns);
        }
        
        public function addNewColumn ($name, $displayName, $typeId, $search = false, $ankieta = false)
        {
            $query = 'insert into dane_dodatkowe_lista (nazwa, nazwa_wyswietlana, id_typ) values (\''.$name.'\', \''.$displayName.'\', '.$typeId.');';
            $this->dal->pgQuery($query);
            PermanentCache::delete(self::KEY_CONF_D_D);
            $this->getQueryAddData();
            if ($search) 
            {
                $this->addToSearchColumns($name, $displayName, $typeId);
            }
            if($ankieta)
            {
                $this->addToAnkieta($this->searchAddColumns[$name]['id']);
            }
        }
        
        public function updateColumn ($id, $name, $displayName, $typeId)
        {                                             
            //aktualizacja tej kolumny spowoduje potencjalny rozjazd integralnosci, brak kolumny referencji w kolumny wyszukiwanie to powoduje
            //obecnosc tej referencji tam jest dyskusyjna, pole nazwa musi miec klauzule unikatowego
            //nazwa = \''.$name.'\',
            $query = 'update dane_dodatkowe_lista set nazwa_wyswietlana = \''.$displayName.'\', id_typ = '.$typeId.' where id = '.$id.';';
            $this->dal->pgQuery($query);
            PermanentCache::delete(self::KEY_CONF_D_D);
            $this->getQueryAddData();
        }
        
        public function deleteColumn ($id)
        {
            $query = 'delete from dane_dodatkowe_lista where id = '.$id.';';
            $this->dal->pgQuery($query);
            PermanentCache::delete(self::KEY_CONF_D_D);
            $this->getQueryAddData();
        }
        
        public function addToSearchColumns ($name, $displayName, $typeId)
        {
            $query = 'insert into kolumny_wyszukiwanie (nazwa, id_typ, id_tabela_wyszukiwanie, kolejnosc, naglowek) values (\''.$name.'\', '.$typeId.', '.self::ID_TABELA_DANE_DODATKOWE.', (select max(kolejnosc) + 1 from kolumny_wyszukiwanie), \''.$displayName.'\');';
            $this->dal->pgQuery($query);
            PermanentCache::delete(self::SEARCH_COLUMNS_CACHE_KEY);
        }
        
        public function updateSearchColumn ($id, $name, $displayName, $typeId)
        {
            $query = 'update kolumny_wyszukiwanie set id_typ = '.$typeId.', naglowek = \''.$displayName.'\' where id = '.$id.';';
            $this->dal->pgQuery($query);
            PermanentCache::delete(self::SEARCH_COLUMNS_CACHE_KEY);
        }
        
        public function removeFromSearchColumns ($recId)
        {
            $query = 'delete from kolumny_wyszukiwanie where id = '.$recId.';';
            $this->dal->pgQuery($query);
            PermanentCache::delete(self::SEARCH_COLUMNS_CACHE_KEY);
        }
        
        public function addToAnkieta ($columnId)
        {
            $query = 'insert into dane_dodatkowe_internet_lista (id_dane_dodatkowe_lista) values ('.$columnId.');';
            $this->dal->pgQuery($query);
            PermanentCache::delete(self::KEY_CONF_D_D_ANKIETA);
        }
        
        public function removeFromAnkieta ($columnId)
        {
            $query = 'delete from dane_dodatkowe_internet_lista where id_dane_dodatkowe_lista = '.$columnId.';';
            $this->dal->pgQuery($query);
            PermanentCache::delete(self::KEY_CONF_D_D_ANKIETA);
        }
        
        public function getAdditionalColumns ()
        {
            return $this->searchAddColumns;
        }
        
        public function getAdditionalColumnsData ($idOsoba) 
        {
            $dane = $this->dal->PobierzDane('select dane_dodatkowe.id, dane_dodatkowe.id_osoba, dane_dodatkowe.wartosc, dane_dodatkowe_lista.nazwa, dane_dodatkowe_lista.edycja from dane_dodatkowe join dane_dodatkowe_lista on dane_dodatkowe.id_dane_dodatkowe_lista = dane_dodatkowe_lista.id where dane_dodatkowe.id_osoba = '.$idOsoba.' order by nazwa;', $ilosc_wierszy);
            if ($ilosc_wierszy > 0)
                $this->searchAddColumnsData = $this->remapId($dane, 'nazwa');
            else
                $this->searchAddColumnsData = array();
                    
            return $this->searchAddColumnsData;
        }
        
        public function setAdditionalColumnsData($idOsoba, $data)
        {
            if (!$this->searchAddColumnsData && false === $this->isRegForm)
            {
                $this->getAdditionalColumnsData($idOsoba);
            }
                        
            $data = array_intersect_key ($data, $this->searchAddColumns);

            $result = sizeof($data) == 0;
            
            //TODO either break on first error on result or remember the result for each run query
            //NOT TODO this will all be replaced and therefore thrown away
            foreach ($data as $key => $value)
            {
                //czy wogle wolno mi tu cos ruszac
                if ($this->searchAddColumns[$key]['edycja'] == 't')
                {
                    // czy osoba ma taka wartosc juz wczesniej podana
                    if (isset($this->searchAddColumnsData[$key]))
                    {
                        //update
                        if (strlen($value) > 0 && $value !== '--------')
                            $result = $this->dal->pgQuery('update '.$this->dataTable.' set wartosc = \''.$value.'\' where id = '.$this->searchAddColumnsData[$key]['id']);
                        else
                            $result = $this->dal->pgQuery('delete from '.$this->dataTable.' where id = '.$this->searchAddColumnsData[$key]['id']);
                    }
                    else
                    {
                        //insert
                        if (strlen($value) > 0 && $value !== '--------')
                            $result = $this->dal->pgQuery('insert into '.$this->dataTable.' (id_osoba, '.$this->dataTableFKey.', wartosc) values ('.$idOsoba.', '.$this->searchAddColumns[$key]['id'].', \''.$value.'\');');
                    }
                }
            }
            
            if ($data[self::ROZMOWA] == 'tak' && (!isset($this->searchAddColumnsData[self::ROZMOWA]) || $this->searchAddColumnsData[self::ROZMOWA][self::WARTOSC] != $data[self::ROZMOWA]))
            {
                $this->bllDaneOsobowe->sendWelcomeEmail($idOsoba);
            }
            
            return $result;
            
        }
        //dodac do remapId obsluge powielenia klucza tablicy, jesli wydajnosc pozwoli
        protected function remapId ($table, $column = self::COLUMN_OSOBA_ID)
        {
            $result = array();
            if(is_array($table))
            foreach ($table as $row)
            {
                $result[$row[$column]] = $row;
            }
            return $result;
        }
    }
    
    class QueriesEngine extends QueriesBase
    {
        const RANK_MIN                 = 1;
        const RANK_MAX                 = 10;
        
        const INDEX_EVENT              = 'event';
        const INDEX_FUNCTION           = 'function';
        const INDEX_LENGTH             = 'length';
        
        const COLUMN_HEADER            = 'naglowek';
        const COLUMN_TABLE             = 'tabela';
        const COLUMN_NAME              = 'kolumna';
        const COLUMN_VALIDATION        = 'id_typ';
        const COLUMN_RANK              = 'ranga';
        
        const DESCRIPTION_TABLE        = 0;
        const DESCRIPTION_COLUMN       = 1;
        const DESCRIPTION_ACTION       = 2;
        
        const RESULT_MAX_ROWS          = 3500;
        const RESULT_INTERVAL          = 5;
        
        const ACTION_SHOW              = 'show';
        const ACTION_NOT               = 'not';
        const ACTION_MISSING           = 'missing';
        const ACTION_FILTER            = 'filter';
        
        const QUERY                    = 'query';
        const QUERIES                  = 'queries';
        const QUERIES_FILTER           = 'queries_filter';
        const QUERIES_FILTER_SHOW      = 'queries_filter_show';
        const QUERIES_SHOW             = 'queries_show';
        
        const QUERY_SYSTEM_DEFAULT     = 'system_test';
        
        const INDEX_LOCAL              = 'local_index';
        const INDEX_REMOTE             = 'remote_index';
        const TABLE_NAME               = 'table_name';
        
        protected $searchColumns;
        protected $tableRanks;
        protected $queryData;
        protected $queryHeaders;
        protected $viewHeaders;
                
        protected $userId = 1;
        protected $filterName = 'system_test';
        protected $filtrationId = null;
        
        protected $resultIdSet = null;
        protected $resultSet = null;
        protected $sortDirection = array(0 => SORT_DESC, 1 => SORT_ASC); 
        
        protected $validations = array (
            //boole ida w combach, nie ma co walidowac
            1 => array (
                QueriesEngine::INDEX_EVENT    => '',
                QueriesEngine::INDEX_FUNCTION => '',
            ),
            2 => array (
                QueriesEngine::INDEX_EVENT      => 'onkeypress',
                QueriesEngine::INDEX_FUNCTION   => 'queriesValidations.queriesValidateInt',
            ),
            3 => array (
                QueriesEngine::INDEX_EVENT    => 'onkeypress',
                QueriesEngine::INDEX_FUNCTION => 'queriesValidations.textValidate',
            ),
            5 => array (
                QueriesEngine::INDEX_EVENT    => 'onkeypress',
                QueriesEngine::INDEX_FUNCTION => 'queriesValidations.queriesValidateDate',
                QueriesEngine::INDEX_LENGTH   => '21',
            ),
        );
        
        protected $serverDataPatterns = array (
            //bool
            1 => '/^(tak|nie)$/',
            //int
            2 => '/^[0-9,\ %]+$/',
            //string
            3 => '/^(.*)$/', //[0-9a-zA-Z\,\-\ %]+
            //date
            4 => '/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/',
            //daterange
            5 => '/^[0-9]{4}-[0-9]{2}-[0-9]{2}\ [0-9]{4}-[0-9]{2}-[0-9]{2}$/',
        );
        
        //map of the dependancies of the columns - more general inforamtion column => array of columns with more specific information
        protected $columnDependancies = array (
            QueriesEngine::COLUMN_ZNA_JEZYK => QueriesEngine::TABLE_ZNANE_JEZYKI, 
            QueriesEngine::COLUMN_POSIADA_PR_J => QueriesEngine::TABLE_POS_PRAWO_JAZDY,
            QueriesEngine::COLUMN_Z_OS_TOW => QueriesEngine::TABLE_DODATKOWE_OSOBY,
        );
        
        protected $specificTablesColumns = array(
            QueriesEngine::TABLE_ZNANE_JEZYKI => array(
                QueriesEngine::COLUMN_JEZYK => '',
                QueriesEngine::COLUMN_POZIOM => '',
                QueriesEngine::COLUMN_ZATWIERDZONY_JEZYK => '',
            ),
            QueriesEngine::TABLE_POS_PRAWO_JAZDY => array(
                QueriesEngine::COLUMN_PRAWO_JAZDY => '',
            ),
            QueriesEngine::TABLE_DODATKOWE_OSOBY => array(
                QueriesEngine::COLUMN_DODATKOWA_OSOBA => '',
                QueriesEngine::COLUMN_PLEC_DODATKOWA_OSOBA => '',
            ),
        );
        
        private $addAsNew = false;
        
        public function __construct ($userId = 1, $filterName = self::QUERY_SYSTEM_DEFAULT, $filtrationId = null)
        {            
            parent::__construct();
            
            if ($filtrationId)
            {
                $this->filtrationId = $filtrationId;
                $query = 'select nazwa, id_uzytkownik, naglowki, p_select from kwerendy where id = '.$this->filtrationId;
                $result = $this->dal->PobierzDane($query, $ilosc_wierszy);
                if ($ilosc_wierszy == 1) 
                {
                    $this->filterName = $result[0]['nazwa'];
                    $this->userId = $result[0]['id_uzytkownik'];
                    $this->queryData = unserialize(stripslashes($result[0]['p_select']));
                    $this->queryHeaders = unserialize(stripslashes($result[0]['naglowki']));
                }
            }
            //to oznacza, ze uzytkownik jest na etapie zapisu nowego lub aktualizacji/powielenia zapytania, w odroznieniu od sytuacji, gdy uzytkownik edytuje dane o zapytaniu
            //co realizuje blok powyzej
            if ($filterName !== '') 
            {
                $this->filterName = $filterName;
                $this->userId = $userId;
            }
            
            if (!($this->searchColumns = PermanentCache::get(self::SEARCH_COLUMNS_CACHE_KEY)))
            {
                $this->searchColumns = $this->dal->PobierzDane('select kolumny_wyszukiwanie.id, kolumny_wyszukiwanie.nazwa as kolumna, id_typ, naglowek, tabela_wyszukiwanie.nazwa as tabela, 
                tabela_wyszukiwanie.ranga, kolumny_wyszukiwanie.kolejnosc 
                from kolumny_wyszukiwanie join tabela_wyszukiwanie on kolumny_wyszukiwanie.id_tabela_wyszukiwanie = tabela_wyszukiwanie.id order by kolejnosc;');
                $this->searchColumns = $this->remapId($this->searchColumns, 'kolumna');
                PermanentCache::set(self::SEARCH_COLUMNS_CACHE_KEY, $this->searchColumns);
            }
            
            if (!($this->tableRanks = PermanentCache::get(self::TABLE_RANKS_CACHE_KEY)))
            {
                $this->tableRanks = $this->dal->PobierzDane('select tabela_wyszukiwanie.nazwa as tabela, 
                tabela_wyszukiwanie.ranga from tabela_wyszukiwanie order by ranga;');
                $this->tableRanks = $this->remapId($this->tableRanks, 'tabela');
                PermanentCache::set(self::TABLE_RANKS_CACHE_KEY, $this->tableRanks);
            }
            
            if ($this->queryHeaders && $this->searchColumns)
            {
                $headers = array();
                foreach ($this->searchColumns as $header => $columnData)
                {
                    if (in_array($header, $this->queryHeaders))
                    {
                        $headers[$header] = $columnData['naglowek'];
                    }
                }
                
                $this->viewHeaders = $headers;
            }
        }
        
        public function setAddNew ()
        {
            $this->addAsNew = true;
        }
        
        public function getColumns ()
        {
            return $this->searchColumns;
        }
        
        public function getValidations ()
        {
            return $this->validations;
        }
        
        public function getHeaders()
        {
            return $this->viewHeaders;
        }
        
        public function getData()
        {
            return $this->queryData;
        }
        
        public function getUserId()
        {
            return $this->userId;
        }
        
        public function isOwner ()
        {
            if ($this->userId == $_SESSION[UZYTKOWNIK_ID])
                return true;
            else
                return false;
        }
        
        public function getFilterName()
        {
            return $this->filterName;
        }
        
        private function validateData ($data, $type) 
        {
            $regexp = $this->serverDataPatterns[$type];
            if (preg_match($regexp, $data))
            {
                return $data;
            }
            else 
            {
                //tu zapewne docelowo exception
                return null;
            }
        }
        /*
        * Wczytanie danych z formularza zapytan. Mielone sÄ… kolejne pola talicy post, brane pod uwage tylko te, ktore maja nazwe rozdzielona dwoma minusami "-" 
        * - tym sposobem jesli chce sie stworzyc pole w formularzu nie podlegajace temu mechanizmowi trzeba dac mu w nazwie jeden lub trzy minusy albo zadnego.
        * W rezultacie dzialania metody zapamietywane sa wszelkie informacje o tym jak zbudowac odpytanie - jakie informacje beda zbierane i wg jakis kryteriow. 
        * NAstepnie metoda odpala generate queries, czyli wytwarza na podstawie zebranych danych zapytania.
        */
        public function readFormData ($table)
        {
            $this->queryData = array();
            $this->queryHeaders = array();
            
            $hasParams = false;
            foreach ($table as $key => $item)
            {
                //ustalenie indexow struktury danych odpytywania
                $description = explode(DESC_SEPARATOR, $key);
                if (sizeof($description) == 3)
                {
                    $kolumna = $description[self::DESCRIPTION_COLUMN];
                    $tabela = $description[self::DESCRIPTION_TABLE];
                    $akcja = $description[self::DESCRIPTION_ACTION];

                    switch($akcja)
                    {
                        case self::ACTION_FILTER:
                            if (strlen($item) > 0)
                            {
                                $typ = $this->searchColumns[$kolumna][self::COLUMN_VALIDATION];
                                //walidacja regexpem lub podobna po stronie php, sprawdzenie spojnosci danych wchodzacych do zapytania
                                if (($item = $this->validateData($item, $typ)) !== null)
                                {
                                    $this->queryData[$tabela][$akcja][$kolumna] = $item; //[$ranga]
                                    $hasParams = true;
                                }
                                else
                                    $error[] = new QueryError('BĹ‚Ä…d w tresci w polu: '.$this->searchColumns[$kolumna][self::COLUMN_HEADER], $kolumna);
                            }
                            break;
                        case self::ACTION_SHOW:
                            $this->queryData[$tabela][$akcja][$kolumna] = $kolumna; //[$ranga]
                            $this->queryHeaders[$this->searchColumns[$kolumna]['kolejnosc']] = $kolumna;
                            break;
                        case self::ACTION_NOT:
                            $this->queryData[$tabela][$akcja][$kolumna] = true; //[$ranga]
                            break;
                        case self::ACTION_MISSING:
                            $this->queryData[$tabela][$akcja][$kolumna] = true; //[$ranga]
                            break;
                    }
                }
            }
            
            if (!empty($error))
                return $error;
                
            if (!$hasParams)
                return false;
                
            //obciac dane pokrewne
            $this->generalCriteriaReduction();
            
            //wywolac metode tworzaca zapytania i zbierajaca dane, z zapytaniami serializowac dane do cache i do bazy
            return $this->generateQueries();
        }
        
        protected function generalCriteriaReduction ()
        {
            $actions = array(
                QueriesEngine::ACTION_FILTER,
                QueriesEngine::ACTION_MISSING,
                QueriesEngine::ACTION_NOT,
                QueriesEngine::ACTION_SHOW,
            );
            
            if (isset($this->queryData[QueriesEngine::TABLE_DANE_DODATKOWE]))
            {
                $dataDaneDodatkowe = & $this->queryData[QueriesEngine::TABLE_DANE_DODATKOWE];
                foreach ($this->columnDependancies as $additionalDataColumn => $specificTable)
                {
                    if (isset($this->queryData[$specificTable]))
                    {
                        foreach ($actions as $action)
                        {
                            if (isset($dataDaneDodatkowe[$action][$additionalDataColumn], $this->queryData[$specificTable][$action]))
                            {
                                unset($dataDaneDodatkowe[$action][$additionalDataColumn]);
                                //cut headers off for column
                                if ($action == QueriesEngine::ACTION_SHOW)
                                    unset($this->queryHeaders[$this->searchColumns[$additionalDataColumn]['kolejnosc']]);
                            }
                        }
                    }
                }
            }
        }
        
        protected function addFiltration ()
        {
            if ($this->filtrationId === null || $this->addAsNew === true)
            {
                $query = 'INSERT INTO kwerendy (id_uzytkownik, nazwa, valid, naglowki, p_select) VALUES ('.$this->userId.', \''.$this->filterName.'\', 1, \''.
                addslashes(serialize($this->queryHeaders)).'\', \''.addslashes(serialize($this->queryData)).'\');';
                $this->dal->pgQuery($query);
                $newIdQuery = 'select currval(\'kwerendy_id_seq\') as id;';
                $result = $this->dal->PobierzDane($newIdQuery);
                //zapis w sesji id kwerendy
                $_SESSION['kwerenda'] = $result[0]['id'];
            } 
            else
            {
                $query = 'update kwerendy set id_uzytkownik = '.$this->userId.', nazwa = \''.$this->filterName.'\', valid = 1, naglowki = \''.
                addslashes(serialize($this->queryHeaders)).'\', p_select = \''.addslashes(serialize($this->queryData)).'\' where id = '.$this->filtrationId.';';
                $this->dal->pgQuery($query);
            }
        }
        
        protected function generateQueries ()
        {
            //foreach ($this->queryData as $ranga => $tabele)
            //{
            foreach ($this->queryData as $nazwaTabela => $akcje)
            {
                $ranga = $this->tableRanks[$nazwaTabela][self::COLUMN_RANK];
                $action = 'queries'.ucfirst($nazwaTabela);
                $this->$action($ranga, $akcje);
            }
            //}
            //var_dump($this->queryData);
            //zapis calej kwerendy w bazie danych
            $this->addFiltration();
            //return $this->runQueries();
        }
        //wstawic cache dla poszczegolnej osoby, ezby nie ciagac z bazy ?
        public function runQueries ($page, $rowsPerPage, $sortDir = '', $sortCol = '')
        {
            //QUERIES_FILTER
            $queryFilter = '';
            $queryFilterNum = 0;
            
            for ($i = self::RANK_MIN; $i <= self::RANK_MAX; $i++)
            {
                if (isset($this->queryData[self::QUERIES][$i][self::QUERIES_FILTER]))
                {
                    $queries = $this->queryData[self::QUERIES][$i][self::QUERIES_FILTER];
                    foreach ($queries as $queryAr)
                    {
                        $query = $queryAr[self::QUERY];
                        $osoba_id = $queryAr[self::COLUMN_OSOBA_ID];
                        
                        if ($queryFilterNum > 0)
                        {                                            //'.$osoba_id.'                                      //'.$osoba_id.'            .$queryAr[self::COLUMN_OSOBA_ID]
                            $queryFilter .= ' join ('.$query.' order by id asc) as a'.$queryFilterNum.' on a0.id = a'.$queryFilterNum.'.id';
                        }
                        else
                        {                                     //'.$osoba_id.'
                            $queryFilter = '('.$query.' order by id asc) as a'.$queryFilterNum;
                        }

                        $queryFilterNum++;
                        /*if (is_array($this->resultIdSet))
                            $query .= ' and '.$osoba_id.' in ('.implode(',', $this->resultIdSet).')'; //column reference ambiguous problem
                        
                        $result = $this->dal->PobierzDane($query, $rowsCount);
                        if ($rowsCount > 0)
                            $this->resultIdSet = $this->matchKeyValueId($result);
                        else
                            return null;*/
                    }
                }

            }
            
            /*if ($queryFilter) 
            {
                $queryFilter = 'select * from ('.$queryFilter.')';
                $result = $this->dal->PobierzDane($queryFilter, $rowsCount);
                if ($rowsCount > 0)
                    $this->resultIdSet = $this->matchKeyValueId($result);
                else
                    return null;
            }
            
            //QUERIES_FILTER_SHOW
            $queryFilter = '';
            $queryFilterNum = 0;*/
            for ($i = self::RANK_MIN; $i <= self::RANK_MAX; $i++)
            {
                if (isset($this->queryData[self::QUERIES][$i][self::QUERIES_FILTER_SHOW]))
                {
                    $queries = $this->queryData[self::QUERIES][$i][self::QUERIES_FILTER_SHOW];
                    foreach ($queries as $queryAr)
                    {
                        $query = $queryAr[self::QUERY];
                        $osoba_id = $queryAr[self::COLUMN_OSOBA_ID];
                        
                        if ($queryFilterNum > 0)
                        {                                                                                           //'.$osoba_id.'             .$queryAr[self::COLUMN_OSOBA_ID]
                            $queryFilter .= ' join ('.$query.' order by id asc) as a'.$queryFilterNum.' on a0.id = a'.$queryFilterNum.'.id';
                        }
                        else
                        {
                            $queryFilter = '('.$query.' order by id asc) as a'.$queryFilterNum;
                        }
                        
                        $queryFilterNum++;
                        
                        /*if (is_array($this->resultIdSet))
                            $query .= ' and '.$osoba_id.' in ('.implode(',', $this->resultIdSet).')';
                        
                        $result = $this->dal->PobierzDane($query, $rowsCount);
                        if ($rowsCount == 0)
                            return null;
                        
                        $this->intersectIdSet($result);
                        if (sizeof($result) > 0)
                            $result = $this->remapId($result);

                        if($this->resultSet)
                            $this->resultSet = $this->mergeColumns($this->resultSet, $result);
                        else
                            $this->resultSet = $result; */
                            
                    }
                }

            }
            
            if ($queryFilter) 
            {
                if ($queryFilterNum > 1)
                    $queryFilter = 'select * from ('.$queryFilter.')';
                else
                    $queryFilter = 'select * from '.$queryFilter;
                //echo $queryFilter; exit;
                $result = $this->dal->PobierzDane($queryFilter, $rowsCount);
                if ($rowsCount == 0)
                    return null;
                        
                $this->intersectIdSet($result);
                if (sizeof($result) > 0)
                    $result = $this->remapId($result);

                if($this->resultSet)
                    $this->resultSet = $this->mergeColumns($this->resultSet, $result);
                else
                    $this->resultSet = $result;
            }
            
            if (($size = sizeof($this->resultIdSet)) > self::RESULT_MAX_ROWS)
            {
                throw new TooManyRowsException('Za duĹĽo rekordĂłw.', $size);
            }
            //QUERIES_SHOW
            for ($i = self::RANK_MIN; $i <= self::RANK_MAX; $i++)
            {
                if (isset($this->queryData[self::QUERIES][$i][self::QUERIES_SHOW]))
                {
                    $queries = $this->queryData[self::QUERIES][$i][self::QUERIES_SHOW];
                    foreach ($queries as $queryAr)
                    {
                        $query = $queryAr[self::QUERY];
                        $osoba_id = $queryAr[self::COLUMN_OSOBA_ID];
                        //it can happen for show query to have a where clause - for additional columns
                        $where = 'where';
                        if (stripos($query, $where) !== false)
                            $where = 'and';
                        if (is_array($this->resultIdSet))
                            $query .= ' '.$where.' '.$osoba_id.' in ('.implode(',', $this->resultIdSet).')';
                        
                        $result = $this->dal->PobierzDane($query); 
                        if (sizeof($result) > 0)
                            $result = $this->remapId($result);
                        //bĹ‚Ä…d !!bĹ‚edne zaleznie ze dla wszystkich rekordow dane istnieja w merge columns  ?
                        if($this->resultSet)
                            $this->resultSet = $this->mergeColumns($this->resultSet, $result);
                        else
                            $this->resultSet = $result;

                    }
                }

            }

            //sort result set 1 asc 0 desc
            if (strlen($sortDir) > 0 && strlen($sortCol) > 0)
            {
                foreach ($this->resultSet as $key => $data)
                {
                    /*
                    * problem sortowania multisortem i prawdopodobnie wszystkim innym rowniez dotyczy polskich znakĂłw. Jesli nie ma mozliwosci ustawienia contextu encodingowego do sortowania 
                    * dodac util, ktory zamienia (najlepiej jakis preg replace) znaki polskie na niepolskie w kolumnie sortowania. Zmiany te nie zaszkodzÄ… wynikowemu result setowi, 
                    * bo multisort sortuje kolumne i potem wg uzyskanej kolejnosci kluczy z bazowej tabeli uklada result set. Ponizej przykladowy dzialajacy str replace.
                    */
                     /*
                     * Jesli kombinacje z setlocale nie dzadzÄ… rezultatu
                     * Zaimplementowac po swojemu multisortowanie .... posortowac tablice kolumny indexowana indexami tablicy danych z zachowaniem kluczy tablicy 
                     * przy pomocy wybrancej funkcji do porownywania stringow strcoll, potem przelozyc tablice glowna wg szyku kluczy sortowanej tablicy.
                     */
                    //$colVals[$key] = str_replace('Ĺ�', 'L', $data[$sortCol]);
                    
                    //in case the data[sortcol] is null, omit the data
                    if (isset($data[$sortCol]))
                        $colVals[$key] = $data[$sortCol];
                    else
                        $colVals[$key] = null;
                }

                //array_multisort($colVals, $this->sortDirection[$sortDir], $this->resultSet);
                $this->resultSet = own_array_multisort($colVals, $this->sortDirection[$sortDir], $this->resultSet);
            }
            //cut array_slice
            $iloscRek = sizeof($this->resultSet);
            if ($page >= self::RESULT_INTERVAL)
            {
                $offset = ($page - self::RESULT_INTERVAL) * $rowsPerPage;
                $limit = ($page + self::RESULT_INTERVAL) * $rowsPerPage;
            }
            else
            {
                $offset = 0;
                $limit = 2 * self::RESULT_INTERVAL * $rowsPerPage;
            }
            if ($limit > $iloscRek)
                $limit = $iloscRek;
//add if for empty set
            $_SESSION['edycja_masowa'] = implode('|', $this->resultIdSet);
            return array(array_slice($this->resultSet, $offset, $limit, true), $iloscRek);
        }
        
        protected function intersectIdSet($table, $idColumn = self::COLUMN_OSOBA_ID)
        {
            $result = array();
            foreach ($table as $row)
            {
                $result[$row[$idColumn]] = $row[$idColumn];
            }
            $this->resultIdSet = $result;
            if ($this->resultSet)
                $this->resultSet = array_intersect_key($this->resultSet, $this->resultIdSet);
        }
        
        protected function addQuery($selectClause, $fromClause, $whereClause, $ranga, $filter, $show, $idOsobaClause)
        {
            $query = $selectClause.$fromClause;
            //jesli nie bylo action filter to do action show, jak bylo jedno i drugie to do filter show, inaczej do filter, jesli by nic nie bylo metoda by sie nie odpalila
            if ($filter && $show) 
            {
                $query .= $whereClause;  
                $this->queryData[self::QUERIES][$ranga][self::QUERIES_FILTER_SHOW][] = array(self::QUERY => $query, self::COLUMN_OSOBA_ID => $idOsobaClause);
            }
            else
            {
                if ($show)
                    $this->queryData[self::QUERIES][$ranga][self::QUERIES_SHOW][] = array(self::QUERY => $query, self::COLUMN_OSOBA_ID => $idOsobaClause);
                else
                {
                    $query .= $whereClause;
                    $this->queryData[self::QUERIES][$ranga][self::QUERIES_FILTER][] = array(self::QUERY => $query, self::COLUMN_OSOBA_ID => $idOsobaClause);
                }
            }
            //echo $query."\n<br />";
        }
        
        protected function createWhereClause ($columnName, $dane, $filterColumnName = null)
        {
            $columnType = $this->searchColumns[$columnName][self::COLUMN_VALIDATION];
            //$columnTable = $this->searchColumns[$columnName][self::COLUMN_TABLE];
            if($filterColumnName)
                $columnName = $filterColumnName;
                
            //$columnName = $columnTable.'.'.$columnName;
            
            $relationOp = 'like';
            $addLower = false;
            $clause = '(';
            switch ($columnType)
            {
                case self::VALIDATION_INT:
                    if (preg_match('/^([0-9]+)\s([0-9]+)$/', $dane))
                    {
                        $kryteria = explode(' ', $dane);
                        $clause .= $columnName.' between \''.$kryteria[0].'\' and \''.$kryteria[1].'\'';
                        $clause .= ')';
                        return $clause;
                    }
                    $relationOp = '=';
                    break;
                case self::VALIDATION_STRING:
                //dodac prywatna metode do ustalania operatora relacji np = jesli nie ma % lub _ - i to jest o dziwo nienajlepszy pomysl !!
                    $relationOp = 'like';
                    $addLower = true;
                    break;
                case self::VALIDATION_DATERANGE:
                    //sprawdzic i dac between and
                    $kryteria = explode(' ', $dane);
                    $clause .= $columnName.' between \''.$kryteria[0].'\' and \''.$kryteria[1].'\'';
                    $clause .= ')';
                    return $clause;
            }
            //operacje dla typu innego niz zakres dat
            $kryteria = explode(',', $dane);
            $i = 0;
            if ($addLower)
                    $columnName = 'lower('.$columnName.')';
            foreach ($kryteria as $kryterium)
            {
                $kryterium = trim($kryterium);
                if ($i > 0)
                    $clause .= ' or ';
                if ($addLower)
                    $kryterium = 'lower(\''.$kryterium.'\')';
                else
                    $kryterium = '\''.$kryterium.'\'';
                    
                $clause .= $columnName.' '.$relationOp.' '.$kryterium;
                $i++;
            }
            $clause .= ')';
            return $clause;
        }
        
        protected function matchKeyValueId ($table, $column = self::COLUMN_OSOBA_ID)
        {
            $result = array();
            foreach ($table as $row)
            {
                $result[$row[$column]] = $row[$column];
            }
            return $result;
        }
        
        protected function mergeColumns($resultSet, $result)
        {
            if(is_array($result))
            foreach ($result as $key => $row)
            {
                foreach ($result[$key] as $column => $value)
                    $resultSet[$key][$column] = $value;
            }
            return $resultSet;
        }
        
        private function queriesDane_osobowe ($ranga, $akcje)
        {
            //'imie' => array (self::INDEX_LOCAL => 'id_imie', self::INDEX_REMOTE => 'id', self::TABLE_NAME => 'imiona', self::COLUMN_NAME => 'nazwa'), 
            $tableDaneOs = 'dane_osobowe';
            $colTablesToJoin = array (
            //po dodaniu kolumny w ramach denormalizacji wykosic stad wpis jesli nazwa kolumny dodanej zgadza sie z ta w kolumny wyszukiwanie
                                        //'plec' => array (self::INDEX_LOCAL => 'id_plec', self::INDEX_REMOTE => 'id', self::TABLE_NAME => 'plec', self::COLUMN_NAME => 'nazwa'),
                                        //'miejscowosc' => array (self::INDEX_LOCAL => 'id_miejscowosc', self::INDEX_REMOTE => 'id', self::TABLE_NAME => 'miejscowosc', self::COLUMN_NAME => 'nazwa'),
                                        'wyksztalcenie' => array (self::INDEX_LOCAL => 'id_wyksztalcenie', self::INDEX_REMOTE => 'id', self::TABLE_NAME => 'wyksztalcenie', self::COLUMN_NAME => 'nazwa'),
                                        'zawod' => array (self::INDEX_LOCAL => 'id_zawod', self::INDEX_REMOTE => 'id', self::TABLE_NAME => 'zawod', self::COLUMN_NAME => 'nazwa'),
                                        'konsultant' => array (self::INDEX_LOCAL => 'id_konsultant', self::INDEX_REMOTE => 'id', self::TABLE_NAME => 'uprawnienia', self::COLUMN_NAME => 'nazwa_uzytkownika'),
                                        'charakter_pracy' => array (self::INDEX_LOCAL => 'id_charakter', self::INDEX_REMOTE => 'id', self::TABLE_NAME => 'charakter', self::COLUMN_NAME => 'nazwa'),
                                        'zrodlo_informacji' => array (self::INDEX_LOCAL => 'id_zrodlo', self::INDEX_REMOTE => 'id', self::TABLE_NAME => 'zrodlo', self::COLUMN_NAME => 'nazwa'),
                                     );
            $tablesJoined = array();
            $selectClause = 'select '.$tableDaneOs.'.id, '.$tableDaneOs.'.id as '.self::COLUMN_OSOBA_ID;
            $fromClause = ' from '.$tableDaneOs;
            $whereClause = ' where ';
            $filter = false;
            $i = 0;
            
            if (isset($akcje[self::ACTION_FILTER]))
            {
                $filter = true;
                
                foreach ($akcje[self::ACTION_FILTER] as $kolumna => $dane)
                {
                    $tabJoinDesc = '';
                    $kolumnaFiltr = $tableDaneOs.'.'.$kolumna;
                    // jesli kolumna wymaga joina dodaj do klauzuli from
                    if (isset($colTablesToJoin[$kolumna]))
                    {
                        $tabJoinDesc = $colTablesToJoin[$kolumna];
                        $fromClause .= ' join '.$tabJoinDesc[self::TABLE_NAME].' on '.$tableDaneOs.'.'.$tabJoinDesc[self::INDEX_LOCAL].'='.$tabJoinDesc[self::TABLE_NAME].'.'.$tabJoinDesc[self::INDEX_REMOTE];
                        $colTablesToJoin[$kolumna]['used'] = true;
                        $kolumnaFiltr = $tabJoinDesc[self::TABLE_NAME].'.'.$tabJoinDesc[self::COLUMN_NAME];
                    }
                    //filtracja - utworzyc klauzule where z ewentualnym wykorzystaniem info o dolaczanych tabelach
                    if ($i > 0)
                        $whereClause .= ' and ';
                    if (isset($akcje[self::ACTION_NOT][$kolumna]))
                        $whereClause .= ' not ';
                        
                    if (isset($akcje[self::ACTION_MISSING][$kolumna]))
                    {
                        $missingKolumnaFiltr = $kolumnaFiltr;
                        
                        if (isset($colTablesToJoin[$kolumna]))
                        {
                            $missingKolumnaFiltr = $tableDaneOs.'.'.$tabJoinDesc[self::INDEX_LOCAL];
                        }
                        
                        $whereClause .= '(('.$this->createWhereClause($kolumna, $dane, $kolumnaFiltr).') or ('.$missingKolumnaFiltr.' is null))';
                        unset($akcje[self::ACTION_MISSING][$kolumna]);
                    }
                    else
                    {
                        $whereClause .= $this->createWhereClause($kolumna, $dane, $kolumnaFiltr);
                    }
                    $i++;
                }
            }
            
            if (isset($akcje[self::ACTION_MISSING]))
            {
                $filter = true;
                
                foreach ($akcje[self::ACTION_MISSING] as $kolumna => $dane)
                {
                    $tabJoinDesc = '';
                    $kolumnaFiltr = $tableDaneOs.'.'.$kolumna;
                    
                    if (isset($colTablesToJoin[$kolumna]))
                    {
                        $tabJoinDesc = $colTablesToJoin[$kolumna];
                        $kolumnaFiltr = $tableDaneOs.'.'.$tabJoinDesc[self::INDEX_LOCAL];
                    }

                    //filtracja - utworzyc klauzule where z ewentualnym wykorzystaniem info o dolaczanych tabelach
                    if ($i > 0)
                        $whereClause .= ' and ';
                    
                    $whereClause .= $kolumnaFiltr.' is null';//$this->createWhereClause($kolumna, $dane, $kolumnaFiltr);
                    $i++;
                }
            }
            $show = false;
            if (isset($akcje[self::ACTION_SHOW]))
            {
                unset ($akcje[self::ACTION_SHOW]['id']);
                $show = true;
                foreach ($akcje[self::ACTION_SHOW] as $kolumna => $dane)
                {
                    $tabJoinDesc = '';
                    $selectClause .= ', ';
                    if (isset($colTablesToJoin[$kolumna]))
                    {
                        $tabJoinDesc = $colTablesToJoin[$kolumna];
                        $selectClause .= $tabJoinDesc[self::TABLE_NAME].'.'.$tabJoinDesc[self::COLUMN_NAME].' as '.$kolumna;
                        if (empty($colTablesToJoin[$kolumna]['used']))
                            $fromClause .= ' join '.$tabJoinDesc[self::TABLE_NAME].' on '.$tableDaneOs.'.'.$tabJoinDesc[self::INDEX_LOCAL].'='.$tabJoinDesc[self::TABLE_NAME].'.'.$tabJoinDesc[self::INDEX_REMOTE];
                    }
                    else
                    {
                        $selectClause .= $tableDaneOs.'.'.$kolumna;
                    }
                }
            }
                
            $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, $filter, $show, $tableDaneOs.'.id');
        }
        
        private function queriesPos_prawo_jazdy($ranga, $akcje) 
        {
            $tablePrawoJazdy = 'pos_prawo_jazdy';
            $selectClause = 'select '.$tablePrawoJazdy.'.id, '.$tablePrawoJazdy.'.id as '.self::COLUMN_OSOBA_ID;
            $fromClause = ' from '.$tablePrawoJazdy.' join prawo_jazdy on '.$tablePrawoJazdy.'.id_prawka = prawo_jazdy.id';
            $whereClause = ' where ';
            
            $kolumna = 'prawo_jazdy'; 
            $filter = false;
            
            if (isset($akcje[self::ACTION_FILTER]))
            {
                //if missing - left join for from
                $filter = true;
                //skoro istnieje action filter, a tam jest dopuszcona jedna kolumna, to musi byc to prawo_jazdy, stad sztywne zalozenia
                //metoda jest customowa dla zapytania op posiadane prawo jazdy, stad generycznosci tu sa zbedne
                $kolumnaFiltr = 'prawo_jazdy.nazwa';

                $dane = $akcje[self::ACTION_FILTER][$kolumna];
                if (isset($akcje[self::ACTION_NOT][$kolumna]))
                    $whereClause .= ' not ';

                $whereClause .= $this->createWhereClause($kolumna, $dane, $kolumnaFiltr);
            }
            
            $show = false;
            if (isset($akcje[self::ACTION_SHOW]))
            {
                $show = true;
                
                $selectClause .= ', prawo_jazdy.nazwa as '.$kolumna;
            }
            
            if (isset($akcje[self::ACTION_MISSING]))
            {
                $tablePrawoJazdy = 'dane_osobowe';
                
                if ($filter)
                {
                    //union
                    $unionSelect = 'select '.$tablePrawoJazdy.'.id, '.$tablePrawoJazdy.'.id as '.self::COLUMN_OSOBA_ID;
                    $unionFromClause = ' from '.$tablePrawoJazdy;
                    $unionWhereClause = ' where ';
                    
                    if ($show)
                    {
                        //dodatkowo z show
                        $unionSelect .= ', null as '.$kolumna;
                    }
                    
                    
                    $unionWhereClause .= $tablePrawoJazdy.'.id not in (select distinct id from pos_prawo_jazdy)';
                    $unionSelect .= $unionFromClause.$unionWhereClause.' union '.$selectClause;
                                                                                                             //last slot unused, if brought back reshape this query
                    //dodanie pojechanego zapytania 
                    $this->addQuery($unionSelect, $fromClause, $whereClause, $ranga, true, $show, $tablePrawoJazdy.'.id');
                }
                else
                {
                    //podmiana zapytania na dane os
                    
                    $selectClause = 'select '.$tablePrawoJazdy.'.id, '.$tablePrawoJazdy.'.id as '.self::COLUMN_OSOBA_ID;
                    $fromClause = ' from '.$tablePrawoJazdy;
                    $whereClause = ' where ';
                    if ($show)
                    {
                        //dodac show - null as kol
                        $selectClause .= ', null as '.$kolumna;
                    }
                    
                    $whereClause .= $tablePrawoJazdy.'.id not in (select distinct id from pos_prawo_jazdy)';
                    
                    //normalne add query, zmieniona tabela
                    $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, true, $show, $tablePrawoJazdy.'.id');
                }
                
                
            }
            else     
                $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, $filter, $show, $tablePrawoJazdy.'.id');
                
        }
        private function queriesUmiejetnosci_osob($ranga, $akcje)
        {
            $tableUmiejetnosci = 'umiejetnosci_osob';
            $selectClause = 'select '.$tableUmiejetnosci.'.id, '.$tableUmiejetnosci.'.id as '.self::COLUMN_OSOBA_ID;
            $fromClause = ' from '.$tableUmiejetnosci.' join umiejetnosc on '.$tableUmiejetnosci.'.id_umiejetnosc = umiejetnosc.id';
            $whereClause = ' where ';
            
            $kolumna = 'umiejetnosci'; 
            $filter = false;
            if (isset($akcje[self::ACTION_FILTER]))
            {
                $filter = true;
                //skoro istnieje action filter, a tam jest dopuszcona jedna kolumna, to musi byc to prawo_jazdy, stad sztywne zalozenia
                //metoda jest customowa dla zapytania o posiadane prawo jazdy, stad generycznosci tu sa zbedne

                $dane = $akcje[self::ACTION_FILTER][$kolumna];
                $kolumnaFiltr = 'umiejetnosc.nazwa';

                if (isset($akcje[self::ACTION_NOT][$kolumna]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumna, $dane, $kolumnaFiltr);
               
            }
            $show = false;
            if (isset($akcje[self::ACTION_SHOW]))
            {
                $show = true;
                
                $selectClause .= ', umiejetnosc.nazwa as '.$kolumna;
            }
            
            if (isset($akcje[self::ACTION_MISSING]))
            {
                $table = 'dane_osobowe';
                $inTable = 'umiejetnosci_osob';
                
                if ($filter)
                {
                    //union
                    $unionSelect = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $unionFromClause = ' from '.$table;
                    $unionWhereClause = ' where ';
                    
                    if ($show)
                    {
                        //dodatkowo z show
                        $unionSelect .= ', null as '.$kolumna;
                    }

                    $unionWhereClause .= $table.'.id not in (select distinct id from '.$inTable.')';
                    $unionSelect .= $unionFromClause.$unionWhereClause.' union '.$selectClause;
                                                                                                             //last slot unused, if brought back reshape this query
                    //dodanie pojechanego zapytania 
                    $this->addQuery($unionSelect, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
                else
                {
                    //podmiana zapytania na dane os
                    
                    $selectClause = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $fromClause = ' from '.$table;
                    $whereClause = ' where ';
                    if ($show)
                    {
                        //dodac show - null as kol
                        $selectClause .= ', null as '.$kolumna;
                    }
                    
                    $whereClause .= $table.'.id not in (select distinct id from '.$inTable.')';
                    
                    //normalne add query, zmieniona tabela
                    $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
            }
            else
                $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, $filter, $show, $tableUmiejetnosci.'.id');
        }
         
        private function queriesStat($ranga, $akcje)
        {
            $tableStat = 'stat';
            $selectClause = 'select '.$tableStat.'.id, '.$tableStat.'.id as '.self::COLUMN_OSOBA_ID;
            $fromClause = ' from '.$tableStat.' join status on '.$tableStat.'.id_status = status.id';
            $whereClause = ' where ';
            
            $kolumna = 'status'; 
            $filter = false;
            if (isset($akcje[self::ACTION_FILTER]))
            {
                $filter = true;
                //skoro istnieje action filter, a tam jest dopuszcona jedna kolumna, to musi byc to prawo_jazdy, stad sztywne zalozenia
                //metoda jest customowa dla zapytania o posiadane prawo jazdy, stad generycznosci tu sa zbedne

                $dane = $akcje[self::ACTION_FILTER][$kolumna];
                $kolumnaFiltr = 'status.nazwa';

                if (isset($akcje[self::ACTION_NOT][$kolumna]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumna, $dane, $kolumnaFiltr);
               
            }
            $show = false;
            if (isset($akcje[self::ACTION_SHOW]))
            {
                $show = true;
                
                $selectClause .= ', status.nazwa as '.$kolumna;
            }
            
            if (isset($akcje[self::ACTION_MISSING]))
            {
                $tableStat = 'dane_osobowe';
                
                if ($filter)
                {
                    //union
                    $unionSelect = 'select '.$tableStat.'.id, '.$tableStat.'.id as '.self::COLUMN_OSOBA_ID;
                    $unionFromClause = ' from '.$tableStat;
                    $unionWhereClause = ' where ';
                    
                    if ($show)
                    {
                        //dodatkowo z show
                        $unionSelect .= ', null as '.$kolumna;
                    }

                    $unionWhereClause .= $tableStat.'.id not in (select distinct id from stat)';
                    $unionSelect .= $unionFromClause.$unionWhereClause.' union '.$selectClause;
                                                                                                             //last slot unused, if brought back reshape this query
                    //dodanie pojechanego zapytania 
                    $this->addQuery($unionSelect, $fromClause, $whereClause, $ranga, true, $show, $tableStat.'.id');
                }
                else
                {
                    //podmiana zapytania na dane os
                    
                    $selectClause = 'select '.$tableStat.'.id, '.$tableStat.'.id as '.self::COLUMN_OSOBA_ID;
                    $fromClause = ' from '.$tableStat;
                    $whereClause = ' where ';
                    if ($show)
                    {
                        //dodac show - null as kol
                        $selectClause .= ', null as '.$kolumna;
                    }
                    
                    $whereClause .= $tableStat.'.id not in (select distinct id from stat)';
                    
                    //normalne add query, zmieniona tabela
                    $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, true, $show, $tableStat.'.id');
                }
                
                
            }
            else    
                $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, $filter, $show, $tableStat.'.id');
        }
        
        private function queriesKontakt($ranga, $akcje)
        {
            $tableKontakt = 'kontakt';
            $selectClause = 'select distinct '.$tableKontakt.'.id as '.self::COLUMN_OSOBA_ID.', '.$tableKontakt.'.id';
            $fromClause = ' from '.$tableKontakt; 
            
            $kolumnaDataKon = 'ostatni_kontakt'; 
            $kolumnaKonsKon = 'konsultant_kontakt';
            
            if (isset($akcje[self::ACTION_FILTER][$kolumnaKonsKon]) || isset($akcje[self::ACTION_SHOW][$kolumnaKonsKon]))
                $fromClause .= ' join uprawnienia on '.$tableKontakt.'.id_konsultant = uprawnienia.id';
            
            $whereClause = ' where ';
            $and = '';
            $filter = false;
            if (isset($akcje[self::ACTION_FILTER][$kolumnaDataKon]))
            {
                $filter = true; $and = ' and ';
                $dane = $akcje[self::ACTION_FILTER][$kolumnaDataKon];
                $kolumnaFiltr = 'data';

                if (isset($akcje[self::ACTION_NOT][$kolumnaDataKon]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumnaDataKon, $dane, $kolumnaFiltr);
            }
            
            if (isset($akcje[self::ACTION_FILTER][$kolumnaKonsKon]))
            {
                $filter = true;
                $dane = $akcje[self::ACTION_FILTER][$kolumnaKonsKon];
                $kolumnaFiltr = 'uprawnienia.nazwa_uzytkownika';
                
                $whereClause .= $and;

                if (isset($akcje[self::ACTION_NOT][$kolumnaKonsKon]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumnaKonsKon, $dane, $kolumnaFiltr);
            }
            $show = false;
            if (isset($akcje[self::ACTION_SHOW][$kolumnaDataKon]))
            {
                $show = true;
                $selectClause .= ', data as '.$kolumnaDataKon;
            }
            
            if (isset($akcje[self::ACTION_SHOW][$kolumnaKonsKon]))
            {
                $show = true;
                $selectClause .= ', uprawnienia.nazwa_uzytkownika as '.$kolumnaKonsKon;
            }
            if (isset($akcje[self::ACTION_MISSING][$kolumnaDataKon]) || isset($akcje[self::ACTION_MISSING][$kolumnaKonsKon]))
            {
                $table = 'dane_osobowe';
                $inTable = 'kontakt';
                
                if ($filter)
                {
                    //union
                    $unionSelect = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $unionFromClause = ' from '.$table;
                    $unionWhereClause = ' where ';
                    
                    if (isset($akcje[self::ACTION_SHOW][$kolumnaDataKon]))
                    {
                        $show = true;
                        $unionSelect .= ', null as '.$kolumnaDataKon;
                    }
                    
                    if (isset($akcje[self::ACTION_SHOW][$kolumnaKonsKon]))
                    {
                        $show = true;
                        $unionSelect .= ', null as '.$kolumnaKonsKon;
                    }

                    $unionWhereClause .= $table.'.id not in (select distinct id from '.$inTable.')';
                    $unionSelect .= $unionFromClause.$unionWhereClause.' union '.$selectClause;
                                                                                                             //last slot unused, if brought back reshape this query
                    //dodanie pojechanego zapytania 
                    $this->addQuery($unionSelect, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
                else
                {
                    //podmiana zapytania na dane os
                    
                    $selectClause = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $fromClause = ' from '.$table;
                    $whereClause = ' where ';
                    if (isset($akcje[self::ACTION_SHOW][$kolumnaDataKon]))
                    {
                        $show = true;
                        $selectClause .= ', null as '.$kolumnaDataKon;
                    }
                    
                    if (isset($akcje[self::ACTION_SHOW][$kolumnaKonsKon]))
                    {
                        $show = true;
                        $selectClause .= ', null as '.$kolumnaKonsKon;
                    }
                    
                    $whereClause .= $table.'.id not in (select distinct id from '.$inTable.')';
                    
                    //normalne add query, zmieniona tabela
                    $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
            }
            else    
                $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, $filter, $show, $tableKontakt.'.id');
        }
        
        private function queriesTelefon($ranga, $akcje)
        {
            $tableTelefon = 'telefon';
            $selectClause = 'select '.$tableTelefon.'.id, '.$tableTelefon.'.id as '.self::COLUMN_OSOBA_ID;
            $fromClause = ' from '.$tableTelefon;
            $whereClause = ' where ';
            
            $kolumna = 'telefon'; 
            $filter = false;
            if (isset($akcje[self::ACTION_FILTER]))
            {
                $filter = true;

                $dane = $akcje[self::ACTION_FILTER][$kolumna];
                $kolumnaFiltr = 'nazwa';

                if (isset($akcje[self::ACTION_NOT][$kolumna]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumna, $dane, $kolumnaFiltr);
               
            }
            $show = false;
            if (isset($akcje[self::ACTION_SHOW]))
            {
                $show = true;
                
                $selectClause .= ', nazwa as '.$kolumna;
            }
            
            if (isset($akcje[self::ACTION_MISSING]))
            {
                $table = 'dane_osobowe';
                
                if ($filter)
                {
                    //union
                    $unionSelect = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $unionFromClause = ' from '.$table;
                    $unionWhereClause = ' where ';
                    
                    if ($show)
                    {
                        //dodatkowo z show
                        $unionSelect .= ', null as '.$kolumna;
                    }
                    
                    
                    $unionWhereClause .= $table.'.id not in (select distinct id from telefon)';
                    $unionSelect .= $unionFromClause.$unionWhereClause.' union '.$selectClause;
                                                                                                             //last slot unused, if brought back reshape this query
                    //dodanie pojechanego zapytania 
                    $this->addQuery($unionSelect, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
                else
                {
                    //podmiana zapytania na dane os
                    
                    $selectClause = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $fromClause = ' from '.$table;
                    $whereClause = ' where ';
                    if ($show)
                    {
                        //dodac show - null as kol
                        $selectClause .= ', null as '.$kolumna;
                    }
                    
                    $whereClause .= $table.'.id not in (select distinct id from telefon)';
                    
                    //normalne add query, zmieniona tabela
                    $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
                
                
            }
            else    
                $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, $filter, $show, $tableTelefon.'.id');
        }
        
        private function queriesTelefon_kom($ranga, $akcje)
        {
            $tableTelefon = 'telefon_kom';
            $selectClause = 'select '.$tableTelefon.'.id, '.$tableTelefon.'.id as '.self::COLUMN_OSOBA_ID;
            $fromClause = ' from '.$tableTelefon;
            $whereClause = ' where ';
            
            $kolumna = 'komorka'; 
            $filter = false;
            if (isset($akcje[self::ACTION_FILTER]))
            {
                $filter = true;

                $dane = $akcje[self::ACTION_FILTER][$kolumna];
                $kolumnaFiltr = 'nazwa';

                if (isset($akcje[self::ACTION_NOT][$kolumna]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumna, $dane, $kolumnaFiltr);
               
            }
            $show = false;
            if (isset($akcje[self::ACTION_SHOW]))
            {
                $show = true;
                $selectClause .= ', nazwa as '.$kolumna;
            }
            
            if (isset($akcje[self::ACTION_MISSING]))
            {
                $table = 'dane_osobowe';
                
                if ($filter)
                {
                    //union
                    $unionSelect = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $unionFromClause = ' from '.$table;
                    $unionWhereClause = ' where ';
                    
                    if ($show)
                    {
                        //dodatkowo z show
                        $unionSelect .= ', null as '.$kolumna;
                    }
                    
                    
                    $unionWhereClause .= $table.'.id not in (select distinct id from '.$tableTelefon.')';
                    $unionSelect .= $unionFromClause.$unionWhereClause.' union '.$selectClause;
                                                                                                             //last slot unused, if brought back reshape this query
                    //dodanie pojechanego zapytania 
                    $this->addQuery($unionSelect, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
                else
                {
                    //podmiana zapytania na dane os
                    
                    $selectClause = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $fromClause = ' from '.$table;
                    $whereClause = ' where ';
                    if ($show)
                    {
                        //dodac show - null as kol
                        $selectClause .= ', null as '.$kolumna;
                    }
                    
                    $whereClause .= $table.'.id not in (select distinct id from '.$tableTelefon.')';
                    
                    //normalne add query, zmieniona tabela
                    $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
            }
            else    
                $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, $filter, $show, $tableTelefon.'.id');
        }
        
        private function queriesTelefon_inny($ranga, $akcje)
        {
            $tableTelefon = 'telefon_inny';
            $selectClause = 'select '.$tableTelefon.'.id, '.$tableTelefon.'.id as '.self::COLUMN_OSOBA_ID;
            $fromClause = ' from '.$tableTelefon;
            $whereClause = ' where ';
            
            $kolumna = 'telefon_inny'; 
            $filter = false;
            if (isset($akcje[self::ACTION_FILTER]))
            {
                $filter = true;

                $dane = $akcje[self::ACTION_FILTER][$kolumna];
                $kolumnaFiltr = 'nazwa';

                if (isset($akcje[self::ACTION_NOT][$kolumna]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumna, $dane, $kolumnaFiltr);
               
            }
            $show = false;
            if (isset($akcje[self::ACTION_SHOW]))
            {
                $show = true;
                
                $selectClause .= ', nazwa as '.$kolumna;
            }
            if (isset($akcje[self::ACTION_MISSING]))
            {
                $table = 'dane_osobowe';
                $inTable = 'telefon_inny';
                
                if ($filter)
                {
                    //union
                    $unionSelect = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $unionFromClause = ' from '.$table;
                    $unionWhereClause = ' where ';
                    
                    if ($show)
                    {
                        //dodatkowo z show
                        $unionSelect .= ', null as '.$kolumna;
                    }

                    $unionWhereClause .= $table.'.id not in (select distinct id from '.$inTable.')';
                    $unionSelect .= $unionFromClause.$unionWhereClause.' union '.$selectClause;
                                                                                                             //last slot unused, if brought back reshape this query
                    //dodanie pojechanego zapytania 
                    $this->addQuery($unionSelect, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
                else
                {
                    //podmiana zapytania na dane os
                    
                    $selectClause = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $fromClause = ' from '.$table;
                    $whereClause = ' where ';
                    if ($show)
                    {
                        //dodac show - null as kol
                        $selectClause .= ', null as '.$kolumna;
                    }
                    
                    $whereClause .= $table.'.id not in (select distinct id from '.$inTable.')';
                    
                    //normalne add query, zmieniona tabela
                    $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
            }
            else    
                $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, $filter, $show, $tableTelefon.'.id');
        }
        
        private function queriesEmail($ranga, $akcje)
        {
            $tableTelefon = 'email';
            $selectClause = 'select '.$tableTelefon.'.id, '.$tableTelefon.'.id as '.self::COLUMN_OSOBA_ID;
            $fromClause = ' from '.$tableTelefon;
            $whereClause = ' where ';
            
            $kolumna = 'email'; 
            $filter = false;
            if (isset($akcje[self::ACTION_FILTER]))
            {
                $filter = true;

                $dane = $akcje[self::ACTION_FILTER][$kolumna];
                $kolumnaFiltr = 'nazwa';

                if (isset($akcje[self::ACTION_NOT][$kolumna]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumna, $dane, $kolumnaFiltr);
               
            }
            $show = false;
            if (isset($akcje[self::ACTION_SHOW]))
            {
                $show = true;
                
                $selectClause .= ', nazwa as '.$kolumna;
            }
            if (isset($akcje[self::ACTION_MISSING]))
            {
                $table = 'dane_osobowe';
                $inTable = 'email';
                
                if ($filter)
                {
                    //union
                    $unionSelect = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $unionFromClause = ' from '.$table;
                    $unionWhereClause = ' where ';
                    
                    if ($show)
                    {
                        //dodatkowo z show
                        $unionSelect .= ', null as '.$kolumna;
                    }

                    $unionWhereClause .= $table.'.id not in (select distinct id from '.$inTable.')';
                    $unionSelect .= $unionFromClause.$unionWhereClause.' union '.$selectClause;
                                                                                                             //last slot unused, if brought back reshape this query
                    //dodanie pojechanego zapytania 
                    $this->addQuery($unionSelect, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
                else
                {
                    //podmiana zapytania na dane os
                    
                    $selectClause = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $fromClause = ' from '.$table;
                    $whereClause = ' where ';
                    if ($show)
                    {
                        //dodac show - null as kol
                        $selectClause .= ', null as '.$kolumna;
                    }
                    
                    $whereClause .= $table.'.id not in (select distinct id from '.$inTable.')';
                    
                    //normalne add query, zmieniona tabela
                    $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
            }
            else    
                $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, $filter, $show, $tableTelefon.'.id');
        }
        
        private function queriesZnane_jezyki($ranga, $akcje)
        {
            /*if (isset($akcje[self::ACTION_FILTER][$kolumnaZatwierdzony]) && 
                (($akcje[self::ACTION_FILTER][$kolumnaZatwierdzony] == 'tak' && isset($akcje[self::ACTION_NOT][$kolumnaZatwierdzony])) || 
                ($akcje[self::ACTION_FILTER][$kolumnaZatwierdzony] == 'nie' && !isset($akcje[self::ACTION_NOT][$kolumnaZatwierdzony]))))
            {
                $tableZnaneJezyki = 'osoby_bez_jezykow';
                $selectClause = 'select '.$tableZnaneJezyki.'.id_osoby, '.$tableZnaneJezyki.'.id_osoby as '.self::COLUMN_OSOBA_ID;
            }
            else 
            {*/
                $tableZnaneJezyki = 'znane_jezyki';
                $selectClause = 'select '.$tableZnaneJezyki.'.id, '.$tableZnaneJezyki.'.id as '.self::COLUMN_OSOBA_ID;
            //}
            $fromClause = ' from '.$tableZnaneJezyki; 
            
            $kolumnaJezyk = 'jezyk'; 
            $kolumnaPoziom = 'poziom';
            $kolumnaZatwierdzony = 'zatwierdzony_jezyk';
            
            //if (isset($akcje[self::ACTION_FILTER][$kolumnaJezyk]) || isset($akcje[self::ACTION_SHOW][$kolumnaJezyk]))
            //    $fromClause .= ' join jezyki on '.$tableZnaneJezyki.'.id_jezyk = jezyki.id';
            //if (isset($akcje[self::ACTION_FILTER][$kolumnaPoziom]) || isset($akcje[self::ACTION_SHOW][$kolumnaPoziom]))
            //    $fromClause .= ' join poziomy on '.$tableZnaneJezyki.'.id_poziom = poziomy.id';
            if (isset($akcje[self::ACTION_FILTER][$kolumnaZatwierdzony]) && 
                (($akcje[self::ACTION_FILTER][$kolumnaZatwierdzony] == 'tak' && !isset($akcje[self::ACTION_NOT][$kolumnaZatwierdzony])) ||
                ($akcje[self::ACTION_FILTER][$kolumnaZatwierdzony] == 'nie' && isset($akcje[self::ACTION_NOT][$kolumnaZatwierdzony]))))
                $fromClause .= ' join zatwierdzone_jezyki on '.$tableZnaneJezyki.'.id_znany_jezyk = zatwierdzone_jezyki.id_znany_jezyk';

            //    $fromClause .= ' join osoby_bez_jezykow on '.$tableZnaneJezyki.'.id_znany_jezyk = zatwierdzone_jezyki.id_znany_jezyk';
            else if (isset($akcje[self::ACTION_SHOW][$kolumnaZatwierdzony]) || isset($akcje[self::ACTION_FILTER][$kolumnaZatwierdzony])) 
                $fromClause .= ' left join zatwierdzone_jezyki on '.$tableZnaneJezyki.'.id_znany_jezyk = zatwierdzone_jezyki.id_znany_jezyk'; 
            
            $whereClause = ' where ';
            $and = '';
            $filter = false;
            if (isset($akcje[self::ACTION_FILTER][$kolumnaJezyk]))
            {
                $filter = true; $and = ' and ';
                $dane = $akcje[self::ACTION_FILTER][$kolumnaJezyk];
                //$kolumnaFiltr = 'jezyki.nazwa';
                $kolumnaFiltr = 'jezyk';

                if (isset($akcje[self::ACTION_NOT][$kolumnaJezyk]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumnaJezyk, $dane, $kolumnaFiltr);
            }
            
            if (isset($akcje[self::ACTION_FILTER][$kolumnaPoziom]))
            {
                $filter = true; 
                $dane = $akcje[self::ACTION_FILTER][$kolumnaPoziom];
                //$kolumnaFiltr = 'poziomy.nazwa';
                $kolumnaFiltr = 'poziom';
                
                $whereClause .= $and;

                if (isset($akcje[self::ACTION_NOT][$kolumnaPoziom]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumnaPoziom, $dane, $kolumnaFiltr);
                $and = ' and ';
            }
            //lepiej juz byl z samym left joinem wydajnosc ta sama jak nie lepsza a przynajmniej nie bylo fuck upow i kupy szajsu
            if (isset($akcje[self::ACTION_FILTER][$kolumnaZatwierdzony]) && ($akcje[self::ACTION_FILTER][$kolumnaZatwierdzony] == 'tak' || $akcje[self::ACTION_FILTER][$kolumnaZatwierdzony] == 'nie'))
            {
                $filter = true;
                if (($akcje[self::ACTION_FILTER][$kolumnaZatwierdzony] == 'nie' && !isset($akcje[self::ACTION_NOT][$kolumnaZatwierdzony])) || 
                    ($akcje[self::ACTION_FILTER][$kolumnaZatwierdzony] == 'tak' && isset($akcje[self::ACTION_NOT][$kolumnaZatwierdzony])))
                {
                    $kolumnaFiltr = 'zatwierdzone_jezyki.id_znany_jezyk';
                    
                    $whereClause .= $and.'('.$kolumnaFiltr.' is null)';
                }
                else
                {
                    $whereClause .= $and.'(1 = 1)';
                }
            }
            
            $show = false;
            if (isset($akcje[self::ACTION_SHOW][$kolumnaJezyk]))
            {
                $show = true;
                //$selectClause .= ', jezyki.nazwa as '.$kolumnaJezyk;
                $selectClause .= ', jezyk as '.$kolumnaJezyk;
            }
            
            if (isset($akcje[self::ACTION_SHOW][$kolumnaPoziom]))
            {
                $show = true;
                //$selectClause .= ', poziomy.nazwa as '.$kolumnaPoziom;
                $selectClause .= ', poziom as '.$kolumnaPoziom;
            }
            
            if (isset($akcje[self::ACTION_SHOW][$kolumnaZatwierdzony]))
            {
                $show = true;
                $selectClause .= ', zatwierdzone_jezyki.data as '.$kolumnaZatwierdzony;
            }
            if (isset($akcje[self::ACTION_MISSING]))
            {
                $table = 'dane_osobowe';
                $inTable = 'znane_jezyki';
                
                if ($filter)
                {
                    //union
                    $unionSelect = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $unionFromClause = ' from '.$table;
                    $unionWhereClause = ' where ';
                    
                    if (isset($akcje[self::ACTION_SHOW][$kolumnaJezyk]))
                    {
                        $show = true;
                        $unionSelect .= ', null as '.$kolumnaJezyk;
                    }
                    
                    if (isset($akcje[self::ACTION_SHOW][$kolumnaPoziom]))
                    {
                        $show = true;
                        $unionSelect .= ', null as '.$kolumnaPoziom;
                    }
                    
                    if (isset($akcje[self::ACTION_SHOW][$kolumnaZatwierdzony]))
                    {
                        $show = true;
                        $unionSelect .= ', null as '.$kolumnaZatwierdzony;
                    }

                    $unionWhereClause .= $table.'.id not in (select distinct id from '.$inTable.')';
                    $unionSelect .= $unionFromClause.$unionWhereClause.' union '.$selectClause;
                                                                                                             //last slot unused, if brought back reshape this query
                    //dodanie pojechanego zapytania 
                    $this->addQuery($unionSelect, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
                else
                {
                    //podmiana zapytania na dane os
                    
                    $selectClause = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $fromClause = ' from '.$table;
                    $whereClause = ' where ';
                    if (isset($akcje[self::ACTION_SHOW][$kolumnaJezyk]))
                    {
                        $show = true;
                        $selectClause .= ', null as '.$kolumnaJezyk;
                    }
                    
                    if (isset($akcje[self::ACTION_SHOW][$kolumnaPoziom]))
                    {
                        $show = true;
                        $selectClause .= ', null as '.$kolumnaPoziom;
                    }
                    
                    if (isset($akcje[self::ACTION_SHOW][$kolumnaZatwierdzony]))
                    {
                        $show = true;
                        $selectClause .= ', null as '.$kolumnaZatwierdzony;
                    }
                    
                    $whereClause .= $table.'.id not in (select distinct id from '.$inTable.')';
                    
                    //normalne add query, zmieniona tabela
                    $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
            }
            else    
                $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, $filter, $show, $tableZnaneJezyki.'.id');
        }
        
        private function queriesDodatkowe_osoby($ranga, $akcje) 
        {
            $tableDodatkoweOsoby = 'dodatkowe_osoby';
            $selectClause = 'select '.$tableDodatkoweOsoby.'.id, '.$tableDodatkoweOsoby.'.id as '.self::COLUMN_OSOBA_ID;
            $fromClause = ' from '.$tableDodatkoweOsoby; 
            
            $kolumnaGodnosc = 'dodatkowa_osoba'; 
            $kolumnaPlec = 'plec_dodatkowa_osoba';
            
            $fromClause .= ' join dane_osobowe on '.$tableDodatkoweOsoby.'.id_osoby_dod = dane_osobowe.id';
            if (isset($akcje[self::ACTION_FILTER][$kolumnaGodnosc]) || isset($akcje[self::ACTION_SHOW][$kolumnaGodnosc]))
                $fromClause .= ' join imiona on dane_osobowe.id_imie = imiona.id';
            if (isset($akcje[self::ACTION_FILTER][$kolumnaPlec]) || isset($akcje[self::ACTION_SHOW][$kolumnaPlec]))
                $fromClause .= ' join plec on dane_osobowe.id_plec = plec.id';
             
            
            $whereClause = ' where ';
            $and = '';
            $filter = false;
            if (isset($akcje[self::ACTION_FILTER][$kolumnaGodnosc]))
            {
                $filter = true; $and = ' and ';
                $dane = $akcje[self::ACTION_FILTER][$kolumnaGodnosc];
                $kolumnaFiltr = 'dane_osobowe.nazwisko || \' \' || imiona.nazwa'; 

                if (isset($akcje[self::ACTION_NOT][$kolumnaGodnosc]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumnaGodnosc, $dane, $kolumnaFiltr);
            }
            
            if (isset($akcje[self::ACTION_FILTER][$kolumnaPlec]))
            {
                $filter = true; 
                $dane = $akcje[self::ACTION_FILTER][$kolumnaPlec];
                $kolumnaFiltr = 'plec.nazwa';
                
                $whereClause .= $and;

                if (isset($akcje[self::ACTION_NOT][$kolumnaPlec]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumnaPlec, $dane, $kolumnaFiltr);
                $and = ' and ';
            }
            
            $show = false;
            if (isset($akcje[self::ACTION_SHOW][$kolumnaGodnosc]))
            {
                $show = true;
                $selectClause .= ', dane_osobowe.nazwisko || \' \' || imiona.nazwa as '.$kolumnaGodnosc;
            }
            
            if (isset($akcje[self::ACTION_SHOW][$kolumnaPlec]))
            {
                $show = true;
                $selectClause .= ', plec.nazwa as '.$kolumnaPlec;
            }
            
            if (isset($akcje[self::ACTION_MISSING][$kolumnaGodnosc]) || isset($akcje[self::ACTION_MISSING][$kolumnaPlec]))
            {
                $table = 'dane_osobowe';
                $inTable = 'dodatkowe_osoby';
                
                if ($filter)
                {
                    //union
                    $unionSelect = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $unionFromClause = ' from '.$table;
                    $unionWhereClause = ' where ';
                    
                    if (isset($akcje[self::ACTION_SHOW][$kolumnaGodnosc]))
                    {
                        $show = true;
                        $unionSelect .= ', null as '.$kolumnaGodnosc;
                    }
                    
                    if (isset($akcje[self::ACTION_SHOW][$kolumnaPlec]))
                    {
                        $show = true;
                        $unionSelect .= ', null as '.$kolumnaPlec;
                    }

                    $unionWhereClause .= $table.'.id not in (select distinct id from '.$inTable.')';
                    $unionSelect .= $unionFromClause.$unionWhereClause.' union '.$selectClause;
                                                                                                             //last slot unused, if brought back reshape this query
                    //dodanie pojechanego zapytania 
                    $this->addQuery($unionSelect, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
                else
                {
                    //podmiana zapytania na dane os
                    
                    $selectClause = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $fromClause = ' from '.$table;
                    $whereClause = ' where ';
                    if (isset($akcje[self::ACTION_SHOW][$kolumnaGodnosc]))
                    {
                        $show = true;
                        $selectClause .= ', null as '.$kolumnaGodnosc;
                    }
                    
                    if (isset($akcje[self::ACTION_SHOW][$kolumnaPlec]))
                    {
                        $show = true;
                        $selectClause .= ', null as '.$kolumnaPlec;
                    }
                    
                    $whereClause .= $table.'.id not in (select distinct id from '.$inTable.')';
                    
                    //normalne add query, zmieniona tabela
                    $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
            }
            else
                $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, $filter, $show, $tableDodatkoweOsoby.'.id');
        }
        
        private function queriesZatrudnienie($ranga, $akcje)
        {
            $tableZatrudnienie = 'zatrudnienie';
            $selectClause = 'select '.$tableZatrudnienie.'.id_osoba as id, '.$tableZatrudnienie.'.id_osoba as '.self::COLUMN_OSOBA_ID;
            $fromClause = ' from '.$tableZatrudnienie; 
            
            $kolumnaPracodawca = 'znani_pracodawcy'; 
            $kolumnaBiuro = 'biuro';
            $kolumnaMscOdjazd = 'msc_odjazd';
            $kolumnaDataPowrot = 'data_powrotu';
            //potencjalnie status zrodlem problemow, dodac na palke z dupy wtedy klauuzle na id status
            $kolumnaDataWyjazd = 'data';
            
            if (isset($akcje[self::ACTION_FILTER][$kolumnaPracodawca]) || isset($akcje[self::ACTION_SHOW][$kolumnaPracodawca]))
                $fromClause .= ' join klient on '.$tableZatrudnienie.'.id_klient = klient.id';
                
            if (isset($akcje[self::ACTION_FILTER][$kolumnaMscOdjazd]) || isset($akcje[self::ACTION_SHOW][$kolumnaMscOdjazd]))
                $fromClause .= ' join msc_odjazdu on '.$tableZatrudnienie.'.id_msc_odjazd = msc_odjazdu.id';
                
            if (isset($akcje[self::ACTION_FILTER][$kolumnaBiuro]) || isset($akcje[self::ACTION_SHOW][$kolumnaBiuro]))
            {
                $fromClause .= ' join oddzialy_klient on '.$tableZatrudnienie.'.id_oddzial = oddzialy_klient.id 
                join miejscowosc_biuro on oddzialy_klient.id_biuro = miejscowosc_biuro.id 
                join msc_biura on miejscowosc_biuro.id_msc_biuro = msc_biura.id';
            }
            //hak na sztywno
            if (isset($this->queryData['stat'][self::ACTION_FILTER]['status']) || isset($this->queryData['stat'][self::ACTION_SHOW]['status']))
                $fromClause .= ' join stat on '.$tableZatrudnienie.'.id_osoba = stat.id';
            
            $whereClause = ' where ';
            $and = '';
            $filter = false;
            if (isset($akcje[self::ACTION_FILTER][$kolumnaPracodawca]))
            {
                $filter = true; $and = ' and ';
                $dane = $akcje[self::ACTION_FILTER][$kolumnaPracodawca];
                $kolumnaFiltr = 'klient.nazwa';

                if (isset($akcje[self::ACTION_NOT][$kolumnaPracodawca]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumnaPracodawca, $dane, $kolumnaFiltr);
            }
            
            if (isset($akcje[self::ACTION_FILTER][$kolumnaBiuro]))
            {
                $filter = true; 
                $dane = $akcje[self::ACTION_FILTER][$kolumnaBiuro];
                $kolumnaFiltr = 'msc_biura.nazwa';
                if (!$and)
                    $and = ' and ';
                else
                    $whereClause .= $and;

                if (isset($akcje[self::ACTION_NOT][$kolumnaBiuro]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumnaBiuro, $dane, $kolumnaFiltr);
            }
            if (isset($akcje[self::ACTION_FILTER][$kolumnaMscOdjazd]))
            {
                $filter = true; 
                $dane = $akcje[self::ACTION_FILTER][$kolumnaMscOdjazd];
                $kolumnaFiltr = 'msc_odjazdu.nazwa';
                
                if (!$and)
                    $and = ' and ';
                else
                    $whereClause .= $and;

                if (isset($akcje[self::ACTION_NOT][$kolumnaMscOdjazd]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumnaMscOdjazd, $dane, $kolumnaFiltr);
            }
            if (isset($akcje[self::ACTION_FILTER][$kolumnaDataPowrot]))
            {
                $filter = true; 
                $dane = $akcje[self::ACTION_FILTER][$kolumnaDataPowrot];
                $kolumnaFiltr = $tableZatrudnienie.'.data_powrotu';
                
                if (!$and)
                    $and = ' and ';
                else
                    $whereClause .= $and;

                if (isset($akcje[self::ACTION_NOT][$kolumnaDataPowrot]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumnaDataPowrot, $dane, $kolumnaFiltr);
            }
            //chamski hak - sprawdzenie, czy dana szukana dla danych osobowych sztywno po randze, bo tu te dane nie sa podawane
            if ($filter && isset($this->queryData['dane_osobowe'][self::ACTION_FILTER][$kolumnaDataWyjazd]))
            {
                $dane = $this->queryData['dane_osobowe'][self::ACTION_FILTER][$kolumnaDataWyjazd];
                $kolumnaFiltr = $tableZatrudnienie.'.data_wyjazdu';
                
                if (!$and)
                    $and = ' and ';
                else
                    $whereClause .= $and;

                if (isset($this->queryData['dane_osobowe'][self::ACTION_NOT][$kolumnaDataWyjazd]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumnaDataWyjazd, $dane, $kolumnaFiltr);
            }
            //chamski hak na sztywno - teoretycznie tymczasowy
            if ($filter && isset($this->queryData['stat'][self::ACTION_FILTER]['status']))
            {
                $filter = true;
                $dane = $this->queryData['stat'][self::ACTION_FILTER]['status'];
                $kolumnaFiltr = 'stat.id_status';
                
                if (!$and)
                    $and = ' and ';
                else
                    $whereClause .= $and;

                if (isset($this->queryData['stat'][self::ACTION_NOT]['status']))
                    $whereClause .= ' not ';
                    
                $dane = explode(',', $dane);
                
                foreach ($dane as $element)
                {
                    $element = trim($element);
                    if ($element)
                        $filtr[] = 'lower(nazwa) like lower(\''.trim($element).'\')';
                }
                    
                //$whereClause .= '('.$kolumnaFiltr.' in (select id from status where '.implode(' or ', $filtr).'))';
                $whereClause .= '('.$tableZatrudnienie.'.id_status = '.$kolumnaFiltr.')';
                //echo $whereClause; exit;
            }
            
            $show = false;
            if (isset($akcje[self::ACTION_SHOW][$kolumnaPracodawca]))
            {
                $show = true;
                $selectClause .= ', klient.nazwa as '.$kolumnaPracodawca;
            }
            
            if (isset($akcje[self::ACTION_SHOW][$kolumnaBiuro]))
            {
                $show = true;
                $selectClause .= ', msc_biura.nazwa as '.$kolumnaBiuro;
            }
            
            if (isset($akcje[self::ACTION_SHOW][$kolumnaMscOdjazd]))
            {
                $show = true;
                $selectClause .= ', msc_odjazdu.nazwa as '.$kolumnaMscOdjazd;
            }
            
            if (isset($akcje[self::ACTION_SHOW][$kolumnaDataPowrot]))
            {
                $show = true;
                $selectClause .= ', '.$tableZatrudnienie.'.data_powrotu as '.$kolumnaDataPowrot;
            }    
            if (isset($akcje[self::ACTION_MISSING]))
            {
                $table = 'dane_osobowe';
                $inTable = 'zatrudnienie';
                $colsList = array($kolumnaPracodawca, $kolumnaBiuro, $kolumnaMscOdjazd, $kolumnaDataPowrot);
                
                if ($filter)
                {
                    //union
                    $unionSelect = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $unionFromClause = ' from '.$table;
                    $unionWhereClause = ' where ';
                    
                    foreach ($colsList as $kolumna)
                    {
                        if (isset($akcje[self::ACTION_SHOW][$kolumna]))
                        {
                            $show = true;
                            $unionSelect .= ', null as '.$kolumna;
                        }
                    }

                    $unionWhereClause .= $table.'.id not in (select distinct id_osoba from '.$inTable.')';
                    $unionSelect .= $unionFromClause.$unionWhereClause.' union '.$selectClause;
                                                                                                             //last slot unused, if brought back reshape this query
                    //dodanie pojechanego zapytania 
                    $this->addQuery($unionSelect, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
                else
                {
                    //podmiana zapytania na dane os
                    
                    $selectClause = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $fromClause = ' from '.$table;
                    $whereClause = ' where ';
                    foreach ($colsList as $kolumna)
                    {
                        if (isset($akcje[self::ACTION_SHOW][$kolumna]))
                        {
                            $show = true;
                            $selectClause .= ', null as '.$kolumna;
                        }
                    }
                    
                    $whereClause .= $table.'.id not in (select distinct id_osoba from '.$inTable.')';
                    
                    //normalne add query, zmieniona tabela
                    $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
            }
            else
                $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, $filter, $show, $tableZatrudnienie.'.id_osoba');
        }
        
        private function queriesPreferencje($ranga, $akcje)                                           
        {
            $tablePreferencje = 'preferencje';
            $selectClause = 'select '.$tablePreferencje.'.id, '.$tablePreferencje.'.id as '.self::COLUMN_OSOBA_ID;
            $fromClause = ' from '.$tablePreferencje.' join klient on '.$tablePreferencje.'.id_klient = klient.id';
            $whereClause = ' where ';
            
            $kolumna = 'preferencje'; 
            $filter = false;
            if (isset($akcje[self::ACTION_FILTER]))
            {
                $filter = true;

                $dane = $akcje[self::ACTION_FILTER][$kolumna];
                $kolumnaFiltr = 'klient.nazwa';

                if (isset($akcje[self::ACTION_NOT][$kolumna]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumna, $dane, $kolumnaFiltr);
               
            }
            $show = false;
            if (isset($akcje[self::ACTION_SHOW]))
            {
                $show = true;
                
                $selectClause .= ', klient.nazwa as '.$kolumna;
            }
            if (isset($akcje[self::ACTION_MISSING]))
            {
                $table = 'dane_osobowe';
                $inTable = 'preferencje';
                
                if ($filter)
                {
                    //union
                    $unionSelect = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $unionFromClause = ' from '.$table;
                    $unionWhereClause = ' where ';
                    
                    if ($show)
                    {
                        //dodatkowo z show
                        $unionSelect .= ', null as '.$kolumna;
                    }

                    $unionWhereClause .= $table.'.id not in (select distinct id from '.$inTable.')';
                    $unionSelect .= $unionFromClause.$unionWhereClause.' union '.$selectClause;
                                                                                                             //last slot unused, if brought back reshape this query
                    //dodanie pojechanego zapytania 
                    $this->addQuery($unionSelect, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
                else
                {
                    //podmiana zapytania na dane os
                    
                    $selectClause = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $fromClause = ' from '.$table;
                    $whereClause = ' where ';
                    if ($show)
                    {
                        //dodac show - null as kol
                        $selectClause .= ', null as '.$kolumna;
                    }
                    
                    $whereClause .= $table.'.id not in (select distinct id from '.$inTable.')';
                    
                    //normalne add query, zmieniona tabela
                    $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
            }
            else    
                $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, $filter, $show, $tablePreferencje.'.id');
        }
        
        private function queriesAntypatie($ranga, $akcje)                                           
        {
            $tableAntypatie = 'antypatie';
            $selectClause = 'select '.$tableAntypatie.'.id, '.$tableAntypatie.'.id as '.self::COLUMN_OSOBA_ID;
            $fromClause = ' from '.$tableAntypatie.' join klient on '.$tableAntypatie.'.id_klient = klient.id';
            $whereClause = ' where ';
            
            $kolumna = 'antypatie'; 
            $filter = false;
            if (isset($akcje[self::ACTION_FILTER]))
            {
                $filter = true;

                $dane = $akcje[self::ACTION_FILTER][$kolumna];
                $kolumnaFiltr = 'klient.nazwa';

                if (isset($akcje[self::ACTION_NOT][$kolumna]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumna, $dane, $kolumnaFiltr);
               
            }
            $show = false;
            if (isset($akcje[self::ACTION_SHOW]))
            {
                $show = true;
                
                $selectClause .= ', klient.nazwa as '.$kolumna;
            }
            if (isset($akcje[self::ACTION_MISSING]))
            {
                $table = 'dane_osobowe';
                $inTable = 'antypatie';
                
                if ($filter)
                {
                    //union
                    $unionSelect = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $unionFromClause = ' from '.$table;
                    $unionWhereClause = ' where ';
                    
                    if ($show)
                    {
                        //dodatkowo z show
                        $unionSelect .= ', null as '.$kolumna;
                    }

                    $unionWhereClause .= $table.'.id not in (select distinct id from '.$inTable.')';
                    $unionSelect .= $unionFromClause.$unionWhereClause.' union '.$selectClause;
                                                                                                             //last slot unused, if brought back reshape this query
                    //dodanie pojechanego zapytania 
                    $this->addQuery($unionSelect, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
                else
                {
                    //podmiana zapytania na dane os
                    
                    $selectClause = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $fromClause = ' from '.$table;
                    $whereClause = ' where ';
                    if ($show)
                    {
                        //dodac show - null as kol
                        $selectClause .= ', null as '.$kolumna;
                    }
                    
                    $whereClause .= $table.'.id not in (select distinct id from '.$inTable.')';
                    
                    //normalne add query, zmieniona tabela
                    $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
            }
            else    
                $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, $filter, $show, $tableAntypatie.'.id');
        }
        
        private function queriesZadania_dnia($ranga, $akcje)
        {
            $tableZadania = 'zadania_dnia';
            $selectClause = 'select '.$tableZadania.'.id, '.$tableZadania.'.id as '.self::COLUMN_OSOBA_ID;
            $fromClause = ' from '.$tableZadania;
            $whereClause = ' where ';
            $kolumnaZadaniaDnia = 'zadania_dnia'; 
            $kolumnaZadanie = 'zadanie';
            $and = '';
            $filter = false;
            if (isset($akcje[self::ACTION_FILTER][$kolumnaZadaniaDnia]))
            {
                $filter = true; $and = ' and ';
                $dane = $akcje[self::ACTION_FILTER][$kolumnaZadaniaDnia];
                $kolumnaFiltr = 'data';

                if (isset($akcje[self::ACTION_NOT][$kolumnaZadaniaDnia]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumnaZadaniaDnia, $dane, $kolumnaFiltr);
            }
            
            if (isset($akcje[self::ACTION_FILTER][$kolumnaZadanie]))
            {
                $filter = true; 
                $dane = $akcje[self::ACTION_FILTER][$kolumnaZadanie];
                $kolumnaFiltr = 'problem';
                
                if (!$and)
                    $and = ' and ';
                else
                    $whereClause .= $and;

                if (isset($akcje[self::ACTION_NOT][$kolumnaZadanie]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumnaZadanie, $dane, $kolumnaFiltr);
            }
            
            if ($filter)
            {
                $whereClause .= $and;
                $whereClause .= '(active = true)';
            }
            
            $show = false;
            if (isset($akcje[self::ACTION_SHOW][$kolumnaZadaniaDnia]))
            {
                $show = true;
                $selectClause .= ', '.$tableZadania.'.data as '.$kolumnaZadaniaDnia;
            }
            
            if (isset($akcje[self::ACTION_SHOW][$kolumnaZadanie]))
            {
                $show = true;
                $selectClause .= ', '.$tableZadania.'.problem as '.$kolumnaZadanie;
            }
            if (isset($akcje[self::ACTION_MISSING]))
            {
                $table = 'dane_osobowe';
                $inTable = 'zadania_dnia';
                $colsList = array($kolumnaZadaniaDnia, $kolumnaZadanie);
                
                if ($filter)
                {
                    //union
                    $unionSelect = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $unionFromClause = ' from '.$table;
                    $unionWhereClause = ' where ';
                    
                    foreach ($colsList as $kolumna)
                    {
                        if (isset($akcje[self::ACTION_SHOW][$kolumna]))
                        {
                            $show = true;
                            $unionSelect .= ', null as '.$kolumna;
                        }
                    }

                    $unionWhereClause .= $table.'.id not in (select distinct id from '.$inTable.')';
                    $unionSelect .= $unionFromClause.$unionWhereClause.' union '.$selectClause;
                                                                                                             //last slot unused, if brought back reshape this query
                    //dodanie pojechanego zapytania 
                    $this->addQuery($unionSelect, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
                else
                {
                    //podmiana zapytania na dane os
                    
                    $selectClause = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $fromClause = ' from '.$table;
                    $whereClause = ' where ';
                    foreach ($colsList as $kolumna)
                    {
                        if (isset($akcje[self::ACTION_SHOW][$kolumna]))
                        {
                            $show = true;
                            $selectClause .= ', null as '.$kolumna;
                        }
                    }
                    
                    $whereClause .= $table.'.id not in (select distinct id from '.$inTable.')';
                    
                    //normalne add query, zmieniona tabela
                    $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
            }
            else
                $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, $filter, $show, $tableZadania.'.id');
        }
        
        private function queriesKorespondencje($ranga, $akcje)
        {
            $tableKorespondencje = 'korespondencje';
            $selectClause = 'select '.$tableKorespondencje.'.id, '.$tableKorespondencje.'.id as '.self::COLUMN_OSOBA_ID;
            $fromClause = ' from '.$tableKorespondencje;
            $whereClause = ' where ';
            $kolumnaKorespondencja = 'korespondencja'; 
            $kolumnaRodzajKorespondencji = 'rodzaj_korespondencji';
            
            if (isset($akcje[self::ACTION_FILTER][$kolumnaRodzajKorespondencji]) || isset($akcje[self::ACTION_SHOW][$kolumnaRodzajKorespondencji]))
                $fromClause .= ' join rodzaj_korespondencji on '.$tableKorespondencje.'.id_korespondencji = rodzaj_korespondencji.id';
                
            $and = '';
            $filter = false;
            if (isset($akcje[self::ACTION_FILTER][$kolumnaKorespondencja]))
            {
                $filter = true; $and = ' and ';
                $dane = $akcje[self::ACTION_FILTER][$kolumnaKorespondencja];
                $kolumnaFiltr = 'data_korespondencji';

                if (isset($akcje[self::ACTION_NOT][$kolumnaKorespondencja]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumnaKorespondencja, $dane, $kolumnaFiltr);
            }
            
            if (isset($akcje[self::ACTION_FILTER][$kolumnaRodzajKorespondencji]))
            {
                $filter = true;
                $dane = $akcje[self::ACTION_FILTER][$kolumnaRodzajKorespondencji];
                $kolumnaFiltr = 'rodzaj_korespondencji.nazwa';
                
                if (!$and)
                    $and = ' and ';
                else
                    $whereClause .= $and;

                if (isset($akcje[self::ACTION_NOT][$kolumnaRodzajKorespondencji]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumnaRodzajKorespondencji, $dane, $kolumnaFiltr);
            }
            
            $show = false;
            if (isset($akcje[self::ACTION_SHOW][$kolumnaKorespondencja]))
            {
                $show = true;
                $selectClause .= ', '.$tableKorespondencje.'.data_korespondencji as '.$kolumnaKorespondencja;
            }
            
            if (isset($akcje[self::ACTION_SHOW][$kolumnaRodzajKorespondencji]))
            {
                $show = true;
                $selectClause .= ', rodzaj_korespondencji.nazwa as '.$kolumnaRodzajKorespondencji;
            }
            if (isset($akcje[self::ACTION_MISSING]))
            {
                $table = 'dane_osobowe';
                $inTable = 'korespondencje';
                $colsList = array($kolumnaKorespondencja, $kolumnaRodzajKorespondencji);
                
                if ($filter)
                {
                    //union
                    $unionSelect = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $unionFromClause = ' from '.$table;
                    $unionWhereClause = ' where ';
                    
                    foreach ($colsList as $kolumna)
                    {
                        if (isset($akcje[self::ACTION_SHOW][$kolumna]))
                        {
                            $show = true;
                            $unionSelect .= ', null as '.$kolumna;
                        }
                    }

                    $unionWhereClause .= $table.'.id not in (select distinct id from '.$inTable.')';
                    $unionSelect .= $unionFromClause.$unionWhereClause.' union '.$selectClause;
                                                                                                             //last slot unused, if brought back reshape this query
                    //dodanie pojechanego zapytania 
                    $this->addQuery($unionSelect, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
                else
                {
                    //podmiana zapytania na dane os
                    
                    $selectClause = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $fromClause = ' from '.$table;
                    $whereClause = ' where ';
                    foreach ($colsList as $kolumna)
                    {
                        if (isset($akcje[self::ACTION_SHOW][$kolumna]))
                        {
                            $show = true;
                            $selectClause .= ', null as '.$kolumna;
                        }
                    }
                    
                    $whereClause .= $table.'.id not in (select distinct id from '.$inTable.')';
                    
                    //normalne add query, zmieniona tabela
                    $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
            }
            else
                $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, $filter, $show, $tableKorespondencje.'.id');
        }
        
        private function queriesReklamacje($ranga, $akcje)                                           
        {
            $tableReklamacje = 'reklamacje';
            $selectClause = 'select '.$tableReklamacje.'.id, '.$tableReklamacje.'.id as '.self::COLUMN_OSOBA_ID;
            $fromClause = ' from '.$tableReklamacje;
            $whereClause = ' where ';
            
            $kolumna = 'data_reklamacji'; 
            $filter = false;
            if (isset($akcje[self::ACTION_FILTER]))
            {
                $filter = true;

                $dane = $akcje[self::ACTION_FILTER][$kolumna];
                $kolumnaFiltr = 'data';

                if (isset($akcje[self::ACTION_NOT][$kolumna]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumna, $dane, $kolumnaFiltr);
               
            }
            $show = false;
            if (isset($akcje[self::ACTION_SHOW]))
            {
                $show = true;
                
                $selectClause .= ', data as '.$kolumna;
            }
            if (isset($akcje[self::ACTION_MISSING]))
            {
                $table = 'dane_osobowe';
                $inTable = 'reklamacje';
                
                if ($filter)
                {
                    //union
                    $unionSelect = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $unionFromClause = ' from '.$table;
                    $unionWhereClause = ' where ';
                    
                    if ($show)
                    {
                        //dodatkowo z show
                        $unionSelect .= ', null as '.$kolumna;
                    }

                    $unionWhereClause .= $table.'.id not in (select distinct id from '.$inTable.')';
                    $unionSelect .= $unionFromClause.$unionWhereClause.' union '.$selectClause;
                                                                                                             //last slot unused, if brought back reshape this query
                    //dodanie pojechanego zapytania 
                    $this->addQuery($unionSelect, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
                else
                {
                    //podmiana zapytania na dane os
                    
                    $selectClause = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $fromClause = ' from '.$table;
                    $whereClause = ' where ';
                    if ($show)
                    {
                        //dodac show - null as kol
                        $selectClause .= ', null as '.$kolumna;
                    }
                    
                    $whereClause .= $table.'.id not in (select distinct id from '.$inTable.')';
                    
                    //normalne add query, zmieniona tabela
                    $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
            }
            else    
                $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, $filter, $show, $tableReklamacje.'.id');
        }
        
        private function queriesPoprzedni_pracodawca($ranga, $akcje)
        {
            $tablePoprzedniPracodawca = 'poprzedni_pracodawca';
            $selectClause = 'select '.$tablePoprzedniPracodawca.'.id, '.$tablePoprzedniPracodawca.'.id as '.self::COLUMN_OSOBA_ID;
            $fromClause = ' from '.$tablePoprzedniPracodawca;
            $whereClause = ' where ';
            $kolumnaPoprzedniPracodawca = 'poprzedni_pracodawca'; 
            $kolumnaBranza = 'branza';
            $kolumnaWykonywanyZawod = 'wykonywany_zawod';
            
            if (isset($akcje[self::ACTION_FILTER][$kolumnaBranza]) || isset($akcje[self::ACTION_SHOW][$kolumnaBranza]))
                $fromClause .= ' join branza on '.$tablePoprzedniPracodawca.'.id_branza = branza.id';
                
            if (isset($akcje[self::ACTION_FILTER][$kolumnaWykonywanyZawod]) || isset($akcje[self::ACTION_SHOW][$kolumnaWykonywanyZawod]))
                $fromClause .= ' join zawod on '.$tablePoprzedniPracodawca.'.id_grupa_zawodowa = zawod.id';
                
            $and = '';
            $filter = false;
            if (isset($akcje[self::ACTION_FILTER][$kolumnaPoprzedniPracodawca]))
            {
                $filter = true; $and = ' and ';
                $dane = $akcje[self::ACTION_FILTER][$kolumnaPoprzedniPracodawca];
                $kolumnaFiltr = $tablePoprzedniPracodawca.'.nazwa';

                if (isset($akcje[self::ACTION_NOT][$kolumnaPoprzedniPracodawca]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumnaPoprzedniPracodawca, $dane, $kolumnaFiltr);
            }
            
            if (isset($akcje[self::ACTION_FILTER][$kolumnaBranza]))
            {
                $filter = true; 
                $dane = $akcje[self::ACTION_FILTER][$kolumnaBranza];
                $kolumnaFiltr = 'branza.nazwa';
                
                if (!$and)
                    $and = ' and ';
                else
                    $whereClause .= $and;

                if (isset($akcje[self::ACTION_NOT][$kolumnaBranza]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumnaBranza, $dane, $kolumnaFiltr);
            }
            
            if (isset($akcje[self::ACTION_FILTER][$kolumnaWykonywanyZawod]))
            {
                $filter = true; 
                $dane = $akcje[self::ACTION_FILTER][$kolumnaWykonywanyZawod];
                $kolumnaFiltr = 'zawod.nazwa';
                
                if (!$and)
                    $and = ' and ';
                else
                    $whereClause .= $and;

                if (isset($akcje[self::ACTION_NOT][$kolumnaWykonywanyZawod]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumnaWykonywanyZawod, $dane, $kolumnaFiltr);
            }
            
            $show = false;
            if (isset($akcje[self::ACTION_SHOW][$kolumnaPoprzedniPracodawca]))
            {
                $show = true;
                $selectClause .= ', '.$tablePoprzedniPracodawca.'.nazwa as '.$kolumnaPoprzedniPracodawca;
            }
            
            if (isset($akcje[self::ACTION_SHOW][$kolumnaBranza]))
            {
                $show = true;
                $selectClause .= ', branza.nazwa as '.$kolumnaBranza;
            }
            
            if (isset($akcje[self::ACTION_SHOW][$kolumnaWykonywanyZawod]))
            {
                $show = true;
                $selectClause .= ', zawod.nazwa as '.$kolumnaWykonywanyZawod;
            }
            if (isset($akcje[self::ACTION_MISSING]))
            {
                $table = 'dane_osobowe';
                $inTable = 'poprzedni_pracodawca';
                $colsList = array($kolumnaPoprzedniPracodawca, $kolumnaBranza, $kolumnaWykonywanyZawod);
                
                if ($filter)
                {
                    //union
                    $unionSelect = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $unionFromClause = ' from '.$table;
                    $unionWhereClause = ' where ';
                    
                    foreach ($colsList as $kolumna)
                    {
                        if (isset($akcje[self::ACTION_SHOW][$kolumna]))
                        {
                            $show = true;
                            $unionSelect .= ', null as '.$kolumna;
                        }
                    }

                    $unionWhereClause .= $table.'.id not in (select distinct id from '.$inTable.')';
                    $unionSelect .= $unionFromClause.$unionWhereClause.' union '.$selectClause;
                                                                                                             //last slot unused, if brought back reshape this query
                    //dodanie pojechanego zapytania 
                    $this->addQuery($unionSelect, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
                else
                {
                    //podmiana zapytania na dane os
                    
                    $selectClause = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $fromClause = ' from '.$table;
                    $whereClause = ' where ';
                    foreach ($colsList as $kolumna)
                    {
                        if (isset($akcje[self::ACTION_SHOW][$kolumna]))
                        {
                            $show = true;
                            $selectClause .= ', null as '.$kolumna;
                        }
                    }
                    
                    $whereClause .= $table.'.id not in (select distinct id from '.$inTable.')';
                    
                    //normalne add query, zmieniona tabela
                    $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
            }
            else
                $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, $filter, $show, $tablePoprzedniPracodawca.'.id');
        }
        
        private function queriesJarograf($ranga, $akcje)
        {
            $tableJarograf = 'jarograf';
            $selectClause = 'select '.$tableJarograf.'.id, '.$tableJarograf.'.id as '.self::COLUMN_OSOBA_ID;
            $fromClause = ' from '.$tableJarograf;
            $whereClause = ' where ';
            $kolumnaJarograf = 'jarograf'; 
            $kolumnaKlient = 'klient_jarograf';
            $kolumnaData = 'odebrany.data';
            $kolumnaOdebrany = 'odebrany';
            
            if (isset($akcje[self::ACTION_FILTER][$kolumnaKlient]) || isset($akcje[self::ACTION_SHOW][$kolumnaKlient]))
                $fromClause .= ' join klient on '.$tableJarograf.'.id_klient = klient.id';
                
            if (isset($akcje[self::ACTION_FILTER][$kolumnaOdebrany]) || isset($akcje[self::ACTION_SHOW][$kolumnaOdebrany]))
                $fromClause .= ' left join odebrany on '.$tableJarograf.'.id = odebrany.id and '.$tableJarograf.'.rok = odebrany.rok';
                
                
            $and = '';
            $filter = false;
            if (isset($akcje[self::ACTION_FILTER][$kolumnaJarograf]))
            {
                $filter = true; $and = ' and ';
                $dane = $akcje[self::ACTION_FILTER][$kolumnaJarograf];
                $kolumnaFiltr = 'jarograf.rok';

                if (isset($akcje[self::ACTION_NOT][$kolumnaJarograf]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumnaJarograf, $dane, $kolumnaFiltr);
            }
            
            if (isset($akcje[self::ACTION_FILTER][$kolumnaKlient]))
            {
                $filter = true; 
                $dane = $akcje[self::ACTION_FILTER][$kolumnaKlient];
                $kolumnaFiltr = 'klient.nazwa';
                
                if (!$and)
                    $and = ' and ';
                else
                    $whereClause .= $and;

                if (isset($akcje[self::ACTION_NOT][$kolumnaKlient]))
                    $whereClause .= ' not ';
                    
                $whereClause .= $this->createWhereClause($kolumnaKlient, $dane, $kolumnaFiltr);
            }
            
            if (isset($akcje[self::ACTION_FILTER][$kolumnaOdebrany]))
            {
                $filter = true; 
                $dane = $akcje[self::ACTION_FILTER][$kolumnaOdebrany];
                $kolumnaFiltr = $kolumnaData;
                
                if (!$and)
                    $and = ' and ';
                else
                    $whereClause .= $and;

                if (isset($akcje[self::ACTION_NOT][$kolumnaOdebrany]))
                    $whereClause .= ' not ';
                    
                //$whereClause .= $this->createWhereClause($kolumnaData, $dane, $kolumnaFiltr);
                
                if ($dane == 'tak') {
                    
                    $whereClause .= ' '.$kolumnaFiltr.' is not null ';
                } else {
                    
                    $whereClause .= ' '.$kolumnaFiltr.' is null ';
                }
            }
            
            $show = false;
            if (isset($akcje[self::ACTION_SHOW][$kolumnaJarograf]))
            {
                $show = true;
                $selectClause .= ', '.$tableJarograf.'.rok as '.$kolumnaJarograf;
            }
            
            if (isset($akcje[self::ACTION_SHOW][$kolumnaKlient]))
            {
                $show = true;
                $selectClause .= ', klient.nazwa as '.$kolumnaKlient;
            }
            
            if (isset($akcje[self::ACTION_SHOW][$kolumnaOdebrany]))
            {
                $show = true;
                $selectClause .= ', '.$kolumnaData.' as '.$kolumnaOdebrany;
            }
            
            if (isset($akcje[self::ACTION_MISSING]))
            {
                $table = 'dane_osobowe';
                $inTable = 'jarograf';
                $colsList = array($kolumnaJarograf, $kolumnaKlient, $kolumnaOdebrany);
                
                if ($filter)
                {
                    //union
                    $unionSelect = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $unionFromClause = ' from '.$table;
                    $unionWhereClause = ' where ';
                    
                    foreach ($colsList as $kolumna)
                    {
                        if (isset($akcje[self::ACTION_SHOW][$kolumna]))
                        {
                            $show = true;
                            $unionSelect .= ', null as '.$kolumna;
                        }
                    }

                    $unionWhereClause .= $table.'.id not in (select distinct id from '.$inTable.')';
                    $unionSelect .= $unionFromClause.$unionWhereClause.' union '.$selectClause;
                                                                                                             //last slot unused, if brought back reshape this query
                    //dodanie pojechanego zapytania 
                    $this->addQuery($unionSelect, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
                else
                {
                    //podmiana zapytania na dane os
                    
                    $selectClause = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    $fromClause = ' from '.$table;
                    $whereClause = ' where ';
                    foreach ($colsList as $kolumna)
                    {
                        if (isset($akcje[self::ACTION_SHOW][$kolumna]))
                        {
                            $show = true;
                            $selectClause .= ', null as '.$kolumna;
                        }
                    }
                    
                    $whereClause .= $table.'.id not in (select distinct id from '.$inTable.')';
                    
                    //normalne add query, zmieniona tabela
                    $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, true, $show, $table.'.id');
                }
            }
            else
                $this->addQuery($selectClause, $fromClause, $whereClause, $ranga, $filter, $show, $tableJarograf.'.id');
        }
        
        private function queriesDane_dodatkowe($ranga, $akcje)
        {
            //poszczegolne rangi z $this->searchColumns
            $tableDaneDodatkowe = 'dane_dodatkowe';
            $fromClause = ' from '.$tableDaneDodatkowe;
            $kolumnaFiltr = 'wartosc';

            
            $aliasesCount = 1;
            $selectClause = 'select d1.id_osoba as id, d1.id_osoba as '.self::COLUMN_OSOBA_ID;
            $outerSelect = 'select id, '.self::COLUMN_OSOBA_ID;
            $innerFrom = ' from '.$tableDaneDodatkowe.' d'.$aliasesCount.' group by id_osoba';
            $finalWhere = ' ';
            
            if (isset($akcje[self::ACTION_FILTER]))
            {                
                foreach ($akcje[self::ACTION_FILTER] as $kolumna => $dane)
                {    
                    //$whereClause = ' where id_dane_dodatkowe_lista = '.$this->searchAddColumns[$kolumna]['id'].' and ';
                    $aliasesCount++;
                    $wartoscWhereClause = $this->createWhereClause($kolumna, $dane, $kolumna);
                    
                    if (isset($akcje[self::ACTION_NOT][$kolumna]))
                        $wartoscWhereClause = ' not '.$wartoscWhereClause;
                        
                    $selectClause .= ', (select '.$kolumnaFiltr.' from '.$tableDaneDodatkowe.' d'.$aliasesCount.' where d1.id_osoba = d'.$aliasesCount.'.id_osoba and d'.$aliasesCount.'.id_dane_dodatkowe_lista = '.$this->searchAddColumns[$kolumna]['id'].') as '.$kolumna;
                    
                    $show = false;
                    if (isset($akcje[self::ACTION_SHOW][$kolumna]))
                    {
                        $outerSelect .= ', '.$kolumna;
                        $show = true;
                        unset ($akcje[self::ACTION_SHOW][$kolumna]);
                    }
                    
                    $ranga = $this->searchColumns[$kolumna][self::COLUMN_RANK];
                    
                    if (isset($akcje[self::ACTION_MISSING][$kolumna]))
                    {
                        //and dane_dodatkowe_lista = 1 w joinie
                        //$table = 'dane_osobowe';
                        $wartoscWhereClause = '('.$wartoscWhereClause.' or '.$kolumna.' is null)';
                        $finalWhere .= ($aliasesCount > 2 ? ' and ' : ' where ') . $wartoscWhereClause;
                        /*$this->addQuery(
                        'select '.$table.'.id as id, '.$table.'.id as '.self::COLUMN_OSOBA_ID, 
                        ' from '.$table.' left join '.$tableDaneDodatkowe.' on id_dane_dodatkowe_lista = '.$this->searchAddColumns[$kolumna]['id'].' and '.$table.'.id = '.$tableDaneDodatkowe.'.id_osoba', ' where ('.$wartoscWhereClause.' or '.$kolumnaFiltr.' is null)', 
                        $ranga, true, $show, $tableDaneDodatkowe.'.id_osoba');*/
                        unset ($akcje[self::ACTION_MISSING][$kolumna]);
                    }
                    else
                        $finalWhere .= ($aliasesCount > 2 ? ' and ' : ' where ') . $wartoscWhereClause;
                        //$this->addQuery($selectClause, $fromClause, ' where id_dane_dodatkowe_lista = '.$this->searchAddColumns[$kolumna]['id'].' and '.$wartoscWhereClause, $ranga, true, $show, $tableDaneDodatkowe.'.id_osoba');
                }

            }
            if (isset($akcje[self::ACTION_MISSING]))
            {
                $table = 'dane_osobowe';
                $inTable = 'dane_dodatkowe'; 
                
                foreach ($akcje[self::ACTION_MISSING] as $kolumna => $dane)
                {
                    $aliasesCount++;
                    //$selectClause = 'select '.$table.'.id, '.$table.'.id as '.self::COLUMN_OSOBA_ID;
                    //$whereClause = ' where '.$kolumnaFiltr.' is null';
                    
                    $selectClause .= ', (select '.$kolumnaFiltr.' from '.$tableDaneDodatkowe.' d'.$aliasesCount.' where d1.id_osoba = d'.$aliasesCount.'.id_osoba and d'.$aliasesCount.'.id_dane_dodatkowe_lista = '.$this->searchAddColumns[$kolumna]['id'].') as '.$kolumna;
                    
                    $wartoscWhereClause = '('.$kolumna.' is null)';
                    $finalWhere .= ($aliasesCount > 2 ? ' and ' : ' where ') . $wartoscWhereClause;
                                            
                    $show = false;
                    if (isset($akcje[self::ACTION_SHOW][$kolumna]))
                    {
                        //$selectClause .= ', null as '.$kolumna;
                        $outerSelect .= ', '.$kolumna;
                        $show = true;
                        unset ($akcje[self::ACTION_SHOW][$kolumna]);
                    }
                    
                    $ranga = $this->searchColumns[$kolumna][self::COLUMN_RANK];
                    //$this->addQuery($selectClause, ' from '.$table.' left join '.$inTable.' on id_dane_dodatkowe_lista = '.$this->searchAddColumns[$kolumna]['id'].' and ' .$table.'.id = '.$inTable.'.id_osoba', $whereClause, $ranga, true, $show, $table.'.id');
                }
            }
            if (isset($akcje[self::ACTION_SHOW]))
            {
                $show = true;                
                foreach ($akcje[self::ACTION_SHOW] as $kolumna => $dane)
                {
                    $aliasesCount++;
                    $outerSelect .= ', '.$kolumna;
                    //$selectClause = 'select '.$tableDaneDodatkowe.'.id_osoba as id, '.$tableDaneDodatkowe.'.id_osoba as '.self::COLUMN_OSOBA_ID.', '.$tableDaneDodatkowe.'.'.$kolumnaFiltr.' as '.$kolumna;
                    $selectClause .= ', (select '.$kolumnaFiltr.' from '.$tableDaneDodatkowe.' d'.$aliasesCount.' where d1.id_osoba = d'.$aliasesCount.'.id_osoba and d'.$aliasesCount.'.id_dane_dodatkowe_lista = '.$this->searchAddColumns[$kolumna]['id'].') as '.$kolumna;
                    
                    //$whereClause = ' where id_dane_dodatkowe_lista = '.$this->searchAddColumns[$kolumna]['id'];
                    //dirty fuck up here - we need extra where clause to point the right row
                    //$this->addQuery($selectClause, $fromClause.$whereClause, '', $ranga, false, true, $tableDaneDodatkowe.'.id_osoba');
                }
            }
            
            $query = $outerSelect.' from ('.$selectClause.$innerFrom.') as ddd ';
            $this->addQuery($query, '', $finalWhere, $ranga, true, $show, 'ddd.id_osoba');
        }
    }
    
    class QueryError
    {
        private $column;
        private $msg;
        
        public function __construct ($msg, $column) 
        {
            $this->msg = $msg;
            $this->column = $column;
        }
        
        public function getMessage ()
        {
            return $this->msg;
        }
        
        public function getColumn ()
        {
            return $this->column;
        }
            
    }
    
    class DataRetrievalException extends Exception {}
    class TooManyRowsException extends DataRetrievalException {}