<?php
    require_once '../conf.php';
    CommonUtils::outputBufferingOn();
    CommonUtils::SessionStart();

    echo '<html>';
    HelpersUI::addHtmlBasicHeaders(HelpersUI::PATH_LEVEL_1);
    echo '<body>';
    
    include("../bll/definicjeKlas.php");
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else if (isset($_SESSION['masowy_sms']))
    {
        $controls = new valControl();
        
        $dbDicts = AvDicts::GetDicts();
        $dbSubNames = AvDicts::GetSubNames();
        $i = 2;
        $j = 0;
        echo '<table>';
        echo '<tr><td><form method="POST" action="slownik_zrodel.php">';
        echo $controls->AddSubmit('zarzadzaj', 'id_zarzadzaj', 'Zarz±dzaj s³ownikiem ¼róde³', '');
        echo '</form></td><td><form method="POST" action="slownik_zawodow.php">';
        echo $controls->AddSubmit('zarzadzaj', 'id_zarzadzaj', 'Zarz±dzaj s³ownikiem zawodów', '');
        echo '</form></td>';
        while(isset($dbDicts[$j]))
        {
            if ($i % 4 == 0)
                echo '<tr>';
                
            echo '<td><form method="POST" action="wprowadz_el_slownika.php?element='.$dbDicts[$j].'">';
            echo $controls->AddSubmit('wprowadz', 'id_wprowadz', 'Wprowad¼ '.$dbSubNames[$j], '');
            echo '</form></td>';
            
            if ($i % 4 == 3)
                echo '</tr>';
                
            $i++;
            $j++;
        }
        
        
	    echo '</table><hr /><form method="GET" action="dodatkowe_kolumny.php">';
        echo $controls->AddSubmit('dodatkowe_kolumny', 'id_dodatkowe_kolumny', 'Dodatkowe kolumny', '');
        echo '</form>';
        
        echo '<form method="GET" action="rozklad_jazdy.php">';
        echo $controls->AddSubmit('rozklad_jazdy', 'id_rozklad_jazdy', 'Rozk³ad jazdy', '');
        echo '</form>';
        
        echo '<form method="GET" action="kody_pocztowe_rejestracje.php">';
        echo $controls->AddSubmit('kody_pocztowe_rejestracje', 'id_kody_pocztowe_rejestracje', 'Kody pocztowe rejestracji', '');
        echo '</form>';
        
        echo '<form method="GET" action="opisy_prac.php">';
        echo $controls->AddSubmit('opisy_prac', 'id_opisy_prac', 'Opisy prac', '');
        echo '</form>';
    }
    CommonUtils::sendOutputBuffer();
?>
</html>