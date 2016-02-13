<?php

    require_once 'fpdf.php';
    require_once 'bll/TextUtils.php';
    define('FPDF_FONTPATH', 'wsparcie/font/');
    
    //basic pdf building handling, like coordinates etc, pages number. Wrapper to a specific pdf generator library
    /**
    * @desc enables generating pure text pdf with arbitrary (but reasonable) columns number, as many pages as necessary, text wrapping etc
    */
    class PdfManager {
        
        const FONT_SIZE             = 10;
        const FONT_TYPE             = 'times';
        const FONT_FILE             = 'times.php';
        const FONT_TYPE_TEXT_UTILS  = TextUtils::FONT_TIMES_NEW_ROMAN;
        //right side page limit
        const LIMIT_X               = 210; //
        //end of page limit
        const LIMIT_Y               = 290; //
        
        const LEFT_X_MARGIN         = 7; //
        
        const LENGHT_RATIO          = 0.35;
        
        const COLUMNS_SPACE         = 3; 
        const ROWS_SPACE            = 5;  //size + little extra
        
        //pdf object
        protected $pdf;
        //left indentation for each column, calculated on the fly with coming data; starts with 1, for 1 st column indentation from left is 7, the 2 and next columns depend on
        //longest prev column value, offset is absolute, (includes constant space and previous offset)
        protected $currentX = array();
        //how much are we advanced to the page bottom, a condition to add new page
        //it is never downsized back to start, it is just divided by number of pages to get current page coordinate
        protected $currentY;
        // current page number in pdf
        protected $currentPageNumber = 1;
        // number of pdf pages
        protected $totalPages = 0;
        //how many columns are there in the pdf
        protected $colsNumber = 2; //the default
        // what is the character on which we can break the word to the next line
        protected $wrapDelimiter = ' '; //the default
        // a certain row custom delimiter
        protected $customRowsDelimiters = array();
        //the row values for each column (starting from 1) to be added only after necessary calculations are done
        protected $pdfRows = array();
        // the width of a column for given pages number
        protected $averageColumnWidth;
        
        
        public function __construct($colsNumber = 2, $wrapDelimiter = ' ') {
            
            $this->pdf = new FPDF();
            $this->pdf->open();
            $this->pdf->setCompression(true);
            $this->pdf->SetDisplayMode('real');
            $this->addPage();
            $this->pdf->AddFont(self::FONT_TYPE, '', self::FONT_FILE);
            $this->pdf->setFont(self::FONT_TYPE, '', self::FONT_SIZE);
            
            $this->colsNumber = $colsNumber;
            $this->wrapDelimiter = $wrapDelimiter;
            $this->currentX = array(0 => self::LEFT_X_MARGIN); // todo this and below 7 to const
            $this->currentY = 0;
            $this->averageColumnWidth = (self::LIMIT_X - self::LEFT_X_MARGIN) / $this->colsNumber;
        }
        
        /**
        * @desc Add a page, preserve total count.
        */
        protected function addPage() {
            
            $this->pdf->addPage();
            $this->totalPages++;
        }
        
        /**
        * @desc calculate next y coordinate, add page if turns out is needed
        * @param int current y we now use
        */
        protected function getNextYCoordinate($currentY) {
                       
            // the current Y coordinate is on thenext page, we don't yet have
            if (((int)($currentY / self::LIMIT_Y) + 1) > $this->totalPages) {
                
                $this->addPage();
            }
            
            return ($currentY + self::ROWS_SPACE);
        }
        
        /**
        * @desc Calculate current Y coordinate and page number it belongs to
        * @param int current y we now use
        */
        protected function getYCoordinate ($coordY) {
            
            // calculate the page number the coordinate is used on
            $this->currentPageNumber = (int)(($coordY / self::LIMIT_Y) + 1);
            
            // add page, as current page number points a page that does not yet exist
            if ($this->currentPageNumber > $this->totalPages) {
                
                $this->addPage();
            }
            
            return (($coordY % self::LIMIT_Y) + self::ROWS_SPACE);
        }
        
        /**
        * @desc Add a row to pdf, each column value in array per each column number
        * @param array set of values for each column, columns start from 0
        * @param string custom delimiter
        */
        public function addRow ($colValuesSet, $customDelimier = '') {
            
            if (sizeof($colValuesSet) < $this->colsNumber) {
                
                //just skipping a row, maybe exception ?
                return;
            }
            
            foreach ($colValuesSet as $columnNr => $columnValue) {
                
                //if no more space to right side exception for to many columns, more detail below
                $nextColumn = $columnNr + 1; 
                
                // get the lenght of the phrase to calculate maximum width of column
                $offsetCandidate = TextUtils::getTextLength(self::FONT_TYPE_TEXT_UTILS, $columnValue, self::LENGHT_RATIO) + self::COLUMNS_SPACE + $this->currentX[$columnNr];
                
                if (!isset($this->currentX[$nextColumn]) || $this->currentX[$nextColumn] < $offsetCandidate) {
                    
                    $this->currentX[$nextColumn] = $offsetCandidate;
                }
            }
            
            if ($customDelimier) {
                // save a custom delimiter for particular row
                $this->customRowsDelimiters[sizeof($this->pdfRows)] = $customDelimier;
            }
            
            $this->pdfRows[] = $colValuesSet;
        }
        
        /**
        * @desc Iterate over rows, add them, return pdf
        */
        public function getPdf () {
            
            // normalize column dimensions -> check if exceed maximum and shrink
            $this->normalizeWidth();
            
            foreach ($this->pdfRows as $key => $row) {
                
                $this->printRow($row, isset($this->customRowsDelimiters[$key]) ? $this->customRowsDelimiters[$key] : '');
            }
            
            return $this->pdf;
        }
        
        /**
        * @desc Adjust row widths.
        */
        protected function normalizeWidth () {
            
            // the info about the width of last column (more specificaly x coordinate of a next column after the last, which (obviously) is not there)
            $lastOffset = $this->currentX[$this->colsNumber];
            
            if ($lastOffset <= self::LIMIT_X) {
                
                $this->currentX[$this->colsNumber] = self::LIMIT_X;
                return;
            } else {
                
                $currentCol = 1;
                
                for ($currentCol; $currentCol <= $this->colsNumber; $currentCol++) {
                    
                    $colCurrentWidth = $this->currentX[$currentCol] - $this->currentX[($currentCol - 1)];
                    
                    // cut the width to average for a column. it works, 'cos if all columns fit this logic is not working
                    // on opposite all the previous columns are shrinked to average size, whereas the last one is getting the rest of available space
                    // our use case has 2 columns in use, where 1 will never be wide, so this logic will just shrink the size of 2 column, if data exceed it
                    if ($colCurrentWidth > $this->averageColumnWidth) {
                        
                        //TODO a logic shortening each column width by percentage of how we exceed, bearing in mind how many columns still in front of us
                        //so far no needed, as we will use that for 2 columns. The logic for putting a row needs to verify length and wrap words
                        
                        // <=>
                        
                        // todo might want to add some artificial inteligence to shrink only one column by the value sufficient to avoid shrinking the rest, 
                        //so far considered  overkill
                        if ($currentCol < $this->colsNumber)
                            $this->currentX[$currentCol] = $this->currentX[($currentCol - 1)] + $this->averageColumnWidth;
                        else {
                            
                            $this->currentX[$this->colsNumber] = self::LIMIT_X;
                        }
                    }
                    // else is also not needed, as the thiner columns were adjusted to fit exactly earlier
                }
            }
        }
        
        /**
        * @desc add a single row calculating all coordinates
        */
        protected function printRow($row, $customDelimiter = '') {
            
            // add each column on proper coordinates
            // calculate available x length, wrap word in case to long
            // calculate string length with custom letter length function
                
            $delimiter = $customDelimiter ? $customDelimiter : $this->wrapDelimiter;
            $tmpNewY = $this->currentY;
            
            foreach ($row as $colNumber => $colValue) {
    
                $currentAvWidth = $this->currentX[($colNumber + 1)] - $this->currentX[($colNumber)];
                
                $wrappedText = TextUtils::wrapText(self::FONT_TYPE_TEXT_UTILS, $colValue, $currentAvWidth, $delimiter, self::LENGHT_RATIO);
                if (sizeof($wrappedText) > 1) {
                    
                    $tmpY = $this->currentY;
                    foreach ($wrappedText as $rowElement) {
                        
                        $this->pdf->text($this->currentX[($colNumber)], $this->getYCoordinate($tmpY), $rowElement, $this->currentPageNumber);//
                        $tmpY = $this->getNextYCoordinate($tmpY);
                    }
                    
                    if ($tmpNewY < $tmpY) 
                        $tmpNewY = $tmpY;
                } else {
                    
                    $this->pdf->text($this->currentX[($colNumber)], $this->getYCoordinate($this->currentY), $colValue, $this->currentPageNumber);
                }
            }
            
            //slide y normally or by temporary higher one in case of wrapping
            
            if ($tmpNewY > $this->currentY) {
                $this->currentY = $tmpNewY;
                //todo calculate new current y, % page size; juggle with page nr ;/
            } else    
                $this->currentY = $this->getNextYCoordinate($this->currentY);
            
            // save highest temp Y 
        }
    }