<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="js/script.js"></script>
<link href="css/layout.css" rel="stylesheet" type="text/css"></head>
</head>
<?php
    session_start();
    include("vaElClass.php"); 
    $controls = new valControl();
    if (empty($_SESSION['uzytkownik']))
    {
        require("log_in.php");
    }
    else
    {
        require("naglowek.php");
        require("conf.php");
        if (isset($_POST['h_os']))
        {
            unset($_SESSION['osoby']);
            $_SESSION['osoby'] = $_POST['h_os'];
        }
        $tab_id = substr(str_replace("|", ",", $_SESSION['edycja_masowa']), 0, strlen($_SESSION['edycja_masowa']) - 1);
        $tab_id = "(".$tab_id.")";
        echo '<form method = "POST" action = "'.$_SERVER['PHP_SELF'].'">';
        echo '<div align = "CENTER">Ilość rekordów do aktualizacji: '.(count(explode(",", $tab_id))).'</div>';
        echo '<table align = "CENTER"><tr><td>Wpisz nową datę wyjazdu:</td>
        <td>'.$controls->AddDatebox("data", "data", "", 10, 10).'</td><td>'
        .$controls->AddSubmit('data_w', 'id_data_w', 'Zmień date', '').'</td></tr>';
        
        
        echo("</table>");
        echo("</form>");
        if ((isset($_POST['data_w'])) && ($_POST['data'] >= date("Y-m-d")) && (trim($_POST['data']) != ""))
        {
            $database = pg_connect($con_str);
            //echo("{$_POST['radio']} {$_POST['data']}");
            $zapytanie = "update dane_osobowe set data = '".$_POST['data']."' where id in $tab_id and (select count(*) from zatrudnienie where id_osoba = dane_osobowe.id and id_status in (".ID_STATUS_AKTYWNY.", ".ID_STATUS_WYJEZDZAJACY.")) = 0;";
            $wynik = pg_query($database, $zapytanie);
            echo("<div align = 'CENTER'>Zmiany zostały wprowadzone</div>");
        }
	//else if ((isset($_POST['data_w'])) && ($_POST['data'] < date("Y-m-d")) || (trim($_POST['data']) == ""))
        //else if (($_POST['data'] < date("Y-m-d")) || (trim($_POST['data']) == ""))
	else if ((isset($_POST['data'])) && ($_POST['data'] < date("Y-m-d")))
        {
            echo("<div align = 'CENTER'>Podałeś/aś złą datę</div>");   
        }
        require("stopka.php");
    }
?>
</html>
