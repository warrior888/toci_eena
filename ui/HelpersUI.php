<?php
define ('PRZEWOZNIK_BARTUS_ID', 1);
define ('PRZEWOZNIK_SOLTYSIK_ID', 5);

define ('PONIEDZIALEK_ID', 1);
define ('CZWARTEK_ID', 4);
define ('PIATEK_ID', 5);
//php id
define ('NIEDZIELA_ID', 7);
define ('NIEDZIELA_DB_ID', 0);

require_once 'vaElClass.php';
require_once 'adl/User.php';
require_once 'ui/ControlsUI.php';
require_once 'ui/PartialsUI.php';
require_once 'ui/HtmlControls.php';
require_once 'ui/FormValidator.php';

/*
 Uprawnienia !!! Priviledges:

 - controls = mandatory param - action type
 - controls - match against priviledges, render no button
 - view - action list ( => required priviledge) analyzed for each post, matched agains priviledge list, 403 on mismatch
 */

abstract class View
{
    const LOG_IN_LEVEL_NONE                  = 1;
    const LOG_IN_LEVEL_ACTIVE_PASS_EXPIRED   = 2;
    const LOG_IN_LEVEL_LOGGED                = 3;

    const FORM_XSRF_HIDDEN_NAME    = 'form_id';

    const HTML_TAG_GRID_TABLE      = '<table cellspacing="0" cellpadding="3" border="0" align="center" class="gridTable">';
    const HTML_TAG_END_TABLE       = '</table>';
     
    protected $bll;
    /**
     * @var valControl
     */
    protected $controls;
    /**
     *
     * @var User
     */
    protected $user;
    protected $loginLevel;
    protected $priviledgeLevel;
    // These should be used.
    /**
     * @desc HtmlControls util delivering html controls of desired type
     * @var HtmlControls
     */
    protected $htmlControls;
    /**
     * @desc array list of actions to be verified against priviledges
     */
    protected $actionList = array(); // todo make private (?)
    /**
     * @desc Partials form html snipets class
     */
    protected $partials;
    /**
     * @desc FormValidator validator
     */
    protected $formValidator;
    
    /**
     * Person Id
     * @var int
     */
    protected $personId;


    protected $person;

