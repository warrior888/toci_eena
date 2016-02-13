<?php
    require_once 'bll/Logic.php';

    class Adl {
        
        const SESSION_FIELD_USER    = 'user';
        
        protected $logic;
        protected $data;
        public $today;
        //anything above the main table data
        protected $extraData = array();
        
        public function __construct(Logic $logicObj) {
        
            $this->logic = $logicObj;
            $this->today = date('Y-m-d');
        }
        
        /**
        * @desc Call logic method for data in question, set the result and return it
        * @param string extraDataLogicMethod - logic method responsible for data return
        * @param array target method params in the form of array
        * @return array result {'rowsCount' => , 'data' => , 'metadata' => }
        */
        public function getExtraData ($extraDataLogicMethod, $methodParams) {
            
            //check for callability, but is used stiff way and tested, so seems overhead
            if (!is_callable(array($this->logic, $extraDataLogicMethod)) || !is_array($methodParams)) {
                throw new LogicServerErrorException('['.__CLASS__.'] getExtraData fail for '.$extraDataLogicMethod.' call');
            }
            
            if (isset($this->extraData[$extraDataLogicMethod]))
                return $this->extraData[$extraDataLogicMethod];
            
            $extraData = call_user_func_array(array($this->logic, $extraDataLogicMethod), $methodParams);
            if ($extraData === null)
                $this->extraData[$extraDataLogicMethod] = null;
            else
                $this->extraData[$extraDataLogicMethod] = $extraData; //[Model::RESULT_FIELD_DATA];
            
            return $this->extraData[$extraDataLogicMethod];
        }
    }
    
    class ProjectAdlException extends ProjectException {}
    class AccountExpiredException extends ProjectAdlException {}