<?php

    require_once 'dal/DALWyjazd.php';
    require_once 'bll/Logic.php';
    
    class DeparturesLogic extends Logic {
        
        public function __construct() {
            
            parent::__construct();
            
            $this->dataAccess = new DALWyjazd();
        }
        
        public function getDepartures ($statusList, $dateFrom, $dateTo, $returnFrom, $returnTo, $birthDate, $personId, $travelerId, $name, $surname) {
            
            $dalParams = array();
            
            $dates = array(
                DALWyjazd::DEPARTURE_DATE_FROM      => $dateFrom,
                DALWyjazd::DEPARTURE_DATE_TO        => $dateTo,
                DALWyjazd::RETURN_DATE_FROM         => $returnFrom,
                DALWyjazd::RETURN_DATE_TO           => $returnTo,
                DALWyjazd::DEPARTURE_BIRTH_DATE     => $birthDate,
            );
            
            foreach ($dates as $index => $date) {
                
                if (!is_null($date)) {
                    
                    $dalParams[$index] = date('Y-m-d', $date);
                }
            }
            
            $dalParams[DALWyjazd::DEPARTURE_PERSON_ID] = $personId;
            $dalParams[DALWyjazd::DEPARTURE_TRAVELER_ID] = $travelerId;
            
            $dalParams[DALWyjazd::DEPARTURE_NAME] = $name;
            $dalParams[DALWyjazd::DEPARTURE_SURNAME] = $surname;
            
            try {
            	$result = $this->dataAccess->getCustomDepartureList($statusList, $dalParams);
            } catch (DBException $e) {
                throw new LogicServerErrorException('['.__CLASS__.'] Data access error in getDepartures', '', $e);
            }
            
            return $result[Model::RESULT_FIELD_DATA];
        }
    }