<?php

    require_once '../conf.php';
    require_once 'vaElClass.php';
    require_once '../bll/FileManager.php';
    
    session_start();
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {

        $id_osoba = Utils::PodajIdOsoba();
        
        $headers = array(
            'jpg' => 'Content-type: image/jpeg',
            'jpeg' => 'Content-type: image/jpeg',
            'png' => 'Content-type: image/png',
            'gif' => 'Content-type: image/gif',
            'pdf' => 'Content-type: application/pdf',
        );
        
        $fileManager = new FileManager();
        if (isset($_GET['name']))
        {
            $file = (basename($_GET['name']));
            $ext = strtolower(str_replace('.', '', strpbrk($file, '.')));

            if (!in_array($ext, FileManager::$allowedExts))
                die('Unsupported extension.');
                
            if (!$fileManager->scanDocExists($id_osoba, $file))
                die('Not found: ');
            
            if (isset($headers[$ext]))
                header($headers[$ext]);
            
            header('Cache-Control:    no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            header('Pragma: no-cache');
            header('Etag: '.md5(uniqid(microtime(), true)));    
            
            readfile(FileManager::getTarget($id_osoba).$file);
            return;            
        }
        
        if (isset($_GET['pit']))
        {
            $file = ($_GET['pit']);
            $elements = explode('.', $file);
            $ext = strtolower(array_pop($elements));
            
            if (!in_array($ext, FileManager::$allowedExts))
                die('Unsupported extension.');
            
            if (!$fileManager->taxReadTargetExists($file))
                return;
            
            if (isset($headers[$ext]))
                header($headers[$ext]);
            
            header('Cache-Control:    no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            header('Pragma: no-cache');
            header('Etag: '.md5(uniqid(microtime(), true)));    
            
            readfile(FileManager::getTaxReadTarget($file));
            return;            
        }
        
	    if ($fileManager->scanDocExists($id_osoba, FileManager::getAnkietaName($id_osoba)))
        {
            header('Content-type: application/pdf');
            header('Cache-Control:    no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            header('Pragma: no-cache');
            header('Etag: '.md5(uniqid(microtime(), true)));
            
            readfile(FileManager::getTarget($id_osoba).FileManager::getAnkietaName($id_osoba));
            //clearstatcache();
        }

        require("../stopka.php");
    }
?>