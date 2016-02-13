<?php

    require_once '../bll/TextUtils.php';
    

    echo TextUtils::getTextLength('tnr', 'bartek');
    $result = TextUtils::wrapText('tnr', 'bartek/dsagdfda/fsdagsdf/gdagsdsqwdghdsf/daggsghsdfhfs/gdaghshfhs/dshsdfhfshsfh/dhssfhshsd/dshfhshfsd', 120, '/');
    
    var_dump($result);
    