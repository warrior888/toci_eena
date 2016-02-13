<?php
    require_once 'bll/BLLDaneDodatkowe.php';
    
    class BaseTab
    {
        protected $baseDef = array("nazwa");
        protected $baseName;// = "branza";
        protected $baseId = "id";
        protected $baseShowName;// = array("Bran¿a");
        protected $baseFieldLength;// = array("150");
        public function Def()
        {
            return $this->baseDef;
        }
        public function Name()
        {
            return $this->baseName;
        }
        public function Id()
        {
            return $this->baseId;
        }
        public function ShowName()
        {
            return $this->baseShowName;
        }
        public function FieldLength()
        {
            return $this->baseFieldLength;
        }
    }
    class BranzaTab extends BaseTab
    {
        public function __construct()
        {
            $this->baseName = "branza";
            $this->baseShowName = array("Bran¿a");
            $this->baseFieldLength = array("150");
        }
    }
    class ImieTab extends BaseTab
    {
        public function __construct()
        {
            $this->baseName = "imiona";
            $this->baseShowName = array("Imiê");
            $this->baseFieldLength = array("20");
        }
    }
    class MiejscowoscTab extends BaseTab
    {
        public function __construct()
        {
            $this->baseName = "miejscowosc";
            $this->baseShowName = array("Miejscowo¶æ");
            $this->baseFieldLength = array("30");
        }
    }
    class ZrodloTab extends BaseTab
    {
        public function __construct()
        {
            $this->baseName = "zrodlo";
            $this->baseShowName = array("¼ród³o");
            $this->baseFieldLength = array("35");
        }
    }
    class BankTab extends BaseTab
    {
        public function __construct()
        {
            $this->baseName = "bank";
            $this->baseShowName = array("Bank");
            $this->baseFieldLength = array("30");
        }
    }
    class ZawodTab extends BaseTab
    {
        const TABLE_NAME  = 'zawod';
        
        const COLUMN_ID    = 'id';
        const COLUMN_ZAWOD = 'nazwa';
        
        public function __construct()
        {
            $this->baseName = "zawod";
            $this->baseShowName = array("Zawód");
            $this->baseFieldLength = array("100");
        }
    }
    class JezykTab extends BaseTab
    {
        public function __construct()
        {
            $this->baseName = "jezyki";
            $this->baseShowName = array("Jêzyk");
            $this->baseFieldLength = array("12");
        }
    }
    class MscBiuroTab extends BaseTab
    {
        public function __construct()
        {
            $this->baseName = "msc_biura";
            $this->baseShowName = array("Miejscowo¶æ");
            $this->baseFieldLength = array("15");
        }
    }
    class AdresBiuroTab extends BaseTab
    {
        public function __construct()
        {
            $this->baseName = "adresy_obs_biur";
            $this->baseShowName = array("Adres biura");
            $this->baseFieldLength = array("80");
        }
    }
    class MscOdjazduTab extends BaseTab
    {
        public function __construct()
        {
            $this->baseName = "msc_odjazdu";
            $this->baseShowName = array("Miejscowo¶æ odjazdu");
            $this->baseFieldLength = array("25");
        }
    }
    class RodzajKorTab extends BaseTab
    {
        public function __construct()
        {
            $this->baseName = "rodzaj_korespondencji";
            $this->baseShowName = array("Rodzaj korespondencji");
            $this->baseFieldLength = array("20");
        }
    }
    class FirmaTab extends BaseTab
    {
        public function __construct()
        {
            $this->baseName = "firma";
            $this->baseShowName = array("Firma");
            $this->baseFieldLength = array("100");
        }
    }
    class PanstwoTab extends BaseTab
    {
        public function __construct()
        {
            $this->baseName = "panstwo";
            $this->baseShowName = array("Pañstwo");
            $this->baseFieldLength = array("25");
        }
    }     
    class ListaDokTab extends BaseTab
    {
        public function __construct()
        {
            $this->baseName = "lista_dokumenty_skan";
            $this->baseShowName = array("Nazwa wprowadzanego dokumentu (¿adnych spacji i polskich znaków)");
            $this->baseFieldLength = array("25");
        }
    }
    class AvDicts
    {
        //private static $AvDicts = array("imie","branza");
        
        public static function Dictionaries($name)
        {
            switch($name)
            {
                case "imie":
                return new ImieTab();
                break;
                case "branza":
                return new BranzaTab();
                break;
                case "miejscowosc":
                return new MiejscowoscTab();
                break;
                case "zrodlo":
                return new ZrodloTab();
                break;
                case "bank":
                return new BankTab();
                break;
                case "jezyk":
                return new JezykTab();
                break;
                case "msc_biura":
                return new MscBiuroTab();
                break;
                case "adresy_obs_biur":
                return new AdresBiuroTab();
                break;
                case "msc_odjazdu":
                return new MscOdjazduTab();
                break;
                case "rodzaj_korespondencji":
                return new RodzajKorTab();
                break;
                case "firma":
                return new FirmaTab();
                break;
                case "panstwo":
                return new PanstwoTab();
                break;
                case "lista_dokumenty_skan":
                return new ListaDokTab();
                break;
		        case "umiejetnosc":
                return new DodatkoweUmiejetnosciTab();
            }
            
        }
        //do wykonania listy buttonow formularzy wprowadzania slownikow
        public static function GetDicts()
        {
            return array("imie", "branza", "miejscowosc", "zrodlo", "bank", "jezyk", "msc_biura", "adresy_obs_biur", "msc_odjazdu", "rodzaj_korespondencji", "firma", "panstwo", "umiejetnosc", 'lista_dokumenty_skan');
        }
        //do formatki formsow jest potrzebne do wyswietlenia na buttonie co ponizej
        public static function GetSubNames()
        {
            return array("imiê", "bran¿ê", "miejscowo¶æ", "¼ród³o", "bank", "jêzyk", "miejscowo¶æ biura", "adres obs³ugiwanego biura", "miejscowo¶æ odjazdu", "rodzaj korespondencji", "firmê", "pañstwo", "umiejêtno¶æ", "nazwê dokumentu skanowanego");
        }
    }
    
    
    class ComplexTab
    {
        protected $baseDef;// = array("nazwa");
        protected $baseName;// = "branza";
        protected $baseId;// = "id";
        protected $baseShowName;// = array("Bran¿a");
        protected $baseFieldLength;// = array("150");
        public function Def()
        {
            return $this->baseDef;
        }
        public function Name()
        {
            return $this->baseName;
        }
        public function Id()
        {
            return $this->baseId;
        }
        public function ShowName()
        {
            return $this->baseShowName;
        }
        public function FieldLength()
        {
            return $this->baseFieldLength;
        }
    }
    class PoprzedniPracTab extends ComplexTab
    {
        const TABLE_NAME = 'poprzedni_pracodawca';
        
        const COLUMN_NAZWA             = 'nazwa';
        const COLUMN_ID                = 'id_wiersz';
        const COLUMN_ID_DANE_OSOBOWE   = 'id';
        const COLUMN_ID_GRUPA_ZAWODOWA = 'id_grupa_zawodowa';
        
        
        public function __construct()
        {
            $this->baseName = "poprzedni_pracodawca";
            $this->baseDef = array("nazwa", "id_branza", "id_grupa_zawodowa");
            $this->baseId = "id_wiersz";
            //bez sensu ...
            $this->baseShowName = array("Poprzedni pracodawca", "Wykonywany zawód", "Agencja");
            $this->baseFieldLength = array("130", "100", "100");
        }
    }
    
    class PoprzedniPracodawca
    {
        //public $Index;
        public $EmpName;
        public $BranchName;
        public $OccName;
        public $BranchId;
        public $OccId;
        public $AgencyName;
    }
    class PoprzedniPracodawcaCollection
    {
        protected $PopPracCollection = array();
        protected $counter = 0;
        protected $hasExperience;
        
        public function hasExperience($hasExperience)
        {
            $this->hasExperience = $hasExperience;
            if (false === $hasExperience)
            {
                $this->PopPracCollection = array();
                $this->counter = 0;
            }
        }
        
        public function isExperienced()
        {
            return $this->hasExperience;
        }
        
        public function AddFormerEmp(PoprzedniPracodawca $popPracObj)
        {
            $this->PopPracCollection[$this->counter] = $popPracObj;
            $this->counter++;
            $this->hasExperience = true;
        }
        public function UpdateFormerEmpByIndex($popPracObj, $index)
        {
            $this->PopPracCollection[$index] = $popPracObj;
        }
        public function RemoveFormerEmpByIndex($index)
        {
            $this->counter--;
            if ($index == $this->counter)
            {
                unset($this->PopPracCollection[$this->counter]);
            }
            else
            {
                $this->PopPracCollection[$index] = $this->PopPracCollection[$this->counter];
                unset($this->PopPracCollection[$this->counter]);
            }
        }
        public function GetCollection()
        {
            return $this->PopPracCollection;
        }
        
        public function saveToDb ($dalObj, $seqNumber)
        {
            if ($this->hasExperience !== null) {
                
                $bllDaneInternet = new BLLDaneDodatkowe(true);
                
                if ($this->hasExperience === true)
                    $bllDaneInternet->setAdditionalInfoRow($seqNumber, $bllDaneInternet->getAdditionalInfoId(BLLDaneDodatkowe::HAS_EMP_HISTORY), true);
                if ($this->hasExperience === false)
                    $bllDaneInternet->setAdditionalInfoRow($seqNumber, $bllDaneInternet->getAdditionalInfoId(BLLDaneDodatkowe::HAS_EMP_HISTORY), false);
            
                if ($this->counter > 0)
                {
                    $query = '';
                    
                    foreach ($this->PopPracCollection as $popPrac)
                    {
                        $query .= 'insert into poprzedni_pracodawca_ankieta (id, nazwa, id_grupa_zawodowa, agencja) values ('.$seqNumber.', \''.$popPrac->EmpName.'\', '.$popPrac->OccId.', \''.$popPrac->AgencyName.'\');';
                    }
                    
                    $dalObj->pgQuery($query);
                }
            }
        }
        
        public function renderInfo ()
        {
            $txt = 'Poprzedni pracodawcy: ';
            if ($this->counter == 0)
                $txt .= 'brak.';
            else
            {
                foreach ($this->PopPracCollection as $popPrac)
                {
                    $txt .= '<br />'.$popPrac->EmpName;
                }
            }
            
            return $txt;
        }
        
	    public function GetCount()
        {
            return $this->counter;
        }
    }
    
    class DodatkoweUmiejetnosci
    {
        public $dodUmId;
        public $dodUm;        
    }
    
    class DodatkoweUmiejetnosciCollection
    {
        protected $DodUmCollection = array();
        protected $counter = 0;
        protected $hasSkills;
        
        public function hasSkills($hasSkills)
        {
            $this->hasSkills = $hasSkills;
            if (false === $hasSkills)
            {
                $this->DodUmCollection = array();
                $this->counter = 0;
            }
        }
        
        public function isSkilled()
        {
            return $this->hasSkills;
        }
        
        public function DodajUmiejetnosc(DodatkoweUmiejetnosci $dodUmObj)
        {
            $this->DodUmCollection[$this->counter] = $dodUmObj;
            $this->counter++;
            $this->hasSkills = true;
        }
        public function UsunUmiejetnosc($index)
        {
            $this->counter--;
            if ($index == $this->counter)
            {
                unset($this->DodUmCollection[$this->counter]);
            }
            else
            {
                $this->DodUmCollection[$index] = $this->DodUmCollection[$this->counter];
                unset($this->DodUmCollection[$this->counter]);
            }
        }
        public function GetCollection()
        {
            return $this->DodUmCollection;
        }
        
        public function saveToDb ($dalObj, $seqNumber)
        {
            if ($this->hasSkills !== null) {
                
                $bllDaneInternet = new BLLDaneDodatkowe(true);
                if ($this->hasSkills === true)
                    $bllDaneInternet->setAdditionalInfoRow($seqNumber, $bllDaneInternet->getAdditionalInfoId(BLLDaneDodatkowe::HAS_SKILLS), true);
                if ($this->hasSkills === false)
                    $bllDaneInternet->setAdditionalInfoRow($seqNumber, $bllDaneInternet->getAdditionalInfoId(BLLDaneDodatkowe::HAS_SKILLS), false);
                    
                if ($this->counter > 0)
                {
                    $query = '';
                    
                    foreach ($this->DodUmCollection as $dodUm)
                    {
                        $query .= 'insert into umiejetnosci_osob_internet (id, id_umiejetnosc) values ('.$seqNumber.', '.$dodUm->dodUmId.');'; 
                    }
                    
                    $dalObj->pgQuery($query);
                }
            }
        }
        
        public function renderInfo ()
        {
            $txt = 'Dodatkowe umiejêtno¶ci: ';
            if ($this->counter == 0)
                $txt .= 'brak.';
            else
            {
                foreach ($this->DodUmCollection as $dodUm)
                {
                    $txt .= '<br />'.$dodUm->dodUm;
                }
            }
            
            return $txt;
        }
        public function GetCount()
        {
            return $this->counter;
        }
    }
    
    class PrawoJazdy
    {
        public $licenseId;
        public $licenseName;        
    }
    
    class PrawoJazdyCollection
    {
        protected $licenseCollection = array();
        protected $counter = 0;
        protected $hasLicense;
        
        public function hasLicense($hasLicense)
        {
            $this->hasLicense = $hasLicense;
            if (false === $hasLicense)
            {
                $this->licenseCollection = array();
                $this->counter = 0;
            }
        }
        
        public function isLicensed()
        {
            return $this->hasLicense;
        }
        
        public function DodajPrawo(PrawoJazdy $licenseObj)
        {
            $this->licenseCollection[$licenseObj->licenseId] = $licenseObj;
            $this->counter++;
            $this->hasLicense = true;
        }
        public function UsunPrawo($licenseId)
        {
            if (isset($this->licenseCollection[$licenseId]))
            {
                $this->counter--;
                unset($this->licenseCollection[$licenseId]);
            }
        }
        public function GetCollection()
        {
            return $this->licenseCollection;
        }
        
        public function renderInfo ()
        {
            $txt = 'Posiadane prawa jazdy: ';
            if ($this->counter == 0)
                $txt .= 'brak.';
            else
            {
                $txt .= '<br />';
                foreach ($this->licenseCollection as $prawko)
                {
                    $txt .= $prawko->licenseName.', ';
                }
            }
            
            return $txt;
        }
        
        public function getIdList ()
        {
            return array_keys($this->licenseCollection);
        }
        
        public function saveToDb ($dalObj, $seqNumber)
        {
            if ($this->hasLicense !== null) {
                
                $bllDaneInternet = new BLLDaneDodatkowe(true);
                if ($this->hasLicense === true)
                    $bllDaneInternet->setAdditionalInfoRow($seqNumber, $bllDaneInternet->getAdditionalInfoId(BLLDaneDodatkowe::HAS_DRIVING_LICENSE), true);
                if ($this->hasLicense === false)
                    $bllDaneInternet->setAdditionalInfoRow($seqNumber, $bllDaneInternet->getAdditionalInfoId(BLLDaneDodatkowe::HAS_DRIVING_LICENSE), false);
                    
                if ($this->counter > 0)
                {
                    $query = '';
                    
                    foreach ($this->licenseCollection as $license)
                    {
                        $query .= 'insert into prawo_jazdy_internet (id, id_prawka) values ('.$seqNumber.', '.$license->licenseId.');'; 
                    }
                    
                    $dalObj->pgQuery($query);
                }
            }
        }
        public function GetCount()
        {
            return $this->counter;
        }
    }
    
    class JezykiObce
    {
        public $languageId;
        public $levelId;
        public $langEntry;        
    }
    
    class JezykiObceCollection
    {
        protected $languageCollection = array();
        protected $counter = 0;
        protected $hasLanguage;
        
        public function hasLanguage($hasLanguage)
        {
            $this->hasLanguage = $hasLanguage;
            if (false === $hasLanguage)
            {
                $this->languageCollection = array();
                $this->counter = 0;
            }
        }
        
        public function isLanguaged()
        {
            return $this->hasLanguage;
        }
        
        public function DodajJezyk(JezykiObce $languageObj)
        {
            $this->languageCollection[$languageObj->languageId] = $languageObj;
            $this->counter++;
            $this->hasLanguage = true;
        }
        public function UsunJezyk($languageId)
        {
            if (isset($this->languageCollection[$languageId]))
            {
                $this->counter--;
                unset($this->languageCollection[$languageId]);
            }
        }
        public function GetCollection()
        {
            return $this->languageCollection;
        }
        
        public function renderInfo ()
        {
            $txt = 'Znane jêzyki obce: ';
            if ($this->counter == 0)
                $txt .= 'brak.';
            else
            {
                foreach ($this->languageCollection as $jezyk)
                {
                    $txt .= '<br />'.$jezyk->langEntry;
                }
            }
            
            return $txt;
        }
        
        public function __toString()
        {
            return $this->renderInfo();
        }
        
        public function getIdList ()
        {
            return array_keys($this->languageCollection);
        }
        
        public function saveToDb ($dalObj, $seqNumber)
        {
            if ($this->hasLanguage !== null)
            {
                $bllDaneInternet = new BLLDaneDodatkowe(true);
                if ($this->hasLanguage === true)
                    $bllDaneInternet->setAdditionalInfoRow($seqNumber, $bllDaneInternet->getAdditionalInfoId(BLLDaneDodatkowe::HAS_FOREIGN_LANGUAGE), true);
                if ($this->hasLanguage === false)
                    $bllDaneInternet->setAdditionalInfoRow($seqNumber, $bllDaneInternet->getAdditionalInfoId(BLLDaneDodatkowe::HAS_FOREIGN_LANGUAGE), false);
                    
                if ($this->counter > 0)
                {
                    $query = '';
                    
                    foreach ($this->languageCollection as $language)
                    {
                        $query .= 'insert into jezyki_internet (id, id_jezyk, id_poziom) values ('.$seqNumber.', '.$language->languageId.', '.$language->levelId.');'; 
                    }
                    
                    $dalObj->pgQuery($query);
                }
            }
        }
        public function GetCount()
        {
            return $this->counter;
        }
    }
    
    class DodatkoweUmiejetnosciTab extends BaseTab
    {
        public function __construct()
        {
            $this->baseName = "umiejetnosc";
            $this->baseShowName = array("Dodatkowe umiejetno¶ci");
            $this->baseFieldLength = array("50");
        }
    }
?>