<?php

require_once(__DIR__.'/../Interfaces/IDbDelete.php');
require_once(__DIR__.'/../Interfaces/IDbInsert.php');
require_once(__DIR__.'/../Interfaces/IDbSelect.php');
require_once(__DIR__.'/../Interfaces/IDbUpdate.php');

require_once(__DIR__.'/../Postgres/PgQuery.php');

abstract class dbModel {

	/**
	 * 
	 * 
	 * @var IDbDelete
	 */
    public $dbDelete;
    public $dbInsert;
    public $dbSelect;
    public $dbUpdate;
    public $dbHandle;

    public function __construct(
        $deleteInstance,
        $insertInstance,
        $selectInstance,
        $updateInstance,
        $databaseHandle
    ) {
        $this->dbDelete = $this->DbDeleteInstance($deleteInstance);
        $this->dbInsert = $this->DbInsertInstance($insertInstance);
        $this->dbSelect = $this->DbSelectInstance($selectInstance);
        $this->dbUpdate = $this->DbUpdateInstance($updateInstance);
        $this->dbHandle = $this->DbHandleInstance($databaseHandle);
    }


    protected static function DbDeleteInstance(IDbDelete $object) {
        return $object;
    }

    protected static function DbInsertInstance(IDbInsert $object) {
        return $object;
    }

    protected static function DbSelectInstance(IDbSelect $object) {
        return $object;
    }

    protected static function DbUpdateInstance(IDbUpdate $object) {
        return $object;
    }

    protected static function DbQueryInstance(IDbQuery $object) {
        return $object;
    }
    protected static function DbHandleInstance(IDbHandle $object) {
        return $object;
    }
    

}
