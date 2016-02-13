<?php


    class FormValidator {
        
        const VALIDATION_RESULT             = 'validationResult';
        const VALIDATION_ERRORS             = 'validationErrors';
        const VALIDATION_INPUT              = 'validationInput';
        
        const ADDITIONALS_KEY_ID      = 'id';
        const ADDITIONALS_KEY_NAZWA   = 'nazwa';
        const ADDITIONALS_KEY_LABEL   = 'nazwa_wyswietlana';
        const ADDITIONALS_KEY_ID_TYP  = 'id_typ';
        const ADDITIONALS_KEY_EDYCJA  = 'edycja';
        
        const REGEX_DATA   = '/^\d{4}-\d{2}-\d{2}$/';
        const REGEX_PHONE = '/^[1-9]{1}\d{8}$/';
        const REGEX_CELL = '/^[5-8]{1}\d{8}$/';
        const REGEX_EMAIL = '/^[\S,@,.,-]{6,35}$/';
        
        protected $formsRegexpValidations;
        protected $formsFieldsRequired;
        protected $formsCallbackValidations;
        
        
        public function __construct($regexpValidations = array(), $callbackValidations = array(), $requiredFields = array()) {
            
            $this->formsRegexpValidations = $regexpValidations;
            $this->formsCallbackValidations = $callbackValidations;
            $this->formsFieldsRequired = $requiredFields;
        }
        
        /**
        * @desc Validate incoming data against regexps and callbacks
        * @param array incoming data list
        * @return array (
        *   validationResult => array()
        *   validationErrors => array()
        *   validationInput => array()
        * )
        */
        public function validate ($incomingData) {
            
            // Keep user input on purpose of error hinting, but assume highly untrusted
            $validationResult = array(self::VALIDATION_INPUT => $incomingData);
            $validationResult[self::VALIDATION_RESULT] = array();
            $validationResult[self::VALIDATION_ERRORS] = array();
            
            foreach ($this->formsRegexpValidations as $field => $regex)
            {
                if (!isset($incomingData[$field]) || strlen($incomingData[$field]) == 0) {
                    
                    $incomingData[$field] = null;
                    if (!isset($this->formsFieldsRequired[$field])) {
                        
                        $validationResult[self::VALIDATION_RESULT][$field] = $incomingData[$field];
                        continue;
                    }
                }
                
                $incomingData[$field] = strip_tags($incomingData[$field]);
                
                $result = preg_match($regex, $incomingData[$field]);
                if (1 === $result)
                    $validationResult[self::VALIDATION_RESULT][$field] = $incomingData[$field];
                else
                {
                    $validationResult[self::VALIDATION_ERRORS][$field] = 'Warto¶æ '.View::escapeOutput($validationResult[self::VALIDATION_INPUT][$field]).' niepoprawna';
                    $validationResult[self::VALIDATION_RESULT][$field] = '';
                }
            }

            foreach ($this->formsCallbackValidations as $field => $callback)
            {
                // HA ! major question do we have regexp for all fields ? so far assume yes, otherwise cannot use data validated by above
                if ($validationResult[self::VALIDATION_RESULT][$field])
                {
                    if (false === call_user_func($callback, $validationResult[self::VALIDATION_RESULT][$field]))
                    {
                        $validationResult[self::VALIDATION_ERRORS][$field] = 'Warto¶æ '.View::escapeOutput($validationResult[self::VALIDATION_INPUT][$field]).' niepoprawna';
                        $validationResult[self::VALIDATION_RESULT][$field] = '';
                    }
                }
            }
            
            //extending array with data omitted by validations, so far unnecessary
            /*foreach ($validationResult[self::VALIDATION_INPUT] as $key => $value) {
                
                if (!isset($validationResult[self::VALIDATION_RESULT][$key])) {
                    //var_dump($key. ' '.$value);
                    $validationResult[self::VALIDATION_RESULT][$key] = $value;
                }
            } */
            
            return $validationResult;
        }
        
        //public function __unset () {
            
            // todo
        //}
    }