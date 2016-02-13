<?php
    session_start();
    require_once 'conf.php';
    require_once 'adl/User.php';
    require_once 'bll/BLLDaneSlownikowe.php';
    require_once 'ui/HtmlControls.php';
?>
<html><head>
<title>E&A - Baza Danych</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2">
<link href="css/layout.css" rel="stylesheet" type="text/css">
<script src="js/utils.js" type="text/javascript"></script>
</head>
<body>
<?
    
    function queryChangePrivileges($userId)
    {
        $zapytanie = 'UPDATE uprawnienia SET ';
        
        $privs = User::getPriviledgesList();
        
        $setsList = array();
        foreach ($privs as $priviledge)
        {
            $setsList[] = $priviledge.(isset($_POST[$priviledge.$userId]) ? "='1'" : "='0'");
        }
        
        $zapytanie .= implode(',', $setsList).', aktywny='.(isset($_POST['aktywny'.$userId]) ? 'true' : 'false');
        
        $zapytanie .= ',id_firma_filia='.$_POST['firma_filia_id_'.$userId];

        $zm = "liczba_rekordow".$userId;
        if ($_POST[$zm]){
        $zapytanie = $zapytanie.",liczba_rekordow='".$_POST[$zm]."'";}

        $zm = "adres_email".$userId;
        if ($_POST[$zm]){
        $zapytanie = $zapytanie.",adres_email='".$_POST[$zm]."'";}
        
        $zapytanie = $zapytanie." WHERE id = ".$userId.";";
        //echo $zapytanie;
        return $zapytanie;
    }
    
    if (empty($_SESSION['uzytkownik']))
    {
        require("log_in.php");
    }
    else
    {
        include_once "vaElClass.php";
        $controls = new valControl();
	    if (isset($_SESSION['zmiana_uprawnien']) && isset($_POST['zmiana_uprawnien']))
	    {
		    $database = pg_connect($con_str);
		    $zapytanie = "select * from uprawnienia where nazwa_uzytkownika != 'postgres' order by id asc;";
		    $wynik = pg_query($database,$zapytanie);
		    echo "<form method='POST' action='zmiana_uprawnien.php'><table>";
		    echo "<tr><td>Użytkownik</td><td><img src='zdj/dodawanie_rek.gif' width='19' height='19' title='Dodawanie rekordu'>
		    </td><td><img src='zdj/dodawanie_kw.gif' width='19' height='19' title='Dodawanie kwerendy'></td><td>
		    <img src='zdj/druk.gif' width='20' height='22' title='Dodawanie cetla'></td><td>
		    <img src='zdj/edycja_rek.gif' width='20' height='20' title='Edytowanie rekordu'></td><td>
		    <img src='zdj/edycja_grupowa.gif' width='20' height='20' title='Edycja grupowa'></td><td>
		    <img src='zdj/kasuj_rek.gif' width='20' height='20' title='Kasowanie rekordu'></td><td>
		    <img src='zdj/druk.gif' width='20' height='20' title='Drukowanie umowy'></td><td>
		    <img src='zdj/druk.gif' width='20' height='20' title='Drukowanie listy'></td><td>
		    <img src='zdj/druk.gif' width='20' height='20' title='Drukowanie rozliczenia'></td><td>
		    <img src='zdj/druk.gif' width='20' height='20' title='Drukowanie ankiety'></td><td>
		    <img src='zdj/druk.gif' width='20' height='20' title='Drukowanie Biletu'></td><td>
		    <img src='zdj/email.gif' width='15' height='11' title='Wysyłanie e-mail'></td><td>
		    <img src='zdj/masemail.gif' width='20' height='20' title='Masowe wysyłanie e-mail'></td><td>
		    <img src='zdj/sms.gif' width='20' height='20' title='Masowe wysyłanie sms'></td><td>
		    <img src='zdj/1.jpg' width='20' height='20' title='Zmiana uprawnień'></td><td>Filia</td><td>Aktywny</td></tr>";
		    echo $controls->AddHidden('id_zm_uzytkownika', 'id_zm_uzytkownika', '');
		    $i=0;
            
            $privs = User::getPriviledgesList();
            $bllDicts = new BLLDaneSlownikowe();
            $filie = $bllDicts->getFiliaeList();
            $htmlControls = new HtmlControls();
            
		    while($wiersz=pg_fetch_array ($wynik))
		    {
			    $i++;
			    echo "<tr><td><input type='text' class='formfield' size='8' 
			    value='".$wiersz['nazwa_uzytkownika']."' name='".$wiersz['nazwa_uzytkownika']."' readonly /></td>";
                
                foreach ($privs as $priviledge)
                {
                    $checked = $wiersz[$priviledge] == '1' ? 'checked="checked"' : '';
                    echo '<td><input type="checkbox" name="'.$priviledge.$wiersz['id'].'" '.$checked.' /></td>';
                }
                
                $checked = $wiersz['aktywny'] == 't' ? 'checked="checked"' : '';
                echo '<td>'.
                $htmlControls->_AddSelect('firma_filia'.$wiersz['id'], 'id_firma_filia'.$wiersz['id'], $filie[Model::RESULT_FIELD_DATA], $wiersz['id_firma_filia'], 
                    'firma_filia_id_'.$wiersz['id'], true).'
                </td><td><input type="checkbox" name="aktywny'.$wiersz['id'].'" '.$checked.' /></td>';

			    echo "<td><input type='text' class='formfield' name='liczba_rekordow".$wiersz['id']."' value='".$wiersz['liczba_rekordow']."' size='3' maxlength='6'></td>";
			    echo "<td><input type='text' class='formfield' name='adres_email".$wiersz['id']."' value='".$wiersz['adres_email']."' size='15' maxlength='30'></td>";
			    
			    //echo "<input type='hidden' name='nazwa".$i."' value='".$wiersz['id']."'>";
			    //echo $wiersz['id'];
			    echo "<td><input type='submit' name='potwierdz' value='Zmien' class='formreset' onmousedown='id_zm_uzytkownika.value=".$wiersz['id']."'></td>
			    <td><input type='submit' name='zmhaslo' value='Zmień hasło' class='formreset' onmousedown='id_zm_uzytkownika.value=".$wiersz['id']."'></td>
			    <td><input type='submit' name='kasuj' value='Kasuj' class='formreset' onClick='id_zm_uzytkownika.value=".$wiersz['id']."'>
			    </td></tr>";
		    }
            echo '<tr><td colspan="18" align="center"><input type="submit" name="globalConfirm" class="formreset" value="Zmień wszystkim."></td></tr>';
		    echo "</form></table>";
		    require("dodawanie/dodaj_uzytkownika.php");
	    }
	    if(isset($_POST['potwierdz']))
	    {
		    //$zm = "zmiana_uprawnien".$j;
		    //echo $$zm;
		    $j = $_POST['id_zm_uzytkownika'];
		    $zapytanie = queryChangePrivileges($j);
		    $database = pg_connect($con_str);
		    //$zapytanie = $zapytanie.";";
		    $wynik = pg_query($database,$zapytanie);
		    //echo $zapytanie;
		    echo "W trakcie bierzącej
	       	    sesji uprawnienia nie ulegają zmianie. 
		    Zmiany zostaną wprowadzone w życie po ponownym zalogowaniu do systemu.";
	    }
        if (isset($_POST['globalConfirm']))
        {
            $database = pg_connect($con_str);
            $queryUsers = 'select id from uprawnienia where nazwa_uzytkownika != \'postgres\' order by id asc;';
            $resUsers = pg_query($database, $queryUsers);
            $queryUpdate = '';
            while($rowIdUser = pg_fetch_array($resUsers))
            {
                $queryUpdate .= queryChangePrivileges($rowIdUser['id']);
            }
            $wynik = pg_query($database,$queryUpdate);
            //echo $zapytanie;
            echo "W trakcie bierzącej
                   sesji uprawnienia nie ulegają zmianie. 
            Zmiany zostaną wprowadzone w życie po ponownym zalogowaniu do systemu.";
        }
	    if (isset($_POST['kasuj']))
	    {
		    $zapytanie = "DELETE FROM uprawnienia WHERE id = '".$_POST['id_zm_uzytkownika']."';";
		    $database = pg_connect($con_str);
		    $zapytanie = $zapytanie.";";
		    $wynik = pg_query($database,$zapytanie);
	    }
	    if (isset($_POST['zmhaslo']))
	    {
		    $zapytanie = "SELECT imie_nazwisko, nazwa_uzytkownika FROM uprawnienia WHERE id = '".$_POST['id_zm_uzytkownika']."';";
		    $database = pg_connect($con_str);
		    $wynik = pg_query($database,$zapytanie);
		    $wiersz = pg_fetch_array($wynik);
		    echo "Zmienianie hasła użytkownika: ".$wiersz['imie_nazwisko'].".";
		    echo "<form action='zmienhaslo.php' method='POST'>";
            echo $controls->AddHidden('id_id', 'id', $_POST['id_zm_uzytkownika']);
            echo "<table><tr><td>Hasło</td><td>";
            echo $controls->AddPassbox('haslo1', 'id_haslo1', '', '20', '20', '');
            echo "</td></tr><tr><td>Powtórz hasło</td><td>";
            echo $controls->AddPassbox('haslo2', 'id_haslo2', '', '20', '20', '');
            echo "</td></tr>
            <tr><td>Użytkownik</td><td>";
            echo $controls->AddTextbox('user', 'id_user', $wiersz['nazwa_uzytkownika'], '20', '20', '');
            echo "</td></tr>
            <tr><td>Imię i nazwisko</td><td>";
            echo $controls->AddTextbox('name', 'id_name', $wiersz['imie_nazwisko'], '20', '20', '');
            echo "</td></tr>
            <tr><td>";
            echo $controls->AddSubmit('potwzmhasla', 'id_potwzmhasla', 'Zrobione', '');
		    echo "</td></tr></table></form>";
	    }
    }
?>
</body>
</html>
