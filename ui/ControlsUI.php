<?php

    class HtmlTable {
        
        const HTML_TAG_GRID_TABLE      = '<table cellspacing="0" cellpadding="3" border="0" align="center" class="gridTable %s">';
        const HTML_TAG_END_TABLE       = '</table>';
        const CSS_ODD_ROW              = 'oddRow';
        const CSS_EVEN_ROW             = 'evenRow';
        const CSS_MARKED_ROW           = 'markedRow';
        const ROWS_PER_PAGE            = 10;
        
        const FORM_PAGE_ID             = 'pageId';
        
        private $tableRowsList = array();
        
        private $counter = 0;
        private $isHeaderSet = false;
        private $tableCss = array();
        
        public function __construct ($rowsSet = null, $colsOrder = null) {
            
            if (!is_null($rowsSet)) {
                
                foreach ($rowsSet as $row) {
                    $this->addRow($row, $colsOrder);
                }
            }
        }
        
        public function addTableCss ($css) {
            
            $this->tableCss[] = $css;
        }
        
        /**
        * @desc add a row to html table
        * @param array $colsSet
        * @param array $colsOrder
        */
        public function addRow ($colsSet, $colsOrder = null, $css = null) {
            
            if (!is_array($colsSet))
                return false;
                
            $this->counter++;
            if (is_null($css))
                $css = (($this->counter % 2) == 0) ? self::CSS_ODD_ROW : self::CSS_EVEN_ROW;
                
            $rowHtml = '<tr class="'.$css.'">';
            if ($colsOrder) {
                
                foreach ($colsOrder as $colIndex) {
                    if (!isset($colsSet[$colIndex]))
                        $colsSet[$colIndex] = '----';
                        
                    $rowHtml .= '<td>'.$colsSet[$colIndex].'</td>';
                }
            } else {
                foreach ($colsSet as $column) {
                    
                    $rowHtml .= '<td>'.$column.'</td>';
                }
            }
            $rowHtml .= '</tr>';
            $this->tableRowsList[] = $rowHtml;
            return true;
        }
        
        /**
        * @desc Set table header
        * @param array colsSet
        * @param array colsOrder
        */
        public function setHeader ($colsSet, $colsOrder = null) {
            
            if (true === $this->isHeaderSet) {
                array_shift($this->tableRowsList);
            }
            
            if (!is_array($colsSet) || (!is_null($colsOrder) && !is_array($colsOrder)))
                return false;
            
            $rowHtml = '<tr>';
            if ($colsOrder) {
                
                foreach ($colsOrder as $colIndex) {
                    if (!isset($colsSet[$colIndex]))
                        $colsSet[$colIndex] = '----';
                        
                    $rowHtml .= '<th>'.$colsSet[$colIndex].'</th>';
                }
            } else {
                
                foreach ($colsSet as $column) {
                    
                    $rowHtml .= '<th>'.$column.'</th>';
                }
            }
            $rowHtml .= '</tr>';
            
            array_unshift($this->tableRowsList, $rowHtml);
            $this->isHeaderSet = true;
            return true;
        }
        
        public function __toString () {
            
            return sprintf(self::HTML_TAG_GRID_TABLE, implode(' ', $this->tableCss)) . implode('', $this->tableRowsList) . self::HTML_TAG_END_TABLE;
        }
        
        public static function renderPagination($rowNumber, $url, $rowsPerPage = self::ROWS_PER_PAGE) {
            $htmlControls = new HtmlControls();
            $result = "<form action=\"$url\" method=\"GET\">"
                    . "<table align = \"CENTER\"><tr>";
            
            //preserve get params
            $uriParams = parse_url($url, PHP_URL_QUERY);
            
            if(!is_null($uriParams)) {
                $params = explode('&', $uriParams);
                foreach ($params as $p) {
                    list($name, $value) = explode('=', $p);
                    if($name == self::FORM_PAGE_ID) continue;
                    $result .= $htmlControls->_AddHidden($name, $name, $value);
                }
            }
            
            $page = isset($_REQUEST[self::FORM_PAGE_ID]) ? (int) $_REQUEST[self::FORM_PAGE_ID] : 1;
            
            for ($pageNr = 1; $pageNr <= ceil(($rowNumber / $rowsPerPage)); $pageNr++)
                {
                    $result .= "<td nowrap align = 'CENTER'>";
                    $result .= $htmlControls->_AddSubmit(
                            USER::PRIV_NONE_REQUIRED, 
                            self::FORM_PAGE_ID, 
                            self::FORM_PAGE_ID, 
                            $pageNr, 
                            ($pageNr == $page) ? 'active' : '', 
                            ''
                    );
                    $result .= "</td>";
                }
            $result .= "</tr></table></form>";
            unset($htmlControls);
            return $result;
        }
    }