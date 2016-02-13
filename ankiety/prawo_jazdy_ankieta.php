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
    function GenerujFormHtmlJs ($prawaJazdy)
    {
        if (sizeof($prawaJazdy))
        {
            $id_kategorii = implode(',', array_keys($prawaJazdy));
            return 'parent.document.getElementById(\'prawoJazdy\').innerHTML = \'Posiadane prawa jazdy:<br />'.implode(', ', array_values($prawaJazdy)).'\'; 
            parent.document.getElementById(\'id_prawo_jazdy\').value = \''.$id_kategorii.'\';';
        }
        else
        {
            return 'parent.document.getElementById(\'prawoJazdy\').innerHTML = \'Posiadane prawa jazdy: brak\';';
        }
    }
    
    session_start();
    require("../conf.php"); 
    require("funkcje_ankieta.php");
    $database = pg_connect($con_str);
    $controls = new valControl();
    
    if (isset($_POST['dodaj_kategorie']))
    {           
        $_SESSION['kategorie'][$_POST['kategoria_dod_id']] = $_POST['kategoria_dod'];
        unset($_SESSION['nie_mam_prawa_jazdy']);        
    }
    if (isset($_POST['usun_kategoria'])) 
    {
        if (isset($_SESSION['kategorie'][$_POST['id_kategoria_usun']]))
        {
            unset($_SESSION['kategorie'][$_POST['id_kategoria_usun']]);
        }
    }
    if (isset($_POST['nie_mam_prawa_jazdy']))
    {
        unset($_SESSION['kategorie']);
        $_SESSION['nie_mam_prawa_jazdy'] = true; 
        echo '<script>'.GenerujFormHtmlJs(array()).'parent.document.getElementById(\'popup\').style.display = \'none\';</script>';     
    }
    
    echo 'Posiadane kategorie prawa jazdy:<br />';
    echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
    
    echo $controls->AddSubmit('nie_mam_prawa_jazdy', 'id_nie_mam_prawa_jazdy', 'Nie mam prawa jazdy', ''); 
	
    if (!empty($_SESSION['kategorie']))
    {
        $id_kategorii = implode(',', array_keys($_SESSION['kategorie']));
        
        echo '<table>';
        echo $controls->AddHidden('id_kategoria_usun', 'id_kategoria_usun', '');
        foreach ($_SESSION['kategorie'] as $id_kategoria => $kategoria)
        {
            echo '<tr><td>'.$kategoria.'</td><td>';
            echo $controls->AddSubmit('usun_kategoria', $id_kategoria, 'Usuń', JsEvents::ONCLICK.'="document.getElementById(\'id_kategoria_usun\').value = this.id;"');
            echo '</td></tr>';
        }
        echo '</table>';
    }

    echo '<table><tr><td>';

    if (!empty($id_kategorii))
    {
        $zap = "select id, nazwa from prawo_jazdy where id not in (".$id_kategorii.") order by nazwa asc;";
    }
    else
    {
        $zap = "select id, nazwa from prawo_jazdy order by nazwa asc;";        
    }
    echo $controls->AddSelectRandomQuery('kategoria_dod', 'id_kategoria_dod', '', $zap, '', 'kategoria_dod_id');
    
	echo '</td><td>';
    echo $controls->AddSubmit('dodaj_kategorie', 'id_dodaj_kategorie', 'Dodaj', '');
	echo("</td></tr></table>");
	echo '<div style="margin-left: 196px;">';
    echo $controls->AddSubmit('Zamknij', 'id_Zamknij', 'Zamknij', JsEvents::ONCLICK.'="'.GenerujFormHtmlJs(isset($_SESSION['kategorie']) ? $_SESSION['kategorie'] : array()).'parent.document.getElementById(\'popup\').style.display = \'none\'; return false;"'); 
	echo '</div></form>';
?>
</body>
</html>