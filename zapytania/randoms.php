<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
<link href="../css/style.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php
    @session_start();
    include("../vaElClass.php");
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
        require("../naglowek.php");
	    require("../conf.php");
        $controls = new valControl();
        $database = pg_connect($con_str);
        
        if (isset($_POST['zatwierdz_query']) && isset($_SESSION['super_logon']))
        {
            //echo $_POST['query'];
            $query = str_replace("\\", "", $_POST['query']);
            //echo $query;
            $result = pg_query($database, $query);
            //var_dump($result);
            $row = pg_fetch_array($result);
            var_dump($row);
            unset($_SESSION['super_logon']);
        }
        if (isset($_POST['log_in']))
        {
            $_POST['user_pass'] = md5($_POST['user_pass']);
            $queryAuth = "select id from uprawnienia where id = ".$_POST['user_id']." and nazwa_uzytkownika = '".$_POST['user_login']."' and haslo = '".$_POST['user_pass']."' and zmiana_uprawnien = '1';";
            //echo $queryAuth;
            $resultAuth = pg_query($database, $queryAuth);
            if (pg_num_rows($resultAuth) == 1)
            {
                $_SESSION['super_logon'] = "granted";
            }
            else
            {
                unset($_SESSION['super_logon']);
            }
        }
        if (!isset($_SESSION['super_logon']))
        {
            echo '<table><form method="POST" action="'.$_SERVER['PHP_SELF'].'"><tr><td>';
            echo $controls->AddNumberbox("user_id", "user_id", "", 2, 3, "sprawdz_ilosc_osob(this);");
            echo '</td></tr><tr><td>';
            echo $controls->AddTextbox("user_login", "user_login", "", 20, 15, "");
            echo '</td></tr><tr><td>';
            echo $controls->AddPassbox("user_pass", "user_pass", "", 20, 15, "");
            echo '</td></tr><tr><td>';
            echo $controls->AddSubmit("log_in", "log_in", "Loguj", "");
            echo '</td></tr></form></table>';
        }
        else
        {
            //echo 'Affirmative.';
            echo '<table><form method="POST" action="'.$_SERVER['PHP_SELF'].'"><tr><td>';
            echo $controls->AddTextbox("query", "query", "", 200, 80, "");
            echo '</td></tr><tr><td>';
            echo $controls->AddSubmit("zatwierdz_query", "zatwierdz_query", "Wykonaj.", "");
            echo '</td></tr></form></table>';
            
            unset($_POST['log_in']);
        }
        require("../stopka.php");
    }
?>
</body>
</html>
