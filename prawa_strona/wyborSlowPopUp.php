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
        if (isset($_GET['table']))
        {
            if (false !== strpbrk($_GET['table'], '; -'))
                die('Bad request');
            
            $_SESSION['table'] = $_GET['table'];
            $_SESSION['hid'] = $_GET['hidId'];
            $_SESSION['txt'] = $_GET['txtId'];
        }
        
        $controls = new valControl();

        if (isset($_POST['potwierdz']))
        {
            echo "<table border='0'>";
            $database = pg_connect($con_str);
            if ($_SESSION['table'] == 'zawod')
            {
                $Filter = "select id, nazwa from ".$_SESSION['table']." where lower(nazwa) like lower('%".$_POST['dana']."%') and widoczne = true order by nazwa asc;";
            }
            else
            {
                $Filter = "select id, nazwa from ".$_SESSION['table']." where lower(nazwa) like lower('%".$_POST['dana']."%') order by nazwa asc;";
            }
            
            $result = pg_query($database, $Filter);
            if (pg_num_rows($result) == 0)
            {
                echo "Zapytanie nie zwroci³o wyników. Rozszerz kryterium.";
            }
            while ($row = pg_fetch_array($result))
            {
                echo "<tr><td><input type='button' class='formreset' name='wybierz_zawod' value='Wybierz' 
                onclick='opener.window.document.getElementById(\"".$_SESSION['hid']."\").value = \"".$row['id']."\";
                opener.window.document.getElementById(\"".$_SESSION['txt']."\").value = \"".$row['nazwa']."\";
                window.close();'></td><td>".$row['nazwa']."</td></tr>";
            }
            echo "</table>";
        }
        echo "<table border='0'><form method='POST' action='".$_SERVER['PHP_SELF']."'>";
        $controls->AddHiddenCtrlConfig("table", "hid", "txt", $_SESSION['table'], $_SESSION['hid'], $_SESSION['txt']);
        echo "<tr><td>".$controls->AddSeekTextbox("dana", isset($_POST['dana']) ? $_POST['dana'] : '', "dana", 30, 30)."</td><td>";
        echo $controls->AddSubmit('potwierdz', 'id_potwierdz', 'Szukaj.', '');
        echo "</td></tr></form></table>";
    }
    CommonUtils::sendOutputBuffer();
?>
</html>