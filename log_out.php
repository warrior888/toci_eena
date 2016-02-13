<?php
    session_start();
    unset($_SESSION);
    session_destroy();
    //echo("Zastales wylogowany <br />");
    //echo("Aby ponownie sie zalogowac kliknij <a href=\"log_in.php\">tutaj</a>");
    require("index.php");
    //header('Location: index.php');
?>
