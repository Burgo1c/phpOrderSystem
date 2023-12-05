<?php

use PHPMailer\PHPMailer\PHPMailer;
//use PHPMailer\PHPMailer\Exception;

//require '/var/www/php/lib/PHPMailer/src/Exception.php';
require '/var/www/php/lib/PHPMailer/src/PHPMailer.php';
require '/var/www/php/lib/PHPMailer/src/SMTP.php';

/**
 * メールを送信する
 * @param String $host SMTP サーバー
 * @param String $from 差出人
 * @param String $uname SMTP ユーザー
 * @param String $pwd SMTP パスワード
 * @param $port TCP ポート
 * @param Array $to 宛先の配列
 * @param String $title 件名
 * @param String $body メールの内容
 * @param String $file エラーファイル
 */
function SendMail($host, $from, $uname, $pwd, $port, $to, $title, $body, $file = null)
{
    try {
        $mail = new PHPMailer(true);

        // Server settings
        $mail->SMTPDebug = 2;  // Enable verbose debug output
        $mail->isSMTP();  // Set mailer to use SMTP
        $mail->Host = $host;  // Specify main and backup SMTP servers
        $mail->SMTPAuth = true;  // Enable SMTP authentication
        $mail->Username = $uname;  // SMTP username
        $mail->Password = $pwd;  // SMTP password
        $mail->Port = intval($port);  // TCP port to connect to

        $mail->CharSet = 'UTF-8';  // Set character encoding
        $mail->Encoding = 'base64';

        $mail->setFrom($from);  // Set the "From" address

        foreach($to as $email){
            $mail->addAddress($email["mail"]);  // Set the "To" address
        }
       
        $mail->isHTML(true);  // Set email format to HTML
        $mail->ContentType = 'text/html; charset=UTF-8';

        $mail->Subject = $title;  // Set the email subject
        $mail->Body = $body;  // Set the email body

        if(!empty($file)){
            $mail->addAttachment($file);  // Add an attachment (if necessary)
        }

        $mail->send();  // Send the email

        return true;
    } catch (Exception $e) {
        // Handle the exception or log the error
        error_log('Error sending email: ' . $e->getMessage());
        return false;
    }
}

