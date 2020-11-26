<?php

class Custom_Mailer
    {

    public function send($recipient, $subject, $body)
        {
        $smtpServer = 'smtp.gmail.com';
        $username = 'italo.albanese@gmail.com';
        $password = 'ital0al';

//        'ssl'=>'ssl',
//                    'port'=>465,
        $config = array('auth' => 'login',
            'ssl' => 'tls',
            'username' => $username,
            'password' => $password);

        $transport = new Zend_Mail_Transport_Smtp($smtpServer, $config);

        $mail = new Zend_Mail();
        Zend_Mail::setDefaultTransport($transport);

        $mail->setFrom('italo.albanese@gmail.com', 'italo.albanese');
        $mail->addTo($recipient, 'italba');
        $mail->setSubject($subject);
        $mail->setBodyText($body);

        return $mail->send($transport);
        }

    public function send2($recipient, $subject, $body)
        {
        $mail_from    =	"MIME-Version: 1.0\n";
        $mail_from   .= "Content-type: text/plain; charset=UTF-8\n";
        $mail_from   .= "Content-Transfer-Encoding: 8bit\n";
        $mail_from   .= "From: noreply@rdscantieri.eu\r\nBcc:rdscantieri@gmail.com\r\n";
        $mail_subject='=?UTF-8?B?'.base64_encode($subject).'?=';
        return mail($recipient, $mail_subject, $body, $mail_from);
//        return mail($recipient, $subject, 
//                    htmlentities($body, ENT_QUOTES, 'UTF-8'), $mail_from);
        }

    public function mail_attachment($filename, $path, $mailto, $from_mail, $from_name, $replyto, $subject, $message)
        {
        $file = $path . $filename;
        $file_size = filesize($file);
        $handle = fopen($file, "r");
        $content = fread($handle, $file_size);
        fclose($handle);
        $content = chunk_split(base64_encode($content));
        $uid = md5(uniqid(time()));
        $name = basename($file);
        $header = "From: " . $from_name . " <" . $from_mail . ">\r\n";
        $header .= "Reply-To: " . $replyto . "\r\n";
        $header .= "MIME-Version: 1.0\r\n";
        $header .= "Content-Type: multipart/mixed; boundary=\"" . $uid . "\"\r\n\r\n";
        $header .= "This is a multi-part message in MIME format.\r\n";
        $header .= "--" . $uid . "\r\n";
        $header .= "Content-type:text/plain; charset=iso-8859-1\r\n";
        $header .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $header .= $message . "\r\n\r\n";
        $header .= "--" . $uid . "\r\n";
        $header .= "Content-Type: application/octet-stream; name=\"" . $filename . "\"\r\n"; // use different content types here
        $header .= "Content-Transfer-Encoding: base64\r\n";
        $header .= "Content-Disposition: attachment; filename=\"" . $filename . "\"\r\n\r\n";
        $header .= $content . "\r\n\r\n";
        $header .= "--" . $uid . "--";
        if (mail($mailto, $subject, "", $header))
            {
            echo "mail send ... OK"; // or use booleans here
            } else
            {
            echo "mail send ... ERROR!";
            }
        }

    }