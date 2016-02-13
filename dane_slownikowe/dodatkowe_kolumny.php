<?php
    require_once '../conf.php';
    require_once '../bll/queries.php';
    require_once '../ui/UtilsUI.php';
    require_once '../ui/HelpersUI.php';
    
    class AddColumnsView extends View 
    {
        const ACTION_ADD    = 'dodaj_kolumne';
        const ACTION_EDIT   = 'edytuj_kolumne';
        const ACTION_ERASE  = 'kasuj_kolumne';
        const ACTION_UPDATE = 'aktualizuj_kolumne';
        
        const HIDDEN_ID_KOLUMNA    = 'id_dod_kolumna';
        const HIDDEN_ID_UP_KOLUMNA = 'id_up_kolumna';
        
        const FORM_COLUMN_NAME         = 'nazwa_kolumny';
        const FORM_COLUMN_DISPLAY_NAME = 'nazwa_wysw_kolumny';
        const FORM_COLUMN_TYPE_ID      = 'typ_id';
        const FORM_COLUMN_SEARCH       = 'szukaj';
        const FORM_COLUMN_ANKIETA      = 'ankieta';
                
        const ID_KOLUMNY_WYSZUKIWANIE          = 'id_kolumny_wyszukiwanie';
        const ID_DANE_DODATKOWE_INTERNET_LISTA = 'id_dane_dodatkowe_internet_lista';
        
        protected $queriesEngine;
        protected $queriesBaseAnkieta;
        protected $addColumns;
        protected $addColumnsAnkieta;
        protected $searchColumns;
        protected $typesList = null;
        
        private $typesMapping = array(
            'bool'      => 'Tak/Nie',
            'int'       => 'Liczba',
            'string'    => 'Tekst',
            'date'      => 'Data',
            'daterange' => 'Zakres dat',
        );
        
        public function __construct()
        {
            parent::__construct();
            $this->queriesEngine = new QueriesEngine();
            $this->addColumns = $this->queriesEngine->getAdditionalColumns();
            $this->searchColumns = $this->queriesEngine->getColumns();
            
            $this->queriesBaseAnkieta = new QueriesBase(
                array (
                    QueriesBase::CONF_ADD_COLUMNS => 'dod_kolumny_ankieta',
                    QueriesBase::CONF_ADD_QUERY_DATA => 'select id, nazwa, nazwa_wyswietlana, id_typ, edycja from dane_dodatkowe_lista where id in (select id_dane_dodatkowe_lista from dane_dodatkowe_internet_lista) order by nazwa;',
                    QueriesBase::CONF_DATA_TABLE => 'dane_dodatkowe_ankieta',
                    QueriesBase::CONF_DATA_TABLE_FKEY => 'id_dane_dodatkowe_internet_lista',
                )
            );
            
            $this->addColumnsAnkieta = $this->queriesBaseAnkieta->getAdditionalColumns();
        }
        
        public function addColumnsList ()
        {
            $result = $this->addFormPostPre().'<table>';
            $result .= $this->controls->AddHidden(self::HIDDEN_ID_KOLUMNA, self::HIDDEN_ID_KOLUMNA);
            foreach ($this->addColumns as $row) 
            {
                if ($row['edycja'] == 't')
                {
                    $result .= '<tr><td bgcolor="#DDDDDD">';
                    $result .= $this->controls->AddTableSubmit(self::ACTION_EDIT, self::ACTION_EDIT.$row['nazwa'], "Edytuj.", JsEvents::ONCLICK."='".self::HIDDEN_ID_KOLUMNA.".value=\"".$row['nazwa']."\";'");
                    $result .= '</td><td bgcolor="#DDDDDD">';
                    $result .= $this->controls->AddTableSubmit(self::ACTION_ERASE, self::ACTION_ERASE.$row['nazwa'], "Kasuj.", JsEvents::ONCLICK."='".self::HIDDEN_ID_KOLUMNA.".value=\"".$row['nazwa']."\"; return confirm(\"Operacja jest nieodwracalna, czy jeste¶ pewien ?\");'");
                    $result .= '</td>';
                    $result .= HelpersUI::addTableRow(array($row['nazwa_wyswietlana']));
                    $result .= '</tr>';
                }
            }
            $result .= '</table>'.$this->addFormSuf();
            return $result;
        }
        
        public function addInsertColumnForm ()
        {
            return $this->addAddUpdateForm();
        }
        
        public function addEditColumnForm ($columnId)
        {
            return $this->addAddUpdateForm($this->getColumnInfo($columnId));
        }
        
        public function updateColumn ($data)
        {
            //roznica polega na tym, ze tu pobieramy istniejacy rekord (w data musi byc uzupelniony hidden kolumna)
            //dodatkowo potencjalnie usuwane sa rekordy (lub dodawane) z tabel konfiguracji szukania i ankiety          
            $this->queriesEngine->updateColumn($data[self::HIDDEN_ID_UP_KOLUMNA], $data[self::FORM_COLUMN_NAME], $data[self::FORM_COLUMN_DISPLAY_NAME], $data[self::FORM_COLUMN_TYPE_ID]);            
            $this->addColumns = $this->queriesEngine->getAdditionalColumns();
            $this->searchColumns = $this->queriesEngine->getColumns();
            
            $columnInfo = $this->getColumnInfo($data[self::FORM_COLUMN_NAME]);
            if (isset($data[self::FORM_COLUMN_SEARCH]) && !$columnInfo[self::ID_KOLUMNY_WYSZUKIWANIE])
            {
                //dodac do search
                $this->queriesEngine->addToSearchColumns($data[self::FORM_COLUMN_NAME], $data[self::FORM_COLUMN_DISPLAY_NAME], $data[self::FORM_COLUMN_TYPE_ID]);
            }
            else if(!isset($data[self::FORM_COLUMN_SEARCH]) && $columnInfo[self::ID_KOLUMNY_WYSZUKIWANIE])
            {
                //wywalic z search
                $this->queriesEngine->removeFromSearchColumns($columnInfo[self::ID_KOLUMNY_WYSZUKIWANIE]);
            }
            else if($columnInfo[self::ID_KOLUMNY_WYSZUKIWANIE])
            {
                //potencjalnie nazwa zmiany wysw i typu, jest tu id wiersza :> ?? :(, update
                $this->queriesEngine->updateSearchColumn($columnInfo[self::ID_KOLUMNY_WYSZUKIWANIE], $data[self::FORM_COLUMN_NAME], $data[self::FORM_COLUMN_DISPLAY_NAME], $data[self::FORM_COLUMN_TYPE_ID]);
            }
            
            if (isset($data[self::FORM_COLUMN_ANKIETA]) && !$columnInfo[self::ID_DANE_DODATKOWE_INTERNET_LISTA])
            {
                //dodac do ankieta
                $this->queriesEngine->addToAnkieta($columnInfo['id']);
            }
            if(!isset($data[self::FORM_COLUMN_ANKIETA]) && $columnInfo[self::ID_DANE_DODATKOWE_INTERNET_LISTA])
            {
                //wywalic z ankieta
                $this->queriesEngine->removeFromAnkieta($columnInfo[self::ID_DANE_DODATKOWE_INTERNET_LISTA]);
            }
        }
        
        public function deleteColumn ($data)
        {
            #TODO: sprawdzenie, czy sa na kolumne dane
            $columnInfo = $this->getColumnInfo($data[self::HIDDEN_ID_KOLUMNA]);
            if ($columnInfo[self::ID_KOLUMNY_WYSZUKIWANIE])
                $this->queriesEngine->removeFromSearchColumns($columnInfo[self::ID_KOLUMNY_WYSZUKIWANIE]);
                
            if ($columnInfo[self::ID_DANE_DODATKOWE_INTERNET_LISTA])
                $this->queriesEngine->removeFromAnkieta($columnInfo[self::ID_DANE_DODATKOWE_INTERNET_LISTA]);
            
            $this->queriesEngine->deleteColumn($columnInfo['id']);
            
            $this->addColumns = $this->queriesEngine->getAdditionalColumns();
            $this->searchColumns = $this->queriesEngine->getColumns();
        }
        
        public function addColumn($data)
        { 
            //wylacznie self::FORM_COLUMN_..., max 3 inserty i flush cache
            $this->queriesEngine->addNewColumn($data[self::FORM_COLUMN_NAME], $data[self::FORM_COLUMN_DISPLAY_NAME], $data[self::FORM_COLUMN_TYPE_ID], 
                isset($data[self::FORM_COLUMN_SEARCH]) ? true : false, isset($data[self::FORM_COLUMN_ANKIETA]) ? true : false);
            $this->addColumns = $this->queriesEngine->getAdditionalColumns();
            $this->searchColumns = $this->queriesEngine->getColumns();
        }
        
        private function getColumnInfo ($columnId)
        {
            if (isset($this->addColumns[$columnId]))
            {
                $record = $this->addColumns[$columnId];
                $record[self::ID_KOLUMNY_WYSZUKIWANIE] = false;
                $record[self::ID_DANE_DODATKOWE_INTERNET_LISTA] = false;
                
                if (isset($this->searchColumns[$columnId]))
                {
                    $record[self::ID_KOLUMNY_WYSZUKIWANIE] = $this->searchColumns[$columnId]['id'];
                }
                if (isset($this->addColumnsAnkieta[$columnId]))
                {
                    $record[self::ID_DANE_DODATKOWE_INTERNET_LISTA] = $this->addColumnsAnkieta[$columnId]['id'];
                }
                return $record;
            }
            return null;
        }        

        private function getTypesList ()
        {
            if (!$this->typesList) 
            {
                $this->typesList = $this->controls->dalObj->PobierzDane('select id, nazwa from typ', $ilosc_wierszy);
                foreach ($this->typesList as $key => &$type)
                {
                    if (isset($this->typesMapping[$type['nazwa']]))
                        $type['nazwa'] = $this->typesMapping[$type['nazwa']];
                }
            }
            return $this->typesList;
        }
        
        private function addAddUpdateForm ($record = null)
        {
            $recId = null;
            $nazwa = null;
            $nazwaWysw = null;
            $idTyp = null;
            $szukaj = null;
            $ankieta = null;
            
            $submitName = self::ACTION_ADD;
            $submitValue = 'Dodaj';
            
            if(is_array($record)) 
            {
                $recId = $record['id'];
                $nazwa = $record['nazwa'];
                $nazwaWysw = $record['nazwa_wyswietlana'];
                $idTyp = $record['id_typ'];
                $szukaj = $record[self::ID_KOLUMNY_WYSZUKIWANIE];
                $ankieta = $record[self::ID_DANE_DODATKOWE_INTERNET_LISTA];
                
                $submitName = self::ACTION_UPDATE;
                $submitValue = 'Aktualizuj';
            }
            
            $result = $this->addFormPostPre().'<table>';
            $result .= $this->controls->AddHidden(self::HIDDEN_ID_UP_KOLUMNA, self::HIDDEN_ID_UP_KOLUMNA, $recId);
            $result .= '<tr><td>Nazwa: </td><td>'.$this->controls->AddTextbox(self::FORM_COLUMN_NAME, 'id_'.self::FORM_COLUMN_NAME, $nazwa, 30, 30, '').'</td></tr>';
            $result .= '<tr><td>Nazwa wy¶wietlana: </td><td>'.$this->controls->AddTextbox(self::FORM_COLUMN_DISPLAY_NAME, 'id_'.self::FORM_COLUMN_DISPLAY_NAME, $nazwaWysw, 30, 30, '').'</td></tr>';
            $result .= '<tr><td>Typ: </td><td>'.$this->controls->_AddSelectData('typ', 'id_typ', '', $this->getTypesList(), $idTyp, self::FORM_COLUMN_TYPE_ID).'</td></tr>';
            $result .= '<tr><td></td><td>'.$this->controls->_AddCheckbox(self::FORM_COLUMN_SEARCH, 'id_'.self::FORM_COLUMN_SEARCH, (bool)$szukaj, '', 'Szukanie po kolumnie', $szukaj).'</td></tr>';
            $result .= '<tr><td></td><td>'.$this->controls->_AddCheckbox(self::FORM_COLUMN_ANKIETA, 'id_'.self::FORM_COLUMN_ANKIETA, (bool)$ankieta, '', 'Poka¿ pole w ankiecie', $ankieta).'</td></tr>';
            $result .= '<tr><td>'.$this->controls->AddSubmit($submitName, 'id_'.$submitName, $submitValue, '').'</td></tr>';
            $result .= '</table>'.$this->addFormSuf();
            
            return $result;
        }
    }
    
    CommonUtils::outputBufferingOn();
    CommonUtils::SessionStart();
