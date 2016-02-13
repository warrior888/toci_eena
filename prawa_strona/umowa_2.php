<?php
    if (empty($_SESSION['uzytkownik']))
    {
        die('Nieprawidłowe żądanie');
    }
    else
    {
        ///skrypt partialowy, pociagany z umowy, niezdolny do samodzielnej egzystencji :P
        if (empty($rowDeal))
            die('Złe wywołanie');
            
        require_once 'bll/HtmlToPdfManager.php';
            
        $controls = unserialize($_SESSION['controls']);
       
        //echo("<body onLoad = 'window.print();'>");
        
        $db = new zatrudnienie();
        $wakat = new wakaty();
        $oddzial = new oddzial();
        $status = new status();

        $insertDeal = "insert into umowa_ewidencja values (nextval('umowa_ewidencja_id_seq'), ".$id_osoba.", ".$rowDeal['id_zatrudnienie'].", (select id from uprawnienia where nazwa_uzytkownika = '".$_SESSION['uzytkownik']."'), '".$date."');";
        //GOWNO !!! id_wakat w tabeli umowa ewidencja w rzeczywistosci zostal przemianowany na id_zatrudnienie i taka jest tam referencja
        //TODO - migracja !
        $presenceTest = "select id from umowa_ewidencja where id_wakat = ".$rowDeal['id_zatrudnienie'].";";
        $resTest = $controls->dalObj->PobierzDane($presenceTest, $recordsCount);
        if ($recordsCount == 0)
        {
            //result unique not to overwrite next one
            $resUN = $controls->dalObj->pgQuery($insertDeal);
        }
        
        $zapytanie = "select ".$oddzial->tableId." as id,wiekowe from ".$oddzial->tableName." where ".$oddzial->tableId." = 
        (select id_oddzial from ".$db->tableName." where ".$db->tableName.".".$db->tableId." = ".$rowDeal['id_zatrudnienie'].");";
        $wynik = $controls->dalObj->pgQuery($zapytanie);

        $tab = explode('-', $rowDeal['data_urodzenia']);
        $wiek = oblicz_wiek($tab[0], $tab[1], $tab['2']);
        $KlientInf = pg_fetch_array($wynik);
        if ($KlientInf['wiekowe'] == 1)
        {
            $TestWiek = "select max(wiek) as wiek_max, min(wiek) as wiek_min from oddzial_stawki where id_oddzial = ".$KlientInf['id'].";";
            $wynik = $controls->dalObj->pgQuery($TestWiek);
            $WiekProg = pg_fetch_array($wynik);
            if ($wiek > $WiekProg['wiek_max'])
            {
                $ZapStawka = "select stawka from ".$oddzial->tableName." where id = ".$KlientInf['id'].";";
                $wynik = $controls->dalObj->pgQuery($ZapStawka);
                $StawkaArr = pg_fetch_array($wynik);
                $StawkaPrac = $StawkaArr['stawka'];
            }
            else
            {
                if ($wiek <= $WiekProg['wiek_min'])
                {
                    $ZapStawka = "select stawka from oddzial_stawki where id_oddzial = ".$KlientInf['id']." and wiek = ".$WiekProg['wiek_min'].";";
                    $wynik = $controls->dalObj->pgQuery($ZapStawka);
                    $StawkaArr = pg_fetch_array($wynik);
                    $StawkaPrac = $StawkaArr['stawka'];
                }
                else
                {
                    $ZapStawka = "select stawka from oddzial_stawki where id_oddzial = ".$KlientInf['id']." and wiek = ".$wiek.";";
                    $wynik = $controls->dalObj->pgQuery($ZapStawka);
                    $StawkaArr = pg_fetch_array($wynik);
                    $StawkaPrac = $StawkaArr['stawka'];
                }
            }
        }
        else
        {
            $ZapStawka = "select stawka from ".$oddzial->tableName." where id = ".$KlientInf['id'].";";
            $wynik = $controls->dalObj->pgQuery($ZapStawka);
            $StawkaArr = pg_fetch_array($wynik);
            $StawkaPrac = $StawkaArr['stawka'];
        }     

        $persPrefs = array(
            ID_PLEC_MEZCZYZNA => array(
                'panem' => 'Panem',
                'zamieszkalym' => 'zamieszkałym',
            ),
            ID_PLEC_KOBIETA => array(
                'panem' => 'Panią',
                'zamieszkalym' => 'zamieszkałą',
            )
        );
        
        //txt umowy start 
        
        $manager = new HtmlToPdfManager();
        
            $WydUm = "select imie_nazwisko from uprawnienia where nazwa_uzytkownika = '".$_SESSION['uzytkownik']."';";
            $konsultant = $controls->dalObj->PobierzDane($WydUm);
            $rowDeal['konsultant'] = $konsultant[0]['imie_nazwisko'];
            
            $agreementHtml = '';
            // iconv
            $agreementHtml .= '<div align="RIGHT">Opole, dnia '.$date.'r.</div>
            <div align="CENTER">UMOWA</div>
            <div>Niniejszym potwierdza się zawarcie umowy pomiędzy:</div>
            <div>'.$persPrefs[$rowDeal['id_plec']]['panem'].' '.iconv('ISO-8859-2', 'UTF-8', $rowDeal['imie']).' '.iconv('ISO-8859-2', 'UTF-8', $rowDeal['nazwisko']).
            ', ur. '.$rowDeal['data_urodzenia'].' w miejscowości '.iconv('ISO-8859-2', 'UTF-8', $rowDeal['msc_ur'])
            .', '.$persPrefs[$rowDeal['id_plec']]['zamieszkalym'].' w miejscowości '.$rowDeal['kod'].' '.iconv('ISO-8859-2', 'UTF-8', $rowDeal['msc']).', ul. '.
            iconv('ISO-8859-2', 'UTF-8', $rowDeal['ulica']).' jako osobę kierowaną do pracy za granicą u zagranicznego pracodawcy, zwaną dalej "Kandydatem"</div>';
            
            $agreementHtml .= "<div>a E&A Sp. z o.o. z siedzibą w 45-064 Opolu przy ul. Kołłątaja 3/1, wpisaną do Krajowego Rejestru Sądowego pod nr 0000175518, posiadającą - jako podmiot prowadzący agencję zatrudnienia - certyfikat KRAZ nr 385, reprezentowaną przez
Pośrednika pracy o godności: ".iconv('ISO-8859-2', 'UTF-8', $rowDeal['konsultant']).", 
zwanego dalej \"Agencją\",</div>
            <div>o następującej treści i warunkach:</div>
            <div>Agencja umożliwi Kandydatowi podjęcie pracy u zagranicznego pracodawcy na następujących warunkach:</div>";

            $agreementHtml .= '<div>1.  Pracodawca zagraniczny: E&A Uitzendbureau, Emma Goldmanweg 8h, 5032 MN Tilburg / oddział: '.$rowDeal['biuro'].' Belgia<br /><br />
2.  Okres zatrudnienia: Umowa o pracę pracownika czasowego zostanie zawarta z zagranicznym pracodawcą na podstawie przepisów prawa belgijskiego, 
minimum '.$rowDeal['ilosc_tyg'].' tygodni.
<br />
3.  Warunki zatrudnienia u zagranicznego pracodawcy:<br />
-    funkcja/sektor:         '.iconv('ISO-8859-2', 'UTF-8', $rowDeal['stanowisko']).',<br />
-    data rozpoczęcia:        '.$rowDeal['data_wyjazdu'].',<br />
stawka godzinowa brutto:    według pracodawcy użytkownika: '.iconv('ISO-8859-2', 'UTF-8', $StawkaPrac).' &#8364;.<br /> 
Dodatki/potrącenia:';
            $WarZatr = "select warunki_zatrudnienia.nazwa as nazwa, warunki_zatrudnienia.szczegoly as szczegoly from warunki_zatrudnienia 
            join warunki_oddzial on warunki_zatrudnienia.id = warunki_oddzial.id_warunek 
            join ".$oddzial->tableName." on ".$oddzial->tableName.".".$oddzial->tableId." = warunki_oddzial.id_oddzial 
            where ".$oddzial->tableName.".".$oddzial->tableId." = ".$KlientInf['id']." order by warunki_zatrudnienia.nazwa asc;";
            $wynik = $controls->dalObj->pgQuery($WarZatr);

            while ($WarKlient = pg_fetch_array($wynik))
            {
                $agreementHtml .= "            - ".iconv('ISO-8859-2', 'UTF-8', $WarKlient['nazwa']).": ".iconv('ISO-8859-2', 'UTF-8', $WarKlient['szczegoly']).",<br />";
            }
            //- liczniki (opomiarowane media: woda, prąd, gaz).<br />
$agreementHtml .= "</div>";
$ubezpieczenie = 'Zgodnie z belgijskim systemem ubezpieczeń społecznych (mutualiteit) . Zawierane samodzielnie i płatne  przez pracownika raz w roku.';

            $agreementHtml .= '<div>
4. Warunki ubezpieczenia społecznego:
<br />
-    Ubezpieczenie zdrowotne: 
<br />'.$ubezpieczenie
.'<br />

-    Ubezpieczenie emerytalne: 
<br />

Odprowadzane od 1go dnia pracy w składkach RSZ (przyp. składki na ubezp. społ;)
<br />

-    Ubezpieczenie od następstw nieszczęśliwych wypadków (i chorób tropikalnych):
<br />

Pracodawca belgijski nie ma obowiązku objęcia tym ubezpieczeniem pracowników; pozostaje ono w gestii kandydata.
<br />
5. Obowiązki i uprawnienia Kandydata: <br />
     -  podjęcia pracy przyrzeczonej zgodnie z powyższymi warunkami,<br /> 
     -  staranne wywiązywanie się z nałożonych obowiązków i zdyscyplinowanie w stosunku do<br /> 
        zaleceń przełożonych z zachowaniem przepisów bezpieczeństwa i higieny pracy,<br />
     -  poinformowanie Agencji o każdorazowej zmianie pracodawcy Użytkownika w okresie trwania umowy,<br />
     -  zgłoszenie przerwania pracy z 1-dniowym wyprzedzeniem, ze skutkiem pokrycia <br />
        kosztów administracyjnych,<br />
     -  odcinek wypłaty, <br />
     -  posiadanie i dysponowanie kartą płatniczą na terenie Belgii jest obowiązkowe,
<br />
   
   Obowiązki i uprawnienia Agencji:<br />
-  zapewnienie u pracodawcy za  granicą pracy przyrzeczonej Kandydatowi,<br />
-  zadbanie o podpisanie umowy o pracę czasową z kandydatem przez pracodawcę na warunkach nie gorszych niż w niniejszej umowie,<br />
-  reagowania na nieprawidłowości zgłaszane przez pracowników w trakcie ich realizacji,<br />
-  wsparcie Kandydata w znalezieniu nowego zlecenia, w przypadku rozwiązania umowy przez pracodawcę-użytkownika<br />
<br />
6. Zakres odpowiedzialności cywilnej stron w przypadku niewykonania lub nienależytego 
     wykonania umowy:<br />
a)   Jeżeli pracodawca uchyla się od zawarcia umowy o pracę, Kandydat może dochodzić od Agencji zawarcia umowy o pracę na warunkach nie gorszych niż warunki wynikające z niniejszej umowy; w przypadku braku takiej możliwości Kandydat może żądać odszkodowania w postaci zwrotu kosztów przejazdu,<br />
b)   Agencji przysługuje prawo żądania odszkodowania od Kandydata w wysokości poniesionych kosztów rekrutacji danego Kandydata w przypadku, gdy:<br />
- Kandydat uchyla się od zawarcia umowy o pracę z wskazanym Pracodawcą Zagranicznym na warunkach nie gorszych niż wskazane,<br />
- Kandydat rozwiąże umowę w trakcie jej realizacji bez uzasadnionej przyczyny i bez uprzedniego kontaktu lub negocjacji z Agencją,<br />
- wskutek naruszenia zasad współżycia międzyludzkiego, nadużywania alkoholu, przemocy, czy innych niestosownych zachowań z winy Kandydata, Pracodawca Zagraniczny odstąpi od podpisania z nim umowy o pracę za granicą lub umowę taką - już zawartą - rozwiąże ze skutkiem natychmiastowym w okresie 14 dni od daty jej zawarcia,<br />    
- Kandydat zatai fakt bycia karanym lub złoży nieprawdziwe oświadczenie o niekaralności. <br />

