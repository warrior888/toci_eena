<?php
    require_once '../conf.php';
    CommonUtils::outputBufferingOn();
    CommonUtils::SessionStart();

    echo '<html>';
    HelpersUI::addHtmlBasicHeaders(HelpersUI::PATH_LEVEL_1);
    echo '<body>';
    
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
        $controls = new valControl();
        if (isset($_POST['potw_gr_zaw']))
        {
            echo "<table border='0'>";
            $database = pg_connect($con_str);
            $Filter = "select id, nazwa from zawod where lower(nazwa) like lower('%".$_POST['grupa_zawodowa']."%') and widoczne = true order by nazwa asc;";
            $result = pg_query($database, $Filter);
            if (pg_num_rows($result) == 0)
            {
                echo "Zapytanie nie zwroci³o wyników. Rozszerz kryterium.";
            }
            while ($row = pg_fetch_array($result))
            {
                echo "<tr><td><input type='button' class='formreset' name='wybierz_zawod' value='Wybierz' 
                onclick='opener.window.document.getElementById(\"hid_gr_zaw\").value = \"".$row['id']."\";
                opener.window.document.getElementById(\"txt_gr_zaw\").value = \"".$row['nazwa']."\";
                window.close();'></td><td>".$row['nazwa']."</td></tr>";
            }
            echo "</table>";
        }
        $_POST['grupa_zawodowa'] = isset($_POST['grupa_zawodowa']) ? $_POST['grupa_zawodowa'] : null;
        echo "<table border='0'><form method='POST' action='".$_SERVER['PHP_SELF']."'>";
        echo "<tr><td>".$controls->AddSeekTextbox("grupa_zawodowa", $_POST['grupa_zawodowa'], "grupa_Zawodowa", 30, 30)."</td><td>";
        echo $controls->AddSubmit('potw_gr_zaw', 'id_potw_gr_zaw', 'Szukaj', '');
        echo "</td></tr></form></table>";
    }
    CommonUtils::sendOutputBuffer();
?>
</html>