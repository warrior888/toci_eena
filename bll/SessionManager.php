<?php

    class SessionManager {
        
        public static function set ($key, $value) {
            
            $_SESSION[$key] = $value;
        }
        
        public static function get ($key) {
            
            return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
        }
        
        public static function delete ($key) {
            
            unset($_SESSION[$key]);
        }
        
        public static function getSessionId () {
        	
        	return session_id();
        }
    }