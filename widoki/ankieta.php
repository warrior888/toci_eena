<?php

    require_once '../conf.php';
    require_once '../bll/cv.php';
    require_once '../bll/FileManager.php';
    require_once '../bll/PdfManager.php';
    //require_once 'fpdf.php';
    
    function create_ankieta($id)
    {
        $id_osoba = $id;
	    //define('FPDF_FONTPATH', 'wsparcie/font/');
        $cvDataLogic = new CvDataLogic();
        $dane = $cvDataLogic->getUserData($id_osoba);
        
        $naglowki = array(
            'id' => "Id:", 
            'imie' => "Name:", 
            'nazwisko' => "Surname:", 
            'plec' => "Gender:", 
            'data_urodzenia' => "Birth date:", 
            'msc_ur' => "Birth place:", 
            'msc' => "Place:", 
            'ulica' => "Street:", 
            'kod' => "Postal code:", 
            'wyksztalcenie' => "Education:", 
            'zawod' => "Occupation:", 
            'telefon' => "Telephone:", 
            'tel_kom' => "Cell phone:", 
            'email' => "E-mail:", 
            'paszport' => "Passport number:", 
            'data_waznosci' => "Expiry date:", 
            'sofi' => "Soffinumer:", 
            'bank' => "Bank:", 
            'swift' => "Swift:", 
            'konto' => "Account number", 
            'prawo_jazdy' => "Driving license:", 
            'jezyki' => "Foreign language:", 
            'nr_obuwia' => "Shoes size:", 
            'poprzedni_pracodawca' => "Former employer:", 
            'klient' => "Employer:", 
            'data_wyjazdu' => "Departure date:", 
            'ilosc_tyg' => "Weeks:", 
            'biuro' => "Office:"
        );
        
        $pdfManager = new PdfManager(2);
        
        //$naglowki, $dane
        
        foreach ($naglowki as $naglowek => $text) {
            
            $customDelimiter = '';
            if(isset($dane[$naglowek]) && is_array($dane[$naglowek])) {
                
                $info = array();
                
                foreach ($dane[$naglowek] as $dana) {
                    if (isset($dana['nazwa'])) {
                        $info[] = $dana['nazwa'];
                    } else {
                        $info[] = $dana['jezyk'].' - '.$dana['poziom'];
                    }
                }
                
                if ($naglowek != 'poprzedni_pracodawca') {
                    
                    $customDelimiter = ', ';
                    $dane[$naglowek] = implode(', ', $info);
                } else {
                    
                    $customDelimiter = '/';
                    $dane[$naglowek] = $info;
                }
            }
            
            if(isset($dane[$naglowek])) {
                $pdfManager->addRow(array($text, $dane[$naglowek]), $customDelimiter);
            }
        }
        
        $fileManager = new FileManager();
        $fileManager->setAnkieta($id_osoba, $pdfManager->getPdf());
	}
