<?php

    require_once '../conf.php';
    require_once 'ui/HelpersUI.php';
    
    class UserManagementView extends View 
    {
        const FORM_USER_MGMT_CHANGE_PASSWD              = 'changePassword';
        const FORM_USER_MGMT_OLD_PASSWD                 = 'inputOldPassword';
        const FORM_USER_MGMT_NEW_PASSWD                 = 'inputNewPassword';
        const FORM_USER_MGMT_REPLY_NEW_PASSWD           = 'inputReplyNewPassword';
        
        const PASSWORD_LENGHT_MIN                       = 8;
        
        public function __construct ($loginLevel = View::LOG_IN_LEVEL_ACTIVE_PASS_EXPIRED) {
            
            parent::__construct($loginLevel);
        }
        
        protected function viewChangePasswordForm () {
            
            $html = '';
            
            if (isset($_POST[self::FORM_USER_MGMT_CHANGE_PASSWD])) {
                
                $oldPasswdMatch = $this->getUser()->verifyPassword($_POST[self::FORM_USER_MGMT_OLD_PASSWD]);
                
                if ($oldPasswdMatch && $_POST[self::FORM_USER_MGMT_NEW_PASSWD] === $_POST[self::FORM_USER_MGMT_REPLY_NEW_PASSWD])
                {
                    $passwd = $_POST[self::FORM_USER_MGMT_NEW_PASSWD];
                    
                    if ($passwd != $_POST[self::FORM_USER_MGMT_OLD_PASSWD]) {
                    
                        if (preg_match('/[0-9]{1,}/', $passwd) && preg_match('/[a-z]{1,}/', $passwd) 
                            && strlen($passwd) >= self::PASSWORD_LENGHT_MIN && sizeof(count_chars($passwd, 1)) > 4) {

                            $result = $this->user->changePassword($_POST[self::FORM_USER_MGMT_NEW_PASSWD]);
                            if ($result && $this->user->isLogged()) {
                                
                                return self::postSuccessfull('/pgsql.php');
                            }
                            
                        } else {
                            $html .= 'Has³o niedostatecznie d³ugie/skomplikowane. Wymagane has³o musi mieæ minimum 8 znaków, litery i cyfry, conajmniej 5 ró¿nych znaków.';
                        }
                    } else {
                        
                        $html .= 'Stare has³o i nowe has³o musz± siê ró¿niæ.';
                    }
                    
                } else {
                    
                    $html .= $oldPasswdMatch ? 'Has³a niezgodne' : 'Niepoprawne stare has³o';
                } 
            }
            
            
            $html .= $this->addFormPostPre($_SERVER['REQUEST_URI']);
            $html .= '<table>';

            $html .= '<tr><td>Stare has³o: </td><td>'.                                                                               //todo
                $this->htmlControls->_AddTextbox(self::FORM_USER_MGMT_OLD_PASSWD, self::FORM_USER_MGMT_OLD_PASSWD, '', 50, 20, '', '', '', '', HtmlControls::INPUT_TYPE_PASSWORD)
                .'</td></tr>';
            $html .= '<tr><td>Nowe has³o: </td><td>'.                                                                                 //todo
                $this->htmlControls->_AddTextbox(self::FORM_USER_MGMT_NEW_PASSWD, self::FORM_USER_MGMT_NEW_PASSWD, '', 50, 20, '', '', '', '', HtmlControls::INPUT_TYPE_PASSWORD)
                .'</td></tr>';
            $html .= '<tr><td>Powtórz has³o: </td><td>'.                                                                                          //todo
                $this->htmlControls->_AddTextbox(self::FORM_USER_MGMT_REPLY_NEW_PASSWD, self::FORM_USER_MGMT_REPLY_NEW_PASSWD, '', 50, 20, '', '', '', '', HtmlControls::INPUT_TYPE_PASSWORD)
                .'</td></tr>';
            $html .= '<tr><td></td><td>'.$this->htmlControls->_AddNoPrivilegeSubmit(self::FORM_USER_MGMT_CHANGE_PASSWD, self::FORM_USER_MGMT_CHANGE_PASSWD, 'Zmieñ', '', '').'</td></tr>';
            
            $html .= '</table>';
            $html .= $this->addFormSuf();
            
            return $html;
        }
        
        public function run () {
            
            return $this->viewChangePasswordForm();
        }
    }
    
    $output = new UserManagementView();
    $output->execute();