c)  Wszelkiego rodzaju kary poniesione przez kandydata z jego winy nałożone przez dowolną instytucję uprawnioną do egzekwowania od kandydata odpowiedzialności karnej obciążają kandydata. W wypadku pokrycia przez agencję kosztów poniesionej przez kandydata kary, agencja ma prawo do odzyskania od kandydata poniesionych kosztów.<br />
d)   Jeżeli z winy kandydata ulegnie uszkodzeniu, dewastacji lub zniszczeniu mienie agencji, agencja ma prawo do odszkodowania ze strony kandydata w wysokości poniesionych przez agencję strat. <br />
e)   Okres pracy obowiązujący kandydata ustalony niniejszą umową może podlegać renegocjacji, jeśli kandydat przedstawi wystarczająco istotne i wiarygodne powody, aby renegocjować termin wypełnienia umowy.<br />
<br />
7. Agencja ani pracodawca belgijski  nie pobiera opłat od pracownika w związku ze 
skierowaniem go do pracy za granicą. Kandydat ponosi we własnym zakresie koszty, 
przejazdu pomiędzy Polską a Belgią, zaświadczenia o niekaralność wraz z pełnomocnictwem, 
koszty tłumaczenia dokumentów, a także badań lekarskich, a w razie ich poniesienia przez 
Agencję Pracownik zobowiązany jest do ich zwrotu w wysokości faktycznie poniesionej 
przez Agencję (art. 85 ust. 2 pkt 7a ustawy z dnia 20-04-2004 o promocji zatrudnienia i 
instytucjach rynku pracy). Jak też koszt uzyskania dokumentów potrzebnych do nostryfikacji dyplomu.
            </div>';    
            $z7 = "select dokumenty.pass_nr, dokumenty.data_waznosci, bank.nazwa as bank, dokumenty.nr_konta from dokumenty join bank on dokumenty.id_bank = bank.id where dokumenty.id = '".$id_osoba."';";
            $w7 = $controls->dalObj->pgQuery($z7);
            $r7 = pg_fetch_array($w7);
            $agreementHtml .= '<div>
