<?php
require_once 'dal/DALZatrudnienie.php';
require_once 'adl/User.php';

if (empty($_SESSION['uzytkownik']))
{
    die();
}
else
{
    $dalZatrudnienie = new DALZatrudnienie();
    $user = User::getInstance();

    if(isset($_POST[$delete]))
    {
        $query = "delete from zatrudnienie_odjazd where id_zatrudnienie = ".$_POST[ID_ZATRUDNIENIE].";
            delete from ".$db->tableName." where ".$db->tableName.".".$db->tableId." = ".$_POST[ID_ZATRUDNIENIE].";";
        $result = $controls->dalObj->pgQuery($query);
        DeleteTriggerLogic($id_osoba, $controls);
        echo '<script>window.close();</script>';
    }
    if(isset($_POST[$update]))
    {
        $chosenStatus = (int)$_POST['status_wwkp_id'];
        $idDeparturePlan = isset($_POST['rozklad_jazdy_wwkp_id']) ? (int)$_POST['rozklad_jazdy_wwkp_id'] : null;
        $idDepartureReturnPlan = isset($_POST['rozklad_jazdy_powrot_wwkp_id']) ? (int)$_POST['rozklad_jazdy_powrot_wwkp_id'] : null;

        if ($chosenStatus == ID_STATUS_WYJEZDZAJACY && !$idDeparturePlan) {

            echo '<label class="error">B³±d. Ustalono status wyje¿d¿aj±cy bez podania odjazdu. </label><br />';
        } else if ($_POST['oddzial_wwkp_id'] > 0 && $_POST['status_wwkp_id'] > 0 && $_POST['ilosc_tyg_wwkp'] > 0 && strlen($_POST['data_wyjazdu_wwkp']) && strlen($_POST['data_powrotu_wwkp']) == 10 && $_POST['decyzja_wwkp_id'] > 0) {

            $data = array(
            Model::COLUMN_ZTR_ID               => (int)$_POST[ID_ZATRUDNIENIE],
            Model::COLUMN_ZTR_ID_KLIENT        => (int)$_POST['klient_id_wwkp'],
            Model::COLUMN_ZTR_ID_ODDZIAL       => (int)$_POST['oddzial_wwkp_id'],
            Model::COLUMN_ZTR_ID_WAKAT         => (int)$wakat_const_pasywny,
            Model::COLUMN_ZTR_ID_STATUS        => $chosenStatus,
            Model::COLUMN_ZTR_DATA_WYJAZDU     => $_POST['data_wyjazdu_wwkp'],
            Model::COLUMN_ZTR_ILOSC_TYG        => (int)$_POST['ilosc_tyg_wwkp'],
            Model::COLUMN_ZTR_DATA_POWROTU     => $_POST['data_powrotu_wwkp'],
            Model::COLUMN_ZTR_ID_DECYZJA       => (int)$_POST['decyzja_wwkp_id'],
            Model::COLUMN_ZTR_ID_MSC_ODJAZD    => isset($_POST['msc_odjazd_wwkp_id']) ? (int)$_POST['msc_odjazd_wwkp_id'] : null,
            Model::COLUMN_ZTR_ID_MSC_POWROT    => isset($_POST['msc_powrot_wwkp_id']) ? (int)$_POST['msc_powrot_wwkp_id'] : null,
            Model::COLUMN_ZTR_ID_BILET         => isset($_POST['bilet_wwkp_id']) ? (int)$_POST['bilet_wwkp_id'] : null,
            Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_WYJAZD => $idDeparturePlan,
            Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_POWROT => $idDepartureReturnPlan,
            'id_miejsca_docelowe'              => (int)$_POST['destination_wysw_wyb_klient_pas'],
            'id_osoby_kontaktowe'              => (int)$_POST['contactPerson_wysw_wyb_klient_pas'],
            'id_forma_platnosci'               => (int)$_POST['id_forma_platnosci_wwkp_id'],
            'id_ticket_state'                  => (int)$_POST['id_stan_realizacji_wwkp_id'],
            );

            $dalZatrudnienie->set($data);

            $query = "update ".$daneOs->tableName." SET  data='".$_POST['data_wyjazdu_wwkp']."', ilosc_tyg='".(int)$_POST['ilosc_tyg_wwkp']."'
                where ".$daneOs->tableId." = ".$id_osoba.";";
             
            if (ColisionDetection($id_osoba, $_POST[ID_ZATRUDNIENIE], $_POST['data_wyjazdu_wwkp'], $_POST['data_powrotu_wwkp'], $db, $controls) == enum::$ALLOWDATA)
            {
                if ($_POST['data_wyjazdu_wwkp'] < $_POST['data_powrotu_wwkp'])
                {
                    TriggerLogic($query, $dzis, $_POST['data_wyjazdu_wwkp'], $_POST['data_powrotu_wwkp'], $_POST['status_wwkp_id'], $id_osoba, isset($_POST['msc_odjazd_wwkp_id']) ? $_POST['msc_odjazd_wwkp_id'] : null, $controls, $db, $wyodrMsc);
                    $result = $controls->dalObj->pgQuery($query);
                    
                    echo 'Umówiono osobê.<script>PassValsToOpener("datawyjazd,tygodnie", "'.$_POST['data_wyjazdu_wwkp'].','.$_POST['ilosc_tyg_wwkp'].'"); 
                    window.close();</script>'; 
                    //die();
                }
                else
                {
                    echo 'B³±d spójno¶ci danych, nie uaktualniono (data wyjazdu nie mo¿e byæ wiêksza od daty powrotu).';
                }
            }
            else
            {
                echo 'Zatrudnienie zachodzi terminami z innym zatrudnieniem.';
            }
        }
        else
        {
            echo '<label class="error">Formularz niekompletny. ';

            if (!$_POST['ilosc_tyg_wwkp'] > 0)
            echo 'Podaj Ilo¶æ tygodni. ';

            if (!strlen($_POST['data_powrotu_wwkp']) == 10)
            echo 'Podaj datê powrotu.';

            if (!strlen($_POST['data_wyjazdu_wwkp']) == 10)
            echo 'Podaj datê wyjazdu.';

            echo '</label>';
        }
    }
    $zapytanie = "select imie, nazwisko from dane_osobowe
        where dane_osobowe.id = '".$wiersz['id_osoba']."';";
    $wynik1 = $controls->dalObj->pgQuery($zapytanie);
    $wiersz1 = pg_fetch_array($wynik1);
    //bierzacy rekord zatrudnienia
    $result = $dalZatrudnienie->get($wiersz['id']);
    $row = $result[Model::RESULT_FIELD_DATA][0];

    echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'" id="wysw_wyb_klient_pas"><table>';
    echo $controls->AddSelectHelpHidden();
    echo $controls->AddHidden(ID_OSOBA, ID_OSOBA, $id_osoba);
    echo $controls->AddHidden(ID_ZATRUDNIENIE, ID_ZATRUDNIENIE, $row['id']);
    echo '<tr><td>Nazwisko klienta:</td><td>'.$wiersz1['nazwisko'].'</td></tr>';
    echo '<tr><td>Imiê klienta:</td><td>'.$wiersz1['imie'].'</td></tr>';

    echo "<tr><td>Konsultant:</td><td>".$row['imie_nazwisko']."</td></tr>";
    echo "<tr><td>Data zapisu:</td><td>".$row['data_wpisu']."</td></tr>";

    //doswiadczenie zawodowe osoby
    $query = "select distinct id_oddzial as ".$oddzial->tableId.", ".$db->tableName.".id_klient,
        ".$klient->tableName.".nazwa_alt || ', ' || ".$oddzial->tableName.".nazwa as nazwa
        from ".$db->tableName."
        join ".$oddzial->tableName." on ".$db->tableName.".id_oddzial = ".$oddzial->tableName.".".$oddzial->tableId."
        join ".$klient->tableName." on ".$db->tableName.".id_klient = ".$klient->tableName.".".$klient->tableId."
        where id_oddzial in 
        (select distinct id_oddzial from ".$db->tableName." where id_osoba = '".$id_osoba."' and id_status in 
        (select ".$status->tableId." from ".$status->tableName." where nazwa in ('Pasywny','Aktywny')));";

    //<tr>Poni¿ej widnieje lista klientów, u których osoba jest pasywna. Do tych klientów mo¿na ponownie umówiæ osobê.</tr>

    echo '<tr><td>Klient:</td><td>';
    //echo $controls->AddSelectRandomQuerySVbyId("klient_wwkp", "klient_wwkp", "", $query, $row['id_oddzial'], "oddzial_wwkp_id", $klient_const_name, $oddzial->tableId, "");

    $vacatsList = $controls->dalObj->PobierzDane($query);

    $addsList = array(
            'klient_id_wwkp' => 'id_klient',
            'oddzial_wwkp_id' => 'id',
    );

    echo $htmlControls->_AddSelect("klient_wwkp", "klient_wwkp", $vacatsList, $row['id_oddzial'], "oddzial_wwkp_id", true, '', '', '', $addsList, "employmentHistory.setWakatDropdowns('wysw_wyb_klient_pas'); ");
    echo '</td></tr><tr><td>Status:</td><td>';
    $query = "select ".$status->tableId.", nazwa from ".$status->tableName." where nazwa in ('Aktywny','Pasywny','Wyje¿d¿aj±cy') order by nazwa asc;";
    echo $controls->AddSelectRandomQuerySVbyId("id_status_wwkp", "id_status_wwkp", "", $query, $row['id_status'], "status_wwkp_id", "nazwa", $status->tableId, "");
    echo '</td></tr><tr><td>Data wyjazdu:</td><td>';
    echo $controls->AddDateboxFuture("data_wyjazdu_wwkp", "data_wyjazdu_wwkp", $row['data_wyjazdu'], 10, 10,
    JsEvents::ONCHANGE.'="utils.resetSelect(\'id_przewoznik_wwkp\', \'przewoznik_id_wwkp\'); utils.getElementById(\'msc_odjazd_wwkp_container\').innerHTML = \'\';"');
    echo '</td></tr><tr><td>Ilo¶æ tygodni:</td><td>';
    echo $controls->AddNumberbox("ilosc_tyg_wwkp", "tygodnie", $row['ilosc_tyg'],  2, 3, "sprawdz_tygodnie(this)");
    echo '</td></tr><tr><td>Data powrotu:</td><td>';
    echo $controls->AddDateboxFuture("data_powrotu_wwkp", "data_powrotu_wwkp", $row['data_powrotu'], 10, 10);
    echo strftime('%A', strtotime($row['data_powrotu']));
    echo '</td></tr><tr><td>Decyzja:</td><td>';
    echo $controls->AddSelectRandomQuerySVbyId("id_decyzja_wwkp", "id_decyzja_wwkp", "", "select id, nazwa from decyzja order by nazwa asc;", $row['id_decyzja'], "decyzja_wwkp_id", "nazwa", "id", "");

    echo '</td></tr><tr><td>Przewo¼nik:</td><td>';

    $przewoznik_id = !empty($row[Model::COLUMN_RJA_ID_PRZEWOZNIK_WYJAZD]) ? $row[Model::COLUMN_RJA_ID_PRZEWOZNIK_WYJAZD] : null;
    $przewoznik_powrot_id = !empty($row[Model::COLUMN_RJA_ID_PRZEWOZNIK_POWROT]) ? $row[Model::COLUMN_RJA_ID_PRZEWOZNIK_POWROT] : null;
    $rozklad_jazdy_id = !empty($row[Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_WYJAZD]) ? $row[Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_WYJAZD] : null;
    $rozklad_jazdy_powrot_id = !empty($row[Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_POWROT]) ? $row[Model::COLUMN_ZTR_ID_ROZKLAD_JAZDY_POWROT] : null;

    echo $controls->AddSelectRandomQuerySVbyId('przewoznik', 'id_przewoznik_wwkp', '',
            "select null as id, '--------' as nazwa union select id, nazwa from przewoznik order by nazwa asc;", 
    $przewoznik_id, 'przewoznik_id_wwkp', 'nazwa', 'id',
            'getDepartureCityCombo(utils.getElementValue(\'data_wyjazdu_wwkp\'), utils.getElementValue(\'przewoznik_id_wwkp\'), \'msc_odjazd_wwkp_container\', \'wwkp\', \'msc_odjazd\', \'id_msc_odjazd\', 0);
            getTicketsCombo(utils.getElementValue(\'przewoznik_id_wwkp\'), \'wwkp\', \'bilet_wwkp_container\');');

    //przy wrzucaniu na pasywnego nie ma mowy o decyzji, wszyscy sa umowieni, bo to nie dodawanie do wakatu
    echo '</td></tr></td></tr><tr><td>Miejscowo¶æ odjazdu:</td><td id="msc_odjazd_wwkp_container">';

    if($rozklad_jazdy_id)
    {
        $dzien = strftime('%w', strtotime($row['data_wyjazdu']));

        if ($przewoznik_id == PRZEWOZNIK_BARTUS_ID) //bartus
        {
            if ($dzien < CZWARTEK_ID)
            $dzien = PONIEDZIALEK_ID;
            else
            $dzien = PIATEK_ID;
        }

        if ($przewoznik_id == PRZEWOZNIK_SOLTYSIK_ID)
        {
            if ($dzien != 0 && $dzien != PONIEDZIALEK_ID && $dzien != PIATEK_ID)
            {
                if ($dzien < CZWARTEK_ID)
                    $dzien = PONIEDZIALEK_ID;
                else
                    $dzien = PIATEK_ID;
            }
        }

        $query = 'select rozklad_jazdy.id,  rozklad_jazdy.id_msc_odjazdu, 
        msc_odjazdu.nazwa || \', \' || rozklad_jazdy.godzina || \', \' || rozklad_jazdy.przystanek as nazwa
            from rozklad_jazdy join msc_odjazdu on rozklad_jazdy.id_msc_odjazdu = msc_odjazdu.id where 
            rozklad_jazdy.id_przewoznik = '.$przewoznik_id.
            ' and rozklad_jazdy.dzien = '.$dzien.' and active = true order by nazwa asc; ';

        echo $controls->AddSelectRandomQuerySVbyId('rozklad_jazdy_wwkp', 'id_rozklad_jazdy_wwkp', '', $query, $rozklad_jazdy_id, 
        	'rozklad_jazdy_wwkp_id', 'nazwa', 'id', 
        	'utils.setHiddenFromSelectLabel(this, \'msc_odjazd_wwkp_id\');', 'id_msc_odjazdu', 'msc_odjazd_wwkp_id');
    }

    echo '</td></tr><tr><td>Rodzaj biletu:</td><td id="bilet_wwkp_container">';
    if ($przewoznik_id > 0)
    {
        $query = 'select id, nazwa from bilety where id_przewoznik = '.$przewoznik_id.' order by nazwa asc;';
        echo $controls->AddSelectRandomQuerySVbyId("id_bilet_wwkp", "id_bilet_wwkp", "", $query, $row['id_bilet'], "bilet_wwkp_id", "nazwa", "id", "");
    }

    echo '</td></tr><tr><td>Forma p³atno¶ci:</td><td id="forma_platnosci_wwkp_container">';
        
    $query = 'select 0 AS id, \'--------\' AS nazwa UNION select id, nazwa from forma_platnosci order by nazwa asc';
    echo $controls->AddSelectRandomQuerySVbyId("id_forma_platnosci_wwkp", "id_forma_platnosci_wwkp", "", $query, $row['id_forma_platnosci'], "id_forma_platnosci_wwkp_id", "nazwa", "id", "");
    
    echo '</td></tr><tr><td>Stan realizacji:</td><td id="stan_realizacji_wwkp_container">';
        
    $query = "select 0 AS id, '--------' AS nazwa UNION select id, nazwa from stan_realizacji order by nazwa asc";
    echo $controls->AddSelectRandomQuerySVbyId("id_stan_realizacji_wwkp", "id_stan_realizacji_wwkp", "", $query, $row['id_ticket_state'], "id_stan_realizacji_wwkp_id", "nazwa", "id", "");
    
    echo '</td></tr><tr><td>Przewo¼nik powrót:</td><td>';
    echo $controls->AddSelectRandomQuerySVbyId('przewoznik', 'id_przewoznik_powrot_wwkp', '',
            "select null as id, '--------' as nazwa union select id, nazwa from przewoznik order by nazwa asc;", 
            $przewoznik_powrot_id, 'przewoznik_id_wwkp', 'nazwa', 'id',
            'getDepartureCityCombo(utils.getElementValue(\'data_wyjazdu_wwkp\'), utils.getElementValue(\'przewoznik_id_wwkp\'), \'msc_powrot_wwkp_container\', \'powrot_wwkp\', \'msc_powrot\', \'id_msc_powrot\', 1);');
    
    echo '</td></tr></td></tr><tr><td>Miejscowo¶æ powrotu:</td><td id="msc_powrot_wwkp_container">';
    
    if($rozklad_jazdy_powrot_id)
    {
        $dzien = strftime('%w', strtotime($row['data_wyjazdu']));

        if ($przewoznik_powrot_id == PRZEWOZNIK_BARTUS_ID) //bartus
        {
            if ($dzien < CZWARTEK_ID)
            $dzien = PONIEDZIALEK_ID;
            else
            $dzien = PIATEK_ID;
        }

        if ($przewoznik_powrot_id == PRZEWOZNIK_SOLTYSIK_ID)
        {
            if ($dzien != PONIEDZIALEK_ID && $dzien != PIATEK_ID)
            {
                if ($dzien < CZWARTEK_ID)
                    $dzien = PONIEDZIALEK_ID;
                else
                    $dzien = PIATEK_ID;
            }
        }

        $query = 'select rozklad_jazdy.id,  rozklad_jazdy.id_msc_odjazdu as id_msc_powrotu, 
        msc_odjazdu.nazwa || \', \' || rozklad_jazdy.godzina || \', \' || rozklad_jazdy.przystanek as nazwa
            from rozklad_jazdy join msc_odjazdu on rozklad_jazdy.id_msc_odjazdu = msc_odjazdu.id where 
            rozklad_jazdy.id_przewoznik = '.$przewoznik_powrot_id.
            ' and rozklad_jazdy.dzien = '.$dzien.' and active = true order by nazwa asc; ';

        echo $controls->AddSelectRandomQuerySVbyId('rozklad_jazdy_wwkp', 'id_rozklad_jazdy_wwkp', '', $query, $rozklad_jazdy_powrot_id, 'rozklad_jazdy_powrot_wwkp_id', 'nazwa', 'id', 'utils.setHiddenFromSelectLabel(this, \'msc_powrot_wwkp_id\');', 'id_msc_powrotu', 'msc_powrot_wwkp_id');
    }
    
    echo '</td></tr><tr><td>Miejsce docelowe:</td><td id="msc_docelowe_container">';
    echo '</td></tr><tr><td>Osoba kontaktowa:</td><td id="osoba_kontaktowa_container">';
    echo '</td></tr><tr><td colspan="2">';
    echo $controls->AddSubmit($update, $row['id'], "Aktualizuj.", "onclick=\"utils.setHiddenOnLoad('destination_wysw_wyb_klient_pas,contactPerson_wysw_wyb_klient_pas', 'id_destination_wysw_wyb_klient_pas,id_contactPerson_wysw_wyb_klient_pas');\"");
    echo $controls->AddSubmit($delete, $row['id'], "Usuñ.", '');
    echo "<button class=\"formreset\" onclick=\"window.open('/prawa_strona/bilety.php?id={$wiersz['id']}')\">Poka¿ bilet</button></td></tr></table></form>";
    echo '</td></tr></table></form>';

    echo "\n\n\n<script>"
                    . "if (typeof employmentHistoryData == 'undefined') {  employmentHistoryData = {}; }"
                    . "employmentHistoryData.wysw_wyb_klient_pas = {}; "
                    . "employmentHistoryData.wysw_wyb_klient_pas.wakatDropdownsSelector = '#klient_wwkp';"
                    . "employmentHistoryData.wysw_wyb_klient_pas.destinationId = " . ($row['id_miejsca_docelowe'] ? $row['id_miejsca_docelowe'] : 0) .";"
                    . "employmentHistoryData.wysw_wyb_klient_pas.contactPerson = ". ($row['id_osoby_kontaktowe'] ? $row['id_osoby_kontaktowe'] : 0) .";"
            . "</script>\n\n";
    
    echo $htmlControls->_AddNoPrivilegeSubmit('', '', 'Wewnetrzny opis pracy', '', '
        onclick="var url = \'wewn_opis_pracy.php?'.Model::COLUMN_OPR_ID.'=\' + utils.getElementById(\'oddzial_wwkp_id\').value;
        window.open(url, \'wewnetrzny_opis_pracy\', \'toolbar=no, scrollbars=yes, width=750,height=650\');"
        ', 'button');
}
?>