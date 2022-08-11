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
  if (!isset($_COOKIE[TOKEN . '_session']) || $_COOKIE[TOKEN . '_session']=="") {
      $sr=new SleekShopRequest();
      $json=$sr->get_new_session();
      $json=json_decode($json);
      if (isset($json->code)) {
          $code=(string)$json->code;
          self::SetSession($code);
      } else {
        throw new Exception("API ERROR // Error getting session");
      }
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
