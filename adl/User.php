<?php
    require_once 'adl/Adl.php';
    require_once 'bll/BLLUprawnienia.php';
    require_once 'bll/SessionManager.php';
    require_once 'bll/LogManager.php';
    require_once 'bll/utilsbll.php';

    /*TODO
        - expiracja hasla - force pass change
        - walidacja sily hasla
        - blokada konta po 3 probie ?
    */
    
    class User extends Adl {
        
        const SESSIONS_ALL_KEY          = 'AllSessions';
        
        const PRIV_DODAWANIE_REKORDU  = Model::COLUMN_UPR_DODAWANIE_REKORDU;//  'dodawanie_rekordu';
        const PRIV_DODAWANIE_KWERENDY = Model::COLUMN_UPR_DODAWANIE_KWERENDY;// 'dodawanie_kwerendy';
        const PRIV_DODAWANIE_ZETTLA   = Model::COLUMN_UPR_DODAWANIE_ZETTLA; // 'dodawanie_zettla';
        const PRIV_EDYCJA_REKORDU     = Model::COLUMN_UPR_EDYCJA_REKORDU; //'edycja_rekordu';
        const PRIV_EDYCJA_GRUPOWA     = Model::COLUMN_UPR_EDYCJA_GRUPOWA; //'edycja_grupowa';
        const PRIV_KASOWANIE_REKORDU  = Model::COLUMN_UPR_KASOWANIE_REKORDU; //'kasowanie_rekordu';
        const PRIV_DRUK_UMOWY         = Model::COLUMN_UPR_DRUK_UMOWY; //'druk_umowy';
        const PRIV_DRUK_LISTY         = Model::COLUMN_UPR_DRUK_LISTY; // 'druk_listy';
        const PRIV_DRUK_ROZLICZENIA   = Model::COLUMN_UPR_DRUK_ROZLICZENIA; // 'druk_rozliczenia';
        const PRIV_DRUK_ANKIETY       = Model::COLUMN_UPR_DRUK_ANKIETY; // 'druk_ankiety';
        const PRIV_DRUK_BILETU        = Model::COLUMN_UPR_DRUK_BILETU; // 'druk_biletu';
        const PRIV_EMAIL              = Model::COLUMN_UPR_EMAIL; // 'email';
        const PRIV_MASOWY_EMAIL       = Model::COLUMN_UPR_MASOWY_EMAIL; // 'masowy_email';
        const PRIV_MASOWY_SMS         = Model::COLUMN_UPR_MASOWY_SMS; // 'masowy_sms';
        const PRIV_ZMIANA_UPRAWNIEN   = Model::COLUMN_UPR_ZMIANA_UPRAWNIEN; // 'zmiana_uprawnien';
        const PRIV_AKTYWNY            = Model::COLUMN_UPR_AKTYWNY;// 'aktywny';
        
        const PRIV_NONE_REQUIRED      = 'none_required';
        
        const PRIVILEDGE_ALLOWED      = 1;
        const PRIVILEDGE_NOT_ALLOWED  = 0;
        
        
        const PASSWORD_EXPIRE_DAYS          = 45;
        const PASSWORD_EXPIRE_WARN_DAYS     = 5;
        
        protected $userData;
        protected $allUsersData;
        protected static $instance;

        /**
         * @return User
         */
        public static function getInstance () {
            
            CommonUtils::SessionStart();
            if (!self::$instance)
                self::$instance = new User();
            
            return self::$instance;
        }
        //NOTE HACK : this is made public only because of extension from adl - this class is a singleton, hence below dirty if
        //calling this constructor is unfortunately anyway possible and harmfull and unallowed
        public function __construct() {
            
            //checking if there is already an instance
            if (self::$instance instanceof User) {
                throw new LogicServerErrorException('Instantiation of User singleton - unallowed');
            } else {
                parent::__construct(new BLLUprawnienia());
                $sessionData = SessionManager::get(self::SESSION_FIELD_USER);
            
                if ($sessionData)
                    $this->userData = $sessionData;
                    
                $this->allUsersData = PermanentCache::get(self::SESSIONS_ALL_KEY);
                
                if (!$this->allUsersData)
                    $this->allUsersData = array();
            }
        }
        
        public function hasSession () {
            
            return (isset($this->userData[Model::COLUMN_UPR_ID]) && ($this->isActive() === true));
        }
        
        public function isLogged () {
            
            return (($this->hasSession() === true) && ($this->isExpired() === false));
            
        }
        
        public function isActive () {
            
            return ($this->userData[Model::COLUMN_UPR_AKTYWNY]);
        }
        
        public function isExpired () {
            
            $isExpired = ($this->userData[Model::COLUMN_UPR_WYGASA] < $this->today);
            
            if (true === $isExpired)
                throw new AccountExpiredException();
            
            return $isExpired;
        }
        
        public function isExpiring () {
            
            $closeFuture = time() + 24 * 60 * 60 * self::PASSWORD_EXPIRE_WARN_DAYS;
            
            return ($this->userData[Model::COLUMN_UPR_WYGASA] < $closeFuture);
        }
        
        public function logIn ($userName, $password) {
            
            try {
                $user = $this->logic->getUserByLogin($userName, $password);
            } catch (LogicNotFoundException $e) {
                
                LogManager::log(LOG_NOTICE, 'Login fail: '.$e->getMessage());
                return false;
            }
            
            $this->userData = $user[Model::RESULT_FIELD_DATA][0];
            
            try {
                
                $isLogged = $this->isLogged();
            } catch (AccountExpiredException $e) {
                
                $this->setNewUserLogged();   
                SessionManager::set(self::SESSION_FIELD_USER, $this->userData);
                
                throw $e;
            }
            
            if ($isLogged) {
                
                $this->setNewUserLogged();
                SessionManager::set(self::SESSION_FIELD_USER, $this->userData);
                return true;
            }
            
            return false;
        }
        
        public function verifyPassword ($password) {
            
            try {
                $user = $this->logic->getUserByLogin($this->getUserName(), $password);
            } catch (LogicNotFoundException $e) {
                
                LogManager::log(LOG_NOTICE, 'Login fail: '.$e->getMessage());
                return false;
            }
            
            return is_array($user) && isset($user[Model::RESULT_FIELD_DATA]);
        }
        
        public function isAllowed ($priviledge) {
            
            if ($priviledge === self::PRIV_NONE_REQUIRED)
                return true;
            
            if (isset($this->userData[$priviledge]) && ($this->userData[$priviledge] === self::PRIVILEDGE_ALLOWED || $this->userData[$priviledge] === true))
                return true;
                
            return false;
        }
        
        public function update ($data) {

            if (!isset($data[Model::COLUMN_UPR_ID]))
                return false;
            
            $result = $this->logic->setUser($data);
            
            if (!$result)
                return false;
                
            //var_dump($this->logic->getUser($this->userData[Model::COLUMN_UPR_ID]));
            foreach ($result as $key => $row) {
                
                $this->userData[$key] = $result[$key];
            }
            
            SessionManager::set(self::SESSION_FIELD_USER, $this->userData);
            return true;
        }
        
        public function changePassword ($newPassword) {
            
            $expireAt = date('Y-m-d', time() + (24 * 60 * 60 * self::PASSWORD_EXPIRE_DAYS));
            
            $data = array(
                Model::COLUMN_UPR_ID        => $this->getUserId(),
                Model::COLUMN_UPR_HASLO     => $newPassword,
                Model::COLUMN_UPR_WYGASA    => $expireAt,
            );
            
            $success = $this->update($data);
            if ($success) {
                $this->setNewUserLogged();
            }
            
            return $success;
        }
        
        public function getUserName () {
            
            if (!$this->hasSession())
                return null;
                
            return $this->userData[Model::COLUMN_UPR_NAZWA_UZYTKOWNIKA];
        }
        
        public function getFullUserName () {
            
            if (!$this->hasSession())
                return null;
                
            return $this->userData[Model::COLUMN_UPR_IMIE_NAZWISKO];
        }
        
        public function getUserId () {
            
            if (!$this->hasSession())
                return null;
                
            return $this->userData[Model::COLUMN_UPR_ID];
        }
        
        public function getRecordsNumber () {
            
            if (!$this->hasSession())
                return null;
                
            return $this->userData[Model::COLUMN_UPR_LICZBA_REKORDOW];
        }
        
        public function getAllLoggedUsers ()
        {
            if ($this->isAllowed(User::PRIV_ZMIANA_UPRAWNIEN))
            {
                $this->allUsersData = PermanentCache::get(self::SESSIONS_ALL_KEY);
                
                if (!$this->allUsersData)
                    $this->allUsersData = array();
                    
                return $this->allUsersData;
            }
            
            return null;
        }
        
        /**
        * @desc Administrative logout of a user by force
        */
        public function logOffAnotherUser($userName)
        {
            if ($this->isAllowed(User::PRIV_ZMIANA_UPRAWNIEN))
            {
                if (isset($this->allUsersData[$userName]))
                {
                    return $this->destroySessionById($this->allUsersData[$userName][Model::COLUMN_DICT_ID]);
                }
            }
        }
        
        protected function setNewUserLogged ()
        {
            //if (isset($this->allUsersData[$this->getUserName()]))
            //    $this->destroySessionById($this->allUsersData[$this->getUserName()][Model::COLUMN_DICT_ID]);
            
            //TODO set as many sessions for user as he has, but cut off a proper one on logout, so add logout here
            $_SESSION['uzytkownik'] = $this->getUserName();
            $_SESSION[UZYTKOWNIK_ID] = $this->getUserId();
            $_SESSION['ilosc_rekordow'] = $this->getRecordsNumber();
                    
                    
            $this->allUsersData[$this->getUserName()] = array(Model::COLUMN_DICT_ID => session_id(), Model::COLUMN_DICT_NAZWA => $this->getFullUserName());
            PermanentCache::set(self::SESSIONS_ALL_KEY, $this->allUsersData);
            
            $this->setOldschoolSession();
        }
        
        protected function setOldschoolSession() {
            
            $_SESSION['uzytkownik'] = $this->getUserName();
            $_SESSION[UZYTKOWNIK_ID] = $this->getUserId();
            $_SESSION['ilosc_rekordow'] = $this->getRecordsNumber();
            $_SESSION['kwerenda'] = 0;
            $_SESSION['edycja_masowa'] = "";
            
            if ($this->isAllowed(Model::COLUMN_UPR_DODAWANIE_REKORDU)) {$_SESSION['dodawanie_rekordu'] = 1;}
            if ($this->isAllowed(Model::COLUMN_UPR_DODAWANIE_KWERENDY)){$_SESSION['dodawanie_kwerendy'] = 1;}
            if ($this->isAllowed(Model::COLUMN_UPR_DODAWANIE_ZETTLA)){$_SESSION['dodawanie_zettla'] = 1;}
            if ($this->isAllowed(Model::COLUMN_UPR_EDYCJA_REKORDU)){$_SESSION['edycja_rekordu'] = 1;}
            if ($this->isAllowed(Model::COLUMN_UPR_EDYCJA_GRUPOWA)){$_SESSION['edycja_grupowa'] = 1;}
            if ($this->isAllowed(Model::COLUMN_UPR_KASOWANIE_REKORDU)){$_SESSION['kasowanie_rekordu'] = 1;}
            if ($this->isAllowed(Model::COLUMN_UPR_DRUK_UMOWY)){$_SESSION['druk_umowy'] = 1;}
            if ($this->isAllowed(Model::COLUMN_UPR_DRUK_LISTY)){$_SESSION['druk_listy'] = 1;}
            if ($this->isAllowed(Model::COLUMN_UPR_DRUK_ROZLICZENIA)){$_SESSION['druk_rozliczenia'] = 1;}
            if ($this->isAllowed(Model::COLUMN_UPR_DRUK_ANKIETY)){$_SESSION['druk_ankiety'] = 1;}
            if ($this->isAllowed(Model::COLUMN_UPR_DRUK_BILETU)){$_SESSION['druk_biletu'] = 1;}
            if ($this->isAllowed(Model::COLUMN_UPR_EMAIL)){$_SESSION['email1'] = 1;} //WTF ?! email to adres email, a uprawnienie to email1 !!!?
            if ($this->isAllowed(Model::COLUMN_UPR_MASOWY_EMAIL)){$_SESSION['masowy_email'] = 1;}
            if ($this->isAllowed(Model::COLUMN_UPR_MASOWY_SMS)){$_SESSION['masowy_sms'] = 1;}
            if ($this->isAllowed(Model::COLUMN_UPR_ZMIANA_UPRAWNIEN)){$_SESSION['zmiana_uprawnien'] = 1;}
            
            $controls = new valControl();
                    
            $_SESSION['controls'] = serialize($controls);
        }
        
        /**
        * @desc Log off, destroy session, primarily other than own, but in specific use case own
        */
        protected function destroySessionById ($sessionId) 
        {
            $ownSession = session_id();
            //set to user's session
            session_id($sessionId);
            //kill it
            session_destroy();
            //return to own session
            session_id($ownSession);
            
            return true;
        }
        
        public static function getPriviledgesList ()
        {
            return array(
            
                self::PRIV_DODAWANIE_REKORDU => self::PRIV_DODAWANIE_REKORDU,
                self::PRIV_DODAWANIE_KWERENDY => self::PRIV_DODAWANIE_KWERENDY,
                self::PRIV_DODAWANIE_ZETTLA => self::PRIV_DODAWANIE_ZETTLA,
                self::PRIV_EDYCJA_REKORDU => self::PRIV_EDYCJA_REKORDU,
                self::PRIV_EDYCJA_GRUPOWA => self::PRIV_EDYCJA_GRUPOWA,
                self::PRIV_KASOWANIE_REKORDU => self::PRIV_KASOWANIE_REKORDU,
                self::PRIV_DRUK_UMOWY => self::PRIV_DRUK_UMOWY,
                self::PRIV_DRUK_LISTY => self::PRIV_DRUK_LISTY,
                self::PRIV_DRUK_ROZLICZENIA => self::PRIV_DRUK_ROZLICZENIA,
                self::PRIV_DRUK_ANKIETY => self::PRIV_DRUK_ANKIETY,
                self::PRIV_DRUK_BILETU => self::PRIV_DRUK_BILETU,
                self::PRIV_EMAIL => self::PRIV_EMAIL,
                self::PRIV_MASOWY_EMAIL => self::PRIV_MASOWY_EMAIL,
                self::PRIV_MASOWY_SMS => self::PRIV_MASOWY_SMS,
                self::PRIV_ZMIANA_UPRAWNIEN => self::PRIV_ZMIANA_UPRAWNIEN,
            );
        }
    }