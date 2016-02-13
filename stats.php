<?php

require_once 'conf.php';
require_once 'adl/Person.php';
require_once 'dal/DALZatrudnienie.php';
require 'common/PHPExcel-master/Classes/PHPExcel.php';

class StatsView extends View {

    const FORM_GENERATE_STATS_BUTTON = 'stats';
    const FORM_START_DATE            = 'start_date';
    const FORM_END_DATE              = 'end_date';
    
    /** 
     *
     * @var type @var DALZatrudnienie
     */
    private $dal;


    public static function instantiate () {
        return new StatsView();
    }

    public function __construct () {
        $this->actionList = array(
                self::FORM_GENERATE_STATS_BUTTON => User::PRIV_AKTYWNY
        );
        
        parent::__construct();
        $this->dal = new DALZatrudnienie();
    }

    public function run() {
        if(isset($_POST[self::FORM_GENERATE_STATS_BUTTON])) {
            $startDate = '';
            $endDate = '';
            
            if(isset($_POST[self::FORM_START_DATE])) {
                $startDate = strtotime($_POST[self::FORM_START_DATE]);
                $startDate = $startDate ? date('Y-m-d', $startDate) : '';
            }
            if(isset($_POST[self::FORM_END_DATE])) {
                $endDate = strtotime($_POST[self::FORM_END_DATE]);
                $endDate = $endDate ? date('Y-m-d', $endDate) : '';
            }    
            $type = $_POST['type'];
            if($type == 'company') {
                $this->generateXlsCompany($startDate, $endDate);
                
                //Html generation
                //$result = $this->generateDataCompany($startDate, $endDate);
            } else {
                $this->generateXlsPerson($startDate, $endDate);
                
                //Html generation
                //$result = $this->generateDataPerson($startDate, $endDate);
            }
        } else {
            $result = $this->renderAddForm();
        }
        
        echo $result;
    }
    
    protected function renderAddForm() {
            $result = "";
            
            $result .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
            $result .= '<table>';
            $result .= '<tr><td>Typ:</td><td><input type="radio" name="type" value="company" checked>podmioty </td></tr>';
            $result .= '<tr><td></td><td><input type="radio" name="type" value="person">osoby </td></tr>';
            $result .= '<tr><td>Data pocz±tkowa:</td><td>' . $this->htmlControls->_AddDatebox(self::FORM_START_DATE, self::FORM_START_DATE, '', 10, 10) . '</td></tr>';
            $result .= '<tr><td>Data koñcowa:</td><td>' . $this->htmlControls->_AddDatebox(self::FORM_END_DATE, self::FORM_END_DATE, '', 10, 10) . '</td></tr>';
            $result .= '<tr><td></td><td>' . $this->htmlControls->_AddSubmit(User::PRIV_AKTYWNY, self::FORM_GENERATE_STATS_BUTTON, self::FORM_GENERATE_STATS_BUTTON, 'Generuj', '', '') .'<td/></tr>';
            $result .= '</table>';
            $result .= $this->addFormSuf();
            
            return $result;
    }
    
    protected function generateXlsCompany($startDate, $endDate) {
        $data = $this->dal->getStatsCompanies($startDate, $endDate);
        $objPHPExcel = new PHPExcel();
        $this->setXlsxHeaders($objPHPExcel->setActiveSheetIndex(0), array('Lp.', 'Podmiot'));
        
        $rowNo = 2;
        if($data) {
            foreach ($data[Model::RESULT_FIELD_DATA] as $row) {                
                $objPHPExcel->setActiveSheetIndex(0)
                                ->setCellValueExplicit("B".$rowNo, iconv("ISO-8859-2", "UTF-8", $row['nazwa']), PHPExcel_Cell_DataType::TYPE_STRING2)
                                ->setCellValue("A".$rowNo, $rowNo-1);
                $objPHPExcel->getActiveSheet()->getStyle("B".$rowNo)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $objPHPExcel->getActiveSheet()->getRowDimension($rowNo)->setRowHeight(-1);
                $objPHPExcel->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
                $rowNo++;
            }
        }
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('tmp/stats.xlsx');
        $this->returnXlsxHeaders();
        readfile('tmp/stats.xlsx');
        unlink('tmp/stats.xlsx');
        exit;
    }
    
