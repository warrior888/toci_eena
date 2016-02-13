<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="js/script.js"></script>
<link href="css/style.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php
    // ś ą
    session_start();
    if (empty($_SESSION['uzytkownik']))
    {
        require("log_in.php");
    }
    else
    {
        require("naglowek.php");
	    require("conf.php");
        require_once "vaElClass.php";
        $controls = new valControl();
        
        $data_od = $dzis;
        $data_do = $dzis;
        
        if (isset($_SESSION['zmiana_uprawnien']))
        {
            if (isset($_POST['data_od']))
            {
                $data_od = $_POST['data_od'];
                $data_do = $_POST['data_do'];
            }
            
            echo '<form action="wyslane_sms.php" method="POST" target="center">';
            echo '<table><tr><td>Data od: </td><td>'.$controls->AddDatebox('data_od', 'data_od', $data_od, 10, 10);
            echo '</td><td>Data do: </td><td>'.$controls->AddDatebox('data_do', 'data_do', $data_do, 10, 10);
            echo '</td><td>';
            echo $controls->AddSubmitStiffWidth("pokaz_sms", "pokaz_sms", "Pokaż wysłane sms.", "");
            echo '</td></tr></table>';
            echo '</form>';
            
            if (isset($_POST['pokaz_sms']))
            {
                $zapytanie = 'select * from pokaz_wyslane_sms where data >= \''.$data_od.' 00:00:00\'::timestamp and data <= \''.$data_do.' 23:59:59\'::timestamp order by data asc;';
                $wynik = $controls->dalObj->PobierzDane($zapytanie, $ilosc_wierszy);
                if ($ilosc_wierszy > 0)
                {
                    echo '<table border="1">';
                    echo '<tr><td>Adresat</td><td>Nadawca</td><td>Data</td><td>Status</td><td>Telefon</td><td>Treść</td><td>Id wysyłki</td></tr>'; 
                    foreach ($wynik as $wiersz)
                    {
                        echo '<tr><td>'.$wiersz['nazwisko'].' '.$wiersz['imie'].'</td><td>'.$wiersz['imie_nazwisko'].'</td><td>'.$wiersz['data'].'</td><td>'.$wiersz['status'].'</td><td>'.$wiersz['telefon'].'</td>
                        <td>'.$wiersz['tresc'].'</td><td>'.$wiersz['remote_id'].'</td></tr>';
                    }
                    echo '</table>';
                }
            }
        }
        require("stopka.php");
    }
?>
</body>
</html>
