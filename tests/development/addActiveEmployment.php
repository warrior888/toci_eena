<?php 

    require_once 'test.setup.php';
    
    require_once 'dal/DALZatrudnienie.php';
    
    class EmploymentTest extends DevelopmentTest {
        
        protected $data = array(
            Model::COLUMN_ZTR_ID_KLIENT        => 61,
            Model::COLUMN_ZTR_ID_ODDZIAL       => 32,
            Model::COLUMN_ZTR_ID_WAKAT         => 1,
            Model::COLUMN_ZTR_ID_STATUS        => ID_STATUS_AKTYWNY,
            Model::COLUMN_ZTR_ILOSC_TYG        => 8,
            Model::COLUMN_ZTR_ID_DECYZJA       => ID_DECYZJA_UMOWIONY,
            Model::COLUMN_ZTR_ID_MSC_ODJAZD    => 37,
            Model::COLUMN_ZTR_ID_BILET         => 1,
            Model::COLUMN_ZTR_ID_PRACOWNIK     => 1,
            Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY => 349, // poniedzialek o 14
        );
        
        public function __construct($personId) {
            
            $this->data[Model::COLUMN_ZTR_ID_OSOBA] = $personId;
        }
        
        public function setUp() {
            
            $this->dal = new DALZatrudnienie();
            
            $this->data[Model::COLUMN_ZTR_DATA_WYJAZDU] = date('Y-m-d', mktime(0, 0, 0, date('m'), date('d') - 2, date('Y')));
            $this->data[Model::COLUMN_ZTR_DATA_POWROTU] = date('Y-m-d', mktime(0, 0, 0, date('m') + 1, date('d'), date('Y')));
        }
        
        public function runTests() {
            
            $this->dal->set($this->data);
        }
    }
    
    if (!isset($argv[1])) {
        
        die ('A do kogo to przypisac ?');
    }
    
    $testObj = new EmploymentTest((int)$argv[1]);
    $testObj->run(); 