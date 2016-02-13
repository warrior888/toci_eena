<?php

    require_once '../conf.php';
    require_once 'ui/UtilsUI.php';
    require_once 'ui/HelpersUI.php';
    
    require_once 'bll/BLLOpisPrac.php';
    
    class InternalJobDescriptionView extends View 
    {
        public function __construct() {
                        
            parent::__construct();
            
            $this->type = DALOpisPrac::OP_TYPE_INTERNAL;
            $this->bll = new BLLOpisPrac();
        }
        
        public function run () {
            
            $id = isset($_GET[Model::COLUMN_OPR_ID]) ? (int)$_GET[Model::COLUMN_OPR_ID] : null;
            
            if (null === $id) {
                
                throw new ViewBadRequestException('Id missing');
            }
            
            $opis = $this->bll->getAll(DALOpisPrac::OP_TYPE_INTERNAL, $id);
            $html = '';
            $html .= '<b>Wewnetrzny opis pracy:</b><br />';
            
            if (is_array($opis) && $opis[Model::RESULT_FIELD_ROWS_COUNT] > 0) {
                
                $html .= $opis[Model::RESULT_FIELD_DATA][0][Model::COLUMN_OPR_OPIS];
            } else {
                
                $html .= 'brak';
            }
            
            return $html;
        }
    }
    
    CommonUtils::SessionStart();

    try {
        $output = new InternalJobDescriptionView();

        if (!$output->getUser()->isLogged())
        {
            require 'logowanie.php';
            die();
        }
        else
        {
            $html = $output->run();
        }
    } catch (ViewException $e) {
    
        LogManager::log(LOG_ERR, '['.__FILE__.'] Output instantiate/run error: '.$e->getMessage(), $e);
        $html = CommonUtils::getViewExceptionMessage($e);
    } catch (Exception $e) {
        
        LogManager::log(LOG_ERR, '['.__FILE__.'] Output instantiate/run unhandled exception: '.$e->getMessage(), $e);
        $html = CommonUtils::getServerErrorMsg();
    }
    
    echo '<html>';
    HelpersUI::addHtmlBasicHeaders(HelpersUI::PATH_LEVEL_1);
    echo '<body>';
    echo $html;
    echo '</body></html>';