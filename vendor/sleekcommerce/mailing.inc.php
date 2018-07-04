<?php
/*
* Checks wether a given eMail - Addy is correct or not
* If the mail seems to be correct the function returns 1
* else 0
*/
function is_valid_email($email)
{
   if(eregi("^[a-zA-Z0-9_]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$]", $email))
   {
      return(0);
   }
    return(1);
   list($Username, $Domain) = split("@",$email);

   if(getmxrr($Domain, $MXHost))
   {
      return(1);
   }
   else
   {
      if(@fsockopen($Domain, 25, $errno, $errstr, 30))
      {
         return(1);
      }
      else
      {
         return(0);
      }
   }
}


/**
 * Sends a simple plain - mail
 */
function send_plain_mail($receiver,$subject,$message,$from)
{

$header_ = 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/plain; charset=UTF-8' . "\r\n";
$header .= "From: $from";
  mail($receiver, $subject, $message, $header);    

}


?>