    protected function setXlsxHeaders($sheet, $headers) {
        $headerCol = 'A';
        
        foreach ($headers as $n) {
            $sheet->setCellValue( $headerCol.'1', $n);
            $sheet->getStyle($headerCol.'1')->getFont()->setBold(true);
            $headerCol++;
        }
    }

    protected function returnXlsxHeaders() {
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="stats.xlsx"');
        header('Cache-Control: max-age=0');
        // If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0
    }

    protected function generateXlsPerson($startDate, $endDate) {
        $data = $this->dal->getStatsPersons($startDate, $endDate);
        
        $objPHPExcel = new PHPExcel();
        $this->setXlsxHeaders($objPHPExcel->setActiveSheetIndex(0), array('Lp.', 'Osoba', 'Podmiot', 'Data wyjazdu', 'Data powrotu'));
        
        $rowNo = 2;
        if($data) {
            foreach ($data[Model::RESULT_FIELD_DATA] as $row) {                
                $objPHPExcel->setActiveSheetIndex(0)
                                ->setCellValue("A".$rowNo, $rowNo-1)
                                ->setCellValueExplicit("B".$rowNo, iconv("ISO-8859-2", "UTF-8", $row['osoba']), PHPExcel_Cell_DataType::TYPE_STRING2)
                                ->setCellValueExplicit("C".$rowNo, iconv("ISO-8859-2", "UTF-8", $row['podmiot']), PHPExcel_Cell_DataType::TYPE_STRING2)
                                ->setCellValueExplicit("D".$rowNo, iconv("ISO-8859-2", "UTF-8", $row['data_wyjazdu']), PHPExcel_Cell_DataType::TYPE_STRING2)
                                ->setCellValueExplicit("E".$rowNo, iconv("ISO-8859-2", "UTF-8", $row['data_powrotu']), PHPExcel_Cell_DataType::TYPE_STRING2);

                $objPHPExcel->getActiveSheet()->getStyle("B".$rowNo)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $objPHPExcel->getActiveSheet()->getStyle("C".$rowNo)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $objPHPExcel->getActiveSheet()->getStyle("D".$rowNo)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $objPHPExcel->getActiveSheet()->getStyle("E".$rowNo)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                $objPHPExcel->getActiveSheet()->getRowDimension($rowNo)->setRowHeight(-1);
                $rowNo++;
            }
            
            $objPHPExcel->getActiveSheet()->getColumnDimension("B")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension("C")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension("D")->setAutoSize(true);
            $objPHPExcel->getActiveSheet()->getColumnDimension("E")->setAutoSize(true);   
        }
        $this->returnXlsxHeaders();
        
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('tmp/stats.xlsx');
        $this->returnXlsxHeaders();
        readfile('tmp/stats.xlsx');
        unlink('tmp/stats.xlsx');
        exit;
    }

    protected function generateDataCompany($startDate, $endDate)
    {
        $data = $this->dal->getStatsCompanies($startDate, $endDate);
        
        $table = new HtmlTable();
        $table->setHeader(array('Lp.', 'Nazwa'));
        if ($data) {
            $lp = 1;
            foreach ($data[Model::RESULT_FIELD_DATA] as $row) {
                $table->addRow(array($lp,
                                $row['nazwa']
                                ), null, "");
                $lp++;
            }
            $result = $table->__toString();
        } else {
            $result = "Brak wyników";
        }

        return $result;
    }
    
    protected function generateDataPerson($startDate, $endDate)
    {
        $data = $this->dal->getStatsPersons($startDate, $endDate);
        
        $table = new HtmlTable();
        $table->setHeader(array('Lp.', 'Osoba', 'Podmiot', 'Data wyjazdu', 'Data powrotu'));
        if ($data) {
            $lp = 1;
            foreach ($data[Model::RESULT_FIELD_DATA] as $row) {
                $table->addRow(array($lp,
                                $row['osoba'],
                                $row['podmiot'],
                                $row['data_wyjazdu'],
                                $row['data_powrotu'],
                                ), null, "");
                $lp++;
            }
            $result = $table->__toString();
        } else {
            $result = "Brak wyników";
        }

        return $result;
    }
}


$output = StatsView::instantiate();
$output->execute();