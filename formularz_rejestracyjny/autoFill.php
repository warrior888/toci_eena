<?php
	require("xajax/xajax.inc.php");
	
	function AutoFill($idTextBox, $idDiv, $count = 10)
	{		
		$selectId = "selectFor".$idTextBox;
		$action = "\"document.getElementById('".$idTextBox."').value = this.options[this.selectedIndex].value; document.getElementById('".$selectId."').style.display = 'none';\"";
		$result = array();
		$result[] = "<option name = 'o1' value = 'Opti±n1'>Option1</option>";
		$result[] = "<option name = 'o2' value = 'Option2'>Option2</option>";
		$result[] = "<option name = 'o3' value = 'Option3'>Option3</option>";
		$result[] = "<option name = 'o4' value = 'Option4'>Option4</option>";
		$result[] = "<option name = 'o5' value = 'Option5'>Option5</option>";
		$result[] = "<option name = 'o6' value = 'Option6'>Option6</option>";
		$result[] = "<option name = 'o7' value = 'Option7'>Option7</option>";
		$result[] = "<option name = 'o8' value = 'Option8'>Option8</option>";
		$result[] = "<option name = 'o9' value = 'Option9'>Option9</option>";
		$result[] = "<option name = 'o10' value = 'Option10'>Option10</option>";
		
		$dataToView = array_slice($result, 0, $count);
		
		$data = zamien_na_ascii("<select id = '".$selectId."' size = '".count($dataToView)."' onClick = ".$action.">".implode($dataToView)."</select>");
		
		
		$objResponse = new xajaxResponse();
	    $objResponse->addAssign($idDiv,"innerHTML",$data);
	    
		return $objResponse;
	}
    
    function zamien_na_ascii($msc)
    {
        $result = "";
        for ($i = 0; $i < strlen($msc); $i++)
        {
            $result .= ord($msc[$i])."|";
        }
        $result = substr($result, 0, strlen($result) - 1);
        return $result;
    }
	
	$xajax = new xajax(); 
    $xajax->setCharEncoding('iso-8859-2'); 
    $xajax->registerFunction('AutoFill');
    $xajax->processRequests();
?>
<html>
<head>
	<?php $xajax->printJavascript('xajax//');?>
</head>
<body>
<input type = "text" id = "t1" onkeypress = "xajax_AutoFill(t1, d1);" />
<div id = "d1">
</div>
</body>
</html>