<?php
    require_once '../conf.php';
    CommonUtils::outputBufferingOn();
    CommonUtils::SessionStart();
?>
<html><HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
  <script language="javascript" src="../js/queries_validation.js"></script>
  <script language="javascript" src="../js/utils.js"></script>
  <script language="javascript" src="../js/validations.js"></script>
<link href="../css/layout.css" rel="stylesheet" type="text/css"></head>
</head><body>
<?php
    include("../dal/klient.php");
    
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
        require_once '../bll/queries.php';
        require_once '../ui/UtilsUI.php';
        
        $queries = new QueriesEngine();
        
        if (isset($_POST['utworz_zapytanie']))
        {
            $wyb_uz = $_POST['wyb_uz'];
            $nazwa_zapytania = $_POST['nazwa_zapytania'];
            unset($_POST['utworz_zapytanie'], $_POST['nazwa_zapytania'], $_POST['wyb_uz']);
            
            $queries = new QueriesEngine($wyb_uz, $nazwa_zapytania);
            $errors = $queries->readFormData($_POST);
            if ($errors)
            {
                foreach ($errors as $error)
                    echo $error->getMessage().'<br />';
                    
                $_POST['tworzenie_zapytan'] = true;
                $data = $queries->getData();
            }
            else 
            {
                echo '<script>parent.frames[0].document.location.reload();</script>';
                require 'zapytania.php';
            }
        }
        
	    if (isset($_POST['tworzenie_zapytan']))
	    {
            $controls = new valControl();
            echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'" name="formwidok"><table><table>';
            echo "<tr><td>Przynależność:</td></tr>";
            echo "<tr><td><input type='radio' id='users_personal' name='wyb_uz' value='".$_SESSION[UZYTKOWNIK_ID]."' checked='checked'><label for='users_personal'>".$_SESSION['uzytkownik']."</label></td></tr>";
            echo "<tr><td><input type='radio' id='users_all' name='wyb_uz' value='3'><label for='users_all'>Wszyscy</label></td></tr><tr><td>Nazwa zapytania: ";
            echo $controls->AddTextbox('nazwa_zapytania', 'id_nazwa_zapytania', $queries->getFilterName(), 30, 30, '');
            echo "</td><td><input type='submit' name='utworz_zapytanie' value='Utwórz' class='formreset'></td></tr></table>
            <hr />
            <table class='gridTable' cellspacing='0'>";
            
            $columns = $queries->getColumns();
            $validations = $queries->getValidations();
            if(empty($data))
            {
                $count = 0;
                foreach ($columns as $column)
                {
                    $count++;
                    $rowCss = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
                    echo UtilsUI::searchFormElement($controls, $column, $validations, '', '', '', '', $rowCss);
                }
            }
            else
            {
                $count = 0;
                foreach ($columns as $column)
                {
                    $count++;
                    $rowCss = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
                    //$rank = $column[QueriesEngine::COLUMN_RANK];
                    $show = isset($data[$column[QueriesEngine::COLUMN_TABLE]][QueriesEngine::ACTION_SHOW][$column[QueriesEngine::COLUMN_NAME]]) ? 'checked="checked"' : '';//[$rank]
                    $negate = isset($data[$column[QueriesEngine::COLUMN_TABLE]][QueriesEngine::ACTION_NOT][$column[QueriesEngine::COLUMN_NAME]]) ? 'checked="checked"' : '';
                    $missing = isset($data[$column[QueriesEngine::COLUMN_TABLE]][QueriesEngine::ACTION_MISSING][$column[QueriesEngine::COLUMN_NAME]]) ? 'checked="checked"' : '';
                    $filter = isset($data[$column[QueriesEngine::COLUMN_TABLE]][QueriesEngine::ACTION_FILTER][$column[QueriesEngine::COLUMN_NAME]]) ? $data[$column[QueriesEngine::COLUMN_TABLE]][QueriesEngine::ACTION_FILTER][$column[QueriesEngine::COLUMN_NAME]] : '';
                    
                    echo UtilsUI::searchFormElement($controls, $column, $validations, $show, $negate, $filter, $missing, $rowCss);
                }
            }
            
            echo '</table></form>';
	    }
    }
    CommonUtils::sendOutputBuffer();
?>
</html>
