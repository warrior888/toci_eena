<html>
<head>
</head>
<body>
<?php
//naglowek edycji w tablicy
if (isset($_SESSION['edycja_rekordu']))
{
	echo "<td bgcolor=\"#DDDDDD\">Edycja</td>";
}
//naglowek kasowania
if (isset($_SESSION['kasowanie_rekordu']))
{
	echo "<td bgcolor=\"#DDDDDD\">Kasowanie</td>";
}
?>
</body>
</html>