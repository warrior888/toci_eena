<?php

    require_once 'dal/DALDaneDodatkowe.php';
    
    class DALDaneDodatkoweOsoba extends DALDaneDodatkowe {
        
        /**
        * @desc Get list of columns of additional data available for a person
        */
        public function getAdditionalDictionary () {
            
            $result = $this->dal->PobierzDane('select '.Model::COLUMN_DICT_ID.', '.Model::COLUMN_DICT_NAZWA.', 
                '.Model::COLUMN_DDL_EDYCJA.', '.Model::COLUMN_DDL_NAZWA_WYSWIETLANA.', '.Model::COLUMN_DDL_ID_TYP.' from '.
                Model::TABLE_DANE_DODATKOWE_LISTA.' order by '.Model::COLUMN_DICT_NAZWA, $rowsCount);
            //.' where '.Model::COLUMN_DICT_NAZWA.' in (\''.implode('\',\'', $this->configurationTable).'\');'));
                
            if ($rowsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $rowsCount);
        }

        /**
        * @desc Get the entire list of additional infos for a person
        */
        public function getAdditionalInfo ($personId) {
            
            $_personId = $this->dal->escapeInt($personId);
            
            $query = 'select * from '.Model::TABLE_DANE_DODATKOWE.' where '.Model::COLUMN_DDO_ID_OSOBA.' = '.$_personId;
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        /**
        * @desc Get the list of additional infos for a person and info id
        */
        public function getAdditionalInfoById ($personId, $infoId) {
            
            $_personId = $this->dal->escapeInt($personId);
            $_infoId = $this->dal->escapeInt($infoId);
            
            $query = 'select * from '.Model::TABLE_DANE_DODATKOWE.' where '.Model::COLUMN_DDO_ID_OSOBA.' = '.$_personId.' and '.Model::COLUMN_DDO_ID_DANE_DODATKOWE_LISTA.' = '.$_infoId;
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        /**
        * @desc Set additional information for a candidate for information id. Potential value discovery/validation should be based on bll logic.
        */
        public function setAdditionalInfo ($personId, $infoIndex, $value) {
            
            $_personId = $this->dal->escapeInt($personId);
            $_infoIndex = $this->dal->escapeInt($infoIndex);
            $_value = $this->dal->escapeString($value);
            
            $query = 'select setAdditionalInfoById as success 
            from setAdditionalInfoById('.$_personId.', '.$_infoIndex.', \''.$_value.'\');';
            $result = $this->dal->PobierzDane($query);
            
            if ($result[0]['success'] == 0)
                return null;
                
            return true;
        }
        
        /**
        * @desc delete record of additional person info
        * @param int id of a person
        * @param int id of an information
        */
        public function deleteAdditionalInfo ($personId, $infoIndex) {
            
            $_personId = $this->dal->escapeInt($personId);
            $_infoIndex = $this->dal->escapeInt($infoIndex);
            
            $query = 'delete from '.Model::TABLE_DANE_DODATKOWE.' where '.Model::COLUMN_DDO_ID_OSOBA.' = '.$_personId.' and '.Model::COLUMN_DDO_ID_DANE_DODATKOWE_LISTA.' = '.$_infoIndex;
            
            return $this->dal->pgQuery($query);
        }
        
        /**
        * @desc get person meta data
        * @param int id of a person
        */
        public function getMetaData ($personId) {
            
            $_personId = $this->dal->escapeInt($personId);
            
            $query = 'select * from '.Model::TABLE_METADANE_OSOBOWE.' where '.Model::COLUMN_MDO_ID_OSOBA.' = '.$_personId;
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            $dane = array_shift($result);
            $dane[Model::COLUMN_MDO_DANE] = unserialize($dane[Model::COLUMN_MDO_DANE]);
                
            return $this->formatDataOutput($dane, $recordsCount);
        }
        
        /**
        * @desc set person meta data
        * @param int id of a person
        * @param array person meta data
        */
        public function setMetaData ($personId, $metadata) {
            
            if (!is_array($metadata))
                throw new DBInvalidDataException('metadata for set metadata are expected to be array');
                
            $_personId = $this->dal->escapeInt($personId);
            $result = $this->getMetaData($_personId);
            
            $dane = $result ? $result[Model::RESULT_FIELD_DATA][Model::COLUMN_MDO_DANE] : array();
            
            $dane = array_merge($dane, $metadata);
            
            $_dbMetadata = $this->dal->escapeString(serialize($dane));
            
            if ($result) {
                
                //update
                $query = 'update '.Model::TABLE_METADANE_OSOBOWE.' set '.Model::COLUMN_MDO_DANE.' = \''.$_dbMetadata.'\' where '.Model::COLUMN_MDO_ID_OSOBA.' = '.$_personId;
            } else {
                
                $query = 'insert into '.Model::TABLE_METADANE_OSOBOWE.' ('.Model::COLUMN_MDO_ID_OSOBA.', '.Model::COLUMN_MDO_DANE.') values ('.$_personId.', \''.$_dbMetadata.'\');';
            }
            
            return $this->dal->pgQuery($query);
        }
    }