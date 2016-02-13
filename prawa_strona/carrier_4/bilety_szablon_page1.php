<table border="0">
<tr>
    <td style="width: 27%"><br/><br/>
        <img src="/zdj/juzwa-logo.png" style="margin: 10px;"><br/><br/>
        Codzienne przewozy do i z Holandii
        <br/>
        
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
<hr/>
<h3>WYJAZD:</h3>
<h4>z: <?=$data['msc_odjazdu'] ?>, <?=$data['wyjazd_przystanek']?>, <?=$data['dzien_wyjazdu']?> <?=$data['data_wyjazdu'] ?>, godz. <?=$data['wyjazd_godzina'] ?></h4>

<h3>PRZYJAZD:</h3>
<h4>do: <?=$data['miasto_docelowe'] ?>, <?=$data['miejsce_docelowe'] ?></h4>
<br/>
<span style="font-size: 8">Numer telefonu kontaktowego pod którym mogą Państwo uzyskać przed odjazdem informacje na temat przystanku to +48774313440 lub +48604225821 w godzinach od 8:00 do 20:00.
<b>Podchodząc do mikrobusa platformy dopytujcie Państwo o swoje nazwisko na liście pasażerów.</b>
</span>
<? if ($data['bilet'] == 'Obustronny' || $data['bilet'] == 'Założenie dwustronne') : ?>
<h3>WYJAZD:</h3>
<h4>z: <?=$data['miasto_docelowe'] ?>, <?=$data['miejsce_docelowe'] ?>, "OPEN", godz. odjazdu: <?=$data['godzina_wyjazdu_powrot']?> </h4>

<h3>PRZYJAZD:</h3>
<h4>do: <?=$data['msc_powrotu'] ?>, <?=$data['powrot_przystanek']?>, "OPEN", godz. przyjazdu <?=$data['powrot_godzina']?></h4>
<? else : ?>
<h3> </h3>
<h4> </h4>

<h3> </h3>
<h4> </h4>
<? endif; ?>

<!------------------------------------------------------- -->

<span style="font-size: 1px"> </span><br/>
<hr />

<span style="font-size: 1px"> </span><br/>

<table>
    <tr>
        <td><img src="/zdj/juzwa-logo.png" style="margin: 10px;"></td>
        <td style="text-align: center"><h4>KUPON RABATOWY<br/><u style="font-size: 8">POWRÓT Z HOLANDII 10 EURO TANIEJ!</u></h4></td>
        <td rowspan="2" style="width: 30%; text-align: center; font-size: 8;">
            <img src="/zdj/ea_logo_small_pdf.png">
            <br/>Pieczęć biura wydającego
            <div style="border: 1px solid black; font-size: 10"><br/><br/><br/>podpis</div>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="width: 70%"><b><?="{$data['imie']} {$data['nazwisko']}, ur. {$data['data_urodzenia']}"?></b><br/>
            <b>Wyjazd z dnia:</b> <?=$data['dzien_wyjazdu']?> <?=$data['data_wyjazdu'] ?> <br/>
            <b>Numer rezerwacji:</b> <?=$data['id_oddzial']?><?=$data['id_pracownik']?><?=$data['id']?><?=$data['id_osoba']?> <br/>
            <b>Miejsce docelowe:</b> 
        <? if (empty($data['msc_powrotu'])) : ?>
            <?=$data['msc_odjazdu'] ?>, <?=$data['wyjazd_przystanek']?>
        <? else : ?>
            <?=$data['msc_powrotu'] ?>, <?=$data['powrot_przystanek']?>
        <? endif; ?>
        </td>
    </tr>
</table>
<!------------------------------------------------------- -->
<hr />
<span style="font-size: 1px"> </span><br/>

<table>
    <tr>
        <td><img src="/zdj/juzwa-logo.png" style="margin: 10px;"></td>
        <td style="text-align: center"><h4>ODCINEK NA WYJAZD DO HOLANDII</h4></td>
        <td rowspan="2" style="width: 30%; text-align: center; font-size: 8;">
            <img src="/zdj/ea_logo_small_pdf.png">
            <br/>Pieczęć biura wydającego
            <div style="border: 1px solid black; font-size: 10"><br/><br/><br/>podpis</div>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="width: 70%"><b><?="{$data['imie']} {$data['nazwisko']}, ur. {$data['data_urodzenia']}"?></b><br/>
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
        <td><img src="/zdj/juzwa-logo.png" style="margin: 10px;"></td>
        <td style="text-align: center"><h4>ODCINEK DLA BIURA</h4></td>
        <td rowspan="2" style="width: 30%; text-align: center; font-size: 8;">
            <img src="/zdj/ea_logo_small_pdf.png">
            <br/>Pieczęć biura wydającego
            <div style="border: 1px solid black; font-size: 10"><br/><br/><br/>podpis</div>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="width: 70%"><b><?="{$data['imie']} {$data['nazwisko']}, ur. {$data['data_urodzenia']}"?></b><br/>
            <b>Numer rezerwacji:</b> <?=$data['id_oddzial']?><?=$data['id_pracownik']?><?=$data['id']?><?=$data['id_osoba']?> <br/>
            <b>Rodzaj biletu:</b> <?=$data['bilet'] ?><br/>
            <b>Cena:</b> <?=$data['price']?> zł; <?=$data['forma_platnosci'] ?><br/>
            <b>Miejsce odjazdu:</b> <?=$data['msc_odjazdu'] ?>, <?=$data['wyjazd_przystanek']?>, <?=$data['dzien_wyjazdu']?> <?=$data['data_wyjazdu'] ?>, godz. <?=$data['wyjazd_godzina'] ?><br/>
            <b>Miejsce docelowe:</b> <?=$data['miasto_docelowe'] ?>, <?=$data['miejsce_docelowe'] ?>
        </td>
    </tr>
</table>
<br/><br/>
<table border="1">
    <tr>
        <td>
            UWAGI:<br/>
        </td>
    </tr>
</table>
