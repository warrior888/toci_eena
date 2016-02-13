<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
  <script language="javascript" src="../js/utils.js"></script>
  <script language="javascript" src="../js/validations.js"></script>
<link href="../css/layout.css" rel="stylesheet" type="text/css"></head>
</head>
<?php
    require_once '../conf.php';
    require_once 'bll/strukturaKlas.php';
    require_once 'bll/definicjeKlas.php';
    require_once 'bll/utilsbll.php';
    
    $table = new TableLevelOne();
    $controls = new valControl();
    //$avDictionaries = AvDicts::Dictionaries();
    @session_start();
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
        require("../naglowek.php");
	    
        $element = '';
        //uzywane zapewne w wyjatkowej sytuacji zastosowania skryptu przez get
        if (isset($_GET['element']))
        {
            $element = $_GET['element'];
        }
        if (isset($_POST['potwierdz']))
        {
            $zapytanie = "select ".$_POST['id']." from ".$_POST['name']." where lower(nazwa) = lower('".$_POST['nazwa']."');";
            $database = pg_connect($con_str);
            $wynik = pg_query($database, $zapytanie);
            if (pg_num_rows($wynik) > 0)
            {
                echo "Informacja już widnieje w słowniku.";
            }
            else
            {
                $tabela = pg_escape_string($_POST['name']);
                $nazwa = pg_escape_string($_POST['nazwa']);
            	if($_POST['element'] == 'msc_odjazdu') {
            		$query = "insert into ".$tabela." (nazwa, panstwo_id) values ('".$nazwa."','".(int)$_POST['panstwo_id']."');";
                    $query .= " insert into strefy (przewoznik_id, msc_odjazdu_id, strefa_id) VALUES (4, (SELECT id FROM $tabela WHERE nazwa = '$nazwa'), ".(int)$_POST['strefa_id'].");";
                    $query .= " insert into strefy (przewoznik_id, msc_odjazdu_id, strefa_id) VALUES (5, (SELECT id FROM $tabela WHERE nazwa = '$nazwa'), ".(int)$_POST['strefa_id'].");";
            	} else {
            		//ten post nazwa podlega wymianie, tu to tak zadziala bez komplikowania sobie zycia :P
                	$query = "insert into ".$tabela." (nazwa) values ('".$nazwa."');";
        		}
                $wynik = pg_query($database,$query);
                $query = "select count(id) from ".$tabela.";";
                $wynik = pg_query($database, $query);
                $wiersz = pg_fetch_array($wynik);
                echo "W słowniku widnieje: ".$wiersz[0]." rekordów.";
                PermanentCache::delete(CACHED_DICTIONARIES);
            }
            if ($element == "")
            {
                $element = $_POST['element'];
            }
        }
        $dictObj = AvDicts::Dictionaries($element);
        $table->TableConfig($dictObj->Def(), $dictObj->Name(), $dictObj->Id(), $dictObj->ShowName(), $dictObj->FieldLength());
        echo '<table><form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
        $controls->AddHiddenTableConfig($dictObj->Id(), $dictObj->Name());
        echo $controls->AddHidden('id_element', 'element', $element);
        //foreach ($table->tabLODef as $key => $value)
        $i = 0;
        while(isset($table->tabLODef[$i]))
        {
            echo '<tr><td>'.$table->tabLOShowName[$i].':</td><td>';
            echo $controls->AddTextbox($table->tabLODef[$i], $table->tabLODef[$i], "", $table->tabLOFieldLength[$i], $table->tabLOFieldLength[$i], "").'</td></tr>';
            $i++;
        }

        if($element == 'msc_odjazdu') {
            $listStref = $controls->dalObj->PobierzDane("select id, nazwa from strefa_odjazdu order by nazwa asc;");
            echo '<tr><td>Strefa:</td><td>';
            echo $controls->AddSelectWithData('strefa', 'strefa', "", $listStref, null, 'strefa_id', '').'</td></tr>';

            $listCountry = $controls->dalObj->PobierzDane("select id, nazwa from panstwo order by nazwa asc;");
            echo '<tr><td>Państwo:</td><td>';
            echo $controls->AddSelectWithData('panstwo', 'panstwo', "", $listCountry, 2, 'panstwo_id', '').'</td></tr>';
            $i++;
        }

        echo '<tr><td>'.$controls->AddSubmit("potwierdz", "potwierdz", "Dodaj.", "").'</td></tr>';
        echo '</form><table>';
                
        require("../stopka.php");
    }
?>
</html>
