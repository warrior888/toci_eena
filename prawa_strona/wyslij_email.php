<?php
    require_once '../conf.php';
    CommonUtils::outputBufferingOn();
    CommonUtils::SessionStart();

    echo '<html>';
    HelpersUI::addHtmlBasicHeaders(HelpersUI::PATH_LEVEL_1);
    echo '<body>';
    
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
        $controls = new valControl();
	    include_once('../bll/mail.php');
        
        $id_osoba = Utils::PodajIdOsoba();
        
	    echo "<form method = \"POST\" action = \"".$_SERVER['PHP_SELF']."\">";
        echo $controls->AddHidden(ID_OSOBA, ID_OSOBA, $id_osoba);
	    $database = pg_connect($con_str);
	    $zapytanie = "select nazwa from email where id = '".$id_osoba."';";
        $wynik = pg_query($database, $zapytanie);
	    if (pg_num_rows($wynik) > 0)
	    {
        	    echo("<table align = \"CENTER\"><tr><td>");
        	    echo "</td><td>Wpisz temat maila:</td><td>";
                echo $controls->AddTextbox('temat', 'id_temat', '', 20, 20, '');
        	    echo "</td></tr></table><table align = \"CENTER\"><tr><td>
                <textarea wrap=\"ON\" name = \"tekst\" rows = \"10\" cols = \"80\" class = \"formfield\"></textarea></td></tr></table>";
        	    echo("<table align = \"CENTER\"><tr><td>");
                echo $controls->AddSubmit('wyslij_email', 'id_wyslij_email', 'Wyslij', '');
        	    echo("</td></tr></table></form>");
        	    if (isset($_POST['wyslij_email']))
        	    {
                    $mail = new MailSend();
			        
                    $zapytanie = "select nazwa from email where id = '".$id_osoba."';";
                    $wynik = pg_query($database, $zapytanie);
                    $w = pg_fetch_array($wynik);
                    $mail->DodajOdbiorca($w['nazwa']);

			        //$mail->WordWrap = 50;
			        if(!$mail->WyslijMail($_POST['temat'], $_POST['tekst']))
			        {
				        echo "<center>Wiadomo¶ci nie wys³no. Skontaktuj siê z administratorem.</center><br><div align = 'CENTER'>";
				        echo $controls->AddSubmit('Zamknij', 'id_Zamknij', 'Zamknij', JsEvents::ONCLICK.'="window.close();"');
                        echo "</div>"; 
			        } 
			        else 
			        {
				        echo "<center>Wiadomo¶æ wys³ano pomy¶lnie.</center><br><div align = 'CENTER'>";
                        echo $controls->AddSubmit('Zamknij', 'id_Zamknij', 'Zamknij', JsEvents::ONCLICK.'="window.close();"');
                        echo "</div>";
			        }
        	    }
	    }
	    else
	    {
		    echo("<table align = 'CENTER'><tr><td>Osoba nie posiada adresu e-mail</td></tr></table>");
		    echo "<table align = 'CENTER'><tr><td>";
            echo $controls->AddSubmit('Zamknij', 'id_Zamknij', 'Zamknij', JsEvents::ONCLICK.'="window.close();"');
            echo "</td></tr></table>";
	    }
    }
    CommonUtils::sendOutputBuffer();
?>
</html>