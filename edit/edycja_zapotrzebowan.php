<?php
    require_once '../conf.php';
    CommonUtils::outputBufferingOn();
    CommonUtils::SessionStart();

    echo '<html>';
    HelpersUI::addHtmlBasicHeaders(HelpersUI::PATH_LEVEL_1);
    echo '<body>';
    
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
        include_once("../prawa_strona/f_image_operations.php");
        include_once("../dal/klient.php");
        include_once 'dal/DALWakaty.php';
       
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
        
        //$controls = new valControl();
        
        if (isset($_POST[$update]))
        {
            //make sure no collision is present and update
            $testQuery = "select id from ".$db->tableName." where ".$db->tableId." != ".$_POST[$hidden_id]." and id_oddzial = ".$_POST['oddzial_id']." and data_wyjazdu = '".$_POST['data_wyjazdu']."';";
            $result = $controls->dalObj->pgQuery($testQuery);
            if (pg_num_rows($result) == 0)
            {
                $dalWakaty = new DALWakaty();
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
                
                $data = array(
                    Model::COLUMN_WAK_ID               => $_POST[$hidden_id],
                    Model::COLUMN_WAK_ID_ODDZIAL       => $_POST['oddzial_id'],
                    Model::COLUMN_WAK_DATA_WYJAZDU     => $_POST['data_wyjazdu'],
                    Model::COLUMN_WAK_DOKLADNY         => $dokladny,
                    Model::COLUMN_WAK_WIDOCZNE_WWW     => $widoczne,
                    Model::COLUMN_WAK_ILOSC_KOBIET     => $_POST['ilosc_kobiet'],
                    Model::COLUMN_WAK_ILOSC_MEZCZYZN   => $_POST['ilosc_mezczyzn'],
                    Model::COLUMN_WAK_ILOSC_TYG        => $_POST['czas_pobytu'],
                );
                
                $id = $dalWakaty->set($data);
                
                //for update of a referencing collumns we use id's that are hidden in hiddens :P
                
                /*$query = "update ".$db->tableName." set id_oddzial = '".$_POST['oddzial_id']."',
                data_wyjazdu = '".$_POST['data_wyjazdu']."', ilosc_kobiet = '".$_POST['ilosc_kobiet']."',
                ilosc_mezczyzn = '".$_POST['ilosc_mezczyzn']."', ilosc_tyg = '".$_POST['czas_pobytu']."'
                where ".$db->tableName.".".$db->tableId." = ".$_POST[$hidden_id].";";*/
                
                //$result = $controls->dalObj->pgQuery($query);
            }
            else
            {
                echo 'Upewnij siê, czy taki wakat nie jest ju¿ w systemie.';
            }
        }
        if (isset($_POST[$delete]))
        {
            $query = "delete from ".$db->tableName." where ".$db->tableName.".".$db->tableId." = ".$_POST[$hidden_id].";";
            $result = $controls->dalObj->pgQuery($query);
        }
        if (isset($_POST[$edit]))
        {
            $query = "select ".$db->tableName.".".$db->tableId.", ".$klient->tableName.".nazwa as klient, 
            ".$oddzial->tableName.".nazwa as oddzial, ".$db->tableName.".data_wyjazdu, ".$db->tableName.".ilosc_kobiet, 
            ".$db->tableName.".ilosc_mezczyzn, ".$db->tableName.".ilosc_tyg, ".$db->tableName.".dokladny, ".$db->tableName.".widoczne_www,  
            ".$klient->tableName.".".$klient->tableId." as klient_id
            from ".$db->tableName." 
            join ".$klient->tableName." on ".$db->tableName.".id_klient = ".$klient->tableName.".".$klient->tableId."
            join ".$oddzial->tableName." on ".$db->tableName.".id_oddzial = ".$oddzial->tableName.".".$oddzial->tableId." 
            where ".$db->tableName.".".$db->tableId." = ".$_POST[$hidden_id].";";
            
            $result = $controls->dalObj->pgQuery($query);
            $row = pg_fetch_array($result);
            $checked = '';
            $checkedWww = '';
            if ($row['dokladny'] == "t")
            {
                $checked = "CHECKED";
            }
            if ($row['widoczne_www'] == "t")
            {
                $checkedWww = "CHECKED";
            }
            echo '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
            echo $controls->AddSelectHelpHidden();
            echo $controls->AddHidden($hidden_id, $hidden_id, ''); 
            echo '<table class="gridTable" border="0" cellspacing="0"><tr><td>Klient:</td><td>';
            echo $controls->AddTextbox("klient", "klient", $row['klient'], 35, 35, "READONLY");                        
            echo '<tr id="trBranch"><td>Dzia³:</td><td id="selectBranch">';
            echo $controls->AddSelectRandomQuery("oddzial", "oddzial", "", "select ".$oddzial->tableId.",nazwa from ".$oddzial->tableName." where id_klient = ".$row['klient_id']." order by nazwa asc;", $row['oddzial'], "oddzial_id", "nazwa", "id", "");
            echo '</td></tr></td></tr><tr><td>Data wyjazdu:</td><td>';
            echo $controls->AddDateboxFuture("data_wyjazdu", "data_wyjazdu_wakat", $row['data_wyjazdu'], 10, 10);
            echo '</td></tr><tr><td>Ilo¶æ kobiet:</td><td>';
            echo $controls->AddNumberbox("ilosc_kobiet", "ilosc_kobiet", $row['ilosc_kobiet'], 2, 3, "sprawdz_ilosc_osob(this);");
            echo "</td><td>Niekoniecznie: ";
            echo $controls->AddCheckbox('dokladny', 'id_dokladny', $checked, '');
            echo '<tr><td>Ilo¶æ mê¿czyzn:</td><td>';
            echo $controls->AddNumberbox("ilosc_mezczyzn", "ilosc_mezczyzn", $row['ilosc_mezczyzn'], 2, 3, "sprawdz_ilosc_osob(this);");
            echo '</td></tr><tr><td>Czas pobytu:</td><td>';
            echo $controls->AddNumberbox("czas_pobytu", "czas_pobytu", $row['ilosc_tyg'], 2, 3, "sprawdz_tygodnie(this);");
            echo '</td></tr><tr><td>Widoczne www:</td><td>';
            echo $controls->AddCheckbox('widoczne_www', 'id_widoczne_www', $checkedWww, '');
            echo '</td></tr><tr><td>';
            echo $controls->AddSubmit($update, $row['id'], "Aktualizuj.", "onClick='".$hidden_id.".value=this.id; utils.setHiddenOnLoad(\"oddzial_id\", \"oddzial\");'");
            echo '</td></tr></table></form>';
        }

        $zapytanie = "select ".$db->tableName.".".$db->tableId.", ".$klient->tableName.".nazwa as klient, 
        ".$oddzial->tableName.".nazwa as oddzial, ".$db->tableName.".data_wyjazdu, ".$db->tableName.".ilosc_kobiet, 
        ".$db->tableName.".ilosc_mezczyzn, ".$db->tableName.".ilosc_tyg, ".$db->tableName.".widoczne_www  
        from ".$db->tableName." 
        join ".$klient->tableName." on ".$db->tableName.".id_klient = ".$klient->tableName.".".$klient->tableId."
        join ".$oddzial->tableName." on ".$db->tableName.".id_oddzial = ".$oddzial->tableName.".".$oddzial->tableId." 
        where ".$db->tableName.".data_wyjazdu >= '".date("Y-m-d")."' order by ".$db->tableName.".data_wyjazdu asc;";
        $query = $controls->dalObj->pgQuery($zapytanie);
        echo '<form method = "POST" action = "'.$_SERVER['PHP_SELF'].'">';
        echo $controls->AddHidden($hidden_id, $hidden_id, ''); 
        echo '<table class="gridTable" border="0" cellspacing="0">';
	    echo "<tr>";
        //echo $controls->AddSubmitStiffWidth("edycja_zapotrzebowan", "edycja_zapotrzebowan", "Edycja zapotrzebowañ.", "");
        if (isset($_SESSION['edycja_rekordu']))
        {
            echo '<th nowrap align="center">Edycja</th>';   
            echo '<th nowrap align="center">Kasowanie</th>';   
        }
        $odlamki_nag = explode(",","Klient, Oddzia³, Data, Ilo¶æ kobiet, Ilo¶æ mê¿czyzn, Ilo¶æ tygodni, Widoczne na stronie");
        setHeadingRow($odlamki_nag);
        echo '</tr>';
        $count = 0;
        while ($row = pg_fetch_assoc($query))
        {
            $count++;
            $css = (($count % 2) == 0) ? 'oddRow' : 'evenRow';
            echo '<tr class="'.$css.'" onmouseover="markRow(this, \'hoveredRow\');" onmouseout="markRow(this, \''.$css.'\');" onclick="pointRow(this, \'markedRow\');">';
            if (isset($_SESSION['edycja_rekordu']))
            {
                echo '<td nowrap align="center">';
                echo $controls->AddTableSubmit($edit, $row['id'], "Edytuj.", "onClick = '".$hidden_id.".value = this.id;'");
                echo '<td nowrap align="center">';
                echo $controls->AddTableSubmit($delete, $row['id'], "Kasuj.", "onClick = '".$hidden_id.".value = this.id;'");
            }
            unset ($row['id']);
            addRowsToTable($row);
            echo '</tr>';
        }
        echo '</table></form>';
    }
    CommonUtils::sendOutputBuffer();
?>
</body>
</html>
