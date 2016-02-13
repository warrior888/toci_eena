<?php

    class CvDataLogic {
        
        const QUERY_ANKIETA              = 'select * from ankieta_holandia where id = %d and id_status in (1, 5);';
        const QUERY_ANKIETA_NOWY         = 'select * from ankieta_nowy where id = %d;';
        const QUERY_JEZYKI               = 'select jezyki.nazwa as jezyk, poziomy.nazwa as poziom from znane_jezyki join jezyki on jezyki.id = znane_jezyki.id_jezyk join poziomy on poziomy.id = znane_jezyki.id_poziom where znane_jezyki.id = %d;';
        const QUERY_PRAWO_JAZDY          = 'select prawo_jazdy.nazwa from pos_prawo_jazdy join prawo_jazdy on pos_prawo_jazdy.id_prawka = prawo_jazdy.id where pos_prawo_jazdy.id = %d;';
        const QUERY_POPRZEDNI_PRACODAWCA = 'select poprzedni_pracodawca.nazwa from poprzedni_pracodawca where poprzedni_pracodawca.id = %d;';
        
        const INDEX_JEZYKI               = 'jezyki';
        const INDEX_PRAWO_JAZDY          = 'prawo_jazdy';
        const INDEX_POPRZEDNI_PRACODAWCA = 'poprzedni_pracodawca';
        
        protected $dal;
        
        public function __construct() {
            
            $this->dal = dal::getInstance();
        }
        
        public function getUserData ($userId) {
            
            $dane = $this->dal->PobierzDane(sprintf(self::QUERY_ANKIETA, $userId));
            if (!$dane) {
                
                $dane = $this->dal->PobierzDane(sprintf(self::QUERY_ANKIETA_NOWY, $userId));
            }
            $dane = $dane[0];
            
            $daneJezyki = $this->dal->PobierzDane(sprintf(self::QUERY_JEZYKI, $userId));
            $danePrawoJazdy = $this->dal->PobierzDane(sprintf(self::QUERY_PRAWO_JAZDY, $userId));
            $danePoprzedniPracodawca = $this->dal->PobierzDane(sprintf(self::QUERY_POPRZEDNI_PRACODAWCA, $userId));
            
            $dane[self::INDEX_JEZYKI] = $daneJezyki;
            $dane[self::INDEX_PRAWO_JAZDY] = $danePrawoJazdy;
            $dane[self::INDEX_POPRZEDNI_PRACODAWCA] = $danePoprzedniPracodawca;
            
            return $dane;
        }
    }