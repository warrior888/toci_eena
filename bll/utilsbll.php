<?php
    require_once 'bll/FileManager.php';

    //quite an adl feature in bll .....
    class Cache 
    {
        public static function set ($key, $value)
        {
            $_SESSION[$key] = $value;
        }
        
        public static function get ($key)
        {
            return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
        }
        
        public static function delete ($key) 
        {
            unset ($_SESSION[$key]);
        }
    }
    //why the fuck serialization is not embedde here ? no reason ? developer stupidity ?
    class PermanentCache
    {
        //initialize sets the right path
        protected static $path;
        
        public static function initialize()
        {
            self::$path = FileManager::getBasicPath().'tmp/';
            if (!is_dir(self::$path))
                mkdir(self::$path);
        }
        
        public static function set ($key, $value)
        {
            $isDir = is_dir(self::$path);
            if (!$isDir)
                $isDir = mkdir(self::$path);
            
            if ($isDir)
                file_put_contents(self::$path.$key, serialize($value));
        }
        
        public static function get ($key)
        {
            return file_exists(self::$path.$key) ? unserialize(file_get_contents(self::$path.$key)) : null;
        }
        
        public static function delete ($key) 
        {
            if (file_exists(self::$path.$key))
                unlink(self::$path.$key);
        }
    }
    
    PermanentCache::initialize();
    
    class UtilsBLL 
    {
        public static function GetIdForDictName($dictionary, $name)
        {
            $id = null;
            
            foreach ($dictionary as $key => $value)
            {
                if ($value[Model::COLUMN_DICT_NAZWA] == $name)
                {
                    return $value[Model::COLUMN_DICT_ID];
                }
            }
            
            return $id;
        }
    }