<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="js/script.js"></script>
<link href="css/layout.css" rel="stylesheet" type="text/css"></head>
</head>
<?php
    session_start();
    if (empty($_SESSION['uzytkownik']))
    {
        require("log_in.php");
    }
    else
    {
        require('naglowek.php');
        require('conf.php');
        include_once('bll/mail.php');
        include_once "vaElClass.php";
        
        $sendLimit = 2;
        
        $controls = new valControl();
	    //require("class.phpmailer.php");
        echo '<form method = "POST" action = "'.$_SERVER['PHP_SELF'].'">';
        echo("<table align = \"CENTER\"><tr><td>");
        echo("</td><td>Wpisz temat maila:</td><td>");
        echo $controls->AddTextbox('temat', 'id_temat', '', '20', '20', '');
        echo ("</td></tr></table><table align = \"CENTER\"><tr><td>");
        echo("<textarea wrap=\"ON\" name = \"tekst\" rows = \"10\" cols = \"130\" class = \"formfield\"></textarea></td></tr></table>");
        echo("<table align = \"CENTER\"><tr><td>");
        echo $controls->AddSubmit('wyslij_e_m', 'id_wyslij_e_m', 'Wyslij', '');
        echo("</td></tr></table>");
        echo("</form>");
        if (isset($_POST['wyslij_e_m']))
        {
     		$database = pg_connect($con_str);
		    $tab = substr(str_replace("|",",",$_SESSION['edycja_masowa']), 1,strlen($_SESSION['edycja_masowa']) - 2);
            
            $zapytanie = "select id, nazwa from email where id in (".$tab.");";
	        $wynik = pg_query($database, $zapytanie);
            $mail = new MailSend();
            $i = 0;
            $personIds = array();
        	while ($w = pg_fetch_array($wynik))
		    {
               	if ($w['nazwa'] != "")
		        {
		            $i++;
					$mail->DodajUkrytyOdbiorca($w['nazwa']);
					$personIds[] = $w['id'];
					
					if ($i % $sendLimit == 0)
					{
    					if(!$mail->WyslijMail($_POST['temat'], $_POST['tekst']))
            		    {
            			    echo "<center>Wiadomości nie wysłano. Skontaktuj się z administratorem. Pominięto: ".implode(', ', $personIds)."</center><br>";
            		    } 
            		    else 
            		    {
            			    echo "<center>Wiadomość wysłano pomyślnie.</center><br>";
            		    }
            		    
            		    $personIds = array();
            		    $mail = new MailSend();
            		    echo "<center>Przetworzono rekordów: ".$i.".</center><br>";
					}
			    }
			    
			    
		    }
            if(!$mail->WyslijMail($_POST['temat'], $_POST['tekst']))
            {
                echo "<center>Wiadomości nie wysłano. Skontaktuj się z administratorem. Pominięto: ".implode(', ', $personIds)."</center><br>";
            } 
		    else 
		    {
			    echo "<center>Wiadomość wysłano pomyślnie.</center><br>";
		    }
        }
        require('stopka.php');
    }
?>
</html>
