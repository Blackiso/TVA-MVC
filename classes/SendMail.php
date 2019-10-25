<?php
	use Views\View as View;
	use PHPMailer\PHPMailer\PHPMailer;
	use PHPMailer\PHPMailer\SMTP;
	use PHPMailer\PHPMailer\Exception;

	require 'vendor/autoload.php';

	class SendMail {

		public $Mailer;
		private $SMTP_server = "smtp.zoho.com";
		private $SMTP_port   = 587;
		private $SMTP_user   = "no-reply@paramanagers.com";
		private $SMTP_pass   = "Perlnova_password_852";
		private $support_email = "support@paramanagers.com";

		function __construct() {
			try {
				$this->Mailer = new PHPMailer(true);
			    $this->Mailer->isSMTP();
			    $this->Mailer->Host       = $this->SMTP_server;
			    $this->Mailer->SMTPAuth   = true;    
			    $this->Mailer->Username   = $this->SMTP_user;  
			    $this->Mailer->Password   = $this->SMTP_pass;  
			    $this->Mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
			    $this->Mailer->Port       = $this->SMTP_port;
			    $this->Mailer->CharSet = 'UTF-8';

			    $this->Mailer->setFrom($this->SMTP_user, 'Perlnova Support');
			}catch (Exception $e) {
				$this->error();
			}
		}

		function send_html($template, $to, $subject, $text = null) {
			try {
				$this->Mailer->addAddress($to);  
				$this->Mailer->isHTML(true); 
				$this->Mailer->Subject = $subject;
				$this->Mailer->Body = $template;
				$this->Mailer->AltBody = $text;
				$this->Mailer->send();
			}catch (Exception $e) {
				$this->error();
			}
			return true;
		}

		function send_support_mail($body, $from, $full_name, $subject) {
			try {
				$this->Mailer->addAddress($this->support_email);  
				$this->Mailer->addReplyTo($from, $full_name);
				$this->Mailer->Subject = $subject;
				$this->Mailer->Body = $body;
				$this->Mailer->AltBody = $body;
				$this->Mailer->send();
			}catch (Exception $e) {
				echo $e;
				die();
				$this->error();
			}
			return true;
		}

		function error() {
			View::throw_error('sendmail_error');
		}

	}
