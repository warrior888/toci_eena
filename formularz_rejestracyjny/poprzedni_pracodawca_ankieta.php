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
 
ini_set('display_errors', 0);
class FormFields {
    
    const FIELD_COUNTRY       = 'country';
    const FIELD_ID_COUNTRY    = 'id_country';
    const FIELD_COUNTRY_ID    = 'country_id';
    const FIELD_CITY          = 'city';
    const FIELD_EMPLOYER_NAME = 'formerEmployer';
    const FIELD_DEPARTMENT    = 'department';
    const FIELD_POSITION      = 'position';
    const FIELD_PERIOD        = 'period';
    const FIELD_AGENCY        = 'agency';
    const FIELD_OCCUPATION    = 'occupation';
    
    const MAX_LENGTH_EMPLOYER_NAME = 30;
    const MAX_LENGTH_COUNTRY       = 30;
    const MAX_LENGTH_CITY          = 30;
    const MAX_LENGTH_DEPARTMENT    = 30;
    const MAX_LENGTH_POSITION      = 80;
    const MAX_LENGTH_PERIOD        = 30;
    const MAX_LENGTH_AGENCY        = 50;
    
    const DATA_SEPARATOR = '/';
}
    function setHeadingRow($odlamki_nag)
    {
        //$odlamki_nag = explode(",","Klient, Data, Ilość kobiet, Ilość mężczyzn, Ilość tygodni");
        $licznik_nagl = 0;
        while (isset($odlamki_nag[$licznik_nagl]))
        {
            echo "<th align='CENTER'>".$odlamki_nag[$licznik_nagl]."</th>";
            //echo "";
            $licznik_nagl++;
        }
    }
    
        require("../naglowek.php");
	    require("../conf.php");
	    require_once 'ui/ControlsUI.php';
	    require_once 'bll/utilsbll.php';
        include_once "../vaElClass.php";
        $controls = new valControl();
        
        include("../bll/definicjeKlas.php");
        define('EMP_COLLECTION', 'pracCollection');
        
        $controls = new valControl();
        $poprPrac = new PoprzedniPracTab();
        $branza = new BranzaTab();
        $zawod = new ZawodTab();
        $popPrac = new PoprzedniPracodawca();
        $pracCollection = isset($_SESSION[EMP_COLLECTION]) ? unserialize($_SESSION[EMP_COLLECTION]) : new PoprzedniPracodawcaCollection();
        $tableDef = $poprPrac->Def();
        $tableLen = $poprPrac->FieldLength();
        $tableShow = $poprPrac->ShowName();
        
        $branzaTabName = $branza->Name().".nazwa as branza";
        $zawodTabName = $zawod->Name().".nazwa as zawod";
        $branchName = 'branchName';
        $occName = 'occName';
        // $aggName = 'aggName';
        $divId = 'poprzedniPracodawca';
                
        if (isset($_POST['update']))
        {
            if (in_array('', array($_POST[FormFields::FIELD_COUNTRY], $_POST[FormFields::FIELD_PERIOD], $_POST[FormFields::FIELD_POSITION], $_POST[$tableDef[2]]))) {
                
                echo 'Nie uzupełniono wymaganych (czerwonych) pól.<br /><br />';
            } else {
                $popPrac->EmpName = implode(FormFields::DATA_SEPARATOR, array($_POST[FormFields::FIELD_COUNTRY], $_POST[FormFields::FIELD_CITY], $_POST[FormFields::FIELD_EMPLOYER_NAME], $_POST[FormFields::FIELD_DEPARTMENT], $_POST[FormFields::FIELD_POSITION], $_POST[FormFields::FIELD_PERIOD]));
                $popPrac->OccId = $_POST[$tableDef[2]];
                $popPrac->OccName = $_POST[$occName];
                $popPrac->AgencyName = $_POST[FormFields::FIELD_AGENCY];
                $pracCollection->UpdateFormerEmpByIndex($popPrac, $_POST['col_id']);
            }
        }
        if (isset($_POST['erase']))
        {
            $pracCollection->RemoveFormerEmpByIndex($_POST['col_id']);
        }
        if (isset($_POST['nie_mam_dosw_zawod']))
        {
            $pracCollection->hasExperience(false);
            echo '<script>parent.document.getElementById(\'popup\').style.display = \'none\';</script>';
        }
        
        $isErrorRepeat = null;
        if (isset($_POST['potwierdz']))
        {            
            if (in_array('', array($_POST[FormFields::FIELD_COUNTRY], $_POST[FormFields::FIELD_PERIOD], $_POST[FormFields::FIELD_POSITION], $_POST[$tableDef[2]]))) {
                
                echo 'Nie uzupełniono wymaganych (czerwonych) pól.<br /><br />';
                $isErrorRepeat = true;
            } else {
            
                $isErrorRepeat = false;
                $popPrac->EmpName = implode(FormFields::DATA_SEPARATOR, array($_POST[FormFields::FIELD_COUNTRY], $_POST[FormFields::FIELD_CITY], $_POST[FormFields::FIELD_EMPLOYER_NAME], $_POST[FormFields::FIELD_DEPARTMENT], $_POST[FormFields::FIELD_POSITION], $_POST[FormFields::FIELD_PERIOD]));
                $popPrac->OccId = $_POST[$tableDef[2]];
                $popPrac->OccName = $_POST[$occName];
                $popPrac->AgencyName = $_POST[FormFields::FIELD_AGENCY];
                $pracCollection->AddFormerEmp($popPrac);
            }
        }
        
        if (isset($_POST['edit']))
        {
            $bllDaneSlownikowe = new BLLDaneSlownikowe();
            $countriesList = $bllDaneSlownikowe->getCountriesList();
            $collection = $pracCollection->GetCollection();
            $popPrac = $collection[$_POST['col_id']]; 
            echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">'; 
            echo $controls->AddHidden('id_col_id', 'col_id', '');
            echo '<table>';
            echo '<tr><td>'.$tableShow[0].':</td><td>';
            list ($country, $city, $firmName, $department, $position, $period) = explode(FormFields::DATA_SEPARATOR, $popPrac->EmpName);
            $agencyName = $popPrac->AgencyName;
            
            $countryId = UtilsBLL::GetIdForDictName($countriesList[Model::RESULT_FIELD_DATA], $country);
            
            //echo $controls->AddTextbox($tableDef[0], $tableDef[0], $popPrac->EmpName, $tableLen[0], $tableLen[0], "");
            echo '<tr><td>Nazwa firmy:</td><td>'.$controls->AddTextbox(FormFields::FIELD_EMPLOYER_NAME, FormFields::FIELD_EMPLOYER_NAME, $firmName, FormFields::MAX_LENGTH_EMPLOYER_NAME, '30', '', 'rightFloat').'</td></tr>'.
            '<tr><td>Państwo:</td><td>'.$controls->_AddSelectWithData(FormFields::FIELD_COUNTRY, FormFields::FIELD_ID_COUNTRY, '', '', $countriesList[Model::RESULT_FIELD_DATA], $countryId, FormFields::FIELD_COUNTRY_ID, null, 'rightFloat').'</td></tr>'.
            '<tr><td>Miasto:</td><td>'.$controls->AddTextbox(FormFields::FIELD_CITY, FormFields::FIELD_CITY, $city, FormFields::MAX_LENGTH_CITY, '30', '', 'rightFloat').'</td></tr>'.
            '<tr><td>Branża:</td><td>'.$controls->AddTextbox(FormFields::FIELD_DEPARTMENT, FormFields::FIELD_DEPARTMENT, $department, FormFields::MAX_LENGTH_DEPARTMENT, '30', '', 'rightFloat').'</td></tr>'.
            '<tr><td>Zakres obowiązków:</td><td>'.$controls->AddAnkietaTextbox(FormFields::FIELD_POSITION, $position, FormFields::MAX_LENGTH_POSITION, '30', '', '', 'required rightFloat').'</td></tr>'.
            '<tr><td>Okres zatrudnienia:</td><td>'.$controls->AddTextbox(FormFields::FIELD_PERIOD, FormFields::FIELD_PERIOD, $period, FormFields::MAX_LENGTH_PERIOD, '30', '', 'required rightFloat').'</td></tr>'.
            '<tr><td>Agencja:</td><td>'.$controls->AddTextbox(FormFields::FIELD_AGENCY, FormFields::FIELD_AGENCY, $agencyName, FormFields::MAX_LENGTH_AGENCY, '30', '', 'rightFloat').'</td></tr>';
            
            echo '</td></tr><tr><td>'.$tableShow[1].':</td><td>';
            echo valControl::_PopupChoiceControl("occName", "occName", $popPrac->OccName, $tableDef[2], $tableDef[2], $popPrac->OccId, '', 'rightFloat', "wyborSlowPopUp.php?table=1&txtId=occName&hidId=".$tableDef[2], 
            'zawod_kandydata', '', 5, '');
            
            echo '</td></tr><tr><td>'.$controls->AddSubmit("update", $_POST['col_id'], "Aktualizuj.", "onclick='col_id.value=this.id;'").'</td></tr>';
            echo '</table>';
            echo '</form>';
        }
        
        if (isset($_SESSION['pracCollection']))
        {
            $collection = $pracCollection->GetCollection();
            if (count($collection) > 0)
            {
                echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">'; 
                echo '<table class="gridTable" border="0" cellspacing="0"><tr>';
                echo '<th>Edycja</th><th>Kasowanie</th>';
                setHeadingRow($tableShow);
                echo '</tr>';
                echo $controls->AddHidden('id_col_id', 'col_id', '');                 
                
                $i = 0;
                for($i = 0; $i < count($collection); $i++)
                {
                    $css = (($i % 2) == 0) ? 'oddRow' : 'evenRow';
                    $popPrac = $collection[$i];
                    echo '<tr class="'.$css.'"><td>';
                    echo $controls->AddSubmit("edit", $i, "Edycja.", "onclick='col_id.value=this.id;'");
                    echo '</td><td>';
                    echo $controls->AddSubmit("erase", $i, "Kasowanie.", "onclick='col_id.value=this.id;'");
                    echo '</td><td>';
                    echo $popPrac->EmpName;
                    echo '</td><td>';
                    echo $popPrac->OccName;
                    echo '</td><td>';
                    echo $popPrac->AgencyName;
                    echo '</td></tr>';
                }
                echo '</table>';
                echo '</form>';
            }

            echo '<script language="javascript">parent.document.getElementById("'.$divId.'").innerHTML = "'.$pracCollection->renderInfo().'";</script>'; 
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
            $agencyName = '';
            $occName = '';
            $occId = '';
            
            if (isset($_POST[FormFields::FIELD_COUNTRY], $_POST['potwierdz']) && true === $isErrorRepeat) {
                
                $country = htmlspecialchars($_POST[FormFields::FIELD_COUNTRY]);
                $firmName = htmlspecialchars($_POST[FormFields::FIELD_EMPLOYER_NAME]);
                $city = htmlspecialchars($_POST[FormFields::FIELD_CITY]);
                $department = htmlspecialchars($_POST[FormFields::FIELD_DEPARTMENT]);
                $position = htmlspecialchars($_POST[FormFields::FIELD_POSITION]);
                $period = htmlspecialchars($_POST[FormFields::FIELD_PERIOD]);
                $agencyName = htmlspecialchars($_POST[FormFields::FIELD_AGENCY]);
                $occName = htmlspecialchars($_POST['occName']);
                $occId = htmlspecialchars($_POST[$tableDef[2]]);
            }
            
            $bllDaneSlownikowe = new BLLDaneSlownikowe();
            $countriesList = $bllDaneSlownikowe->getCountriesList();
            
            $countryId = UtilsBLL::GetIdForDictName($countriesList[Model::RESULT_FIELD_DATA], $country);
            
            echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';                                                                    
            echo $controls->AddSubmit('nie_mam_dosw_zawod', 'id_nie_mam_dosw_zawod', 'Jeszcze nie pracowałem/am', '');
            echo '<table class="empFormWideTable">';
            echo '<tr><td>Nazwa firmy:</td><td>'.$controls->AddTextBox(FormFields::FIELD_EMPLOYER_NAME, FormFields::FIELD_EMPLOYER_NAME, $firmName, FormFields::MAX_LENGTH_EMPLOYER_NAME, '30', '', 'rightFloat').'</td></tr>'.
            '<tr><td>Państwo:</td><td>'.$controls->_AddSelectWithData(FormFields::FIELD_COUNTRY, FormFields::FIELD_ID_COUNTRY, '', '', $countriesList[Model::RESULT_FIELD_DATA], $countryId, FormFields::FIELD_COUNTRY_ID, null, 'rightFloat').'</td></tr>'.
            '<tr><td>Miasto:</td><td>'.$controls->AddTextbox(FormFields::FIELD_CITY, FormFields::FIELD_CITY, $city, FormFields::MAX_LENGTH_CITY, '30', '', 'rightFloat').'</td></tr>'.
            '<tr><td>Branża:</td><td>'.$controls->AddTextbox(FormFields::FIELD_DEPARTMENT, FormFields::FIELD_DEPARTMENT, $department, FormFields::MAX_LENGTH_DEPARTMENT, '30', '', 'rightFloat').'</td></tr>'.
            '<tr><td>Zakres obowiązków:</td><td>'.$controls->AddAnkietaTextbox(FormFields::FIELD_POSITION, $position, FormFields::MAX_LENGTH_POSITION, '30', '', '', 'required rightFloat').'</td></tr>'.
            '<tr><td>Okres zatrudnienia:</td><td>'.$controls->AddTextbox(FormFields::FIELD_PERIOD, FormFields::FIELD_PERIOD, $period, FormFields::MAX_LENGTH_PERIOD, '30', '', 'required rightFloat').'</td></tr>'.
            '<tr><td>Agencja:</td><td>'.$controls->AddTextbox(FormFields::FIELD_AGENCY, FormFields::FIELD_AGENCY, $agencyName, FormFields::MAX_LENGTH_AGENCY, '30', '', 'rightFloat').'</td></tr>';
            //echo '<tr><td>'.$tableShow[0].':</td><td>';
            //echo $controls->AddTextbox($tableDef[0], $tableDef[0], "", $tableLen[0], $tableLen[0], "style='float: left; width: 285px;'");
            
            echo '</td></tr><tr><td>'.$tableShow[1].':</td><td>';
            echo valControl::_PopupChoiceControl("occName", "occName", $occName, $tableDef[2], $tableDef[2], $occId, '', 'rightFloat', "wyborSlowPopUp.php?table=1&txtId=occName&hidId=".$tableDef[2], 
            'zawod_kandydata', '', 5, '');
            
            echo '</td></tr><tr><td>'.$controls->AddSubmit("potwierdz", "potwierdz", "Dodaj.", "").'</td></tr>';
            echo '</table></form>';
        }
        echo $controls->AddSubmit('Zamknij', 'id_Zamknij', 'Zamknij', JsEvents::ONCLICK.'="parent.document.getElementById(\'popup\').style.display = \'none\'; return false;"');
        $_SESSION[EMP_COLLECTION] = serialize($pracCollection);
        require("../stopka.php");
?>
</body>
</html>