8.  Kandydat oświadcza, że: <br />
- Założy rachunek bankowy w Belgii, na który będzie otrzymywał wynagrodzenie z tytułu  pracy,<br /> 
- dysponuje aktualnym do '.$r7['data_waznosci'].' dokumentem tożsamości o nr: '.$r7['pass_nr'].', 
zgodnie z którym spełnia tryb i warunek dopuszczenia go do pracy w Belgii,<br />
- nie figuruje w Krajowym Rejestrze Karnym.
<br /><br />

9.  Dodatkowe ustalenia: brak.<br /><br />

10. Kandydat jest zobowiązany do zachowania w poufności wszelkich informacji uzyskanych w 
      związku z zatrudnieniem lub świadczeniem pracy. <br /><br />

11. Umowa o pracę z pracodawcą zagranicznym będzie zawarta na podstawie prawa belgijskiego, 
a wszelkie jej spory będą rozstrzygane w oparciu o Układ Zbiorowy Pracy dla pracowników czasowych, przepisy ustawy chorobowej opartej o wypłaty przez 
Zrzeszenie Ubezpieczeń Pracowniczych, oraz o Fundację Funduszu Emerytalnego Pracowników Czasowych.<br /><br />

12. Roszczenia wynikające z niniejszej umowy przedawniają się z upływem 14 dni od daty podpisania umowy 
z zagranicznym pracodawcą lub odpowiednio z upływem 3 miesięcy od dnia, w którym taka umowa o pracę miała być zawarta.<br />
Kandydat ma prawo wnieść swoje roszczenia wynikające z niewykonania lub nienależytego wykonania umowy w stosunku do Agencji Zatrudnienia w formie pisemnej na adres siedziby Agencji Zatrudnienia w terminie 14 dni od daty podpisania umowy z pracodawcą zagranicznym.<br /><br />

