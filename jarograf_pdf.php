<?php
     session_start();
?>
<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="js/script.js"></script>
  <script language="javascript" src="js/utils.js"></script>
  <script language="javascript" src="js/validations.js"></script>
<link href="css/layout.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php
    // ś ą
    if (empty($_SESSION['uzytkownik']))
    {
        require("log_in.php");
    }
    else
    {
        require("naglowek.php");
	    require("conf.php");
        require_once("vaElClass.php");
        require_once("dal.php");
        require_once("bll/FileManager.php");
        
        $controls = new valControl();
        $rok = date("Y") - 1;
        if (isset($_POST['zatwierdz']))
        {
            //jarograf/".$_SESSION['id']."/$rok
            $_POST['msc'] = str_replace(' ', '', $_POST['msc']);
            $_POST['msc'] = ucfirst(strtolower($_POST['msc']));
            $dirname = FileManager::getRawTaxDirPath($_POST['msc']);
            
            $dal = dal::getInstance();
            if ($handle = opendir($dirname))
            {
                $i = 0;
                $fileManager = new FileManager();
                while  ($file = readdir($handle)) 
                {
                    $dane = explode('.', $file);
                    $mulSoffi = explode('_', $dane[0]);
                    $soffi = (int)$mulSoffi[0];
                
                    if (strlen($soffi) == 9 && is_int($soffi))
                    {
                        $zapytanie = "select id from dokumenty where nip = '".$soffi."';";
                        $wynik = $dal->PobierzDane($zapytanie);
                        if (isset($wynik[0]['id']))
                        {
                            $osobaId = (int)$wynik[0]['id'];
                            $zapytanie = "select zatrudnienie.id_klient from zatrudnienie where id_osoba = ".$osobaId." 
                            and data_wyjazdu between '".$rok."-01-01' and '".$rok."-12-31' limit 1;";
                            $klient = $dal->PobierzDane($zapytanie);
                            if (!isset($klient[0]['id_klient']))
                            {
                                $zapytanie = "select zatrudnienie.id_klient from zatrudnienie where id_osoba = ".$osobaId." limit 1;";
                                $klient = $dal->PobierzDane($zapytanie);
                            }
                            if (!isset($klient[0]['id_klient']))
                            {
                                $klient[0]['id_klient'] = 1;
                            }
                            $klientId = (int)$klient[0]['id_klient'];

                            $zapytanie2 = "select count(plik) as ilosc from jarograf where id = '".$osobaId."' and rok = '".$rok."';";
                            $wynik2 = $dal->PobierzDane($zapytanie2);
                            $ilosc = (int)$wynik2[0]['ilosc'] + 1;

                            $name = "jarograf".$osobaId.$rok.$klientId.$ilosc.'.'.$dane[1];
                            $sourceFile = $dirname.'/'.$file;

                            $res = $fileManager->setTaxDoc($osobaId, $sourceFile, $name, $rok, $klientId, true);
                            if (!$res)
                            {
                                echo 'Błąd dla '.$osobaId.', '.$soffi.'<br />';
                            }
                        }
                        else
                        {
                            echo 'Nie znaleziono: '.$soffi.'<br />';
                            $i++;
                        }
                    }
                }
                echo '<br />Łącznie nie znaleziono '.$i.' osób.';
            }
        }
        echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'"><table><tr><td>';
        echo $controls->AddSelectHelpHidden();
        echo $controls->AddSelectRandomQuery('msc', 'msc', '', 'select id, nazwa from msc_biura order by nazwa asc;', '', 'msc_id', 'nazwa', 'id', '');
        echo '</td></tr><tr><td>';
        echo $controls->AddSubmit('zatwierdz', 'zatwierdz', 'Zatwierdź.', '');
        echo '</td></tr></table></form>';
        require("stopka.php");
    }
?>
</body>
</html>
