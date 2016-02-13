<?php

    require_once 'Model.php';
    
    class DALLogManager extends Model {
        
        public function __construct () {
            
            parent::__construct();
        }
        
        /**
        * @desc Set the log entry into db
        * @param array $logData - data per column
        * @return bool success
        */
        public function set ($logData) {
            
            //there has to be made an exception for the rule here - we have to catch log errors here and not send them any higher
            //this is because if we happen to have a db error log manager will not try to log this to db
            //but in case we have query error like 'there is no table by that name' we will log this in db unless we forbid that
            //that is why we have to catch a logging error ... error and ... log it :) only in file
            
            //there is yet another reason to catch this exception here: logging occurs in catch blocks, so this exception would 
            //remain uncaught and cause a hardcore fatal
            
            $setEscCallbacks = array (
                Model::COLUMN_LOM_ID           => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_LOM_LOG_LEVEL    => array($this->dal, Model::METHOD_ESCAPE_INT),
                Model::COLUMN_LOM_MSG          => array($this->dal, Model::METHOD_ESCAPE_STRING),
            );
            
            $_logData = $this->escapeParamsList($setEscCallbacks, $logData);
            
            $query = 'insert into '.Model::TABLE_LOG_MANAGER.' ('.implode(',', array_keys($_logData)).') values (\''.implode('\',\'', $_logData).'\');';

            try {
                $this->dal->pgQuery($query);
            } catch (DBQueryErrorException $e) {
                //log query error in file only
                LogManager::log(LOG_ERR, '['.__CLASS__.'] Log manager dal set error for query '.$query.' with msg '.$e->getMessage());
            } catch (DBException $e) {
                //log db error - this would always be in the file only anyway
                LogManager::log(LOG_ERR, '['.__CLASS__.'] Log manager dal DBException set error for query '.$query.' with msg '.$e->getMessage());
            }
        }
    }