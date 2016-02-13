<?php
    require 'conf.php';
    CommonUtils::outputBufferingOn();
    CommonUtils::SessionStart();

    echo '<html>';
    HelpersUI::addHtmlBasicHeaders();
    echo '<body class="left">';

    if (empty($_SESSION['uzytkownik']))
    {
        
    }
    else
    {
        include_once "vaElClass.php";
        $controls = new	valControl();
	    
        echo '<form method="POST" action="sort.php" target="center">';
		echo $controls->AddSubmitStiffWidth("sort", "sort", "Sortuj.", "", 'roseBkgnd btnBorder');
        echo '</form>';
        
        echo '<form method="POST">';
        echo $controls->AddSubmitStiffWidth('people', 'people', 'Kandydat.', '','roseBkgnd btnBorder');
        echo '</form>';
        
        if (isset($_POST['people']))
        {
            if (isset($_SESSION['dodawanie_rekordu']))
            {
                echo '<form method="GET" action="main/osoba.php" target="center">';
                echo $controls->AddSubmitStiffWidth("dodaj_osobe", "dodaj_osobe", "Dodaj osobê.", "", 'peopleBtn');
                echo '</form><form method="POST" action="ankiety/ankiety_podglad.php" target="center">';
                echo $controls->AddSubmitStiffWidth("dodaj_osobe", "dodaj_osobe", "Rejestracje.", "", 'peopleBtn');
                echo '</form>';
            }
            if (isset($_SESSION['edycja_grupowa']))
            {
                echo "<form method='POST' action='edycja_masowa.php' target='center'>";
                echo $controls->AddHidden('id_h_os', 'h_os', '');
                echo $controls->AddSubmitStiffWidth("edytuj_grupowo", "edytuj_grupowo", "Edytuj Grupowo.", "", 'peopleBtn');
                echo '</form>';
            }
            
            if (isset($_SESSION['masowy_email']))
            {
                echo "<form method='POST' action='masowy_email.php' target='center'>";
                echo $controls->AddSubmitStiffWidth("masowy_email", "masowy_email", "Wyslij e-mail.", "", 'peopleBtn');
                echo '</form>';
            }
            if (isset($_SESSION['masowy_sms']))
            {
                echo "<form method='POST' action='masowy_sms.php' target='center'>";
                echo $controls->AddSubmitStiffWidth("masowy_sms", "masowy_sms", "Wyslij SMS.", "", 'peopleBtn');
                echo '</form>';
            }
            if (isset($_SESSION['dodawanie_zettla']))
            {
                echo "<form method='POST' action='masowy_zettel.php' target='center'>";
                echo $controls->AddSubmitStiffWidth("masowy_zettel", "masowy_zettel", "Wyslij Zettle.", "", 'peopleBtn');
                echo '</form>';
            }
            
        }
        
        echo '<form method="POST">';
        echo $controls->AddSubmitStiffWidth('firm', 'firm', 'Klient.', '', 'roseBkgnd btnBorder');
        echo '</form>';
        
        if (isset($_POST['firm']))
        {
            if (isset($_SESSION['dodawanie_kwerendy']))
            {
                echo "<form method='POST' action='dodawanie/dodaj_zapotrzebowanie.php' target='center'>";
                echo $controls->AddSubmitStiffWidth("dodaj_zapotrzebowanie", "dodaj_zapotrzebowanie", "Dodaj zapotrzebowanie.", "", 'peopleBtn');
                echo '</form>';
                echo "<form method='POST' action='edit/edycja_zapotrzebowan.php' target='center'>";
                echo $controls->AddSubmitStiffWidth("edycja_zapotrzebowan", "edycja_zapotrzebowan", "Edycja zapotrzebowañ.", "", 'peopleBtn');
                echo '</form>';
            }
            if (isset($_SESSION['dodawanie_rekordu']))
            {
                echo '<form method="POST" action="dodawanie/dodaj_klient.php" target="center">';
                echo $controls->AddSubmitStiffWidth("dodaj_klient", "dodaj_klient", "Dodaj klienta.", "", 'peopleBtn');
                echo '</form>';
                
                //if (isset($_SESSION['zmiana_uprawnien']))
                //{
                    echo '<form method="POST" action="przegladaj_klient.php" target="center">';
                    echo $controls->AddSubmitStiffWidth("przegladaj_klient", "przegladaj_klient", "Przegl±daj klientów.", "", 'peopleBtn');
                    echo '</form>';
                //}
                
                echo '<form method="POST" action="dodawanie/obs_biura.php" target="center">';
                echo $controls->AddSubmitStiffWidth("obs_biura", "obs_biura", "Obs³ugiwane biura.", "", 'peopleBtn');
                echo '</form>';
            }
        }
	    
        echo '<form method="POST">';
        echo $controls->AddSubmitStiffWidth('administration', 'administration', 'Administracja.', '', 'roseBkgnd btnBorder');
        echo '</form>';
        
        if (isset($_POST['administration']))
        {
            // tak ma byc
            if (isset($_SESSION['masowy_sms']))
            {
                echo "<form method='POST' action='dane_slownikowe/uzupelnianie_slownika.php' target='center'>";
                echo $controls->AddSubmitStiffWidth("uzupelnianie_slownika", "uzupelnianie_slownika", "Dane s³ownikowe.", "", 'peopleBtn');
                echo '</form>';
            }
            
            if (isset($_SESSION['druk_listy']))
            {
                echo "<form method='POST' action='zalozenia.php' target='center'>";
                echo $controls->AddSubmitStiffWidth("zalozenia", "zalozenia", "Za³o¿enia.", "", 'peopleBtn'); 
                echo '</form>';
            }
            if (isset($_SESSION['druk_biletu']))
            {
                echo "<form method='POST' action='statystyka/statystyka.php' target='center'>";
                echo $controls->AddSubmitStiffWidth("statystyka", "statystyka", "Statystyka.", "", 'peopleBtn');
                echo '</form>';
            }
            if (isset($_SESSION['zmiana_uprawnien']))
            {
                echo "<form method='POST' action='zmiana_uprawnien.php' target='center'>";
                echo $controls->AddSubmitStiffWidth("zmiana_uprawnien", "zmiana_uprawnien", "Zmien uprawnienia.", "", 'peopleBtn');
                echo '</form>';
                echo "<form method='POST' action='tagesraport.php' target='center'>";
                echo $controls->AddSubmitStiffWidth("tagesraport", "tagesraport", "S³ownik e-mail.", "", 'peopleBtn');
                echo '</form><form action="wprowadzanie_masowe/konsultanci.php" target="center">';   //uzupelnianie.html
                echo $controls->AddSubmitStiffWidth("upload", "upload", "Uzupe³nianie masowe.", "", 'peopleBtn');
                echo '</form>';
                echo '<form action="roznosci.php" target="center">';   //uzupelnianie.html
                echo $controls->AddSubmitStiffWidth("roznosci", "roznosci", "Ró¿no¶ci.", "", 'peopleBtn');
                echo '</form>';
                echo "<form method='POST' action='jarograf_pdf.php' target='center'>";
                echo $controls->AddSubmitStiffWidth("jarografy", "jarografy", "Jarografy z PDF.", "", 'peopleBtn');
                echo '</form>';
                /*echo "<form method='POST' action='pokaz_sesje.php' target='center'>";
                echo $controls->AddSubmitStiffWidth("pokaz_sesje", "pokaz_sesje", "Zmienne sesyjne.", "");
                echo '</form>';
                echo "<form method='POST' action='zapytania/randoms.php' target='center'>";
                echo $controls->AddSubmitStiffWidth("random_queries", "random_queries", "Panel administracyjny.", "");
                echo '</form>';*/
            }
                        
            echo '<form action="main/user_management.php" target="center">';   //uzupelnianie.html
            echo $controls->AddSubmitStiffWidth("user_mgmt", "user_mgmt", "Zmieñ has³o.", "", 'peopleBtn');
            echo '</form>';
            
            echo '<form method="GET" action="stats.php" target="center">';
            echo $controls->AddSubmitStiffWidth("generate_stats", "generate_stats", "Generuj statystyki.", "", 'peopleBtn');
            echo '</form>';
        }
        
	    
    /*if (isset($_SESSION['edycja_grupowa']))
	{
		echo "<form method='POST' action='zmiana_statusow/zmien_status.php' target='center'>";
        
        echo $controls->AddHidden('id_h_os', 'h_os', '');
        echo $controls->AddSubmitStiffWidth("zmiana_statusow", "zmiana_statusow", "Zmiana statusów.", "");
		echo '</form>';
	}*/
	    
        
		echo "<form method='POST' action='log_out.php' target='_parent'>";
        echo $controls->AddSubmitStiffWidth("wyloguj", "wyloguj", "Wyloguj.", "", 'logoutBtn');
		echo '</form>';
		//</div></div>";
    }
	CommonUtils::sendOutputBuffer();
?>
</body>
</html>
