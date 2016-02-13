<?php 
session_start();
require_once '../conf.php';
require("f_xajax.php");?>  
<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
  <script language="javascript" src="../js/utils.js"></script>
  <script language="javascript" src="../js/validations.js"></script>
  <script language="javascript">
        function fill_address(tabName, selName, hidName)   //
        {
            xajax_GetComboContentById('tdAddress', 'innerHTML', 'id_miejscowosc', document.getElementById("miejscowosc_id").value, tabName, selName, hidName);
        }
  </script>
<link href="../css/layout.css" rel="stylesheet" type="text/css">
<?php $xajax->printJavascript("xajax/"); ?>
<title>Oddziały klientów</title>
</head>
<body> 
<?php
    // ś ą

    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
        require("../naglowek.php");
        require_once 'vaElClass.php';
        require_once 'ui/UtilsUI.php';
        include_once("../prawa_strona/f_image_operations.php");
        include_once("dal/klient.php");
	    
        //object of a class responsible for controls like number, text, date, etc
        $controls = unserialize($_SESSION['controls']);
        $htmlControls = new HtmlControls();
        //do commenta potem

        //hidden with oddzial id for editing etc
        $hidden_id = 'id_oddzial';
        //names of form submits
        $insert = 'insert_oddzial';
        $update = 'update_oddzial';
        $delete = 'erase_oddzial';
        $edit = 'edit_oddzial';
        //dal object inside those controls handles db connection
        //bussines logic layer classes responsible for table name and primary key info
        $db = new oddzial();
        $klient = new klient();
        $firma = new firma();
        $msc_biuro = new miejscowoscBiuro();
        $msc_slow_biuro = new mscBiuro();
        $adres_biuro = new adresBiuro();
        $zawod = new grupyZawodowe();
        $stawki = new stawkiOddzial();
        
        if (isset($_GET['id']))
        {
            $_SESSION['klient_id'] = $_GET['id'];
        }
        //update is missing
        if (isset($_POST[$update]))
        {
            //make sure no collision is present and update
            $testQuery = "select id from ".$db->tableName." where ".$db->tableName.".".$db->tableId." != ".$_POST[$hidden_id]."
            and ".$db->tableName.".id_klient = ".$_SESSION['klient_id']."
            and (lower(nazwa) = lower('".$_POST['oddzial']."') or lower(nazwa_alt) = lower('".$_POST['oddzial_alt']."'));";
            
            $result = $controls->dalObj->pgQuery($testQuery);
            if (pg_num_rows($result) == 0)
            {
                $wiekowe = 0;
                if (isset($_POST['wiekowe'])) $wiekowe = 1;
                //for update of a referencing collumns we use id's that are hidden in hiddens :P
                $query = "update ".$db->tableName." set nazwa = '".$_POST['oddzial']."', nazwa_alt = '".$_POST['oddzial_alt']."',
                stawka = '".$_POST['stawka']."', wiekowe = '".$wiekowe."', stanowisko = '".$_POST['id_gr_zaw']."',
                id_biuro = ".$_POST['miejscowosc_id'].",adres_biuro = ".$_POST['adres_biuro_id']." 
                where ".$db->tableName.".".$db->tableId." = ".$_POST[$hidden_id].";";
                //if wages for youngers were unchecked we delete if there are any in the database
                if ($wiekowe == 0)
                {
                    $query .= "delete from ".$stawki->tableName." where id_oddzial = ".$_POST[$hidden_id].";";
                }
                
                $result = $controls->dalObj->pgQuery($query);
            }
            else
            {
                echo 'Kolizja w nazwie oddziałów.';
            }
        }
        if (isset($_POST[$delete]))
        {
            $query = "delete from ".$db->tableName." where ".$db->tableName.".".$db->tableId." = ".$_POST[$hidden_id].";";
            $result = $controls->dalObj->pgQuery($query);
        }
        if (isset($_POST[$edit]))
        {
            $query = "select ".$db->tableName.".".$db->tableId." as id,".$klient->tableName.".nazwa as klient,".$db->tableName.".nazwa,
            ".$db->tableName.".nazwa_alt,".$db->tableName.".stawka,".$db->tableName.".wiekowe,".$db->tableName.".stanowisko,
            ".$zawod->tableName.".nazwa as zawod,".$db->tableName.".id_biuro,".$msc_slow_biuro->tableName.".nazwa as biuro,
            ".$db->tableName.".adres_biuro,".$adres_biuro->tableName.".nazwa as adres,".$klient->tableName.".id_firma,
            ".$firma->tableName.".nazwa as firma
            from ".$db->tableName." 
            join ".$klient->tableName." on ".$db->tableName.".id_klient = ".$klient->tableName.".".$klient->tableId."
            join ".$zawod->tableName." on ".$db->tableName.".stanowisko = ".$zawod->tableName.".".$zawod->tableId."
            join ".$msc_biuro->tableName." on ".$db->tableName.".id_biuro = ".$msc_biuro->tableName.".".$msc_biuro->tableId."
            join ".$msc_slow_biuro->tableName." on ".$msc_biuro->tableName.".id_msc_biuro = ".$msc_slow_biuro->tableName.".".$msc_slow_biuro->tableId."
            join ".$adres_biuro->tableName." on ".$db->tableName.".adres_biuro = ".$adres_biuro->tableName.".".$adres_biuro->tableId."
            join ".$firma->tableName." on ".$klient->tableName.".id_firma = ".$firma->tableName.".".$firma->tableId."
            where ".$db->tableName.".".$db->tableId." = ".$_POST[$hidden_id]." order by ".$db->tableName.".nazwa asc;";
                      
            $result = $controls->dalObj->pgQuery($query);
            $row = pg_fetch_array($result);
            $checked = '';
            if ($row['wiekowe'] == 1)
            {
                $checked = "checked";
            }
            //sprawdzic czemu to tak muli to raz
            //walidacje js przed zezwoleniem na submit forma
            //przy update oddzialu jak odpadaja stawki wiekowe wywalamy definicje stawektabela i zamysl stawek idento
            
            //na selectcie randomowym podmiana id musi bycna onblur bo onchange nie ma miejsca kiedy klepiemy z palca 
            //chyba ze da sie wlozyc gdzies w jakiegos boola ze change mial miejsce programowo
            echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST"><table>';
            echo $controls->AddHidden($hidden_id, $hidden_id, '');
            echo $controls->AddSelectHelpHidden();
            echo '<tr><td>Klient:</td><td>';
            echo $controls->AddTextbox("klient", "klient", $row['klient'], 35, 35, "READONLY");
            echo '</td></tr><tr><td>Oddział:</td><td>';
            echo $controls->AddTextbox("oddzial", "oddzial", $row['nazwa'], 35, 35, "");
            echo '</td></tr><tr><td>Wersja skr.:</td><td>';
            echo $controls->AddTextbox("oddzial_alt", "oddzial_alt", $row['nazwa_alt'], 25, 25, "");
            echo '</td></tr><tr><td>Stawka:</td><td>';
            echo $controls->AddTextbox("stawka", "stawka", $row['stawka'], 5, 5, "onChange='sprawdz_stawka(this);'");
            echo '</td></tr><tr><td>Wiekowe:</td><td>';                                    //showHideButton(document.getElementById(\'wiekowe\'), document.getElementById(\'stawki_wiek\'))
            echo $controls->AddCheckbox("wiekowe", "wiekowe", $checked, "onclick='showHideButton(\"wiekowe\", \"stawki_wiek\");'");  //we check if wiekowe is checked and we hide/unhide span stawki wiek
            echo '<span id="stawki_wiek">&nbsp;&nbsp;'.$controls->AddPopUpButton("Stawki", "stawki", "../edit/stawki_wiekowe.php?id=".$_POST[$hidden_id]."", 700, 500).'</span>';
            if (!isset($checked))
            {
                echo '<script>document.getElementById("stawki_wiek").style.display="none";</script>';
            }
            echo '</td></tr><tr><td>Zawód:</td><td>';
            echo $controls->OccGroupControl("Wybierz", "wybor_gr", "grupa_zawodowa", "txt_gr_zaw", $row['zawod'],"id_gr_zaw", "hid_gr_zaw", $row['stanowisko'], "../prawa_strona/wybor_grupy_zaw.php", "Grupyzawodowe");
            echo '</td></tr><tr><td>Biuro '.$row['firma'].':</td><td>';     
            $query = "select ".$msc_biuro->tableName.".".$msc_biuro->tableId.", ".$msc_slow_biuro->tableName.".nazwa from ".$msc_biuro->tableName."
            join ".$msc_slow_biuro->tableName." on ".$msc_biuro->tableName.".id_msc_biuro = ".$msc_slow_biuro->tableName.".".$msc_slow_biuro->tableId." where id_firma = ".$row['id_firma']." order by nazwa asc;";
            echo $controls->AddSelectRandomQuery("miejscowosc", "miejscowosc", "", $query, $row['biuro'], "miejscowosc_id", "nazwa", "id", "fill_address('".$adres_biuro->tableName."','".$adres_biuro->tableName."','adres_biuro_id');");
            echo '</td></tr><tr><td>Adres biura '.$row['firma'].':</td><td id="tdAddress">';
            echo $controls->AddSelectRandomQuery("adres_biuro", "adres_biuro", "", "select ".$adres_biuro->tableId.", nazwa from ".$adres_biuro->tableName." where id_miejscowosc = ".$row['id_biuro']." order by nazwa asc;", $row['adres'], "adres_biuro_id", "nazwa", "id", "");      
            echo '</td></tr><tr><td>';
            //dostawic 2 selecty, przetworzyc stawki, dokonczyc warunki zatrudnienia
            echo '</td></tr><tr><td>';
            echo $controls->AddPopUpButton("Warunki", "warunki", "../edit/warunki_zatrudnienia.php?id=".$_POST[$hidden_id]."", 700, 500);
            echo '</td></tr><tr><td>';
            echo $controls->AddSubmit($update, $row['id'], "Aktualizuj.", "onClick='".$hidden_id.".value=this.id; adres_biuro_id.value=adres_biuro.options[adres_biuro.selectedIndex].id;'");

            echo '</td></tr></table></form>';
        }
        if (isset($_POST[$insert]))
        {
            $testQuery = "select id from ".$db->tableName." where id_klient = ".$_SESSION['klient_id']." and (lower(nazwa) = lower('".$_POST['oddzial']."') 
            or lower(nazwa_alt) = lower('".$_POST['oddzial_alt']."'));";
            $result = $controls->dalObj->pgQuery($testQuery);
            if (pg_num_rows($result) == 0)
            {
                if ($_POST['wiekowe'])
                {
                    $st_wiekowe = 1;
                }
                else
                {
                    $st_wiekowe = 0;
                }
                $query = "INSERT into ".$db->tableName." values (nextval('".$db->tableName."_".$db->tableId."_seq'), 
                ".$_SESSION['klient_id'].", '".$_POST['oddzial']."', '".$_POST['oddzial_alt']."', 
                '".$_POST['stawka']."', '".$st_wiekowe."',".$_POST['id_gr_zaw'].",".$_POST['miejscowosc_id'].", ".$_POST['adres_biuro_id'].");";

                $result = $controls->dalObj->pgQuery($query);
                $query = "select currval('".$db->tableName."_".$db->tableId."_seq');";
                $wynik = $controls->dalObj->pgQuery($query);
                $wiersz = pg_fetch_array($wynik);
                echo "Oddział został zapisany w systemie pod numerem ".$wiersz[0].".";
            }
            else
            {
                echo 'Oddział widnieje w systemie.';
            }
        }
        
        if (!isset($_POST[$edit]))
        {
            $query = "select ".$db->tableName.".".$db->tableId." as id,".$klient->tableName.".nazwa as klient,".$db->tableName.".nazwa as oddzial
            from ".$db->tableName." join ".$klient->tableName." on ".$db->tableName.".id_klient = ".$klient->tableName.".".$klient->tableId."
            where ".$db->tableName.".id_klient = ".$_SESSION['klient_id']." order by ".$db->tableName.".nazwa asc;";
            
            //stara architektura - jak dotad nie ma lepsiejszego sposobu, przewiduje, ze klient no 1 jest systemowy
            //dodatkowo wakat no 1 tez jestsystemowy :P
            
            $result = $controls->dalObj->pgQuery($query);
            $ile = pg_num_rows($result);
            
            echo "<form action='".$_SERVER['PHP_SELF']."' method='POST'><table class='gridTable' border='0' cellspacing='0'>";
            echo valControl::_RowsCount($ile);
            echo $controls->AddHidden($hidden_id, $hidden_id, '');
            echo '<tr>';
            if (isset($_SESSION['edycja_rekordu']))
            {
                echo "<th>Edycja</th>";
            }
            //naglowek kasowania
            if (isset($_SESSION['kasowanie_rekordu']))
            {
                echo "<th>Kasowanie</th>";
            }
            $odlamki_nag = explode(",","Opis wewnętrzny, Id, Klient, Oddział");
            setHeadingRow($odlamki_nag);
            echo '</tr>';
            $count = 0;
            while ($wiersz = pg_fetch_assoc($result))
            {
                $count++;
                $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
                echo "<tr class='".$css."'>";
                if (isset($_SESSION['edycja_rekordu']))
                {
                     echo '<td nowrap align="CENTER">';
                     echo $controls->AddTableSubmit($edit, $wiersz['id'], "Edytuj.", "onClick='".$hidden_id.".value=this.id;'");
                     echo '</td>';
                }
                //przycisk kasowania, zasada okreslania id osoby jak powyzej
                if (isset($_SESSION['kasowanie_rekordu']))
                {
                     echo '<td nowrap align="CENTER">';
                     echo $controls->AddTableSubmit($delete, $wiersz['id'], "Kasuj.", "onClick='".$hidden_id.".value=this.id;'");
                     echo '</td>';
                }
                
                echo '<td nowrap align="CENTER">';
                echo $htmlControls->_AddNoPrivilegeSubmit('', '', 'Wewnetrzny opis pracy', '', '
                onclick="var url = \'../prawa_strona/wewn_opis_pracy.php?'.Model::COLUMN_OPR_ID.'='.$wiersz['id'].'\';
                window.open(url, \'wewnetrzny_opis_pracy\', \'toolbar=no, scrollbars=yes, width=750,height=650\');"', 'button');
                echo '</td>';
                
                addRowsToTable($wiersz);
                echo '</tr>';
            }  
            echo '</table></form>';

            echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST"><table>';
            echo $controls->AddHidden($hidden_id, $hidden_id, '');
            echo $controls->AddSelectHelpHidden();
            echo '<tr><td>Nazwa oddziału:</td><td>';
            echo $controls->AddTextbox("oddzial", "oddzial", "", 45, 45, "onChange='sprawdz_klienta(this);'");
            echo '</td><tr><td>Skrócona nazwa oddziału:</td><td>';
            echo $controls->AddTextbox("oddzial_alt", "oddzial_alt", "", 25, 25, "onChange='sprawdz_klienta(this);'");
            echo '</td><tr><td>Stawka:</td><td>';
            echo $controls->AddTextbox("stawka", "stawka", "", 5, 5, "onChange='sprawdz_stawka(this);'");
            
            echo '</td></tr><tr><td>Wiekowe:</td><td>';
            echo $controls->AddCheckbox("wiekowe", "wiekowe", "", "");
            
            echo '</td></tr><tr><td>Zatrudniana grupa zawodowa:</td><td>';
            echo $controls->OccGroupControl("Wybierz", "wybor_gr", "grupa_zawodowa", "txt_gr_zaw", "","id_gr_zaw", "hid_gr_zaw", "", "../prawa_strona/wybor_grupy_zaw.php", "Grupyzawodowe");
            //tu jest juz zle, z id klienta wynika biuro obslugujace, mamy dostarczyc miejscowosc biura i adres w obrebie miejscowosci
            //pobieramy miejscowosci dla danego biura obslugujacego, co pobieramy z tabeli klient na id_klient, ktore mamy w sesji
            
            $query = "select ".$firma->tableName.".".$firma->tableId.", ".$firma->tableName.".nazwa as nazwa from ".$firma->tableName."
            join ".$klient->tableName." on ".$klient->tableName.".id_firma = ".$firma->tableName.".".$firma->tableId." 
            where ".$klient->tableName.".".$klient->tableId." = ".$_SESSION['klient_id'].";";
            $result = $controls->dalObj->pgQuery($query);
            $row = pg_fetch_array($result);
            
            echo '</td></tr><tr><td>Biuro '.$row['nazwa'].':</td><td>';     
            $query = "select ".$msc_biuro->tableName.".".$msc_biuro->tableId.", ".$msc_slow_biuro->tableName.".nazwa from ".$msc_biuro->tableName."
            join ".$msc_slow_biuro->tableName." on ".$msc_biuro->tableName.".id_msc_biuro = ".$msc_slow_biuro->tableName.".".$msc_slow_biuro->tableId." where id_firma = ".$row[$firma->tableId]." order by nazwa asc;";
            echo $controls->AddSelectRandomQuery("miejscowosc", "miejscowosc", "", $query, "", "miejscowosc_id", "nazwa", "id", "fill_address('".$adres_biuro->tableName."','".$adres_biuro->tableName."','adres_biuro_id');");
            echo '</td></tr><tr><td>Adres biura '.$row['nazwa'].':</td><td id="tdAddress">';
            //zamienic onchange z onblurem w select cie, ogolnie onchange wystarczy zeby zmieniac pozniej id, onblur nie jest juz potrzebny, na onblur
            //dam logike rozmyta, bo nie ma wyjscia, ewentualnie wymyslic cos jeszzce       
            echo '</td></tr><tr><td>';
            echo $controls->AddSubmit($insert, $insert, "Dodaj.", "onClick='utils.setHiddenOnLoad(\"adres_biuro_id\", \"adres_biuro\");'");

            echo '</td></tr><tr><td>';
            echo $controls->AddSubmit("", "", "Zamknij.", "onClick='window.close();'");
            echo '</td></tr></form></table>';
        }
        require("../stopka.php");
    }
?>
</body>
</html>