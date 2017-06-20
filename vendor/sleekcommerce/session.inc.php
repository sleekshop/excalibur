<?php

class SessionCtl
{

  function __construct()
  {

  }

/*
 * Delivers a valid session and returns it
 */
public function GetSession()
 {
  if($_COOKIE[TOKEN . '_session']=="")
  {
  $sr=new SleekShopRequest();
  $xml=$sr->get_new_session();
  $xml=new SimpleXMLElement($xml);
  $code=(string)$xml->code;
  self::SetSession($code);
  }
  else
  {
  	$code=$_COOKIE[TOKEN . '_session'];
  }
  return($code);
}



/*
 * Sets the session into the cookie
 */
 public function SetSession($session="")
 {
 	setcookie(TOKEN . '_session',$session);
 }


}

?>
