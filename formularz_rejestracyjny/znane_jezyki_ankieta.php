<?php session_start(); 
$cssFile = 'ankieta';
if(false !== stripos($_SERVER['HTTP_USER_AGENT'], 'MSIE'))
    $cssFile .= '_ie';
?>
<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
  <script language="javascript" src="../js/validations.js"></script>
  <script language="javascript" src="../js/utils.js"></script>
  <script language="javascript" src="utils.js"></script>
<link href="../css/<?php echo $cssFile; ?>.css" rel="stylesheet" type="text/css">
</head>
<body onload="document.getElementById('04281b8a5f8524422522dfc1a13453b5').innerHTML = document.getElementById('04281b8a5f8524422522dfc1a13453b5').innerHTML;
document.getElementById('a2050ba4d06fe79ac013ce4749f21552').innerHTML = document.getElementById('a2050ba4d06fe79ac013ce4749f21552').innerHTML;
">
<?php

ini_set('display_errors', 0);
    //ten popierdolony onload jest dla ie 7
    
    
    function GenerujFormHtmlJs ($info)
    {
        //$znane_jezyki = PodajZnaneJezyki($jezyki, $poziomy_id);
        //$secret = $_SESSION['obfuscation_secret'];
        //$idForeignLanguage = md5('id_foreign_language_id'.$secret);
        //$idLanguageLevel = md5('id_language_level_id'.$secret);

        return 'parent.document.getElementById(\'jezykiObce\').innerHTML = \''.$info.'\';';

    }
    
    require_once '../conf.php'; 
    require_once '../bll/definicjeKlas.php';
    define('LANGUAGE_COLLECTION', 'languageCollection');
    $controls = new valControl();
    $languageCollection = isset($_SESSION[LANGUAGE_COLLECTION]) ? unserialize($_SESSION[LANGUAGE_COLLECTION]) : new JezykiObceCollection();
    
    //$languageCollection = new JezykiObceCollection();
    
    echo 'Znane języki obce:<br />';
	if (isset($_POST['dodaj_jezyk']))
	{           
        $jezyk = new JezykiObce();
        $jezyk->languageId = htmlspecialchars($_POST['jezyk_dod_id']);
        $jezyk->levelId = htmlspecialchars($_POST['poziom_dod_id']);
        $jezyk->langEntry = htmlspecialchars($_POST['jezyk_dod']).' - '.htmlspecialchars($_POST['poziom_dod']);
        
        $languageCollection->DodajJezyk($jezyk);      
	}
    if (isset($_POST['nie_znam_jezykow']))
    {
        $languageCollection->hasLanguage(false);
        echo '<script>'.GenerujFormHtmlJs($languageCollection->renderInfo()).'parent.document.getElementById(\'popup\').style.display = \'none\';</script>';       
    }
    if (isset($_POST['usun_jezyk'])) 
    {
        $languageCollection->UsunJezyk($_POST['id_jezyk_usun']);
    }
    echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
    echo $controls->AddSubmit('nie_znam_jezykow', 'id_nie_znam_jezykow', 'Nie znam języka obcego', '');
    
    if (($collection = $languageCollection->GetCollection()))
    {        
        echo '<table>';
        echo $controls->AddHidden('id_jezyk_usun', 'id_jezyk_usun', '');
        
        foreach ($collection as $jezyk)
        {
            echo '<tr><td>'.$jezyk->langEntry.'</td><td>';
            echo $controls->AddSubmit('usun_jezyk', $jezyk->languageId, 'Usuń', JsEvents::ONCLICK.'="document.getElementById(\'id_jezyk_usun\').value = this.id;"');
            echo '</td></tr>';
        }
        echo '</table>';
    }
    
    $id = md5('id_jezyk_dod');
    
    if (($idList = $languageCollection->getIdList()))
    {
        $zap = "select id, nazwa from jezyki where id not in (".implode(',', $idList).") order by nazwa asc;";
    }
    else
    {
        $zap = "select id, nazwa from jezyki order by nazwa asc;";
    }
    
    $srcJezyki = $controls->dalObj->PobierzDane($zap, $remainingLangsCount);
    $srcPoziomy = $controls->dalObj->PobierzDane('select id, nazwa from poziomy;');
    
    if ($remainingLangsCount > 0) {
        
        echo '<table align="CENTER"><tr><td>';
        echo valControl::_AddSelectWithData('jezyk_dod', 'id_jezyk_dod', '', '', $srcJezyki, null, 'jezyk_dod_id', 1, 'leftFloat selectMinMargin');
	    echo '</td><td>';
        echo valControl::_AddSelectWithData('poziom_dod', 'id_poziom_dod', '', '', $srcPoziomy, null, 'poziom_dod_id', 2, 'leftFloat selectMinMargin'); 
	    echo '</td><td>';
	    echo $controls->AddSubmit('dodaj_jezyk', 'id_dodaj_jezyk', 'Dodaj', '');
        echo '</td></tr></table>';
    }
    
	echo '<div align="CENTER">';
	echo $controls->AddSubmit('Zamknij', 'id_Zamknij', 'Zamknij', 
        JsEvents::ONCLICK.'="'.GenerujFormHtmlJs($languageCollection->renderInfo()).'parent.document.getElementById(\'popup\').style.display = \'none\'; return false;"');
	echo("</div></form>");
    
    $_SESSION[LANGUAGE_COLLECTION] = serialize($languageCollection);
?>
</body>
</html>