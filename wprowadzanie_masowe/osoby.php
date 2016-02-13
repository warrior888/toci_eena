<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript1.3" src="../js/script.js"></script>
<link rel="stylesheet" href="../css/styluzup.css">
</head>
<?php
	@session_start();
	require("../conf.php");
	require("../oblicz_date.php");
	$zmienna="../uploads/";
	$zmienna=$zmienna."osoby.txt";
	$dzis = date('Y-m-d');
	//$zmienna2="uploads/";
	//$zmienna2=$zmienna2."kolumna2.txt";
	if($plik=fopen($zmienna,"r"))
	{
		//$plik2=fopen($zmienna2,"r");
		echo "Plik wczytany!<br>";
		$database = pg_connect($con_str);
		//$licz = 0;
		$rrr = 0;
		//while(!feof($plik) && $rrr < 10)
		while(!feof($plik))
		{
			$rrr++;
			//$licz++;
			//$insert="insert into dane_osobowe values (nextval('dane_osobowe_id_seq'),";
			$ciag=addslashes(trim(fgets($plik)));
			//echo $ciag;
			$odlamki = explode(";", $ciag);
			//echo $odlamki[0];
			$j = 0;
			while ($odlamki[$j])
			{
				$odlamki[$j] = trim($odlamki[$j]);
				$j++;
			}
			$licz = 0;
			echo "<br>Wprowadzany: ".$odlamki[$licz+1]." ".$odlamki[$licz+2]." ID: ".$odlamki[$licz]."<br>";
			if ($odlamki[$licz+27] == "malwina"){$odlamki[$licz+27] = "weronika";}
			$query = "select id from uprawnienia where nazwa_uzytkownika = '".$odlamki[$licz+27]."';";
			$result = pg_query($database,$query);
			$row = pg_fetch_array($result);
			$uzytkownik = $row['id'];
			//24 index terminu wyjazdu
			if ($odlamki[$licz+24] == "-")
			{
				$odlamki[$licz+24] = "2005-01-01";
			}
			//19 index wyksztalcenia, tym, ktorzy nic nie podali lub odparowalo damy srednie z automatu
			if ($odlamki[$licz+19] == "-")
			{
				$odlamki[$licz+19] = "Średnie";
			}
			//jesli maja kopnieta date ur to wpisujemy z urzedu date z sufitu ;)
			if (strlen($odlamki[$licz+3]) < 9)
			{
				$odlamki[$licz+3] = "1995-01-01";
			}
			$insert="insert into dane_osobowe values ('".$odlamki[$licz]."',
			(select id from imiona where lower(nazwa) = lower('".$odlamki[$licz+1]."')),'".$odlamki[$licz+2]."',
			(select id from plec where nazwa = '".$odlamki[$licz+5]."'),'".$odlamki[$licz+3]."',
			(select id from miejscowosc where lower(nazwa) = lower('".$odlamki[$licz+4]."')),
			(select id from miejscowosc where lower(nazwa) = lower('".$odlamki[$licz+6]."')),'".$odlamki[$licz+7]."','".$odlamki[$licz+8]."',
			(select id from wyksztalcenie where lower(nazwa) = lower('".$odlamki[$licz+19]."')),
			(select id from zawod where lower(nazwa) = lower('".$odlamki[$licz+20]."')),
			(select id from uprawnienia where nazwa_uzytkownika = '".$odlamki[$licz+27]."'),";
			//30 index daty wprowadzenia do systemu, kazdy musi miec jakas date
			if ($odlamki[$licz+30] != "-")
			{
				$insert=$insert."'".$odlamki[$licz+30]."',";
			}
			else
			{
				$insert=$insert."'2003-01-01',";
			}
			//$insert=$insert."'".$row['id']."',";
			//29 charakter pracy, wiekszosc nie ma charakteru, damy im staly :P
			if ($odlamki[$licz+29] == "-")
			{
				$odlamki[$licz+29] = "Stała";
			}
			$query = "select id from charakter where nazwa = '".$odlamki[$licz+29]."';";
			$result = pg_query($database,$query);
			if (pg_num_rows($result))
			{
				$row = pg_fetch_array($result);
				$insert=$insert."'".$row['id']."','1',";
			}
			else
			{
				$insert=$insert."'1','2',";
			}
			//24 data kont, 25 ilosc tyg
			$insert=$insert."'".$odlamki[$licz+24]."','".$odlamki[$licz+25]."',";
			if ($odlamki[$licz+31] != "-")
			{
				$query = "select id from ankieta where lower(nazwa) = lower('".$odlamki[$licz+31]."');";
			}
			else
			{
				$query = "select id from ankieta where nazwa = 'Telefon';";
			}
			$result = pg_query($database,$query);
			$row = pg_fetch_array($result);
			$insert=$insert."'".$row['id']."',
			(select id from zrodlo where lower(nazwa) = lower('".$odlamki[$licz+32]."')),'".$odlamki[$licz+34]."');";
			//32 zrodlo, 34 buty, 33 umiejetnosci
			$wykonaj = pg_query ($database,$insert);
			//$wykonaj = "ffff";
			if (!$wykonaj)
			{
				echo "<br>Nie powiodl sie: ".$insert."<br>";
			}
			else
			{
				//28 statusy, jak ktos nie ma statusu to niedostepny mu
				if ($odlamki[$licz+37] != "-" && strtolower($odlamki[$licz+28]) == "nowy")
				{
					$odlamki[$licz+28] = "Pasywny";
				}
				if ($odlamki[$licz+37] == "-" && strtolower($odlamki[$licz+28]) == "pasywny")
				{
					$odlamki[$licz+28] = "Nowy";
				}
				if ($odlamki[$licz+28] == "-")
				{
					$odlamki[$licz+28] = "Niedostępny";
				}
				if (strtolower($odlamki[$licz+28]) == strtolower("Czarna owca"))
				{
					$odlamki[$licz+28] = "Nieodpowiedni";
				}
				if (strtolower($odlamki[$licz+28]) == strtolower("Niedostępny"))
				{
					//37 klient
					if ($odlamki[$licz+37] == "-")
					{
						$odlamki[$licz+28] = "Nowy";
					}
					else
					{
						$odlamki[$licz+28] = "Pasywny";
					}	
					$zapytanie_ch = "UPDATE dane_osobowe SET id_charakter = (select id from charakter where nazwa = 'Niedostępny') where id = ".$odlamki[$licz].";";
					$result = pg_query($database, $zapytanie_ch);
				}
				$query = "select id from status where nazwa = '".$odlamki[$licz+28]."';";
				//echo "<br>";
				//echo $query."<br>".$odlamki[$licz+28]."<br>";
				$result = pg_query($database,$query);
				if (!$result)
				{
					echo "<br>Nie powiodl sie: ".$query."<br>";
				}
				$wynkon = pg_fetch_array($result);
				if (!$wynkon)
				{
					$query = "select id from charakter where nazwa = '".$odlamki[$licz+29]."';";
					$result = pg_query($database,$query);				
					if (!$result)
					{
						echo "<br>Nie powiodl sie: ".$query."<br>";
					}
					$row = pg_fetch_array($result);
					$insert2 = "UPDATE dane_osobowe SET id_charakter = '".$row['id']."' where id = ".$odlamki[$licz].";";
					$wykonaj = pg_query ($database,$insert2);
					if (!$wykonaj)
					{
						echo "<br>Nie powiodl sie: ".$insert2."<br>";
					}
				}
				else
				{
					//$query = "select id from status where nazwa = '".$odlamki[27]."';";
					//echo "<br>";
					//echo $query."<br>";
					//$result = pg_query($database,$query);
					//$row = pg_fetch_array($result);
					$insert2 = "INSERT INTO stat VALUES (".$odlamki[$licz].",'".$wynkon['id']."');";
					//echo $insert2;
					$wykonaj = pg_query ($database,$insert2);
					if (!$wykonaj)
					{
						echo "<br>Nie powiodl sie: ".$insert2."<br>";
					}
				}
				if ($odlamki[$licz+9] != "-")
				{
					if (strlen($odlamki[$licz+9]) > 9)
					{
						$query = "INSERT INTO telefon_inny VALUES (".$odlamki[$licz].",'".$odlamki[$licz+9]."');";
					}
					else
					{
						$query = "INSERT INTO telefon VALUES (".$odlamki[$licz].",'".$odlamki[$licz+9]."');";
					}
					$result = pg_query($database,$query);
					if (!$result)
					{
						echo "<br>Nie powiodl sie: ".$query."<br>";
					}
				}
				if ($odlamki[$licz+10] != "-")
				{
					if (strlen($odlamki[$licz+10]) > 9)
					{
						$query = "INSERT INTO telefon_inny VALUES (".$odlamki[$licz].",'".$odlamki[$licz+10]."');";
					}
					else
					{
						$query = "INSERT INTO telefon_kom VALUES (".$odlamki[$licz].",'".$odlamki[$licz+10]."');";
					}
					$result = pg_query($database,$query);
					if (!$result)
					{
						echo "<br>Nie powiodl sie: ".$query."<br>";
					}
				}
				//analiza paszportu (11)
				if ($odlamki[$licz+11] != "-" && strlen($odlamki[$licz+11] > 5))
				{
					//waznosc paszportu (12) jak niepodane ustawiamy na niewazny
					if ($odlamki[$licz+12] == "-")
					{
						$odlamki[$licz+12] = "2005-01-01";
					}
					//$query1 = "select id from bank where nazwa = '".$odlamki[$licz+14]."'";
					//$res = pg_query($database,$query1);
					//if (!$res)
					//{
						//echo "<br>Nie powiodl sie: ".$query1."<br>";
					//}
					//$row = pg_fetch_array($res);
					//14 bank, - tez jest brany pod uwage
					$query = "insert into dokumenty values ('".$odlamki[$licz]."',
					'".$odlamki[$licz+11]."','".$odlamki[$licz+12]."','".$odlamki[$licz+13]."',
					(select id from bank where nazwa = '".$odlamki[$licz+14]."'),
					'".$odlamki[$licz+15]."');";
					$result = pg_query($database,$query);
					if (!$result)
					{
						echo "<br>Nie powiodl sie: ".$query."<br>";
					}
				}
				//16 prawo jazdy
				if ($odlamki[$licz+16] != "-")
				{
					$prawa_jazdy = explode(",",$odlamki[$licz+16]);
					$i = 0;
					$prawa_jazdy[$i] = trim($prawa_jazdy[$i]);
					while ($prawa_jazdy[$i])
					{
						@$prawa_jazdy[$i+1] = trim($prawa_jazdy[$i+1]);
						//$query1 = "select id from prawo_jazdy where nazwa = '".$prawa_jazdy[$i]."';";
						//$res = pg_query($database,$query1);
						//if (!$res)
						//{
							//echo "<br>Nie powiodl sie: ".$query1."<br>";
						//}
						//if (($row = pg_fetch_array($res)) > 0)
						//{
						$query = "insert into pos_prawo_jazdy values (".$odlamki[$licz].",
						(select id from prawo_jazdy where nazwa = '".$prawa_jazdy[$i]."'));";
						$result = pg_query ($database, $query);
						if (!$result)
						{
							echo "<br>Nie powiodl sie: ".$query."<br>";
						}
						//}
						$i++;
					}
				}
				//17 jezyk, 18 poziom
				if ($odlamki[$licz+17] != "-" && $odlamki[$licz+18] != "-")
				{
					$jezyk = explode(",",$odlamki[$licz+17]);
					$poziom = explode(",",$odlamki[$licz+18]);
					$i = 0;
					$jezyk[$i] = trim($jezyk[$i]);
					$poziom[$i] = trim($poziom[$i]);
					while ($jezyk[$i])
					{
						$jezyk[$i+1] = trim($jezyk[$i+1]);
						$poziom[$i+1] = trim($poziom[$i+1]);
						switch($poziom[$i])
						{
							case "Podstawy":
								$poziom[$i] = "Podstawowy";
								break;
							case "Średnio":
								$poziom[$i] = "Średni";
								break;
							case "Dobrze":
								$poziom[$i] = "Dobry";
								break;
							case "B.Dobrze":
								$poziom[$i] = "Bardzo dobry";
								break;
						}
						//$query1 = "select id from jezyki where nazwa = '".$jezyk[$i]."';";
						//$query2 = "select id from poziomy where nazwa = '".$poziom[$i]."';";
						//echo "<br>".$query1."<br>";
						//echo "<br>".$query2."<br>";
						/*$res = pg_query($database,$query1);
						if (!$res)
						{
							echo "<br>Nie powiodl sie: ".$query1."<br>";
						}
						$res2 = pg_query($database,$query2);
						if (!$res2)
						{
							echo "<br>Nie powiodl sie: ".$query2."<br>";
						}
						$row = pg_fetch_array($res);
						$row2 = pg_fetch_array($res2);*/
						//if ($row && $row2)
						//{
						$query = "insert into znane_jezyki values (".$odlamki[$licz].",
						(select id from jezyki where nazwa = '".$jezyk[$i]."'),
						(select id from poziomy where nazwa = '".$poziom[$i]."'));";
						//echo "<br>".$query."<br>";
						$result = pg_query ($database, $query);
						if (!$result)
						{
							echo "<br>Nie powiodl sie: ".$query."<br>";
						}
						//}
						$i++;
					}
				}
				//23 ustalenia, 26 ostatni kontakt
				if ($odlamki[$licz+23] != "-")
				{
					if ($odlamki[$licz+26] == "-")
					{$odlamki[$licz+26] = "2006-01-01";}
					if (strlen($odlamki[$licz+23]) > 149)
					{$odlamki[$licz+23] = substr($odlamki[$licz+23],0,149);}
					$query = "insert into semantyka values (".$odlamki[$licz].",'".$odlamki[$licz+26]."','".$uzytkownik."','".$odlamki[$licz+23]."');";
					$result = pg_query($database,$query);
					if (!$result)
					{
						echo "<br>Nie powiodl sie: ".$query."<br>";
					}
				}
				if ($odlamki[$licz+26] != "-")
				{
					$query = "insert into kontakt values (".$odlamki[$licz].",'".$odlamki[$licz+26]."','".$uzytkownik."');";
					$result = pg_query($database,$query);
					if (!$result)
					{
						echo "<br>Nie powiodl sie: ".$query."<br>";
					}
				}
				if ($odlamki[$licz+33] != "-")
				{
					$query = "insert into umiejetnosci values (".$odlamki[$licz].",'".$odlamki[$licz+33]."');";
					$result = pg_query($database,$query);
					if (!$result)
					{
						echo "<br>Nie powiodl sie: ".$query."<br>";
					}
				}
				//gdzie kto pracowal - masakra !!
				if ($odlamki[$licz+37] != "-")
				{
					$klienci = explode(",",$odlamki[$licz+37]);
					$i = 0;
					$klienci[$i] = trim($klienci[$i]);
					while($klienci[$i])
					{
						@$klienci[$i+1] = trim($klienci[$i+1]);
						$i++;
					}
					$i--;
					if ($odlamki[$licz+28] == "Aktywny")
					{
						$klienci[0]=str_replace(" ","",$klienci[0]);
						$klienci[0]=str_replace(".","",$klienci[0]);
						$klienci[0]=str_replace("/","",$klienci[0]);
						$klienci[0]=str_replace("-","",$klienci[0]);
						$klienci[0]=strtolower($klienci[0]);
						switch($klienci[0])
						{
							case "csalvesen":
								$klienci[0] = "csalvesentilb";
								break;
							case "csalvesent":
								$klienci[0] = "csalvesentilb";
								break;
							case "csalvesenb":
								$klienci[0] = "csalvesenboxt";
								break;
							case "cia":
								$klienci[0] = "c&adist";
								break;
							case "ahdcz":
								$klienci[0] = "ahdczwol";
								break;
							case "ahdcg":
								$klienci[0] = "ahdcgelder";
								break;
							case "bonn":
								$klienci[0] = "%fa%boon";
								break;
							case "boon":
								$klienci[0] = "%fa%boon";
								break;
							case "fabonn":
								$klienci[0] = "%fa%boon";
								break;
							case "ghuybregets":
								$klienci[0] = "ghuybregts";
								break;
							case "freshideaas":
								$klienci[0] = "freshideas";
								break;
							case "bot":
								$klienci[0] = "gebrbot";
								break;
							case "germaco":
								$klienci[0] = "germoco";
								break;
							case "ahzaandam":
								$klienci[0] = "ahzandaam";
								break;
							case "dejonglilies":
								$klienci[0] = "dejonglelies";
								break;
							case "dejong":
								$klienci[0] = "de%jong";
								break;
							case "pvanbaar":
								$klienci[0] = "vanbaar";
								break;
							case "mtsoud":
								$klienci[0] = "faoud";
								break;
							case "mtsbot":
								$klienci[0] = "gebroedbot";
								break;
							case "dejung":
								$klienci[0] = "de%jong";
								break;
							case "pcconcept":
								$klienci[0] = "pfconcept";
								break;
							case "mantellholland":
								$klienci[0] = "mantelholland";
								break;
							case "mantellholand":
								$klienci[0] = "mantelholland";
								break;
							case "manttelholland":
								$klienci[0] = "mantelholland";
								break;
							case "vanviliet":
								$klienci[0] = "vanvliet";
								break;
							case "vanvielt":
								$klienci[0] = "vanvliet";
								break;
							case "jdewitt":
								$klienci[0] = "jandewit";
								break;
							case "dewitt":
								$klienci[0] = "jandewit";
								break;
							case "dewit":
								$klienci[0] = "jandewit";
								break;
							case "reus":
								$klienci[0] = "gebrreus";
								break;
							case "res":
								$klienci[0] = "gebrreus";
								break;
							case "bakkresland":
								$klienci[0] = "bakkersland";
								break;
							case "fastorz":
								$klienci[0] = "fastosz";
								break;
							case "schenk":
								$klienci[0] = "vofschenk";
								break;
						}
						if($klienci[0] != "%fa%boon" && $klienci[0] != "lico" && $klienci[0] != "de%jong")
						{
							$klienci[0]=chunk_split($klienci[0],1,"%");
							$klienci[0] = "%".$klienci[0];
						}
						$data = explode("-",$odlamki[$licz+26]);
						$odlamki[$licz+35] = oblicz_date($data[0],$data[1],$data[2],$odlamki[$licz+25]);
						$query = "insert into historia_zatrudnienia values (".$odlamki[$licz].",
						'".$odlamki[$licz+26]."','".$odlamki[$licz+25]."','".$odlamki[$licz+35]."',
						(select id from klienci where lower(nazwa) like lower('".$klienci[0]."')),
						'".$uzytkownik."','2006-11-01','1','1','1',
						(select id from msc_odjazdu where nazwa like '".$odlamki[$licz+38]."'),'1');";
						$result = pg_query($database,$query);
						if (!$result)
						{
							echo "<br>Nie powiodl sie: ".$query."<br>";
						}
					}
					else
					{
						$klienci[0]=str_replace(" ","",$klienci[0]);
						$klienci[0]=str_replace(".","",$klienci[0]);
						$klienci[0]=str_replace("-","",$klienci[0]);
						$klienci[0]=str_replace("/","",$klienci[0]);
						$klienci[0]=strtolower($klienci[0]);
						switch($klienci[0])
						{
							case "csalvesen":
								$klienci[0] = "csalvesentilb";
								break;
							case "csalvesent":
								$klienci[0] = "csalvesentilb";
								break;
							case "csalvesenb":
								$klienci[0] = "csalvesenboxt";
								break;
							case "cia":
								$klienci[0] = "c&adist";
								break;
							case "ahdcz":
								$klienci[0] = "ahdczwol";
								break;
							case "ahdcg":
								$klienci[0] = "ahdcgelder";
								break;
							case "bonn":
								$klienci[0] = "%fa%boon";
								break;
							case "boon":
								$klienci[0] = "%fa%boon";
								break;
							case "fabonn":
								$klienci[0] = "%fa%boon";
								break;
							case "ghuybregets":
								$klienci[0] = "ghuybregts";
								break;
							case "freshideaas":
								$klienci[0] = "freshideas";
								break;
							case "bot":
								$klienci[0] = "gebrbot";
								break;
							case "germaco":
								$klienci[0] = "germoco";
								break;
							case "ahzaandam":
								$klienci[0] = "ahzandaam";
								break;
							case "dejonglilies":
								$klienci[0] = "dejonglelies";
								break;
							case "dejong":
								$klienci[0] = "de%jong";
								break;
							case "pvanbaar":
								$klienci[0] = "vanbaar";
								break;
							case "mtsoud":
								$klienci[0] = "faoud";
								break;
							case "mtsbot":
								$klienci[0] = "gebroedbot";
								break;
							case "dejung":
								$klienci[0] = "de%jong";
								break;
							case "pcconcept":
								$klienci[0] = "pfconcept";
								break;
							case "mantellholland":
								$klienci[0] = "mantelholland";
								break;
							case "mantellholand":
								$klienci[0] = "mantelholland";
								break;
							case "manttelholland":
								$klienci[0] = "mantelholland";
								break;
							case "vanviliet":
								$klienci[0] = "vanvliet";
								break;
							case "vanvielt":
								$klienci[0] = "vanvliet";
								break;
							case "jdewitt":
								$klienci[0] = "jandewit";
								break;
							case "dewitt":
								$klienci[0] = "jandewit";
								break;
							case "dewit":
								$klienci[0] = "jandewit";
								break;
							case "reus":
								$klienci[0] = "gebrreus";
								break;
							case "res":
								$klienci[0] = "gebrreus";
								break;
							case "bakkresland":
								$klienci[0] = "bakkersland";
								break;
							case "fastorz":
								$klienci[0] = "fastosz";
								break;
							case "schenk":
								$klienci[0] = "vofschenk";
								break;
						}
						//co literke dajemy % by ulatwic aquiring klienta
						if($klienci[0] != "%fa%boon" && $klienci[0] != "lico" && $klienci[0] != "de%jong")
						{
							$klienci[0]=chunk_split($klienci[0],1,"%");
							$klienci[0] = "%".$klienci[0];
						}
						$query = "insert into historia_zatrudnienia values (".$odlamki[$licz].",'2006-01-01',
						'6','2006-02-14',(select id from klienci where lower(nazwa) like lower('".$klienci[0]."')),
						'".$uzytkownik."','2006-01-01','1','1',
						(select id from status where nazwa = 'Pasywny'),
						(select id from msc_odjazdu where nazwa like '".$odlamki[$licz+38]."'),'1');";
						$result = pg_query($database,$query);
						if (!$result)
						{
							echo "<br>Nie powiodl sie: ".$query."<br>";
						}
					}
					$j = $i;
					while ($j > 0)
					{
						$klienci[$j]=str_replace(" ","",$klienci[$j]);
						$klienci[$j]=str_replace(".","",$klienci[$j]);
						$klienci[$j]=str_replace("/","",$klienci[$j]);
						$klienci[$j]=str_replace("-","",$klienci[$j]);
						$klienci[$j]=strtolower($klienci[$j]);
						switch($klienci[$j])
						{
							case "csalvesen":
								$klienci[$j] = "csalvesentilb";
								break;
							case "csalvesent":
								$klienci[$j] = "csalvesentilb";
								break;
							case "csalvesenb":
								$klienci[$j] = "csalvesenboxt";
								break;
							case "cia":
								$klienci[$j] = "c&adist";
								break;
							case "ahdcz":
								$klienci[$j] = "ahdczwol";
								break;
							case "ahdcg":
								$klienci[$j] = "ahdcgelder";
								break;
							case "bonn":
								$klienci[$j] = "%fa%boon";
								break;
							case "boon":
								$klienci[$j] = "%fa%boon";
								break;
							case "fabonn":
								$klienci[$j] = "%fa%boon";
								break;
							case "ghuybregets":
								$klienci[$j] = "ghuybregts";
								break;
							case "freshideaas":
								$klienci[$j] = "freshideas";
								break;
							case "bot":
								$klienci[$j] = "gebrbot";
								break;
							case "germaco":
								$klienci[$j] = "germoco";
								break;
							case "ahzaandam":
								$klienci[$j] = "ahzandaam";
								break;
							case "dejonglilies":
								$klienci[$j] = "dejonglelies";
								break;
							case "dejong":
								$klienci[$j] = "de%jong";
								break;
							case "pvanbaar":
								$klienci[$j] = "vanbaar";
								break;
							case "mtsoud":
								$klienci[$j] = "faoud";
								break;
							case "mtsbot":
								$klienci[$j] = "gebroedbot";
								break;
							case "dejung":
								$klienci[$j] = "de%jong";
								break;
							case "pcconcept":
								$klienci[$j] = "pfconcept";
								break;
							case "mantellholland":
								$klienci[$j] = "mantelholland";
								break;
							case "mantellholand":
								$klienci[$j] = "mantelholland";
								break;
							case "manttelholland":
								$klienci[$j] = "mantelholland";
								break;
							case "vanviliet":
								$klienci[$j] = "vanvliet";
								break;
							case "vanvielt":
								$klienci[$j] = "vanvliet";
								break;
							case "jdewitt":
								$klienci[$j] = "jandewit";
								break;
							case "dewitt":
								$klienci[$j] = "jandewit";
								break;
							case "dewit":
								$klienci[$j] = "jandewit";
								break;
							case "reus":
								$klienci[$j] = "gebrreus";
								break;
							case "res":
								$klienci[$j] = "gebrreus";
								break;
							case "bakkresland":
								$klienci[$j] = "bakkersland";
								break;
							case "fastorz":
								$klienci[$j] = "fastosz";
								break;
							case "schenk":
								$klienci[$j] = "vofschenk";
								break;
						}
						//co literke dajemy % by ulatwic aquiring klienta
						if($klienci[$j] != "%fa%boon" && $klienci[$j] != "lico" && $klienci[$j] != "de%jong")
						{
							$klienci[$j]=chunk_split($klienci[$j],1,"%");
							$klienci[$j] = "%".$klienci[$j];
						}
						$query = "insert into historia_zatrudnienia values (".$odlamki[$licz].",'2006-01-01',
						'6','2006-02-14',(select id from klienci where lower(nazwa) like lower('".$klienci[$j]."')),'".$uzytkownik."','2006-01-01',
						'1','1',(select id from status where nazwa = 'Pasywny'),(select id from msc_odjazdu where nazwa like '".$odlamki[$licz+38]."'),'1');";
						$result = pg_query($database,$query);
						if (!$result)
						{
							echo "<br>Nie powiodl sie: ".$query."<br>";
						}
						$j--;
					}
				}
				//korespondencje
				if($odlamki[$licz+40] != "-" && $odlamki[$licz+39] != "-")
				{
					if ($odlamki[$licz+39] == "2006-02-31"){$odlamki[$licz+39] = "2006-02-28";}
					$odlamki[$licz+40]=chunk_split($odlamki[$licz+40],1,"%");
					$query = "insert into korespondencje values (".$odlamki[$licz].",(select id from rodzaj_korespondencji where nazwa like '".$odlamki[$licz+40]."'),'".$uzytkownik."','".$odlamki[$licz+39]."');";
					$result = pg_query($database,$query);
					if (!$result)
					{
						echo "<br>Nie powiodl sie: ".$query."<br>";
					}
				}
				//zadania dnia, nie skonwertowane sa daty, wiec konwersja ponizej
				if($odlamki[$licz+41] != "-")
				{
					@$dzis_h = date(Y."-".m."-".d." ".H.":".i.":".s);
					$dane = explode(" ",$odlamki[$licz+41]);
					$odlamki[$licz+41] = $dane[2]."-".$dane[1]."-".$dane[0];
					$query = "insert into zadania_dnia values (".$odlamki[$licz].",'".$odlamki[$licz+41]."','".$odlamki[$licz+42]."','".$uzytkownik."', '".$dzis_h."');";
					if($odlamki[$licz+41] >= $dzis)
					{	
						$result = pg_query($database,$query);
						if (!$result)
						{
							echo "<br>Nie powiodl sie: ".$query."<br>";
						}
					}
				}
				if($odlamki[$licz+47] != "-" && $odlamki[$licz+47] != "")
				{
					if (strlen($odlamki[$licz+48]) > 149)
					{$odlamki[$licz+48] = substr($odlamki[$licz+48],0,149);}
					$query = "insert into reklamacje values (".$odlamki[$licz].",'".$odlamki[$licz+47]."','".$odlamki[$licz+48]."','".$uzytkownik."', 1, 'false');";
					$result = pg_query($database,$query);
					if (!$result)
					{
						echo "<br>Nie powiodl sie: ".$query."<br>";
					}
				}
			}
			//if ($wykonaj)
			//{
			//	echo "Dane wprowadzone poprawnie!<br>";
			//}
		}
		fclose($plik);
		//fclose($plik2);
		//echo "<br><a href='select.php'>Obejrzyj tabelke.</a>";
	}
	else
	{
		echo "Brak pliku.";
	}
?>
</html>
