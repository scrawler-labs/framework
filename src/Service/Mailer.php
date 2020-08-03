<?php
/**
 * Scarawler Mailer Service
 *
 * @package: Scrawler
 * @author: Pranjal Pandey
 */

namespace Scrawler\Service;

use Scrawler\Scrawler;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

Class Mailer extends PHPMailer{

    function __construct(){

        $config = Scrawler::engine()->config()->all();
        if(Scrawler::engine()->config()->get('general.env')=='prod')
        $exception = false;
        else
        $exception = true;

        parent::__construct($exception);
        $this->SMTPDebug = SMTP::DEBUG_SERVER;                                              // Enable verbose debug output
        $this->isSMTP();                                                                    // Send using SMTP
        $this->Host = $config['mailer']['host'];                         // Set the SMTP server to send through
        $this->SMTPAuth = true;                                                             // Enable SMTP authentication
        $this->Username = $config['mailer']['username'];                 // SMTP username
        $this->Password = $config['mailer']['password'];                 // SMTP password
        if ($config['mailer']['secure']) {
        $this->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;                                 // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
        }
        $this->Port = $config['mailer']['port'];                         // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above
    }

    function __set($key,$value){
        if($key == 'from'){
            $this->setFrom($value);
        }
        if($key == 'to'){
            $this->addAddress($value);
        }
        if($key == 'reply'){
            $this->addReplyTo($value);
        }
        if($key == 'cc'){
            $this->addCC($value);
        }
        if($key == 'bcc'){
            $this->addBCC($value);
        }
        if($key == 'attachement'){
            $this->addAttachment($value);
        }
    }
    

}