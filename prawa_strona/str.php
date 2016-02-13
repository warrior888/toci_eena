<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <title>Wydruk jarografu</title>
</head>
<body onLoad="window.print();">
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
        //a tu piszemy cala reszte :P
	    echo "<img src='".$_GET['zasob_graficzny']."' width='650'>";
        require("../stopka.php");
    }
?>
</body>
</html>
