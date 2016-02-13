<?php

    //TODO add combos
    //it is highly dependant on adl dicts implementation
    class HtmlControls {
        
        const NO_RIGHT = '';
        
        const INPUT_TYPE_TEXT       = 'text';
        const INPUT_TYPE_PASSWORD   = 'password';
        
        const SELECT_ITEM_OPTION_TAGS = 'option_tags';
        const SELECT_ITEM_FOUND = 'option_found';
        const SELECT_ITEM_SELECTED_VALUE = 'option_selected_value';
        const SELECT_ITEM_SELECTED_ID = 'option_selected_id';
        const SELECT_ITEM_IDS_SELECTED = 'option_selected_ids';
        
        private $user;
        private $errorsList = array();
        private $hasSelectHelper = false;
        
        public function __construct ($errors = array()) {
            
            $this->user = User::getInstance();
            // this should have instantiation level error object, it will have array instead
            $this->errorsList = $errors;
        }
        
        /**
        * @desc Set the list of errors
        * @param array list of errors
        */
        public function setErrors ($errors) {
            
            $this->errorsList = $errors;
        }
        
        // Priviledges challenged controls
        
        public function _AddSubmit($priviledge, $name, $id, $value, $css, $validation, $type = 'submit') {
            
            if (!$this->user->isAllowed($priviledge))
                return self::NO_RIGHT;
            
            return '<input type="'.$type.'" name="'.$name.'" id="'.$id.'" value="'.$value.'" class="formreset '.$css.'" '.$validation.' />';
        }
        
        /**
        * @desc Add a submit button that warns before deleting entry, priviledge arbitrary (business rules allow weaker deletes on edit priviledge)
        */
        public function _AddDeleteSubmit($priviledge, $name, $id, $value, $css, $validation, $onclick, $type = 'submit') {
            
            if (!$this->user->isAllowed($priviledge))
                return self::NO_RIGHT;
            
            return '<input type="'.$type.'" name="'.$name.'" id="'.$id.'" value="'.$value.'" class="formreset '.$css.'" '.$validation.' '.JsEvents::ONCLICK.'="'.$onclick.'; return confirm(\'Operacja jest nieodwracalna, czy jeste¶ pewien ?\');" />';
        }
        
        public function _AddNoPrivilegeSubmit($name, $id, $value, $css, $validation, $type = 'submit') {
                        
            return '<input type="'.$type.'" name="'.$name.'" id="'.$id.'" value="'.$value.'" class="formreset '.$css.'" '.$validation.' />';
        }
        
        public function _AddHidden($id, $name, $value = '') {
            
            return '<input type="hidden" name="'.$name.'" id="'.$id.'" value="'.View::escapeOutput($value).'" />';
        }
        
        public function _AddTextbox($name, $id, $value, $length, $size, $validation, $css = '', $errMsg = '', $onblurvalidation = '', $type = self::INPUT_TYPE_TEXT)  
        {
            $errMsg = $this->getErrMsg($name, $errMsg);
                                                                            
            return '<input type="'.$type.'" name="'.$name.'" id="'.$id.'" value="'.View::escapeOutput($value).'" class="formfield '.$css.'" maxlength="'.$length.'" size="'.$size.'" 
            onblur="this.value = utils.trim(this.value); '.$onblurvalidation.'" '.$validation.' />'.$this->_addErrorLabel($id, $errMsg);
        }
        
        public function _AddTextarea($name, $id, $value, $length, $rows, $cols, $validation, $css = '', $errMsg = '', $onblurvalidation = '', $type = self::INPUT_TYPE_TEXT)  
        {
            $errMsg = $this->getErrMsg($name, $errMsg);                                                                    
            $_value = View::escapeOutput($value);
            
            return '<textarea id="'.$id.'" wrap="ON" name="'.$name.'" rows="'.$rows.'" cols="'.$cols.'" maxlength="'.$length.'" class="formfield '.$css.'" value="'.$_value.'" 
            onblur="this.value = utils.trim(this.value); '.$onblurvalidation.'" '.$validation.'>'.$_value.'</textarea>'.$this->_addErrorLabel($id, $errMsg);
        }
        
        public function _AddCheckbox($name, $id, $checked, $specific, $komentarz='', $value='')  
        {
            if(is_bool($checked))
            {
                if($checked)
                    $checked = 'checked="checked"';
                else
                    $checked = '';
          }
          
          return '<input type="checkbox" name="'.$name.'" id="'.$id.'" value="'.View::escapeOutput($value).'" onmouseup="blur();" class="formfield" '.$checked.' '.$specific.' /><label for="'.$id.'"><i>'.$komentarz.'</i></label>';
        }
        
        public function _AddDatebox($name, $id, $value, $length, $size, $css = '', $errMsg = '')  //date designed textbox
        {
            $errMsg = $this->getErrMsg($name, $errMsg);                                                                    
            return '<input type="text" name="'.$name.'" id="'.$id.'" value="'.View::escapeOutput($value).'" onkeypress="return validations.DateValidate(this, event);" 
                onblur="CheckLength(this);" onkeyup="DateKeyUp(this);"  maxlength="'.$length.'" size="'.$size.'" class="formfield '.$css.'">'.$this->_addErrorLabel($id, $errMsg);
        }
        
        public function _AddPostCodebox($name, $id, $value, $css = '', $errMsg = '')  //textbox
        {
            $errMsg = $this->getErrMsg($name, $errMsg);
                                                                                
            return '<input type="text" name="'.$name.'" id="'.$id.'" value="'.View::escapeOutput($value).'" onkeypress="return validations.DateValidate(this, event);" 
                onBlur="sprawdz_kod(this);" maxlength="6" size="6" class="formfield '.$css.'">'.$this->_addErrorLabel($id, $errMsg);
        }  
        
        public function _AddNumberbox($name, $id, $value, $length, $size, $validation, $css = '', $errMsg = '')  //nuber designed textbox
        {
            $errMsg = $this->getErrMsg($name, $errMsg);
                                                                            
            return '<input type="text" name="'.$name.'" id="'.$id.'" value="'.View::escapeOutput($value).'" onkeypress="return validations.OnlyNumber(this, event);" 
                '.$validation.' maxlength="'.$length.'" size="'.$size.'" class="formfield '.$css.'">'.$this->_addErrorLabel($id, $errMsg);
        }
        
        public function GroupChoiceControl ($butValue, $butName, $txtName, $txtId, $txtValue, $hidName, $hidId, $hidValue, $scrUrl, $windowName, $css = '', $errMsg = '')
        {
            if ($txtValue && !$hidValue) {
                
                $errMsg .= ' Warto¶æ '.View::escapeOutput($txtValue).' nie znaleziona.';
                $txtValue = '';
            }
            
            return $this->_AddTextbox($txtName, $txtId, View::escapeOutput($txtValue), 30, 30, 'readonly="readonly"', $css, $errMsg)
                .$this->_AddHidden($hidId, $hidName, $hidValue)
                .$this->_AddNoPrivilegeSubmit($butName, $butName, $butValue, '', 
                    JsEvents::ONCLICK.'="window.open(\''.$scrUrl.'\', \''.$windowName.'\', \'toolbar=no, scrollbars=yes, width=750,height=650\')"', 'button');

      }
        
        /**
        * @desc Add drop down list html for given data.
        * @param string name Select name
        * @param string id html element id
        * @param array data the list to fill combo box with
        * @param string selectedItem int or string value to be selected
        * @param string hidden id - id of a hidden appended to select with chosen id value
        * @param bool is selected item an id - indicates whether the value to be selected by default in combo box is an int id or string value
        * @param string errMsg an error message for potential error that might have occured
        * @param string specific any arbitrary valid html attributes
        * @param string css class or space separated classes
        * @param array hidden name => source column set of hidden fields and value source of additional data to be set when an item is chosen
        * @param string
        */
        //TODO custom columns ...
        public function _AddSelect($name, $id, $data, $selectedItem, $hiddenId, $isId = true, $errMsg = '', $specific = '', $css = '', $addInfoHiddenMapping = array(), $onchange = '')
        {
            $columnId = 'id';
            $addInfoHiddenMapping[$hiddenId] = $columnId;
            
            //when selected value is a value which is not found on the list (same might be for id) output this as improper
            $str = '<span class="selectContainer"><select class="'.$css.'" name="'.$name.'" id="'.$id.'" 
                onkeyup="utils.selectAutoFill(this, event);" onchange="this.options[this.selectedIndex].click();'.$onchange.'" onblur="this.options[this.selectedIndex].click();" 
                class="formfield" '.$specific.'>';

            $results = $this->generateSelectOptions($data, 'nazwa', $columnId, $selectedItem, $isId, null, $addInfoHiddenMapping);
            $str .= $results[self::SELECT_ITEM_OPTION_TAGS];
            
            if ($selectedItem && $results[self::SELECT_ITEM_FOUND] === false && $errMsg == '') {
                
                $errMsg = 'Warto¶æ '.View::escapeOutput($selectedItem).' nie znaleziona/niepoprawna.';
            }
            
            $str .= '</select></span>'.$this->_addErrorLabel($id, $errMsg);
                
            foreach ($addInfoHiddenMapping as $hiddenName => $columnName) {
                
                $str .= $this->_AddHidden($hiddenName, $hiddenName, $results[self::SELECT_ITEM_IDS_SELECTED][$hiddenName]);
            }

            $str .= $this->AddSelectHelpHidden();
          
            return $str;
        }
        
        function _AddPopUpButton($name, $id, $value,  $openSite, $width, $height, $css = '')
        {
            $str = "<input 
                        id=\"".$id."\"
                        type=\"button\" 
                        value=\"".$value."\" 
                        name=\"".$name."\" 
                        class=\"formreset ".$css."\" 
                        onClick = \"window.open('".$openSite."', '".$name."','toolbar=no, scrollbars=yes, width=".$width.",height=".$height."')\"/>";
            return $str;
        }
        
        /**
        * @desc protected helpers
        */
        
        protected function AddSelectHelpHidden()
        {
            if ($this->hasSelectHelper == false) {
                
                $this->hasSelectHelper = true;
                
                return $this->_AddHidden('ukryty_js', 'ukryty_js', null)
                    .$this->_AddHidden('id_temp_sel_helper', 'id_temp_sel_helper', null)
                    .$this->_AddHidden('id_sel_autofill', 'id_sel_autofill', null);
            }
            
            return '';
        }
        
        protected function _addErrorLabel ($id, $value)
        {
            return '<label class="error" for="'.$id.'">'.$value.'</label>';
        }
        
        protected function getErrMsg ($name, $errMsg) {
            
            if ($errMsg == '') {
                
                if (isset($this->errorsList[$name])) {
                    
                    $errMsg = $this->errorsList[$name];
                }
            }
            
            return $errMsg;
        }

        // $columnLabel only if we indeed want some fancy title
        /**
        * @desc generate option tags for select html tag, return all necessary info about the select listing
        * @param array source data list for options
        * @param string column name for option value
        * @param string column name for option id
        * @param mixed selected value - id or value 
        * @param bool if the selected value is id or value
        * @param string column name for option title
        * @param array hidden name => source column set of hidden fields and value source of additional data to be set when an item is chosen
        * @return array option tags html, bool is selected item found, id we have selected, all additional info specific for each option that we have selected 
        */
        protected function generateSelectOptions($result, $columnValue, $columnId, $selectedItem = null, $isSelectedItemId = true, $columnLabel = null, $addInfoHiddenMapping = array())
        {
            /*
            concept approved by proof of concept :) 
            select has onchange method firing click on chosen option; option in onclick has as many things as we need it to have
            */
            
            $selectedValue = null;
            $selectedId = null;
            $selectedIdsList = array();
            $isSelectedItemFound = false;
            
            if (is_null($selectedItem)) {
                
                // first array element id selectedId
                reset($result);
                $row = current($result);
                $selectedId = $row[$columnId];
                $selectedValue = $row[$columnValue];
                $isSelectedItemFound = true;
            } else {
                
                // decide which column responds for selected item
                $targetColumn = (true === $isSelectedItemId) ? $columnId : $columnValue;
                
                foreach ($result as $row) {
                    
                    if ($selectedItem == $row[$targetColumn]) {
                        
                        // set selected value/id too, verify is selected item found
                        $selectedId = $row[$columnId];
                        $selectedValue = $row[$columnValue];
                        $isSelectedItemFound = true;
                        // not necessary to iterate any more
                        break;
                    }
                }
            }
            
            $optionsSet = '';
            
            foreach ($result as $row) {
                
                $onclick = array();
                
                foreach($addInfoHiddenMapping as $hiddenName => $respectiveColumnName) {
                    
                    $onclick[] = 'utils.setHiddenValue(\''.$row[$respectiveColumnName].'\', \''.$hiddenName.'\');';
                    
                    if ($row[$columnId] == $selectedId) {
                        
                        $selectedIdsList[$hiddenName] = $row[$respectiveColumnName];
                    }
                }
                
                $selected = ($row[$columnId] == $selectedId) ? 'selected' : '';
                $title = !is_null($columnLabel) ? 'title="'.$row[$columnLabel].'"' : '';
                
                $optionsSet .= '<option id="'.$row[$columnId].'" value="'.$row[$columnValue].'" 
                    onclick="'.implode('', $onclick).'" '.$selected.' '.$title.'>'.$row[$columnValue].'</option>';
            }
            
            // we return array of data: option tags html, bool is selected item found, id we have selected, all additional info specific for each option that we have selected 
            return array(
                self::SELECT_ITEM_OPTION_TAGS => $optionsSet,
                self::SELECT_ITEM_FOUND => $isSelectedItemFound,
                self::SELECT_ITEM_IDS_SELECTED => $selectedIdsList,
                // these 2 shoul be unnecessary
                self::SELECT_ITEM_SELECTED_ID => $selectedId,
                self::SELECT_ITEM_SELECTED_VALUE => $selectedValue,
            );
        }
    }