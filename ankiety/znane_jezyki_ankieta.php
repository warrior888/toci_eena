<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
  <script language="javascript" src="../js/utils.js"></script>
  <script language="javascript" src="../js/validations.js"></script>
  <link href="style_form_box.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php
//<link href="../css/ankieta.css" rel="stylesheet" type="text/css">
    function PodajZnaneJezyki($tablica, &$poziomy = array())
    {
        $wynik = array();

        foreach ($tablica as $id_jezyk => $jezyk) 
        {
            $wynik[$id_jezyk] = $jezyk[JEZYK].' - '.$jezyk[POZIOM];
            $poziomy[$id_jezyk] = $jezyk[POZIOM_ID];
        }
        return $wynik;
    }
    
    function GenerujFormHtmlJs ($jezyki)
    {
        if (sizeof($jezyki))
        {
            $znane_jezyki = PodajZnaneJezyki($jezyki, $poziomy_id);
            
            return 'parent.document.getElementById(\'jezykiObce\').innerHTML = \'Znane języki obce:<br />'.
            implode('<br />', $znane_jezyki).'\'; 
            parent.document.getElementById(\'id_jezyk_obcy\').value = \''.implode(',', array_keys($jezyki)).'\'; 
            parent.document.getElementById(\'id_poziom_jezyk_obcy\').value = \''.implode(',', $poziomy_id).'\';';
        }
        else
        {
            return 'parent.document.getElementById(\'jezykiObce\').innerHTML = \'Znane języki obce: brak\';';
        }
    }
    
    session_start();
    require("../conf.php");
    require("funkcje_ankieta.php");
    $database = pg_connect($con_str);
    $controls = new valControl();
    
    define('JEZYK', 'jezyk');
    define('JEZYK_ID', 'jezyk_id');
    define('POZIOM', 'poziom');
    define('POZIOM_ID', 'poziom_id');

    echo 'Znane języki obce:<br />';
	if (isset($_POST['dodaj_jezyk']))
	{           
        $_SESSION['jezyki'][$_POST['jezyk_dod_id']] = array(
                                                    JEZYK => $_POST['jezyk_dod'], 
                                                    JEZYK_ID => $_POST['jezyk_dod_id'], 
                                                    POZIOM => $_POST['poziom_dod'], 
                                                    POZIOM_ID => $_POST['poziom_dod_id']
                                                    ); 
        unset($_SESSION['nie_znam_jezykow']);       
	}
    if (isset($_POST['nie_znam_jezykow']))
    {
        unset($_SESSION['jezyki']);
        $_SESSION['nie_znam_jezykow'] = true;
        echo '<script>'.GenerujFormHtmlJs(array()).'parent.document.getElementById(\'popup\').style.display = \'none\';</script>';       
    }
    if (isset($_POST['usun_jezyk'])) 
    {
        if (isset($_SESSION['jezyki'][$_POST['id_jezyk_usun']]))
        {
            unset($_SESSION['jezyki'][$_POST['id_jezyk_usun']]);
        }
    }
    echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
    echo $controls->AddSubmit('nie_znam_jezykow', 'id_nie_znam_jezykow', 'Nie znam języka obcego', '');
    
    if (!empty($_SESSION['jezyki']) && is_array($_SESSION['jezyki']))
    {
        $znane_jezyki = PodajZnaneJezyki($_SESSION['jezyki'], $poziomy_id);
        $id_jezyk_obcy = implode(',', array_keys($_SESSION['jezyki']));
        
        echo '<table>';
        echo $controls->AddHidden('id_jezyk_usun', 'id_jezyk_usun', '');
        
        foreach ($znane_jezyki as $id_jezyk => $jezyk)
        {
            echo '<tr><td>'.$jezyk.'</td><td>';
            echo $controls->AddSubmit('usun_jezyk', $id_jezyk, 'Usuń', JsEvents::ONCLICK.'="document.getElementById(\'id_jezyk_usun\').value = this.id;"');
            echo '</td></tr>';
        }
        echo '</table>';
    }
    
    echo '<table>';
    echo '<tr><td>';
    if (!empty($id_jezyk_obcy))
    {
        $zap = "select id, nazwa from jezyki where id not in (".$id_jezyk_obcy.") order by nazwa asc;";
    }
    else
    {
        $zap = "select id, nazwa from jezyki order by nazwa asc;";
    }
    echo $controls->AddSelectRandomQuery('jezyk_dod', 'id_jezyk_dod', '', $zap, '', 'jezyk_dod_id');

	echo '</td><td>';
    echo $controls->AddSelectRandomQuery('poziom_dod', 'id_poziom_dod', '', 'select id, nazwa from poziomy;', '', 'poziom_dod_id'); 

	echo '</td><td>';
	echo $controls->AddSubmit('dodaj_jezyk', 'id_dodaj_jezyk', 'Dodaj', '');
	echo '</td></tr></table>';
	echo '<div style="margin-left: 387px;">';
	echo $controls->AddSubmit('Zamknij', 'id_Zamknij', 'Zamknij', 
        JsEvents::ONCLICK.'="'.GenerujFormHtmlJs(isset($_SESSION['jezyki']) ? $_SESSION['jezyki'] : array()).'parent.document.getElementById(\'popup\').style.display = \'none\'; return false;"');
	echo '</div></form>';
?>
</body>
</html>