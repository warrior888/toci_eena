<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="js/script.js"></script>
<link href="css/style.css" rel="stylesheet" type="text/css">
</head>
<?php
    @session_start();
    if (empty($_SESSION['uzytkownik']))
    {
        require("log_in.php");
    }
    else
    {
        require("naglowek.php");
	    require("conf.php");
        if (isset($_POST['dodaj_zettel']))
        {
            $database = pg_connect($con_str);
            $zapytanie = "select tydzien from zettel where id = '".$_SESSION['id']."' and tydzien = '".addslashes($_POST['tydzien'])."';";
            $query = pg_query($database, $zapytanie);
            if (pg_num_rows($query) == 0)
            {
                $zapytanie_insert = "insert into zettel values ('".$_SESSION['id']."', (select id from uprawnienia where nazwa_uzytkownika = '".$_SESSION['uzytkownik']."'), '".date("Y-m-d")."', '".addslashes($_POST['tydzien'])."');";
                $query_insert = pg_query($database, $zapytanie_insert);
                echo("<script>window.close();</script>");
            }
            else
            {
                echo("Zettel z {$_POST['tydzien']} jest już wpisany");
            }
        }        
        echo("{$_SESSION['id_zettel']}");
        echo("<form action = '".$_SERVER['PHP_SELF']."' method = 'POST'>");
        echo("<div align = 'CENTER'>Wpisz tydzień:</div>");
        echo "<div align = 'CENTER'>";
        echo $controls->AddTextbox('tydzien', 'id_tydzien', '', '20', '20', '');
        echo "</div><div align = 'CENTER'>";
        echo $controls->AddSubmit('dodaj_zettel', 'id_dodaj_zettel', 'Dodaj','');
        echo("</div></form>");
        require("stopka.php");
    }
?>
</html>
