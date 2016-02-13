<?php

//TODO a jednak php unit, test integracyjne, klasa z set upem i rozne warianty ...
//tablica idkow do usuniecia dla clean upa
    require_once 'test.setup.php';
    #the test
    require_once 'bll/definicjeKlas.php';
    require_once 'dal/DALDaneInternet.php';
    require_once 'bll/BLLDaneDodatkowe.php';
    require_once 'dal.php';
    
    class RegistrationCollectionsTest extends IntegrationTest {

        protected $newIdsList = array();
        
        public function setUp () {
            
            $this->msg = __CLASS__;
            $this->dal = dal::getInstance();
        }
        
        protected function addNewRecord () {
            
            $dalDaneInternet = new DALDaneInternet();
            
            $newId = $dalDaneInternet->set(
                array(
                    Model::COLUMN_DIN_ID_IMIE               => 1,
                    Model::COLUMN_DIN_NAZWISKO              => 'aaaa',
                    Model::COLUMN_DIN_ID_PLEC               => 1,
                    Model::COLUMN_DIN_DATA_URODZENIA        => '2001-11-01',
                    Model::COLUMN_DIN_ID_MIEJSCOWOSC_UR     => 1, 
                    Model::COLUMN_DIN_ID_MIEJSCOWOSC        => 2, 
                    Model::COLUMN_DIN_ULICA                 => 'bbbbbb', 
                    Model::COLUMN_DIN_KOD                   => '21-123', 
                    Model::COLUMN_DIN_TELEFON               => 322143341, 
                    Model::COLUMN_DIN_KOMORKA               => 123123456, 
                    Model::COLUMN_DIN_EMAIL                 => 'dsafdas@dfsadfe.df', 
                    Model::COLUMN_DIN_ID_WYKSZTALCENIE      => 1, 
                    Model::COLUMN_DIN_ID_ZAWOD              => 1,  
                    Model::COLUMN_DIN_ID_CHARAKTER          => 1,
                    Model::COLUMN_DIN_DATA                  => '2012-12-12',
                    Model::COLUMN_DIN_ILOSC_TYG             => 2,
                    Model::COLUMN_DIN_ID_ZRODLO             => 1
                )
            );
            
            $this->newIdsList[$newId] = $newId;
            
            return $newId;
        }
        
        private function testAddDataAddDefaultAdditionals () {
        
            $newId = $this->addNewRecord();
            
            #region prawo jazdy
            $prawoJazdyCollection = new PrawoJazdyCollection();
            
            $prawoJazdy = new PrawoJazdy();
            $prawoJazdy->licenseId = 1;
            $prawoJazdyCollection->DodajPrawo($prawoJazdy);
            $prawoJazdyCollection->saveToDb($this->dal, $newId);
            
            
            $employmentCollection = new PoprzedniPracodawcaCollection();
            
            $employment = new PoprzedniPracodawca();
            $employment->OccId = 1;
            $employment->BranchName = 'aa';
            $employment->EmpName = 'aaaaa';
            $employment->OccName = 'ddddd';
            
            $employmentCollection->AddFormerEmp($employment);
            $employmentCollection->saveToDb($this->dal, $newId);
            
            
            $skillsCollection = new DodatkoweUmiejetnosciCollection();
            
            $additionalSkill = new DodatkoweUmiejetnosci();
            $additionalSkill->dodUmId = 8;
            
            $skillsCollection->DodajUmiejetnosc($additionalSkill);
            $skillsCollection->saveToDb($this->dal, $newId);
            
            
            $languagesCollection = new JezykiObceCollection();
            
            $lang = new JezykiObce();
            $lang->languageId = 1;
            $lang->levelId = 1;
            
            $languagesCollection->DodajJezyk($lang);
            $languagesCollection->saveToDb($this->dal, $newId);
            
            #region verification
            $bllDaneDodatkowe = new BLLDaneDodatkowe(true);
            $additionalInfo = $bllDaneDodatkowe->getAdditionalInfo($newId, false);
            
            $hasDrLic = $bllDaneDodatkowe->getAdditionalInfoId(BLLDaneDodatkowe::HAS_DRIVING_LICENSE);
            $hasEmpHis = $bllDaneDodatkowe->getAdditionalInfoId(BLLDaneDodatkowe::HAS_EMP_HISTORY);
            $hasSkills = $bllDaneDodatkowe->getAdditionalInfoId(BLLDaneDodatkowe::HAS_SKILLS);
            $hasForeignLanguage = $bllDaneDodatkowe->getAdditionalInfoId(BLLDaneDodatkowe::HAS_FOREIGN_LANGUAGE);
            
            $licenseMatch = false;
            $employmentMatch = false;
            $skillsMatch = false;
            $langsMatch = false;
            
            foreach ($additionalInfo[Model::RESULT_FIELD_DATA] as $addInfo) {
                
                if ($addInfo[Model::COLUMN_DDO_ID_DANE_DODATKOWE_LISTA] == $hasDrLic && 
                $addInfo[Model::COLUMN_DDA_WARTOSC] == BLLDaneDodatkowe::BOOL_TRUE) {
                    
                    $licenseMatch = true;
                }
                
                if ($addInfo[Model::COLUMN_DDO_ID_DANE_DODATKOWE_LISTA] == $hasEmpHis && 
                $addInfo[Model::COLUMN_DDA_WARTOSC] == BLLDaneDodatkowe::BOOL_TRUE) {
                    
                    $employmentMatch = true;
                }
                
                if ($addInfo[Model::COLUMN_DDO_ID_DANE_DODATKOWE_LISTA] == $hasSkills && 
                $addInfo[Model::COLUMN_DDA_WARTOSC] == BLLDaneDodatkowe::BOOL_TRUE) {
                    
                    $skillsMatch = true;
                }
                
                if ($addInfo[Model::COLUMN_DDO_ID_DANE_DODATKOWE_LISTA] == $hasForeignLanguage && 
                $addInfo[Model::COLUMN_DDA_WARTOSC] == BLLDaneDodatkowe::BOOL_TRUE) {
                    
                    $langsMatch = true;
                }
            }
            
            return $this->endSingleTest(($employmentMatch && $licenseMatch && $skillsMatch && $langsMatch), 'testAddDataAddDefaultAdditionals');
        }
        
        private function testAddDefaults($setTrue = true) {
            
            $newId = $this->addNewRecord();
            
            $prawoJazdyCollection = new PrawoJazdyCollection();
            $prawoJazdyCollection->hasLicense($setTrue);
            $prawoJazdyCollection->saveToDb($this->dal, $newId);
            
            $employmentCollection = new PoprzedniPracodawcaCollection();
            $employmentCollection->hasExperience($setTrue);
            $employmentCollection->saveToDb($this->dal, $newId);
            
            $skillsCollection = new DodatkoweUmiejetnosciCollection();
            $skillsCollection->hasSkills($setTrue);
            $skillsCollection->saveToDb($this->dal, $newId);
            
            $langsCollection = new JezykiObceCollection();
            $langsCollection->hasLanguage($setTrue);
            $langsCollection->saveToDb($this->dal, $newId);
            
            $bllDaneDodatkowe = new BLLDaneDodatkowe(true);
            $additionalInfo = $bllDaneDodatkowe->getAdditionalInfo($newId, false);
            
            $hasDrLic = $bllDaneDodatkowe->getAdditionalInfoId(BLLDaneDodatkowe::HAS_DRIVING_LICENSE);
            $hasEmpHis = $bllDaneDodatkowe->getAdditionalInfoId(BLLDaneDodatkowe::HAS_EMP_HISTORY);
            $hasSkills = $bllDaneDodatkowe->getAdditionalInfoId(BLLDaneDodatkowe::HAS_SKILLS);
            $hasForeignLanguage = $bllDaneDodatkowe->getAdditionalInfoId(BLLDaneDodatkowe::HAS_FOREIGN_LANGUAGE);
            
            $licenseMatch = false;
            $employmentMatch = false;
            $skillsMatch = false;
            $langsMatch = false;
            
            $cmpBool = $setTrue ? BLLDaneDodatkowe::BOOL_TRUE : BLLDaneDodatkowe::BOOL_FALSE;
            
            foreach ($additionalInfo[Model::RESULT_FIELD_DATA] as $addInfo) {
                
                if ($addInfo[Model::COLUMN_DDO_ID_DANE_DODATKOWE_LISTA] == $hasDrLic && 
                $addInfo[Model::COLUMN_DDA_WARTOSC] == $cmpBool) {
                    
                    $licenseMatch = true;
                }
                
                if ($addInfo[Model::COLUMN_DDO_ID_DANE_DODATKOWE_LISTA] == $hasEmpHis && 
                $addInfo[Model::COLUMN_DDA_WARTOSC] == $cmpBool) {
                    
                    $employmentMatch = true;
                }
                
                if ($addInfo[Model::COLUMN_DDO_ID_DANE_DODATKOWE_LISTA] == $hasSkills && 
                $addInfo[Model::COLUMN_DDA_WARTOSC] == $cmpBool) {
                    
                    $skillsMatch = true;
                }
                
                if ($addInfo[Model::COLUMN_DDO_ID_DANE_DODATKOWE_LISTA] == $hasForeignLanguage && 
                $addInfo[Model::COLUMN_DDA_WARTOSC] == $cmpBool) {
                    
                    $langsMatch = true;
                }
            }
            
            return $this->endSingleTest(($employmentMatch && $licenseMatch && $skillsMatch && $langsMatch), 'testAddDefaults '.(int)$setTrue);
        }
        
        public function cleanUp () {
            
            #cleanup
            $dalDaneInternet = new DALDaneInternet();
            
            $result = true;
            foreach ($this->newIdsList as $newId) {
                
                $result = $dalDaneInternet->delete($newId) && $result;
            }
            
            return $result;
        }
        
        public function runTests () {
            
            try {
                $result = $this->testAddDataAddDefaultAdditionals();
                $result = $this->testAddDefaults() && $result;
                $result = $this->testAddDefaults(false) && $result;
                
                return $result;
            } catch (Exception $e) {
                echo $this->msg.' Unexpected error: '.$e->getMessage();
                return false;
            }
        }
    }