    public function __construct($loginLevel = self::LOG_IN_LEVEL_LOGGED, $priviledgeLevel = User::PRIV_AKTYWNY)
    {
        $this->loginLevel = $loginLevel;
        $this->priviledgeLevel = $priviledgeLevel;
        // this is strongly deprecated
        $this->controls = new valControl();
        $this->htmlControls = new HtmlControls();
        $this->user = User::getInstance();

        $forbidden = false;
        if (sizeof($this->actionList)) {

            foreach ($this->actionList as $action => $priviledge) {

                if (isset($_REQUEST[$action])) {
                    $forbidden = true;
                    if ($this->user->isAllowed($priviledge))
                    $forbidden = false;
                }
            }
        }

        if (true !== $this->user->isAllowed($this->priviledgeLevel)) {

            $forbidden = true;
        }

        if ($forbidden === true) {

            throw new ViewForbiddenException();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST')
        $this->validatePost();
    }

    protected function setActionList ($actionList)
    {
        $this->actionList = $actionList;
    }

    /**
     * @desc Util to add
     */
    public function addFormPostPre ($action = '', $attrs = array())
    {
        if (!$action)
        $action = $_SERVER['PHP_SELF'];

        $formAttributes = array();
        if (sizeof($attrs)) {

            foreach ($attrs as $key => $value) {

                $formAttributes[] = $key.'="'.$value.'"';
            }
        }
        return '<form method="POST" action="'.$action.'" '.implode(' ', $formAttributes).'>'.
        $this->htmlControls->_AddHidden(self::FORM_XSRF_HIDDEN_NAME.'_'.md5($action), self::FORM_XSRF_HIDDEN_NAME, SessionManager::getSessionId());
    }

    /**
     * TODO could be static
     * @desc
     */
    public function addFormSuf ()
    {
        return '</form>';
    }

    public function getControls()
    {
        return $this->controls;
    }

    public function getUser() {

        return $this->user;
    }

    public function validatePost () {
         
        $token = isset($_POST[self::FORM_XSRF_HIDDEN_NAME]) ? $_POST[self::FORM_XSRF_HIDDEN_NAME] : null;
         
        if (!$token || SessionManager::getSessionId() !== $token)
        throw new ViewBadRequestException('Request invalid, missing or invalid session id, env: '.var_export($_POST, true));

        return true;
    }

    public static function postSuccessfull ($url) {
         
        header('Location: '.$url);
        die();
    }

    public static function escapeOutput ($output) {
        return htmlspecialchars($output, ENT_QUOTES, 'iso-8859-1');
        //return htmlentities($output, ENT_QUOTES, 'UTF-8');
    }

    /**
     * If $display set to true method display result, otherwise return.
     * @param string $display display or return result
     * @return void|string
     */
    public function execute ($display = true) {

        // TODO unnecessary ?
        CommonUtils::SessionStart();

        try {

            if (!$this->checkLoggedLevel())
            {
                require 'logowanie.php';
                die();
            }
            else
            {
                $html = $this->run();
            }
        } catch (ViewException $e) {

            LogManager::log(LOG_ERR, '['.__FILE__.'] Output instantiate/run error: '.$e->getMessage(), $e);
            $html = CommonUtils::getViewExceptionMessage($e);
        } catch (Exception $e) {

            LogManager::log(LOG_ERR, '['.__FILE__.'] Output instantiate/run unhandled exception: '.$e->getMessage(), $e);
            $html = CommonUtils::getServerErrorMsg();
        }

        $result = '<html>';
        $result .= HelpersUI::_addHtmlBasicHeaders(HelpersUI::PATH_LEVEL_1);
        $result .= '<body>';
        $result .= $html;
        $result .= '</body></html>';
            
        if($display) {
            echo $result;
        } else {
            return $result;
        }
    }

    protected function checkLoggedLevel() {

        switch ($this->loginLevel)
        {
            case self::LOG_IN_LEVEL_LOGGED:
                return $this->getUser()->isLogged();
            case self::LOG_IN_LEVEL_ACTIVE_PASS_EXPIRED:
                return $this->getUser()->hasSession();
            case self::LOG_IN_LEVEL_NONE:
                return true;
            default:
                return false;
        }
    }

    public function run () {
        //abstract
    }
}

class HelpersUI
{
    const PATH_LEVEL_1 = '../';
    const PATH_LEVEL_2 = '../../';

    public static function _addHtmlBasicHeaders ($path = '', $customJsPaths = array())
    {
        //<link href="'.$path.'css/reset.css" rel="stylesheet" type="text/css">

        $cssFile = 'layout';
        //if(false !== stripos($_SERVER['HTTP_USER_AGENT'], 'MSIE'))
        //    $cssFile .= '_ie';

        $html =  '
            <head>
            <title>E&A - Baza Danych</title>
            <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-2">
            <script language="javascript" src="'.$path.'js/script.js"></script>
            <script language="javascript" src="'.$path.'js/utils.js"></script>
            <script language="javascript" src="'.$path.'js/validations.js"></script>
            <script language="javascript" src="'.$path.'js/jquery.js"></script>';

        foreach($customJsPaths as $customJsPath) {

            $html .= '<script language="javascript" src="'.$path.$customJsPath.'"></script>';
        }

        $html .= '<link href="'.$path.'css/'.$cssFile.'.css" rel="stylesheet" type="text/css">
            </head>
            ';

        return $html;
    }
    /**
     * @desc Method adds most common headers, for specific create custom method or DIY old fashioned way.
     * the path is the local file storing place path
     */
    public static function addHtmlBasicHeaders ($path = '', $customJsPaths = array())
    {
        echo self::_addHtmlBasicHeaders($path, $customJsPaths);
    }

    /**
     * @desc Add html row - generate table row html
     * // TODO chosen columns list as option
     */
    public static function addTableRow($wiersz, $isHeader = false, $headerMap = array())
    {
        $tdh = $isHeader ? 'th' : 'td';
        $result = '';
        $mapSize = sizeof($headerMap);
        foreach ($wiersz as $index => $kolumna)
        {
            if(!$kolumna)
            $kolumna = '----';

            if (($mapSize > 0 && isset($headerMap[$index])) || $mapSize === 0)
            $result .= '<'.$tdh.' nowrap align="center">'.$kolumna.'</'.$tdh.'>';
        }

        return $result;
    }

}

class CommonUtils
{
    protected static $exceptionMapping = array(
    ProjectLogicException::ERR_CODE_BAD_DATA      => 'ViewBadRequestException',
    ProjectLogicException::ERR_CODE_SERVER_ERROR  => 'ViewServerErrorException',
    );

