<?php

    require_once 'dal/DALZatrudnienie.php';
    require_once 'dal/DALWakaty.php';
    require_once 'dal/DALDaneOsobowe.php';
    require_once 'bll/Logic.php';
    
    require_once 'oblicz_date.php';
    
    class BLLZatrudnienie extends Logic {
        
        protected $dalZatrudnienie;
        protected $dalWakaty;
        protected $dalDaneOsobowe;
        
        public function __construct() {
            
            parent::__construct();
            //$this->dataAccess
            $this->dalZatrudnienie = new DALZatrudnienie();
            $this->dalWakaty = new DALWakaty();
            $this->dalDaneOsobowe = new DALDaneOsobowe();
        }
        
        public function getEmployPretendents($vacatId, $decisionId) {
            
            $vacatDetailsData = $this->dalWakaty->get($vacatId);
            
            if ($vacatDetailsData[Model::RESULT_FIELD_ROWS_COUNT] == 0) {
                
                throw new LogicConflictDataException('Vacat is not found', 'Nie znaleziony wakat.');
            }
            
            try {
                return $this->dalZatrudnienie->getEmployPretendents($vacatId, $decisionId);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getEmployPretendents', 'B³¹d pobierania danych osób zatrudnianych.', $e);
            }
        }
        
        // logika sprawdzenia czy wpis w zatrudnieniu istnieje, dodanie
        /**
         * pobranie danych o wakacie po vacatId i sformulowanie wpisu do zatrudnienia z opcj¹ aplikuj¹cy
         * @param int $personId 
         * @param int $vacatId
         */
        public function apply($personId, $vacatId) {
            
            $hasVacat = $this->dalZatrudnienie->getByVacatIdPersonId($personId, $vacatId);
            
            // check if he has this vacat already
            if ($hasVacat[Model::RESULT_FIELD_ROWS_COUNT] > 0) {
                
                throw new LogicConflictDataException('Vacat already applied to: person id '.$personId.', vacat id '.$vacatId.'.', 'Aplikowano ju¿ na ten wakat.');
            }
            
            $vacatData = $this->dalWakaty->get($vacatId);
            $personData = $this->dalDaneOsobowe->get($personId);
            
            if ($vacatData[Model::RESULT_FIELD_ROWS_COUNT] == 0 || $vacatData[Model::RESULT_FIELD_DATA][0][Model::COLUMN_WAK_DATA_WYJAZDU] < date('Y-m-d')) {
                
                throw new LogicConflictDataException('Vacat missing or too old: person id '.$personId.', vacat id '.$vacatId.'.', 'Nie znaleziono wakatu.');
            }
            
            $employmentData = $this->mapVacatToEmployment($vacatData[Model::RESULT_FIELD_DATA][0], $personId);
            $employmentData[Model::COLUMN_ZTR_ID_PRACOWNIK] = $personData[Model::RESULT_FIELD_DATA][Model::COLUMN_DOS_ID_KONSULTANT];
            
            try {
                $employmentId = $this->dalZatrudnienie->set($employmentData);
                return $employmentId;
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in apply', 'B³±d wprowadzania danych danych osoby zatrudnianej.', $e);
            }
        }
        
        protected function mapVacatToEmployment($vacatData, $personId) {

            $mapping = array(
            
                Model::COLUMN_WAK_ID               => Model::COLUMN_ZTR_ID_WAKAT,
                Model::COLUMN_WAK_ID_KLIENT        => Model::COLUMN_ZTR_ID_KLIENT,
                Model::COLUMN_WAK_ID_ODDZIAL       => Model::COLUMN_ZTR_ID_ODDZIAL,
                Model::COLUMN_WAK_DATA_WYJAZDU     => Model::COLUMN_ZTR_DATA_WYJAZDU,
                Model::COLUMN_WAK_ILOSC_TYG        => Model::COLUMN_ZTR_ILOSC_TYG,
                Model::COLUMN_WAK_ID_KONSULTANT    => Model::COLUMN_ZTR_ID_PRACOWNIK,
                Model::COLUMN_WAK_DATA_WPISU       => Model::COLUMN_ZTR_DATA_WPISU,
            );
            // TODO
            // Model::COLUMN_ZTR_ID_STATUS          
  
            $employmentData = array();
            
            $employmentData[Model::COLUMN_ZTR_ID_OSOBA] = $personId;
            $employmentData[Model::COLUMN_ZTR_ID_STATUS] = ID_STATUS_NOWY;
            $employmentData[Model::COLUMN_ZTR_ID_DECYZJA] = ID_DECYZJA_APLIKUJACY;
            
            foreach ($mapping as $key => $value) {
                
                $employmentData[$value] = $vacatData[$key];
            }
            
            list ($year, $month, $day) = explode('-', $employmentData[Model::COLUMN_ZTR_DATA_WYJAZDU]);
            $employmentData[Model::COLUMN_ZTR_DATA_POWROTU] = oblicz_date($year, $month, $day, $employmentData[Model::COLUMN_ZTR_ILOSC_TYG]);
            
            return $employmentData;
        }

    }