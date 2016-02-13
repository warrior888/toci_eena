<?
	function ustaw_sesje()
	{
		//session_start();
		//echo "fsadgtsfh";
        $_SESSION['uzytkownik'] = $uzytkownik;
		    $_SESSION['kwerenda'] = 0;
		    $_SESSION['edycja_masowa'] = "";
		    $wiersz=pg_fetch_array ($wynik);
		    $_SESSION['ilosc_rekordow'] = $wiersz['liczba_rekordow'];
		    if ($wiersz['dodawanie_rekordu']){$_SESSION['dodawanie_rekordu'] = 1;}
		    if ($wiersz['dodawanie_kwerendy']){$_SESSION['dodawanie_kwerendy'] = 1;}
		    if ($wiersz['dodawanie_zettla']){$_SESSION['dodawanie_zettla'] = 1;}
		    if ($wiersz['edycja_rekordu']){$_SESSION['edycja_rekordu'] = 1;}
		    if ($wiersz['edycja_grupowa']){$_SESSION['edycja_grupowa'] = 1;}
		    if ($wiersz['kasowanie_rekordu']){$_SESSION['kasowanie_rekordu'] = 1;}
		    if ($wiersz['druk_umowy']){$_SESSION['druk_umowy'] = 1;}
		    if ($wiersz['druk_listy']){$_SESSION['druk_listy'] = 1;}
		    if ($wiersz['druk_rozliczenia']){$_SESSION['druk_rozliczenia'] = 1;}
		    if ($wiersz['druk_ankiety']){$_SESSION['druk_ankiety'] = 1;}
		    if ($wiersz['druk_biletu']){$_SESSION['druk_biletu'] = 1;}
		    if ($wiersz['email']){$_SESSION['email1'] = 1;}
		    if ($wiersz['masowy_email']){$_SESSION['masowy_email'] = 1;}
		    if ($wiersz['masowy_sms']){$_SESSION['masowy_sms'] = 1;}
		    if ($wiersz['zmiana_uprawnien']){$_SESSION['zmiana_uprawnien'] = 1;}
		    //$_SESSION['id_kl'] = "dana";
	}
?>
