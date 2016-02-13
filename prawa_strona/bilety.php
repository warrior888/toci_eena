<?php
    
    require_once '../conf.php';
    require_once 'adl/Person.php';
    require_once 'bll/HtmlToPdfManager.php';
    require_once 'dal/DALZatrudnienie.php';
    require_once './bilety_common.php';

    class TicketsView extends View {
        
        protected $dal;
        private $data;

        private $errorMessage;
        
        private $allowedCarriers = array(4, 5);
                        
        public static function instantiate () {
            return new TicketsView();
        }
        
        public function __construct () {
            parent::__construct();
        }
        
        public function run() {
            $zatrudnienieId = (int)$_GET['id'];
        
            $this->dal = new DALZatrudnienie();
            $this->data = $this->dal->getTicketData($zatrudnienieId);

            if(! $this->isDataCorrect()){
                echo $this->errorMessage;
                return;
            }

            $this->data = $this->convertArrayEncoding($this->data['data'][0]);

            $this->showTicket($this->data['id_przewoznik']);
        }

        private function isDataCorrect() {
            if($this->data['rowsCount'] == 0) {
                $this->errorMessage = "Brak danych!";
                return false;
            }

            $data = $this->data['data'][0];

            if(!isset($data['pass_nr']) OR $data['pass_nr'] == null) {
                $this->errorMessage = "Proszê wprowadziæ dane dokumentu to¿samo¶ci.";
                return false;
            }

            if(!isset($data['miejsce_docelowe']) OR $data['miejsce_docelowe'] == ''){
                $this->errorMessage = "Proszê wprowadziæ miejsce docelowe.";
                return false;
            }

            if(!isset($data['miasto_docelowe']) OR $data['miasto_docelowe'] == ''){
                $this->errorMessage = "Proszê wprowadziæ wprowadziæ miasto do miejsca docelowego.";
                return false;
            }

            if(!isset($data['przewoznik']) OR $data['przewoznik'] == ''){
                $this->errorMessage = "Proszê wprowadziæ przewoznika.";
                return false;
            }

            if(!isset($data['forma_platnosci']) OR $data['forma_platnosci'] == ''){
                $this->errorMessage = "Proszê wprowadziæ formê p³atnosci.";
                return false;
            }


            return true;
        }
        
        protected function showTicket($carrierId) {
            if(!in_array($carrierId, $this->allowedCarriers)) {
                echo "Dla tego przewo¼nika bilety nie s± dostêpne.";
                return;
            }
            $folder = "carrier_" . $carrierId . DIRECTORY_SEPARATOR;
            
            $manager = new HtmlToPdfManager();
            $manager->SetFont('dejavusans', '', 9);
            $manager->setHtml($this->get_include_contents($folder . 'bilety_szablon_page1.php'));
            $manager->AddPage();
            $manager->setHtml($this->get_include_contents($folder . 'bilety_szablon_page2.php'));
            if($this->data['forma_platnosci_id'] == 5) { //za³ozenie jednostronne
                $manager->AddPage();
                $manager->setHtml($this->get_include_contents($folder . 'bilety_szablon_page3.php'));    
            }
            
            $manager->OutputPdf();
        }

        function get_include_contents($filename) {
            $data = $this->fill($this->data);
            if (is_file($filename)) {
                ob_start();
                include $filename;
                return ob_get_clean();
            }
            return false;
        }
        
        private function fill($data) {
            $data['dzien_wyjazdu'] = own_iconv('ISO-8859-2', 'UTF-8', strftime('%A', strtotime($data['data_wyjazdu'])));
            if(strtotime($data['godzina_przyjazdu']) <= strtotime($data['wyjazd_godzina'])) {
                $data['data_przyjazdu'] = date('Y-m-d', strtotime($data['data_wyjazdu']) + 86400 );
            }
            else {
                $data['data_przyjazdu'] = $data['data_wyjazdu'];
            }
            $data['dzien_przyjazdu'] = own_iconv('ISO-8859-2', 'UTF-8', strftime('%A', strtotime($data['data_przyjazdu'])));            
            
            $priceList = new PriceList($data['bilet'], $data['strefa_id'], $data['data_urodzenia'], $data['id_przewoznik']);
            $data['price'] = $priceList->getPrice();
            
            return $data;
        }

        private function convertArrayEncoding(array $data) {
            foreach($data as &$d) {
                $d = own_iconv('ISO-8859-2', 'UTF-8', $d);
            }
            return $data;
        }
    }
    
    
    

    $output = TicketsView::instantiate();
    $output->execute();
    