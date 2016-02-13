<?php
    //die('temporarily closed');
    require_once '../conf.php';
    require_once 'DeparturesLogic.php';
    require_once 'RestOutput.php';
    require_once 'Authorization.php';
    require_once 'bll/LogManager.php';
    
    // option 1 : date interval
    
    // option 2 : dob, name/surname, person id
    
    // TODO authorization, fail on wrong
    
    class DeparturesNsf {
        
        /**
        * @desc IOutput output
        */
        protected $output;
        /**
        * @desc DeparturesLogic
        */
        protected $logic;
        
        protected $authorization;
        
        public static $paramsArray = array(
            'ext',
            'departureDateFrom',
            'departureDateTo',
            'returnDateFrom',
            'returnDateTo',
            'personId',
            'travelerId',
            'birthDate',
            'surname',
            'name',
        );
        
        public function __construct(IOutput $output, DeparturesLogic $logic, Authorization $authorization) {
            
            $this->output = $output;
            $this->logic = $logic;
            $this->authorization = $authorization;

            // get request headers, authorize
            
            if (!$authorization->Authorize())
            {
            	LogManager::log(LOG_NOTICE, 'Invalid authorization. Time diff: '.$authorization->getAuthTimeDifference().', '.$authorization->getGeneratedHash().', '.$authorization->getRequestAuthorization());
                $this->output->ExitError(401);
            }
        }
        
        public function getDepartures($params) {
            
            $extType = isset($params['ext']) ? $params['ext'] : null;
    
            if (is_null($extType))
            {
                $this->output->ExitError(400);
            }
            
            if (sizeof(array_intersect_key($params, array_flip(self::$paramsArray))) < 2)
            {
            	$this->output->ExitError(400);
            }
            
            /*
            imię, nazwisko, dokładny adres osoby, kontakt(email i/lub 
        telefon), data i miejsce wyjazdu, ewentualnie powrotu.
            */
            
            $dateFrom = isset($params['departureDateFrom']) ? (int)$params['departureDateFrom'] : null;
            $dateTo = isset($params['departureDateTo']) ? (int)$params['departureDateTo'] : null;
            $returnFrom = isset($params['returnDateFrom']) ? (int)$params['returnDateFrom'] : null;
            $returnTo = isset($params['returnDateTo']) ? (int)$params['returnDateTo'] : null;
            $birthDate = isset($params['birthDate']) ? (int)$params['birthDate'] : null;
            $personId = isset($params['personId']) ? (int)$params['personId'] : null;
            $travelerId = isset($params['travelerId']) ? (int)$params['travelerId'] : null;
            $name = isset($params['name']) ? $params['name'] : null;
            $surname = isset($params['surname']) ? $params['surname'] : null;
            
            try {
            
            	$resultsArray = $this->logic->getDepartures(array(ID_STATUS_WYJEZDZAJACY, ID_STATUS_AKTYWNY), $dateFrom, $dateTo, $returnFrom, $returnTo, $birthDate, $personId, $travelerId, $name, $surname);
            	$this->output->RenderOutput($resultsArray, $extType, $this->authorization->Authorize());
            } catch (ViewException $e) {
    
		        LogManager::log(LOG_ERR, '['.__FILE__.'] Output instantiate/run error: '.$e->getMessage(), $e);
		        $this->output->ExitError(500);
		    } catch (Exception $e) {
		        
		        LogManager::log(LOG_ERR, '['.__FILE__.'] Output instantiate/run unhandled exception: '.$e->getMessage(), $e);
		        $this->output->ExitError(500);
		    }
        }
    }
    //http://eenadev.21infinity.com/api/departures.php?ext=xml&departureDateFrom=12

    $authorization = array(Authorization::AUTHORIZATION => isset($_SERVER['HTTP_X_AUTHORIZATION']) ? $_SERVER['HTTP_X_AUTHORIZATION'] : '');
	    
    $departures = new DeparturesNsf(new RestOutput(), new DeparturesLogic(), new Authorization($_GET, $authorization, DeparturesNsf::$paramsArray));
	    
    $departures->getDepartures($_GET);   