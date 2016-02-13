<?php 

    require_once 'dal/Model.php';
    
    class DALWakaty extends Model {
        
        public function __construct () {
            
            parent::__construct();
            $this->dal = dal::getInstance(true);
        }
        
        public function set ($data) {
            
            $stringEscCallback = array($this->dal, 'escapeString');
            $intEscCallback = array($this->dal, 'escapeInt');
            $boolEscCallback = array($this->dal, 'escapeBool');
            
            $configuration = array(
                Model::COLUMN_WAK_ID               => $intEscCallback,
                Model::COLUMN_WAK_ID_KLIENT        => $intEscCallback,
                Model::COLUMN_WAK_ID_ODDZIAL       => $intEscCallback,
                Model::COLUMN_WAK_DATA_WYJAZDU     => $stringEscCallback,
                Model::COLUMN_WAK_ILOSC_KOBIET     => $intEscCallback,
                Model::COLUMN_WAK_ILOSC_MEZCZYZN   => $intEscCallback,
                Model::COLUMN_WAK_ILOSC_TYG        => $intEscCallback,
                Model::COLUMN_WAK_ID_KONSULTANT    => $intEscCallback,
                Model::COLUMN_WAK_DATA_WPISU       => $stringEscCallback,
                Model::COLUMN_WAK_DOKLADNY         => $boolEscCallback,
                Model::COLUMN_WAK_WIDOCZNE_WWW     => $boolEscCallback,
            );
            
            $_dataList = $this->escapeParamsList($configuration, $data);
            
            $_id = isset($_dataList[Model::COLUMN_WAK_ID]) ? $_dataList[Model::COLUMN_WAK_ID] : null;
            if ($_id) {
                
                $query = 'update '.Model::TABLE_WAKAT.' set '.$this->createSetClause($_dataList).' where '.
                Model::COLUMN_WAK_ID.' = '.$_id.';';
            } else {
                
                $_id = $_dataList[Model::COLUMN_WAK_ID] = $this->getNextWakatId();
                $query = 'insert into '.Model::TABLE_WAKAT.' '.$this->createInsertClause($_dataList).';';
            }
            
            $this->dal->pgQuery($query);

            return $_id;
        }
        
        public function getAll() {
            
            $query = 'select * from '.Model::VIEW_WAKAT_STRONA.' where '.Model::COLUMN_WAS_DATA_WYJAZDU.' > \''.$this->dzis.'\' 
            order by '.Model::COLUMN_WAS_DATA_WPISU.' desc';

            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            return $this->formatDataOutput($result, $rowsCount);
        }
        
        public function getPageVacat($id) {
            
            $_id = $this->dal->escapeInt($id);
            
            $query = 'select * from '.Model::VIEW_WAKAT_STRONA.' where '.Model::COLUMN_WAS_ID.' = '.$_id;
  
            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            return $this->formatDataOutput($result, $rowsCount);
        }
        
        public function get ($id) {
            
            $_id = $this->dal->escapeInt($id);
            
            $query = 'select * from '.Model::TABLE_WAKAT.' where '.Model::COLUMN_WAK_ID.' = '.$_id;
  
            $result = $this->dal->PobierzDane($query, $rowsCount);
            
            return $this->formatDataOutput($result, $rowsCount);
        }
        
        public function delete ($id) {
            
            $_id = $this->dal->escapeInt($id);
            $query = 'delete from '.Model::TABLE_WAKAT.' where '.Model::COLUMN_WAK_ID.' = '.$_id;
            
            return $result = $this->dal->pgQuery($query);
        }
        
        protected function getNextWakatId () {
            
            $result = $this->dal->PobierzDane('select nextval(\''.Model::TABLE_WAKAT.'_id_seq\') as id;');
            $_id = $result[0]['id'];
            
            return $_id;
        }
    }