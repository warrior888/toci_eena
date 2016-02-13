<HTML>
<HEAD>
<META HTTP-EQUIV="Content-type" CONTENT="text/html; charset=UTF-8">
</head>
<?php
    setlocale(LC_ALL, 'pl_PL.UTF-8');
    $array  = array('ślęża', 'słabe', 'trutnik', 'słone', 'szama');
    
    echo strcoll('ślęża', 'słabe');
    echo "\n";
    usort($array, 'strcoll');
    var_dump($array);
?>
</html>