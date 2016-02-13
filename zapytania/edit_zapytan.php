<?php
    require_once '../conf.php';
    CommonUtils::outputBufferingOn();
    CommonUtils::SessionStart();
?>
<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
  <script language="javascript" src="../js/queries_validation.js"></script>
  <script language="javascript" src="../js/utils.js"></script>
  <script language="javascript" src="../js/validations.js"></script>
<link href="../css/layout.css" rel="stylesheet" type="text/css"></head>
</head>
<?php

    include '../dal/klient.php';
    if (empty($_SESSION['uzytkownik']))
    {
        require '../log_in.php';
    }
    else
    {
        require_once '../bll/queries.php';
        require_once '../ui/UtilsUI.php';
        
        if (isset($_POST['nadpisz_zapytanie']) || isset($_POST['dodaj_zapytanie']))
        {
            $wyb_uz = $_POST['wyb_uz'];
            $nazwa_zapytania = $_POST['nazwa_zapytania'];
            $id_kwerenda = $_POST['kwerenda_id'];
            unset($_POST['nadpisz_zapytanie'], $_POST['nazwa_zapytania'], $_POST['wyb_uz']);
            
            $queries = new QueriesEngine($wyb_uz, $nazwa_zapytania, $id_kwerenda);
            if (isset($_POST['dodaj_zapytanie']))
            {
                $queries->setAddNew();
            }
            
            $errors = $queries->readFormData($_POST);
            if ($errors)
            {
                foreach ($errors as $error)
                    echo $error->getMessage().'<br />';
                    
                $_POST['edycja_zapytan'] = true;
            }
            else 
            {
                echo '<script>parent.frames[0].document.location.reload();</script>';
                require 'zapytania.php';
            }
        }
        if (isset($_POST['usun_zapytanie']))
        {
            $kasowanie = "delete from kwerendy where id = ".$_SESSION['kwerenda'].";";
            $database = pg_connect($con_str);
            $wynik = pg_query($database, $kasowanie);
            if ($wynik)
            {
                echo "Wykasowano.";
                echo("<script>parent.frames[0].document.location.reload();</script>");
                $query = "select id from kwerendy order by nazwa limit 1;";
                $res = pg_query($database,$query);
                $rov = pg_fetch_array($res);
                $_SESSION['kwerenda'] = $rov['id'];
                require("zapytania.php");
            }
        }
        
	    if (isset($_POST['edycja_zapytan']))
	    {
            $id_kwerenda = $_SESSION['kwerenda'];
            if ($_POST['id_kwerendy'])
            {
                $id_kwerenda = $_POST['id_kwerendy'];
            }
            $queries = new QueriesEngine('', '', $id_kwerenda); 
            $controls = new valControl();
            
            if ($queries->isOwner())
            {
                $userSelected = 'checked';
                $allSelected = '';
            }    
            else
            {
                $userSelected = '';
                $allSelected = 'checked="checked"';
            }
		    echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'" name="formwidok"><table border="1"><table>';
            echo $controls->AddHidden('kwerenda_id', 'kwerenda_id', $_SESSION['kwerenda']);
            echo '<tr><td>Przynależność:</td></tr><tr><td><input type="radio" id="users_personal" name="wyb_uz" value="'.$_SESSION[UZYTKOWNIK_ID].'" '.$userSelected.'>
            <label for="users_personal">'.$_SESSION['uzytkownik'].'</label></td></tr>';
            echo '<tr><td><input type="radio" id="users_all" name="wyb_uz" value="3" '.$allSelected.'><label for="users_all">Wszyscy</label></td></tr>
            <tr><td>Nazwa zapytania: ';
            echo $controls->AddTextbox('nazwa_zapytania', 'id_nazwa_zapytania', $queries->getFilterName(), 30, 30, '');
            echo '</td><td>';
            echo $controls->AddSubmit('nadpisz_zapytanie', 'id_nadpisz_zapytanie', 'Zapisz', ''); 
            echo '</td><td>';
            echo $controls->AddSubmit('dodaj_zapytanie', 'id_dodaj_zapytanie', 'Dodaj nowe', ''); 
            echo '</td><td>';
            echo $controls->AddDeleteSubmit('usun_zapytanie', 'id_usun_zapytanie', 'Kasuj'); 
            echo '</td></tr></table><hr /><table class="gridTable" cellspacing="0">';
            
            $columns = $queries->getColumns();
            $validations = $queries->getValidations();
            $data = $queries->getData();
            $count = 0;
            
            foreach ($columns as $column)
            {
                $count++;
                $rowCss = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
                //$rank = $column[QueriesEngine::COLUMN_RANK];
                $show = isset($data[$column[QueriesEngine::COLUMN_TABLE]][QueriesEngine::ACTION_SHOW][$column[QueriesEngine::COLUMN_NAME]]) ? 'checked="checked"' : ''; //[$rank]
                $negate = isset($data[$column[QueriesEngine::COLUMN_TABLE]][QueriesEngine::ACTION_NOT][$column[QueriesEngine::COLUMN_NAME]]) ? 'checked="checked"' : '';
                $missing = isset($data[$column[QueriesEngine::COLUMN_TABLE]][QueriesEngine::ACTION_MISSING][$column[QueriesEngine::COLUMN_NAME]]) ? 'checked="checked"' : '';
                $filter = isset($data[$column[QueriesEngine::COLUMN_TABLE]][QueriesEngine::ACTION_FILTER][$column[QueriesEngine::COLUMN_NAME]]) ? $data[$column[QueriesEngine::COLUMN_TABLE]][QueriesEngine::ACTION_FILTER][$column[QueriesEngine::COLUMN_NAME]] : '';
                
                echo UtilsUI::searchFormElement($controls, $column, $validations, $show, $negate, $filter, $missing, $rowCss);
            }
            
            echo '</table></form>';
	    }
    }
    CommonUtils::sendOutputBuffer();
?>
</html>
