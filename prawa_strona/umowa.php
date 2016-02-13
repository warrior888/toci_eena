<?php
    session_start();

    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
        die();
    }
    else
    {
        require '../naglowek.php';
 	    require '../conf.php';
        require_once '../vaElClass.php';
        require_once 'ui/HtmlControls.php';
        $id_osoba = Utils::PodajIdOsoba();
        
        require_once '../prawa_strona/f_image_operations.php';
        require_once '../dal/klient.php';
	    require 'oblicz_wiek.php';
        $date = $dzis;
	    $query = "select * from PodajDaneUmowa(".$id_osoba.");";
	    $controls = unserialize($_SESSION['controls']);
        $wynik = $controls->dalObj->PobierzDane($query, $iloscWierszy);
	    if ($iloscWierszy == 0 || !$wynik[0]['id_panstwo'])
	    {
		    die('Brak osoby lub podstaw do wydania umowy.');
	    }
	    else
	    {   
			 $rowDeal = $wynik[0];
             $idPanstwo = $rowDeal['id_panstwo'];
             
             if (!isset($_GET['decyzja_ksztalt_umowy'])) {
                 
                 $htmlControls = new HtmlControls();
                 
                 echo '<head><meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2"></head>
                 <form method="GET" action="'.$_SERVER['REQUEST_URI'].'">';
                 echo $htmlControls->_AddHidden(ID_OSOBA, ID_OSOBA, $id_osoba);
                 echo $htmlControls->_AddCheckbox('umowa', 'id_umowa', true, '', 'Umowa', '');
                 echo $htmlControls->_AddCheckbox('umowa_blank_page', 'id_umowa_blank_page', false, '', 'Pusta strona za umow±', '');
                 echo $htmlControls->_AddCheckbox('opis_pracy', 'id_opis_pracy', false, '', 'Opis pracy', '');
                 echo $htmlControls->_AddCheckbox('zaswiadczenie', 'id_zaswiadczenie', false, '', 'Za¶wiadczenie o niekaralno¶ci', '');
                 echo $htmlControls->_AddCheckbox('zaswiadczenie_blank_page', 'id_zaswiadczenie_blank_page', false, '', 'Pusta strona za za¶wiadczeniami', '');
                 echo $htmlControls->_AddSubmit(User::PRIV_DRUK_UMOWY, 'decyzja_ksztalt_umowy', 'id_decyzja_ksztalt_umowy', 'Zatwierd¼', '', '');
                 echo '</form>';
             } else {

                 $file = 'umowa_'.$idPanstwo.'.php';
                 
                 $pokazUmowa = isset($_GET['umowa']);
                 $pokazOpis = isset($_GET['opis_pracy']);
                 $umowaBlank = isset($_GET['umowa_blank_page']);
                 $pokazZaswiadczenie = isset($_GET['zaswiadczenie']);
                 $zaswiadczenieBlank = isset($_GET['zaswiadczenie_blank_page']);
                 
                 if (is_file($file))
                    require $file;
                 else
                    die('Szablon umowy nie zostal znaleziony');
             }
        }
        require("../stopka.php");
    }
?>