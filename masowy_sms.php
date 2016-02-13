<?php session_start(); ?>

<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="js/script.js"></script>
<link href="css/layout.css" rel="stylesheet" type="text/css"></head>
</head>
<?php
    //include
    require_once 'dal.php';
    require_once 'wsparcie/sms.php';
    require_once 'vaElClass.php';
    $controls = new valControl();
    if (empty($_SESSION['uzytkownik']))
    {
        require("log_in.php");
    }      
    else
    {
        require("naglowek.php");
        require("conf.php");
	    $_SESSION['licznik_sms'] = 0;
        echo("<form method = \"POST\" action = \"".$_SERVER['PHP_SELF']."\">");
        echo("<table align = \"CENTER\"><tr><td>");
        echo "<textarea wrap=\"ON\" name = \"tekst1\" rows = \"5\" cols = \"50\" class = \"formfield\" onkeypress='return DlugoscSms(this, event);' onblur='CutTooLong(this);'></textarea></td></tr></table>";
        echo "<table align = \"CENTER\"><tr><td>";
        echo $controls->AddSubmit('wyslij_sms', 'id_wyslij_sms', 'Wyslij', '');
        echo "</td></tr></table>";
        echo "</form>";
        if (isset($_POST['wyslij_sms']))
        {
            $nr_id = str_replace("|", ',', $_SESSION['edycja_masowa']);
            $nr_id = substr($nr_id, 1, strlen($nr_id) - 2);
            $dal = dal::getInstance();
            $zapytanie = 'select id as id_dane_osobowe, nazwa as telefon from telefon_kom where id in ('.$nr_id.');';
            $wynik = $dal->PobierzDane($zapytanie, $ilosc_wierszy);
            $sms = new Sms();
            $sms->MasowySms($wynik, $_POST['tekst1'], null, 'telefon');
            
            //hmm, jakies info zwrotne ?
        }
        require("stopka.php");
    }
?>
</html>
