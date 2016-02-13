<?php

    set_time_limit(0);
    require_once '../dal.php';
    
    class RanksUpdater {
        
        const MIN_RANK = 1;
        const CEIL_RANK = 8;
        const MAX_RANK = 9;
        
        const TABLE_DANE_DODATKOWE  = 'dane_dodatkowe';
        
        const COLUMN_ID     = 'id';
        const COLUMN_NAZWA  = 'nazwa';
        const COLUMN_RANGA  = 'ranga';
        
        private $peopleCount;
        private $seekTables;
        private $dal;
        
        public function __construct() {
            
            $this->dal = dal::getInstance();
            $peopleCount = $this->dal->PobierzDane('select count(*) as count from dane_osobowe;');
            
            if (isset($peopleCount[0]['count']))
                $this->peopleCount = $peopleCount[0]['count'];
            else
                die('ni ma counta');
            
            $this->seekTables = $this->dal->PobierzDane('select * from tabela_wyszukiwanie;');
            
            //dane_dodatkowe_lista
            //dane_dodatkowe
            //tabela_wyszukiwanie
        }
        
        //update tabela_wyszukiwanie set ranga = 10 where id = 20 and nazwa = 'dane_dodatkowe';
        public function calculateRanks () {
            
            foreach ($this->seekTables as $seekTable) {
                
                $tabela = $seekTable[self::COLUMN_NAZWA];
                if (self::TABLE_DANE_DODATKOWE == $tabela) 
                    continue;
                    
                $query = 'select count(*) as count from '.$tabela.';';
                $tableDataCount = $this->dal->PobierzDane($query);
                
                if (isset($tableDataCount[0]['count']))
                    $dataCount = $tableDataCount[0]['count'];
                else
                    die('ni ma counta dla '.$tabela);
                    
                $rank = ($dataCount / $this->peopleCount) * 10;
                
                if ($rank > self::MAX_RANK)
                    $rank = self::MAX_RANK;
                    
                if ($rank < self::MIN_RANK)
                    $rank = self::MIN_RANK;
                    
                if ($rank == self::MAX_RANK && $this->peopleCount > $dataCount) {
                    
                    $rank = self::CEIL_RANK;
                }
                    
                $rank = (int)floor($rank);
                
                //echo $tabela.': '.$rank.'<br />';
                $this->dal->pgQuery('update tabela_wyszukiwanie set ranga = '.$rank.' where id = '.$seekTable[self::COLUMN_ID]);
            }
        }
    }
    
    $rank = new RanksUpdater();
    $rank->calculateRanks();