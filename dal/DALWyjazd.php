<?

    require_once 'dal/Model.php';
    
    class DALWyjazd extends Model {
        
        const DEPARTURE_DATE_FROM       = 'departureDateFrom';
        const DEPARTURE_DATE_TO         = 'departureDateTo';
        const RETURN_DATE_FROM          = 'returnDateFrom';
        const RETURN_DATE_TO            = 'returnDateTo';
        const DEPARTURE_PERSON_ID       = 'personId';
        const DEPARTURE_TRAVELER_ID     = 'travelerId';
        const DEPARTURE_BIRTH_DATE      = 'birthDate';
        const DEPARTURE_SURNAME         = 'surname';
        const DEPARTURE_NAME            = 'name';

        public function __construct () {
            
            parent::__construct();
            $this->dal = dal::getInstance(true);
        }
        
        private $customParams = array(
        
            //self::DEPARTURE_DATE_FROM => self::DEPARTURE_DATE_FROM,
            'departureDateFrom' => 'departureDateFrom',
            'departureDateTo' => 'departureDateTo',
            'returnDateFrom' => 'returnDateFrom',
            'returnDateTo' => 'returnDateTo',
            'personId' => 'personId',
            'travelerId' => 'travelerId',
            'birthDate' => 'birthDate',
            'surname' => 'surname',
            'name' => 'name',
        );
        
        /**
        * @desc get departure list relying on custom criteria
        * @param array list of criteria to apply
        */
        public function getCustomDepartureList($statusList, $params) {

            if (count($params) < 1 && count(array_intersect_key($params, $this->customParams)) < 1)
                return null;
    
            $stringEscCallback = array($this->dal, 'escapeString');
            $intEscCallback = array($this->dal, 'escapeInt');
            
            $configuration = array(
                self::DEPARTURE_DATE_FROM               => $stringEscCallback,
                self::DEPARTURE_DATE_TO                 => $stringEscCallback,
                self::RETURN_DATE_FROM                  => $stringEscCallback,
                self::RETURN_DATE_TO                    => $stringEscCallback,
                self::DEPARTURE_BIRTH_DATE              => $stringEscCallback,
                self::DEPARTURE_PERSON_ID               => $intEscCallback,
                self::DEPARTURE_TRAVELER_ID             => $intEscCallback,
                self::DEPARTURE_NAME                    => $stringEscCallback,
                self::DEPARTURE_SURNAME                 => $stringEscCallback,
            );
            
            $_dataList = $this->escapeParamsList($configuration, $params);

            $query = 'select * from rezerwacje_wyjazd where ';
            
            $criteria = array( ' id_status in ('.implode(',', $statusList).')' );
            
            if (isset($_dataList[self::DEPARTURE_DATE_FROM])) {
                
                $criteria[] = ' data_wyjazdu >= \''.$_dataList[self::DEPARTURE_DATE_FROM].'\'';
            }
            
            if (isset($_dataList[self::DEPARTURE_DATE_TO])) {
                
                $criteria[] = ' data_wyjazdu <= \''.$_dataList[self::DEPARTURE_DATE_TO].'\'';
            }
            
            if (isset($_dataList[self::RETURN_DATE_FROM])) {
                
                $criteria[] = ' data_powrotu >= \''.$_dataList[self::RETURN_DATE_FROM].'\'';
            }
            
            if (isset($_dataList[self::RETURN_DATE_TO])) {
                
                $criteria[] = ' data_powrotu <= \''.$_dataList[self::RETURN_DATE_TO].'\'';
            }
            
            if (isset($_dataList[self::DEPARTURE_BIRTH_DATE])) {
                
                $criteria[] = ' data_urodzenia = \''.$_dataList[self::DEPARTURE_BIRTH_DATE].'\'';
            }
            
            if (isset($_dataList[self::DEPARTURE_PERSON_ID]) && $_dataList[self::DEPARTURE_PERSON_ID] > 0) {
                
                $criteria[] = ' id = '.$_dataList[self::DEPARTURE_PERSON_ID];
            }
            
            if (isset($_dataList[self::DEPARTURE_TRAVELER_ID]) && $_dataList[self::DEPARTURE_TRAVELER_ID] > 0) {
                
                $criteria[] = ' id_przewoznik = '.$_dataList[self::DEPARTURE_TRAVELER_ID];
            }
            
            if (isset($_dataList[self::DEPARTURE_NAME]) && strlen($_dataList[self::DEPARTURE_NAME])) {
                
                $criteria[] = ' imie = \''.$_dataList[self::DEPARTURE_NAME].'\'';
            }
            
            if (isset($_dataList[self::DEPARTURE_SURNAME]) && strlen($_dataList[self::DEPARTURE_SURNAME])) {
                
                $criteria[] = ' nazwisko = \''.$_dataList[self::DEPARTURE_SURNAME].'\'';
            }
            
            $query .= implode(' and ', $criteria);
//var_dump($query);
            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            return $this->formatDataOutput($result, $rowsCount);
        }
        
        public function getActiveList ($date) {
            
            $dal = dal::getInstance();
            $_date = $dal->escapeString($date);
            
            $query = 'select * from aktywny where data_wyjazdu <= \''.$_date.'\' and data_powrotu >= \''.$_date.'\';';
        
            $result = $dal->PobierzDane($query, $rowsCount);
            
            return $this->formatDataOutput($result, $rowsCount);
        }
        
        public function getActiveListRecipients() {
            
            $query = 'select * from '.Model::TABLE_RAPORT_AKTYWNY;
            
            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            return $this->formatDataOutput($result, $rowsCount);
        }
    }