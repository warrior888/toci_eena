<?php
	abstract class BaseForm
	{
		protected  $name;
		
		public function SetName($name)
		{
			$this->name = $name;
		}
		
		public function GetName()
		{
			return $this->name;	
		}
	}
?>