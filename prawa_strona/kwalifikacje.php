<?php 

    require_once '../conf.php';
    require_once 'ui/HelpersUI.php';
    require_once 'bll/FileManager.php';
    require_once 'dal/DALDaneOsobowe.php';
    
    $qualificationsHtml = '<html>'.HelpersUI::_addHtmlBasicHeaders(HelpersUI::PATH_LEVEL_1).'<body>';
    
    $qualificationsHtml .= '<div class="qualificationsContainer">';
    require 'kontakt.php';
    $contactsView = new ContactsView();
    
    $html = $contactsView->execute();
    
    $qualificationsHtml .= $html;
    $qualificationsHtml .= '</div>';
    
    $qualificationsHtml .= '<div class="qualificationsContainer" style="height: 312px;"><div style="height: 313px; overflow: auto;">';
    require 'umiejetnosci.php';
    $qualificationsHtml .= $html;
    $qualificationsHtml .= '</div></div>';
    
    $qualificationsHtml .= '<div class="qualificationsContainer">';
    require 'prawo_jazdy.php';
    $qualificationsHtml .= $html;
    $qualificationsHtml .= '</div>';
    
    $qualificationsHtml .= '<div class="qualificationsContainer">';
    require 'znane_jezyki.php';
    $qualificationsHtml .= $languagesHtml;
    $qualificationsHtml .= '</div>';
// style="clear: both;"
    $qualificationsHtml .= '<div style="height: 312px;"><div style="height: 313px; overflow: auto;" class="qualificationsContainer">';
    require 'semantyka.php';
    $qualificationsHtml .= $settlementsHtml;
    $qualificationsHtml .= '</div></div>';
    
    $fileManager = new FileManager();
    $dalDaneOsobowe = new DALDaneOsobowe();
    $idOsoba = Utils::PodajIdOsoba();
    $personIDDoc = $dalDaneOsobowe->getScannerDocuments($idOsoba, DOKUMENTY_LISTA_DO_AWERS_ID);
    
    if ($personIDDoc)
    {
        $personIDScan = $personIDDoc[Model::RESULT_FIELD_DATA][0][Model::COLUMN_DSK_NAZWA_PLIK];
        if ($fileManager->scanDocExists($id_osoba, $personIDScan))
        {
            $qualificationsHtml .= '<img height="250" src="file.php?'.ID_OSOBA.'='.$id_osoba.'&name='.$personIDScan.'"></img>';
            //$qualificationsHtml .= '<a href="file.php?'.ID_OSOBA.'='.$id_osoba.'&name='.$personIDScan.'">pokaz</a>';
        }
    }
    
    $qualificationsHtml .= '<div class="qualificationsContainer">';
    require 'poprzedni_pracodawca.php';
    $qualificationsHtml .= $outHtml;
    $qualificationsHtml .= '</div>';
    
    $qualificationsHtml .= '</body></html>';
    
    echo $qualificationsHtml;