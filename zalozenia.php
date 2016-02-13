<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="js/script.js"></script>
<link href="css/layout.css" rel="stylesheet" type="text/css">
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
        include_once "vaElClass.php";
        $controls = new valControl();
	    require("conf.php");
        $database = pg_connect($con_str);
        include_once "Spreadsheet/Excel/Writer.php";
		include "generuj_excel.php";
        if (isset($_POST['send_mies']))
        {       
            $nazwa = "Zalozenia.xls";
            $xls = new Spreadsheet_Excel_Writer($nazwa);
	        $sheet =& $xls->addWorksheet('lista');
	        $format =& $xls->addFormat();
	        if($_POST['stan_zal'] == "Niezałatwione")
	        {
            	    $zapytanie = "select * from zalozenia where (select count(id) from stan_zalozenia where data_wyjazdu = zalozenia.data_wyjazdu and id = zalozenia.id) = 0;";
	        }
	        else
	        {
            	    $zapytanie = "select * from zalozenia where (select count(id) from stan_zalozenia where data_wyjazdu = zalozenia.data_wyjazdu and id = zalozenia.id) = 1;";
	        }
            $nag = array("ID","Imię", "Nazwisko", "Data urodzenia", "Biuro", "Data wyjazdu", "Cena");
            create_excel (7,$zapytanie,$sheet, $nag);
            $xls->close();
		    echo "<div align = 'CENTER'><a href='$nazwa'>Pobierz plik xls z założeniami</a></div>";
        }
        if (isset($_POST['zalatw_zal']))
        {
            $fixquery = "insert into stan_zalozenia values(".$_POST['id_os'].",'".$_POST['data_wyjazdu']."');";
            $testquery = "select id from stan_zalozenia where id = ".$_POST['id_os']." and data_wyjazdu = '".$_POST['data_wyjazdu']."';";
            $restest = pg_query($database, $testquery);
            if (pg_num_rows($restest) == 0)
            {
                $resfix = pg_query($database, $fixquery);
            }
        }
        echo("<form method = 'POST' action = '".$_SERVER['PHP_SELF']."'>");
        echo $controls->AddHidden('id_os', 'id_os', '');
        echo $controls->AddHidden('id_data_wyjazdu', 'data_wyjazdu', '');
        echo("<table align = 'CENTER' class='gridTable' border='0' cellspacing='0'>");
        $zapytanie = "select * from zalozenia where (select count(id) from stan_zalozenia where data_wyjazdu = zalozenia.data_wyjazdu and id = zalozenia.id) = 0;";
        $wynik = pg_query($database, $zapytanie);
        $count = 0;
        while ($wiersz = pg_fetch_array($wynik))
        {
            $count++;
            $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
            echo "<tr class='".$css."'>";
            $i = 0;
            while(isset($wiersz[$i]))
            {
                echo "<td>".$wiersz[$i]."</td>";
                $i++;
            }
	        echo "<td>";
            echo $controls->AddPopUpButton('Drukuj założenie','druk_zalozenia', 'prawa_strona/zalozenie.php?id_os='.$wiersz['id'].'&data_wyjazdu='.$wiersz['data_wyjazdu'], '900', '700');
            echo "</td><td>";
            echo $controls->AddSubmit('zalatw_zal', 'id_zalatw_zal', 'Załatwione', JsEvents::ONCLICK.'=\'id_os.value='.$wiersz['id'].'; data_wyjazdu.value="'.$wiersz['data_wyjazdu'].'";\'');
            echo "</tr>";
        }
        
	    echo "<input type='radio' id='1' name='stan_zal' value='Załatwione'>Załatwione&nbsp;";
	    echo "<input type='radio' id='2' name='stan_zal' value='Niezałatwione' CHECKED>Niezałatwione&nbsp;";
        echo $controls->AddSubmit('send_mies', 'id_send_mies', 'Generuj listę.', '');   

        echo("</form>");

        require("stopka.php");
    }
?>
</html>
