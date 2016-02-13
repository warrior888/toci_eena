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
<body onload="document.getElementById('0fe8ca4cf8db37083eac59233352948d').innerHTML = document.getElementById('0fe8ca4cf8db37083eac59233352948d').innerHTML;">
<?php
//
ini_set('display_errors', 0);
    function GenerujFormHtmlJs ($rendered)
    {
        //$id_kategorii = implode(',', array_keys($prawaJazdy));
        //$secret = $_SESSION['obfuscation_secret'];
        //$idDriversLicense = md5('id_drivers_license_id'.$secret);
        return 'parent.document.getElementById(\'prawoJazdy\').innerHTML = \''.$rendered.'\';';
        //parent.document.getElementById(\''.$idDriversLicense.'\').value = \''.$id_kategorii.'\';

    }
    
    
    require_once '../conf.php'; 
    require_once '../bll/definicjeKlas.php';
    define('LICENSE_COLLECTION', 'licenseCollection');
    $controls = new valControl();
    $licenseCollection = isset($_SESSION[LICENSE_COLLECTION]) ? unserialize($_SESSION[LICENSE_COLLECTION]) : new PrawoJazdyCollection();
    
    //$licenseCollection = new PrawoJazdyCollection();

    if (isset($_POST['dodaj_kategorie']))
    {
        $prawko = new PrawoJazdy();
        $prawko->licenseId = htmlspecialchars($_POST['kategoria_dod_id']);
        $prawko->licenseName = htmlspecialchars($_POST['kategoria_dod']);
        
        $licenseCollection->DodajPrawo($prawko);        
    }
    if (isset($_POST['usun_kategoria'])) 
    {
        $licenseCollection->UsunPrawo($_POST['id_kategoria_usun']);
    }
    if (isset($_POST['nie_mam_prawa_jazdy']))
    {
        $licenseCollection->hasLicense(false);
        echo '<script>'.GenerujFormHtmlJs($licenseCollection->renderInfo()).'parent.document.getElementById(\'popup\').style.display = \'none\';</script>';     
    }
    
    echo 'Posiadane kategorie prawa jazdy:<br />';
    echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
    
    echo $controls->AddSubmit('nie_mam_prawa_jazdy', 'id_nie_mam_prawa_jazdy', 'Nie mam prawa jazdy', ''); 
	
    if (($collection = $licenseCollection->GetCollection()))
    {
        
        echo '<table>';
        echo $controls->AddHidden('id_kategoria_usun', 'id_kategoria_usun', '');
        foreach ($collection as $id_kategoria => $kategoria)
        {
            echo '<tr><td>'.$kategoria->licenseName.'</td><td>';
            echo $controls->AddSubmit('usun_kategoria', $kategoria->licenseId, 'Usuń', JsEvents::ONCLICK.'="document.getElementById(\'id_kategoria_usun\').value = this.id;"');
            echo '</td></tr>';
        }
        echo '</table>';
    }

    

    if (($idList = $licenseCollection->getIdList()))
    {
        $zap = "select id, nazwa from prawo_jazdy where id not in (".implode(',', $idList).") order by nazwa asc;";
    }
    else
    {
        $zap = "select id, nazwa from prawo_jazdy order by nazwa asc;";        
    }
    
    $dane = $controls->dalObj->PobierzDane($zap, $remainingLicensesCount);
    
    if ($remainingLicensesCount > 0) {
        
        echo '<table align="CENTER"><tr><td>';
        echo valControl::_AddSelectWithData('kategoria_dod', 'id_kategoria_dod', '', '', $dane, null, 'kategoria_dod_id', 1, 'leftFloat selectMinMargin');
	    echo '</td><td>';
        echo $controls->AddSubmit('dodaj_kategorie', 'id_dodaj_kategorie', 'Dodaj', '');
        echo '</td></tr></table>';
    }
	
	echo("<div align='CENTER'>");
    echo $controls->AddSubmit('Zamknij', 'id_Zamknij', 'Zamknij', JsEvents::ONCLICK.'="'.GenerujFormHtmlJs($licenseCollection->renderInfo()).'parent.document.getElementById(\'popup\').style.display = \'none\'; return false;"'); 
	echo("</div></form>");
    
    $_SESSION[LICENSE_COLLECTION] = serialize($licenseCollection);
?>
</body>
</html>