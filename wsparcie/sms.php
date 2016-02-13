<?php
    include_once ('dal.php');
        
    class Sms
    {
        //powinna byc const albo jakos przez property zakamuflowana, zeby w obiekcie nie dalo sie zmienic :P
        //protected $adres_url = 'http://api.smsapi.pl/send.do?'; //Old Address
        protected $adres_url = 'http://api.smsapi.pl/sms.do?';
        protected $adres_url_backup = 'http://api2.smsapi.pl/sms.do?';
        protected $username = 'eena';
        protected $password = '781df4529cb1c668d98777ce7abf01d1';        //eena_sms
        protected $encoding = 'iso-8859-2';
        protected $nadawca = 'EenA.pl';
                               
        
        //zlozenie adresu url do wykonania oraz odczyt wyniku
        protected function WyslijSms ($uriParams, $backup = false)
        {            
            $address = $backup ? $this->adres_url_backup : $this->adres_url;
            $url = parse_url($address . $uriParams);
            $host = $url["host"];
            $path = $url["path"] . "?" . $url["query"];
            $timeout = 30;
            $buf = '';

            $fp = fsockopen($host, 80, $errno, $errstr, $timeout);
            if ($fp)
            {
                fputs ($fp, "POST $path HTTP/1.0\nHost: ".$host."\n\n");
                while (!feof($fp))
                {
                  $buf .= fgets($fp, 128);
                }
                fclose($fp);
            } 
            else 
            {
                echo("$errno, $errstr");
                return null;
            }
            if(!preg_match('#HTTP\/\d\.\d 200#', $buf)) {
                $str = "ERROR! Zwrócony wynik:" . $buf;
            } else {
                $pos = strpos($buf, 'OK:');
                $str = substr($buf, $pos);
            }
            
            return $str;
            //analiza $buf
            //echo $buf;
            
            //$res = file_get_contents($adres);
            //echo '<br />'.$res;
        }
        
        protected function AnalizaOdp ($wynik)
        {
            $odp_posz_wysylka = explode(';', $wynik);

            $status = array();
            $remote_id = array();
            $punkty = array();
            //$telefon = array();
            
            foreach ($odp_posz_wysylka as $wysylka)
            {
                $elementy = explode(':', $wysylka);
                $status[] = $elementy[0];
                $remote_id[] = $elementy[1];
                $punkty[] = $elementy[2];
                //TODO wtf ? bylo wykomentowane ?
                $telefon[] = substr($elementy[3], 2);
            }
            
            $rezultat = array('status' => $status, 'remote_id' => $remote_id, 'punkty' => $punkty, 'telefon' => $telefon);
            return $rezultat;
        }
        
        private function Zanotuj ($parsed_data, $wiadomosc, $tab_tel)
        {
            $dal = dal::getInstance();
            
            $status = $parsed_data['status'];
            $remote_id = $parsed_data['remote_id'];
            $punkty = $parsed_data['punkty'];
            //$telefon = $parsed_data['telefon'];
            
            $i = 0;
            while (isset($status[$i]) && isset($tab_tel[$i]['id_dane_osobowe']))
            {
                if (!isset($punkty[$i]))
                    $punkty[$i] = 1;

                $zapytanie = 'insert into wysylka_sms (id_dane_osobowe, tresc, remote_id, telefon, punkty, status, id_konsultant) values 
                ('.$tab_tel[$i]['id_dane_osobowe'].', \''.$wiadomosc.'\', \''.$remote_id[$i].'\', \''.$tab_tel[$i]['telefon'].'\', '.$punkty[$i].', \''.$status[$i].'\', 
                (select id from uprawnienia where nazwa_uzytkownika = \''.$_SESSION['uzytkownik'].'\'));';

                $dal->pgQuery($zapytanie);
                $i++;
            }
        }
        
        /**
        * @desc wysylka sms ow
        * @param array lista telefonow
        * @param string wiadomosc
        * @param string nadawca
        */
        public function MasowySms ($tab_tel, $wiadomosc, $nadawca = null, $index_tel = null) //index tel oznacza index inf o telefonie w tablicy tablic, ewentualnie podac index wiadomosci i oprogramowac dla roznych wiadomosci
        {
            if ($nadawca)
            {
                $this->nadawca = $nadawca;
            }
            if ($index_tel == null)
            {
                $ciag_tel = implode(',', $tab_tel);
            }
            else
            {
                $ciag_tel = '';
                $przecinek = false;
                foreach ($tab_tel as $element)
                {
                    if ($przecinek)
                    {
                        $ciag_tel .= ',';
                    }
                    $przecinek = true;
                    $ciag_tel .= $element[$index_tel];
                }
            }
            $uriParams = 'username='.$this->username.'&password='.$this->password.'&encoding='.$this->encoding.'&from='.urlencode($this->nadawca).'&to='.$ciag_tel.'&message='.urlencode($wiadomosc);
            //echo $adres;
            
            $wynik = $this->WyslijSms($uriParams);
            if (strpos($wynik, 'ERROR') !== false) {   //Try to use backup address
                $wynik = $this->WyslijSms($uriParams, true);
            }
            
            if (strpos($wynik, 'ERROR') !== false)
            {
                echo 'Wyst±pi³ b³±d ! Nie wys³ano sms. <br/><br/><br/>ERROR CODE: ' . $wynik;
                //poszukac cyfry po errorze i interpretowac
            }
            else
            {
                $parsed_data = $this->AnalizaOdp($wynik);
                //zanotowac, tu powinno byc sprawdzenie czy this jest instancja klasy sms, bo wywolywana metoda jest prywatna, wiec sie nie odziedziczy; w odziedziczonej klasie bedzie blad ...
                $this->Zanotuj($parsed_data, $wiadomosc, $tab_tel);
                echo 'Wys³ano pomy¶lnie.';
                return $parsed_data;
            }
        }
    }
?>