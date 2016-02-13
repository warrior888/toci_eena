<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
<link href="../css/layout.css" rel="stylesheet" type="text/css"></head>
</head>
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

        if (isset($_POST['zmien_widocznosc']))
        {
            if ($_POST['zmien_widocznosc'] == 'Pokaż.')
            {
                $zapytanie = 'update zrodlo set widoczne = true where id = '.$_POST['element_slownika'].';';
                $kontrolki->dalObj->pgQuery($zapytanie);
            }
            else
            {
                $zapytanie = 'update zrodlo set widoczne = false where id = '.$_POST['element_slownika'].';';
                $kontrolki->dalObj->pgQuery($zapytanie);
            }
        }
        
        $zapytanie = 'select id, nazwa, widoczne from zrodlo order by id asc;';
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
            echo '<td>'.$wiersz['nazwa'].'</td><td>'.$fraza.'</td><td>';
            echo $kontrolki->AddSubmit('zmien_widocznosc', $wiersz['id'], $przycisk, 'onclick="element_slownika.value = this.id;"');
            echo '</td></tr>';
        }
        echo '</table></form>';
        require("../stopka.php");
    }
?>
</html>
