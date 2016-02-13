<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="js/script.js"></script>
  <script language="javascript" src="js/utils.js"></script>
  <script language="javascript" src="js/validations.js"></script>
<link href="css/layout.css" rel="stylesheet" type="text/css">
</head>
<?php
    @session_start();
    if (empty($_SESSION['uzytkownik']))
    {
        require("log_in.php");
    }
    else
    {
        require("naglowek.php");
        include_once "vaElClass.php";
        $controls = new valControl();
	    require("conf.php");
        $database = pg_connect($con_str);
        if (isset($_POST['dodaj']))
        {
            if (isset($_POST['biuro_t_id']) && ($_POST['e-mail'] != ""))   
            {
                $zapytanie_insert = "insert into tagesraport values (".$_POST['biuro_t_id'].", '".addslashes($_POST['e-mail'])."');";   
                $query_insert = pg_query($database, $zapytanie_insert);
            }
        }
        //dodaj_adres_ankieta
        if (isset($_POST['dodaj_adres_ankieta']))
        {
            if (isset($_POST['biuro_a_id']) && ($_POST['adres_email'] != ""))   
            {
                $zapytanie_insert = "insert into email_ankieta (id_msc_biura, email) values (".$_POST['biuro_a_id'].", '".addslashes($_POST['adres_email'])."');";   
                $query_insert = pg_query($database, $zapytanie_insert);
            }
        }
        if (isset($_POST['dodaj_b']))
        {
            if ($_POST['email_b'] != "")   
            {
                $zapytanie_insert_bartus = "insert into bartus values ('".addslashes($_POST['email_b'])."');";   
                $query_insert_bartus = pg_query($database, $zapytanie_insert_bartus);
            }
        }
        if (isset($_POST['kasuj']))
        {
            $zapytanie_delete = "delete from tagesraport where email = '".$_POST['kasuj']."';";   
            $query_delete = pg_query($database, $zapytanie_delete);
        }
        //email_ankieta_id
        //usun_mail_ankieta
        if (isset($_POST['usun_mail_ankieta']))
        {
            $zapytanie_delete = "delete from email_ankieta where id = ".$_POST['email_ankieta_id'].";";
            $query_delete = pg_query($database, $zapytanie_delete);
        }
        if (isset($_POST['kasuj']))
        {
            $zapytanie_delete = "delete from tagesraport where email = '".$_POST['kasuj']."';";   
            $query_delete = pg_query($database, $zapytanie_delete);
        }
        if (isset($_POST['kasuj_b']))
        {
            $zapytanie_delete_bartus = "delete from bartus where email = '".$_POST['kasuj_b']."';";   
            $query_delete_bartus = pg_query($database, $zapytanie_delete_bartus);
        }
        
        echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
        echo $controls->AddSubmit('pokaz_mail_tagesraport', 'id_pokaz_mail_tagesraport', 'Pokaż adresy wysyłek tagesraportów.', '');
        echo '</form>';
        
        if (isset($_POST['pokaz_mail_tagesraport']))
        {
            $zapytanie = "select msc_biura.nazwa as biuro, tagesraport.email from tagesraport join msc_biura on tagesraport.id_msc_biura = msc_biura.id order by msc_biura.nazwa desc;";
            $query = pg_query($database, $zapytanie);
            echo("<form action = '".$_SERVER['PHP_SELF']."' method = 'POST'>");
            echo $controls->AddHidden('id_kasuj', 'kasuj', ''); 
            echo $controls->AddHidden('id_kasuj_b', 'kasuj_b', ''); 
            echo '<table align="CENTER" class="gridTable" border="0" cellspacing="0">';
            $count = 0;
            while ($row = pg_fetch_array($query))
            {
                $count++;
                $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
                echo "<tr class='".$css."'><td>".$row['biuro']."</td><td>".$row['email']."</td><td>";
                echo $controls->AddSubmit($row['email'], 'id', 'Kasuj', JsEvents::ONCLICK.'="kasuj.value = this.name;"'); 
                echo "</td></tr>";   
            }
            echo '</table>';
        }
        
        echo '<hr /><form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
        echo $controls->AddSubmit('pokaz_mail_ankieta', 'id_pokaz_mail_ankieta', 'Pokaż adresy wysyłek ankiet.', '');
        echo '</form>';
        
        if (isset($_POST['pokaz_mail_ankieta']))
        {
            $zapytanie = "select email_ankieta.id, msc_biura.nazwa as biuro, email_ankieta.email from email_ankieta join msc_biura on email_ankieta.id_msc_biura = msc_biura.id order by msc_biura.nazwa desc;";
            $query = pg_query($database, $zapytanie);
            echo("<form action = '".$_SERVER['PHP_SELF']."' method='POST'>");
            echo $controls->AddHidden('email_ankieta_id', 'email_ankieta_id', ''); 
            echo '<table align="CENTER" class="gridTable" border="0" cellspacing="0">';
            $count = 0;
            while ($row = pg_fetch_array($query))
            {
                $count++;
                $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
                echo "<tr class='".$css."'><td>".$row['biuro']."</td><td>".$row['email']."</td><td>";
                echo $controls->AddSubmit('usun_mail_ankieta', $row['id'], 'Kasuj.', JsEvents::ONCLICK.'="email_ankieta_id.value = this.id;"');
                echo "</td></tr>";   
            }
            echo '</table></form>';
        }
        
        echo("<hr align = 'CENTER' />");
        echo("<form action = '".$_SERVER['PHP_SELF']."' method='POST'>");
        echo '<table align="CENTER" class="gridTable" border="0" cellspacing="0">';
        $zapytanie_bartus = "select email from bartus;";
        $query_bartus = pg_query($database, $zapytanie_bartus);
        $count = 0;
        while ($row_bartus = pg_fetch_array($query_bartus))
        {
            $count++;
            $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
            echo "<tr class='".$css."'><td>Bartuś</td><td>".$row_bartus['email']."</td><td>";
            echo $controls->AddSubmit($row_bartus['email'], 'id', 'Kasuj', JsEvents::ONCLICK.'="kasuj_b.value = this.name;"');
            echo "</td></tr>";    
        }
        echo '</table></form>';
        
        echo("<hr align = 'CENTER' />");
        echo("<form action = '".$_SERVER['PHP_SELF']."' method='POST'>");
        $zapytanie_biura = "select id, nazwa from msc_biura order by nazwa asc;";
        
        echo("<div align = 'CENTER'>");
        echo 'Dodaj adres do wysyłek tagesraportu: ';
        echo $controls->AddSelectRandomQuery('biuro', 'id_biuro', '', $zapytanie_biura, null, 'biuro_t_id');
        
        echo $controls->AddTextbox('e-mail', 'id_e-mail', '', '40', '20', '');
        echo $controls->AddSubmit('dodaj', 'id_dodaj', 'Dodaj', '');
        echo("</div>");
        echo("</form>");
        
        echo("<hr align = 'CENTER' />");
        echo("<form action = '".$_SERVER['PHP_SELF']."' method='POST'>");


        echo("<div align = 'CENTER'>");
        echo 'Dodaj adres do wysyłek ankiet: ';
        echo $controls->AddSelectRandomQuery('biuro', 'id_biuro', '', $zapytanie_biura, null, 'biuro_a_id');

        echo $controls->AddTextbox('adres_email', 'id_adres_email', '', '40', '20', '');
        echo $controls->AddSubmit('dodaj_adres_ankieta', 'id_dodaj_adres_ankieta', 'Dodaj', '');
        echo("</div>");
        echo("</form>");
        
        echo("<hr />");
        echo("<form action = '".$_SERVER['PHP_SELF']."' method='POST'>");
        echo("<div align = 'CENTER'>");
        echo "Bartuś:"; 
        echo $controls->AddTextbox('email_b', 'id_email_b', '', '30', '20', '');
        echo $controls->AddSubmit('dodaj_b', 'id_dodaj_b', 'Dodaj', '');
        echo("</div>");
        echo("</form>");

        require("stopka.php");
    }
?>
</html>
