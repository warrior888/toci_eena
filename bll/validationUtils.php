<?php
    class ValidationUtils 
    {
        public static function validateInt ($data, $regex = null)
        {
            $_data = (int)$data;
            
            return ($_data == $data);
        }
        
        public static function validateDate ($date)
        {
            if (!strstr($date, '-') || strlen($date) != 10)
                return false;
                
            list($year, $month, $day) = explode('-', $date);
            return checkdate($month, $day, $year);
        }
        
        public static function validateDateFuture ($date)
        {
            return self::validateDate($date) ? (time() < strtotime($date)) : false;
        }
        
        public static function validateDatePast ($date)
        {
            return self::validateDate($date) ? (time() > strtotime($date)) : false;
        }
        
        public static function validatePhone ($phone, $isExtra = false) {
            
            $lenght = strlen($phone);
            
            if ($lenght <= 9) // is extra actually answers the same question (in theory)
                $phone = (int)$phone;
            
            return is_numeric($phone) && ($isExtra === true ? ($lenght >= 9) : ($lenght == 9));
        }
        
        public static function validateExtraPhone ($phone) {
            
            return preg_match('/^\d{9,16}$/', $phone);
        }
        
        public static function validateEmail ($email)
        {
            //filter_var if is defined ble ble
            
            $result = filter_var($email, FILTER_VALIDATE_EMAIL);
            if (false === $result)
                return false;
                
            return true;
        }
    }
    
    if (!is_callable('filter_var'))
    {
        function filter_var(){return true;}
        define('FILTER_VALIDATE_EMAIL', '');
    }
?>