13. Udokumentowane okresy zatrudnienia obywateli polskich za granicą 
      u pracodawców zagranicznych są zaliczane do okresów pracy w Rzeczypospolitej Polskiej w zakresie uprawnień pracowniczych.<br /><br />

14. Wyrażam zgodę na przetwarzanie moich danych osobowych zawartych w niniejszym formularzu dla potrzeb realizacji projektu rekrutacji zgodnie Ustawą z dnia 29 sierpnia 
      1997 r o ochronie danych osobowych. ( Dz. U. Nr 133, poz. 883). Przyjmuję do wiadomości, że przysługuje mi prawo wglądu do treści moich danych, prawo ich poprawiania 
      i uzupełniania oraz wniesienia żądania o zaprzestaniu ich przetwarzania.<br /><br />
  
15. Wszelkie spory mogące wyniknąć na tle niniejszej umowy rozstrzygane będą przez sąd 
      właściwy miejscowo dla siedziby Agencji.<br /><br />

16. Umowę niniejszą sporządzono w języku polskim, w dwóch jednobrzmiących egzemplarzach, 
      po jednym dla Agencji i Kandydata.<br /><br /></div>';
            $agreementHtml .= '<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Agencja&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Kandydat</div>';
        
        $descHasNewPage = false;
        if ($pokazUmowa) {
            
            $manager->setHtml($agreementHtml);
        }
        
        if ($umowaBlank) {
            
            $manager->AddPage();
            
            if ($pokazZaswiadczenie || $pokazOpis) //inaczej trafi w ta strone (extra dodana) z nastepnym html
                $manager->AddPage();
                
            $descHasNewPage = true;
        }
        
        $zaswHtml = '';
        
        if ($pokazZaswiadczenie === true) {
            
            $zaswHtml = '<img height="890" src="/zdj/krk.png"></img>';
            $manager->setHtml($zaswHtml);
            
            $zaswHtml = '';
            if ($zaswiadczenieBlank) {
                
                $manager->AddPage();
                $manager->AddPage();
            } 
            
            $zaswHtml .= '<img height="890" src="/zdj/pelnomocnictwo.png"></img>';
            $manager->setHtml($zaswHtml);
            
            if ($zaswiadczenieBlank) {
                
                $manager->AddPage();
                
                if ($pokazOpis)
                    $manager->AddPage();
                    
                $descHasNewPage = true;
            }
        }
        
        //$manager->setHtml($agreementHtml);
        
        if ($pokazOpis) {
            
            if (!$descHasNewPage)
                $manager->AddPage(); 
                        
            $descriptionHtml = '';
            $indentation = '<br /><br /><br /><br /><br />';
            
            if ($pokazZaswiadczenie !== true)
                $indentation = '<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />';
                
            //$indentation.
            $descriptionHtml .= '<div align="center"><b>Opis pracy</b><br /><br /></div>';
        
            $descriptionHtml .= $rowDeal['opis']; 
            
            $manager->setHtml($descriptionHtml, true);
        }
        
        $manager->OutputPdf();
        
        //echo $agreementHtml;

        //echo '</body>'; 

    }
?>