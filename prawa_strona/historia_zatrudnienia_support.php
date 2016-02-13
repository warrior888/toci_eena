<?php
    //±¶
    //TODO move to ajaxapi
    require_once '../conf.php';
    CommonUtils::SessionStart();
    header('Content-Type: text/html; charset=iso-8859-2');
    
    

    setlocale(LC_ALL, 'pl_PL');
    
    function getDepartureCityCombo ($data, $przewoznikId, $suffix, $mscName, $mscId, $isReturn) 
    {
        //return a combo filled with departures available for a day for a travel agency
        $controls = new valControl();
        //dzien w zakresie 0-6 (1 - poniedzialek, 0 - niedziela)
        $time = strtotime($data);
        $dzien = strftime('%w', $time);
        $dzienNazwa = strftime('%A', $time);
        
        $dayNames = array(
            PONIEDZIALEK_ID => 'poniedzia³ek',
            PIATEK_ID       => 'pi±tek',
        );
        
        //extra html hint dla bartusia w nietypowy dzien
        $extraHint = '';
        
        if ($przewoznikId == PRZEWOZNIK_BARTUS_ID) //bartus
        {
            if($time > strtotime('2014-12-29')) {
                $extraHint .= '<br /><span style="font-size: 14px; color: red;"><b>Uwaga:</b> "Bartu¶" nie kursuje od 2015-01-05</span>';    
            }
            

            if ($dzien != PONIEDZIALEK_ID && $dzien != PIATEK_ID)
            {
                if ($dzien < CZWARTEK_ID || $dzien == NIEDZIELA_ID)
                    $dzien = PONIEDZIALEK_ID;
                else
                    $dzien = PIATEK_ID;
                    
                $extraHint .= '<br /><span style="font-size: 14px;"><b>Uwaga:</b> Data wskazuje na dzieñ: <b>'.$dzienNazwa.
                '</b>, w którym normalnie Bartu¶ nie kursuje, za³adowano listê wyjazdow± dla dnia: <b>'.$dayNames[$dzien].'</b></span>'; 
            }
        }

        if ($przewoznikId == PRZEWOZNIK_SOLTYSIK_ID)
        {
            // 0 - niedziela tylko dla wyjazdów, 6 - sobota
            $extraHint .= '<br /><span style="font-size: 14px; color: red;"><b>Uwaga:</b> Od dnia 2015-08-01 "So³tysik" kursuje tylko w poniedzia³ki i pi±tki</span>';
            
            $busWorkDays = array(1, 5);
            if($isReturn != 1) {
                $busWorkDays[] = 0;
            }

            if (!in_array($dzien, $busWorkDays))
            {
                if ($dzien < CZWARTEK_ID)
                    $dzien = PONIEDZIALEK_ID;
                else
                    $dzien = PIATEK_ID;
                    
                $extraHint .= '<br /><span style="font-size: 14px;"><b>Uwaga:</b> Data wskazuje na dzieñ: <b>'.$dzienNazwa.
                '</b>, w którym normalnie So³tysik nie kursuje, za³adowano listê wyjazdow± dla dnia: <b>'.$dayNames[$dzien].'</b></span>'; 
            }
        }
        
        $query = 'select rozklad_jazdy.id,  rozklad_jazdy.id_msc_odjazdu as '.$mscId.', 
        msc_odjazdu.nazwa || \', \' || rozklad_jazdy.godzina || \', \' || rozklad_jazdy.przystanek as nazwa 
        from rozklad_jazdy join msc_odjazdu on rozklad_jazdy.id_msc_odjazdu = msc_odjazdu.id 
        where rozklad_jazdy.id_przewoznik = '.$przewoznikId.
        ' and rozklad_jazdy.dzien = '.$dzien.' and active = true order by nazwa asc; ';
        
        return $controls->AddSelectRandomQuery('rozklad_jazdy_'.$suffix, 'id_rozklad_jazdy_'.$suffix, '', $query, null, 'rozklad_jazdy_'.$suffix.'_id', 'nazwa', 'id', 'utils.setHiddenFromSelectLabel(this, \''.$mscName.'_'.$suffix.'_id\');', $mscId, $mscName.'_'.$suffix.'_id').$extraHint;
    }
    
    function getTicketsCombo($przewoznikId, $suffix) 
    {
        $controls = new valControl();
        
        $query = 'select id, nazwa from bilety where id_przewoznik = '.$przewoznikId.' order by nazwa asc;';
        return $controls->AddSelectRandomQuery('id_bilet_'.$suffix, 'id_bilet_'.$suffix, "", $query, "", 'bilet_'.$suffix.'_id', "nazwa", "id", "", null, ""
                , "getPaymentFormsCombo(utils.getElementValue('id_bilet_$suffix') ,'ww', 'forma_platnosci_".$suffix."_container'); getPaymentStatusCombo(utils.getElementValue('id_bilet_$suffix') ,'ww', 'stan_realizacji_".$suffix."_container');");
    }
    
    function getPaymentFormsCombo($idBilet, $suffix) 
    {
        if($idBilet == 'Brak') return; //Brak biletu
        $controls = new valControl();
        
        $query = 'select 0 AS id, \'--------\' AS nazwa UNION select id, nazwa from forma_platnosci order by nazwa asc';
        return $controls->AddSelectRandomQuery('id_forma_platnosci_'.$suffix, 'id_forma_platnosci_'.$suffix, "", $query, "", 'forma_platnosci_'.$suffix.'_id', "nazwa", "id", "");
    }
    
    function getPaymentStatusCombo($idBilet, $suffix) 
    {
        if($idBilet == 'Brak') return; //Brak biletu
        $controls = new valControl();
        
        $query = "select 0 AS id, '--------' AS nazwa UNION select id, nazwa from stan_realizacji order by nazwa asc";
        return $controls->AddSelectRandomQuery('id_stan_realizacji_'.$suffix, 'id_stan_realizacji_'.$suffix, "", $query, "", 'stan_realizacji_'.$suffix.'_id', "nazwa", "id", "");
    }
    
    function getDestinations($id, $type = null) {
        $controls = new valControl();
        $id = (int)$id;
        
        if($type == 'wysw_liste_znanych_klientow' || $type == 'klient_wwkp' || $type == 'wysw_wyb_klient_pas' || $type == 'wysw_po_wyjezdzie') {
            $query = "select null as id, '--------' as nazwa 
            union 
                select md.id, md.nazwa from public.miejsca_docelowe md 
                    JOIN public.oddzialy_klient ok ON ok.id_biuro = md.id_miejscowosc_biuro 
                    WHERE ok.id = $id "
            . "ORDER BY nazwa";
        } else {               
            $query = "select null as id, '--------' as nazwa 
                union 
                    select md.id, md.nazwa from public.miejsca_docelowe md 
                        JOIN public.oddzialy_klient ok ON ok.id_biuro = md.id_miejscowosc_biuro 
                        JOIN public.wakat w ON w.id_oddzial = ok.id
                        WHERE w.id = $id "
                . "ORDER BY nazwa";
        }
        
        return $controls->AddSelectRandomQuery('id_destination_' . $type, 'id_destination_' . $type, "", $query, "", 'destination_' . $type, "nazwa", "id", "");
    }
    
    function getContactPersons($id, $type = null) {
        $controls = new valControl();
        $id = (int)$id;
        
        if($type == 'wysw_liste_znanych_klientow' || $type == 'klient_wwkp' || $type == 'wysw_wyb_klient_pas' || $type == 'wysw_po_wyjezdzie') {
            $query = "select null as id, '--------' as osoba 
                union 
                    select k.id, k.osoba from public.osoby_kontaktowe k 
                        JOIN public.oddzialy_klient ok ON ok.id_biuro = k.id_miejscowosc_biuro 
                        WHERE ok.id = $id "
                . "ORDER BY osoba";
        } else {
            $query = "select null as id, '--------' as osoba 
                union 
                    select k.id, k.osoba from public.osoby_kontaktowe k 
                        JOIN public.oddzialy_klient ok ON ok.id_biuro = k.id_miejscowosc_biuro 
                        JOIN public.wakat w ON w.id_oddzial = ok.id
                        WHERE w.id = $id "
                . "ORDER BY osoba";
        }
        
        return $controls->AddSelectRandomQuery('id_contactPerson_' . $type, 'id_contactPerson_' . $type, "", $query, "", 'contactPerson_' . $type, "osoba", "id", "");
    }
    
    if (empty($_SESSION['uzytkownik']))
    {
        header('HTTP/1.1 401');
        die();
    }
    else
    {
        ///return all sources of html to append to form
        if (!empty($_GET['przewoznik_id']) && !empty($_GET['data']) && !empty($_GET['suffix']))
        {
            echo getDepartureCityCombo($_GET['data'], (int)$_GET['przewoznik_id'], $_GET['suffix'], $_GET['mscName'], $_GET['mscId'], $_GET['isReturn']);
        }
        
        if (!empty($_GET['przewoznik_id']) && !empty($_GET['bilety']) && !empty($_GET['suffix']))
        {
            echo getTicketsCombo((int)$_GET['przewoznik_id'], $_GET['suffix']);
        }
        
        if (!empty($_GET['dataType']) && $_GET['dataType'] == 'destinations' && !empty($_GET['wakatId']))
        {
            echo getDestinations((int)$_GET['wakatId'], $_GET['type']);
        }
        
        if (!empty($_GET['dataType']) && $_GET['dataType'] == 'contactPersons' && !empty($_GET['wakatId']))
        {
            echo getContactPersons((int)$_GET['wakatId'], $_GET['type']);
        }
        
        if (!empty($_GET['idBilet']) && !empty($_GET['suffix']))
        {
            echo getPaymentFormsCombo($_GET['idBilet'], $_GET['suffix']);
            die;
        }
        
        if (!empty($_GET['idBiletForStatus']) && !empty($_GET['suffix']))
        {
            echo getPaymentStatusCombo($_GET['idBiletForStatus'], $_GET['suffix']);
            die;
        }
    }
?>