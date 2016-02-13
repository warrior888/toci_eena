<link rel="stylesheet" href="../css/styluzup.css">
<?php
	require("../conf.php");
	$zmienna="../uploads/";
	$zmienna=$zmienna."msc_odjazdu.txt";
	//$zmienna2="uploads/";
	//$zmienna2=$zmienna2."kolumna2.txt";
	if($plik=fopen($zmienna,"r"))
	{
		//$plik2=fopen($zmienna2,"r");
		//echo "Plik wczytany!<br>";
		$database = pg_connect($con_str);
		while(! feof($plik))
		{
			$insert="insert into msc_odjazdu values (nextval('msc_odjazdu_id_seq'),'";
			$ciag=addslashes(trim(fgets($plik)));
			$insert=$insert.$ciag;
			$insert=$insert."');";
			$zapytanie = "select id from msc_odjazdu where lower(nazwa) = lower('".$ciag."');";
			$done = pg_query($database, $zapytanie);
			if (pg_num_rows($done) == 0)
			{
				$wykonaj = pg_query ($database,$insert);
				echo "Wprowadzono miejsce odjazdu: ".$ciag.".<br>";
			}
			else
			{
				echo "Miejsce odjazdu ".$ciag." jest juz w slowniku.<br>";
			}
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
