<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
  <title>Preferencje</title>
<link href="../css/style.css" rel="stylesheet" type="text/css"></head>
</head>
<?php
    @session_start();
    if (empty($_SESSION['uzytkownik']))
    {
        require("../log_in.php");
    }
    else
    {
        require('excel_reader.php');
        require('db_reader.php');
        require('../vaElClass.php');

        function drawGrid($DataSet)
        {
            $gridObj = new SimpleGridView();
            $gridObj->SetTableStyle('border = \'1\' align = \'CENTER\'');
            $gridObj->SetTrStyle('');
            $gridObj->SetTdStyle('nowrap align = \'CENTER\'');
            $gridObj->SetEmptyString("--------");
            $gridObj->SetIdColumnVisible(true);
            $gridObj->SetDataSource($DataSet);
            $gridObj->DataBind();
        }
        
        function ShowStatus($arrayMod, $arrayNMod, $arrayNF, $arrayDbChanged)
        {
            $dsNotFound = new DataSet();
            $dsNotMod = new DataSet();
            $dsMod = new DataSet();
            $dsDbChanged = new DataSet();
            $header = array("Imię i nazwisko", "Data urodzenia", "Sofi", "Klient");           
            
            $dsNotFound->SetHeaders($header);
            $dsNotFound->SetData($arrayNF);
            $dsMod->SetHeaders($header);
            $dsMod->SetData($arrayMod);
            $dsNotMod->SetHeaders($header);
            $dsNotMod->SetData($arrayNMod);
            $dsDbChanged->SetData($arrayDbChanged);
            $dsDbChanged->SetHeaders($header);
            
            echo('<div align = \'CENTER\'>Nie znaleziono w bazie:</div>');
            drawGrid($dsNotFound);
            echo('<div align = \'CENTER\'>Bez zmian:</div>');
            drawGrid($dsNotMod);
            echo('<div align = \'CENTER\'>Zmodyfikowano:</div>');
            drawGrid($dsMod);  
            echo('<div align = \'CENTER\'>Przesunieto powrot:</div>');
            drawGrid($dsDbChanged);  
        }
        
        function CreateRow($imie_nawisko, $data_urodzenia, $sofi, $klient)
        {
            $row = array();
            
            $row[] = $imie_nawisko;
            $row[] = $data_urodzenia;
            $row[] = $sofi;
            $row[] = $klient;
            
            return $row;
        }
        
        function Zmien($mode, $date, $office)
        {
            $obj = new ExcelReader('zmiana_statusu.xls');
            $objReader = new DbReader();
            $oddzialy = $objReader->GetOddzialy($office);
            $tab = $obj->GetData();
            $arrayNotFound = array();
            $arrayNotMod = array();
            $arrayMod = array();
        
            switch ($mode)
            {
                case "status":
                {
                    for ($i = 0; $i < $tab->GetCount(); $i++)
                   {
                        $czlowiek = $tab->GetStatusObj($i);
                        switch ($czlowiek->ChangeStatus($date, $oddzialy))
                        {
                            case "Mod":
                            {
                                $arrayMod[] = CreateRow($czlowiek->GetImieNazwisko(), $czlowiek->GetDataUrodzenia(), $czlowiek->GetSofi(), $czlowiek->GetKlient());
                                break;
                            }   
                            case "NMod":
                            {
                                $arrayNotMod[] = CreateRow($czlowiek->GetImieNazwisko(), $czlowiek->GetDataUrodzenia(), $czlowiek->GetSofi(), $czlowiek->GetKlient());
                                break;   
                            }
                            case "NF":
                            {
                                $arrayNotFound[] = CreateRow($czlowiek->GetImieNazwisko(), $czlowiek->GetDataUrodzenia(), $czlowiek->GetSofi(), $czlowiek->GetKlient());
                                break;   
                            }
                        }
                    }

                    $czlowiek = new StatusChangeData();

                    ShowStatus($arrayMod, $arrayNotMod, $arrayNotFound, $objReader->GetPeopleFromDb($objReader->GetIdFromDbForStatus($oddzialy, $czlowiek->GetTabId())));
                    
                    $czlowiek->ToPasive($oddzialy);
                    
                    break;
                }
                case "data":
                {
                   for ($i = 0; $i < $tab->GetCount(); $i++)
                   {
                        $czlowiek = $tab->GetStatusObj($i);
                        switch ($czlowiek->ChangeDate($date, $oddzialy))
                        {
                            case "Mod":
                            {
                                $arrayMod[] = CreateRow($czlowiek->GetImieNazwisko(), $czlowiek->GetDataUrodzenia(), $czlowiek->GetSofi(), $czlowiek->GetKlient());
                                break;
                            }   
                            case "NMod":
                            {
                                $arrayNotMod[] = CreateRow($czlowiek->GetImieNazwisko(), $czlowiek->GetDataUrodzenia(), $czlowiek->GetSofi(), $czlowiek->GetKlient());
                                break;   
                            }
                            case "NF":
                            {
                                $arrayNotFound[] = CreateRow($czlowiek->GetImieNazwisko(), $czlowiek->GetDataUrodzenia(), $czlowiek->GetSofi(), $czlowiek->GetKlient());
                                break;   
                            }
                        }
                    }

                    $czlowiek = new StatusChangeData();

                    ShowStatus($arrayMod, $arrayNotMod, $arrayNotFound, $objReader->GetPeopleFromDb($objReader->GetIdFromDb($date, $oddzialy, $czlowiek->GetTabId()))); 
                    
                    $czlowiek->AddWeek($date, $oddzialy);
                    
                    break;
                }
            }   
        }
        
        if (isset($_POST['Ok']))
        {
            $target_path = 'zmiana_statusu.xls';
            if (move_uploaded_file($_FILES['plik']['tmp_name'], $target_path))
            {
                //echo('Udalo sie :P');
                Zmien($_POST['akcja'], $_POST['data'], $_POST['biuro']);
            }
            else
		    {
			    echo "Nie udalo sie.";
		    }
        }
        
        $controls = new valControl();
        
        echo("<form method=\"POST\" action='".$_SERVER['PHP_SELF']."' enctype=\"multipart/form-data\"><table align=\"CENTER\">");
        echo('<table align = \'CENTER\'>');
        echo('<tr>');
        echo('<td>Podaj plik xls:</td>');
        echo("<td><input type=\"file\" name=\"plik\" size = \"40\" class=\"formfield\" value = '{$_POST['plik']}' /></td>");
        echo('</tr>');
        echo('<tr>');
        echo('<td>Podaj date:</td>');
        echo('<td>');
        echo($controls->AddDatebox('data', 'idData', $_POST['data'], 10, 10));
        echo('</td>');
        echo('</tr>');
        echo('<tr>');
        echo('<td>Podaj biuro:</td>');
        echo('<td>');
        echo($controls->AddSelectRandomQuery('biuro', 'idBiuro', '', 'SELECT * FROM msc_biura ORDER BY nazwa ASC;', $_POST['biuro'], 'hBiuro', 'nazwa', 'id', ''));
        echo('</td>');
        echo('</tr>');
        echo('<tr>');
        echo('<td>Wybierz akcje:</td>');
        echo('<td><input type = \'radio\' name = \'akcja\' value = \'data\' />Zmiena daty<input type = \'radio\' name = \'akcja\' value = \'status\' />Zmiana statusu</td>');
        echo('</tr>');
        echo('<tr>');
        echo('<td></td>');
        echo('<td>');
        echo($controls->AddSubmit('Ok', 'idOk', 'Ok', ''));
        echo('</td>');
        echo('</tr>');
        echo('</table>');
        echo("</form>");
    }
?>
</html>