INSERT INTO strefa_odjazdu VALUES (4, 'IV strefa');

UPDATE strefy SET strefa_id = 4
where przewoznik_id = 4 AND strefy.msc_odjazdu_id IN (
	select id from msc_odjazdu where nazwa in (
	'Bolesławiec',
	'Brzeg',
	'Chojnów',
	'Dobrodzień',
	'Głogówek',
	'Głubczyce',
	'Głuchołazy',
	'Grodków',
	'Jawor',
	'Kędzierzyn - Koźle',
	'Kluczbork',
	'Kłodzko',
	'Krapkowice',
	'Legnica',
	'Niemodlin',
	'Nysa',
	'Olesno',
	'Oława',
	'Opole',
	'Otmuchów',
	'Ozimek',
	'Paczków',
	'Prudnik',
	'Strzelce Opolskie',
	'Strzegom',
	'Szprotawa',
	'Wrocław',
	'Zgorzelec',
	'Złotoryja',
	'Złoty Stok',
	'Żary') 
)