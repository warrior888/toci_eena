<table border="0">
<tr>
    <td style="width: 27%">Linia do Holandii<br/><br/>
        <img src="/zdj/soltysik-logo.png" style="margin: 10px;"><br/><br/>
        Na platformie<br/><br/>
        <img src="/zdj/sindbad_logo.png">
    </td>
    <td style="width: 50%"><h3 style="text-decoration: underline;">POTWIERDZENIE REZERWACJI BILETU</h3>
        
        <b><?="{$data['imie']} {$data['nazwisko']}, ur. {$data['data_urodzenia']}"?></b><br/>
        <b>Numer rezerwacji:</b> <?=$data['id_oddzial']?><?=$data['id_pracownik']?><?=$data['id']?><?=$data['id_osoba']?><br/>
        <b>Rodzaj biletu:</b> <?=$data['bilet'] ?><br/>
        <b>Cena:</b> <?=$data['price']?> zł<br/>
        <b>Forma płatności:</b> <?=$data['forma_platnosci'] ?><br/>
    </td>
    <td>
        <img src="/zdj/ea_logo_pdf.png">
    </td>
</tr>
</table>
<br/>
<hr/>
<h3>WYJAZD:</h3>
<h4>z: <?=$data['msc_odjazdu'] ?>, <?=$data['wyjazd_przystanek']?>, <?=$data['dzien_wyjazdu']?> <?=$data['data_wyjazdu'] ?>, godz. <?=$data['wyjazd_godzina'] ?></h4>

<h3>PRZYJAZD:</h3>
<h4>do: <?=$data['miasto_docelowe'] ?>, <?=$data['miejsce_docelowe'] ?>, <?=$data['dzien_przyjazdu']?> <?=$data['data_przyjazdu']?>, godz. <?=$data['godzina_przyjazdu']?> </h4>
<br/>
<span style="font-size: 8">Numer telefonu kontaktowego pod którym mogą Państwo uzyskać na kilka godzin przed 
odjazdem informacje na temat numeru bocznego autokaru na swoim przystanku 
to +48322387310 lub infolinia +48774434444 a po 17:00 +48728338878. 
<b>Podchodząc do autokaru platformy dopytujcie Państwo o swoje nazwisko na liście pasażerów.</b>
</span>
<? if ($data['bilet'] == 'Obustronny' || $data['bilet'] == 'Założenie dwustronne') : ?>
<h3>WYJAZD:</h3>
<h4>z: <?=$data['miasto_docelowe'] ?>, <?=$data['wyjazd_przystanek']?>, <?=$data['miejsce_docelowe'] ?>, "OPEN", godz. odjazdu: <?=$data['godzina_wyjazdu_powrot']?> </h4>

<h3>PRZYJAZD:</h3>
<h4>do: <?=$data['msc_powrotu'] ?>, <?=$data['powrot_przystanek']?>, "OPEN", godz. przyjazdu <?=$data['powrot_godzina']?></h4>
<br/><br/>
<? else : ?>
<h3> </h3>
<h4> </h4>

<h3> </h3>
<h4> </h4>
<br/><br/>
<? endif; ?>

<!------------------------------------------------------- -->

<span style="font-size: 1px"> </span><br/>
<hr />
<? if ($data['bilet'] == 'Obustronny' || $data['bilet'] == 'Założenie dwustronne') : ?>
<span style="font-size: 1px"> </span><br/>

