<?php
    require_once ('class.phpmailer.php');
    
    class MailSend
    {
        const SEND_PORT = 587;
        protected $mail;
        
        protected $uzytkownik_uw = 'eena.21mail';
        protected $haslo = 'ikBN59X2';
        protected $komp_wys_ip = '21mail.pl';
        protected $mailer = 'smtp';
        protected $charset = 'iso-8859-2';
        protected $smtpauth = true;
        protected $mail_firmowy = 'info@eena.pl';      
        protected $recipients = array();
        protected $debug = false;
        
        public function MailSend()
        {
            $this->mail = new PHPMailer();
            $this->mail->SMTPDebug = $this->debug;
            $this->mail->IsSMTP(); // telling the class to use SMTP
            $this->mail->Username = $this->uzytkownik_uw;
            $this->mail->Password = $this->haslo;
            $this->mail->Host = $this->komp_wys_ip; // SMTP server
            $this->mail->Port = self::SEND_PORT;
            //$mail->From = $_POST['mail'];
            $this->mail->Mailer = $this->mailer;
            //$mail->ContentType = "text/html";
            $this->mail->CharSet = $this->charset;
            $this->mail->SMTPAuth = $this->smtpauth;
            $this->mail->FromName = "E&A";
        }
        
        public function DodajUkrytyOdbiorca ($email)
        {
            $this->mail->AddBCC($email);
        }
        public function DodajOdbiorca($email)
        {
            if (!isset($this->recipients[$email]))
            {
                $this->mail->AddAddress($email);
                $this->recipients[$email] = $email;
            }
        }
        
        public function PodajOdbiorcow() 
        {
            return $this->recipients;
        }
        
        public function DodajZalacznik($nazwa_plik, $nazwa = '')
        {
            if ($nazwa)
                $this->mail->AddAttachment($nazwa_plik, $nazwa);
            else
                $this->mail->AddAttachment($nazwa_plik);
        }
        
        public function WyslijMail($temat, $tresc, $wyslij_od = null, $odpowiedz_do = null)
        {
            $this->mail->Subject = $temat;
            $this->mail->Body = $tresc;
            if ($wyslij_od != null)
            {
                $this->mail->From = $wyslij_od;
            }
            else
            {
                $this->mail->From = $this->mail_firmowy;
            }
            if ($odpowiedz_do != null)
            {
                $this->mail->AddReplyTo($odpowiedz_do);
            }
            else
            {
                $this->mail->AddReplyTo($this->mail_firmowy);
            }
            
            $result = $this->mail->Send();
            if($this->mail->IsError()) {
                error_log(date('Y-m-d H:i:s') ." Error occurred during email sending: ".$this->mail->ErrorInfo."\n", 3, $_SERVER["PHPRC"]."/public_html/error.log");
                error_log(date('Y-m-d H:i:s') ." Mail params: ".var_export($this->mail, true)."\n", 3, $_SERVER["PHPRC"]."/public_html/error.log");
            }
            return $result;
        }        
    }
?>