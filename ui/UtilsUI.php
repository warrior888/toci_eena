<?php
    
    class UtilsUI 
    {
        protected $unikatNazwaGrid = '';
        protected $nazwaHiddenAkcja = '';
        protected static $controls;
        
        public function __construct($unNazwaGrid, $nazwaHiddenAkcja)
        {
            $this->unikatNazwaGrid = $unNazwaGrid;
            $this->nazwaHiddenAkcja = $nazwaHiddenAkcja;
            self::$controls = new valControl();
        }
        
        protected $visibleColumns = array();
        protected $headers = array();
        //enforce those types later
        public function setVisibleColumns ($columns) //array
        {
            $this->visibleColumns = $columns;
        }
        
        public function setHeaders ($headers)//array
        {
            $this->headers = $headers;
        }
        
        protected $sortowanie = false;
        protected $formularz;
        protected $przyciskFormularz;
        protected $formularzKier;
        protected $formularzKol;
        
        public function setSort($form, $formSubmit, $sortDir, $sortCol)
        {
            $this->sortowanie = true;
            $this->formularz = $form;
            $this->przyciskFormularz = $formSubmit;
            $this->formularzKier = $sortDir;
            $this->formularzKol = $sortCol;
        }
        
        protected $liczbaPorzadkowa = 1;
        
        public function displayData ($data, $iloscRek, $offset)
        {
            $i = $iloscRek;
            $rekordyNaStrone = $_SESSION['ilosc_rekordow'];
            $page = $offset;
            if ($offset >= 5)
            {
                $offset = $offset - 5;
                $limit = $offset + 10;
            }
            else
            {
                $offset = 0;
                $limit = 10;
            }
            if ($limit > $iloscRek)
                $limit = $iloscRek;
            $licz = 0;
            $pStr = $offset;
            $this->liczbaPorzadkowa += ($offset * $rekordyNaStrone);
            
            $resultingHtml = '<table cellspacing="0"><tr>';
            
            if ($i > 0)
            {
                $resultingHtml .= '<td class="roseBkgnd">Znaleziono rekordów: '.$iloscRek.'.</td>';
                
                $resultingHtml .= '<td class="roseBkgnd">podstrony : </td>';
            
                while ($i > 0)
                {
                    $onclick = 'parent.frames[2].document.osoby_kwerendy_stronicowanie.wyb_strona.value='.$licz.'; parent.frames[2].document.osoby_kwerendy_stronicowanie.submit();';
                    if ($licz >= $offset && $licz < ($limit))
                    {
                        $onclick = 'PokazStrone(this, \'strona_grid'.$this->unikatNazwaGrid.'\', \'strona_grid_wid'.$this->unikatNazwaGrid.'\', \'str_element'.
                            $this->unikatNazwaGrid.'\', '.$pStr.');';
                        $pStr++;
                    }
                    if ($page == $licz)
                        $color = 'color: yellow;';
                    else
                        $color = 'color: black;';
                    $resultingHtml .= '<td id="str_element'.$this->unikatNazwaGrid.$licz.'" style="cursor: pointer; '.$color.'" class="roseBkgnd" onclick="'.$onclick.'"> '.($licz + 1).' </td>';
                    $licz++;
                    $i -= $rekordyNaStrone;
                }
            }
            $resultingHtml .= '</tr></table>';
            $resultingHtml .= self::$controls->AddHidden('strona_grid_wid'.$this->unikatNazwaGrid, 'strona_grid_wid'.$this->unikatNazwaGrid, $page);
            $resultingHtml .= self::$controls->AddHidden($this->nazwaHiddenAkcja, $this->nazwaHiddenAkcja, '');

            $i = 0;
            $licz = $page;
            $count = 0;
            if(is_array($data))
            foreach ($data as $key => $row)
            {
                if ($i % $rekordyNaStrone == 0)
                {
                    if ($page == $offset)
                        $wyswietl = '';
                    else
                        $wyswietl = 'display: none;';
                            
                    $resultingHtml .= '<div id="strona_grid'.$this->unikatNazwaGrid.$offset.'" style="'.$wyswietl.'"><table cellspacing="0" class="gridTable"><tr bgcolor="#0099CC" valign="center">';
                    
                    $offset++;
                    //if (isset($row['osoba_id']))
                    $resultingHtml .= $this->buttonsNag(); //$row['osoba_id']
                    
                    foreach ($this->headers as $headerName => $header)
                    {
                        $resultingHtml .= '<th nowrap align="center">';

                        if ($this->sortowanie)
                        {
                            //nazwe formularza mozna wykorzystac do dobrania sie do przyciskow bez getelementbyid: document.form.nazwa_el.value
                            //to sie moze przydac przy pominieciu konfliktu jesli id sie powiela; przy przycisku jest to wykorzystane
                            if (strlen($this->przyciskFormularz) > 0)
                            {
                                $jsSubmit = $this->przyciskFormularz.'.click()';
                            }
                            else
                            {
                                $jsSubmit = 'submit()';
                            }
                            $resultingHtml .= '<img width="15" height="15" style="cursor: pointer;" class="przezrocze" src="../zdj/strzalka_dol.gif" onclick="'.$this->formularzKier.'.value=1; '.$this->formularzKol.'.value=\''.
                            $headerName.'\';'.$this->formularz.'.'.$jsSubmit.';"></img>&nbsp;';
                        }
                        $resultingHtml .= $header;
                        if ($this->sortowanie)
                        {
                            $resultingHtml .= '&nbsp;<img width="15" height="15" style="cursor: pointer;" class="przezrocze" src="../zdj/strzalka_gora.gif" onclick="'.$this->formularzKier.'.value=0; '.$this->formularzKol.'.value=\''.
                            $headerName.'\';'.$this->formularz.'.'.$jsSubmit.';"></img>';
                        }
                        $resultingHtml .= '</th>';
                    }
                    $resultingHtml .= '</tr>';
                }
                $count++;
                $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
                //buttons
                $resultingHtml .= '<tr class="'.$css.'" onmouseover="markRow(this, \'hoveredRow\');" onmouseout="markRow(this, \''.$css.'\');" onclick="pointRow(this, \'markedRow\');">';
                if (isset($row['osoba_id']))
                    $resultingHtml .= $this->buttons($row['osoba_id']);

                foreach ($this->visibleColumns as $colKey => $column)
                {
                    $resultingHtml .= '<td>';
                    if (isset($row[$colKey]))
                        $resultingHtml .= $row[$colKey];
                    else
                        $resultingHtml .= ' - ';
                    $resultingHtml .= '</td>';
                }
                $resultingHtml .= "</tr>";
                
                $i++;
                if ($i % $rekordyNaStrone == 0)
                {
                    $resultingHtml .= '</table></div>';
                }
            }
            
            if ($i % $rekordyNaStrone != 0)
            {
                $resultingHtml .= '</table></div>';
            }
            
            return $resultingHtml;
        }
        
        /*public function getControls ()
        {
            return self::$controls;
        }*/
        
        //TODO: permisions to consts
        public function buttonsNag ($choice = array())
        {
            $result = '';
            if (isset($_SESSION['edycja_rekordu']))
            {
                if (($choice && isset($choice['edycja_rekordu'])) || !$choice)
                    $result .= '<th>Edycja</th>';
            }
            //naglowek kasowania
            if (isset($_SESSION['kasowanie_rekordu']))
            {
                if (($choice && isset($choice['kasowanie_rekordu'])) || !$choice)
                    $result .= '<th>Kasowanie</th>';
            }
            //naglowek zetla
            if (isset($_SESSION['dodawanie_zettla']))
            {
                if (($choice && isset($choice['dodawanie_zettla'])) || !$choice) 
                    $result .= '<th>Zettel</th>';
            }
            //zmienna sesyjna id'ków ludzi do edycji masowej
            if (isset($_SESSION['edycja_grupowa']))
            {
                if (($choice && isset($choice['edycja_grupowa'])) || !$choice)
                    $result .= '<th><input type="checkbox" onClick="selectAll(this);" /></th>';
            }
            $result .= '<th>LP.</th>';
            return $result;
        }
        //todo: plug like buttons nag
        public function buttons ($osobaId)
        {
            $result = '';
            if (isset($_SESSION['edycja_rekordu']))
            {
                $result .= '<td nowrap align="CENTER">';
                $result .= self::$controls->AddTableSubmit('edytuj_osobe', $osobaId, 'Edytuj.', 'onClick="'.$this->nazwaHiddenAkcja.'.value=this.id;"');
                $result .= '</td>';
            }
            //przycisk kasowania, zasada okreslania id osoby jak powyzej
            if (isset($_SESSION['kasowanie_rekordu']))
            {
                $result .= '<td nowrap align="CENTER">';
                $result .= self::$controls->AddTableSubmit('kasuj_osobe', $osobaId, 'Kasuj.', 'onClick="'.$this->nazwaHiddenAkcja.'.value=this.id; return confirm(\'Operacja jest nieodwracalna, czy jeste¶ pewien ?\');"');
                $result .= '</td>';
            }
            //przycisk zettla, ktory na klikniecie ma wpisywac bierzaca date i zapis zettel do tabeli korespondencji z id osoby, kolo ktorej jest klikany
            if (isset($_SESSION['dodawanie_zettla']))
            {
                $result .= '<td nowrap align="CENTER">';
                $result .= self::$controls->AddTableSubmit('id_zettel', $osobaId, 'Zettel.', 'onClick="'.$this->nazwaHiddenAkcja.'.value=this.id;"');
                $result .= '</td>';
            }
            //zapamietywanie kolejnych id w zmiennej sesyjnej potrzebnej do edycji masowej
            if (isset($_SESSION['edycja_grupowa']))
            {
                $result .= '<td nowrap align="CENTER"><input type="checkbox" name="id_osoby_checkbox[]"
                value="'.$osobaId.'" title="Id - '.$osobaId.'"></td>';
                if (empty($_SESSION['edycja_masowa']))
                    $_SESSION['edycja_masowa'] = '';
                    
                $_SESSION['edycja_masowa'] = $_SESSION['edycja_masowa'].$osobaId.'|';
            }

            $result .= '<td>'.$this->liczbaPorzadkowa.'</td>';
            $this->liczbaPorzadkowa++;
            
            return $result;
        }
        
        /**
        * @desc Return the html for person add/update form - the additional data snipet
        * @param valControl controls
        * @param array addElements additional elements list
        * @param optional array additional person data
        * @param optional array error messages
        */
        //TODO use html controls here
        public static function formAdditionalData ($controls, $addElements, $addElementsData = array(), $errMsgs = array())
        {
            $result = '';
            $data[] = array('id' => '', 'nazwa' => '--------');
            $data[] = array('id' => 'tak', 'nazwa' => 'tak');
            $data[] = array('id' => 'nie', 'nazwa' => 'nie');
            $count = 0;
            
            
            foreach ($addElements as $key => $addElement)
            {
                $key = $addElement['nazwa']; //todo model
                if ($addElement['edycja'] == true)
                {
                    $count++;
                    $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
                    $result .= '<tr class="'.$css.'"><td>'.$addElement['nazwa_wyswietlana'].':</td><td>';
                    $dana = null;

                    if (isset($addElementsData[$key])) {
                        
                        // Dane z edycji (z bazy), moga byc bardziej kompletne, mamy liste kolumn, stad wyciaganie konkretnej wartosci
                        // Aktualnie uzywana jest metoda upraszczajaca, bo pozostale dane mamy i tak pobrane osobno
                        // GOWNO isset (empty tez) dla stringowego klucza przy operacjiu indexowania stringu podaje true - scislej najpewniej:
                        // - stwierdza ze ma string na warsztacie, nie array
                        // - rzutuje index na int - wylazi 0
                        // - isset od klucza 0 true jesli string jest conajmniej litera³em 1 znakowym !! super !
                        // Teraz pytanie czy czasem tego nie zmienili w nowszych wersjach
                        if (is_array($addElementsData[$key]) && isset($addElementsData[$key]['wartosc'])) {
                            
                            $dana = $addElementsData[$key]['wartosc'];
                        } else {
                            
                            // dana po poscie lub prostej edycji, brak jakichkolwiek wiecej info
                            $dana = $addElementsData[$key];
                        }
                    }
                    
                    switch ($addElement['id_typ'])
                    {
                        case QueriesBase::VALIDATION_INT:
                            if ($controls instanceof valControl) {
                                $result .= $controls->AddNumberbox($addElement['nazwa'], $addElement['id'], $dana, 5, 5, '', 'genericWidth');//pobor danych i uzupelnienie jesli sa
                            } else {
                                $result .= $controls->_AddNumberbox($addElement['nazwa'], $addElement['id'], $dana, 5, 5, '', 'genericWidth', 
                                isset($errMsgs[$key]) ? $errMsgs[$key] : null);
                            }
                            
                            break;
                        case QueriesBase::VALIDATION_STRING:
                            if ($controls instanceof valControl) {
                                $result .= $controls->AddAnkietaTextbox($addElement['nazwa'], $dana, 30, 20, '', '', 'formfield genericWidth');
                            } else {
                                $result .= $controls->_AddTextbox($addElement['nazwa'], $addElement['nazwa'], $dana, 30, 20, '', 'formfield genericWidth', 
                                isset($errMsgs[$key]) ? $errMsgs[$key] : null);
                            }
                            break;
                        case QueriesBase::VALIDATION_BOOL:
                            if ($controls instanceof valControl) {
                                $result .= $controls->_AddSelectData($addElement['nazwa'], $addElement['nazwa'], '', $data, $dana, $addElement['id'], 'genericWidth');
                            } else {
                                $result .= $controls->_AddSelect($addElement['nazwa'], $addElement['nazwa'], $data, $dana, $addElement['id'], false, 
                                isset($errMsgs[$key]) ? $errMsgs[$key] : null, '', 'genericWidth');
                            }
                            break;
                    }
                    $result .= '</td></tr>';//<td>&nbsp;</td><td>&nbsp;</td>
                }
            }
            
            return $result;
        }
        
        public static function searchFormElement ($controls, $column, $validations, $show = '', $negate = '', $filter = '', $missing = '', $rowCss = '')
        {
            $result ='<tr class="'.$rowCss.'"><td>';
            $result .= $controls->_AddCheckbox($column[QueriesEngine::COLUMN_TABLE].DESC_SEPARATOR.$column[QueriesEngine::COLUMN_NAME].DESC_SEPARATOR.'show', $column[QueriesEngine::COLUMN_TABLE].DESC_SEPARATOR.$column[QueriesEngine::COLUMN_NAME].DESC_SEPARATOR.'show', $show, '', $column[QueriesEngine::COLUMN_HEADER]);
            $result .= '</td><td>';
            $result .= $controls->_AddCheckbox($column[QueriesEngine::COLUMN_TABLE].DESC_SEPARATOR.$column[QueriesEngine::COLUMN_NAME].DESC_SEPARATOR.'not', $column[QueriesEngine::COLUMN_TABLE].DESC_SEPARATOR.$column[QueriesEngine::COLUMN_NAME].DESC_SEPARATOR.'not', $negate, '', 'Not');
            $result .= '</td><td>';
            $validation = $validations[$column[QueriesEngine::COLUMN_VALIDATION]][QueriesEngine::INDEX_EVENT].'="return '.$validations[$column[QueriesEngine::COLUMN_VALIDATION]][QueriesEngine::INDEX_FUNCTION].'(this, event);"';
            $length     = isset($validations[$column[QueriesEngine::COLUMN_VALIDATION]][QueriesEngine::INDEX_LENGTH]) ? $validations[$column[QueriesEngine::COLUMN_VALIDATION]][QueriesEngine::INDEX_LENGTH] : '';
            
            if ($column[QueriesEngine::COLUMN_VALIDATION] == QueriesEngine::VALIDATION_BOOL)
            {
                $data[] = array('id' => '', 'nazwa' => '--------');
                $data[] = array('id' => 'tak', 'nazwa' => 'tak');
                $data[] = array('id' => 'nie', 'nazwa' => 'nie');
                $result .= $controls->_AddSelectData('SELECT'.DESC_SEPARATOR.$column[QueriesEngine::COLUMN_TABLE].DESC_SEPARATOR.$column[QueriesEngine::COLUMN_NAME].DESC_SEPARATOR.'filter', 
                    'SELECT'.DESC_SEPARATOR.$column[QueriesEngine::COLUMN_TABLE].DESC_SEPARATOR.$column[QueriesEngine::COLUMN_NAME].DESC_SEPARATOR.'filter', '', $data, 
                    $filter, $column[QueriesEngine::COLUMN_TABLE].DESC_SEPARATOR.$column[QueriesEngine::COLUMN_NAME].DESC_SEPARATOR.'filter');
            }
            else
            {
                $result .= $controls->AddTextbox($column[QueriesEngine::COLUMN_TABLE].DESC_SEPARATOR.$column[QueriesEngine::COLUMN_NAME].DESC_SEPARATOR.'filter', 
                    $column[QueriesEngine::COLUMN_TABLE].DESC_SEPARATOR.$column[QueriesEngine::COLUMN_NAME].DESC_SEPARATOR.'filter', $filter, $length, '100', $validation);
            }
            $result .= '</td><td>&nbsp;&nbsp;&nbsp; v &nbsp;&nbsp;&nbsp;';
            $result .= $controls->_AddCheckbox($column[QueriesEngine::COLUMN_TABLE].DESC_SEPARATOR.$column[QueriesEngine::COLUMN_NAME].DESC_SEPARATOR.'missing', $column[QueriesEngine::COLUMN_TABLE].DESC_SEPARATOR.$column[QueriesEngine::COLUMN_NAME].DESC_SEPARATOR.'missing', $missing, '', 'Brak informacji');
            $result .= '</td></tr>';
            
            return $result;
        }
    }