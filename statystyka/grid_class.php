<?php
	class SimpleGridView
    {
    	private $dataSet;
    	private $tableStyle = "";
    	private $trStyle = "";
    	private $tdStyle = "";
    	private $subTableStyle = "";
    	private $headerStyle = "";
    	private $counterStyle = "";
    	private $isVisibleIdColumn = true;
    	private $count = 1;
    	private $emptyString = "";
    	
    	public function SetTableStyle($style)
    	{
    		$this->tableStyle = $style;
    	}
    	
    	public function SetSubTableStyle($style)
    	{
    		$this->subTableStyle = $style;
    	}
    	
    	public function SetTrStyle($style)
    	{
    		$this->trStyle = $style;
    	}
    	
    	public function SetTdStyle($style)
    	{
    		$this->tdStyle = $style;
    	}
    	
    	public function SetHeaderStyle($style)
    	{
    		$this->headerStyle = $style;
    	}
    	
    	public function SetCounterStyle($style)
    	{
    		$this->counterStyle = $style;	
    	}
    	
    	public function SetDataSource($dataSet)
    	{
    		$this->dataSet = $dataSet;
    	}
    	
    	public function SetIdColumnVisible($bool)
    	{
    		$this->isVisibleIdColumn = $bool;
       	}

       	public function SetEmptyString($string)
       	{
       		$this->emptyString = $string;
       	}
       	
    	public function DataBind($drawByHeaders = false)
    	{    		
            if (true === $drawByHeaders)
                echo $this->CreateTableByHeaders();
            else
    		    echo $this->CreateTable($this->dataSet->GetData());
    	}
    	
        public function CreateTableByHeaders()
        {
            $result = '<table '.$this->tableStyle.'>';
            
            $headers = $this->dataSet->GetHeaders();
            $data = $this->dataSet->GetData();
            $result .= $this->CreateTableRow($headers, "header", "CreateTable");
            
            foreach ($data as $key => $value)
            {                                   
                $result .= $this->CreateTableRowByHeaders($value, "data", "CreateTable", $headers);
            }
            
            $result .= '</table>';
            
            return $result;
        }
        
    	public function CreateTable($data)
    	{
    		$dataCount = 0;
    		
    		if (count($data) > 0)
    		{
    			$dataCount = count($data);
    		}
    		
    		$result = '<table '.$this->tableStyle.'>';
    		$result .= $this->CreateTableRow($this->dataSet->GetHeaders(), "header", "CreateTable");
    		
    		if (is_array($data))
    		{
	    		foreach ($data as $key => $value)
	    		{
	    			$result .= $this->CreateTableRow($value, "data", "CreateTable");
	    		}
    		}
    		else
    		{
    			$result .= $this->CreateTableRow($data, "data", "CreateTable");
    		}
    		
            $result .= '</table>';
            
            return $result;
    	}
    	
    	public function CreateSubTable($data)
    	{
    		$result = '<table '.$this->subTableStyle.'>';
            $result .= $this->CreateTableRow($this->dataSet->GetSubHeaders(), "header", "CreateSubTable");
    		if (is_array($data))
    		{
	    		foreach ($data as $key => $value)
	    		{
	    			$result .= $this->CreateTableRowByHeaders($value, "data", "CreateSubTable", $this->dataSet->GetSubHeaders());
	    		}
    		}
    		else
    		{
    			$result .= $this->CreateTableRow($data, "data", "CreateSubTable");
    		}
    		$result .= '</table>';
            
            return $result;
    	}
    	
    	public function CreateTableRow($data, $type, $sender)
    	{
            $css = (($this->count % 2) == 0) ? 'oddRow' : 'evenRow'; 
    		$result = '<tr '.$this->trStyle.' class="'.$css.'">';    		
    		
    		if ($sender == "CreateTable")
    		{    		
	    		if ($this->isVisibleIdColumn)
	    		{
	    			if ($type == "header")
	    			{
	    				$result .= '<th '.$this->tdStyle.'>Lp.</th>';
	    			}
	    			else if ($type == "data")
	    			{
	    				$result .= '<td '.$this->tdStyle.'>'.$this->count.'</td>';
	    				$this->count++;
	    			}
	    		}
    		}
    		if (is_array($data))
    		{
				foreach ($data as $key => $value)
	    		{
		    		if (is_array($value))
		    		{
		    			$result .= '<td '.$this->tdStyle.'>';
		    			$result .= $this->CreateSubTable($value);
		    			$result .= '</td>';
		    		}
		    		else
		    		{
		    			$result .= $this->CreateTableCell($value, $type);
		    		}
	    		}
    		}
    		else
    		{
    			$result .= $this->CreateTableCell($data, $type);
    		}
            
    		$result .= '</tr>';
            
            return $result;
    	}
        
        public function CreateTableRowByHeaders($data, $type, $sender, $headers)
        {
            $css = (($this->count % 2) == 0) ? 'oddRow' : 'evenRow'; 
            $result = '<tr '.$this->trStyle.' class="'.$css.'">';            
            
            if ($sender == "CreateTable")
            {            
                if ($this->isVisibleIdColumn)
                {
                    if ($type == "header")
                    {
                        $result .= '<th '.$this->tdStyle.'>Lp.</th>';
                    }
                    else if ($type == "data")
                    {
                        $result .= '<td '.$this->tdStyle.'>'.$this->count.'</td>';
                        $this->count++;
                    }
                }
            }
            if (is_array($data))
            {
                foreach ($headers as $key => $hValue)
                {
                    $value = isset($data[$key]) ? $data[$key] : '';
                    if (is_array($value))
                    {
                        $result .= '<td '.$this->tdStyle.'>';
                        $result .= $this->CreateSubTable($value);
                        $result .= '</td>';
                    }
                    else
                    {
                        $result .= $this->CreateTableCell($value, $type);
                    }
                }
            }
            else
            {
                $result .= $this->CreateTableCell($data, $type);
            }
            
            $result .= '</tr>';
            
            return $result;
        }
    	
    	public function CreateTableCell($data, $type = 'row')
    	{
            $td = ($type == 'header') ? 'th' : 'td';
    		if ($data == "")
    		{
    			return ('<'.$td.' '.$this->tdStyle.'>'.$this->emptyString.'</'.$td.'>');
    		}

    		return ('<'.$td.' '.$this->tdStyle.'>'.$data.'</'.$td.'>');
    	}
    	
    	private function CreateCounter($colCount, $dataCount)
    	{
    		if ($this->isVisibleIdColumn)
    		{
    			$colCount++;
    		}
    		
            //zostawiam ze wzgledu na colspan wyliczony zeby pamietac ze jest jakby byl potrzebny
    		//echo('<tr '.$this->headerStyle.'><td '.$this->headerStyle.' colspan = '.$colCount.'>Ilo¶æ rekordów znalezionych: '.$dateCount.'</td></tr>');
            echo valControl::_RowsCount($dataCount);
    	}
    }
?>