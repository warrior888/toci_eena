<?php 

    require_once 'test.setup.php';
    
    require_once 'bll/BLLZatrudnienie.php';
    
    class ApplyPersonTest extends IntegrationTest {
        
        private $bllZatrudnienie;
        private $dalZatrudnienie;
        private $dalWakaty;
        private $sampleVacat = array();
        private $sampleVacatId;
        private $samplePersonId = 9400; // me :)
        private $sampleEmploymentId;
        
        public function setUp () {
            
            $this->msg = __CLASS__;
            $this->dal = dal::getInstance();
            
            // get instances, set any required defaults
            $this->bllZatrudnienie = new BLLZatrudnienie();
            $this->dalZatrudnienie = new DALZatrudnienie();
            $this->dalWakaty = new DALWakaty();
            
            $this->sampleVacat = array(
                Model::COLUMN_WAK_ID_KLIENT        => 1,
                Model::COLUMN_WAK_ID_ODDZIAL       => 2,
                Model::COLUMN_WAK_DATA_WYJAZDU     => date('Y-m-d', time() + 86400),
                Model::COLUMN_WAK_ILOSC_KOBIET     => 1,
                Model::COLUMN_WAK_ILOSC_MEZCZYZN   => 1,
                Model::COLUMN_WAK_ILOSC_TYG        => 8,
                Model::COLUMN_WAK_ID_KONSULTANT    => 1,
                Model::COLUMN_WAK_DATA_WPISU       => date('Y-m-d'),
                Model::COLUMN_WAK_DOKLADNY         => true,
                Model::COLUMN_WAK_WIDOCZNE_WWW     => true,
            );
            
            // set an example vacat to test appliance to
            $this->sampleVacatId = $this->dalWakaty->set($this->sampleVacat);
        }
        
        public function cleanUp () {
            
            // delete all potential mess done by test
            
            $result = $this->dalZatrudnienie->delete($this->sampleEmploymentId);
            $result = $this->dalWakaty->delete($this->sampleVacatId) && $result;
            
            return $result;
        }
        
        public function runTests () {
            
            try {
                $result = $this->apply();
                //$result = $this->testAddDefaults() && $result;

                return $result;
            } catch (Exception $e) {
                echo $this->msg.' Unexpected error: '.$e->getMessage();
                return false;
            }
        }
        
        private function apply() {
            
            $this->sampleEmploymentId = $this->bllZatrudnienie->apply($this->samplePersonId, $this->sampleVacatId);
            
            try {
                $this->sampleEmploymentId = $this->bllZatrudnienie->apply($this->samplePersonId, $this->sampleVacatId);
            } catch (LogicConflictDataException $e) {
                
                var_dump(__FILE__.' Exception: '.$e->getMessage());
            }

            return $this->endSingleTest(true, 'apply');
        }
    }