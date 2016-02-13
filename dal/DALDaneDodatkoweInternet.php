<?php

    require_once 'dal/DALDaneDodatkowe.php';
    
    class DALDaneDodatkoweInternet extends DALDaneDodatkowe {
        
        /**
        * @desc Get list of columns of additional data available for a person on registration form
        */
        public function getAdditionalDictionary () {
            
            $result = $this->dal->PobierzDane('select '.Model::COLUMN_DICT_ID.', '.Model::COLUMN_DICT_NAZWA.', 
                '.Model::COLUMN_DDL_EDYCJA.', '.Model::COLUMN_DDL_NAZWA_WYSWIETLANA.', '.Model::COLUMN_DDL_ID_TYP.' from '.
                Model::TABLE_DANE_DODATKOWE_LISTA.' join '.Model::TABLE_DANE_DODATKOWE_INTERNET_LISTA.' 
                on '.Model::TABLE_DANE_DODATKOWE_LISTA.'.'.Model::COLUMN_DICT_ID.' = '.Model::TABLE_DANE_DODATKOWE_INTERNET_LISTA.'.'.Model::COLUMN_DDIL_ID_DANE_DODATKOWE_LISTA.'
                order by '.Model::COLUMN_DICT_NAZWA, $rowsCount);
            //.' where '.Model::COLUMN_DICT_NAZWA.' in (\''.implode('\',\'', $this->configurationTable).'\');'));
                
            if ($rowsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $rowsCount);
        }
        
        /**
        * @desc Get the list of additional infos for a person
        */
        public function getAdditionalInfo ($candidateId) {
            
            $_candidateId = $this->dal->escapeInt($candidateId);
            
            $query = 'select '.Model::COLUMN_DICT_ID.', '.Model::COLUMN_DDA_ID_OSOBA.', 
            '.Model::COLUMN_DDA_ID_DANE_DODATKOWE_LISTA.' as '.Model::COLUMN_DDO_ID_DANE_DODATKOWE_LISTA.',  '.Model::COLUMN_DDA_WARTOSC.'
            from '.Model::TABLE_DANE_DODATKOWE_ANKIETA.' where '.Model::COLUMN_DDA_ID_OSOBA.' = '.$_candidateId;
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        /**
        * @desc Get the list of additional infos for a person and info id
        */
        public function getAdditionalInfoById ($candidateId, $infoId) {
            
            $_candidateId = $this->dal->escapeInt($candidateId);
            $_infoId = $this->dal->escapeInt($infoId);
            
            $query = 'select * from '.Model::TABLE_DANE_DODATKOWE_ANKIETA.' where '.Model::COLUMN_DDA_ID_OSOBA.' = '.$_candidateId.' and '.Model::COLUMN_DDA_ID_DANE_DODATKOWE_LISTA.' = '.$_infoId;
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        /**
        * @desc Set additional information for a candidate for information id. Potential value discovery/validation should be based on bll logic.
        */
        public function setAdditionalInfo ($candidateId, $infoIndex, $value) {
            
            $_candidateId = $this->dal->escapeInt($candidateId);
            $_infoIndex = $this->dal->escapeInt($infoIndex);
            $_value = $this->dal->escapeString($value);
            
            $query = 'select setAdditionalRegistrationInfoById as success 
            from setAdditionalRegistrationInfoById('.$_candidateId.', '.$_infoIndex.', \''.$_value.'\');';
            
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
        public function deleteAdditionalInfo ($candidateId, $infoIndex) {
            
            $_candidateId = $this->dal->escapeInt($candidateId);
            $_infoIndex = $this->dal->escapeInt($infoIndex);
            
            $query = 'delete from '.Model::TABLE_DANE_DODATKOWE_ANKIETA.' where '.Model::COLUMN_DDA_ID_OSOBA.' = '.$_candidateId.' and '.Model::COLUMN_DDO_ID_DANE_DODATKOWE_LISTA.' = '.$_infoIndex;
            
            return $this->dal->pgQuery($query);
        }
        
        /**
        * @desc get person meta data
        * @param int id of a person
        */
        public function getMetaData ($personId) {
            
            $_personId = $this->dal->escapeInt($personId);
            
            $query = 'select * from '.Model::TABLE_METADANE_INTERNETOWE.' where '.Model::COLUMN_MDI_ID_OSOBA.' = '.$_personId;
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            $dane = array_shift($result);
            $dane[Model::COLUMN_MDI_DANE] = unserialize($dane[Model::COLUMN_MDI_DANE]);
                
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
            
            $dane = $result ? $result[Model::RESULT_FIELD_DATA][Model::COLUMN_MDI_DANE] : array();
            
            $dane = array_merge($dane, $metadata);
            
            $_dbMetadata = $this->dal->escapeString(serialize($dane));
            
            if ($result) {
                
                //update
                $query = 'update '.Model::TABLE_METADANE_INTERNETOWE.' set '.Model::COLUMN_MDI_DANE.' = \''.$_dbMetadata.'\' where '.Model::COLUMN_MDI_ID_OSOBA.' = '.$_personId;
            } else {
                
                $query = 'insert into '.Model::TABLE_METADANE_INTERNETOWE.' ('.Model::COLUMN_MDI_ID_OSOBA.', '.Model::COLUMN_MDI_DANE.') values ('.$_personId.', \''.$_dbMetadata.'\');';
            }
            
            return $this->dal->pgQuery($query);
        }
    }