////////////-------------------------------
    echo '<html>';
    HelpersUI::addHtmlBasicHeaders(HelpersUI::PATH_LEVEL_1);
    echo '<body>';
    
    include("../bll/definicjeKlas.php");
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {

        if (isset($_SESSION['zmiana_uprawnien']) && ($_SERVER['REQUEST_METHOD'] == 'POST' || isset($_GET['dodatkowe_kolumny'])))
        {
            $output = new AddColumnsView();
            if (isset($_POST[AddColumnsView::ACTION_ADD]))
            {
                $output->addColumn($_POST);
            }
            if (isset($_POST[AddColumnsView::ACTION_ERASE]))
            {
                $output->deleteColumn($_POST);
            }
            if (isset($_POST[AddColumnsView::ACTION_UPDATE]))
            {
                $output->updateColumn($_POST);
            }
            if (isset($_POST[AddColumnsView::ACTION_EDIT]))
            {
                //mozna dodac util form insert edit 
                echo $output->addEditColumnForm($_POST[AddColumnsView::HIDDEN_ID_KOLUMNA]);
            }
            
            //dodatkowe pola dla osob
            echo $output->addColumnsList();
            echo '<hr />';            
            
            if (!isset($_POST[AddColumnsView::ACTION_EDIT]))
                echo $output->addInsertColumnForm();
            //pola widoczne w ankiecie
        }
    }
    CommonUtils::sendOutputBuffer();
?>
</html>
