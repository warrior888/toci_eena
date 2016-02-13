<?php


    class DALRemoteSourcesStats extends Model {
        
        /**
        @desc Table create for remote sources
        
            create table zrodla_danych_zdalne (
                
                id serial primary key,
                zrodlo text, --zrodlo pochodzenia, moze wystepowac wielokrotnie, ale wraz z kolumna pole musi byc unikatowe
                pole text, --typ przechowywanej danej
                wartosc text --aka blob, any arbitrary value (object serilaization unadviced ;) )
            );
        
            alter table zrodla_danych_zdalne add constraint zrodla_danych_zdalne_zrodlo_pole_key unique(zrodlo, pole);
        */
        
        /**
        * @desc set remote source certain information for certain information type
        */
        public function set ($source, $field, $value) {
            
            $_source = $this->dal->escapeString($source);
            $_field = $this->dal->escapeString($field);
            $_value = $this->dal->escapeString(serialize($value));
            
            $testQuery = 'select '.Model::COLUMN_ZDZ_ID.' from '.Model::TABLE_ZRODLA_DANYCH_ZDALNE.' 
                where '.Model::COLUMN_ZDZ_POLE.' = \''.$_field.'\' and '.Model::COLUMN_ZDZ_ZRODLO.' = \''.$_source.'\'';
                
            $this->dal->PobierzDane($testQuery, $count);
            
            if ($count > 0) {
                
                //update
                $query = 'update '.Model::TABLE_ZRODLA_DANYCH_ZDALNE.' set '.Model::COLUMN_ZDZ_WARTOSC.' = \''.$_value.'\' 
                    where '.Model::COLUMN_ZDZ_POLE.' = \''.$_field.'\' and '.Model::COLUMN_ZDZ_ZRODLO.' = \''.$_source.'\'';
            } else {
            
                $query = 'insert into '.model::TABLE_ZRODLA_DANYCH_ZDALNE.' ('.Model::COLUMN_ZDZ_POLE.', '.Model::COLUMN_ZDZ_ZRODLO.', '.Model::COLUMN_ZDZ_WARTOSC.') 
                    values (\''.$_field.'\', \''.$_source.'\', \''.$_value.'\')';
            } 
            
            return $this->dal->pgQuery($query);
        }
        
        /**
        * @desc get a certain information for remote source
        */
        public function get ($source, $field) {
            
            $_source = $this->dal->escapeString($source);
            $_field = $this->dal->escapeString($field);
            
            $query = 'select '.Model::COLUMN_ZDZ_ID.', '.Model::COLUMN_ZDZ_POLE.', '.Model::COLUMN_ZDZ_WARTOSC.', '.Model::COLUMN_ZDZ_ZRODLO.' 
                from '.Model::TABLE_ZRODLA_DANYCH_ZDALNE.' where '.Model::COLUMN_ZDZ_POLE.' = \''.$_field.'\' and '.Model::COLUMN_ZDZ_ZRODLO.' = \''.$_source.'\'';
                
            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            if ($rowsCount < 1)
                return null;
                
            $dataRow = array_shift($result);
            
            $dataRow[Model::COLUMN_ZDZ_WARTOSC] = unserialize($dataRow[Model::COLUMN_ZDZ_WARTOSC]);
            
            return $this->formatDataOutput($dataRow, $rowsCount);
        }
    }