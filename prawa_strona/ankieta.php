<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="js/script.js"></script>
<link href="css/style.css" rel="stylesheet" type="text/css"></head>
</head>
<?php
require_once("../conf.php");
	class Ankieta extends View
	{
		protected $cvDataLogic;
		
		protected $headers = array
            (
                'id' => "Id:", 
                'imie' => "Imie:", 
                'nazwisko' => "Nazwisko:", 
                'plec' => "Plec:", 
                'data_urodzenia' => "Data urodzenia:", 
                'msc_ur' => "Miejsce urodzenia:", 
                'msc' => "Miejsce zamieszkania:", 
                'ulica' => "Ulica:", 
                'kod' => "Kod pocztowy:",
                'wyksztalcenie' => "Wyksztalcenie:", 
                'zawod' => "Zawod:",  
                'telefon' => "Telefon:", 
                'tel_kom' => "Telefon komorkowy:", 
                'email' => "Email:", 
                'paszport' => "Nr paszoprtu:", 
                'data_waznosci' => "Data ważności:",
                'sofi' => "Sofi:", 
                'bank' => "Bank:", 
                'swift' => "Swift:", 
                'konto' => "Nr konta:",
                'prawo_jazdy' => "Prawo jazdy:", 
                'jezyki' => "Języki obce:", 
                'nr_obuwia' => "Numer obuwia:", 
                'poprzedni_pracodawca' => "Poprzedni pracodawca:", 
                'klient' => "Klient:", 
                'data_wyjazdu' => "Data wyjazdu:", 
                'ilosc_tyg' => "Ilość tygodni:", 
                'biuro' => "Biuro:"
            );
		
		public function __construct()
		{
			$this->cvDataLogic = new CvDataLogic();
			$this->personId = Utils::PodajIdOsoba();
		}
		
		public function run() 
		{
			return $this->renderAnkieta();
		}
		
		protected function renderAnkieta()
		{
			$data = $cvDataLogic->getUserData($this->personId);
			$html = '';
			
			foreach ($this->headers as $header => $text) 
			{
				
			}
			
			return $html;
		}
	}

    session_start();
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
        require("../naglowek.php");
	    require_once("../conf.php");
        require_once '../bll/cv.php';
        
        $id_osoba = Utils::PodajIdOsoba();
        
	    $query = "select id,id_imie,nazwisko from dane_osobowe WHERE id = '".$id_osoba."';";
		$database = pg_connect($con_str);
		$wynik = pg_query($database, $query);
		if (pg_num_rows($wynik) == 0)
		{
			echo "Osoba w chwili obecnej nie znajduje się już w bazie, musiała dopiero co zostać usunięta !?";
		}
		else
		{   
			$cvDataLogic = new CvDataLogic();
            $dane = $cvDataLogic->getUserData($id_osoba);
            
			$naglowki = array
            (
                'id' => "Id:", 
                'imie' => "Imie:", 
                'nazwisko' => "Nazwisko:", 
                'plec' => "Plec:", 
                'data_urodzenia' => "Data urodzenia:", 
                'msc_ur' => "Miejsce urodzenia:", 
                'msc' => "Miejsce zamieszkania:", 
                'ulica' => "Ulica:", 
                'kod' => "Kod pocztowy:",
                'wyksztalcenie' => "Wyksztalcenie:", 
                'zawod' => "Zawod:",  
                'telefon' => "Telefon:", 
                'tel_kom' => "Telefon komorkowy:", 
                'email' => "Email:", 
                'paszport' => "Nr paszoprtu:", 
                'data_waznosci' => "Data ważności:",
                'sofi' => "Sofi:", 
                'bank' => "Bank:", 
                'swift' => "Swift:", 
                'konto' => "Nr konta:",
                'prawo_jazdy' => "Prawo jazdy:", 
                'jezyki' => "Języki obce:", 
                'nr_obuwia' => "Numer obuwia:", 
                'poprzedni_pracodawca' => "Poprzedni pracodawca:", 
                'klient' => "Klient:", 
                'data_wyjazdu' => "Data wyjazdu:", 
                'ilosc_tyg' => "Ilość tygodni:", 
                'biuro' => "Biuro:"
            );
			echo("<body onLoad = 'window.print();'><table align = 'CENTER'>");
            foreach ($naglowki as $naglowek => $text) {
                
                if ('prawo_jazdy' == $naglowek || 'jezyki' == $naglowek || 'poprzedni_pracodawca' == $naglowek) {
                            
                    $info = array();
                    
                    if(is_array($dane[$naglowek]))
                    foreach ($dane[$naglowek] as $dana) {
                        if (isset($dana['nazwa']))
                            $info[] = $dana['nazwa'];
                        else
                            $info[] = $dana['jezyk'].' - '.$dana['poziom'];
                    }
                    
                    $dane[$naglowek] = implode(', <br />', $info);
                }
                
                echo("<tr><td align = 'LEFT' nowrap>".$text."</td><td align = 'RIGHT'>".$dane[$naglowek]."</td></tr>");

            }

			echo '</table></body>';
        }

        require("../stopka.php");
    }
?>
</html>
