<?php

    // intention is to replace former pdf lib with this one
    // this should however be created on a strategy pattern basis, but we do not have a time for that
    // as well as we would probably not benefit from such architecture that much
    
    //set_include_path(get_include_path() . PATH_SEPARATOR . 'wsparcie/tcpdf');
    
    function own_iconv($src, $dest, $item) {
        //return $item;
        return iconv($src, $dest, $item);
    }

    require_once 'tcpdf/tcpdf.php';
    require_once 'bll/LogManager.php';
    
    class HtmlToPdfManager {
        
        protected $pdf;
        
        private $pageBeak = ':newPage';
        
        public function __construct() {
            
            $this->pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            
            $this->initializePdf();
        }
        
        public function setFont($family, $style, $size) {
            $this->pdf->SetFont($family, $style, $size);
        }

        public function setHtml($html, $shouldConvert = false) {
            
            $descriptionTable = explode($this->pageBeak, $html);
            
            $i = 0;
            foreach ($descriptionTable as $descriptionItem) {
                
                if ($i > 0) {
                    
                    $this->AddPage();
                }

                if ($shouldConvert) {
                    
                    $descriptionItem = iconv('ISO-8859-2', 'UTF-8', $descriptionItem);
                }

                LogManager::log(LOG_DEBUG, 'set some html: '.$descriptionItem);
                $this->pdf->writeHTML($descriptionItem, $ln=1, $fill=0, $reseth=true); 
                $i++;
            }
        }
        
        public function AddPage() {
            
            $this->pdf->AddPage();
        }
        
        public function OutputPdf($asAttachment = false) {
            
            // /var/www/html/eena/devel/umowa.pdf
            $str = $this->pdf->Output('', 'S'); // D F S
            
            header('Content-Type: application/pdf');
            
            if ($asAttachment) {
                
                header('Content-disposition: attachment; filename="'.time().'".pdf');
            }
            
            echo $str;
        }
        
        protected function initializePdf() {
            
            $this->pdf->SetCreator(PDF_CREATOR);
            //$this->pdf->SetAuthor('Nicola Asuni');
            //$this->pdf->SetTitle('TCPDF Example 001');
            //$this->pdf->SetSubject('TCPDF Tutorial');
            //$this->pdf->SetKeywords('TCPDF, PDF, example, test, guide');

            // set default header data
            //$this->pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 001', PDF_HEADER_STRING, array(0,64,255), array(0,64,128));
            
            $this->pdf->setPrintHeader(false);
            $this->pdf->setPrintFooter(false);
            
            //$this->pdf->setFooterData($tc=array(0,64,0), $lc=array(0,64,128));

            // set header and footer fonts
            //$this->pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            //$this->pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

            // set default monospaced font
            $this->pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

            //set margins
            //$this->pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
            //$this->pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
            //$this->pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

            //set auto page breaks
            $this->pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            //set image scale factor
            $this->pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

            //set some language-dependent strings
//            $this->pdf->setLanguageArray($l);

            // ---------------------------------------------------------

            // set default font subsetting mode
            $this->pdf->setFontSubsetting(true);

            // Set font
            // dejavusans is a UTF-8 Unicode font, if you only need to
            // print standard ASCII chars, you can use core fonts like
            // helvetica or times to reduce file size.
            $this->pdf->SetFont('dejavusans', '', 10, '', true);

            // This method (addpage) has several options, check the source code documentation for more information.

            // set text shadow effect
            //$this->pdf->setTextShadow(array('enabled'=>true, 'depth_w'=>0.2, 'depth_h'=>0.2, 'color'=>array(196,196,196), 'opacity'=>1, 'blend_mode'=>'Normal'));
            $this->pdf->AddPage();
        }
    }