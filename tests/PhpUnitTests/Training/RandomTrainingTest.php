<?php

	set_include_path(get_include_path() . PATH_SEPARATOR . 'C:\self\toci\software\bitbucket' . PATH_SEPARATOR . 'C:\self\toci\software\bitbucket\bll'
	. PATH_SEPARATOR . 'C:\self\toci\software\bitbucket\dal'
	. PATH_SEPARATOR . 'C:\self\toci\software\bitbucket\wsparcie');

	require_once 'c:\xampp\php\phpunit.phar';
	require_once 'C:\self\toci\software\bitbucket\dal\DALDokumenty.php';
	
	class RandomTrainingTest extends PHPUnit_Framework_TestCase
	{
		private $documentsDal;
		private $userId = 9466;
		
		public function setUp()
		{
			//var_dump('czy mam racje');
			$this->documentsDal = new DALDokumenty();
		}
		
		public function cleanUp() // ??
		{
			//var_dump('dfsafasdfdas');
			$this->documentsDal->delete($this->userId);
		}
		
		public function testwhatever()
		{
			//$this->
			//$docs = new DALDokumenty();
			
			$passNumer = rand(1234, 4567);
			
			$this->documentsDal->set($this->userId, $passNumer, date('Y-m-d'), '245235436', 1, '1010204732895748956347863');
			
			$result = $this->documentsDal->get($this->userId);

			$this->assertEquals($result[Model::RESULT_FIELD_DATA][0]['pass_nr'], $passNumer); // expected ? actual?
			$this->assertEquals($result[Model::RESULT_FIELD_ROWS_COUNT], 1); // 1 w const
			
			//$this->cleanUp();
		}
	}