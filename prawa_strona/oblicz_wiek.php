<?php
    
	function oblicz_wiek($rok, $miesiac, $dzien)
	{
		$rok1 = date("Y");
		$miesiac1 = date("m");
		$dzien1 = date("d");
		$t = (int)$rok1 - (int)$rok;
		If ((int)$miesiac1 < (int)$miesiac)
    			$t -= 1;
    		If ((int)$miesiac1 == (int)$miesiac)
		{
        		If ((int)$dzien1 < (int)$dzien)
			{
				$t -= 1;
			}
		}		
		return $t;
	}
?>