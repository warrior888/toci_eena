<?php
/**
* @desc Dal major convention !!!!
*     dal files creation and responsibility follows the rules below:
* 
*       - dal file is creatd for major db table
*       - dal file is responsible for operations on tables depending on a major table in the sense that those data are highly correlated and strongly dependant, i.e.:
*           - phone number data have a separate table, but should be managed through a logic in dal file responsible for table major for phone table - person data table
*           - employment history data are also dependant on person data, but those data are not strictly personal data, and they have complex structure behind. therefore the table is a subject for a separate file
*       - dal file cannot exceed certain size to remain readable, and this may be the reason behind some exceptions to the previous rules, like some more normalized data are gonna be secluded to a separate files even though they are strongly dependant etc.
*/
    
require_once 'dal.php';
    //require_once 'Postgres/PgModel.php';
    

    
    class Model { //extends PgModel {
        
        const RESULT_FIELD_DATA       = 'data';
        const RESULT_FIELD_METADATA   = 'metadata';
        const RESULT_FIELD_ROWS_COUNT = 'rowsCount';
        
        const TABLE_DANE_OSOBOWE                = 'dane_osobowe';
        const TABLE_STAT                        = 'stat';
        const TABLE_KONTAKT                     = 'kontakt';
        const TABLE_DOKUMENTY                   = 'dokumenty';
        const TABLE_DOKUMENTY_SKAN              = 'dokumenty_skan';
        const TABLE_KONTAKT_HISTORIA            = 'kontakt_historia';
        const TABLE_DANE_INTERNET               = 'dane_internet';
        const TABLE_ZATRUDNIENIE                = 'zatrudnienie';
        const TABLE_ZATRUDNIENIE_ODJAZD         = 'zatrudnienie_odjazd';
        const TABLE_KLIENT                      = 'klient';
        const TABLE_ODDZIALY_KLIENT             = 'oddzialy_klient';
        const TABLE_UPRAWNIENIA                 = 'uprawnienia';
        const TABLE_LOG_MANAGER                 = 'log_manager';
        const TABLE_TELEFON                     = 'telefon';
        const TABLE_TELEFON_KOM                 = 'telefon_kom';
        const TABLE_TELEFON_INNY                = 'telefon_inny';
        const TABLE_EMAIL                       = 'email';
        const TABLE_JEZYKI                      = 'jezyki';
        const TABLE_POZIOMY                     = 'poziomy';
        const TABLE_ZAWOD                       = 'zawod';
        const TABLE_ZNANE_JEZYKI                = 'znane_jezyki';
        const TABLE_ZATWIERDZONE_JEZYKI         = 'zatwierdzone_jezyki';
        const TABLE_JEZYKI_INTERNET             = 'jezyki_internet';
        const TABLE_UMIEJETNOSC                 = 'umiejetnosc';
        const TABLE_UMIEJETNOSCI_OSOB           = 'umiejetnosci_osob';
        const TABLE_UMIEJETNOSCI_OSOB_INTERNET  = 'umiejetnosci_osob_internet';
        const TABLE_PRAWO_JAZDY                 = 'prawo_jazdy';
        const TABLE_PRAWO_JAZDY_INTERNET        = 'prawo_jazdy_internet';
        const TABLE_POS_PRAWO_JAZDY             = 'pos_prawo_jazdy';
        const TABLE_POPRZEDNI_PRACODAWCA        = 'poprzedni_pracodawca';
        const TABLE_POPRZEDNI_PRAC_ANKIETA      = 'poprzedni_pracodawca_ankieta';
        const TABLE_DANE_DODATKOWE              = 'dane_dodatkowe';
        const TABLE_METADANE_OSOBOWE            = 'metadane_osobowe';
        const TABLE_DANE_DODATKOWE_ANKIETA      = 'dane_dodatkowe_ankieta';
        const TABLE_METADANE_INTERNETOWE        = 'metadane_internetowe';
        const TABLE_DANE_DODATKOWE_LISTA        = 'dane_dodatkowe_lista';
        const TABLE_DANE_DODATKOWE_INTERNET_LISTA = 'dane_dodatkowe_internet_lista';
        const TABLE_ZRODLA_DANYCH_ZDALNE        = 'zrodla_danych_zdalne';
        const TABLE_OPIS_PRAC                   = 'opis_prac';
        const TABLE_ROZKLAD_JAZDY               = 'rozklad_jazdy';
        const TABLE_NIEODPOWIEDNI_POWOD         = 'nieodpowiedni_powod';
        const TABLE_WAKAT                       = 'wakat';
        
        const TABLE_IMIONA                      = 'imiona';
        const TABLE_MIEJSCOWOSC                 = 'miejscowosc';
        const TABLE_WYKSZTALCENIE               = 'wyksztalcenie';
        const TABLE_CHARAKTER                   = 'charakter';
        const TABLE_ANKIETA                     = 'ankieta';
        const TABLE_ZRODLO                      = 'zrodlo';
        const TABLE_FIRMA_FILIA                 = 'firma_filia';
        const TABLE_FIRMA                       = 'firma';
        const TABLE_PANSTWO                     = 'panstwo';
        const TABLE_BANK                        = 'bank';
        const TABLE_SMS_KANDYDAT                = 'sms_kandydat';
        const TABLE_RAPORT_AKTYWNY              = 'raport_aktywny';
        const TABLE_RAPORT_AKTYWNY_PL           = 'raport_aktywny_pl';
        const TABLE_RAPORT_AGENCJA_PP           = 'raport_agencja_pp';
        const TABLE_USTAWIENIA_ADMINISTRACYJNE  = 'ustawienia_administracyjne';
        
        const TABLE_ZADANIA                     = 'zadania_dnia';
        const TABLE_ZADANIA_DNIA_KONSULTANT     = 'zadania_dnia_konsultant';
        const TABLE_DODATKOWE_OSOBY             = 'dodatkowe_osoby';
        const TABLE_LISTA_DOKUMENTY_SCAN        = 'lista_dokumenty_skan';
        const TABLE_JAROGRAF                    = 'jarograf';
        const TABLE_ODEBRANY                    = 'odebrany';
        const TABLE_BIURA                       = 'msc_biura';
        const TABLE_REKLAMACJE                  = 'reklamacje';
        
        const VIEW_SMS_POWITANIE                = 'sms_powitanie';
        const VIEW_ZADANIA_DNIA                 = 'zadania_dnia_filtr';
        const VIEW_POPRZEDNI_PRACODAWCA_AGENCJA = 'poprzedni_pracodawca_agencja';
        const VIEW_WAKAT_STRONA                 = 'wakat_strona';
        const VIEW_ZNAJDZ_KANDYDAT_KONTAKT      = 'znajdz_kandydat_kontakt';
        const VIEW_WAKAT_KANDYDACI              = 'wakat_kandydaci';
        
        const COLUMN_ZTR_ID               = 'id';
        const COLUMN_ZTR_ID_KLIENT        = 'id_klient';
        const COLUMN_ZTR_ID_OSOBA         = 'id_osoba';
        const COLUMN_ZTR_ID_STATUS        = 'id_status';
        const COLUMN_ZTR_ID_ODDZIAL       = 'id_oddzial';
        const COLUMN_ZTR_ID_WAKAT         = 'id_wakat';
        const COLUMN_ZTR_ID_DECYZJA       = 'id_decyzja';
        const COLUMN_ZTR_ID_MSC_ODJAZD    = 'id_msc_odjazd';
        const COLUMN_ZTR_ID_MSC_POWROT    = 'id_msc_powrot';
        const COLUMN_ZTR_ID_ROZKLAD_JAZDY_WYJAZD = 'id_rozklad_jazdy_wyjazd';
        const COLUMN_ZTR_ID_ROZKLAD_JAZDY_POWROT = 'id_rozklad_jazdy_powrot';
        const COLUMN_ZTR_ID_BILET         = 'id_bilet';
        const COLUMN_ZTR_ID_PRACOWNIK     = 'id_pracownik';
        const COLUMN_ZTR_DATA_WYJAZDU     = 'data_wyjazdu';
        const COLUMN_ZTR_ILOSC_TYG        = 'ilosc_tyg';
        const COLUMN_ZTR_DATA_POWROTU     = 'data_powrotu';
        const COLUMN_ZTR_DATA_WPISU       = 'data_wpisu';
        
        const COLUMN_DOK_ID               = 'id';
        const COLUMN_DOK_PASS_NR          = 'pass_nr';
        const COLUMN_DOK_DATA_WAZNOSCI    = 'data_waznosci';
        const COLUMN_DOK_NIP              = 'nip';
        const COLUMN_DOK_ID_BANK          = 'id_bank';
        const COLUMN_DOK_NR_KONTA         = 'nr_konta';
        
        const COLUMN_NPO_POWOD            = 'powod';
        const COLUMN_NPO_ID_UPRAWNIENIA   = 'id_uprawnienia';
        const COLUMN_NPO_ID_ZATRUDNIENIE  = 'id_zatrudnienie';
        const COLUMN_NPO_ID_DANE_OSOBOWE  = 'id_dane_osobowe';
        
        const COLUMN_RJA_ID               = 'id';
        const COLUMN_RJA_ID_PRZEWOZNIK    = 'id_przewoznik';
        const COLUMN_RJA_ID_PRZEWOZNIK_WYJAZD    = 'id_przewoznik_wyjazd';
        const COLUMN_RJA_ID_PRZEWOZNIK_POWROT    = 'id_przewoznik_powrot';
        
        const COLUMN_FIF_ID               = 'id';
        const COLUMN_FIF_NAZWA            = 'nazwa';
        const COLUMN_FIF_EMAIL            = 'email';
        
        const COLUMN_KLN_ID                 = 'id';
        const COLUMN_KLN_ADRES              = 'adres';
        const COLUMN_KLN_NAZWA              = 'nazwa';
        const COLUMN_KLN_NAZWA_ALT          = 'nazwa_alt';
        const COLUMN_KLN_ID_PANSTWO_POS     = 'id_panstwo_pos';
        const COLUMN_KLN_ID_PANSTWO_EGZ     = 'id_panstwo_egz';
        const COLUMN_KLN_ID_FIRMA           = 'id_firma';
        
        const COLUMN_ODK_ID                 = 'id';
        const COLUMN_ODK_ID_KLIENT          = 'id_klient';
        
        const COLUMN_UPR_ID                 = 'id';
        const COLUMN_UPR_NAZWA_UZYTKOWNIKA  = 'nazwa_uzytkownika';
        const COLUMN_UPR_IMIE_NAZWISKO      = 'imie_nazwisko';
        const COLUMN_UPR_HASLO              = 'haslo';
        const COLUMN_UPR_ADRES_EMAIL        = 'adres_email';
        const COLUMN_UPR_LICZBA_REKORDOW    = 'liczba_rekordow';
        const COLUMN_UPR_DODAWANIE_REKORDU  = 'dodawanie_rekordu';
        const COLUMN_UPR_DODAWANIE_KWERENDY = 'dodawanie_kwerendy';
        const COLUMN_UPR_DODAWANIE_ZETTLA   = 'dodawanie_zettla';
        const COLUMN_UPR_EDYCJA_REKORDU     = 'edycja_rekordu';
        const COLUMN_UPR_EDYCJA_GRUPOWA     = 'edycja_grupowa';
        const COLUMN_UPR_KASOWANIE_REKORDU  = 'kasowanie_rekordu';
        const COLUMN_UPR_DRUK_UMOWY         = 'druk_umowy';
        const COLUMN_UPR_DRUK_LISTY         = 'druk_listy';
        const COLUMN_UPR_DRUK_ROZLICZENIA   = 'druk_rozliczenia';
        const COLUMN_UPR_DRUK_ANKIETY       = 'druk_ankiety';
        const COLUMN_UPR_DRUK_BILETU        = 'druk_biletu';
        const COLUMN_UPR_EMAIL              = 'email';
        const COLUMN_UPR_MASOWY_EMAIL       = 'masowy_email';
        const COLUMN_UPR_MASOWY_SMS         = 'masowy_sms';
        const COLUMN_UPR_ZMIANA_UPRAWNIEN   = 'zmiana_uprawnien';
        const COLUMN_UPR_AKTYWNY            = 'aktywny';
        const COLUMN_UPR_WYGASA             = 'wygasa';
        const COLUMN_UPR_ID_FIRMA_FILIA     = 'id_firma_filia';
        
        const COLUMN_DIN_ID                 = 'id';
        const COLUMN_DIN_TELEFON            = 'telefon';
        const COLUMN_DIN_KOMORKA            = 'komorka';
        const COLUMN_DIN_INNY_TEL           = 'inny_tel';
        const COLUMN_DIN_EMAIL              = 'email';
        const COLUMN_DIN_IMIE               = 'imie';
        const COLUMN_DIN_ID_IMIE            = 'id_imie';
        const COLUMN_DIN_NAZWISKO           = 'nazwisko';
        const COLUMN_DIN_ID_PLEC            = 'id_plec';
        const COLUMN_DIN_PLEC               = 'plec';
        const COLUMN_DIN_DATA_URODZENIA     = 'data_urodzenia';
        const COLUMN_DIN_ID_MIEJSCOWOSC_UR  = 'id_miejscowosc_ur';
        const COLUMN_DIN_ID_MIEJSCOWOSC     = 'id_miejscowosc';
        const COLUMN_DIN_MIEJSCOWOSC        = 'miejscowosc';
        const COLUMN_DIN_MIEJSCOWOSC_UR     = 'miejscowosc_ur';
        const COLUMN_DIN_ULICA              = 'ulica';
        const COLUMN_DIN_KOD                = 'kod';
        const COLUMN_DIN_WYKSZTALCENIE      = 'wyksztalcenie';
        const COLUMN_DIN_ID_WYKSZTALCENIE   = 'id_wyksztalcenie';
        const COLUMN_DIN_ZAWOD              = 'zawod';
        const COLUMN_DIN_ID_ZAWOD           = 'id_zawod';
        const COLUMN_DIN_DATA_ZGLOSZENIA    = 'data_zgloszenia';
        const COLUMN_DIN_CHARAKTER          = 'charakter';
        const COLUMN_DIN_ID_CHARAKTER       = 'id_charakter';
        const COLUMN_DIN_DATA               = 'data';
        const COLUMN_DIN_ILOSC_TYG          = 'ilosc_tyg';
        const COLUMN_DIN_ID_ANKIETA         = 'id_ankieta';
        const COLUMN_DIN_ZRODLO             = 'zrodlo';
        const COLUMN_DIN_ID_ZRODLO          = 'id_zrodlo';
        const COLUMN_DIN_SOURCE             = 'source';
        
        const COLUMN_DOS_ID                 = 'id';
        const COLUMN_DOS_IMIE               = 'imie';
        const COLUMN_DOS_ID_IMIE            = 'id_imie';
        const COLUMN_DOS_NAZWISKO           = 'nazwisko';
        const COLUMN_DOS_PLEC               = 'plec';
        const COLUMN_DOS_ID_PLEC            = 'id_plec';
        const COLUMN_DOS_DATA_URODZENIA     = 'data_urodzenia';
        const COLUMN_DOS_ULICA              = 'ulica';
        const COLUMN_DOS_KOD                = 'kod';
        const COLUMN_DOS_WYKSZTALCENIE      = 'wyksztalcenie';
        const COLUMN_DOS_ID_WYKSZTALCENIE   = 'id_wyksztalcenie';
        const COLUMN_DOS_ZAWOD              = 'zawod';
        const COLUMN_DOS_ID_ZAWOD           = 'id_zawod';
        const COLUMN_DOS_ID_KONSULTANT      = 'id_konsultant';
        const COLUMN_DOS_CHARAKTER          = 'charakter';
        const COLUMN_DOS_ID_CHARAKTER       = 'id_charakter';
        const COLUMN_DOS_DATA               = 'data';
        const COLUMN_DOS_DATA_ZGLOSZENIA    = 'data_zgloszenia';
        const COLUMN_DOS_ILOSC_TYG          = 'ilosc_tyg';
        const COLUMN_DOS_ZRODLO             = 'zrodlo';
        const COLUMN_DOS_ID_ANKIETA         = 'id_ankieta';
        const COLUMN_DOS_ID_ZRODLO          = 'id_zrodlo';
        const COLUMN_DOS_NR_OBUWIA          = 'nr_obuwia';
        const COLUMN_DOS_MIEJSCOWOSC        = 'miejscowosc';
        const COLUMN_DOS_ID_MIEJSCOWOSC     = 'id_miejscowosc';
        const COLUMN_DOS_MIEJSCOWOSC_UR     = 'miejscowosc_ur';
        const COLUMN_DOS_ID_MIEJSCOWOSC_UR  = 'id_miejscowosc_ur';
        
        const COLUMN_STT_ID                 = 'id';
        const COLUMN_STT_ID_STATUS          = 'id_status';
        
        const COLUMN_KON_ID                 = 'id';
        const COLUMN_KON_ID_KONSULTANT      = 'id_konsultant';
        const COLUMN_KON_DATA               = 'data';
        
        const COLUMN_LOM_ID                 = 'id';
        const COLUMN_LOM_LOG_LEVEL          = 'log_level';
        const COLUMN_LOM_TIME               = 'time';
        const COLUMN_LOM_MSG                = 'msg';
        
        const COLUMN_TEL_ID                 = 'id';
        const COLUMN_TEL_NAZWA              = 'nazwa';
        const COLUMN_TEL_ID_WIERSZ          = 'id_wiersz';
        
        const COLUMN_TEK_ID                 = 'id';
        const COLUMN_TEK_NAZWA              = 'nazwa';
        const COLUMN_TEK_ID_WIERSZ          = 'id_wiersz';
        
        const COLUMN_TEI_ID                 = 'id';
        const COLUMN_TEI_NAZWA              = 'nazwa';
        const COLUMN_TEI_ID_WIERSZ          = 'id_wiersz';
        
        const COLUMN_EMA_ID                 = 'id';
        const COLUMN_EMA_NAZWA              = 'nazwa';
        const COLUMN_EMA_ID_WIERSZ          = 'id_wiersz';
        
        const COLUMN_DICT_ID                = 'id';
        const COLUMN_DICT_NAZWA             = 'nazwa';
        
        const COLUMN_UMO_ID                 = 'id';
        const COLUMN_UMO_ID_WIERSZ          = 'id_wiersz';
        const COLUMN_UMO_ID_UMIEJETNOSC     = 'id_umiejetnosc';
        
        const COLUMN_UOI_ID                 = 'id';
        const COLUMN_UOI_ID_WIERSZ          = 'id_wiersz';
        const COLUMN_UOI_ID_UMIEJETNOSC     = 'id_umiejetnosc';
        
        const COLUMN_PPJ_ID_PRAWO_JAZDY                 = 'id_prawka';      
        const COLUMN_PPJ_ID                             = 'id';
        const COLUMN_PPJ_ID_WIERSZ                      = 'id_wiersz';
        
        const COLUMN_PJI_ID                             = 'id';      
        const COLUMN_PJI_ID_PRAWO_JAZDY                 = 'id_prawka';      
        
        const COLUMN_ZNJ_ID                             = 'id';
        const COLUMN_ZNJ_ID_ZNANY_JEZYK                 = 'id_znany_jezyk';
        const COLUMN_JIN_ID                             = 'id';
        const COLUMN_ZNJ_ID_JEZYK                       = 'id_jezyk';
        const COLUMN_ZNJ_JEZYK                          = 'jezyk';
        const COLUMN_JIN_ID_JEZYK                       = 'id_jezyk';
        const COLUMN_ZNJ_ID_POZIOM                      = 'id_poziom';
        const COLUMN_ZNJ_POZIOM                         = 'poziom';
        const COLUMN_JIN_ID_POZIOM                      = 'id_poziom';
        
        const COLUMN_ZJE_ID                             = 'id';
        const COLUMN_ZJE_ID_ZNANY_JEZYK                 = 'id_znany_jezyk';
        const COLUMN_ZJE_ID_KONSULTANT                  = 'id_konsultant';
        const COLUMN_ZJE_DATA                           = 'data';
        
        const COLUMN_DDO_ID                             = 'id';
        const COLUMN_DDO_ID_OSOBA                       = 'id_osoba';
        const COLUMN_DDO_ID_DANE_DODATKOWE_LISTA        = 'id_dane_dodatkowe_lista';
        const COLUMN_DDO_WARTOSC                        = 'wartosc';
        
        const COLUMN_WAK_ID                             = 'id';
        const COLUMN_WAK_ID_KLIENT                      = 'id_klient';
        const COLUMN_WAK_ID_ODDZIAL                     = 'id_oddzial';
        const COLUMN_WAK_DATA_WYJAZDU                   = 'data_wyjazdu';
        const COLUMN_WAK_ILOSC_KOBIET                   = 'ilosc_kobiet';
        const COLUMN_WAK_ILOSC_MEZCZYZN                 = 'ilosc_mezczyzn';
        const COLUMN_WAK_ILOSC_TYG                      = 'ilosc_tyg';
        const COLUMN_WAK_ID_KONSULTANT                  = 'id_konsultant';
        const COLUMN_WAK_DATA_WPISU                     = 'data_wpisu';
        const COLUMN_WAK_DOKLADNY                       = 'dokladny';
        const COLUMN_WAK_WIDOCZNE_WWW                   = 'widoczne_www';
        
        const COLUMN_WAS_ID                             = 'id';
        const COLUMN_WAS_ID_KLIENT                      = 'id_klient';
        const COLUMN_WAS_ID_ODDZIAL                     = 'id_oddzial';
        const COLUMN_WAS_DATA_WYJAZDU                   = 'data_wyjazdu';
        const COLUMN_WAS_DATA_WPISU                     = 'data_wpisu';
        const COLUMN_WAS_ILOSC_TYG                      = 'ilosc_tyg';
        const COLUMN_WAS_KLIENT                         = 'klient';
        const COLUMN_WAS_STANOWISKO                     = 'stanowisko';
        const COLUMN_WAS_PANSTWO                        = 'panstwo';
        const COLUMN_WAS_BIURO                          = 'biuro';
        const COLUMN_WAS_ODDZIAL                        = 'oddzial';
        const COLUMN_WAS_OPIS                           = 'opis';
        
        const COLUMN_MDO_ID_OSOBA                       = 'id_osoba';
        const COLUMN_MDO_DANE                           = 'dane';
        
        const COLUMN_MDI_ID_OSOBA                       = 'id_osoba';
        const COLUMN_MDI_DANE                           = 'dane';
        
        //pseudo column, blob array column key
        const COLUMN_MD_DANE_CVURL                      = 'cvUrl';
        
        const COLUMN_DDIL_ID_DANE_DODATKOWE_LISTA       = 'id_dane_dodatkowe_lista';
        
        const COLUMN_PPR_ID_WIERSZ                      = 'id_wiersz';
        const COLUMN_PPR_ID                             = 'id';
        const COLUMN_PPR_NAZWA                          = 'nazwa';
        const COLUMN_PPR_PANSTWO                        = 'panstwo';
        const COLUMN_PPR_MIASTO                         = 'miasto';
        const COLUMN_PPR_KLIENT                         = 'klient';
        const COLUMN_PPR_AGENCJA                        = 'agencja';
        const COLUMN_PPR_DATA                           = 'data';
        const COLUMN_PPR_ID_GRUPA_ZAWODOWA              = 'id_grupa_zawodowa';
        const COLUMN_PPR_ID_ODDZIALY_KLIENT             = 'id_oddzialy_klient';
        
        const COLUMN_PPA_ID_WIERSZ                      = 'id_wiersz';
        const COLUMN_PPA_ID                             = 'id';
        const COLUMN_PPA_NAZWA                          = 'nazwa';
        const COLUMN_PPA_AGENCJA                        = 'agencja';
        const COLUMN_PPA_ID_GRUPA_ZAWODOWA              = 'id_grupa_zawodowa';
        
        const COLUMN_DDA_ID                             = 'id';
        const COLUMN_DDA_ID_OSOBA                       = 'id_osoba';
        const COLUMN_DDA_ID_DANE_DODATKOWE_LISTA        = 'id_dane_dodatkowe_internet_lista';
        const COLUMN_DDA_WARTOSC                        = 'wartosc';
        
        const COLUMN_DDL_EDYCJA                         = 'edycja';
        const COLUMN_DDL_ID_TYP                         = 'id_typ';
        const COLUMN_DDL_NAZWA_WYSWIETLANA              = 'nazwa_wyswietlana';
        
        const COLUMN_JEZ_JEZYK                          = 'jezyk';
        const COLUMN_JEZ_POZIOM                         = 'poziom';
        const COLUMN_GRU_GRUPA_ZAWODOWA                 = 'grupa_zawodowa';
        
        const COLUMN_TZR_WIDOCZNE                       = 'widoczne';
        
        const COLUMN_ZDZ_ID                             = 'id';
        const COLUMN_ZDZ_ZRODLO                         = 'zrodlo';
        const COLUMN_ZDZ_POLE                           = 'pole';
        const COLUMN_ZDZ_WARTOSC                        = 'wartosc';
        
        const COLUMN_KRF_KOD                            = 'kod';
        
        const COLUMN_UAD_KOD                            = 'kod';
        const COLUMN_UAD_TRESC                          = 'tresc';
        
        const COLUMN_SKA_TRESC                          = 'tresc';
        const COLUMN_SKA_WARUNEK                        = 'warunek';
        const COLUMN_SKA_ID                             = 'id';
        
        const COLUMN_OPR_ID                             = 'id_oddzialy_klient';
        const COLUMN_OPR_TYP                            = 'typ';
        const COLUMN_OPR_OPIS                           = 'opis';
        const COLUMN_OPR_ZRODLO                         = 'zrodlo';
        
        const COLUMN_ZDN_ID                             = 'id';
        const COLUMN_ZDN_DATA                           = 'data';
        const COLUMN_ZDN_PROBLEM                        = 'problem';
        const COLUMN_ZDN_ID_KONSULTANT                  = 'id_konsultant';
        const COLUMN_ZDN_INSERT_DATA                    = 'data_wpisu';
        const COLUMN_ZDN_ACTIVE                         = 'active';
        const COLUMN_ZDN_ROW_ID                         = 'id_wiersz';
        
        const COLUMN_ZDK_DANE_ZAPYTANIA                 = 'dane_zapytania';
        const COLUMN_ZDK_ID_UPRAWNIENIA                 = 'id_uprawnienia';
        
        const COLUMN_DSK_ID                             = 'id';
        const COLUMN_DSK_ID_DANE_OSOBOWE                = 'id_dane_osobowe';
        const COLUMN_DSK_ID_LISTA_DOKUMENTY_SKAN        = 'id_lista_dokumenty_skan';
        const COLUMN_DSK_NAZWA_PLIK                     = 'nazwa_plik';
        
        const COLUMN_ZKK_KOMORKA                        = 'komorka';
        const COLUMN_ZKK_EMAIL                          = 'email';
        
        const COLUMN_WKA_ID_WAKAT                       = 'id_wakat';
        const COLUMN_WKA_ID_DECYZJA                     = 'id_decyzja';
        
        const COLUMN_DODOS_ID                           = "id";
        const COLUMN_DODOS_ID_OSOBY_DOD                 = "id_osoby_dod";
        
        const COLUMN_JRG_ID                             = "id";
        const COLUMN_JRG_ROK                            = "rok";
        const COLUMN_JRG_PLIK                           = "plik";
        const COLUMN_JRG_ID_KLIENT                      = "id_klient";
        
        const COLUMN_ODB_ID                            = 'id';
        const COLUMN_ODB_ID_KONSULTANT                 = 'id_konsultant';
        const COLUMN_ODB_DATA                          = 'data';
        const COLUMN_ODB_ROK                           = 'rok';
        
        const COLUMN_BIURA_ID                           = 'id';
        const COLUMN_BIURA_NAME                         = 'nazwa';
        
        const COLUMN_REK_ID                             = 'id';
        const COLUMN_REK_DATA                           = 'data';
        const COLUMN_REK_PROBLEM                        = 'problem';
        const COLUMN_REK_ID_KONSULT                     = 'id_konsultant';
        const COLUMN_REK_ID_BIURA                       = 'id_msc_biura';
        const COLUMN_REK_ODP                            = 'odpowiedz';
        const COLUMN_REK_ID_REKLAMACJE                  = 'id_reklamacje'; 
        
        const DDL_COLUMN_WZROST                         = 'wzrost';
        
        const METHOD_ESCAPE_INT             = 'escapeInt';
        const METHOD_ESCAPE_STRING          = 'escapeString';
        const METHOD_ESCAPE_BOOL            = 'escapeBool';
        
        const VIEW_EDYCJA_OSOBY       = 'edycja_osoby';
        //const VIEW_OSOBA_INTERNET     = 'osoba_internet_pokaz';
        const VIEW_OSOBA_INTERNET     = 'osoba_internet';
        
        const STATUS_WYJEZDZAJACY     = STATUS_WYJEZDZAJACY;
        const STATUS_ID_WYJEZDZAJACY  = ID_STATUS_WYJEZDZAJACY;
        
        const DD_ID_TYP_BOOL              = 1;
        const DD_ID_TYP_INT               = 2;
        const DD_ID_TYP_STRING            = 3;
        const DD_ID_TYP_DATE              = 4;
        const DD_ID_TYP_DATERANGE         = 5;
        
        /**
         * @var dal
         */
        protected $dal;
        /**
        * @desc RRRR-MM-DD today date
        */
        protected $dzis;
        
        public function __construct() {
            
            $this->dal = dal::getInstance();
            $this->dzis = date('Y-m-d');
        }
        
        /**
        * @desc return select data in the form of array with 2 fields: data, rowsCount
        * @param array the list with the data
        * @param int the count of rows
        * @param optional string the column name to use value for array reindexation
        */
        protected function formatDataOutput ($dataList, $dataCount, $uniqueIdIndex = null) {

            $dataList = $this->castArray($dataList, $uniqueIdIndex);
            
            //we won't check size match, due to dal calculation implementation (it makes sizeof there :) )
            return array (
                self::RESULT_FIELD_DATA          => $dataList,
                self::RESULT_FIELD_ROWS_COUNT    => $dataCount,
            );
        }
        
        /**
        * @desc Method escapes data submited to db according to configuration provided. 
        * configuration is in the form of array mapping key (column name) to value, which is a valid callback array,
        * i.e. array($this->dal, 'escapeString'). The configuration table cuts off any unapproved data provided
        */
        protected function escapeParamsList ($configuration, $dataList) { //array
            
            $_dataList = array();
            
            foreach ($configuration as $columnKey => $columnEscCallback) {
                //avoid skipping nulls which isset does; function inefficient for large arrays, isset preferred
                if (array_key_exists($columnKey, $dataList))
                    $_dataList[$columnKey] = call_user_func_array($columnEscCallback, array($dataList[$columnKey]));
            }
            
            return $_dataList;
        }
        
        protected function createSetClause ($dataList) {
            
            $elements = array();
            
            foreach ($dataList as $column => $value) {
                if (is_int($value) || is_null($value)) {
                    if (is_null($value) || $value === 0)
                        $value = 'null';
                    $elements[] = $column.'='.$value;
                } else {
                    $elements[] = $column.'=\''.$value.'\'';
                }
            }
            
            return implode(',', $elements);
        }
        
        protected function createInsertClause($dataList) {
            
            $setCols = array();
            $setValues = array();
                
            foreach ($dataList as $column => $value) {
                    
                $setCols[] = $column;
                if (is_int($value) || is_null($value)) {
                    if (is_null($value) || $value === 0)
                        $value = 'null';
                    $setValues[] = $value;
                } else {
                    
                    $setValues[] = '\''.$value.'\'';
                }
            }
            
            return '('.implode(',', $setCols).') values ('.implode(',', $setValues).')';
        }
        
        protected function removeNotRequiredEmpty($data, $notRequiredList) {
            
            foreach ($notRequiredList as $notRequired) {
                
                if (empty($data[$notRequired])) {
                    
                    unset($data[$notRequired]);
                }
            }
            
            return $data;
        }
        
        //to ma potencjal rekurencyjny, optional refactor
        protected function castArray ($dataList, $uniqueIdIndex = null) {
            
            $dataCopy = array();
            
            if (is_array($dataList))
            foreach ($dataList as $dataIndex => $dataRow) {
                
                if ($uniqueIdIndex !== null) {
                    
                    $dataIndex = $dataRow[$uniqueIdIndex];
                }
                
                if (is_array($dataRow)) {
                    
                    foreach ($dataRow as $dataRowIndex => $data) {
                        
                        $dataCopy[$dataIndex][$dataRowIndex] = $this->castValue($data);
                    }
                } else {
                    
                    $dataCopy[$dataIndex] = $this->castValue($dataRow);
                }
            }
            
            return $dataCopy;
        }
        
        protected function getDictionaryData ($table) {
            
            $query = 'select '.Model::COLUMN_DICT_ID.', '.Model::COLUMN_DICT_NAZWA.' from '.$table.' order by  '.Model::COLUMN_DICT_NAZWA;
            
            $result = $this->dal->PobierzDane($query, $recordsCount);
            
            if ($recordsCount == 0)
                return null;
                
            return $this->formatDataOutput($result, $recordsCount);
        }
        
        private function castValue ($value) {

            if ('t' === $value || 'true' === $value)
                return true;
                
            if ('f' === $value || 'false' === $value)
                return false;
                
            if ('null' === $value)
                return null;
            
            if (is_numeric($value) && $value < 2147483647 && !$this->isNumberWithZeroPrefix($value)) {
                return (int)$value;
            }
                            
            return $value;            
        }
        
        /**
         * Checking is $value is a number with min 2 digits and have leadnig zero
         * @param string $value
         * @return bool
         */
        private function isNumberWithZeroPrefix($value) {
            return (strlen($value) > 1 && $value[0] == '0');
        }
    }
    
    //the most basic exception all exceptions should derive from (todo: find suitable place for it)
    abstract class ProjectException extends Exception {
    	
    	protected $innerException;
    	
    	public function __construct($message = '', $code = 0, $innerException = null) { //Exception
    		
    		parent::__construct($message, $code);
    		$this->innerException = $innerException;
    	}
    	
    	public function getInnerException () {
    		
    		return $this->innerException;
    	}
    }
    
    class DBException extends ProjectException {}
    
    class DBQueryErrorException extends DBException {}
    class DBInvalidDataException extends DBException {} //trying to add bad/inconsistent data
    class DBConflictDataException extends DBException {
        
        protected $conflictId = 0;
        
        public function __construct($conflictId, $message = '', $code = 0, $innerException = null) { //Exception
            
            parent::__construct($message, $code);
            $this->conflictId = $conflictId;
            $this->innerException = $innerException;
        }
        
        public function getConflictId() {
            
            return $this->conflictId;
        }
    } //data being attepted to add are conflicting with existing data
    class DBMissingDataException extends DBException {} //some relative data are missing
    
   