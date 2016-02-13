<?php
    ini_set ('display_errors', 1);
    ini_set ('error_reporting', E_ALL);
    
    $miejscowosc = iconv('UTF-8', 'ISO-8859-2', 'żźćśąśłółf3eqbrwĆŹŚĘ'); 
    echo $miejscowosc;
?>