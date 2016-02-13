<?php
//ini_set ('display_errors', 1);
//ini_set ('error_reporting', E_ALL);

	require("Spreadsheet/Excel/Writer.php");
	require("Spreadsheet/Excel/reader.php");
	//require("dal.php");
	//require("grid_class.php");
	//require("controls_class.php");
	
	class DataToExcel
    {
        private $dataSet;
        private $sheet;
        private $setByHeaders;
        private $mult = 1.4;
        
        public function __construct($dataSet, $sheet, $setByHeaders = false)
        {
        	$this->dataSet = $dataSet;
        	$this->sheet = $sheet;
            $this->setByHeaders = $setByHeaders;
        	$this->CreateExcelSheet();
        }
        
        private function CreateExcelSheet()
        {
            $headers = $this->dataSet->GetHeaders();
        	$subHeaders = $this->dataSet->GetSubHeaders();
            
            if ($subHeaders)
            {
                // remove the last headers, as it is header over set of headers
                array_pop($headers);
                // in case there are subheaders add them to the end of headers list
                foreach ($subHeaders as $subHeader)
                {
                    $headers[] = $subHeader;
                }
            }
            
            $data = $this->dataSet->GetData();
            $columns = array();
            
            //inicjacja szerokosci kolumn
            for ($i = 0; $i < count($headers); $i++)
            {
            	$columns[$i] = 1;
            }
            
            //generowanie naglowkow
            $j = 0;
            foreach ($headers as $key => $value) 
            {
            	$this->sheet->write(0, $j, $value);
            	if (strlen($value) > $columns[$j])
            	{
            		$columns[$j] = strlen($value) * $this->mult;
            	}
            	$j++;
            }
            
            $i = 0;
            //uzupelnianie arkusza danymi
            if ($this->setByHeaders)
                $this->populateByHeaders($i, 0, $data, $headers, $columns);
            else
                $this->populate($i, 0, $data, $columns);
            
            //ustawianie szerokosci kolumn
        	for ($i = 0; $i < count($headers); $i++)
            {
            	$this->sheet->setColumn($i, $i, $columns[$i], 0, 0, 0);
            }
        }
        
        private function populate ($i, $j, $data, &$columns)
        {
            $basicJ = $j;
            foreach ($data as $key => $value) 
            {
                $j = $basicJ;
                foreach ($value as $keyData => $valueData) 
                {
                    if (is_array($valueData))
                    {
                        // TODO depth of nesting analysis ?
                        $i = $this->populate($i, $j, $valueData, $columns);
                    }
                    else
                    {
                        $this->sheet->write($i + 1, $j, $valueData);
                        if (strlen($valueData) > $columns[$j])
                        {
                            $columns[$j] = strlen($valueData) * $this->mult;
                        }
                        $j++;    
                    }
                }
                $i++;
            }
            
            return $i;
        }
        
        private function populateByHeaders ($i, $j, $data, $headers, &$columns)
        {
            $basicJ = $j;
            foreach ($data as $key => $value) 
            {
                $j = $basicJ;
                foreach ($headers as $keyHeader => $valueHeader) 
                {       
                    $valueData = isset($value[$keyHeader]) ? $value[$keyHeader] : '';
                    
                    if (is_array($valueData))
                    {
                        // TODO depth of nesting analysis ?
                        $i = $this->populateByHeaders($i, $j, $valueData, $this->dataSet->GetSubHeaders(), $columns);
                    }
                    else
                    { 
                        $this->sheet->write($i + 1, $j, $valueData);
                        if (strlen($valueData) > $columns[$j])
                        {
                            $columns[$j] = strlen($valueData) * $this->mult;
                        }
                        $j++;    
                    }
                }
                $i++;
            }
            
            return $i;
        }
    }
    
    class MergeSheetsToXls
    {
        private $xls;
        private $xlsName;
        private $dataToExcelObj;
        
        public function  __construct($xlsName)
        {
        	$this->xls = new Spreadsheet_Excel_Writer($xlsName);
        }
        
        public function SaveToDisc()
        {                         
        	$result = $this->xls->Close();
            if ($result instanceof PEAR_Error)
                die('B³±d zapisu xls: '.$result->getMessage());
        }
        
        public function AddSheet($sheetName, $dataSet, $setByHeaders = false)
        {
            //Spreadsheet_Excel_Writer_Worksheet
            $sheet = $this->xls->addWorksheet($sheetName);
            if (!($sheet instanceof Spreadsheet_Excel_Writer_Worksheet)) {
                //var_dump($sheet);
                die('B³±…d tworzenia arkusza: '.$sheet->getMessage());
            }
        	$this->dataToExcelObj = new DataToExcel($dataSet, $sheet, $setByHeaders);
            //add a shhet to xls
        }
    }
    
    /*
    $dataSet = new DataSet();
    $hdr = array("Id", "Imie", "Nazwisko", "Data urodzenia");
    //$data = array(array("1", "Mateusz", "Gallus", "1986-05-01"), array("2", "Justyna", "Gallus", "1986-06-23"), array("2", "Justyna", "Gallus", "1986-06-23"));
    $data = array(array("id" => "1", "value" => "Mateusz Gallus"), array("id" => "2", "value" => "Justyna Gallus"));
    $dataSet->SetHeaders($hdr);
    $dataSet->SetData($data);
    
    $mclObj = new MultipleChoiceList('list1');
    $mclObj->SetDataSource($dataSet);
    $mclObj->SetActionButtonName('Go');
    $mclObj->SetActionButtonValue('DoAction');
    $mclObj->ShowList();
    
    $hdr = array("Id", "Imie i nazwisko");
    $dataSet->SetHeaders($hdr);
    $dataSet->SetData($mclObj->GetData());
    
    if ($mclObj->GetData())
    {
    	$gridObj = new SimpleGridView();
	    $gridObj->SetTableStyle('border = \'1\' align = \'CENTER\'');
	    $gridObj->SetTrStyle('');
	    $gridObj->SetTdStyle('nowrap align = \'CENTER\'');
	    $gridObj->SetEmptyString("--------");
	    $gridObj->SetIdColumnVisible(false);
	    $gridObj->SetDataSource($dataSet);
	    $gridObj->DataBind();
	    
	    $obj = new MergeSheetsToXls('test.xls');
		$obj->AddSheet('testSheet', $dataSet);
		$obj->AddSheet('testSheet1', $dataSet);
		$obj->AddSheet('testSheet2', $dataSet);
    }
	*/
?>