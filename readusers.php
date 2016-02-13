<?php

    ini_set ('display_errors', 1);
    ini_set ('error_reporting', E_ALL);
    
    $dir = 'users';
    
    $dirhandle = opendir($dir);
    $outputhandle = fopen('subs.txt', 'w');
    while ($file = readdir($dirhandle))
    {
        $file = str_replace($dir.'/', '', $file);
        fputs($outputhandle, $file."\n");
    }
    fclose($outputhandle);