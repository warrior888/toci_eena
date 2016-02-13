<?php
    function zamien_przecinki($tekst, $delim = '<br />')
    {
        return str_replace(",", $delim, $tekst);   
    }
?>