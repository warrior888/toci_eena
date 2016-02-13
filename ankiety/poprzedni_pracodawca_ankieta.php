<?php session_start(); ?>
<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
  <link href="style_form_box.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php
//<link href="../css/ankieta.css" rel="stylesheet" type="text/css"> 

class FormFields {
    
    const FIELD_COUNTRY       = 'country';
    const FIELD_CITY          = 'city';
    const FIELD_EMPLOYER_NAME = 'formerEmployer';
    const FIELD_DEPARTMENT    = 'department';
    const FIELD_POSITION      = 'position';
    const FIELD_PERIOD        = 'period';
    const FIELD_OCCUPATION    = 'occupation';
    
    const MAX_LENGTH_EMPLOYER_NAME = 30;
    const MAX_LENGTH_COUNTRY       = 30;
    const MAX_LENGTH_CITY          = 30;
    const MAX_LENGTH_DEPARTMENT    = 30;
    const MAX_LENGTH_POSITION      = 30;
    const MAX_LENGTH_PERIOD        = 30;
    
    const DATA_SEPARATOR = '/';
}
    function setHeadingRow($odlamki_nag)
    {
        //$odlamki_nag = explode(",","Klient, Data, Ilość kobiet, Ilość mężczyzn, Ilość tygodni");
        $licznik_nagl = 0;
        while (isset($odlamki_nag[$licznik_nagl]))
        {
            echo "<td nowrap bgcolor='#052B8C' align='CENTER'>".$odlamki_nag[$licznik_nagl]."</td>";
            //echo "";
            $licznik_nagl++;
        }
    }
    
        
        require("../naglowek.php");
	    require("../conf.php");
        include_once "../vaElClass.php";
        $controls = new valControl();
        include("../bll/definicjeKlas.php");
        //include("../prawa_strona/f_image_operations.php");
        
        $controls = new valControl();
        $poprPrac = new PoprzedniPracTab();
        $branza = new BranzaTab();
        $zawod = new ZawodTab();
        $popPrac = new PoprzedniPracodawca();
        $pracCollection = new PoprzedniPracodawcaCollection();
        $tableDef = $poprPrac->Def();
        $tableLen = $poprPrac->FieldLength();
        $tableShow = $poprPrac->ShowName();
        
        $branzaTabName = $branza->Name().".nazwa as branza";
        $zawodTabName = $zawod->Name().".nazwa as zawod";
        $branchName = 'branchName';
        $occName = 'occName';
        $divId = 'poprzedniPracodawca';
        
        if (isset($_POST['update']))
        {
            if (in_array('', array($_POST[FormFields::FIELD_COUNTRY], $_POST[FormFields::FIELD_PERIOD], $_POST[FormFields::FIELD_POSITION], $_POST[$tableDef[2]]))) {
                
                echo 'Nie uzupełniono wymaganych (czerwonych) pól.<br /><br />';
            } else {
            
                $pracCollection = unserialize($_SESSION['pracCollection']);

                $popPrac->EmpName = implode(FormFields::DATA_SEPARATOR, array($_POST[FormFields::FIELD_COUNTRY], $_POST[FormFields::FIELD_CITY], $_POST[FormFields::FIELD_EMPLOYER_NAME], $_POST[FormFields::FIELD_DEPARTMENT], $_POST[FormFields::FIELD_POSITION], $_POST[FormFields::FIELD_PERIOD]));
                $popPrac->OccId = $_POST[$tableDef[2]];
                $popPrac->OccName = $_POST[$occName];
                $pracCollection->UpdateFormerEmpByIndex($popPrac, $_POST['col_id']);
                $_SESSION['pracCollection'] = serialize($pracCollection);
            }
        }
        if (isset($_POST['erase']))
        {
            $pracCollection = unserialize($_SESSION['pracCollection']);
            $pracCollection->RemoveFormerEmpByIndex($_POST['col_id']);
            $_SESSION['pracCollection'] = serialize($pracCollection);
        }
        if (isset($_POST['potwierdz']))
        {
            if (isset($_SESSION['pracCollection']))
            {
                $pracCollection = unserialize($_SESSION['pracCollection']);
            }
            
            if (in_array('', array($_POST[FormFields::FIELD_COUNTRY], $_POST[FormFields::FIELD_PERIOD], $_POST[FormFields::FIELD_POSITION], $_POST[$tableDef[2]]))) {
                
                echo 'Nie uzupełniono wymaganych (czerwonych) pól.<br /><br />';
            } else {
            
                $popPrac->EmpName = implode(FormFields::DATA_SEPARATOR, array($_POST[FormFields::FIELD_COUNTRY], $_POST[FormFields::FIELD_CITY], $_POST[FormFields::FIELD_EMPLOYER_NAME], $_POST[FormFields::FIELD_DEPARTMENT], $_POST[FormFields::FIELD_POSITION], $_POST[FormFields::FIELD_PERIOD]));
                $popPrac->OccId = $_POST[$tableDef[2]];
                $popPrac->OccName = $_POST[$occName];
                $pracCollection->AddFormerEmp($popPrac);
                $_SESSION['pracCollection'] = serialize($pracCollection);
            }
        }
        
        if (isset($_POST['edit']))
        {
            $pracCollection = unserialize($_SESSION['pracCollection']);
            $collection = $pracCollection->GetCollection();
            $popPrac = $collection[$_POST['col_id']]; 
            echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">'; 
            echo $controls->AddHidden('id_col_id', 'col_id', '');
            echo '<table>';
            echo '<tr><td>'.$tableShow[0].':</td><td>';
            list ($country, $city, $firmName, $department, $position, $period) = explode(FormFields::DATA_SEPARATOR, $popPrac->EmpName);
            
            //echo $controls->AddTextbox($tableDef[0], $tableDef[0], $popPrac->EmpName, $tableLen[0], $tableLen[0], "");
            echo '<tr><td>Nazwa firmy:</td><td>'.$controls->AddTextbox(FormFields::FIELD_EMPLOYER_NAME, FormFields::FIELD_EMPLOYER_NAME, $firmName, FormFields::MAX_LENGTH_EMPLOYER_NAME, '30', '').'</td></tr>'.
            '<tr><td>Państwo:</td><td>'.$controls->AddAnkietaTextbox(FormFields::FIELD_COUNTRY, $country, FormFields::MAX_LENGTH_COUNTRY, '30', '', '', 'required').'</td></tr>'.
            '<tr><td>Miasto:</td><td>'.$controls->AddTextbox(FormFields::FIELD_CITY, FormFields::FIELD_CITY, $city, FormFields::MAX_LENGTH_CITY, '30', '').'</td></tr>'.
            '<tr><td>Branża:</td><td>'.$controls->AddTextbox(FormFields::FIELD_DEPARTMENT, FormFields::FIELD_DEPARTMENT, $department, FormFields::MAX_LENGTH_DEPARTMENT, '30', '').'</td></tr>'.
            '<tr><td>Zakres obowiązków:</td><td>'.$controls->AddAnkietaTextbox(FormFields::FIELD_POSITION, $position, FormFields::MAX_LENGTH_POSITION, '30', '', '', 'required').'</td></tr>'.
            '<tr><td>Okres zatrudnienia:</td><td>'.$controls->AddAnkietaTextbox(FormFields::FIELD_PERIOD, $period, FormFields::MAX_LENGTH_PERIOD, '30', '', '', 'required').'</td></tr>';
            
            echo '</td></tr><tr><td>'.$tableShow[1].':</td><td>';
            echo $controls->OccGroupControlAnkieta("Wybierz", "selectOcc", "occName", "occName", $popPrac->OccName, $tableDef[2], $tableDef[2], $popPrac->OccId, "wyborSlowPopUp.php?table=".$zawod->Name()."&txtId=occName&hidId=".$tableDef[2]."", "");
            echo '</td></tr><tr><td>'.$controls->AddSubmit("update", $_POST['col_id'], "Aktualizuj.", "onclick='col_id.value=this.id;'").'</td></tr>';
            echo '</table>';
            echo '</form>';
        }
        
        if (isset($_SESSION['pracCollection']))
        {
            $pracCollection = unserialize($_SESSION['pracCollection']);
            $collection = $pracCollection->GetCollection();
            if (count($collection) > 0)
            {
                echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">'; 
                echo '<table border="1"><tr style="color: #FFFFFF;font-weight: bold;">';
                $divContent = 'Poprzedni pracodawcy: <br />';
                require "buttons_nag.php";
                setHeadingRow($tableShow);
                echo '</tr>';
                echo $controls->AddHidden('id_col_id', 'col_id', '');                 
                
                $i = 0;
                for($i = 0; $i < count($collection); $i++)
                {
                    $popPrac = $collection[$i];
                    echo '<tr><td>';
                    echo $controls->AddSubmit("edit", $i, "Edycja.", "onclick='col_id.value=this.id;'");
                    echo '</td><td>';
                    echo $controls->AddSubmit("erase", $i, "Kasowanie.", "onclick='col_id.value=this.id;'");
                    echo '</td><td>';
                    echo $popPrac->EmpName;
                    echo '</td><td>';
                    echo $popPrac->OccName;
                    echo '</td></tr>';
                    $divContent .= $popPrac->EmpName.'<br />';
                }
                echo '</table>';
                echo '</form>';
            }
            else
            {
                $divContent = '';
            }
            echo '<script language="javascript">parent.document.getElementById("'.$divId.'").innerHTML = "'.$divContent.'";</script>'; 
            $_SESSION['pracCollection'] = serialize($pracCollection);
        }
        
        echo '<hr />';
        //if one of choices are being updated, there is no need to enable "add new form"
        if (!isset($_POST['edit']))
        {
            $country = '';
            $firmName = '';
            $city = '';
            $department = '';
            $position = '';
            $period = '';
            $occName = '';
            $occId = '';
            
            if (isset($_POST[FormFields::FIELD_COUNTRY], $_POST['potwierdz'])) {
                
                $country = $_POST[FormFields::FIELD_COUNTRY];
                $firmName = $_POST[FormFields::FIELD_EMPLOYER_NAME];
                $city = $_POST[FormFields::FIELD_CITY];
                $department = $_POST[FormFields::FIELD_DEPARTMENT];
                $position = $_POST[FormFields::FIELD_POSITION];
                $period = $_POST[FormFields::FIELD_PERIOD];
                $occName = $_POST['occName'];
                $occId = $_POST[$tableDef[2]];
            }
            
            echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';                                                                                                                
            echo '<table>';
            echo '<tr><td>Nazwa firmy:</td><td>'.$controls->AddTextBox(FormFields::FIELD_EMPLOYER_NAME, FormFields::FIELD_EMPLOYER_NAME, $firmName, FormFields::MAX_LENGTH_EMPLOYER_NAME, '30', '').'</td></tr>'.
            '<tr><td>Państwo:</td><td>'.$controls->AddAnkietaTextbox(FormFields::FIELD_COUNTRY, $country, FormFields::MAX_LENGTH_COUNTRY, '30', '', '', 'required').'</td></tr>'.
            '<tr><td>Miasto:</td><td>'.$controls->AddTextbox(FormFields::FIELD_CITY, FormFields::FIELD_CITY, $city, FormFields::MAX_LENGTH_CITY, '30', '').'</td></tr>'.
            '<tr><td>Branża:</td><td>'.$controls->AddTextbox(FormFields::FIELD_DEPARTMENT, FormFields::FIELD_DEPARTMENT, $department, FormFields::MAX_LENGTH_DEPARTMENT, '30', '').'</td></tr>'.
            '<tr><td>Zakres obowiązków:</td><td>'.$controls->AddAnkietaTextbox(FormFields::FIELD_POSITION, $position, FormFields::MAX_LENGTH_POSITION, '30', '', '', 'required').'</td></tr>'.
            '<tr><td>Okres zatrudnienia:</td><td>'.$controls->AddAnkietaTextbox(FormFields::FIELD_PERIOD, $period, FormFields::MAX_LENGTH_PERIOD, '30', '', '', 'required').'</td></tr>';
            //echo '<tr><td>'.$tableShow[0].':</td><td>';
            //echo $controls->AddTextbox($tableDef[0], $tableDef[0], "", $tableLen[0], $tableLen[0], "style='float: left; width: 285px;'");
            
            echo '</td></tr><tr><td>'.$tableShow[1].':</td><td>';
            echo $controls->OccGroupControlAnkieta("Wybierz", "selectOcc", "occName", "occName", $occName, $tableDef[2], $tableDef[2], $occId, "wyborSlowPopUp.php?table=".$zawod->Name()."&txtId=occName&hidId=".$tableDef[2]."", "");
            echo '</td></tr><tr><td>'.$controls->AddSubmit("potwierdz", "potwierdz", "Dodaj.", "").'</td></tr>';
            echo '</table></form>';
        }
        echo $controls->AddSubmit('Zamknij', 'id_Zamknij', 'Zamknij', JsEvents::ONCLICK.'="parent.document.getElementById(\'popup\').style.display = \'none\'; return false;"');
        require("../stopka.php");
?>
</body>
</html>
