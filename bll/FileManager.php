<?php
    
    class FileManager {
        
        const RIGHTS    = 0755;
        
        const DIR_DOCS       = 'dokumenty';
        const DIR_TAXS       = 'jarograf';
        const DIR_TAXS_RAW   = 'jarograf_raw';
        const DIR_WORKSHEETS = 'skoroszyty';
        
        private static $docsPath;
        private static $pitsPath;
        private static $rawPitsPath;
        private static $sheetsPath;
        
        private $dal;
        
        public static $allowedExts = array(
            'jpg' => 'jpg',
            'jpeg' => 'jpeg',
            'png' => 'png',
            'gif' => 'gif',
            'pdf' => 'pdf',
        );
        
        public function __construct()
        {
            $this->dal = dal::getInstance();
        }
        
        public static function getBasicPath ()
        {
            if (defined('DEVELOPMENT') || !isset($_SERVER['PHPRC']))
                $_SERVER['PHPRC'] = ROOT_INSTALL_PATH;
    
            $_SERVER['TMPDIR'] = $_SERVER['PHPRC'];
            return $_SERVER['PHPRC'];
        }
        
        public static function initialize()
        {
            $_SERVER['PHPRC'] = self::getBasicPath();
                
            FileManager::$docsPath =  $_SERVER['PHPRC'].FileManager::DIR_DOCS.'/';
            //TODO FIXME oldstyle paths, not necessary any more (to be verified first)
            if (!is_dir(FileManager::$docsPath))
                FileManager::$docsPath =  $_SERVER['PHPRC'].'/public_html/'.FileManager::DIR_DOCS.'/';
                
            FileManager::$pitsPath =  $_SERVER['PHPRC'].FileManager::DIR_TAXS.'/';
            if (!is_dir(FileManager::$pitsPath))
                FileManager::$pitsPath =  $_SERVER['PHPRC'].'/public_html/'.FileManager::DIR_TAXS.'/';
                
            FileManager::$rawPitsPath =  $_SERVER['PHPRC'].FileManager::DIR_TAXS_RAW.'/';
            FileManager::$sheetsPath =  $_SERVER['PHPRC'].FileManager::DIR_WORKSHEETS.'/';
        }
        
        public function setScanDoc ($osobaId, $tmpName, $fileName, $docKind, $kindId)
        {
            $fileName =  basename($fileName);
            $osobaId = (int)$osobaId;
            $kindId = (int)$kindId;
            
            $target_path = $this->createDocsPath($osobaId);

            //okeslenie poprawnego rozszerzenia
            $odlamki = explode('.', $fileName);
            $extension = strtolower($odlamki[count($odlamki) - 1]);
            
            if (!in_array($extension, self::$allowedExts))
                die('Unsupported extension.');
            
            $name = $docKind.$osobaId.'.'.$extension;
            $target_path .= $name;
            if (move_uploaded_file($tmpName, $target_path))
            {
                fittosize($target_path);
                $zapytanie = 'select id from dokumenty_skan where id_dane_osobowe = '.$osobaId.' and id_lista_dokumenty_skan = '.$kindId.';';
                $wynik = $this->dal->PobierzDane($zapytanie);

                if ($wynik[0]['id'] != null)
                {
                    //update
                    $zapytanie = 'update dokumenty_skan set nazwa_plik = \''.$name.'\' where id_dane_osobowe = '.$osobaId.' and id_lista_dokumenty_skan = '.$kindId.';';
                    $this->dal->pgQuery($zapytanie);
                }
                else
                {
                    $zapytanie = 'insert into dokumenty_skan (id_dane_osobowe, id_lista_dokumenty_skan, nazwa_plik) values ('.$osobaId.', '.$kindId.', \''.$name.'\');';
                    $this->dal->pgQuery($zapytanie);
                }
                
                return array(true,'');
            }
            else
            {
                return array(false, 'Nie udalo sie, błąd przenoszenia pliku.');
            }
        }
        
        public function setTaxDoc ($osobaId, $tmpName, $fileName, $year, $klientId, $auto = false)
        {
            $fileName = basename($fileName);
            $osobaId = (int)$osobaId;
            $klientId = (int)$klientId;
            $year = (int)$year;
            
            $target_path = $this->createTaxsPath($osobaId, $year);
            
            $odlamki = explode('.', $fileName);
            $fileExt = strtolower($odlamki[count($odlamki) - 1]);
            
            if (!in_array($fileExt, self::$allowedExts))
                die('Unsupported extension.');
                
            $ilosc = $this->dal->PobierzDane("select count(plik) as ilosc from jarograf where id = ".$osobaId." and rok = '".$year."';");
            $ktory = $ilosc[0]['ilosc'] + 1;

            $name = "jarograf".$osobaId.$year.$klientId.$ktory.'.'.$fileExt;
            $target_path .= $name;
            $dbName = FileManager::getDeprTaxTarget($osobaId, $year).$name;
            
            if ($auto)
                $result = rename($tmpName, $target_path);
            else
                $result = move_uploaded_file($tmpName, $target_path);
                
            if ($result)
            {
                $this->dal->pgQuery("insert into jarograf values (".$osobaId.", '".$year."', '".$dbName."', '".$klientId."');");
                return true;
            }
            else
            {
                return false;
            }
        }
        
        public function scanDocExists ($osobaId, $scanName)
        {
            $osobaId = (int)$osobaId;
            $target_path = FileManager::getTarget($osobaId).$scanName; 
            
            return  file_exists($target_path);
        }
        
        public function setAnkieta ($osobaId, $pdf)
        {
            $result = $this->createDocsPath($osobaId);
            
            if ($result)
            {
                $target_path = FileManager::getTarget($osobaId).FileManager::getAnkietaName($osobaId);
                if ($this->scanDocExists($osobaId, FileManager::getAnkietaName($osobaId)))
                    unlink($target_path);
                    
                $result = $pdf->Output($target_path);
            }
        }
        
        protected function createDocsPath ($osobaId)
        {
            $target_path = FileManager::getTarget($osobaId);
            if (!is_dir($target_path))
            {
                mkdir($target_path, FileManager::RIGHTS, true);
                if (!is_dir($target_path))
                    return false;
            }
            
            return $target_path;
        }
        
        protected function createTaxsPath ($osobaId, $year)
        {
            $target_path = FileManager::getTaxTarget($osobaId, $year);
            if (!is_dir($target_path))
            {
                mkdir($target_path, FileManager::RIGHTS, true);
                if (!is_dir($target_path))
                    return false;
            }
            
            return $target_path;
        }
        
        public static function getAnkietaName ($osobaId)
        {
            $osobaId = (int)$osobaId;
            return 'ankieta'.$osobaId.'.pdf';
        }
        
        public static function getAnkietaXlsxName ($osobaId)
        {
            $osobaId = (int)$osobaId;
            return 'ankieta'.$osobaId.'.xlsx';
        }
        
        public static function getTarget ($osobaId)
        {
            $osobaId = (int)$osobaId;
            return self::$docsPath.$osobaId.'/';
        }
        
        public static function getDocsPath()
        {
            return self::$docsPath;
        }
        
        public static function getTaxTarget ($osobaId, $year)
        {
            $osobaId = (int)$osobaId;
            $year = (int)$year;
            return self::$pitsPath.$osobaId.'/'.$year.'/';
        }
        
        protected static function getDeprTaxTarget ($osobaId, $year)
        {
            $osobaId = (int)$osobaId;
            $year = (int)$year;
            return '../'.self::DIR_TAXS.'/'.$osobaId.'/'.$year.'/';
        }
        
        public static function getTaxReadTarget ($dbName)
        {
            return self::$pitsPath.str_replace('../'.FileManager::DIR_TAXS, '', $dbName);
        }
        
        public static function taxReadTargetExists ($dbName)
        {
            return is_file(self::getTaxReadTarget($dbName));
        }
        
        public static function getRawTaxDirPath ($city)
        {
            $city = basename($city);
            $path = self::$rawPitsPath.$city;
            
            if (is_dir($path))
                return $path;
                
            return false;
        }
        
        public static function getWorkSheetPath ($workSheet) {
            
            if (!is_dir(self::$sheetsPath))
                mkdir(self::$sheetsPath);
                
            $workSheet = basename($workSheet);
            $path = self::$sheetsPath.$workSheet;
            
            if (is_dir(self::$sheetsPath))
                return $path;
                
            return false;
        }
    }
    
    FileManager::initialize();
?>