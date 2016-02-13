<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
<link href="../css/style.css" rel="stylesheet" type="text/css">
</head>
<?php
    @session_start();
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
        //dokladny na true oznacza ze moze byc niedokladnie :P
        require("../naglowek.php");
	    require("../conf.php");
        $database = pg_connect($con_str);
        if ((isset($_POST['wakat_edytuj'])) && ($_POST['wakat_edytuj'] != ""))
        {
            $_SESSION['wakat_edycja'] = $_POST['wakat_edytuj'];
        }
        if ((isset($_POST['aktualizuj_wakaty'])) && ($_POST['aktualizuj_wakaty'] != "") && ($_POST['data_wyjazdu']) >= date("Y-m-d"))
        {
            if (($_POST['data_wyjazdu'] != "") && ($_POST['ilosc_kobiet'] != "") && ($_POST['ilosc_mezczyzn'] != "") && ($_POST['ilosc_tygodni'] != ""))
            {
                $dokladny = "false";
                if (isset($_POST['dokladny']))
                {
                    $dokladny = "true";
                }
                $zapytanie_edit = "update wakaty set data_wyjazdu = '".$_POST['data_wyjazdu']."', ilosc_kobiet = '".$_POST['ilosc_kobiet']."', ilosc_mezczyzn = '".$_POST['ilosc_mezczyzn']."', ilosc_tyg = '".$_POST['ilosc_tygodni']."', dokladny = ".$dokladny." where id = '".$_SESSION['wakat_edycja']."';";
                $query_edit = pg_query($database, $zapytanie_edit);
                unset($_SESSION['wakat_edycja']);
		//echo $zapytanie_edit;
                echo("<script>parent.frames[0].document.location.reload();</script>");
                echo("<script>document.location = \"../edit/edycja_zapotrzebowan.php\"</script>");
            }            
        }
	if ((isset($_POST['wakat_kasuj'])) && ($_POST['wakat_kasuj'] != ""))
        {
            //echo("{$_POST['wakat_kasuj']}");
            $zapytanie_delete = "delete from wakaty where id = '".$_POST['wakat_kasuj']."';";
            $query_delete = pg_query($database, $zapytanie_delete);
            echo("<script>parent.frames[0].document.location.reload();</script>");
            echo("<script>document.location = \"../edit/edycja_zapotrzebowan.php\"</script>");
        }
	if (isset($_SESSION['wakat_edycja']))
	{
        $zapytanie_edit = "select wakaty.id, klienci.nazwa as klient, wakaty.data_wyjazdu, wakaty.ilosc_kobiet, wakaty.ilosc_mezczyzn, wakaty.ilosc_tyg, wakaty.dokladny from wakaty join klienci on wakaty.id_klient = klienci.id where wakaty.id = '".$_SESSION['wakat_edycja']."';";
        $query_edit = pg_query($database, $zapytanie_edit);
        $row_edit = pg_fetch_array($query_edit);
        $checked = "";
        if ($row_edit['dokladny'] == "t")
        {
            $checked= "CHECKED";
        }
        //echo $_SERVER['PHP_SELF'];
        echo("<form method = 'POST' action = '".$_SERVER['PHP_SELF']."'>");
        echo("<table align = 'CENTER'>");
        echo("<tr><td>Klient</td><td>".$row_edit['klient']."</td></tr>");
        echo("<tr><td>Data wyjazdu</td><td><input type = 'text' name = 'data_wyjazdu' value = '".$row_edit['data_wyjazdu']."' onChange = 'test(this);' /></td></tr>");
        echo("<tr><td>Ilość kobiet</td><td><input type = 'text' name = 'ilosc_kobiet' value = '".$row_edit['ilosc_kobiet']."' onChange='sprawdz_ilosc_osob(this);' /></td></tr>");
        echo("<tr><td>Ilość mężczyzn</td><td><input type = 'text' name = 'ilosc_mezczyzn' value = '".$row_edit['ilosc_mezczyzn']."' onChange='sprawdz_ilosc_osob(this);' /></td></tr>");
        echo("<tr><td>Ilość tygodni</td><td><input type = 'text' name = 'ilosc_tygodni' value = '".$row_edit['ilosc_tyg']."' onChange='sprawdz_tygodnie(this);' /></td></tr>");
        echo "<tr><td>Niekoniecznie</td><td><input type='checkbox' name='dokladny' ".$checked."></td></tr>";
        echo("<tr><td></td><td><input type = 'submit' name = 'aktualizuj_wakaty' value = 'Aktualizuj' /></td></tr>");
        echo("</table>");
        echo("</form>");
	}
        //a tu piszemy cala reszte :P
        require("../stopka.php");
    }
?>
</html>
