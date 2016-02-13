<?php
    set_include_path(get_include_path().
    PATH_SEPARATOR.'wsparcie'.
    PATH_SEPARATOR.'../wsparcie'.
    PATH_SEPARATOR.'../ui'.
    PATH_SEPARATOR.'./../'
    );

    $path = '';

    $dzis = date('Y-m-d');

    if (!defined('ID_OSOBA'))
        define ('ID_OSOBA', 'id_osoba');
        
    if (!defined('DESC_SEPARATOR'))
        define ('DESC_SEPARATOR', '-');
        
    if (!defined('ID_ZATRUDNIENIE'))
        define ('ID_ZATRUDNIENIE', 'id_zatrudnienie');
    if (!defined('UZYTKOWNIK_ID'))
        define ('UZYTKOWNIK_ID', 'id_uzytkownik');
    
    require_once 'config.const.php';    
    require_once 'common-config.php';    
    require_once 'ui/HelpersUI.php';
    
    $con_str = DATABASE_CONNECTION_STRING;
    
    //DEVEL only !
    if (!defined('DEVELOPMENT'))
        define('DEVELOPMENT', 1);
?>
