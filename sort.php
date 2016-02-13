<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="js/script.js"></script>
<link href="css/layout.css" rel="stylesheet" type="text/css"></head>
</head>
<?php
    @session_start();
    if (empty($_SESSION['uzytkownik']))
    {
        require("log_in.php");
    }
    else
    {
        include_once 'vaElClass.php';
        $controls = new valControl();
        
        function popraw(&$zrodlo)
        {
            $zrodlo[0] = strtoupper($zrodlo[0]);  
            for ($i = 1; $i < strlen($zrodlo); $i++)
            {
                if ($zrodlo[$i] == "_")
                    $zrodlo[$i] = " ";
            } 
            return $zrodlo;
        }
        function cofnij(&$zrodlo)
        {
            $zrodlo[0] = strtolower($zrodlo[0]);  
            for ($i = 1; $i < strlen($zrodlo); $i++)
            {
                if ($zrodlo[$i] == " ")
                    $zrodlo[$i] = "_";
            } 
            return $zrodlo;
        }
        $licznik = 3;
        $rozny = true;
        $ilosc = 0;
        require("naglowek.php");
	    require("conf.php");
        $database = pg_connect($con_str);
        if (!empty($_SESSION['szukaj_sql']))
        {
            unset($_SESSION['kwerenda_sql']);
            unset($_SESSION['widok_sql']);
            unset($_SESSION['wakaty_umowieni']);
            unset($_SESSION['wakaty_zainteresowani']);
            $tab = array("id", "imie", "nazwisko", "plec", "data urodzenia", "msc urodzenia", "msc", "ulica", "kod", "wyksztalcenie", "konsultant");
            $zap = stripslashes($_SESSION['szukaj_sql']);
            $poz_w_pyt = strpos($zap, "order by");
            $zap = substr($zap, 0, $poz_w_pyt);
            $zap .= " order by ";
            if (count($_POST) != 0)
            {
                for ($z = 0; $z < $licznik; $z++)
                {
                    $rozny = true;
                    $d = "co".$z;
                    $d1 = "jak".$z;
                    @$tmp = $_POST["$d"];
                    if ($_POST["$d1"] != "--------")
                    {
                        for ($z1 = 0; $z1 < $licznik; $z1++)
                        {
                            $d2 = "co".$z1;
                            $d3 = "jak".$z1;
                            if ($z1 != $z)
                            {
                                if (($tmp == $_POST["$d2"]) && ($_POST["$d3"]) != "--------")
                                {
                                    $rozny = false;
                                    break;
                                }          
                            }
                        }
                        if (!$rozny)
                            break;
                        else
                            $ilosc++;
                    }   
                }
            }
            if (($_POST['czy_sortowac'] == "true") && ($rozny) && ($ilosc > 0))
            {
                for ($i = 0; $i < $licznik; $i++)
                {
                    $t = "co".$i;
                    $t1 = "jak".$i;
                    if ($_POST["$t1"] != "--------")
                    {
                        $zap .= cofnij($_POST["$t"])." ";
                        if ($_POST["$t1"] == "Rosnąco")
                        {
                            $zap .= " asc, ";   
                        }
                        else
                        {
                            $zap .= " desc, ";   
                        }
                    }
                    //echo("$z");
                }
                $zap = substr($zap, 0, strlen($zap) - 2);
                $zap .= ";";
                //echo("$zap");
                $_SESSION['szukaj_sql'] = $zap;
                //require("wypisz.php");
                //wypisz($zap, $con_str);
                //require("szukaj.php");
                echo("<script>wroc();</script>");
            }
            else if ((!$rozny) && ($_POST['czy_sortowac'] == "true"))
            {
                echo("Bład");   
                echo("<form action = '".$_SERVER['PHP_SELF']."' method = 'POST'>");
                echo $controls->AddHidden('id_czy_sortowac', 'czy_sortowac', 'false');
                for ($j = 0; $j < $licznik; $j++)
                {
                    echo("<select name = 'co".$j."'>");
                    for ($i = 0; $i < count($tab); $i++)
                    {
                        echo("<option>".popraw($tab[$i])."");   
                    }
                    echo("</select>");
                    echo("<select name = 'jak".$j."'>");
                    echo("<option>--------");
                    echo("<option>Rosnąco");
                    echo("<option>Malejąco");
                    echo("</select><br />");
                }
                echo $controls->AddSubmit('sort', 'id_sort', 'Sortuj', 'onClick = "czy_sortowac.value = true;"');
                echo("</form>");
            }
            else if ($ilosc == 0)
            {
                echo("<form action = '".$_SERVER['PHP_SELF']."' method = 'POST'>");
                echo $controls->AddHidden('id_czy_sortowac', 'czy_sortowac', 'false');
                for ($j = 0; $j < $licznik; $j++)
                {
                    echo("<select name = 'co".$j."'>");
                    for ($i = 0; $i < count($tab); $i++)
                    {
                        echo("<option>".popraw($tab[$i])."");   
                    }
                    echo("</select>");
                    echo("<select name = 'jak".$j."'>");
                    echo("<option>--------");
                    echo("<option>Rosnąco");
                    echo("<option>Malejąco");
                    echo("</select><br />");
                }
                echo $controls->AddSubmit('sort', 'id_sort', 'Sortuj', 'onClick = "czy_sortowac.value = true;"');
                echo("</form>");
            }
            else
            {
                echo("<form action = '".$_SERVER['PHP_SELF']."' method = 'POST'>");
                echo $controls->AddHidden('id_czy_sortowac', 'czy_sortowac', 'false');
                for ($j = 0; $j < $licznik; $j++)
                {
                    echo("<select name = 'co".$j."'>");
                    for ($i = 0; $i < count($tab); $i++)
                    {
                        echo("<option>".popraw($tab[$i])."");   
                    }
                    echo("</select>");
                    echo("<select name = 'jak".$j."'>");
                    echo("<option>--------");
                    echo("<option>Rosnąco");
                    echo("<option>Malejąco");
                    echo("</select><br />");
                }
                echo $controls->AddSubmit('sort', 'id_sort', 'Sortuj', 'onClick = "czy_sortowac.value = true;"');
                echo("</form>");
            }
            //echo(stripslashes($_SESSION['szukaj_sql']));
        }
        else if (!empty($_SESSION['kwerenda_sql']))
        {
            unset($_SESSION['widok_sql']);
            unset($_SESSION['szukaj_sql']);
            unset($_SESSION['wakaty_umowieni']);
            unset($_SESSION['wakaty_zainteresowani']);
            $tab = array();
            $zap = stripslashes($_SESSION['kwerenda_sql']);
            $zap = substr($zap, 0, strpos($zap, "order by"));
            $query = pg_query($database, $zap);
            $row = pg_fetch_array($query);
            foreach ($row as $key => $value)
            {
                if (is_string($key))
                {
                    $tab[] = $key;
                }   
            }
            //$poz_w_pyt = strpos($zap, "order by");
            //$zap = substr($zap, 0, $poz_w_pyt);
            $zap = substr($zap, 0, strlen($zap) - 1);
            //$zap = "select * from (".$zap.") as kwerenda";
            $zap .= " order by ";
            if (count($_POST) != 0)
            {
                for ($z = 0; $z < $licznik; $z++)
                {
                    $rozny = true;
                    $d = "co".$z;
                    $d1 = "jak".$z;
                    @$tmp = $_POST["$d"];
                    if ($_POST[$d1] != "--------")
                    {
                        for ($z1 = 0; $z1 < $licznik; $z1++)
                        {
                            $d2 = "co".$z1;
                            $d3 = "jak".$z1;
                            if ($z1 != $z)
                            {
                                if (($tmp == $_POST[$d2]) && ($_POST[$d3]) != "--------")
                                {
                                    $rozny = false;
                                    break;
                                }          
                            }
                        }
                        if (!$rozny)
                            break;
                        else
                            $ilosc++;
                    }   
                }
            }
            if (($_POST['czy_sortowac'] == "true") && ($rozny) && ($ilosc > 0))
            {
                for ($i = 0; $i < $licznik; $i++)
                {
                    $t = "co".$i;
                    $t1 = "jak".$i;
                    if ($_POST["$t1"] != "--------")
                    {
                        $zap .= "kwerenda.".cofnij($_POST["$t"])." ";
                        if ($_POST["$t1"] == "Rosnąco")
                        {
                            $zap .= " asc, ";   
                        }
                        else
                        {
                            $zap .= " desc, ";   
                        }
                    }
                    //echo("$z");
                }
                $zap = substr($zap, 0, strlen($zap) - 2);
                $zap .= ";";
                //echo("$zap");
                $_SESSION['kwerenda_sql'] = $zap;
                echo("<script>wroc();</script>");
                //wypisz($zap, $con_str);
            }
            else if ((!$rozny) && ($_POST['czy_sortowac'] == "true"))
            {
                echo("Bład");   
                echo("<form action = '".$_SERVER['PHP_SELF']."' method = 'POST'>");
                echo $controls->AddHidden('id_czy_sortowac', 'czy_sortowac', 'false');
                for ($j = 0; $j < $licznik; $j++)
                {
                    echo("<select name = 'co".$j."'>");
                    for ($i = 0; $i < count($tab); $i++)
                    {
                        echo("<option>".popraw($tab[$i])."");   
                    }
                    echo("</select>");
                    echo("<select name = 'jak".$j."'>");
                    echo("<option>--------");
                    echo("<option>Rosnąco");
                    echo("<option>Malejąco");
                    echo("</select><br />");
                }
                echo $controls->AddSubmit('sort', 'id_sort', 'Sortuj', 'onClick = "czy_sortowac.value = true;"');
                echo("</form>");
            }
            else if ($ilosc == 0)
            {
                echo("<form action = '".$_SERVER['PHP_SELF']."' method = 'POST'>");
               echo $controls->AddHidden('id_czy_sortowac', 'czy_sortowac', 'false');
                for ($j = 0; $j < $licznik; $j++)
                {
                    echo("<select name = 'co".$j."'>");
                    for ($i = 0; $i < count($tab); $i++)
                    {
                        echo("<option>".popraw($tab[$i])."");   
                    }
                    echo("</select>");
                    echo("<select name = 'jak".$j."'>");
                    echo("<option>--------");
                    echo("<option>Rosnąco");
                    echo("<option>Malejąco");
                    echo("</select><br />");
                }
               echo $controls->AddSubmit('sort', 'id_sort', 'Sortuj', 'onClick = "czy_sortowac.value = true;"');
                echo("</form>");
            }
            else
            {
                echo("<form action = '".$_SERVER['PHP_SELF']."' method = 'POST'>");
                echo $controls->AddHidden('id_czy_sortowac', 'czy_sortowac', 'false');
                for ($j = 0; $j < $licznik; $j++)
                {
                    echo("<select name = 'co".$j."'>");
                    for ($i = 0; $i < count($tab); $i++)
                    {
                        echo("<option>".popraw($tab[$i])."");   
                    }
                    echo("</select>");
                    echo("<select name = 'jak".$j."'>");
                    echo("<option>--------");
                    echo("<option>Rosnąco");
                    echo("<option>Malejąco");
                    echo("</select><br />");
                }
                echo $controls->AddSubmit('sort', 'id_sort', 'Sortuj', 'onClick = "czy_sortowac.value = true;"');
                echo("</form>");
            }
        }
        else if (!empty($_SESSION['widok_sql']))
        {
            unset($_SESSION['kwerenda_sql']);
            unset($_SESSION['szukaj_sql']);
            unset($_SESSION['wakaty_umowieni']);
            unset($_SESSION['wakaty_zainteresowani']);
            $tab = array();
            $zap = stripslashes($_SESSION['widok_sql']);
            $zap = substr($zap, 0, strpos($zap, "order by"));
            $query = pg_query($database, $zap);
            $row = pg_fetch_array($query);
            foreach ($row as $key => $value)
            {
                if (is_string($key))
                {
                    $tab[] = $key;
                }   
            }
            //$poz_w_pyt = strpos($zap, "order by");
            //$zap = substr($zap, 0, $poz_w_pyt);
            $zap = substr($zap, 0, strlen($zap) - 1);
            $zap .= " order by ";
            if (count($_POST) != 0)
            {
                for ($z = 0; $z < $licznik; $z++)
                {
                    $rozny = true;
                    $d = "co".$z;
                    $d1 = "jak".$z;
                    @$tmp = $_POST["$d"];
                    if ($_POST["$d1"] != "--------")
                    {
                        for ($z1 = 0; $z1 < $licznik; $z1++)
                        {
                            $d2 = "co".$z1;
                            $d3 = "jak".$z1;
                            if ($z1 != $z)
                            {
                                if (($tmp == $_POST["$d2"]) && ($_POST["$d3"]) != "--------")
                                {
                                    $rozny = false;
                                    break;
                                }          
                            }
                        }
                        if (!$rozny)
                            break;
                        else
                            $ilosc++;
                    }   
                }
            }
            if (($_POST['czy_sortowac'] == "true") && ($rozny) && ($ilosc > 0))
            {
                for ($i = 0; $i < $licznik; $i++)
                {
                    $t = "co".$i;
                    $t1 = "jak".$i;
                    if ($_POST["$t1"] != "--------")
                    {
                        $zap .= cofnij($_POST["$t"])." ";
                        if ($_POST["$t1"] == "Rosnąco")
                        {
                            $zap .= " asc, ";   
                        }
                        else
                        {
                            $zap .= " desc, ";   
                        }
                    }
                    //echo("$z");
                }
                $zap = substr($zap, 0, strlen($zap) - 2);
                $zap .= ";";
                //echo("$zap");
                $_SESSION['widok_sql'] = $zap;
                echo("<script>wroc();</script>");
                //wypisz($zap, $con_str);
            }
            else if ((!$rozny) && ($_POST['czy_sortowac'] == "true"))
            {
                echo("Bład");   
                echo("<form action = '".$_SERVER['PHP_SELF']."' method = 'POST'>");
                echo $controls->AddHidden('id_czy_sortowac', 'czy_sortowac', 'false');
                for ($j = 0; $j < $licznik; $j++)
                {
                    echo("<select name = 'co".$j."'>");
                    for ($i = 0; $i < count($tab); $i++)
                    {
                        echo("<option>".popraw($tab[$i])."");   
                    }
                    echo("</select>");
                    echo("<select name = 'jak".$j."'>");
                    echo("<option>--------");
                    echo("<option>Rosnąco");
                    echo("<option>Malejąco");
                    echo("</select><br />");
                }
                echo $controls->AddSubmit('sort', 'id_sort', 'Sortuj', 'onClick = "czy_sortowac.value = true;"');
                echo("</form>");
            }
            else if ($ilosc == 0)
            {
                echo("<form action = '".$_SERVER['PHP_SELF']."' method = 'POST'>");
                echo $controls->AddHidden('id_czy_sortowac', 'czy_sortowac', 'false');
                for ($j = 0; $j < $licznik; $j++)
                {
                    echo("<select name = 'co".$j."'>");
                    for ($i = 0; $i < count($tab); $i++)
                    {
                        echo("<option>".popraw($tab[$i])."");   
                    }
                    echo("</select>");
                    echo("<select name = 'jak".$j."'>");
                    echo("<option>--------");
                    echo("<option>Rosnąco");
                    echo("<option>Malejąco");
                    echo("</select><br />");
                }
                echo $controls->AddSubmit('sort', 'id_sort', 'Sortuj', 'onClick = "czy_sortowac.value = true;"');
                echo("</form>");
            }
            else
            {
                echo("<form action = '".$_SERVER['PHP_SELF']."' method = 'POST'>");
                echo $controls->AddHidden('id_czy_sortowac', 'czy_sortowac', 'false');
                for ($j = 0; $j < $licznik; $j++)
                {
                    echo("<select name = 'co".$j."'>");
                    for ($i = 0; $i < count($tab); $i++)
                    {
                        echo("<option>".popraw($tab[$i])."");   
                    }
                    echo("</select>");
                    echo("<select name = 'jak".$j."'>");
                    echo("<option>--------");
                    echo("<option>Rosnąco");
                    echo("<option>Malejąco");
                    echo("</select><br />");
                }
               echo $controls->AddSubmit('sort', 'id_sort', 'Sortuj', 'onClick = "czy_sortowac.value = true;"');
                echo("</form>");
            }
            //echo(stripslashes($_SESSION['szukaj_sql']));
        }
        else if ((!empty($_SESSION['wakaty_umowieni']) && (!empty($_SESSION['wakaty_zainteresowani']))))
        {
            unset($_SESSION['kwerenda_sql']);
            unset($_SESSION['szukaj_sql']);
            unset($_SESSION['widok_sql']);
            $tab = array();
            $zap = stripslashes($_SESSION['wakaty_umowieni']);
            $zap1 = stripslashes($_SESSION['wakaty_zainteresowani']);
            $zap = substr($zap, 0, strpos($zap, "order by"));
            $zap1 = substr($zap1, 0, strpos($zap1, "order by"));
            $query = pg_query($database, $zap);
            $row = pg_fetch_array($query);
            foreach ($row as $key => $value)
            {
                if (is_string($key))
                {
                    $tab[] = $key;
                }   
            }
            //$poz_w_pyt = strpos($zap, "order by");
            //$zap = substr($zap, 0, $poz_w_pyt);
            $zap = substr($zap, 0, strlen($zap) - 1);
            $zap .= " order by ";
            $zap1 = substr($zap1, 0, strlen($zap1) - 1);
            $zap1 .= " order by ";
            if (count($_POST) != 0)
            {
                for ($z = 0; $z < $licznik; $z++)
                {
                    $rozny = true;
                    $d = "co".$z;
                    $d1 = "jak".$z;
                    @$tmp = $_POST["$d"];
                    if ($_POST["$d1"] != "--------")
                    {
                        for ($z1 = 0; $z1 < $licznik; $z1++)
                        {
                            $d2 = "co".$z1;
                            $d3 = "jak".$z1;
                            if ($z1 != $z)
                            {
                                if (($tmp == $_POST["$d2"]) && ($_POST["$d3"]) != "--------")
                                {
                                    $rozny = false;
                                    break;
                                }          
                            }
                        }
                        if (!$rozny)
                            break;
                        else
                            $ilosc++;
                    }   
                }
            }
            if (($_POST['czy_sortowac'] == "true") && ($rozny) && ($ilosc > 0))
            {
                for ($i = 0; $i < $licznik; $i++)
                {
                    $t = "co".$i;
                    $t1 = "jak".$i;
                    if ($_POST["$t1"] != "--------")
                    {
                        $zap .= cofnij($_POST["$t"])." ";
                        $zap1 .= cofnij($_POST["$t"])." ";
                        if ($_POST["$t1"] == "Rosnąco")
                        {
                            $zap .= " asc, ";   
                            $zap1 .= " asc, ";   
                        }
                        else
                        {
                            $zap .= " desc, ";   
                            $zap1 .= " desc, ";   
                        }
                    }
                    //echo("$z");
                }
                $zap = substr($zap, 0, strlen($zap) - 2);
                $zap .= ";";
                $zap1 = substr($zap1, 0, strlen($zap1) - 2);
                $zap1 .= ";";
                //echo("$zap");
                $_SESSION['wakaty_zainteresowani'] = $zap1;
                $_SESSION['wakaty_umowieni'] = $zap;
                echo("<script>wroc();</script>");
                //wypisz($zap, $con_str);
            }
            else if ((!$rozny) && ($_POST['czy_sortowac'] == "true"))
            {
                echo("Bład");   
                echo("<form action = '".$_SERVER['PHP_SELF']."' method = 'POST'>");
                echo $controls->AddHidden('id_czy_sortowac', 'czy_sortowac', 'false');
                for ($j = 0; $j < $licznik; $j++)
                {
                    echo("<select name = 'co".$j."'>");
                    for ($i = 0; $i < count($tab); $i++)
                    {
                        echo("<option>".popraw($tab[$i])."");   
                    }
                    echo("</select>");
                    echo("<select name = 'jak".$j."'>");
                    echo("<option>--------");
                    echo("<option>Rosnąco");
                    echo("<option>Malejąco");
                    echo("</select><br />");
                }
                echo $controls->AddSubmit('sort', 'id_sort', 'Sortuj', 'onClick = "czy_sortowac.value = true;"');
                echo("</form>");
            }
            else if ($ilosc == 0)
            {
                echo("<form action = '".$_SERVER['PHP_SELF']."' method = 'POST'>");
                echo $controls->AddHidden('id_czy_sortowac', 'czy_sortowac', 'false');
                for ($j = 0; $j < $licznik; $j++)
                {
                    echo("<select name = 'co".$j."'>");
                    for ($i = 0; $i < count($tab); $i++)
                    {
                        echo("<option>".popraw($tab[$i])."");   
                    }
                    echo("</select>");
                    echo("<select name = 'jak".$j."'>");
                    echo("<option>--------");
                    echo("<option>Rosnąco");
                    echo("<option>Malejąco");
                    echo("</select><br />");
                }
                echo $controls->AddSubmit('sort', 'id_sort', 'Sortuj', 'onClick = "czy_sortowac.value = true;"');
                echo("</form>");
            }
            else
            {
                echo("<form action = '".$_SERVER['PHP_SELF']."' method = 'POST'>");
                echo $controls->AddHidden('id_czy_sortowac', 'czy_sortowac', 'false');
                for ($j = 0; $j < $licznik; $j++)
                {
                    echo("<select name = 'co".$j."'>");
                    for ($i = 0; $i < count($tab); $i++)
                    {
                        echo("<option>".popraw($tab[$i])."");   
                    }
                    echo("</select>");
                    echo("<select name = 'jak".$j."'>");
                    echo("<option>--------");
                    echo("<option>Rosnąco");
                    echo("<option>Malejąco");
                    echo("</select><br />");
                }
                echo $controls->AddSubmit('sort', 'id_sort', 'Sortuj', JsEvents::ONCLICK.'="czy_sortowac.value = true;"');
                echo("</form>");
            }
            //echo(stripslashes($_SESSION['szukaj_sql']));
        }
        //a tu piszemy cala reszte :P
        require("stopka.php");
    }
?>
</html>
