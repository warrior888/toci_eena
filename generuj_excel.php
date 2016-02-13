<?php
	function create_excel ($IloscKolumn,$query,$sheet, $nag)
	{	
		require("conf.php");
		$database = pg_connect($con_str);
        pg_set_client_encoding ($database, 'LATIN2');
		$i = 0;
		//$kolumna to tablica maksymalnych dlugosci zapisywnych tam textow, pozniej kazdy dluzszy text bedzie poszerzal kolumne
		while ($i <= $IloscKolumn)
		{
			$kolumna[$i] = 4;
			$i++;
		}
        for ($l = 0; $l < count($nag); $l++)
        {
            $sheet->write(0, $l, $nag[$l], 0);
        }
		$j = 1;
		$zapytanie = $query;
		$wynik = pg_query($database,$zapytanie);
		while ($wiersz = pg_fetch_assoc($wynik))
		{
			$i = 0;
			foreach ($wiersz as $key => &$column)
			{
				$column = str_replace("±",chr(185),$column);//to co ta powloka widzi jako ± excel pokazuje jako krzak
				//w ecelu ± to 185 ascii, ¶ to 156 ascii, ¼ 159 itd
				$column = str_replace("¶",chr(156),$column);
				$column = str_replace("¼",chr(159),$column);
                $column = str_replace("&#8364;",chr(128),$column);
				$column = str_replace("¦",chr(140),$column);//¦ pod excelem to 140 ascii
				$column = str_replace("¬",chr(143),$column);//¬ pod excelem to 143 ascii
				
				//jesli dlugosc bierzacego stringu w $wiersz[$i] jest wyzsza niz ostatnio zapamietana jako najdluzsza;
				if (strlen($column) > $kolumna[$i])
				{
					//zapisz nowa wartosc
					$kolumna[$i] = strlen($column);
					//echo "Bierzaca dlugosc kolumny ".$i." to ".$kolumna[$i]." .<br>";
				}
                else if (strlen($nag[$i]) > $kolumna[$i])
                {
                    $kolumna[$i] = strlen($nag[$i]);
                }
				$sheet->write($j, $i, $column, 0);//j wiersz, i kolumna, wiersz[i] kolejna informacja z zapytania $zapytanie
				//zamiast 0 w f. powyzej mozna dac $format, jesli ten format powyzej zefiniowany jest ok
				$i++;
			}
			$j++;
		}
        $i = 0;
		while ($i < $IloscKolumn)
		{
			$sheet->setColumn($i, $i, $kolumna[$i], 0, 0, 0);  //szerokosc kolumny
			$i++;
		}
	}
?>
