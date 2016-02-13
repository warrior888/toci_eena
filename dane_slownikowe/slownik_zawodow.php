<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
<link href="../css/layout.css" rel="stylesheet" type="text/css"></head>
</head>
<body onscroll="SaveScroll(document.location.href, document.body.scrollTop);" onload="AutoScrollDownCentral(document.location.href);">
<?php
    @session_start();
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
        require("../naglowek.php");
	    require("../conf.php");
        
        $kontrolki = new valControl();

        if (isset($_POST['aktualizuj']))
        {
            $zapytanie = 'update zawod set nazwa = \''.$_POST['text_aktualizuj'].'\' where id = '.$_POST['element_slownika'].';';
            $kontrolki->dalObj->pgQuery($zapytanie); 
        }
        
        if (isset($_POST['zmien_widocznosc']))
        {
            if ($_POST['zmien_widocznosc'] == 'Pokaż.')
            {
                $zapytanie = 'update zawod set widoczne = true where id = '.$_POST['element_slownika'].';';
                $kontrolki->dalObj->pgQuery($zapytanie);
            }
            else
            {
                $zapytanie = 'update zawod set widoczne = false where id = '.$_POST['element_slownika'].';';
                $kontrolki->dalObj->pgQuery($zapytanie);
            }
        }
        
        $zapytanie = 'select id, nazwa, widoczne from zawod order by nazwa asc;';
        $wynik = $kontrolki->dalObj->PobierzDane($zapytanie);
        
        echo '<form method="POST" action='.$_SERVER['PHP_SELF'].'><table class="gridTable" border="0" cellspacing="0">';
        echo $kontrolki->AddHidden('element_slownika', 'element_slownika', '');
        $count = 0;
        foreach ($wynik as $wiersz)
        {
            $count++;
            $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
            $fraza = 'Niewidoczne.';
            $przycisk = 'Pokaż.';
            if ($wiersz['widoczne'] == 't')
            {
                $fraza = 'Widoczne.';
                $przycisk = 'Ukryj.';
            }
            echo '<tr class="'.$css.'">';
            echo '<td>';
            if (isset($_POST['edytuj']) && $wiersz['id'] == $_POST['element_slownika'])
            {
                echo $kontrolki->AddTextbox('text_aktualizuj', 'id_text_aktualizuj', $wiersz['nazwa'], 200, 120, '');
            }
            else
            {
                echo $wiersz['nazwa'];
            }
            echo '</td><td>'.$fraza.'</td><td>';
            if (isset($_POST['edytuj']) && $wiersz['id'] == $_POST['element_slownika'])
            {
                echo $kontrolki->AddSubmit('aktualizuj', $wiersz['id'], 'Aktualizuj', 'onclick="element_slownika.value = this.id;"');
            }
            else
            {
                echo $kontrolki->AddSubmit('edytuj', $wiersz['id'], 'Edytuj', 'onclick="element_slownika.value = this.id;"');
            }
            echo '</td><td>';
            echo $kontrolki->AddSubmit('zmien_widocznosc', $wiersz['id'], $przycisk, 'onclick="element_slownika.value = this.id;"');
            echo '</td></tr>';
        }
        echo '</table></form>';
        require("../stopka.php");
    }
?>
</body>
</html>
