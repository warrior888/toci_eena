<?php
    //die('temporarily closed');
    require_once '../conf.php';
    require_once 'VacatsLogic.php';
    require_once 'RestOutput.php';
    require_once 'Authorization.php';
    require_once 'bll/LogManager.php';
    
    // option 1 : date interval
    
    // option 2 : dob, name/surname, person id
    
    // TODO authorization, fail on wrong
    
    class Vacats {
        
        /**
        * @desc IOutput output
        */
        protected $output;
        /**
        * @desc VacatsLogic
        */
        protected $logic;
        
        protected $authorization;
        
        public static $paramsArray = array(
            'ext',
            'id',
        );
        
        public function __construct(IOutput $output, VacatsLogic $logic, Authorization $authorization) {
            
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
        
        public function getAll($params) {
            
            $extType = isset($params['ext']) ? $params['ext'] : null;
    
            if (is_null($extType))
            {
                $this->output->ExitError(400);
            }
            
            try {
            
            	$resultsArray = $this->logic->getAll();
            	$this->output->RenderOutput($resultsArray, $extType, $this->authorization->Authorize());
            } catch (ViewException $e) {
    
		        LogManager::log(LOG_ERR, '['.__FILE__.'] Output instantiate/run error: '.$e->getMessage(), $e);
		        $this->output->ExitError(500);
		    } catch (Exception $e) {
		        
		        LogManager::log(LOG_ERR, '['.__FILE__.'] Output instantiate/run unhandled exception: '.$e->getMessage(), $e);
		        $this->output->ExitError(500);
		    }
        }
        
        public function get($params) {
            
            $extType = isset($params['ext']) ? $params['ext'] : null;
            $id = isset($params['id']) ? $params['id'] : null;
    
            if (is_null($extType) || is_null($id))
            {
                $this->output->ExitError(400);
            }
            
            try {
            
            	$resultsArray = $this->logic->get($id);
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
    //http://eenadev.21infinity.com/api/vacats.php?ext=xml
    //http://eenadev.21infinity.com/api/vacats.php?ext=xml&id=1234

    $authorization = array(Authorization::AUTHORIZATION => isset($_SERVER['HTTP_X_AUTHORIZATION']) ? $_SERVER['HTTP_X_AUTHORIZATION'] : '');
	    
    $vacats = new Vacats(new RestOutput(), new VacatsLogic(), new Authorization($_GET, $authorization, Vacats::$paramsArray));
	    
    if (isset($_GET['id']))
    {
        $vacats->get($_GET);
    }
    else
    {
        $vacats->getAll($_GET);
    }   