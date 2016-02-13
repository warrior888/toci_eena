<HTML>
<HEAD>
  <META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=iso-8859-2">
  <script language="javascript" src="../js/script.js"></script>
<link href="../css/style.css" rel="stylesheet" type="text/css">
</head>
<body>
<?php
    function CreateFormularzXLS($xls_name, $dsArray, $url)
    {
        $xlsObj = new MergeSheetsToXls($xls_name);

        for ($i = 0; $i < count($dsArray); $i++)
        {
            $row = $dsArray[$i];
            $xlsObj->AddSheet($row[1], $row[0]);                
        }   
        
        $xlsObj->SaveToDisc();
        
        return "<a href=".$xls_name.">".$url.".</a>";
    }
    // ś ą
    @session_start();
    if (empty($_SESSION['uzytkownik'])) //(false)
    {
        require('../log_in.php');
    }
    else
    {
        require('../naglowek.php');
	    require('../conf.php');
        require('excel_class.php');
        include('bilans_roczny.php');
        
        if (isset($_POST['marszalek']))
        {
            $bilansRoczny = new YearBalance();
            $podpunkt = new GeneralBalance();
            //echo $_POST['year'];
            $bilansRoczny->SetYear($_POST['year']);
            $dsArray = $bilansRoczny->GetData();
            
            $dssArray = $podpunkt->GetData($_POST['year']);
            $dsArray[count($dsArray)] = $dssArray;
                            
            $xls_name = "Bilans.xls";
                    
            echo CreateFormularzXLS($xls_name, $dsArray, "Plik Excel z formularzem informacji o działalności agencji zatrudnienia");
            
        }
        //xls_name -> nazwa pliku wynikowego
        //dsArray -> tablica postaci array(array(dsObj, nazwaArkusza), array(dsObj1, nazwaArkusza1), ...)
        
        //a tu piszemy cala reszte :P
        require('../stopka.php');
    }
?>
</body>
</html>
