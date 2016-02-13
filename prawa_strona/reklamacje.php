<?php
require_once '../conf.php';
require_once 'adl/Person.php';
require_once 'bll/BLLReklamacje.php';

class ComplaintsView extends View {

    const FORM_ADD_COMPLAINT        = 'add_complaint';
    const FORM_ADD_COMPLAINT_ANSEWR = 'add_complaint_answer';
    const FORM_OFFICE               = 'office';
    const FORM_OFFICE_ID            = 'office_id';
    const FORM_COMPLAINT_DESC       = 'complaint_desc';
    const FORM_ANSWER               = 'answer';
    const FORM_COMPLAINT_ID         = 'complaint_id';
    
    protected $bllReklamacje;

    public static function instantiate() {
        return new ComplaintsView();
    }

    public function __construct () {
        $this->actionList = array(
                        self::FORM_ADD_COMPLAINT            => User::PRIV_DODAWANIE_REKORDU,
                        self::FORM_ADD_COMPLAINT_ANSEWR     => User::PRIV_EDYCJA_REKORDU,
        );

        parent::__construct();

        $this->personId = Utils::PodajIdOsoba();
        $this->person = new Person($this->personId);
        $this->partials = new Partials($this->person);
        $this->bllReklamacje = new BLLReklamacje();
    }

    public function run() {
        if(isset($_POST[self::FORM_ADD_COMPLAINT])) {
            $this->bllReklamacje->addComplaint($this->personId, 
                    date("Y-m-d"), 
                    $_POST[self::FORM_COMPLAINT_DESC], 
                    $this->user->getUserId(), 
                    $_POST[self::FORM_OFFICE_ID]);
        }
        if(isset($_POST[self::FORM_ADD_COMPLAINT_ANSEWR])) {
            $complaintId = (int)$_POST[self::FORM_COMPLAINT_ID];
            $this->bllReklamacje->addAnswer(
                    $complaintId,
                    $_POST[self::FORM_ANSWER. '_' . $complaintId]);
        }
        $html = $this->viewForm();
        return $html;
    }

    protected function viewForm() {
        $result = '<div style="width: 400px; margin: 0 auto;">';
        $result .= $this->renderAddForm();
        $result .= '</div>';
        
        $activeComplaints = $this->bllReklamacje->getComplaints($this->personId, true);
        $inactiveComplaints = $this->bllReklamacje->getComplaints($this->personId, false);
        $result .= $this->renderActiveComplaints($activeComplaints);
        $result .= $this->renderInactiveComplaints($inactiveComplaints);
        
        return $result;
    }
    
    protected function renderAddForm() {
        $result = "";
        
        $result .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
        $result .= $this->partials->getNameSurnamePrt().'<br /><hr />';
        $result .= '<table align="center">';
        $result .= '<tr><td valign="top">Ustalenia:</td><td>';
        $result .= $this->htmlControls->_AddTextarea(self::FORM_COMPLAINT_DESC, self::FORM_COMPLAINT_DESC, '', 300, 5, 35, '');
        $result .= '</td></tr>';
        $result .= '<tr><td>Biuro:</td><td>';
        $result .= $this->htmlControls->_AddSelect(self::FORM_OFFICE, self::FORM_OFFICE, $this->bllReklamacje->getOffices(), null, self::FORM_OFFICE_ID);
        $result .= '</td></tr>';
        $result .= '<tr><td>Konsultant:</td><td>'. $this->user->getFullUserName() .'<td/></tr>';
        $result .= '<tr><td>Data wpisu:</td><td>'. date("Y-m-d H:i:s") .'<td/></tr>';
        $result .= '<tr><td></td><td>' . $this->htmlControls->_AddSubmit(User::PRIV_DODAWANIE_REKORDU, self::FORM_ADD_COMPLAINT, self::FORM_ADD_COMPLAINT, 'Dodaj', '', '') .'<td/></tr>';
        $result .= '</table>';
        $result .= $this->addFormSuf();

        return $result;
    }
    
    protected function renderActiveComplaints($data) {
        $result = "<h2>Reklamacje bez odpowiedzi</h2>";
            
        $result .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
        $result .= $this->htmlControls->_AddHidden(self::FORM_COMPLAINT_ID, self::FORM_COMPLAINT_ID);
        $tasksFoundTable = new HtmlTable();
        $tasksFoundTable->addTableCss('wide');
        $tasksFoundTable->setHeader(array('Lp.', 'Data wpisu', 'Konsultant', 'Problem', 'Biuro', 'Odpowied¼', 'Akcje'));
        if ($data) {
            $lp =1;
            foreach ($data[Model::RESULT_FIELD_DATA] as $row) {
                $tasksFoundTable->addRow(array($lp,
                                $row[Model::COLUMN_REK_DATA],
                                $row[Model::COLUMN_UPR_IMIE_NAZWISKO],
                                $row[Model::COLUMN_REK_PROBLEM],
                                $row[Model::COLUMN_BIURA_NAME],
                                $this->htmlControls->_AddTextarea(self::FORM_ANSWER."_".$row[model::COLUMN_REK_ID_REKLAMACJE], self::FORM_ANSWER, '', 300, 5, 35, ''),
                                $this->htmlControls->_AddSubmit(User::PRIV_EDYCJA_REKORDU, 
                                        self::FORM_ADD_COMPLAINT_ANSEWR, 
                                        self::FORM_ADD_COMPLAINT_ANSEWR, 
                                        'Zapisz', 
                                        '', 
                                        "onclick='". self::FORM_COMPLAINT_ID . ".value = ". $row[Model::COLUMN_REK_ID_REKLAMACJE] ." '")));
                $lp++;
            }

            $result .= $tasksFoundTable->__toString();
        }
        $result .= $this->addFormSuf();
        return $result;
    }
    
    protected function renderInactiveComplaints($data) {
        $result = "<h2>Reklamacje z odpowiedzi±</h2>";
        $result .= HtmlTable::renderPagination($data[Model::RESULT_FIELD_ROWS_COUNT], $_SERVER['REQUEST_URI']);

        $page = isset($_REQUEST[HtmlTable::FORM_PAGE_ID]) ? (int) $_REQUEST[HtmlTable::FORM_PAGE_ID] -1 : 0;
        $lp = $page * 10 + 1;
        
        $tasksFoundTable = new HtmlTable();
        $tasksFoundTable->addTableCss('wide');
        $tasksFoundTable->setHeader(array('Lp.', 'Data wpisu', 'Konsultant', 'Problem', 'Biuro', 'Odpowied¼'));
        if ($data) {
            $rows = array_splice($data[Model::RESULT_FIELD_DATA], $page * 10, 10);
            foreach ($rows as $row) {
                $tasksFoundTable->addRow(array($lp,
                                $row[Model::COLUMN_REK_DATA],
                                $row[Model::COLUMN_UPR_IMIE_NAZWISKO],
                                $row[Model::COLUMN_REK_PROBLEM],
                                $row[Model::COLUMN_BIURA_NAME],
                                $row[Model::COLUMN_REK_ODP]
                ));
                $lp++;
            }

            $result .= $tasksFoundTable->__toString();
        }

        return $result;
    }
}

$output = ComplaintsView::instantiate();
$output->execute();
    