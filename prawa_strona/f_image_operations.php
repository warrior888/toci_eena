<?php
	function fittosize ($resourceimage)
	{
		$percent = 1;
		$rozmiar = 1124;
		list($width, $height) = getimagesize($resourceimage);
		if ($width > $rozmiar || $height > $rozmiar)
		{
                //ktory z 2 parametrow jest wiekszy, wzgledem tego skalujemy
			    if ($width > $height)
			    {
			    	$percent = $rozmiar / $width;
			    }
			    else
			    {
			        $percent = $rozmiar / $height;
			    }
		}
		if ($percent < 1)
		{
		    $newwidth = $width * $percent;
            $newheight = $height * $percent;
            $thumb = imagecreatetruecolor($newwidth, $newheight);
		    $source = imagecreatefromjpeg($resourceimage);
		    imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);
			      //image data, image name, quality	
		    ImageJPEG($thumb, $resourceimage, 95);
        }
	}
    function setHeadingRow($odlamki_nag)
    {
        //$odlamki_nag = explode(",","Klient, Data, Ilo¶æ kobiet, Ilo¶æ mê¿czyzn, Ilo¶æ tygodni");
        $licznik_nagl = 0;
        while (isset($odlamki_nag[$licznik_nagl]))
        {
            echo "<th nowrap align='CENTER'>".$odlamki_nag[$licznik_nagl]."</td>";
            //echo "";
            $licznik_nagl++;
        }
    }
    function addRowsToTable($wiersz)
    {
        foreach ($wiersz as $kolumna)
        {
            if (!$kolumna)
                $kolumna = '-';
                
            echo '<td nowrap align="center">'.$kolumna.'</td>';
        }
    }
    //handles inserts to zatrudnienie table
    function TriggerLogic(&$query, $dzis, $data_wyjazd, $data_powrot, $status_id, $person_id, $msc_id, &$controls, &$db, &$wyodrMsc)
    {
        //jesli bierzace umowienie zawiera sie w bierzacych ramach czasowych status z niego jest najbardziej aktualny
        if ($data_wyjazd <= $dzis && $data_powrot >= $dzis)
        {
            $query .= "update stat set id_status = ".$status_id." where id = ".$person_id.";";
        }
        else
        {
            //jesli na bazie nie ma w tej chwili umowienia, ktore wiazaloby sie z aktualnym zatrudnieniem osoby status
            // z nowego umowienia jest najbardziej aktualny
            $testquery = "select id_status from ".$db->tableName." where data_wyjazdu <= '".$dzis."' and data_powrotu >= '".$dzis."' and id_osoba = '".$person_id."';";
            $effect = $controls->dalObj->pgQuery($testquery);
            $rowEff = pg_fetch_array($effect);
            if (pg_num_rows($effect) == 0)
            {
                $query .= "update stat set id_status = '".$status_id."' where id = '".$person_id."';";
                //nie ma wakatu bierzacego, wiec miesjce powrotu moze zostac zupdate'owane
                $msc_pow_applies = true;
            }
            else
            {
                //de facto powinien zostac status ktory byl w przekonaniu ze byl poprawnie ustawiony
                //dla pewnosci to weryfikujemy
                $query .= "update stat set id_status = '".$rowEff['id_status']."' where id = '".$person_id."';";
            } 
        }
        /*$testWyodr = "select ".$wyodrMsc->tableId." from ".$wyodrMsc->tableName." where ".$wyodrMsc->tableId." = ".$person_id.";";
        $resWyodr = $controls->dalObj->pgQuery($testWyodr);
        if (pg_num_rows($resWyodr) == 0)
        {
            $query .= "insert into ".$wyodrMsc->tableName." values (".$person_id.",".$msc_id.",'".$data_powrot."')";
        }
        else
        {
            //update jest mozliwy, jesli osoba nie jest na wyjezdzie z perspektywa powrotu
            //zalozenie jest takie ze data powrotu jest albo dla bierzacego pobytu jesli nie ma lepszego
            //lub data powrotu jest data ostatniego faktycznego powrotu ktory sie odbyl
            //jeszcze to wyklarowac: pytanie czy miejsce powrotu i data powrotu maja byc ostatnimi, jakie w istocie mialy miejsce czy niekoniecznie
            if ($msc_pow_applies == true)
            {
                $query .= "update ".$wyodrMsc->tableName." set id_msc_odjazdu = ".$msc_id.", data_powrotu = '".$data_powrot."' where id = ".$person_id.";";
            }
        } */
    }
    //handle dalate from zatrudnienie
    function DeleteTriggerLogic($person_id, &$controls)
    {
        $dzis = date('Y-m-d');
        $query = "select id_status, data_wyjazdu, data_powrotu from zatrudnienie where id_osoba = '".$person_id."' order by data_wyjazdu desc limit 1;";
  
        $result = $controls->dalObj->pgQuery($query);
        $row = pg_fetch_array($result);
        //jesli na bazie osoba nie ma zadnego umowienia jest nowa
        if (pg_num_rows($result) == 0)
        {
            $query = "update stat set id_status = (select id from status where nazwa = 'Nowy') where id = ".$person_id.";";
            $result = $controls->dalObj->pgQuery($query);
            //echo $query;
        }
        else  //na bazie jest juz jakis wpis w zatrudnieniu
        {
            //poszukiwanie wpisu mieszczacego sie w bierzacych ramach czasowych
            $testquery = "select id_status from zatrudnienie where data_wyjazdu <= '".$dzis."' and data_powrotu >= '".$dzis."' and id_osoba = '".$person_id."';";
            $result = $controls->dalObj->pgQuery($testquery);
            $resRow = pg_fetch_array($result);
            //echo $testquery;
            //zaden wpis nie miesci sie w bierzacych ramach czasowych, uzywamy statusu z ostatniego mozliwego wpisu
            if (pg_num_rows($result) == 0)
            {
                $query = "update stat set id_status = ".$row['id_status']." where id = ".$person_id.";";
                //echo $query;
            }
            else //jeden (oby nie wiecej ;) ) wpis na bazie to bierzace umowienie
            {
                $query = "update stat set id_status = ".$resRow['id_status']." where id = ".$person_id.";";
                //echo $query;
            }
            $result = $controls->dalObj->pgQuery($query);
        }
    }
    function ColisionDetection($person_id, $rekord_id, $data_wyjazd, $data_powrot, $db, $controls)
    {
        //nd new data
        //if data wyjazdu nd jest mniejsza od data powrtou db and data powrotu nd jest wieksza od daty powrotu db - lipa
        //if data wyjazdu nd jest mniejsza od daty wyjazdu db and data powrotu nd jest wieksza od daty wyjazdu db - lipa
        $query = "select id from ".$db->tableName." where id != ".$rekord_id." and id_osoba = ".$person_id." and ((data_powrotu >= '".$data_wyjazd."' and data_powrotu <= 
        '".$data_powrot."') or (data_wyjazdu >= '".$data_wyjazd."' and data_wyjazdu <= '".$data_powrot."'));";
        //jedno z 2: albo dac not na rekord update owany zeby nie zglaszal kolizji ze samym soba
        //albo zrobic nierownosci ostre - to chepskie rozwiazanie bo w przypadku update wyjazdu pasywnego
        //cofne date wyjazdu o 1 dzien i date powrotu o 1 popchne i sie wysra
        //$queryPost = "select id from ".$db->tableName." where data_wyjazdu >= '".$data_wyjazd."' and data_wyjazdu <= '".$data_powrot."';";
        $result = $controls->dalObj->pgQuery($query);
        if (pg_num_rows($result) == 0)
        {
            return enum::$ALLOWDATA;
        }
        else
        {
            return enum::$DONTALLOWDATA;
        }
    }
?>
