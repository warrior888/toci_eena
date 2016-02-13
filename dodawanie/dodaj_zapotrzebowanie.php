<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/jquery.js"></script>
  <script language="javascript" src="../js/script.js"></script>
  <script language="javascript" src="../js/utils.js"></script>
  <script language="javascript" src="../js/validations.js"></script>
  <script>
function fill_dep ()
{
    $.get (<?php $_SERVER['SERVER_NAME']; ?>'/ajaxapi/?clientId=' + document.getElementById("klient_id").value, '', FillClient, 'html');
}

function FillClient (list)
{
    var container = document.getElementById('selectBranch');
    container.innerHTML = list;
}
  </script>
  <link href="../css/layout.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php
    session_start();

    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
        require("../naglowek.php");
	    require("../conf.php");
        include_once("../prawa_strona/f_image_operations.php");
        include_once("../dal/klient.php");
        include_once("dal/DALWakaty.php");
        include_once("adl/User.php");
        
        $controls = unserialize($_SESSION['controls']);
        
        $hidden_id = 'id_wakat';
        //names of form submits
        $insert = 'insert_wakat';
        $update = 'update_wakat';
        $delete = 'erase_wakat';
        $edit = 'edit_wakat';
        
        $db = new wakaty();
        $klient = new klient();
        $oddzial = new oddzial();
        
        if (isset($_POST[$insert]))
        {
            $dokladny = false;
            $widoczne = false;
            if (isset($_POST['dokladny']))
            {
                $dokladny = true;
            }
            
            if (isset($_POST['widoczne_www']))
            {
                $widoczne = true;
            }
            
            $user = User::getInstance();
            
            $dalWakaty = new DALWakaty();
            
            $data = array(
                Model::COLUMN_WAK_ID_KLIENT        => $_POST['klient_id'],
                Model::COLUMN_WAK_ID_ODDZIAL       => $_POST['oddzial_id'],
                Model::COLUMN_WAK_ID_KONSULTANT    => $user->getUserId(),
                Model::COLUMN_WAK_DATA_WPISU       => $_POST['data_wpisu'],
                Model::COLUMN_WAK_DATA_WYJAZDU     => $_POST['data_wyjazdu'],
                Model::COLUMN_WAK_DOKLADNY         => $dokladny,
                Model::COLUMN_WAK_WIDOCZNE_WWW     => $widoczne,
                Model::COLUMN_WAK_ILOSC_KOBIET     => $_POST['ilosc_kobiet'],
                Model::COLUMN_WAK_ILOSC_MEZCZYZN   => $_POST['ilosc_mezczyzn'],
                Model::COLUMN_WAK_ILOSC_TYG        => $_POST['czas_pobytu'],
            );

            /*$query = "INSERT INTO ".$db->tableName." VALUES 
            (nextval('".$db->tableName."_".$db->tableId."_seq'),".$_POST['klient_id'].",
            ".$_POST['oddzial_id'].",'".$_POST['data_wyjazdu']."','".$_POST['ilosc_kobiet']."','"
            .$_POST['ilosc_mezczyzn']."','".$_POST['czas_pobytu']."',(select id from uprawnienia where imie_nazwisko = 
            '".$_POST['konsultant']."'),'".$_POST['data_wpisu']."','".$dokladny."');";
*/
            $testQuery = "select id from ".$db->tableName." where id_oddzial = ".$_POST['oddzial_id']." and data_wyjazdu = '".$_POST['data_wyjazdu']."';";
            $result = $controls->dalObj->pgQuery($testQuery);
            
            //nielogiczna date wyjazdu walidujemy js
            if (pg_num_rows($result) == 0)
            {
                $id = $dalWakaty->set($data);
                echo "Zapotrzebowanie zostało zapisane w systemie pod numerem ".$id.".";
                //echo("<script>parent.frames[0].document.location.reload();</script>");
            }
            else
            {
                echo 'Upewnij się, czy wprowadzany wakat nie jest już w systemie.';
            }
        }
	    echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
        echo $controls->AddSelectHelpHidden();
        
        echo '<table><tr><td>Klient:</td><td>';
        echo $controls->AddSelectRandomQuery("klient", "klient", "", "select ".$klient->tableId.",nazwa from ".$klient->tableName." where ".$klient->tableId." !='1' order by nazwa asc;", "", "klient_id", "nazwa", "id", "fill_dep('".$oddzial->tableName."','".$oddzial->tableName."','oddzial_id');");	                    
        echo '<tr id="trBranch"><td>Dział:</td><td id="selectBranch"></td></tr></td></tr><tr><td>Data wyjazdu:</td><td>';
        echo $controls->AddDateboxFuture("data_wyjazdu", "data_wyjazdu_wakat", "", 10, 10);
	    echo '</td></tr><tr><td>Ilość kobiet:</td><td>';
        echo $controls->AddNumberbox("ilosc_kobiet", "ilosc_kobiet", "", 2, 3, "sprawdz_ilosc_osob(this);");
        
        echo '</td><td>Niekoniecznie: ';
        echo $controls->AddCheckbox('dokladny', 'id_dokladny', '', '');
	    echo '</td></tr><tr><td>Ilość mężczyzn:</td><td>';
        echo $controls->AddNumberbox("ilosc_mezczyzn", "ilosc_mezczyzn", "", 2, 3, "sprawdz_ilosc_osob(this);");
        
        echo '</td></tr><tr><td>Czas pobytu:</td><td>';
        echo $controls->AddNumberbox("czas_pobytu", "czas_pobytu", "", 2, 3, "sprawdz_tygodnie(this);");
        echo '</td></tr><tr><td>Widoczne www:</td><td>';
        echo $controls->AddCheckbox('widoczne_www', 'id_widoczne_www', '', '');
        
	    $zapytanie = "select imie_nazwisko from uprawnienia where nazwa_uzytkownika = '".$_SESSION['uzytkownik']."';";
        $wynik = $controls->dalObj->pgQuery($zapytanie);
	    $wiersz=pg_fetch_array($wynik);
	    echo '</td></tr><tr><td>Konsultant:</td><td><input class="formfield" type="text" name="konsultant" value="'.$wiersz['imie_nazwisko'].'" READONLY></td></tr>
	    <tr><td>Data zapisu:</td><td><input class="formfield" type="text" name="data_wpisu" value='.$dzis.' READONLY></td></tr>
	    <tr><td>';
        echo $controls->AddSubmit($insert, $insert, "Dodaj.", "onClick='utils.setHiddenOnLoad(\"klient_id,oddzial_id\", \"klient,".$oddzial->tableName."\");'");
        echo '</td></tr></table></form>';
        require("../stopka.php");
    }
?>
</body>
</html>
