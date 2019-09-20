<?php

class SessionCtl
{

  function __construct()
  {

  }

/*
 * Delivers a valid session and returns it
 */
public static function GetSession()
 {
  if($_COOKIE[TOKEN . '_session']=="")
  {
  $sr=new SleekShopRequest();
  $json=$sr->get_new_session();
  $json=json_decode($json);
  $code=(string)$json->code;
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
 public static function SetSession($session="")
 {
 	setcookie(TOKEN . '_session',$session);
 }


}

?>
