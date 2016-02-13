<?php

    require_once '../conf.php'; 
    require_once 'api/StartPracaSniper.php';

    session_start();
    
    if (isset($_SESSION['uzytkownik']) && $_SESSION['uzytkownik'] == 'bartek')
    {
        $sniper = new StartPracaSniper();
        $sniper->query();
    }