<table>
    <tr>
        <td><img src="/zdj/soltysik-logo.png" style="margin: 10px;"><br/>
            Na platformie <img src="/zdj/sindbad_logo.png">
        </td>
        <td style="text-align: center"><h4>ODCINEK NA POWRÓT Z HOLANDII</h4></td>
        <td rowspan="2" style="width: 35%; text-align: center; font-size: 8;"><br/><br/>Pieczęć biura wydającego
            <div style="border: 1px solid black;"><br/><br/><br/><br/><br/><br/>podpis</div>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="width: 65%"><b><?="{$data['imie']} {$data['nazwisko']}, ur. {$data['data_urodzenia']}"?></b><br/>
            <b>Numer rezerwacji:</b> <?=$data['id_oddzial']?><?=$data['id_pracownik']?><?=$data['id']?><?=$data['id_osoba']?> <br/>
            <b>Miejsce docelowe:</b> <?=$data['msc_powrotu'] ?>, <?=$data['powrot_przystanek']?>, "OPEN", godz. przyjazdu <?=$data['powrot_godzina']?>
        </td>
    </tr>
</table>
<!------------------------------------------------------- -->
<? endif; ?>
<hr />
<span style="font-size: 1px"> </span><br/>

<table>
    <tr>
        <td><img src="/zdj/soltysik-logo.png" style="margin: 10px;"><br/>
            Na platformie <img src="/zdj/sindbad_logo.png">
        </td>
        <td style="text-align: center"><h4>ODCINEK NA WYJAZD DO HOLANDII</h4></td>
        <td rowspan="2" style="width: 35%; text-align: center; font-size: 8;"><br/><br/>Pieczęć biura wydającego
            <div style="border: 1px solid black;"><br/><br/><br/><br/><br/><br/>podpis</div>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="width: 65%"><b><?="{$data['imie']} {$data['nazwisko']}, ur. {$data['data_urodzenia']}"?></b><br/>
            <b>Numer rezerwacji:</b> <?=$data['id_oddzial']?><?=$data['id_pracownik']?><?=$data['id']?><?=$data['id_osoba']?> <br/>
            <b>Miejsce odjazdu:</b> <?=$data['msc_odjazdu'] ?>, <?=$data['wyjazd_przystanek']?>, <?=$data['dzien_wyjazdu']?> <?=$data['data_wyjazdu'] ?>, godz. <?=$data['wyjazd_godzina'] ?>
        </td>
    </tr>
</table>
<!------------------------------------------------------- -->
<hr />
<span style="font-size: 1px"> </span><br/>

<table>
    <tr>
        <td><img src="/zdj/soltysik-logo.png" style="margin: 10px;"><br/>
            Na platformie <img src="/zdj/sindbad_logo.png">
        </td>
        <td style="text-align: center"><h4>ODCINEK DLA BIURA</h4></td>
        <td rowspan="2" style="width: 35%; text-align: center; font-size: 8;"><br/><br/>Pieczęć biura wydającego
            <div style="border: 1px solid black;"><br/><br/><br/><br/><br/><br/>podpis</div>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="width: 65%"><b><?="{$data['imie']} {$data['nazwisko']}, ur. {$data['data_urodzenia']}"?></b><br/>
            <b>Numer rezerwacji:</b> <?=$data['id_oddzial']?><?=$data['id_pracownik']?><?=$data['id']?><?=$data['id_osoba']?> <br/>
            <b>Rodzaj biletu:</b> <?=$data['bilet'] ?><br/>
            <b>Cena:</b> <?=$data['price']?> zł; <?=$data['forma_platnosci'] ?><br/>
            <b>Miejsce odjazdu:</b> <?=$data['msc_odjazdu'] ?>, <?=$data['wyjazd_przystanek']?>, <?=$data['dzien_wyjazdu']?> <?=$data['data_wyjazdu'] ?>, godz. <?=$data['wyjazd_godzina'] ?><br/>
            <b>Miejsce docelowe:</b> <?=$data['miasto_docelowe'] ?>, <?=$data['miejsce_docelowe'] ?>, <?=$data['dzien_przyjazdu']?> <?=$data['data_przyjazdu']?>, godz. <?=$data['godzina_przyjazdu']?>
        </td>
    </tr>
</table>
<br/><br/>
<table border="1">
    <tr>
        <td>
            UWAGI:<br/><br/>
        </td>
    </tr>
</table>
