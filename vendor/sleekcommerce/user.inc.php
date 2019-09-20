<?php

class UserCtl
{

  function __construct()
  {

  }



 /*
  * Logs in the user
  */
 public static function Login($session="",$username="",$password="")
 {
 	$sr=new SleekShopRequest();
 	$json=$sr->login_user($session,$username,$password);
 	$json=json_decode($json);
 	$result=array();
 	$result["status"]=(string)$json->status;
 	$result["id_user"]=(int)$json->id_user;
 	$result["session_id"]=(string)$json->session_id;
 	$result["username"]=(string)$json->username;
 	$result["email"]=(string)$json->email;
 	return($result);
 }


/*
 * Logs out the user
 */
 public static function LogOut($session="")
 {
 	$sr=new SleekShopRequest();
 	$json=$sr->logout_user($session,$username,$password);
 	$json=json_decode($json);
 	$result["status"]=(string)$json->status;
 	return($result);
 }


 /*
  * Get the user - data
  */
 public static function GetUserData($session="")
 {
 	$sr=new SleekShopRequest();
 	$json=$sr->get_user_data($session);
 	$json=json_decode($json);
 	$result=array();
 	$result["id_user"]=(int)$json->id_user;
 	$result["session_id"]=(string)$json->session_id;
 	$result["username"]=(string)$json->username;
 	$result["email"]=(string)$json->email;
 	$result["salutation"]=(string)$json->attributes->salutation->value;
 	$result["firstname"]=(string)$json->attributes->firstname->value;
 	$result["lastname"]=(string)$json->attributes->lastname->value;
 	$result["companyname"]=(string)$json->attributes->companyname->value;
 	$result["department"]=(string)$json->attributes->department->value;
 	$result["street"]=(string)$json->attributes->street->value;
 	$result["number"]=(string)$json->attributes->number->value;
 	$result["zip"]=(string)$json->attributes->zip->value;
 	$result["city"]=(string)$json->attributes->city->value;
 	$result["state"]=(string)$json->attributes->state->value;
 	$result["country"]=(string)$json->attributes->country->value;
  foreach($json->additional_attributes as $attribute)
	 {
		 $type=(string)$attribute->type;
		 $name=(string)$attribute->name;
	   $result[$name]=(INT)$attribute->value;
	 }
 	return($result);
 }


/*
 * Sets the user - data
 */
 public static function SetUserData($session="",$args=array())
 {
 	$sr=new SleekShopRequest();
 	$json=$sr->set_user_data($session,$args);
 	$json=json_decode($json);
 	$result=array();
 	$result["status"]=(string)$json->status;
 	return($result);
 }


 /*
  * Sets the user - password
 */
 public static function SetUserPassword($session="",$passwd1="",$passwd2="",$passwd3="")
 {
 	$sr=new SleekShopRequest();
 	$json=$sr->set_user_password($session,$passwd1,$passwd2,$passwd3);
 	$json=json_decode($json);
 	$result=array();
 	$result["status"]=(string)$json->status;
 	return($result);
 }

 /*
  * Get the user - orders
 */
 public static function GetUserOrders($session="")
 {
 	$sr=new SleekShopRequest();
 	$json=$sr->get_user_orders($session);
 	$json=json_decode($json);
 	$result=array();
 	foreach($json->orders as $order)
 	{
 		$piecearray=array();
 		$piecearray["id"]=(int)$order->id;
 		$piecearray["order_number"]=(int)$order->order_number;
 		$piecearray["creation_date"]=(string)$order->creation_date;
 		$piecearray["order_email"]=(string)$order->order_email;
 		$piecearray["payment_method"]=(string)$order->payment_method;
 		$piecearray["payment_state_name"]=(string)$order->payment_state->name;
 		$piecearray["payment_state_label"]=(string)$order->payment_state->label;
 		$piecearray["delivery_method"]=(string)$order->delivery_method;
 		$piecearray["delivery_state_name"]=(string)$order->delivery_state->name;
 		$piecearray["delivery_state_label"]=(string)$order->delivery_state->label;
 		$piecearray["order_state"]=(string)$order->order_state;
 		$piecearray["cart_sum"]=(float)$order->cart->sum;
 		$result[]=$piecearray;
 	}
 	return($result);
 }



/*
 * Registering a user
 */
 public static function RegisterUser($args=array(),$lang=DEFAULT_LANGUAGE)
 {
 	$sr=new SleekShopRequest();
 	$json=$sr->register_user($args,$lang);
 	$json=json_decode($json);
 	$result=array();
 	$result["status"]=(string)$json->status;
 	$result["id_user"]=(int)$json->id_user;
 	$result["session_id"]=(string)$json->session_id;
 	return($result);
 }


 /*
  * Verifies a user
 */
 public static function VerifyUser($id_user=0,$session_id="")
 {
 	$sr=new SleekShopRequest();
 	$json=$sr->verify_user($id_user,$session_id);
 	$json=json_decode($json);
 	$result=array();
 	$result["status"]=(string)$json->status;
 	return($result);
 }


}

?>
