<html>
<head>
    <link href="css/login.css" rel="stylesheet" type="text/css">
    <meta content="text/html; charset=iso-8859-2" http-equiv="Content-Type">
    <script language="javascript" type="text/javascript" src="js/utils.js"></script>
    <script language="javascript" type="text/javascript" src="js/validations.js"></script>
</head>
<?php
    $loginValue = isset($_POST['login']) ? htmlspecialchars(strip_tags($_POST['login']), ENT_QUOTES) : '';
    
    if ($loginValue)
        $focusElement = 'id_pass';
    else
        $focusElement = 'id_login';
?>
<body onload="utils.focusElement('<?php echo $focusElement; ?>');">
<?php
    require("naglowek.php");
    include_once("vaElClass.php");
    $controls = new valControl();

    echo "<table align=\"center\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" height=\"200\" width=\"350\">
	<tbody><tr valign=\"top\">
		  <td rowspan=\"3\" width=\"102\"><img src=\"/zdj/1.jpg\" height=\"203\" width=\"102\"></td>
		  <td colspan=\"2\"><img src=\"/zdj/2.jpg\" height=\"32\" width=\"248\"></td></tr>
  <tr valign=\"top\">
	  <td background=\"/zdj/lgn_bg.jpg\" height=\"139\" valign=\"middle\" width=\"234\">
		<form method=\"post\" action=\"/index.php\" target=\"_parent\">
		<br>
	  	<table border=\"0\" cellpadding=\"2\" cellspacing=\"0\" width=\"100%\">
	    	<tbody><tr>
			<td class=\"text_bold\" width=\"32%\"><div align=\"right\">Login:&nbsp;&nbsp;&nbsp;</div></td>
	  		<td width=\"68%\">";
            echo $controls->AddTextbox('login', 'id_login', $loginValue, '20', '20', '');
		  	echo "</td></tr>
			<tr>
			<td class=\"text_bold\"><div align=\"right\">Hasło:&nbsp;&nbsp;&nbsp;</div></td>
			<td>"; 
            echo $controls->AddPassbox('pass','id_pass','','20','20','');
            echo "</tr>
            <td class=\"text_bold\"><div align=\"right\">Skrypty startowe:&nbsp;&nbsp;&nbsp;</div></td>
			<td>"; 
            echo $controls->AddCheckbox('start','id_start',true,'','','start');
            echo "</tr><tr>
			<td class=\"text_bold\">&nbsp;</td>
			<td>
			";
            echo $controls->AddSubmit('wyslij', 'id_wyslij', 'Zaloguj', '');
			
			echo "</td></tr>
	      	</tbody></table>
	      	</form></td>
		<td height=\"139\" width=\"14\"><img src=\"/zdj/4.jpg\" height=\"149\" width=\"14\"></td></tr>
  		<tr valign=\"top\">
	  	<td colspan=\"2\" height=\"22\" valign=\"top\"><img src=\"/zdj/3.jpg\" height=\"22\" width=\"248\"></td></tr>
		<tr valign=\"top\">
		<td colspan=\"2\" height=\"22\" valign=\"top\"></td>
		</tr>
</tbody></table>";
require("stopka.php");
?>
</body>
</html>