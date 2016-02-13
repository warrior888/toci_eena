<table>
    <tr>
        <td style="width: 10%" rowspan="2"></td>
        <td style="width: 40%">E&A sp. z o.o.<br/>
            ul. Kołłątaja 3/1<br/>
            45-064 Opole<br/>
        </td>
        <td style="width: 40%; text-align: right">Opole, dnia <?=$data['data_wpisu']?></td>
        <td style="width: 10%" rowspan="2"></td>
    </tr>
    <tr>
        <td colspan="2">
            <br/>
            <br/>
            <br/>
            <h3 style="text-align: center">Oświadczenie</h3>
            <h3> </h3>
            <h3> </h3>
            Ja, niżej podpisany(a) <?=$data['imie'] ?> <?=$data['nazwisko']?>, ur. <?=$data['data_urodzenia']?> legitymujący(a) się 
            dokumentem tożsamości o nr <?=$data['pass_nr']?> ważnym do <?=$data['data_waznosci']?> zgadzam się na potrącenie mi z 
            wynagrodzenia przez firmę: <?=$data['klient']?>, kwoty <?=$data['price']?> zł za bilet jednostronny <?= $data['przewoznik']?> 
            na trasie <?=$data['msc_odjazdu'] ?> - <?=$data['miasto_docelowe'] ?> z wyjazdem w dniu <?=$data['data_wyjazdu'] ?>.
            <br/>
            <br/>
            <br/>
            Podpis kandydata składającego oświadczenie:<br/>
            <br/>
            <br/>
            ................................................<br/>
            <br/>
            Podpis osoby przyjmującej oświadczenie: <br/>
            <br/>
            <br/>
            ................................................<br/>
        </td>
    </tr>
</table>
