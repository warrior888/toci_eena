<?php
//przycisk edycji danej osoby, na klikniecie zmienna typu hidden id_os sie ustawia na id klikanej osoby, dzieki czemu edytuje sie ta osoba co trzeba
//na klikniecie odpala sie plik z katalogu edit/przetwarzaj_dane_osobowe

$controls = new valControl();
if (isset($_SESSION['edycja_rekordu']))
{
	echo '<td nowrap align="CENTER">';
    echo $controls->AddTableSubmit("edytuj_osobe", $wiersz['id'], "Edytuj.", "onClick='id_os.value=this.id;'");
    //<input type='submit' name='edytuj_osobe' value='Edytuj.' onClick='id_os.value=\"".$wiersz['id']."\";'>
	echo '</td>';
}
//przycisk kasowania, zasada okreslania id osoby jak powyzej
if (isset($_SESSION['kasowanie_rekordu']))
{
	echo '<td nowrap align="CENTER">';
    echo $controls->AddTableSubmit('kasuj_osobe', $wiersz['id'], 'Kasuj.', 'onClick="id_os.value=this.id; return confirm(\'Operacja jest nieodwracalna, czy jeste¶ pewien ?\');"');
	echo '</td>';
}
//przycisk zettla, ktory na klikniecie ma wpisywac bierzaca date i zapis zettel do tabeli korespondencji z id osoby, kolo ktorej jest klikany
//przycisk
if (isset($_SESSION['dodawanie_zettla']))
{
    echo '<td nowrap align="CENTER">';
    echo $controls->AddTableSubmit("id_zettel", $wiersz['id'], "Zettel.", "onClick='id_os.value=this.id;'");
    //<input type='submit' name='id_zettel' value='Zettel' onClick='id_os.value=\"".$wiersz['id']."\";'>
	echo '</td>';
}
//zapamietywanie kolejnych id w zmiennej sesyjnej potrzebnej do edycji masowej
if (isset($_SESSION['edycja_grupowa']))
{
    echo ("<td nowrap align='CENTER'><input type='checkbox' name='id_osoby_checkbox[]'
	value='".$wiersz['id']."' title = 'Id - ".$wiersz['id']."'></td>");
	@$_SESSION['edycja_masowa'] = $_SESSION['edycja_masowa'].$wiersz['id']."|";
}
if (isset($LicznikLiczPorz))
{
    $LicznikLiczPorz++;
}
else
{
    $LicznikLiczPorz = 1;
}
echo "<td>".$LicznikLiczPorz."</td>";
?>