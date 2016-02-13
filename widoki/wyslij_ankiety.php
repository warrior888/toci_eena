<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
<link href="../css/style.css" rel="stylesheet" type="text/css">
</head>
<?php
    @session_start();
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
        require("../naglowek.php");
	    require("../conf.php");
        require_once '../bll/mail.php';
        require_once '../bll/FileManager.php';

        $database = pg_connect($con_str);
        
        if (isset($_POST['sendQuestionaire']) && isset($_POST['h_lista_osoby_ankieta']))
        {
            if (strlen($_POST['h_lista_osoby_ankieta']) > 0)
            {
                require('ankieta_xlsx.php');

                $email_biuro = "";
                $zapytanie_email = "select nazwa as biuro from msc_biura where lower(nazwa) like lower((select co from widoki_edit where id_widoku = '".$_SESSION['widok']."' and gdzie = 'biuro'));";
                $query_email = pg_query($database, $zapytanie_email);
                if (pg_num_rows($query_email) == 1)
                {
                    $row_email = pg_fetch_array($query_email);
                    $email_biuro = $row_email['biuro']; //zmienna email_biuro to nazwa biura
                    
                    ///wylaczenie dodawania uzytkownika wysylajacego ankiety do grupy odbiorcow - ewentualnie zrobic - ponizsze 3 linie i 57 do komentarza
	                $query_user = "select adres_email from uprawnienia where nazwa_uzytkownika = '".$_SESSION['uzytkownik']."';";
	                $res_user = pg_query($database,$query_user);
	                $row_user = pg_fetch_array($res_user);
                    
                    $tab_adr_email = array();
                    $zapytanie_email = "select email from email_ankieta where id_msc_biura = (select id from msc_biura where nazwa = '".$email_biuro."');";
                    $query_email = pg_query($database, $zapytanie_email);
                    while ($row_email = pg_fetch_array($query_email))
                    {
                        $tab_adr_email[count($tab_adr_email)] = $row_email['email'];
                    }
                    
                    $osoby_id_arr = explode('|', $_POST['h_lista_osoby_ankieta']);
                    unset($osoby_id_arr[count($osoby_id_arr) - 1]);
                    $ile_rekordow = count($osoby_id_arr);
                    
                    $forceSend = isset($_POST['forceSend']) ? ($_POST['forceSend'] == 1) : false;
                    $filePath = AnkietaXlsx::Create($osoby_id_arr, $forceSend);
                    
                    $mail = new MailSend();
                    $mail->DodajOdbiorca($row_user['adres_email'].'@eena.pl');
//                    $mail->DodajOdbiorca('marcin.kozerski@gmail.com');

                    foreach ($tab_adr_email as $adres_email)
                    {
                        $mail->DodajOdbiorca($adres_email);
                    }

                    $mail->DodajZalacznik($filePath);

                    if(!$mail->WyslijMail('Ankiety.', 'Ankiety.', $row_user['adres_email'].'@eena.pl', $row_user['adres_email'].'@eena.pl'))
                    {
                        echo "Wiadomości nie wysłano. Skontaktuj się z administratorem.<br />";
                    } 
                    else 
                    {
                        AnkietaXlsx::SetStatusToSend($osoby_id_arr);
                        echo "Wiadomość wysłano pomyślnie.<br />";
                    }
                    
                    //---- OLD VERSION
                    
                    require('ankieta.php');
                    
                    $tab_id = array();
                    $file = array();
                    $licznik = 1;
                    
                    foreach ($osoby_id_arr as $osoba_id)
                    {
                        $tab_id[$licznik] = $osoba_id;
                        create_ankieta($osoba_id);
		                $dir_string = FileManager::getTarget($osoba_id);
                        $katolog = opendir($dir_string);
                        while ($plik = readdir($katolog))
                        {
                            $file[] = $dir_string.$plik;   
                        }
		                if (($licznik % 5 == 0) || ($licznik == $ile_rekordow))
                        {
                            $mail = new MailSend();
                            $mail->DodajOdbiorca($row_user['adres_email'].'@eena.pl');
//                            $mail->DodajOdbiorca('marcin.kozerski@gmail.com');
                            
                            foreach ($tab_adr_email as $adres_email)
                            {
                                $mail->DodajOdbiorca($adres_email);
                            }

				            for ($i = 0; $i < count($file); $i++)
        	                {
				                $mail->DodajZalacznik($file[$i]);
                            }
			                if(!$mail->WyslijMail('Ankiety.', 'Ankiety.', $row_user['adres_email'].'@eena.pl', $row_user['adres_email'].'@eena.pl'))
			                {
				                echo "Wiadomości nie wysłano (stara wersja). Skontaktuj się z administratorem.<br />";
			                } 
			                else 
			                {
				                echo "Wiadomość wysłano pomyślnie (stara wersja).<br />";
			                } 
                            unset($file);
                        } 
                        $licznik++;
                    }
                    
                    //-----OLD VERSION
                }
                else
                {
                    echo 'Wybrano więcej niż jedno biuro lub nie znaleziono żadnego biura. Nie można wysłać ankiet.';
                }
            }
            else
            {
                echo 'Nie wybrano grupy osób do wytworzenia ankiet.<br />';
            }
        }

        require("../stopka.php");
    }
?>
</html>
