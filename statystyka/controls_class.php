<?php
	require("../base_class.php");
	require("../interfaces.php");
	
	class MultipleChoiceList extends BaseForm implements IGetData  
    {
    	private $dataSet;
    	private $actionButtonName;
    	private $actionButtonValue;
    	
    	public function __construct($name)
    	{
    		$this->SetName($name);
    	}
    	
    	public function SetActionButtonName($name)
    	{
    		$this->actionButtonName = $name;
    	}
    	
    	public function SetActionButtonValue($value)
    	{
    		$this->actionButtonValue = $value;
    	}
    	
    	public function SetDataSource($dataSet)
    	{
    		$this->dataSet = $dataSet;
    	}
    	
    	public function ShowList()
    	{
    		$data = $this->dataSet->GetData();
    			
    		echo('<form method = \'POST\' action = \''.$_SERVER['PHP_SELF'].'\'>');
    		echo('<table>');
			
    		for ($i = 0; $i < count($data); $i++)
			{
				$this->CreateItem($data[$i]);	    		
			}
			echo('<tr>');
			echo('<td colspan = \'2\'><input type = \'submit\' name = '.$this->actionButtonName.' value = '.$this->actionButtonValue.' /></td>');
			echo('</tr>');
			echo('</table>');
    		echo('</form>');
    	}
    	
    	public function GetData()
    	{
    		$result = array();
    		
    		if (isset($_POST[$this->actionButtonName]))
    		{
    			foreach ($_POST as $key => $value)
    			{
    				if ($key != $this->actionButtonName)
    				{
    					$tmp = array("id" => $key, "value" => $value);
    					$result[] = $tmp;			
    				}
    			}
    			
    			return $result;
    		}
    		else
    		{
    			return false;
    		}
    	}
    	
    	private function CreateItem($item)
    	{   
    		$checked = isset($_POST[$item['id']]);
    		if ($checked)
    		{
    			echo('<tr><td><input type = \'checkbox\' name = \''.$item['id'].'\' value = \''.$item['value'].'\' checked = \''.$checked.'\' /></td><td>'.$item['value'].'</td></tr>');
    		}
    		else
    		{
    			echo('<tr><td><input type = \'checkbox\' name = \''.$item['id'].'\' value = \''.$item['value'].'\' /></td><td>'.$item['value'].'</td></tr>');
    		}
    	}
    }
?>