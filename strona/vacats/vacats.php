<?php 

    require_once 'VacatsClient.php';
    
    define('VACATS_REQUEST_URL', 'http://eena.21infinity.com/api/vacats.php?');
    
    class Vacats {
        
        private $client;
        
        public function __construct() {
            
            $this->client = new VacatsClient();
        }
        
        public function run () {
            
            $id = null;
        
            if (isset($_GET['vacatId'])) {
                $id = (int)$_GET['vacatId'];
                $id = $id > 0 ? $id : null;
            }
            
            // run view of one or all depending on id
            
            $data = $this->client->query($id);
            
            if (!is_null($id)) {
                
                return $this->viewVacat($data);
            }
            
            return $this->viewVacats($data);
        }
        
        private function viewVacat($vacat) {
            
            var_dump($vacat);
        }
        
        private function viewVacats($vacatsList) {
            
            var_dump($vacatsList);
        }
    }
    
    // use case
    $vacats = new Vacats();
    
    $vacats->run();