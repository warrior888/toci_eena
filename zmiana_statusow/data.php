<?php
    class StatusChangeData
    {
        private $sofi;
        private $klient;
        private $imie_nazwisko;
        private $dataUrodzenia;
        private $reader;
        private $tabId;
        
        public function __construct()
        {
            $this->reader = new DbReader();   
        }
        
        public function SetSofi($sofi)
        {
            $this->sofi = $this->StrToNumber($sofi);
        }   
        
        public function GetSofi()
        {
            return $this->sofi;   
        }
        
        public function SetKlient($klient)
        {
            $this->klient = $klient;
        }   
        
        public function GetKlient()
        {
            return $this->klient;   
        }
        
        public function SetImieNazwisko($imie_nazwisko)
        {
            $this->imie_nazwisko = str_replace(',', '', $imie_nazwisko);
        }   
        
        public function GetImieNazwisko()
        {
            return $this->imie_nazwisko;   
        }
        
        public function SetDataUrodzenia($dataUrodzenia)
        {
            $this->dataUrodzenia = $dataUrodzenia;
        }   
        
        public function GetDataUrodzenia()
        {
            return $this->dataUrodzenia;   
        }
        
        public function GetTabId()
        {
            return "(".substr($this->tabId, 0, strlen($this->tabId) - 1).")";
        }
        
        public function ChangeDate($new_date, $office)
        {        
            $result = "";   
            $id = $this->CheckExists();
            
            if ($id != null)
            {
                if ($this->reader->UpdateDate($id, $new_date, $office) == 1)
                {
                    //echo("Zmodyfikowano: ".$this->GetImieNazwisko()." ".$this->GetDataUrodzenia());
                    $result = "Mod";
                }
                else
                {
                    //echo("Bez zmian: ".$this->GetImieNazwisko()." ".$this->GetDataUrodzenia());
                    $result = "NMod";
                }
                $this->tabId .= $id.",";
            }
            else
            {
                //echo("Nie znaleziono w bazie: ".$this->GetImieNazwisko()." ".$this->GetDataUrodzenia());
                $result = "NF";
            }
            
            return $result;
        }
        
        public function ChangeStatus($date, $office)
        {        
            $result = "";   
            $id = $this->CheckExists();
            
            if ($id != null)
            {
                if ($this->reader->UpdateStatus($id, $date, $office) == 1)
                {
                    //echo("Zmodyfikowano: ".$this->GetImieNazwisko()." ".$this->GetDataUrodzenia());
                    $result = "Mod";
                }
                else
                {
                    //echo("Bez zmian: ".$this->GetImieNazwisko()." ".$this->GetDataUrodzenia());
                    $result = "NMod";
                }
                $this->tabId .= $id.",";
            }
            else
            {
                //echo("Nie znaleziono w bazie: ".$this->GetImieNazwisko()." ".$this->GetDataUrodzenia());
                $result = "NF";
            }
            
            return $result;
        }
        
        public function AddWeek($date, $office)
        {            
            $this->reader->AddWeekToDb($this->reader->GetZatrudnienieID($date, $office, $this->GetTabId()), $date);   
        }
        
        public function ToPasive($office)
        {
             $this->reader->ToPasywny($this->reader->GetIdFromDbForStatus($office, $this->GetTabId()));
        }
        
        private function CheckExists()
        {
            $result = "";
            $sofi = $this->GetSofi();
            
            if ($sofi != "")
            {
                $result = $this->reader->CheckInDb($sofi);
            }
            else
            {
                $result = null;
            }
            
            return $result;
        }
        
        private function StrToNumber($text)
        {
            $result = "";
            
            for ($i = 0; $i < strlen($text); $i++)
            {
                if (is_numeric($text[$i]))
                {
                    $result .= $text[$i];
                }
            }
            
            return $result;   
        }
    }
    
    class StatusChangeDataArray
    {
        private $data = array();
        private $counter = 0;
        
        public function AddStatusObj($statusObj)
        {
            $this->data[] = $statusObj;
            $this->counter++;
        }
        
        public function GetStatusObj($count)
        {
            if (($count >= 0) && ($count < $this->counter))
            {
                return $this->data[$count];
            }
            else
            {
                return null;
            }   
        }
        
        public function GetCount()
        {
            return $this->counter;   
        }
        
        public function GetData()
        {
            return $this->data;   
        }
    }
?>