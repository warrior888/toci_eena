<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="js/script.js"></script>
<link href="css/style.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php
    // ś ą
    session_start();
    if (empty($_SESSION['uzytkownik']))
    {
        require("log_in.php");
    }
    else
    {
        require("naglowek.php");
	    require("conf.php");
        require_once 'vaElClass.php';
        $controls = new valControl();
        
        if (isset($_SESSION['zmiana_uprawnien']))
        {
            echo '<form action="wyslane_sms.php" target="center">';
            echo $controls->AddSubmitStiffWidth("wyslane_sms", "wyslane_sms", "Wysłane sms.", "");
            echo '</form>';
        }
        require("stopka.php");
    }
?>
</body>
</html>
