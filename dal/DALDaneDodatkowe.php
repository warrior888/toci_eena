<?php

    require_once 'dal/Model.php';

    abstract class DALDaneDodatkowe extends Model {

        public function __construct () {
            
            parent::__construct();
        }
        
        abstract public function getAdditionalDictionary ();        
        abstract public function getAdditionalInfo ($recordId);
        abstract public function getAdditionalInfoById ($recordId, $infoId);
        abstract public function setAdditionalInfo ($recordId, $infoIndex, $value);
        abstract public function deleteAdditionalInfo ($recordId, $infoIndex);
        abstract public function getMetaData ($personId);
        abstract public function setMetaData ($personId, $metadata);
    }