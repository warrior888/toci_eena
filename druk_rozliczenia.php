<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="js/script.js"></script>
<link href="css/style.css" rel="stylesheet" type="text/css"></head>
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
	require("conf.php");
        //a tu piszemy cala reszte :P
	if ($druk_rozliczenia)
	{
		/*echo phpinfo();
		$drukarka = printer_open();
		printer_write($drukarka, "Hello World");
		printer_close();*/
	}
	require("stopka.php");
    }
?>
</html>
