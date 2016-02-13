<?php
#TODO: do osobnego pliku, bedzie potrzebne w rozwarstwieniu ...
#case: pobranie listy msc odjazdu po podaniu daty wyjazdu .... - request ajax, zmiana wakatow na liscie wakatow - potencjalna zmiana daty ...
//przy zmianie dat jesli msc wyjazdu pasuje nie burzyc ....

//ta funkcja jest tylko prezentacyjna; pomysl: kazdorazowo w kombie przewzonik bartusia trzeba wybrac
//po jego wybraniu php zwroci html do injectowaniado formularza (jedno kombo z datami i godzinami, 2 idki danych w tym kombie sklejone np |)
    function addTravelAgentCombos($zaznPrzewoznikId, $zaznMscOdjId, $data_wyjazd, $id_msc_odjazd, $msc_odjazd_id) //suffix nazwy przewoznika ? o ile metoda zostanie uzyta
    {        
        $controls = new valControl();
        $dzien = strftime('%w', strtotime($data_wyjazd));
        
        $result = $controls->AddSelectRandomQuerySVbyId('przewoznik', 'id_przewoznik', '', 'select id, nazwa from przewoznik', $zaznPrzewoznikId, 'przewoznik_id');
        
        $result .= '&nbsp;'.$controls->AddSelectRandomQuerySVbyId($id_msc_odjazd, $id_msc_odjazd, '', 
            'select id, nazwa from msc_odjazdu where id in (select id_msc_odjazdu from rozklad_jazdy where id_przewoznik = '.$zaznPrzewoznikId.' and dzien = '.$dzien.')', 
            $zaznMscOdjId, $msc_odjazd_id);
        //3 kombo tylko gdy $zaznMscOdjId != null
        return $result;
    }
    
    function requiredFieldsFilled () {
        
        
    }

    require_once '../conf.php';
    require_once 'ui/FormValidator.php';
    CommonUtils::outputBufferingOn();
    CommonUtils::SessionStart();

    echo '<html>';
    HelpersUI::addHtmlBasicHeaders(HelpersUI::PATH_LEVEL_1);
    echo '<body>';
    echo '<script src="../js/custom/employmentHistory.js"></script>';
    echo '<script>
        function getDepartureCityCombo(data, id_przewoznik, kontener, suffix, mscName, mscId, isReturn) 
        {
            $.ajax({
                
                type: "GET",
                data: "przewoznik_id=" + id_przewoznik + "&data=" + data + "&suffix=" + suffix + "&mscName=" + mscName + "&mscId=" + mscId + "&isReturn=" + isReturn,
                url: "historia_zatrudnienia_support.php",
                timeout: 45000,
                error: function() {
                  console.log("GET failed");
                },
                success: function(result) {
                    document.getElementById(kontener).innerHTML = result;
                }
            })
        }
        
        function getTicketsCombo(id_przewoznik, suffix, kontener) 
        {
            $.ajax({
                
                type: "GET",
                data: "przewoznik_id=" + id_przewoznik + "&bilety=1&suffix=" + suffix,
                url: "historia_zatrudnienia_support.php",
                timeout: 45000,
                error: function() {
                  console.log("GET failed");
                },
                success: function(result) {
                    document.getElementById(kontener).innerHTML = result;
                }
            })
        }
        

        function getPaymentFormsCombo(id_bilet, suffix, kontener)
        {
            $.ajax({
                
                type: "GET",
                data: "idBilet="+id_bilet+"&suffix=" + suffix,
                url: "historia_zatrudnienia_support.php",
                timeout: 45000,
                error: function() {
                  console.log("GET failed");
                },
                success: function(result) {
                    document.getElementById(kontener).innerHTML = result;
                }
            })
        }
        
        function getPaymentStatusCombo(id_bilet, suffix, kontener)
        {
            $.ajax({
                
                type: "GET",
                data: "idBiletForStatus="+id_bilet+"&suffix=" + suffix,
                url: "historia_zatrudnienia_support.php",
                timeout: 45000,
                error: function() {
                  console.log("GET failed");
                },
                success: function(result) {
                    document.getElementById(kontener).innerHTML = result;
                }
            })
        }
        
    </script>';
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
        include_once("../prawa_strona/f_image_operations.php");
        include_once("../dal/klient.php");
        include_once("../oblicz_date.php");
        require_once 'ui/HtmlControls.php';

        $controls = new valControl(); 
        $htmlControls = new HtmlControls(); 
        
        $id_osoba = Utils::PodajIdOsoba();

        //names of form submits
        $insert = 'insert_oddzial';
        $update = 'update_oddzial';
        $delete = 'erase_oddzial';
        $edit = 'edit_oddzial';
        //dal object inside those controls handles db connection
        //bussines logic layer classes responsible for table name and primary key info
        $db = new zatrudnienie();
        $wakat = new wakaty();
        $klient = new klient();
        $oddzial = new oddzial();
        $status = new status();
        $charakter = new charakter();
        $daneOs = new daneOsobowe();
        $wyodrMsc = new mscPowrotWyodr();
        $decyzja = new decyzja();
        
        //enum::$ALLOWDATA;
        //enum::$DONTALLOWDATA;
        
        //region consts
        $wakat_sys = 1;
        $wakat_const_pasywny = 1;
        $status_const_name = 'status';
        $status_const_akt = 'Aktywny';
        $status_const_new_person = "'Aktywny','Nowy','Wyje¿d¿aj±cy'";
        $status_const_pasive_person = "'Aktywny','Pasywny','Nieodpowiedni'";
        $charakter_const_nieod = 'Nieodpowiedni';
        $oddzial_const_name = 'oddzial';
        $klient_const_name = 'klient';
        $wakat_const_name = 'nazwa';
        $umowiony_const_name = 'Umówiony'; 
        
        $zatrudnienie_all_cols = $db->tableName.'.'.$db->tableId.','.$db->tableName.'.id_osoba,'.$db->tableName.'.id_klient,
        '.$db->tableName.'.id_oddzial,'.$db->tableName.'.id_wakat,'.$db->tableName.'.id_status,'.$db->tableName.'.data_wyjazdu,
        '.$db->tableName.'.ilosc_tyg,'.$db->tableName.'.data_powrotu,'.$db->tableName.'.data_wpisu,'.$db->tableName.'.id_decyzja,
        '.$db->tableName.'.id_msc_odjazd,'.$db->tableName.'.id_bilet,'.$db->tableName.'.id_pracownik,
        '.$db->tableName.'.id_miejsca_docelowe,
        '.$db->tableName.'.id_osoby_kontaktowe';      
	    
        $QueryAv = "select ".$daneOs->tableName.".".$daneOs->tableId." from ".$daneOs->tableName." 
        join ".$charakter->tableName." on ".$daneOs->tableName.".id_charakter = ".$charakter->tableName.".".$charakter->tableId." 
        where ".$daneOs->tableName.".".$daneOs->tableId." = ".$id_osoba." 
	    and ".$charakter->tableName.".nazwa = '".$charakter_const_nieod."';";
	    //echo $QueryAv;
        $ResAv = $controls->dalObj->pgQuery($QueryAv);
	    if (pg_num_rows($ResAv) > 0)
	    {
		    echo "Osoba jest nieodpowiednia (ma taki charakter pracy), nie ma mo¿liwo¶ci umawiania na wyjazdy tej osoby.";
	    }
	    else
	    {
		    $ile = 1;
		    //$database = pg_connect($con_str);
		    $zapytanie = "select ".$zatrudnienie_all_cols." from ".$db->tableName." where ".$db->tableName.".id_osoba = '".$id_osoba."' 
            and data_powrotu > '".$dzis."' order by data_wyjazdu desc;";
            $wynik = $controls->dalObj->pgQuery($zapytanie);
		    //$wynik = pg_query($database, $zapytanie);
		    if (pg_num_rows($wynik) > 0)
            {
                $ile = pg_num_rows($wynik);
            }
		    $zapytanie = "select ".$zatrudnienie_all_cols.", ".$status->tableName.".nazwa as ".$status_const_name." from ".$db->tableName." 
            join ".$status->tableName." on ".$db->tableName.".id_status = ".$status->tableName.".".$status->tableId."
            where ".$db->tableName.".id_osoba = '".$id_osoba."' order by data_wyjazdu desc limit ".$ile.";";
		    // and ".$db->tableName.".id_status in 
            // (".ID_STATUS_PASYWNY.", ".ID_STATUS_AKTYWNY.", ".ID_STATUS_NIEODPOWIEDNI.") 
		    $wynik = $controls->dalObj->pgQuery($zapytanie);
		    //$wynik = pg_query($database, $zapytanie);
		    if (pg_num_rows($wynik) > 0)
		    {
			    while ($wiersz = pg_fetch_array($wynik))		
			    {
				    if($dzis > $wiersz['data_wyjazdu'])//wyjazd sie juz odbyl
				    {
					    // "Po wyje¼dzie:";
					    if($dzis <= $wiersz['data_powrotu'])//jeszcze jest w hol
					    {
						    require("wysw_po_wyjezdzie.php");
						    if($ile < 2)
						    {
							    require("wysw_liste_znanych_klientow.php");
							    require("wysw_wakaty.php");
						    }
					    }
					    if($dzis > $wiersz['data_powrotu'])
					    {
						    if($wiersz[$status_const_name] == $status_const_akt)
						    {
							    require("wysw_po_wyjezdzie.php");
						    }
						    else
						    {
							    require("wysw_liste_znanych_klientow.php");
							    require("wysw_wakaty.php");
						    }
					    }
				    }
				    else // wyjazd ma nastapic: tu update
				    {
					    // "Przed wyjazdem:";
					    if($wiersz['id_wakat'] == $wakat_sys)
					    {
						    require("wysw_wyb_klient_pas.php");
					    }
					    else
					    {
						    require("wysw_wyb_wakat.php");
					    }
				    }
			    }
		    }
		    else 
		    {
			    require("wysw_wakaty.php");
		    }
	    }
    }
    CommonUtils::sendOutputBuffer();
?>
</html>
