<?php
class PriceList implements IPriceCalc
{
    private $priceCalc;

    public function __construct($ticketType, $aera, $birthDate, $carrierId) {

        $direction = ($ticketType == 'Obustronny' || $ticketType == 'Za³o¿enie dwustronne') ? 2 : 1;
        
        switch($carrierId) {
            case 4: 
                $this->priceCalc = new PriceCalc_Juzwa($direction, $aera, $birthDate);
                break;
            case 5: 
                $this->priceCalc = new PriceCalc_Soltysik($direction, $aera, $birthDate);
                break;
            default:
                throw new Exception("Obliczanie ceny dla przewo¼nika który nie ma zdefiniowanego cennika");
        }
    }
    
    public function getPrice() {
        return $this->priceCalc->getPrice();
    }
}

abstract class PriceCalcAbstr {
    protected $direction;
    protected $aera;
    protected $birthDate;

    public function __construct($direction, $aera, $birthDate) {
        $this->direction = (int)$direction;
        $this->aera = (int)$aera;
        $this->birthDate = $birthDate;
    }
}

interface IPriceCalc {
    public function getPrice();
}    
    
class PriceCalc_Juzwa extends PriceCalcAbstr implements IPriceCalc {
    
    public function getPrice(){
        if($this->aera == 4) {
            $price = 240;
        } 
        if($this->aera == 1) {
            $price = 260;
        } 
        elseif($this->aera == 2) {
            $price = 300;
        } 
        elseif($this->aera == 3) {
            $price = 330;
        }

        return $price;
    }

}

class PriceCalc_Soltysik extends PriceCalcAbstr implements IPriceCalc {
    public function getPrice(){
        //w jedn± stronê
        if($this->direction == 1) {
            if($this->aera == 1) {
                $price = 310;
            } else {
                $price = 260;
            }
        } else { //W dwie strony
            if($this->aera == 1) {
                $price = 420;
            } else {
                $price = 330;
            }
        }

        $price = $this->getDiscounts($price);

        return $price;
    }

    private function getDiscounts($price) {
        if ( strtotime($this->birthDate) >= strtotime('-26 years')) {
            $price *= 0.9;
        }
        return $price;
    }
}