<?php
//naglowek edycji w tablicy
if (isset($_SESSION['edycja_rekordu']))
{
	echo "<th>Edycja</th>";
}
//naglowek kasowania
if (isset($_SESSION['kasowanie_rekordu']))
{
	echo "<th>Kasowanie</th>";
}
//naglowek zetla
if (isset($_SESSION['dodawanie_zettla']))
{
        echo "<th>Zettel</th>";
}
//zmienna sesyjna id'ków ludzi do edycji masowej
if (isset($_SESSION['edycja_grupowa']))
{
    echo "<th><input type = 'checkbox' onClick = 'selectAll(this);' /></th>";
	@$_SESSION['edycja_masowa'] = $_SESSION['edycja_masowa'].$wiersz['id']."|";
}
echo "<th>LP.</th>";
?>
