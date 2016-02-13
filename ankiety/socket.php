<?php
    function zamien_na_ascii($msc)
    {
        $result = "";
        for ($i = 0; $i < strlen($msc); $i++)
        {
            $result .= ord($msc[$i])."|";
        }
        $result = substr($result, 0, strlen($result) - 1);
        return $result;
    }
    //ze wzgledu na zmiany w poczcie polskiej wypadaloby zrobic odwolanie post do formularza - ogolnie rzecz biorac uznaje to za daremny futer, aczkolwiek to mozliwe
    function pobierz_miasto($postalCode)
    {
        $query = "http://www.poczta-polska.pl/kody/?kod=".$postalCode."&page=kod&bkody="; //.php?kod=$postalCode&ulica=&miejscowosc=&powiat=&wojewodztwo=dowolne&action=search";
                                                  
        $url = parse_url($query);
        $host = $url["host"];
        $path = $url["path"] . "?" . $url["query"];
        $timeout = 30;
        $fp = fsockopen($host, 80, $errno, $errstr, $timeout);
        $buf = '';
        if ($fp)
        {
            fputs ($fp, "POST $path HTTP/1.0\nHost: " . $host . "\n\n");
            while (!feof($fp))
            {
              $buf .= fgets($fp, 128);
            }
            $lines = split("\n", $buf);
            //echo $buf;
            fclose($fp);
        } 
        else 
        {
            echo("$errno, $errstr");
            return null;
        }
        foreach ($lines as $key => $value)
        {
            $miejscowosc = strchr($value, "gmina:");//miejscowo¶æ
            if (strlen($miejscowosc) > 0)
            {
                $miejscowosc = split("\r", $miejscowosc);
                $miejscowosc = $miejscowosc[0];
                $l = strpos($miejscowosc, "gmina:") + 6;
                $miejscowosc = substr($miejscowosc, $l, strlen($miejscowosc));
                $miejscowosc = trim(strip_tags($miejscowosc));
                //echo("$miejscowosc");
                break;
            }
        }
        //echo $miejscowosc;
        //$miejscowosc = zamien_na_ascii($miejscowosc); 
        //return "bartek";
        
        return trim($miejscowosc);
    }
?>