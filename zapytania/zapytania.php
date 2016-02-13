<?php session_start(); ?>
<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>

<link href="../css/layout.css" rel="stylesheet" type="text/css"></head>
</head>
<?php
    if (empty($_SESSION['uzytkownik']))
    {
        require '../log_in.php';
    }
    else
    {
        require '../naglowek.php';
	    require '../conf.php';
        include_once '../vaElClass.php'; 
        require_once '../bll/queries.php';
        require_once '../ui/UtilsUI.php';
        include '../prawa_strona/f_image_operations.php';
        if (!empty($_POST['kwerenda']) && $_POST['kwerenda'] == '--------')
        {
            require("../stopka.php");
            die();
        }
	    if (!empty($_POST['kwerenda']) || isset($_SESSION['kwerenda']))
        {
            $controls = new valControl();
            $page = 0;
            if(isset($_POST['wyb_strona']))
            {
                $page = $_POST['wyb_strona'];
            }
            if (!empty($_POST['kwerenda'])) 
            {
                $_SESSION['kwerenda'] = $_POST['kwerenda'];
            }

            $queries = new QueriesEngine('', '', $_SESSION['kwerenda']);
            

            unset($_SESSION['widok_sql']);
            unset($_SESSION['szukaj_sql']);
            unset($_SESSION['wakaty_umowieni']);
            unset($_SESSION['wakaty_zainteresowani']);

            $start = microtime(true);
            try {
                list($wynik, $iloscRek) = $queries->runQueries($page, $_SESSION['ilosc_rekordow'], isset($_POST['hidden_kier_sort_osoba']) ? $_POST['hidden_kier_sort_osoba'] : '', isset($_POST['hidden_kol_sort_osoba']) ? $_POST['hidden_kol_sort_osoba'] : '');
            } catch (TooManyRowsException $e){
                die(sprintf('Zapytanie zwraca za dużo rekordów (%s). Zbyt ogólnikowe dane.', $e->getCode()));
            }
            $end = microtime(true);
            $diff = $end - $start;
            echo '<br /> Czas: '.$diff.'<br />';
            
            if (sizeof($wynik) > 0)
            {
                $naglowki = $queries->GetHeaders();

                echo '<form action="'.$_SERVER['PHP_SELF'].'" name="osoby_kwerendy_stronicowanie" method="POST">';
                echo $controls->AddHidden('kwerenda', 'kwerenda', $_SESSION['kwerenda']);
                echo $controls->AddHidden('hidden_kier_sort_osoba', 'hidden_kier_sort_osoba', isset($_POST['hidden_kier_sort_osoba']) ? $_POST['hidden_kier_sort_osoba'] : '');
                echo $controls->AddHidden('hidden_kol_sort_osoba', 'hidden_kol_sort_osoba', isset($_POST['hidden_kol_sort_osoba']) ? $_POST['hidden_kol_sort_osoba'] : '');
                echo $controls->AddHidden('wyb_strona', 'wyb_strona', '');
                echo '</form>';
                
			    echo "<form action='../edit/przetwarzaj_dane_osobowe.php' name='osoby_kwerendy' method='GET'>";              
                $grid = new UtilsUI('grid_osoba', 'id_os');
                $grid->setHeaders($naglowki);
                $grid->setVisibleColumns($naglowki);
                $grid->setSort('parent.frames[0].document.kwerendy_form', '', 'parent.frames[0].document.kwerendy_form.hidden_kier_sort_osoba', 'parent.frames[0].document.kwerendy_form.hidden_kol_sort_osoba');
                echo $grid->displayData($wynik, $iloscRek, $page);
                
		        echo "</form>";
            }
            else
            {
                echo 'Brak wyników dla zadanych kryteriów zapytania.';
            }
	    }
        require '../stopka.php';
    }
?>
</html>
