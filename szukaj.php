<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <link href="css/layout.css" rel="stylesheet" type="text/css">
  <script language="javascript" src="js/script.js"></script>
</head>
<?php
    session_start();
    if (empty($_SESSION['uzytkownik']))
    {
        require("log_in.php");
        die();
    }
    else if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        require("naglowek.php");
        require("conf.php");
        include("prawa_strona/f_image_operations.php");

        $controls = new valControl();
        unset($_SESSION['edycja_masowa']);
        unset($_SESSION['kwerenda_sql']);
        unset($_SESSION['widok_sql']);
        unset($_SESSION['wakaty_umowieni']);
        unset($_SESSION['wakaty_zainteresowani']);
        if (isset($_POST['szukaj']))
        {
            $sql = "select * from szukaj ";
            if ((isset($_POST['nazwisko']) && strlen($_POST['nazwisko']) > 0) || (isset($_POST['data']) && strlen($_POST['data']) == 10))
            {
                if ((!empty($_POST['nazwisko'])) && (empty($_POST['data'])))
                {
                     $where = "where lower(nazwisko) LIKE lower('".$controls->dalObj->escapeString($_POST['nazwisko'])."')";
                }
                else if ((!empty($_POST['nazwisko'])) && (!empty($_POST['data'])))
                {
                    $where = "where lower(nazwisko) LIKE lower('".$controls->dalObj->escapeString($_POST['nazwisko'])."')and data_urodzenia = '".$controls->dalObj->escapeString($_POST['data'])."'";
                }
                else
                {
                    $where = "where data_urodzenia = '".$controls->dalObj->escapeString($_POST['data'])."'";
                }
            }
            else
            {
                die('Brak kryteriów wyszukiwania.'); 
            }
            $sql .= $where.' order by id asc';
            $_SESSION['szukaj_sql'] = isset($_SESSION['szukaj_sql']) ? $_SESSION['szukaj_sql'] : null;
            $sql_tmp = trim(substr($sql, 0, strpos($sql, "order by")));
            $sql_ses = trim(substr($_SESSION['szukaj_sql'], 0, strpos($_SESSION['szukaj_sql'], "order by")));

            if (strcmp($sql_ses, $sql_tmp) == 0)
                $sql = trim(str_replace(";", " ", $_SESSION['szukaj_sql']));
                
            if (!empty($where))
            {
                $sql .= " ";
		        $_SESSION['z'] = $sql;
                $_SESSION['szukaj_sql'] = $sql;
                $count = $controls->dalObj->PobierzDane('select count(*) as ilosc from szukaj '.$where);
                $_SESSION['rekordy_szukaj_ilosc'] = $count[0]['ilosc'];
            }
        }
        
        $sql = isset($_SESSION['z']) ? $_SESSION['z'] : die('Brak kryteriów wyszukiwania.');
        $ile = (int)$_SESSION['rekordy_szukaj_ilosc'];
        
        if (isset($_POST['ile']))
        {
            $p = $_POST['ile'] * $_SESSION['ilosc_rekordow'];
            $sql = substr($sql, 0, strlen($sql) - 1);
            $sql .= " LIMIT ".$_SESSION['ilosc_rekordow']." OFFSET ".$p.";";
        } else {
            $sql .= ' LIMIT '.$_SESSION['ilosc_rekordow'].';';
        }
        
        $wynik = $controls->dalObj->PobierzDane($sql);

        if ($ile != 0)
        {
         	echo("<form method=\"POST\" action='szukaj.php'><table align = \"CENTER\"><tr>");
            echo $controls->AddHidden('id_ile', 'ile', '');
            $ilStron = $ile / $_SESSION['ilosc_rekordow'];
            for ($j = 0; $j < ($ilStron); $j++)
            {
                echo "<td>";
                echo $controls->AddSubmit($j, 'id', ($j + 1), JsEvents::ONCLICK.'="ile.value = this.name;"');
                echo "</td>";
            }
            
            $odlamki_nag = explode(',', 'Id, Imię, Nazwisko, Płeć, Data urodzenia, Miejscowość, Ulica, Kod, Wykształcenie, Konsultant');
            echo '</tr></table></form>';
			echo '<form action="edit/przetwarzaj_dane_osobowe.php" method="GET"><table class="gridTable" border="0" cellspacing="0">';
            echo valControl::_RowsCount($ile); 

			echo $controls->AddHidden('id_id_os', 'id_os', '');
			echo $controls->AddHidden('id_id_zetel', 'id_zetel', '');
		    
		    require 'buttons_nag.php';
		    setHeadingRow($odlamki_nag);
            $count = 0;
	        foreach ($wynik as $wiersz)
	        {
                $count++;
                $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';  
				echo '<tr class="'.$css.'" onmouseover="markRow(this, \'hoveredRow\');" onmouseout="markRow(this, \''.$css.'\');" onclick="pointRow(this, \'markedRow\');"> ';
				require 'buttons.php';
                addRowsToTable($wiersz);
				echo '</tr>';
            }
		   	echo '</table></form>';
         }
         else
         {
            echo 'Niestety nikt taki sie nie znalazł...';
         }
         require("stopka.php");
    }
?>
</html>
