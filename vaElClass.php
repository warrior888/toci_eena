<?php

    if (!isset($path))
        require_once 'conf.php';

    include_once($path."dal.php");
  class valControl 
  {
      #region static
      public static function _RowsCount ($count, $trEmbedded = true)
      {
          if (!$trEmbedded)
              return '<div class="roseBkgnd" align="CENTER">Ilosc rekordow znalezionych: '.$count.'</div>';
              
          return '<tr><td colspan="100"><div class="roseBkgnd trEmb" align="CENTER">Ilosc rekordow znalezionych: '.$count.'</div></td></tr>';
      }
      
      public static function _AddInputWithLabel ($name, $id, $value, $label, $maxlength, $size, $css, $tabindex, $validation) 
      {
          $input = '
          <label class="inputLabel" for="'.$id.'">'.$label.'</label>
          <input type="text" name="'.$name.'" id="'.$id.'" value="'.$value.'" maxlength="'.$maxlength.'" size="'.$size.'" class="formfield '.$css.'" tabindex="'.$tabindex.'" '.$validation.'/>';
          
          return $input;
      }
      
      public static function _AddSelectWithData ($name, $id, $label, $specific, $data, $selectedId, $hiddenId, $tabindex, $css = '', $divSpecific = '', $disabled = false)
      {   
          $divClass = '';
          if($disabled) {
              $specific .= ' disabled';
              $divClass = 'disabled';
          }
          $divId = md5($id); 
          $str = "<label class='inputLabel' for='".$id."'>".$label."</label><span class='selectContainer ".$css."'>
          <select class='".$css."' name=".$name." id=".$id." tabindex='".$tabindex."' onkeyup=\"utils.selectAutoFill(this, event, '".$divId."');\" onChange=\"document.getElementById('".$divId."').innerHTML = this.options[this.selectedIndex].value;\" onBlur=\"utils.setHiddenFromSelect(this, '".$hiddenId."');\" class=\"formfield\" ".$specific.">";
          
          $str .= self::fillselectById($data, $selectedId, 'nazwa', 'id', 'nazwa', $markedValue);
          $str .= "</select><div id='".$divId."' class='selectDiv $divClass' ".$divSpecific.">".$markedValue."</div></span>
          <input type='hidden' id='".$hiddenId."' name='".$hiddenId."' value='".$selectedId."'>";
          return $str; 
      }
      
      public static function _PopupChoiceControl($TxtName, $TxtID, $TxtValue, $HidName, $HidID, $HidValue, $label, $css, $ScrUrl, $WindowName, $validation, $tabindex, $hint = '')
      {
          $str = "<label class='inputLabel' for='".$TxtID."'>".$label."</label><input type='hidden' name='".$HidName."' id='".$HidID."' value='".$HidValue."'>
          <input type='text' name='".$TxtName."' id='".$TxtID."' value='".$TxtValue."' size='30' class='formfield required ".$css."' title='".$hint."' readonly='readonly'>
          <input type=\"button\" class='formreset ".$css."' value='Wybierz' name='wybierz' class='popbutton' tabindex='".$tabindex."'
          onClick = \"window.open('".$ScrUrl."', '".$WindowName."','toolbar=no, scrollbars=yes, width=750,height=650')\">
          ";
          
          return $str;
      }
      
      public static function _AddSubmit($name, $id, $value, $css, $validation, $type = 'submit') 
      {
          $str = '<input type="'.$type.'" name="'.$name.'" id="'.$id.'" value="'.$value.'" class="formreset '.$css.'" '.$validation.' />';
          return $str;
      }
      #endregion
      
      public $dalObj;
      
      public function __construct()
      {
          $this->dalObj = dal::getInstance();
      }
      
      function AddCLTextbox($name, $value, $length, $size, $css = '')  //Capital letter textbox
      {                                                                    
          $str = "<input type='text' class='formfield ".$css."' name='".$name."' id='".$name."' value='".$value."' onkeypress = 'return validations.TextValidate(this, event);' onBlur = 'this.value = utils.trim(this.value); sprawdz_nazwisko(this)' maxlength='".$length."' size='".$size."'>";
          return $str;
      }
      function AddAnkietaTextbox ($name, $value, $length, $size, $onblur, $specific, $css) 
      {
          $str = "<input type='text' name='".$name."' id='".$name."' value='".$value."' onkeypress = 'return validations.TextValidate(this, event);' onblur = 'this.value = utils.trim(this.value); ".$onblur."' class='formfield ".$css."' 
          maxlength='".$length."' size='".$size."' ".$specific.">";
          return $str;
      }
      function AddAnkietaTextInput ($name, $value, $length, $size, $onblur, $specific, $css) 
      {
          $str = "<input type='text' name='".$name."' id='".$name."' value='".$value."' onblur = 'this.value = utils.trim(this.value); ".$onblur."' class='".$css."' 
          maxlength='".$length."' size='".$size."' ".$specific.">";
          return $str;
      }
      function AddAnkietaEmailbox ($name, $value, $length, $size, $onblur, $specific, $css) 
      {
          $str = "<input type='text' name='".$name."' id='".$name."' value='".$value."'  onblur = 'EmailValidate(this); this.value = utils.trim(this.value); ".$onblur."' class='".$css."' 
          maxlength='".$length."' size='".$size."' ".$specific.">";
          return $str;
      }
      function AddTextbox($name, $id, $value, $length, $size, $validation, $css = '')  //Capital letter textbox
      {                                                                    
          $str = "<input type='text' name='".$name."' id='".$id."' value='".$value."' class='formfield ".$css."' maxlength='".$length."' size='".$size."' 
          onblur='this.value = utils.trim(this.value);' ".$validation.">";
          return $str;
      }
      function AddPassbox($name, $id, $value, $length, $size, $validation)  //Capital letter textbox
      {                                                                    
          $str = "<input type='password' name='".$name."' id='".$id."' value='".$value."' class='formfield' maxlength='".$length."' size='".$size."' onblur='this.value = utils.trim(this.value);' ".$validation.">";
          return $str;
      }
      //DateTomorrow(this, this.value);
      function AddDatebox($name, $id, $value, $length, $size, $css = '')  //date designed textbox
      {                                                                    
          $str = "<input type='text' name='".$name."' id='".$id."' value='".$value."' onkeypress = 'return validations.DateValidate(this, event);' onblur = 'CheckLength(this);' 
          onkeyup = 'DateKeyUp(this)'  maxlength='".$length."' size='".$size."' class='formfield ".$css."'>";
          return $str;
      }
      function AddDateRangebox($name, $id, $value, $length, $size)  //date designed textbox
      {                                                                    
          $str = "<input type='text' name='".$name."' id='".$id."' value='".$value."' onkeypress = 'return validations.DateValidate(this, event);' onkeyup='DoubleDateKeyUp(this);' onblur='this.value = utils.trim(this.value);' maxlength='".$length."' size='".$size."' class='formfield'>";
          return $str;
      }
      function AddDateboxFuture($name, $id, $value, $length, $size, $specific = '', $css = '')  //date designed textbox
      {                                                                    
          $str = "<input type='text' name='".$name."' id='".$id."' value='".$value."' onkeypress = 'return validations.DateValidate(this, event);' 
          onblur = 'CheckLength(this); DateTomorrow(this, this.value);' onkeyup = 'DateKeyUp(this)'  maxlength='".$length."' size='".$size."' class='formfield ".$css."' ".$specific.">";
          return $str;
      }
      function AddAnkietaDatebox($name, $id, $value, $length, $size, $validation, $css)  //date designed textbox
      {                                                                    
          $str = '<input type="text" name="'.$name.'" id="'.$id.'" value="'.$value.'" onkeypress="return validations.DateValidate(this, event);" onkeyup="DateKeyUp(this);" maxlength="'.$length.'" size="'.$size.'" class="'.$css.'" '.$validation.'>';
          //onmouseout="showHint(\'\');" onmouseover="showHint(\'<i>Data w formacie<br> RRRR-MM-DD (rok-miesi±c-dzieñ).</i>\');"
          return $str;
      }
      function AddSeekTextbox($name, $value, $id, $length, $size)  //textbox
      {                                                                    
          $str = "<input type='text' name='".$name."' id='".$id."' value='".$value."' onkeypress = 'return validations.TextValidate(this, event);' onblur='this.value = utils.trim(this.value); zamien(this);' maxlength='".$length."' size='".$size."' class='formfield'>";
          return $str;
      }
      function AddPostCodebox($name, $value, $length, $size, $css = '')  //textbox
      {                                                                    
          $str = "<input type='text' name='".$name."' id='".$name."' value='".$value."' onkeypress = 'return validations.DateValidate(this, event);' onBlur='sprawdz_kod(this)' 
          maxlength='".$length."' size='".$size."' class='formfield ".$css."'>";
          return $str;
      }
      function AddAnkietaPostCodebox($name, $value, $length, $size, $validation, $css)  //textbox
      {                                                                    
          $str = "<input type='text' name='".$name."' id='".$name."' value='".$value."' onkeypress = 'return validations.DateValidate(this, event);' onBlur='sprawdz_kod(this);' title='Kod pocztowy w formacie xx-xxx.' maxlength='".$length."' size='".$size."' class='".$css."' ".$validation.">";
          return $str;
      }
      function AddNumberbox($name, $id, $value, $length, $size, $onblurvalidation, $css = '')  //nuber designed textbox
      {                                                                    
          $str = "<input type='text' name='".$name."' id='".$id."' value='".$value."' onkeypress = 'return validations.OnlyNumber(this, event);' onBlur='".$onblurvalidation."' 
          maxlength='".$length."' size='".$size."' class='formfield ".$css."'>";
          return $str;
      }
      function AddAnkietaNumberbox($name, $id, $value, $length, $size, $onblurvalidation, $validation, $css, $hint)  //nuber designed textbox
      {                                                                    
          $str = "<input type='text' name='".$name."' id='".$id."' value='".$value."' onkeypress = 'return validations.OnlyNumber(this, event);' onBlur='".$onblurvalidation."' maxlength='".$length."' 
          size='".$size."' class='".$css."' title='".$hint."' ".$validation.">";
          return $str;
      }
      function AddTelSNumberbox($name, $value, $length, $onchangevalidation, $onblurvalidation)  //number designed textbox
      {                                                                    
          $str = "<input type='text' name='".$name."' id='".$name."' value='".$value."' onkeypress = 'return validations.OnlyNumber(this, event);' onChange='".$onchangevalidation."' onBlur='".$onblurvalidation."' maxlength='".$length."' class='formfield'>";
          return $str;
      }
      function _AddCheckbox($name, $id, $checked, $specific, $komentarz='', $value='')  
      {
          if(is_bool($checked))
          {
              if($checked)
                  $checked = 'checked="checked"';
              else
                  $checked = '';
          }
          $str = '<input type="checkbox" name="'.$name.'" id="'.$id.'" value="'.$value.'" onmouseup="blur();" class="formfield" '.$checked.' '.$specific.' /><label for="'.$id.'"><i>'.$komentarz.'</i></label>';
          return $str;
      }
      function AddCheckbox($name, $id, $checked, $specific, $komentarz='', $value='')  
      {          
          echo self::_AddCheckbox($name, $id, $checked, $specific, $komentarz, $value);
      }
      function AddPopUpButton($value, $name, $openSite, $width, $height, $css = '')
      {
          $str = "<input type=\"button\" value=\"".$value."\" name=\"".$name."\"
          class=\"formreset ".$css."\" onClick = \"window.open('".$openSite."', '".$name."','toolbar=no, scrollbars=yes, width=".$width.",height=".$height."')\"/>";
          return $str;  
      }
      function AddSubmit($name, $id, $value, $specific, $css = '')
      {
          $str = '<input type="submit" name="'.$name.'" id="'.$id.'" value="'.$value.'" class="formreset '.$css.'" '.$specific.' />';
          return $str;
      }
      function AddDeleteSubmit($name, $id, $value, $onclick = '', $specific = '')
      {
          $str = '<input type="submit" name="'.$name.'" id="'.$id.'" value="'.$value.'" class="formreset" '.JsEvents::ONCLICK.'="'.$onclick.' return confirm(\'Operacja jest nieodwracalna, czy jeste¶ pewien ?\');" '.$specific.' />';
          return $str;
      }
      function AddTableSubmit($name, $id, $value, $specific)
      {
          $str = "<input type='submit' name='".$name."' id='".$id."' value='".$value."' class='formreset' ".$specific."/>";
          return $str;
      }
      function AddSubmitStiffWidth($name, $id, $value, $specific, $css = '')
      {
          $str = "<input type='submit' name='".$name."' id='".$id."' value='".$value."' class=\"leftSideButtons ".$css."\" ".$specific."/>";
          return $str;
      }
      function AddHidden($id, $name, $value = '')
      {
          $str = '<input type="hidden" name="'.$name.'" id="'.$id.'" value="'.$value.'">';
          return $str;
      }
      function AddHiddenTableConfig($idTab, $nameTab)
      {
          echo '<input type="hidden" name="id" value="'.$idTab.'"><input type="hidden" name="name" value="'.$nameTab.'">';
      }
      function AddHiddenCtrlConfig($nameTab, $nameHid, $nameTxt, $valTab, $valHid, $valTxt)
      {
          echo '<input type="hidden" name="'.$nameTab.'" value="'.$valTab.'">
          <input type="hidden" name="'.$nameHid.'" value="'.$valHid.'">
          <input type="hidden" name="'.$nameTxt.'" value="'.$valTxt.'">';
      }
      function OccGroupControl ($ButValue, $ButName, $TxtName, $TxtID, $TxtValue, $HidName, $HidID, $HidValue, $ScrUrl, $WindowName, $css = '')
      {
          $str = "<input type='text' name='".$TxtName."' id='".$TxtID."' value='".$TxtValue."' size='30' readonly='readonly' class='formfield ".$css."'>
          <input type='hidden' name='".$HidName."' id='".$HidID."' value='".$HidValue."'>
          <input type=\"button\" value='".$ButValue."' name='".$ButName."' class=\"formreset\" onClick = \"window.open('".$ScrUrl."', '".$WindowName."', 
          'toolbar=no, scrollbars=yes, width=750,height=650')\">";
          return $str;
      }
      function OccGroupControlAnkieta ($ButValue, $ButName, $TxtName, $TxtID, $TxtValue, $HidName, $HidID, $HidValue, $ScrUrl, $WindowName, $hint='', $tabindex=0)
      {
          $str = "<input type='hidden' name='".$HidName."' id='".$HidID."' value='".$HidValue."'>
	      <input type=\"button\" value='".$ButValue."' name='".$ButName."' onblur = 'checkName(); checkAll();' style='width: auto;margin-bottom: 5px;' class='popbutton' style='width: auto;' tabindex='".$tabindex."'
	      onClick = \"window.open('".$ScrUrl."', '".$WindowName."','toolbar=no, scrollbars=yes, width=750,height=650')\">
          <input type='text' name='".$TxtName."' id='".$TxtID."' value='".$TxtValue."' size='30' class='required' onchange = 'checkName(); checkAll();' title='".$hint."' READONLY>";
          return $str;
      }
      function AddSelectHelpHidden()
      {
          //uzyc z tej klasy add hidden
          $str = '<input type="hidden" id="ukryty_js"><input type="hidden" id="id_temp_sel_helper" /><input type="hidden" id="id_sel_autofill" />';
          return $str;
      }
      function _AddSelectData ($name, $id, $specific, $data, $selectedId, $hiddenId, $css = '')
      {
          $str = "<span class='selectContainer'><select class='".$css."' name='".$name."' id='".$id."' onkeyup=\"utils.selectAutoFill(this, event);\" onBlur = \"utils.setHiddenFromSelect(this, '".$hiddenId."');\" class=\"formfield\" ".$specific.">";

          $str .= self::fillselectById($data, $selectedId, 'nazwa', 'id');
          $str .= "</select></span><input type='hidden' id='".$hiddenId."' name='".$hiddenId."' value='".$selectedId."'>";
          
          return $str; 
      }
      
      ///untested, yet unused, added in case ever needed
      function _AddSelectDataByValue ($name, $id, $specific, $data, $selectedValue, $hiddenId, $css = '')
      {
          $str = "<span class='selectContainer'><select class='".$css."' name='".$name."' id='".$id."' onkeyup=\"utils.selectAutoFill(this, event);\" onBlur = \"utils.setHiddenFromSelect(this, '".$hiddenId."');\" class=\"formfield\" ".$specific.">";

          $str .= self::fillselect($data, $selectedValue, 'nazwa', 'id', $selectedId);
          $str .= "</select></span><input type='hidden' id='".$hiddenId."' name='".$hiddenId."' value='".$selectedId."'>";
          return $str; 
      }
      
      function AddSelect ($name, $id, $specific, $query, $selectedValue, $hiddenId, $css = '')
      {   
          $str = "<span class='selectContainer'><select class='".$css."' name=".$name." id=".$id." onkeyup=\"utils.selectAutoFill(this, event);\" onBlur=\"utils.setHiddenFromSelect(this, '".$hiddenId."');\" class=\"formfield\" ".$specific.">";
          $result = $this->dalObj->dbQuery($query, true);
          $str .= $this->fillselect($result, $selectedValue, 'nazwa', 'id', $resId);
          $str .= "</select></span><input type='hidden' id='".$hiddenId."' name='".$hiddenId."' value='".$resId."'>";
          return $str; 
      }
      
      public function AddSelectWithData ($name, $id, $specific, $data, $selectedId, $hiddenId, $css = '')
      {   
          $str = "<span class='selectContainer'><select class='".$css."' name=".$name." id=".$id." onkeyup=\"utils.selectAutoFill(this, event);\" onBlur=\"utils.setHiddenFromSelect(this, '".$hiddenId."');\" class=\"formfield\" ".$specific.">";
          
          $str .= $this->fillselectById($data, $selectedId, 'nazwa', 'id');
          $str .= "</select></span><input type='hidden' id='".$hiddenId."' name='".$hiddenId."' value='".$selectedId."'>";
          return $str; 
      }
      /**
      * @desc Add combo filled with random query results.
      */
      public function AddSelectRandomQuery ($name, $id, $specific, $query, $selectedValue, $hiddenId, $itemName = 'nazwa', $itemId = 'id', $onblur = '', $label = null, $labelHiddenId = '', $onchange = '', $css = '')
      {
          $str = "<span class='selectContainer'><select class='".$css."' name=".$name." id=".$id." onkeyup=\"utils.selectAutoFill(this, event); utils.setHiddenFromSelect(this, '".$hiddenId."');\" onchange=\"".$onchange." utils.setHiddenFromSelect(this, '".$hiddenId."'); \" onblur=\" utils.setHiddenFromSelect(this, '".$hiddenId."'); utils.clearAutoFill(); ".$onblur."\" class=\"formfield\" ".$specific.">";
                                           //value='".$hiddenValue."' - consider to put default value of hidden in here
 
          $result = $this->dalObj->PobierzDane($query);
          $str .= $this->fillselect($result, $selectedValue, $itemName, $itemId, $resId, $label, $markedLabel);
          $str .= "</select></span><input type='hidden' id='".$hiddenId."' name='".$hiddenId."' value='".$resId."' />";
          if ($labelHiddenId)
            $str .= "<input type='hidden' id='".$labelHiddenId."' name='".$labelHiddenId."' value='".$markedLabel."' />";
            
          return $str; 
      }
      function AddSelectRandomQuerySVbyId ($name, $id, $specific, $query, $selectedId, $hiddenId, $itemName = 'nazwa', $itemId= 'id', $onblur= '', $label = null, $labelHiddenId = '', $onchange = '', $css = '')
      {
          $str = "<span class='selectContainer'><select class='".$css."' name=".$name." id=".$id." onkeyup=\"utils.selectAutoFill(this, event);\" onchange='".$onchange." blur();' onblur=\"utils.setHiddenFromSelect(this, '".$hiddenId."');".$onblur."\" class=\"formfield\" ".$specific.">";

          $result = $this->dalObj->PobierzDane($query);
          $str .= $this->fillselectById($result, $selectedId, $itemName, $itemId, $label, $markedLabel);
          $str .= "</select></span><input type='hidden' id='".$hiddenId."' name='".$hiddenId."' value='".$selectedId."'>";
          if ($labelHiddenId)
            $str .= "<input type='hidden' id='".$labelHiddenId."' name='".$labelHiddenId."' value='".$markedLabel."' />";
            
          return $str; 
      }
      //function generates option tags for select html control with definitions of id, name and selected option
      //in this specific case we may not know what the selected value is going to be, thus we have to make code tell
      //us which option by default is selected in the combo we have
      function fillselect ($result, $selectedValue, $name, $id, &$IdForHidden, $labelName = null, &$markedLabel = null)
      {
          //name can also be defined in option, it is visible in js
          //inner html for option shows it's inside content, value does't have to be defined
          $str = "";
          $firstId = null;
          if ($result)
          foreach ($result as $row)
          {
                if ($str == "")//if no default value is expected, the first is default
                {          
                    $firstId = $row[$id]; //remember the first id
                    $markedLabel = $labelName ? $row[$labelName] : null;
                }
                if($row[$name] == $selectedValue)                //if the selected value is known and expected
                {
                    $IdForHidden = $row[$id];
                    $markedLabel = $labelName ? $row[$labelName] : null;
                    $selected = 'selected';
                }
                else
                {
                    $selected = '';
                }
                $label = '';
                if ($labelName)
                {
                    if (isset($row[$labelName]))
                    {
                        $label = "title='$row[$labelName]'";
                    }
                }
                $str .= "<option id='".$row[$id]."' value='".$row[$name]."' $selected $label>".$row[$name]."</option>";

          }
          //if we haven't chosen any id it means no selected value was expected
          if (!isset($IdForHidden))
          {
              $IdForHidden = $firstId; //first option is then recognised as selected
          }
          return $str;
      }
      //here the id we want to have selected is known
      //it may happen the id is not present in combo
      //in such a case the id is going to be from the database until there is a blur on select control
      function fillselectById ($result, &$selectedId, $name, $id, $labelName = null, &$markedLabel = null)
      {
          //name can also be defined in option, it is visible in js
          //inner html for option shows it's inside content, value does't have to be defined
          $str = "";
          if ($selectedId === null || $selectedId === '')
          {
              $row = array_shift($result);
              $str .= "<option id='".$row[$id]."' value='".$row[$name]."' selected>".$row[$name];
              $selectedId = $row[$id];
              $markedLabel = $labelName ? $row[$labelName] : null;
          }

          foreach ($result as $row)
          {
                $label = '';
                if ($labelName)
                {
                    if (isset($row[$labelName]))
                    {
                        $label = "title='$row[$labelName]'";
                    }
                }
                if($row[$id] == $selectedId)
                {
                    $str .= "<option id='".$row[$id]."' value='".$row[$name]."' $label selected>".$row[$name];
                    $markedLabel = $labelName ? $row[$labelName] : null;
                }
                else
                {
                    $str .= "<option id='".$row[$id]."' value='".$row[$name]."' $label>".$row[$name];
                }
          }
          return $str;
      }
  }
  
  class JsEvents 
  {
      const ONCLICK = 'onclick';
      const ONCHANGE = 'onchange';
      const ONBLUR = 'onblur';
      const ONMOUSEOVER = 'onmouseover';
      const ONKEYPRESS = 'onkeypress';
      const ONKEYUP = 'onkeyup';
  }
  
  class Utils
  {
      public static function PodajIdOsoba ($shouldDie = true)
      {
          if (!defined('ID_OSOBA'))
              throw new ConstantNotDefinedException('ID osoba is not defined');

          if (isset($_GET[ID_OSOBA]))
          {
              return (int)$_GET[ID_OSOBA];
          }

          if (isset($_POST[ID_OSOBA])) 
          {
              return (int)$_POST[ID_OSOBA];
          }
          
          if (true === $shouldDie)
              die ('ID osoba missing, script cannot continue');
          
          return null;
      }
  }
  
  class ConstantNotDefinedException extends Exception {}
  
  class UIException extends Exception {}
  class DeprecatedUIException extends UIException {}
?>