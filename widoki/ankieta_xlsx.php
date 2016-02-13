<?php

    require_once '../conf.php';
    require_once '../bll/cv.php';
    require '../common/PHPExcel-master/Classes/PHPExcel.php';
    require_once '../bll/BLLDaneOsobowe.php';
    
    class AnkietaXlsx {
    
        private static $naglowki = array(
            'Name' => 'imie', 
            'Surname' => 'nazwisko', 
            'Gender' => 'plec', 
            'Birth date' => 'data_urodzenia',
            'Birth place' => 'msc_ur', 
            'Street' => 'ulica', 
            'Postal code' => 'kod', 
            'Place' => 'msc', 
            'E-mail' => 'email', 
            'Passport number' => 'paszport', 
            'Expiry date' => 'data_waznosci', 
            'Soffinumer' => 'sofi', 
            'Account number' => 'konto', 
            'Swift' => 'swift', 
            'Vestiging' => 'biuro_id',
            'Account number last name' => 'nazwisko', 
            'Nationality' => 'nationality',
            'Land' => 'land',
            'Voorletter' => 'initial',
            'Datumloonbelasting' => 'data',
            'Mobielnummer' => 'tel_kom',
            'Heffingskorting' => 'Heffingskorting'
        );
        
        /**
         * 
         * @param array $ids
         * @param bool $forceSend - send also users who have isAbfahrtSent to true
         * @return string
         */
        public static function Create(array $ids, $forceSend)
        {
            // Create new PHPExcel object
            $objPHPExcel = new PHPExcel();
            $objPHPExcel = self::setXslxHeader($objPHPExcel);
            
            $cvDataLogic = new CvDataLogic();

            $row = 2;
            
            foreach ($ids as $id) {
                $dbData = $cvDataLogic->getUserData($id);
                if($forceSend == false && $dbData['isabfahrtsent'] == 't') {
                    continue; 
                }
                $data = self::GenerateData($dbData);
                $col = 'A';
                
                foreach ($data as $value)
                {            
                    $objPHPExcel->setActiveSheetIndex(0)
                            ->setCellValueExplicit($col.$row, iconv("ISO-8859-2", "UTF-8", $value[1]), PHPExcel_Cell_DataType::TYPE_STRING2);
                    $objPHPExcel->getActiveSheet()->getStyle($col.$row)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                    $objPHPExcel->getActiveSheet()->getRowDimension($row)->setRowHeight(-1);
                    $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
                    //$objPHPExcel->getActiveSheet()->getStyle($col.$row)->getAlignment()->setWrapText(true);
                    $col++;
                }
                $row++;
            }
            // Save Excel 2007 file
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');

            $dir = FileManager::getDocsPath();

            if (!file_exists($dir)) {
                mkdir($dir, 0775, true);
            }
            
            $path = $dir. "/ankieta.xlsx";
            $objWriter->save($path);
            
            return $path;
        }

        protected static function setXslxHeader ($objPHPExcel) {
            $sheet = $objPHPExcel->setActiveSheetIndex(0);
            
            $col = 'A';
            
            foreach (array_keys(self::$naglowki) as $n) {
                $sheet->setCellValue( $col.'1', $n);
                $sheet->getStyle($col.'1')->getFont()->setBold(true);
                $col++;
            }
            
            return $objPHPExcel;
        }

        protected static function GenerateData($dane)  {
            $result = array();

            foreach (self::$naglowki as $text => $naglowek) {

                $customDelimiter = '';
                if(isset($dane[$naglowek]) && is_array($dane[$naglowek])) {

                    $info = array();

                    foreach ($dane[$naglowek] as $dana) {
                        if (isset($dana['nazwa'])) {
                            $info[] = $dana['nazwa'];
                        } else {
                            $info[] = $dana['jezyk'].' - '.$dana['poziom'];
                        }
                    }

                    if ($naglowek != 'poprzedni_pracodawca') {

                        $customDelimiter = ', ';
                        $dane[$naglowek] = implode(', ', $info);
                    } else {
                        $dane[$naglowek] = implode("\n", $info);
                    }
                }

                if(isset($dane[$naglowek])) {
                    $fixedValue = self::FixValue($naglowek, $dane[$naglowek]);
                    $result[] = array($text, CommonUtils::removePolishCharacters($fixedValue));
                }
                else {
                    if($naglowek == 'nationality') {
                        $result[] = array($text, "Poolse");
                    }
                    elseif($naglowek == 'land') {
                        $result[] = array($text, "Polen");
                    }
                    elseif($naglowek == 'sofi') {
                        $result[] = array($text, '');
                    }
                    elseif($naglowek == 'Heffingskorting') {
                        $result[] = array($text, 'J');
                    }
                    else {
                        $result[] = array($text, '');
                    }
                }
            }
            return $result;
        }

        protected static function FixValue($type, $value) {

            $biuroToFlexId = array(
                1 => '81',
                2 => '85',
                3 => '83',
                4 => '105',
                5 => '137',
                6 => '114',
                7 => '115',
                8 => '112',
                9 => '114',
                10 => '120',
                11 => '114',
                12 => '',
                13 => '',
                14 => '',
                15 => '84',
                16 => '78',
                17 => '96',
                18 => '',
                22 => '85'
            );

            if($type == 'plec') {
                if($value == "Mê¿czyzna") {
                    return "M";
                }
                else {
                    return "V";
                }
            }
            elseif ($type == 'data_urodzenia' || $type == 'data_waznosci' || $type == 'data_wyjazdu')
            {
                return date("Y-m-d", strtotime($value));
            }
            elseif($type == 'biuro_id')
            {
                return $biuroToFlexId[$value];
            }
            elseif($type == "sofi" || $type == "konto")
            {
                if($value == '-') {
                    return "";
                }
            }
            elseif($type == "bank")
            {
                if($value == '-') {
                    return "";
                }
            }
            elseif ($type == 'tel_kom') {
                return "+48" . $value;
            }
            return $value;
        }
        
        public static function SetStatusToSend(array $userIds) {
            if(! $userIds) {
                return;
            }
            
            $bllDaneOsobowe = new BLLDaneOsobowe();
            
            foreach ($userIds as $uid) {
                $bllDaneOsobowe->SetAbfahrtSent($uid);
            }
        }
    }