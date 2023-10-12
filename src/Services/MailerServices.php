<?php
namespace Src\Services;
require  '././vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;


Class MailerServices {

    public $config;

    public function __construct(){
        $this->config = json_decode(file_get_contents('config.json'));
    }

    public function sendMail($adresse , $subject , $template){
        $mail = new PHPMailer(true);
        try {
           
                        
            $mail->isSMTP();                                           
            $mail->Host       =  $this->config->mailer->host;                     
            $mail->SMTPAuth   =  true;                                   
            $mail->Username   =  $this->config->mailer->username;                     
            $mail->Password   =  $this->config->mailer->password;                              
            $mail->SMTPSecure =  PHPMailer::ENCRYPTION_SMTPS;            
            $mail->Port       =  465;                                    
            $mail->setFrom('myrecode@recode.fr', 'MyRecode');
            $mail->addAddress($adresse);    
            $mail->isHTML(true);        
            $mail->CharSet = 'UTF-8';                          
            $mail->Subject =  $subject;
            $mail->Body    = $template;
            $mail->send();
            return true ;
        } catch (Exception $e) {
            return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }


    public function header(){
        $imageData = base64_encode(file_get_contents('public/img/LOGO.png'));
        $src = 'data: '.mime_content_type('public/img/LOGO.png').';base64,'.$imageData;

        return '<img width="150"  src="https://myrecode.fr/img/logo_myrecode.png" style="display:block;"  alt="MyRecode" title="MyRecode" ><br><br>';
    }
    
    public function signature(){
            return '';
    }


    public function bodyMail($text)
    {
        return '
            <div class="wrapper">
                <p style="text-align: center;"><!--StartFragment--><span style="font-size:14px"><span style="font-weight:bold">
                    </span></span>
                    <br/>
                    &nbsp;
                </p>
                    <p style="text-align: center;">'. $text.'<br/>
                    <br />
                    <br />
                    <br />
                    <br />
                    <br />
                    <br />
                    <br />
                    <span style="font-size:14px" style="font-weight:bold">A tout de suite sur votre espace client.</span><br />
                    <span style="font-size:14px" style="font-weight:bold">L equipe RECODE !</span>
                </p>
            </div>';
    }

    public function renderBody($header , $body , $signature){
            return $header . $body . $signature ; 
    }

    
}