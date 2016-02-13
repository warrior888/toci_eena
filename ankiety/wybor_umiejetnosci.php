<?php
    session_start();
?>
<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
  <link href="style_form_box.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php
//<link href="../css/ankieta.css" rel="stylesheet" type="text/css">
        
        require("../naglowek.php");
	    require("../conf.php");
        include("../bll/definicjeKlas.php");
        include("../prawa_strona/f_image_operations.php");
        
        $controls = new valControl();
        $dodUm = new DodatkoweUmiejetnosci();
        $dodUmTab = new DodatkoweUmiejetnosciTab();
        $dodUmCollection = new DodatkoweUmiejetnosciCollection();
        
        $umiejetnosci = '';
        if (isset($_POST['umiejetnosci']))
        {
            $umiejetnosci = $_POST['umiejetnosci'];
        }
        
        $divId = 'DUmiejetnosci';
        
        if (isset($_POST['erase']))
        {
            $dodUmCollection = unserialize($_SESSION['dodUmCollection']);
            $dodUmCollection->UsunUmiejetnosc($_POST['col_id']);
            if ($dodUmCollection->GetCount() == 0)
            {
                echo '<script language="javascript">parent.document.getElementById("'.$divId.'").innerHTML = \'\';</script>';
            }
            $_SESSION['dodUmCollection'] = serialize($dodUmCollection);
        }
        if (isset($_POST['potwierdz']))
        {
            if (isset($_SESSION['dodUmCollection']))
            {
                $dodUmCollection = unserialize($_SESSION['dodUmCollection']);
            }
            $dodUm->dodUm = $_POST['dodUmName'];
            $dodUm->dodUmId = $_POST['dodUmId'];
            $dodUmCollection->DodajUmiejetnosc($dodUm);
            $_SESSION['dodUmCollection'] = serialize($dodUmCollection);
        }
        
        if (isset($_SESSION['dodUmCollection']))
        {
            $dodUmCollection = unserialize($_SESSION['dodUmCollection']);
            $collection = $dodUmCollection->GetCollection();
            if (count($collection) > 0)
            {
                echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">'; 
                echo '<table border="1"><tr style="color: #FFFFFF;font-weight: bold;">';
                $divContent = 'Dodatkowe umiejętności: <br />';
                echo "<td bgcolor=\"#052B8C\">Usuń</td>";
                echo "<td bgcolor=\"#052B8C\">Umiejętność</td>";
                echo '</tr>';
                echo $controls->AddHidden('id_col_id', 'col_id', '');
                
                $i = 0;
                for($i = 0; $i < count($collection); $i++)
                {
                    $dodUm = $collection[$i];
                    echo '<tr><td>';
                    echo $controls->AddSubmit("erase", $i, "Usuń.", "onclick='col_id.value=this.id;'");
                    echo '</td><td>';
                    echo $dodUm->dodUm;
                    echo '</td>';
                    $divContent .= $dodUm->dodUm.'<br />';
                }
                echo '</table>';
                echo '</form>';
            }
            else
            {
                $divContent = '';
            }
            echo '<script language="javascript">parent.document.getElementById("'.$divId.'").innerHTML = "'.$divContent.'";</script>';
            $_SESSION['dodUmCollection'] = serialize($dodUmCollection);
        }

        echo "<table border='0'><form method='POST' action='".$_SERVER['PHP_SELF']."'>";
        echo "<tr><td>".$controls->AddSeekTextbox("umiejetnosci", $umiejetnosci, "Umiejetnosci", 30, 30)."</td><td>";
        echo $controls->AddSubmit('szukaj_umiejetnosci', 'id_szukaj_umiejetnosci', 'Szukaj', '');   
        echo "</td></tr></form></table>";
        echo $controls->AddSubmit('Zamknij', 'id_Zamknij', 'Zamknij', JsEvents::ONCLICK.'="parent.document.getElementById(\'popup\').style.display = \'none\'; return false;"');
        
        echo '<hr /><form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
        echo $controls->AddHidden('id_dodUmId', 'dodUmId', '');
        echo $controls->AddHidden('id_dodUmName', 'dodUmName', '');
        echo '<table border="0" class="buttons">';

        $tableName = $dodUmTab->Name();
        $where = "";
        if (isset($_SESSION['dodUmCollection']))
        {
            $dodUmCollection = unserialize($_SESSION['dodUmCollection']);
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
            echo "<tr><td>".$row['umiejetnosc']."</td><td><input type='submit' name='potwierdz' value='Wybierz' onClick = \"dodUmId.value = '".$row['id']."'; dodUmName.value = '".$row['umiejetnosc']."'\" /></td></tr>";
        }
        echo "</table>";
        echo "</form>";

        //if one of choices are being updated, there is no need to enable "add new form"
        require("../stopka.php");
?>
</body>
</html>