<?php
    require_once '../conf.php';
    CommonUtils::outputBufferingOn();
    CommonUtils::SessionStart();

    echo '<html>';
    HelpersUI::addHtmlBasicHeaders(HelpersUI::PATH_LEVEL_1);
    echo '<body>';
    
    include_once ('../dal.php');
    include_once ('../wsparcie/sms.php');
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
        $controls = new valControl();
        $id_osoba = Utils::PodajIdOsoba();
	    if (isset($_POST['wyslij_sms']))
	    {
            $dal = dal::getInstance();
            //$_POST['tekst_sms'] = str_replace("&","%26",$_POST['tekst_sms']);
            $zapytanie = 'select id as id_dane_osobowe, nazwa as telefon from telefon_kom where id  = '.$id_osoba.';';
            $wynik = $dal->PobierzDane($zapytanie, $ilosc_wierszy);
            $sms = new Sms();
            $sms->MasowySms($wynik, $_POST['tekst_sms'], null, 'telefon');
	    }
	    else
	    {
		    echo "<form method = \"POST\" action = \"".$_SERVER['PHP_SELF']."\">";
            echo $controls->AddHidden(ID_OSOBA, ID_OSOBA, $id_osoba); 
		    $database = pg_connect($con_str);
		    $zapytanie = "select nazwa from telefon_kom where id = '".$id_osoba."';";
        	$wynik = pg_query($database, $zapytanie);
            
		    if (pg_num_rows($wynik) > 0)
		    {
			    //wysylamy sms, tersc itd
			    $_SESSION['licznik_sms'] = 0;
	        	    //echo("<form method = \"POST\" action = \"".$_SERVER['PHP_SELF']."\">");
	        	    echo "<table align = \"CENTER\"><tr><td>";
		           
        		    echo "<textarea wrap=\"ON\" name = \"tekst_sms\" rows = \"5\" cols = \"50\" class = \"formfield\" onkeypress='return DlugoscSms(this, event);' onblur='CutTooLong(this);'></textarea></td></tr></table>";
		            echo "<table align = \"CENTER\"><tr><td>";
                    echo $controls->AddSubmit('wyslij_sms', 'id_wyslij_sms', 'Wyslij', '');
                    echo "</td></tr></table>";
		    }
		    else
		    {
			    echo("Osoba nie posiada telefonu komórkowego.");
			    echo "<table align = 'CENTER'><tr><td>";
                echo $controls->AddSubmit('Zamknij', 'id_Zamknij', 'Zamknij', JsEvents::ONCLICK.'="window.close();"');
                echo "</td></tr></table>";
		    }
		    echo "</form>";
	    }
    }
    CommonUtils::sendOutputBuffer();
?>
</html>
