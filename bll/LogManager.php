<?php

    require_once 'dal/DALLogManager.php';

    class LogManager {
        
        protected static $dal;
        
        public static function initialize () {
            
            self::$dal = new DALLogManager();
        }
        
        /**
        * @desc 
        * @param int system log level
        * @param string arbitrary error message
        * @param ProjectException inner exception
        */
        public static function log ($logLevel, $logMsg, $e = null) {   //ProjectException
            
            if (!is_null($e) && $e instanceof ProjectException)
                $logMsg .= 'Trace: '.self::implodeGetMsg($e);
            else if (!is_null($e))
                $logMsg .= $e->getMessage();
            
            syslog($logLevel, $logMsg);
            
            //implement exception db log
            if (!is_null($e) && !($e instanceof ProjectException)) {
                
                self::$dal->set(
                    array(
                        Model::COLUMN_LOM_LOG_LEVEL  => $logLevel,
                        Model::COLUMN_LOM_MSG        => $logMsg,
                    )
                );
            } else if (is_null($e) || false === self::isDbException($e)) {

            	//call db save, unless db is down, glue msg with ex var export
                self::$dal->set(
                    array(
                        Model::COLUMN_LOM_LOG_LEVEL  => $logLevel,
                        Model::COLUMN_LOM_MSG        => $logMsg,
                    )
                );
            } else {
                
                //TODO log to a tmp folder custom log file :)
            }
        }
        
        protected static function isDbException (ProjectException $e) { //protected

            if (get_class($e) === 'DBException')
                return true;
                
            if (get_class($e) === 'DBQueryErrorException' || get_class($e) === 'ProjectException' || get_class($e) === 'Exception')
                return false;
                
            if (!is_null($e->getInnerException()))
                return self::isDbException($e->getInnerException());
                
            return false;
        }
        
        protected static function implodeGetMsg (ProjectException $e) {
            
            $msg = '';
            
            do {
                $msg .= ' ['.get_class($e).'] '.$e->getMessage();
                $e = $e->getInnerException();
            } while (!is_null($e));

            return $msg;
        }
    }
    
    LogManager::initialize();