    protected static $viewExceptionMapping = array(
            'ViewBadRequestException'    => '¯±danie jest nieprawid³owe.',
            'ViewForbiddenException'     => 'Zabroniony dostêp.',
            'ViewNotFoundException'      => 'Nie odnaleziono ¿±danego zasobu.',
            'ViewServerErrorException'   => 'B³±d serwera.',
    );
     
    protected static $viewExceptionHeaders = array(
            'ViewBadRequestException'    => 'HTTP/1.1 400',
            'ViewForbiddenException'     => 'HTTP/1.1 403',
            'ViewNotFoundException'      => 'HTTP/1.1 404',
            'ViewServerErrorException'   => 'HTTP/1.1 500',
    );

    public static function SessionStart ()
    {
        if (empty($_SESSION)) {
            //session_set_cookie_params(0, '/', "");
            session_start();
        }
    }

    public static function outputBufferingOn ()
    {
        //ob_start();
        //echo $_SERVER['PHP_SELF'];
    }

    public static function sendOutputBuffer ()
    {
        //ob_flush();
    }

    /**
     * @desc cut off any markup
     */
    public static function getValidString ($string) {

        return strip_tags($string);
    }

    /**
     * @desc Cast value to int
     */
    public static function getValidInt ($int) {

        return (int)$int;
    }

    /**
     * @desc Encode special html characters into entities
     */
    public static function santizeString ($string) {

        return htmlspecialchars(strip_tags($string), ENT_QUOTES);
    }

    public static function mapLogicException (ProjectLogicException $e) {

        if (isset(self::$exceptionMapping[$e->getCode()])) {

            $customMsg = $e->getCustomMessage();
            if ($customMsg)
            throw new self::$exceptionMapping[$e->getCode()]($customMsg, $e->getCode(), true, $e);

            throw new self::$exceptionMapping[$e->getCode()]('', 0, false, $e);
        }

        throw new ViewServerErrorException('Logic exception report failure for code: '.$e->getCode().' and exception '.get_class($e).', exc message: '.$e->getMessage(), 0, false, $e);
    }

    public static function getViewExceptionMessage (ViewException $e) {
         
        $class = get_class($e);
        if (isset(self::$viewExceptionHeaders[$class]))
        header(self::$viewExceptionHeaders[$class]);

        // Get custom message from inner (ProjectLogicException) exception, publish message is so far only set true with ProjectLogicException inner exception
        if ($e->getPublishMessage())
        return $e->getInnerException()->getCustomMessage();

        if (isset(self::$viewExceptionMapping[$class]))
        return self::$viewExceptionMapping[$class];

        return self::$viewExceptionMapping['ViewServerErrorException'];
    }

    public static function getServerErrorMsg () {
         
        header(self::$viewExceptionHeaders['ViewServerErrorException']);
        return self::$viewExceptionMapping['ViewServerErrorException'];
    }
    
    /**
     * @param type $string
     * @return string
     */
    public static function removePolishCharacters($string) {
       $from = array('±','¿','¶','¼','æ','ñ','³','ó','ê', '¡','¯','¦','¬','Æ','Ñ','£','Ó','Ê');
       $to   = array('a','z','s','z','c','n','l','o','e', 'A','Z','S','Z','C','N','L','O','E');
       return str_replace($from, $to, $string);
    }
}

abstract class ViewException extends ProjectException {

    protected $publishMessage;
    protected $user;

    public function __construct ($message = '', $code = 0, $publishMessage = false, $innerException = null) {

        $this->user = User::getInstance();
        $message .= '[PERFORMER] '.$this->user->getUserName()."\n, env: ";
        //env - think it over
        if ($_SERVER['REQUEST_METHOD'] == 'GET')
        $message .= var_export($_GET, true);
        else
        $message .= var_export($_POST, true);

        parent::__construct($message, $code, $innerException);
        $this->publishMessage = $publishMessage;
    }

    public function getPublishMessage () {
        return $this->publishMessage;
    }
}

class ViewBadRequestException extends ViewException {}
class ViewNotFoundException extends ViewException {}
class ViewForbiddenException extends ViewException {}
class ViewServerErrorException extends ViewException {}