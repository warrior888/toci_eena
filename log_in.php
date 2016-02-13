<?php
        require("naglowek.php");
        require("conf.php");
	    include_once "Spreadsheet/Excel/Writer.php";
	    include "generuj_excel.php";
        include_once('bll/mail.php');
        include("oblicz_date.php");
        
	    require_once 'adl/User.php';
	    require_once 'bll/BLLBalances.php';
        require_once 'api/StartPracaSniper.php';
    	
        if (empty($_SESSION['uzytkownik']))
        {
            require 'logowanie.php';
            if (isset($_POST['wyslij']))
            {
                $user = User::getInstance();

                try {
                    
                    $loggedIn = $user->logIn($_POST['login'], $_POST['pass']);
                } catch (AccountExpiredException $e) {
                    
                    header('Location: main/user_management.php');
                    die();
                }
                
                if (!$loggedIn)
                {
                    die('Lipa');
                }
                else
                {
                    $controls = new valControl();
                    
                    $_SESSION['controls'] = serialize($controls);

			        $dzistime = date('Y-m-d H:i:s');
			        $dzien = date('d');
			        $miesiac = date('m');
			        $rok = date('Y');
			        $wczoraj = cofnij_date($rok, $miesiac, $dzien, 1);
			        
			        $testquery = "select data from historia_wysylek order by data desc limit 1";
                    $database = pg_connect($con_str);
                    if (!$database) {
                        LogManager::log(LOG_ERR, '['.__FILE__.'] Error during connecting to database.');
                    }
			        $wynik = pg_query($database, $testquery);
                    if (!$wynik) {
                        LogManager::log(LOG_ERR, '['.__FILE__.'] Sql error: '. pg_last_error());
                    }
			        $wiersz = pg_fetch_array($wynik);
			        $ost_data = $wiersz['data'];
                    
			        if ($ost_data < $dzis && isset($_POST['start']))
			        {
			            // one time a day action
				        $zrobione = "INSERT into historia_wysylek values ((select id from uprawnienia where nazwa_uzytkownika = 
				        '".$_SESSION['uzytkownik']."'), '".$dzistime."', '".$dzis."');";
				        $res = pg_query($database, $zrobione);
                        if (!$res) {
                            LogManager::log(LOG_ERR, '['.__FILE__.'] Sql error: '. pg_last_error());
                        }
				        $Zapmscbiura = "select id, nazwa from msc_biura";
				        $wynik = pg_query($database, $Zapmscbiura);
                        if (!$wynik) {
                            LogManager::log(LOG_ERR, '['.__FILE__.'] Sql error: '. pg_last_error());
                        }
                        
                        $tagesraportSent = true;
                        $failedEmailList = array();
                        
				        while ($wiersz = pg_fetch_array($wynik))
				        {
					        $zapytanie = "select nazwisko, imie, data_urodzenia, data, imie_nazwisko, problem from raport_dzienny where id_biuro = ".$wiersz['id'].";";
					        $result = pg_query($database, $zapytanie);
                            if (!$result) {
                                LogManager::log(LOG_ERR, '['.__FILE__.'] Sql error: '. pg_last_error());
                            }
					        if (pg_num_rows($result) > 0)
					        {
						        $n = "../Tagesraport".$wiersz['nazwa'].".xls";
						        $xls = new Spreadsheet_Excel_Writer("$n");
						        $sheet = $xls->addWorksheet('lista');
						        $format = $xls->addFormat();
                				        $nag = array("Name", "Surname", "Birth date", "Date notified", "Owner", "Problem");
            					        create_excel (5,$zapytanie,$sheet, $nag);
						        $xls->close();
						        $adres_zw = "select email from tagesraport where id_msc_biura = ".$wiersz['id']." and email like '%eena.pl';";
						        $mail_zw = pg_query($database, $adres_zw);
                                if (!$mail_zw) {
                                    LogManager::log(LOG_ERR, '['.__FILE__.'] Sql error: '. pg_last_error());
                                }
						        $mail_zwrotny = pg_fetch_array($mail_zw);
						        $adresy_mail = "select email from tagesraport where id_msc_biura = ".$wiersz['id'].";";
						        $maile = pg_query($database, $adresy_mail);
                                if (!$maile) {
                                    LogManager::log(LOG_ERR, '['.__FILE__.'] Sql error: '. pg_last_error());
                                }
                                $mail = new MailSend();
                                $mail->DodajZalacznik($n);
						        while ($rowmail = pg_fetch_array($maile))						
						        {
                                    $mail->DodajOdbiorca($rowmail['email']);
                    			}
                                
                                //$mail->DodajOdbiorca('warriorr@poczta.fm');
                                
                                //send only if not in development mode
                                if (!defined('DEVELOPMENT')) {
                                    $result = $mail->WyslijMail('Day report autogenerated by the system.', 'This is the most recent tagesraport generated by the system. Greetings !', $mail_zwrotny['email'], $mail_zwrotny['email']);
                                    
                                    if (!$result) {
                                    
                                        $failedEmailList = array_merge($failedEmailList, $mail->PodajOdbiorcow());
                                    }
                                    
                                } else {
                                    // mock everything is ok
                                    $result = true;
                                }
                                
                                $tagesraportSent = $tagesraportSent && $result; 
					        }
				        }  
                        //update to active and to pasive
			            //linijka z 2 deletem robi synchronizacje dat powrotu z ludzmi, ktorzy w ogole wyjezdzali  iwywala daty ludzi, 
			            //ktorzy nigdy nie wyjechal
                        //5 to wyjezdzajacy
                        
                        //delete from wyodr_msc_powrot where id not in (select distinct id_osoba from zatrudnienie);
                        $queryDeps = "delete from zatrudnienie_odjazd where id_zatrudnienie in 
                        (select id from zatrudnienie where id_decyzja in (".ID_DECYZJA_ZAINTERESOWANY.", ".ID_DECYZJA_APLIKUJACY.") 
                        and data_wyjazdu < '".$dzis."');
                        delete from zatrudnienie where id_decyzja in (".ID_DECYZJA_ZAINTERESOWANY.", ".ID_DECYZJA_APLIKUJACY.") 
                        and data_wyjazdu < '".$dzis."';
                        delete from zatrudnienie where id_status = ".ID_STATUS_PASYWNY." 
                        and data_wyjazdu < '".$dzis."' and data_powrotu > '".$dzis."';
                        update stat set id_status = ".ID_STATUS_AKTYWNY." where
                        id in (select id_osoba from zatrudnienie where data_wyjazdu < '".$dzis."' and
                        data_powrotu >= '".$dzis."' and id_status = ".ID_STATUS_WYJEZDZAJACY.");
                        update stat set id_status = ".ID_STATUS_PASYWNY." where id in
                        (select id_osoba from zatrudnienie where data_powrotu < '".$dzis."' and id_status = ".ID_STATUS_AKTYWNY.");";
                        $queryDeps .= "update zatrudnienie set id_status = ".ID_STATUS_AKTYWNY."
                        where data_wyjazdu < '".$dzis."' and data_powrotu >= '".$dzis."' and id_status = ".ID_STATUS_WYJEZDZAJACY.";";
                        $queryDeps .= "update zatrudnienie set id_status = ".ID_STATUS_PASYWNY."
                        where data_powrotu < '".$dzis."' and id_status = ".ID_STATUS_AKTYWNY.";";

                        $queryDeps .= "DELETE FROM zatrudnienie where data_wyjazdu < '".$dzis."' AND id_status = 3; ";//status Nowy
                        
                        $resDeps = pg_query($database, $queryDeps);
                        if (!$resDeps) {
                            LogManager::log(LOG_ERR, '['.__FILE__.'] Sql error: '. pg_last_error());
                        }
                        if(sizeof($failedEmailList))
                        {
                            die('Wiadomo¶ci tagesraport nie wys³ano.  Lista maili, gdzie nie dotar³a wiadomo¶æ: '.implode(', ', $failedEmailList).'.<br /><a href="pgsql.php">Kontynuuj</a>');
                        }
                        
                        if (!defined('DEVELOPMENT'))
                        {
                            $bllBalances = new BLLBalances();
			                $bllBalances->SendActivesNotification();
			                $bllBalances->SendFormerEmploymentsSummary();
			            
                            //temporary disabled until improve sniper
                            //$sniper = new StartPracaSniper();
                            //$sniper->query();
                        }
			        }
                    
                    header('Location: /pgsql.php');
                    echo '<a href="pgsql.php">Kontynuuj</a>';
                }
            }
        }
        else 
        {
            header('Location: /pgsql.php');
        }
        require("stopka.php");
?>
