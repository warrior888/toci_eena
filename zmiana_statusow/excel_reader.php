<?php
	require("../wsparcie/Spreadsheet/Excel/reader.php");
	require("../dal.php");
	require("../statystyka/grid_class.php");
    require("data.php");
	
    class ExcelReader
    {
    	private $xlsObj;
        private $statusArray;

    	public function __construct($fileName)
    	{
    		$this->xlsObj = new Spreadsheet_Excel_Reader();
    		$this->xlsObj->setRowColOffset(0);
    		$this->xlsObj->read($fileName);
    	}
    	
    	public function GetData()
    	{
            $this->statusArray = new StatusChangeDataArray();
            
            for ($i = 1; $i < $this->xlsObj->sheets[0]['numRows']; $i++) 
			{
				$this->statusArray->AddStatusObj($this->CreateStatusObj($i));
			}
    		
    		return $this->statusArray;			 
    	}
        
        private function CreateStatusObj($rowNumber)
    	{
    		$status = new StatusChangeData();
            
			$status->SetSofi($this->xlsObj->sheets[0]['cells'][$rowNumber][0]);
            $status->SetKlient($this->xlsObj->sheets[0]['cells'][$rowNumber][1]);
            $status->SetImieNazwisko($this->xlsObj->sheets[0]['cells'][$rowNumber][2]);
            $status->SetDataUrodzenia($this->xlsObj->sheets[0]['cells'][$rowNumber][3]);
            
            return $status;
    	}
    	
    	private function CreateHeader()
    	{	
    		$header = array();
    		
    		for ($j = 0; $j < $this->xlsObj->sheets[0]['numCols']; $j++) 
			{
				$header[] = $this->xlsObj->sheets[0]['cells'][0][$j];
			}
			
			return $header;
    	}
    }
?>