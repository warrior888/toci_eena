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
<body>
<?php
//ini_set('display_errors', 0);
        require("../naglowek.php");
	    require("../conf.php");
        include("../bll/definicjeKlas.php");
        include("../prawa_strona/f_image_operations.php");
        define('SKILLS_COLLECTION', 'dodUmCollection');
        
        $dal = new dal();
        $controls = new valControl();
        $dodUm = new DodatkoweUmiejetnosci();
        $dodUmTab = new DodatkoweUmiejetnosciTab();
        $dodUmCollection = isset($_SESSION[SKILLS_COLLECTION]) ? unserialize($_SESSION[SKILLS_COLLECTION]) : new DodatkoweUmiejetnosciCollection();
        //if (! $dodUmCollection instanceof DodatkoweUmiejetnosciCollection)
        //    $dodUmCollection = new DodatkoweUmiejetnosciCollection();
        
        $umiejetnosci = '';
        if (isset($_POST['umiejetnosci']))
        {
            $umiejetnosci = htmlspecialchars($_POST['umiejetnosci']);
        }
        
        $divId = 'dodatkoweUmiejetnosci';
        
        if (isset($_POST['nie_mam_umiejetnosci']))
        {
            $dodUmCollection->hasSkills(false);
            echo '<script>parent.document.getElementById(\'popup\').style.display = \'none\';</script>'; 
            //parent.document.getElementById("'.$divId.'").innerHTML = "'.$dodUmCollection->renderInfo().'";
        }
    
        if (isset($_POST['erase']))
        {
            //$dodUmCollection = unserialize($_SESSION['dodUmCollection']);
            $dodUmCollection->UsunUmiejetnosc($_POST['col_id']);
            if ($dodUmCollection->GetCount() == 0)
            {
                echo '<script language="javascript">parent.document.getElementById("'.$divId.'").innerHTML = \'\';</script>';
            }
            //$_SESSION['dodUmCollection'] = serialize($dodUmCollection);
        }
        if (isset($_POST['potwierdz']))
        {
            //if (isset($_SESSION['dodUmCollection']))
            //{
            //    $dodUmCollection = unserialize($_SESSION['dodUmCollection']);
            //}
            
            ////TODO strip tags instead?
            $dodUm->dodUm = htmlspecialchars($_POST['dodUmName']);
            $dodUm->dodUmId = htmlspecialchars($_POST['dodUmId']);
            $dodUmCollection->DodajUmiejetnosc($dodUm);
            //$_SESSION['dodUmCollection'] = serialize($dodUmCollection);
        }
        
        if (isset($_SESSION['dodUmCollection']))
        {
            //$dodUmCollection = unserialize($_SESSION['dodUmCollection']);
            $collection = $dodUmCollection->GetCollection();
            if (count($collection) > 0)
            {
                echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">'; 
                echo '<table class="gridTable" border="0" cellspacing="0"><tr>';
                echo "<th>Kasowanie</th>";
                echo "<th>Umiejętność</th>";
                echo '</tr>';
                echo $controls->AddHidden('id_col_id', 'col_id', '');
                
                $i = 0;
                for($i = 0; $i < count($collection); $i++)
                {
                    $css = (($i % 2) == 0) ? 'oddRow' : 'evenRow';
                    $dodUm = $collection[$i];
                    echo '<tr class="'.$css.'"><td>';
                    echo $controls->AddSubmit("erase", $i, "Usuń.", "onclick='col_id.value=this.id;'");
                    echo '</td><td>';
                    echo $dodUm->dodUm;
                    echo '</td>';
                }
                echo '</table>';
                echo '</form>';
            }

            echo '<script language="javascript">parent.document.getElementById("'.$divId.'").innerHTML = "'.$dodUmCollection->renderInfo().'";</script>';
            //$_SESSION['dodUmCollection'] = serialize($dodUmCollection);
        }

        echo "<form method='POST' action='".$_SERVER['PHP_SELF']."'>";
        echo $controls->AddSubmit('nie_mam_umiejetnosci', 'id_nie_mam_umiejetnosci', 'Nie mam umiejętności', ''); 
        
        echo "<table border='0'>";
        echo "<tr><td>".$controls->AddSeekTextbox("umiejetnosci", $umiejetnosci, "Umiejetnosci", 30, 30)."</td><td>";
        echo $controls->AddSubmit('szukaj_umiejetnosci', 'id_szukaj_umiejetnosci', 'Szukaj', '');   
        echo "</td></tr></table></form>";
        echo $controls->AddSubmit('Zamknij', 'id_Zamknij', 'Zamknij', JsEvents::ONCLICK.'="parent.document.getElementById(\'popup\').style.display = \'none\'; return false;"');
        
        echo '<hr /><form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
        echo $controls->AddHidden('id_dodUmId', 'dodUmId', '');
        echo $controls->AddHidden('id_dodUmName', 'dodUmName', '');
        echo "<table border='0'>";

        $tableName = $dodUmTab->Name();
        $where = "";
        if (isset($_SESSION['dodUmCollection']))
        {
            //$dodUmCollection = unserialize($_SESSION['dodUmCollection']);
            $collection = $dodUmCollection->GetCollection();
            if (count($collection) > 0)
            {
                for($i = 0; $i < count($collection); $i++)
                {
                    $dodUm = $collection[$i];
                    $where .= $dodUm->dodUmId.",";
                }
            }
        }
        
        if (strlen($where) > 0)
        {
            $where = substr($where, 0, strlen($where) - 1);   
            $where = "and id not in (".$where.")";
        }
        
        $Filter = "select id, nazwa as umiejetnosc from $tableName where lower(nazwa) like lower('%".$umiejetnosci."%') $where order by nazwa asc;";
        $result = $controls->dalObj->PobierzDane($Filter, $ilosc_wierszy);
        if ($ilosc_wierszy < 1)
        {
            echo "Zapytanie nie zwrociło wyników. Rozszerz kryterium.";
        }
        foreach ($result as $row)
        {
            echo "<tr><td>";
            echo $controls->AddSubmit('potwierdz', 'id_potwierdz', 'Wybierz', 'onclick="document.getElementById(\'id_dodUmName\').value = \''.htmlspecialchars($row['umiejetnosc']).'\'; document.getElementById(\'id_dodUmId\').value = \''.$row['id'].'\';"', '');

            echo "</td><td>".$row['umiejetnosc']."</td></tr>";
        }
        echo "</table>";
        echo "</form>";

        $_SESSION['dodUmCollection'] = serialize($dodUmCollection);
        //if one of choices are being updated, there is no need to enable "add new form"
        require("../stopka.php");
?>
</body>
</html>