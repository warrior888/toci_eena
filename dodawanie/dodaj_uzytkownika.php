<?php
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
	    echo "<table><form method='POST' action='insert/insert_uzytkownik.php'>
	    <tr><td></td><td>Login:</td><td>Has³o:</td><td>Imiê i nazwisko:</td></tr>
	    <tr><td>Nowy u¿ytkownik:</td><td>";
        echo $controls->AddTextbox('login', 'ig_login', '', '20', '20', '');
        echo "</td><td>";
        echo $controls->AddPassbox('haslo', 'id_haslo', '', '20', '20', '');
        echo "</td><td>";
        echo $controls->AddTextbox('imie_nazwisko', 'id_imie_nazwisko', '', '20', '20', '');
        echo "</td><td>";
        echo $controls->AddSubmit('dodaj_uzytkownik', 'id_dodaj_uzytkownik', 'Potwierd¼', '');
        echo "</td></tr>";
    }