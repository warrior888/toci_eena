<?php

    /**
    * @desc Business Logic parent class responsible for bll data operations common behaviur.
    * In theory we could implement a chain of responsibility transaction heap for the set of operations on db conducted from a bll class method.
    * If this is eventually to be done, it is best to implement it somewhere here. However this will cause complexity and efficiency loss ata minimum
    * benefit - if there is aheap of 3 transactions, third fails, we rollback previous ones and the first fails, what should we do ?!
    * this can however be used in some more exceptional cases, or we could just replace the heap of operations by stored routine ... :)
    */
    
    require_once 'dal/Model.php';

    class Logic {
        
        const CACHED_DICTIONARIES               = 'dictiornaries_cached';
        
        protected $dataAccess;
        
        public function __construct () {
            
        }
        
        protected function indexResultById ($result, $index) {
            
            $remapped = array();
            foreach ($result as $row) {
                if (isset($row[$index]))
                    $remapped[$row[$index]] = $row;
            }
            
            return $remapped;
        }
        
        //caching support
        /**
        * @desc get a dictionary list for entry
        * @param string dictionary name - database table name usually
        */
        protected function getDictFromCache ($dictName) {
            //echo 'cache get '.$dictName;
            $cache = PermanentCache::get(self::CACHED_DICTIONARIES);
            
            if (!$cache || !isset($cache[$dictName]))
                return null;
                
            return $cache[$dictName];
        }
        
        /**
        * @desc set a dictionary list for entry
        */
        protected function setDictInCache ($dictName, $list) {
            //echo 'cache set '.$dictName;
            $cache = PermanentCache::get(self::CACHED_DICTIONARIES);
            
            if (!$cache)
                $cache = array();
                
            $cache[$dictName] = $list;
            
            PermanentCache::set(self::CACHED_DICTIONARIES, $cache);
        }
        
        protected function getDictionary ($table, $callback)
        {
            $cachedNames = $this->getDictFromCache($table);
            
            if ($cachedNames)
                return $cachedNames;
                
            $result = call_user_func($callback); //$this->dataAccess->getCitiesList();
            $this->setDictInCache($table, $result);
            
            return $result;
        }
    }

    abstract class ProjectLogicException extends ProjectException {
        
        const ERR_CODE_BAD_DATA       = 400;
        const ERR_CODE_NOT_FOUND      = 404;
        const ERR_CODE_CONFLICT_DATA  = 409;
        const ERR_CODE_SERVER_ERROR   = 500;
        
        protected $customMessage;
        
        public function __construct ($message, $code, $frontCustomMessage = '', $innerException = null) { //Exception
            
            parent::__construct($message, $code, $innerException);
            $this->customMessage = $frontCustomMessage;
        }
        
        public function getCustomMessage () {
            return $this->customMessage;
        }
    }
    
    class LogicBadDataException extends ProjectLogicException {
        
        public function __construct ($message, $frontCustomMessage = '', $innerException = null) { //Exception
            
            parent::__construct($message, ProjectLogicException::ERR_CODE_BAD_DATA, $frontCustomMessage, $innerException);
        }
    }
    
    class LogicConflictDataException extends ProjectLogicException {
        
        public function __construct ($message, $frontCustomMessage = '', $innerException = null) { //Exception
            
            parent::__construct($message, ProjectLogicException::ERR_CODE_CONFLICT_DATA, $frontCustomMessage, $innerException);
        }
    }
    
    class LogicNotFoundException extends ProjectLogicException {
        
        public function __construct ($message, $frontCustomMessage = '', $innerException = null) { //Exception
            
            parent::__construct($message, ProjectLogicException::ERR_CODE_NOT_FOUND, $frontCustomMessage, $innerException);
        }
    }
    
    class LogicServerErrorException extends ProjectLogicException {
        
        public function __construct ($message, $frontCustomMessage = '', $innerException = null) { //Exception
            
            parent::__construct($message, ProjectLogicException::ERR_CODE_SERVER_ERROR, $frontCustomMessage, $innerException);
        }
    }