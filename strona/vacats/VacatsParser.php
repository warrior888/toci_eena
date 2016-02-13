<?php 

    require_once 'IParser.php';
/*
  
 {
    "id": "6926",
    "id_klient": "196",
    "id_oddzial": "230",
    "data_wyjazdu": "2013-12-23",
    "ilosc_tyg": "99",
    "data_wpisu": "2013-04-02",
    "klient": "Soprimat",
    "stanowisko": "PielÄgniarki specjalistki",
    "panstwo": "Belgia",
    "biuro": "Mechelen",
    "oddzial": "PielÄgniarka",
    "opis": "fdsa sdg fshdfhf hfdhfdhdf dfh dfh fh&nbsp; dh dg dh d dgh gd dh dfh dfh fdh dfh fhdf<br>"
  }
 */

    class VacatsParser implements IParser {
        
        public function getDataList($receivedData) {
            
            $data = json_decode($receivedData, true);
            
            return $data;
        }        
    }
    