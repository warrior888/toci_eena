<?php 

    require_once 'dal/DALDaneOsobowe.php';
    
    $dal = dal::getInstance();
    $i = 0;
    $offset = 0;
    $shift = 1000;
    
    do 
    {
    
    $query = 'select * from poprzedni_pracodawca where id_oddzialy_klient is null and id_wiersz < 20000 limit '.$shift.' offset '.$offset.';';
    $offset += $shift;
    
    $results = $dal->PobierzDane($query, $iloscRekordow);
    
    $dalDaneOsobowe = new DALDaneOsobowe();
    
    $wlkBrytania = 'Wielka Brytania';
    $austria = 'Austria';
    $belgia = 'Belgia';
    $czechy = 'Czechy';
    $dania = 'Dania';
    $grecja = 'Grecja';
    $francja = 'Francja';
    $holandia = 'Holandia';
    $hiszpania = 'Hiszpania';
    $irlandia = 'Irlandia';
    $islandia = 'Islandia';
    $luksemburg = 'Luksemburg';
    $niemcy = 'Niemcy';
    $norwegia = 'Norwegia';
    $polska = 'Polska';
    $portugalia = 'Portugalia';
    $slowenia = 'S這wenia';
    $szkocja = 'Szkocja';
    $szwecja = 'Szwecja';
    $usa = 'USA';
    $wlochy = 'W這chy';
    
    $countryMigrationMap = array(
        'Anglia'			=> $wlkBrytania,
        'ANGLIA'			=> $wlkBrytania,
        'Austria'			=> $austria,
        'Austrii'			=> $austria,
        'Belgia'			=> $belgia,
        'Czechy'			=> $czechy,
        'Dania'				=> $dania,
        'England'		    => $wlkBrytania,
        'francja'			=> $francja,
        'Francja'			=> $francja,
        'Grecja'			=> $grecja,
        'Halandia'			=> $holandia,
        'Hiszpania'			=> $hiszpania,
        'HISZPANIA'			=> $hiszpania,
        'Holadnia'			=> $holandia,
        'Holandi'			=> $holandia,
        'holandia'			=> $holandia,
        'hOLANDIA'			=> $holandia,
        'Holandia'			=> $holandia,
        'HOLANDIA'			=> $holandia,
        'Holandia,Niemcy'   => $holandia,
        'Holland'			=> $holandia,
        'IRELAND'			=> $irlandia,
        'Irlandia'			=> $irlandia,
        'Islandia'			=> $islandia,
        'Luksemburg'		=> $luksemburg,
        'niemcy'			=> $niemcy,
        'Niemcy'			=> $niemcy,
        'NIemcy'			=> $niemcy,
        'Niemcy, Hiszpania'	=> $niemcy,
        'Niemczech'			=> $niemcy,
        'Nimcy'				=> $niemcy,
        'Nl'				=> $holandia,
        'NL'				=> $holandia,
        'Norwegia'			=> $norwegia,
        'Olkusz'			=> $polska,
        'PL'				=> $polska,
        'Polksa'			=> $polska,
        'polska'			=> $polska,
        'Polska'			=> $polska,
        'PolskA'			=> $polska,
        'POlska'			=> $polska,
        'POLSKA'			=> $polska,
        'Polskaa'			=> $polska,
        'Polska/Holandia/Niemcy' => $polska,
        'Polska/Niemcy/Anglia'	=> $polska,
        'Polskie'			=> $polska,
        'Portugalia'		=> $portugalia,
        'Praha'				=> $czechy,
        'Rzesz闚'			=> $polska,
        'S這wenia'			=> $slowenia,
        'Szkocja'			=> $szkocja,
        'SZKOCJA'           => $szkocja,
        'Szwecja'			=> $szwecja,
        'SZWECJA'			=> $szwecja,
        'UK'				=> $wlkBrytania,
        'USA'				=> $usa,
        'Wielka Brytania UK'	=> $wlkBrytania,
        'Wlk. Bryt.'		=> $wlkBrytania,
        'w這chy'			=> $wlochy,
        'W這chy'			=> $wlochy,
    );
    
    foreach ($results as $result)
    {
        list ($country, $city, $firmName, $department, $position, $period) = explode('/', $result['nazwa']);
        
        $country = $countryMigrationMap[$country];
        
        $personId = $result['id'];
        $rowId = $result['id_wiersz'];
        $occupationId = $result['id_grupa_zawodowa'];
        $dalDaneOsobowe->setFormerEmployer($personId, $result['nazwa'], $country, $city, $firmName, '', $occupationId, $rowId);
        $i++;
        //if ($i > 1)
        //    die($rowId);
    }
    } while ($iloscRekordow > 0);
    
    echo 'Ilosc wierszy: '.$i;
    
    // 63131