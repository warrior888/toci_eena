<?php

    class TextUtils {
        
        const FONT_TIMES_NEW_ROMAN = 'tnr';
        const GENERIC_WIDTH = 4; //in case unrecognized character spot, just add at least the smallest character width
        
        protected static $allowedFonts = array(
            self::FONT_TIMES_NEW_ROMAN      => self::FONT_TIMES_NEW_ROMAN
        );
        //w is a reference for all measures, size is for 10 pixel letters
        protected static $letterLength = array(
            self::FONT_TIMES_NEW_ROMAN => array(
                'a' => 4,
                '±' => 4,
                'A' => 8,
                '¡' => 8,
                'b' => 6,
                'B' => 7,
                'c' => 4,
                'æ' => 4,
                'C' => 7,
                'Æ' => 7,
                'd' => 6,
                'D' => 8,
                'e' => 4,
                'ê' => 4,
                'E' => 7,
                'Ê' => 7,
                'f' => 2,
                'F' => 6,
                'g' => 6,
                'G' => 8,
                'h' => 6,
                'H' => 8,
                'i' => 3,
                'I' => 3,
                'j' => 3,
                'J' => 3,
                'k' => 6,
                'K' => 8,
                'l' => 3,
                '³' => 3,
                'L' => 7,
                '£' => 7,
                'm' => 7,
                'M' => 9,
                'n' => 6,
                'ñ' => 6,
                'N' => 8,
                'Ñ' => 8,
                'o' => 6,
                'ó' => 6,
                'O' => 8,
                'Ó' => 7,
                'p' => 6,
                'P' => 7,
                'q' => 6,
                'Q' => 8,
                'r' => 3,
                'R' => 7,
                's' => 4,
                '¶' => 4,
                'S' => 6,
                '¦' => 6,
                't' => 3,
                'T' => 6,
                'u' => 6,
                'U' => 8,
                'v' => 6,
                'V' => 7,
                'w' => 8,
                'W' => 10,
                'x' => 4,
                'X' => 7,
                'y' => 6,
                'Y' => 8,
                'z' => 4,
                '¿' => 4,
                '¼' => 4,
                'Z' => 6,
                '¯' => 6,
                '¬' => 6,
                '0' => 6,
                '1' => 6,
                '2' => 6,
                '3' => 6,
                '4' => 6,
                '5' => 6,
                '6' => 6,
                '7' => 6,
                '8' => 6,
                '9' => 6,
                '.' => 2,
                '-' => 3,
                ',' => 2,
                '/' => 3,
                '|' => 2,
                '(' => 3,
                ')' => 3,
                '[' => 3,
                ']' => 3,
                '{' => 4,
                '}' => 4,
                ' ' => 2,
                '\\' => 2,
            )
        );
            
        /**
        * @desc get the lenght of the text
        * @param string
        * @param mixed
        * @param float
        */
        public static function getTextLength ($font, $text, $ratio = 1) {
            
            if (!isset(self::$allowedFonts[$font])) {
                
                return -1; //throw ex ?
            }
            
            $fontLetterLength = self::$letterLength[$font];
            
            $subject = array();
            if (is_array($text))
                $subject = $text;
            else
                $subject[] = $text;
            
            $maxLenght = 0;
            foreach ($subject as $text) {
                $i = strlen($text);
                
                $lenght = 0;                
                $j = 0;
                for ($j; $j < $i; $j++) {
                    
                    if (isset($fontLetterLength[$text[$j]])) {
                        
                        $lenght += $fontLetterLength[$text[$j]];
                    } else {
                        
                        // count an unknown character
                        $lenght += self::GENERIC_WIDTH;
                    }
                }
                
                if ($lenght > $maxLenght)
                    $maxLenght = $lenght;
            }
            
            $maxLenght *= $ratio;
            return $maxLenght;
        }
        
        /**
        * @desc 
        * @param string
        * @param mixed
        * @param int
        * @param string
        * @param float the target ratio to apply to length calculated
        */
        public static function wrapText ($font, $text, $maxLength, $wrapOn, $ratio = 1) {
            
            $delimiterLength = self::getTextLength($font, $wrapOn, $ratio);
            $lines = array();
            
            if (is_array($text)) {
                
                foreach ($text as $line) {
                    
                    $lines[] = explode($wrapOn, $line);
                }
            } else {
                
                // split the string to words by delimiter
                $lines[] = explode($wrapOn, $text);
            }
            // initialize result set, length of one line phrase, one line phrase string
            $result = array();
            $totalLength = 0;
            $tempRowString = '';
            
            foreach ($lines as $portions) {
                foreach ($portions as $portion) {
                    
                    // get each portion length (+ delimiter length)
                    $portionLength = self::getTextLength($font, $portion, $ratio) + $delimiterLength;
                    
                    if (($totalLength + $portionLength) > $maxLength) {
                        
                        // the next portion exceeds available line length, add current line to result set, initialize new line
                        $result[] = $tempRowString;
                        $totalLength = $portionLength;
                        $tempRowString = $portion . $wrapOn;
                    } else {
                        //  build current line text and length
                        $totalLength += $portionLength;
                        $tempRowString .= $portion . $wrapOn;
                    }
                }

                // after the loop there will always be a line that hasn't finished (like the 1 line), or just started , when previous line 
                // exceeded
                $result[] = $tempRowString;
                $totalLength = 0;
                $tempRowString = '';
            }
            
            return $result;
        }
    }