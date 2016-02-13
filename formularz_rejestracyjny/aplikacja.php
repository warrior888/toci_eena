<?php

// ¶ ± ¼

ini_set('display_errors', 0);

    require_once '../conf.php';
    require_once 'ui/UtilsUI.php';
    require_once 'ui/HelpersUI.php';    
    require_once 'dal/DALZatrudnienie.php';    
    require_once 'bll/BLLZatrudnienie.php';    
    require_once 'bll/mail.php';    

    /*
     * TODO 
     * 
     * select by phone and mail logic
     * send id mail to email address
     * do an insert for an application to certain vacat id - dal method required ? get vacat, get defaults, do apply record, insert
     */
    
    class Application extends View
    {
        const FORM_APPLY_SUBMIT = 'apply_submit';
        
        const FORM_MAIL_INPUT = 'mail';
        const FORM_PHONE_INPUT = 'phone';
        const FORM_ID_INPUT = 'id_number';
        
        protected $dalZatrudnienie;
        protected $bllZatrudnienie;
        protected $bllMail;
        
        protected $vacatId;

        public function __construct() {
            
            parent::__construct(View::LOG_IN_LEVEL_NONE, User::PRIV_NONE_REQUIRED);
            $this->dalZatrudnienie = new DALZatrudnienie();
            $this->bllZatrudnienie = new BLLZatrudnienie();
            $this->bllMail = new MailSend();
            
            $this->vacatId = isset($_GET['vacatId']) ? (int)($_GET['vacatId']) : null;
        }
        
        public function run() {
            
            return $this->RenderForm();
        }
        
        private function RenderForm()
        {
            $this->htmlControls = new HtmlControls();
            
            $showIdInput = false;
            
            $mail = '';
            $phone = '';
            $idNr = '';
            
            if (isset($_POST[self::FORM_APPLY_SUBMIT])) {
                
                $mail = $_POST[self::FORM_MAIL_INPUT];
                $phone = $_POST[self::FORM_PHONE_INPUT];
                $idNr = isset($_POST[self::FORM_ID_INPUT]) ? (int)$_POST[self::FORM_ID_INPUT] : null;
                
                if (is_null($idNr)) {
                    
                    if (!$phone || !$mail)
                    {
                        $html .= '<table align="center"><tr><td class="error highlight">Konieczne jest podanie zarówno telefonu jak i adresu email.</td></tr></table>';
                    }
                    else 
                    {
                        $result = $this->dalZatrudnienie->getKandydatKontakt($phone, $mail);
                        if ($result[Model::RESULT_FIELD_ROWS_COUNT] == 1) {
                            
                            // send an emial with an id
                            $data = $result[Model::RESULT_FIELD_DATA][0];
                            
                            $id = $data[Model::COLUMN_DOS_ID];
                            $email = $data[Model::COLUMN_DIN_EMAIL];
                            
                            $tresc = 'Witamy
    	Twoje ID to : '.$id.'.
    Z powa¿aniem
    E&A';
                            
                            $this->bllMail->DodajOdbiorca($email);
                            $this->bllMail->WyslijMail('Aplikacja na wakat online - E&A - Numer ID', $tresc);
                            
                            $html .= '<table align="center"><tr><td class="highlight">Pod podany adres emial wys³ano numer ID. Przepisz go do nowo dodanego pola.</td></tr></table>';
                            $showIdInput = true;
                        } else if ($result[Model::RESULT_FIELD_ROWS_COUNT] < 1) {
                            
                            $html .= '<table align="center"><tr><td class="error highlight">Dla podanej pary danych kontaktowych nie znaleziono rekordu kandydata.</td></tr></table>';
                        } else {
                            
                            $html .= '<table align="center"><tr><td class="error highlight">Podane dane kontaktowe wskazuj± wiêcej ni¿ jednego kandydata. Nie ma mo¿liwoœci aplikowania online.</td></tr></table>';
                        }
                    }
                } else {
                    
                    $result = $this->dalZatrudnienie->getKandydatKontakt($phone, $mail);
                    $data = $result[Model::RESULT_FIELD_DATA][0];
                    
                    if ($idNr == $data[Model::COLUMN_DOS_ID]) { 

                        try {
                            
                            $this->bllZatrudnienie->apply($idNr, $this->vacatId);
                            $html .= '<table align="center"><tr><td class="highlight">Poprawnie aplikowano na wakat. Dziêkujemy.</td></tr></table>';
                        } catch (ProjectLogicException $e) {
                            
                            $html .= '<table align="center"><tr><td class="error highlight">'.$e->getCustomMessage().'</td></tr></table>';
                        }
                    } else {
                        
                        $html .= '<table align="center"><tr><td class="error highlight">Niezgodny numer ID.</td></tr></table>';
                    }
                }
            }
            
            $html .= '<table align="center"><tr><td>';
    
            $html .= '
            <div class="leftFloat headDiv">Jeste¶ ju¿ naszym kandydatem:
            <br />
            <br />
            '.$this->addFormPostPre($_SERVER['REQUEST_URI']).'
            <table class="gridTable">
            ';
            
            $html .= '<tr class="evenRow"><td>Podaj swój adres email:</td><td>'.$this->htmlControls->_AddTextbox(self::FORM_MAIL_INPUT, 'id_mail', View::escapeOutput($mail), 80, 20, '', 'required').'<br /></td></tr>';
            $html .= '<tr class="oddRow"><td>Podaj swój telefon komórkowy:</td><td>'.$this->htmlControls->_AddNumberbox(self::FORM_PHONE_INPUT, 'id_phone', View::escapeOutput($phone), 9, 9, '', 'required').'</td></tr>';
            
            if ($showIdInput)
                $html .= '<tr class="evenRow"><td>Podaj swój numer id:</td><td>'.$this->htmlControls->_AddNumberbox(self::FORM_ID_INPUT, 'id_numer_id', View::escapeOutput($idNr), 8, 8, '', 'required').'</td></tr>';
            
            $submit = $showIdInput ? 'Aplikuj' : 'Zatwierd¼';
                
            $html .= '<tr><td>'.$this->htmlControls->_AddNoPrivilegeSubmit(self::FORM_APPLY_SUBMIT, 'id_'.self::FORM_APPLY_SUBMIT, $submit, '', '').'</td></tr>';
            
            $html .= '</table></form>
            </div>';
            
            $html .= '<div class="leftFloat headDiv">Nie jeste¶ jeszcze naszym kandydatem:<br />';
            $html .= '<a href="http://eena.21infinity.com/formularz_rejestracyjny/ankieta.php">Wype³nij formularz rejestracyjny</a></div>';
            
            $html .= '</td></tr></table>';
            
            return $html;
        }
    }
    
    $cssFile = 'ankieta';
    if(false !== stripos($_SERVER['HTTP_USER_AGENT'], 'MSIE'))
        $cssFile .= '_ie';
    
    //onload="document.getElementById(\''.$output->getElementName('span_'.AnkietaView::FIELD_CITY).'\').innerHTML = document.getElementById(\''.$output->getElementName(AnkietaView::FIELD_CITY, true).'\').value;"
    $html = '<html>
    <head>
        <meta HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
        <script language="javascript" src="../js/script.js"></script>
        <script language="javascript" src="jquery.js"></script>
        <script language="javascript" src="../js/validations.js"></script>
        <script language="javascript" src="../js/utils.js"></script>
        <script language="javascript" src="utils.js"></script>
        <link href="../css/reset.css" rel="stylesheet" type="text/css">
        <link href="../css/'.$cssFile.'.css" rel="stylesheet" type="text/css">
    </head>
    <body onload="alertRegFormCookie(\'ciacho\', \'W³±czenie obs³ugi ciasteczek jest konieczne, formularz nie mo¿e zostaæ poprawnie wype³niony.\', \'cookieAlertMsgBox\', \'mainFormTable,agreementTable,bottomSummaryPlaceHolder,add\');">
    <div id="popup" style="display: none;"></div>';

    echo $html;
    
    if (isset($_GET['vacatId'])) {
        
        $application = new Application(); 
        
        echo $application->run();
    }
?>
</body>
</html>