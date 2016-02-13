<?php
        require("naglowek.php");
	//include("ustaw_sesje.php");
	include_once "Spreadsheet/Excel/Writer.php";
	include "generuj_excel.php";
	include("class.phpmailer.php");
	include("oblicz_date.php");
        if (empty($_SESSION['uzytkownik']))
        {
            require("logowanie.php");
            if (isset($_POST['wyslij']))
            {
                require("conf.php");
                $haslo=md5($_POST['pass']);
                $uzytkownik = addslashes($_POST['login']);
                //echo("$haslo");
                $database = pg_connect($con_str);
                $zapytanie = "SELECT * FROM uprawnienia WHERE nazwa_uzytkownika = '$uzytkownik' AND haslo = '$haslo';";
                $wynik = pg_query($database, $zapytanie);
                if (pg_num_rows($wynik) == 0)
                {
                    echo("Lipa");
                }
                else
                {
                    	$_SESSION['uzytkownik'] = $uzytkownik;
			            $_SESSION['kwerenda'] = 0;
			            $_SESSION['edycja_masowa'] = "";
			            $wiersz=pg_fetch_array ($wynik);
			            $_SESSION['ilosc_rekordow'] = $wiersz['liczba_rekordow'];
			            if ($wiersz['dodawanie_rekordu']){$_SESSION['dodawanie_rekordu'] = 1;}
			            if ($wiersz['dodawanie_kwerendy']){$_SESSION['dodawanie_kwerendy'] = 1;}
			            if ($wiersz['dodawanie_zettla']){$_SESSION['dodawanie_zettla'] = 1;}
			            if ($wiersz['edycja_rekordu']){$_SESSION['edycja_rekordu'] = 1;}
			            if ($wiersz['edycja_grupowa']){$_SESSION['edycja_grupowa'] = 1;}
			            if ($wiersz['kasowanie_rekordu']){$_SESSION['kasowanie_rekordu'] = 1;}
			            if ($wiersz['druk_umowy']){$_SESSION['druk_umowy'] = 1;}
			            if ($wiersz['druk_listy']){$_SESSION['druk_listy'] = 1;}
			            if ($wiersz['druk_rozliczenia']){$_SESSION['druk_rozliczenia'] = 1;}
			            if ($wiersz['druk_ankiety']){$_SESSION['druk_ankiety'] = 1;}
			            if ($wiersz['druk_biletu']){$_SESSION['druk_biletu'] = 1;}
			            if ($wiersz['email']){$_SESSION['email1'] = 1;}
			            if ($wiersz['masowy_email']){$_SESSION['masowy_email'] = 1;}
			            if ($wiersz['masowy_sms']){$_SESSION['masowy_sms'] = 1;}
			            if ($wiersz['zmiana_uprawnien']){$_SESSION['zmiana_uprawnien'] = 1;}
			            //ustaw_sesje();
			            $dzis = date(Y."-".m."-".d);
			            $dzistime = date(Y."-".m."-".d." ".H.":".i.":".s);
			            $dzien = date(d);
			            $miesiac = date(m);
			            $rok = date(Y);
			            $wczoraj = cofnij_date($rok, $miesiac, $dzien, 1);
			            
			            $testquery = "select data from historia_wysylek order by data desc limit 1;";
			            $wynik = pg_query($database, $testquery);
			            $wiersz = pg_fetch_array($wynik);
			            $ost_data = $wiersz['data'];
			            //echo "Ost data:".$wiersz['data']."<br>";
			            //echo "Wczoraj:".$wczoraj."<br>";
			            //echo "Dzis:".$dzis."<br>";

			            if ($ost_data < $dzis)
			            {
				            //action
				            $zrobione = "INSERT into historia_wysylek values ((select id from uprawnienia where nazwa_uzytkownika = 
				            '".$_SESSION['uzytkownik']."'), '".$dzistime."', '".$dzis."');";
				            $res = pg_query($database, $zrobione);
				            $Zapmscbiura = "select id, nazwa from msc_biura";
				            $wynik = pg_query($database, $Zapmscbiura);
				            while ($wiersz = pg_fetch_array($wynik))
				            {
					            $zapytanie = "select nazwisko, imie, data_urodzenia, data, problem
				      	            from raport_dzienny where id_biuro = 
					            ".$wiersz['id'].";";
					            $result = pg_query($database, $zapytanie);
					            //echo $zapytanie;
					            if (pg_num_rows($result) > 0)
					            {
						            $n = "Tagesraport".$wiersz['nazwa'].".xls";
						            $xls =& new Spreadsheet_Excel_Writer("$n");
						            $sheet =& $xls->addWorksheet('lista');
						            $format =& $xls->addFormat();
                				            $nag = array("Name", "Surname", "Birth date", "Date notified", "Problem");
            					            create_excel (5,$zapytanie,$sheet, $nag);
						            $xls->close();
						            $adres_zw = "select email from tagesraport where id_msc_biura = ".$wiersz['id']." and email like '%eena.pl';";
						            $mail_zw = pg_query($database, $adres_zw);
						            $mail_zwrotny = pg_fetch_array($mail_zw);
						            $adresy_mail = "select email from tagesraport where id_msc_biura = ".$wiersz['id'].";";
						            $maile = pg_query($database, $adresy_mail);
						            while ($rowmail = pg_fetch_array($maile))						
						            {
							    	//echo "Robi.<br />";
							            $mail = new PHPMailer();
							            $mail->IsSMTP(); // telling the class to use SMTP
							            $mail->Host = "192.168.1.50"; // SMTP server
							            $mail->From ="".$mail_zwrotny['email']."";
							            $mail->FromName="".$mail_zwrotny['email']."";
							            $mail->Subject = "Day report autogenerated by the system.";
							            $mail->AddAddress("".$rowmail['email']."");
							            $mail->Body = "This is the most recent tagesraport generated by the system. If there seem to be any mistakes or illogical information please notify administrator of the polish database system. \nGreetings !";
							            $mail->WordWrap = 100;
							            $mail->AddAttachment($n);
							            if(!$mail->Send())
							            {
								            echo "Wiadomo¶ci nie wys³ano. Skontaktuj siê z administratorem.<br>";
							            } 
							            else 
							            {
								            echo "Wiadomo¶æ wys³ano pomy¶lnie.<br>";
							            }
                    				            }
					            }
				            }
                            //update to active and to pasive
			    //linijka z 2 deletem robi synchronizacje dat powrotu z ludzmi, ktorzy w ogole wyjezdzali  iwywala daty ludzi, 
			    //ktorzy nigdy nie wyjechal
                //delete from wyodr_msc_powrot where id not in (select distinct id from historia_zatrudnienia);
                            $queryDeps = "delete from historia_zatrudnienia where id_decyzja = (select id from decyzja where nazwa = 'Zainteresowany') 
                            and data_wyjazdu < '".$dzis."';
                            update stat set id_status = (select id from status where nazwa = 'Aktywny') where
                            id = (select id from historia_zatrudnienia where data_wyjazdu < '".$dzis."' and
                            data_powrotu > '".$dzis."' and id = stat.id and id_status = (select id from status where nazwa = 'Wyje¿d¿aj¹cy'));
                            update stat set id_status = (select id from status where nazwa = 'Pasywny') where id =
                            (select id from historia_zatrudnienia where data_powrotu < '".$dzis."' and id = stat.id
                            and id_status = (select id from status where nazwa = 'Aktywny'));";
                            $queryDeps .= "update historia_zatrudnienia set id_status = (select id from status where nazwa = 'Aktywny')
                            where data_wyjazdu < '".$dzis."' and data_powrotu > '".$dzis."' and id_status = (select id from status where nazwa = 'Wyje¿d¿aj¹cy');";
                            $queryDeps .= "update historia_zatrudnienia set id_status = (select id from status where nazwa = 'Pasywny')
                            where data_powrotu < '".$dzis."' and id_status = (select id from status where nazwa = 'Aktywny');";
                            $resDeps = pg_query($database, $queryDeps);
			            }
                        header('Location: pgsql.php');
                }
            }
        }
require("stopka.php");
?>
