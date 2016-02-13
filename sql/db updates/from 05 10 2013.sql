update status set nazwa = 'Zwolniony' where id = 2;

select dane_osobowe.id, dane_osobowe.nazwisko, dane_osobowe.imie, dane_osobowe.data_urodzenia, 
	zatrudnienie.data_powrotu, zatrudnienie.id_status, klient.nazwa as klient, oddzialy_klient.nazwa as oddzial, 
	nieodpowiedni_powod.powod, zatrudnienie.id_pracownik, nieodpowiedni_powod.id_uprawnienia,
	u1.imie_nazwisko as konsultant_zatrudnienie, u2.imie_nazwisko as konsultant_powod
from dane_osobowe join zatrudnienie on dane_osobowe.id = zatrudnienie.id_osoba 
left join nieodpowiedni_powod on zatrudnienie.id = nieodpowiedni_powod.id_zatrudnienie
join klient on zatrudnienie.id_klient = klient.id
join oddzialy_klient on zatrudnienie.id_oddzial = oddzialy_klient.id
join uprawnienia u1 on zatrudnienie.id_pracownik = u1.id
left join uprawnienia u2 on nieodpowiedni_powod.id_uprawnienia = u2.id 
where zatrudnienie.id_status = 2 and nieodpowiedni_powod.powod is null;


insert into nieodpowiedni_powod (id_dane_osobowe, id_uprawnienia, id_zatrudnienie, powod) 
select dane_osobowe.id, 1, zatrudnienie.id, 'Powodu zwolneinia szukaj w ustaleniach' 
from dane_osobowe join zatrudnienie on dane_osobowe.id = zatrudnienie.id_osoba 
left join nieodpowiedni_powod on zatrudnienie.id = nieodpowiedni_powod.id_zatrudnienie 
join klient on zatrudnienie.id_klient = klient.id 
join oddzialy_klient on zatrudnienie.id_oddzial = oddzialy_klient.id 
join uprawnienia u1 on zatrudnienie.id_pracownik = u1.id 
left join uprawnienia u2 on nieodpowiedni_powod.id_uprawnienia = u2.id 
where zatrudnienie.id_status = 2 and nieodpowiedni_powod.powod is null;