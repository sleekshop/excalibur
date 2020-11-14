<?php

class OrderCtl
{

  function __construct()
  {

  }



private static function get_order_details($json="")
{
	$result=array();
	$result["id_user"]=(int)$json->user->id_user;
	$result["username"]=(string)$json->user->username;
	$result["order_id_payment_method"]=(int)$json->order_payment_method->id;
	$result["order_payment_method"]=(string)$json->order_payment_method->name;
	$result["order_id_delivery_method"]=(int)$json->order_delivery_method->id;
	$result["order_delivery_method"]=(string)$json->order_delivery_method->name;
	$result["order_id_payment_state"]=(int)$json->order_payment_state->id;
	$result["order_payment_state"]=(string)$json->order_payment_state->name;
	$result["order_id_delivery_state"]=(int)$json->order_delivery_state->id;
	$result["order_delivery_state"]=(string)$json->order_delivery_state->name;
	$result["id_order_state"]=(int)$json->order_state->id;
	$result["order_state"]=(string)$json->order_state->name;
	$result["id_order_type"]=(int)$json->order_type->id;
	$result["order_type"]=(string)$json->order_type->name;
	$result["order_number"]=(string)$json->order_number;
	$result["username_creator"]=(string)$json->username_creator;
	$result["creation_date"]=(string)$json->creation_date;
	$result["username_modifier"]=(string)$json->username_modifier;
	$result["modification_date"]=(string)$json->modification_date;
	$result["delivery_companyname"]=(string)$json->delivery_companyname;
	$result["delivery_department"]=(string)$json->delivery_department;
	$result["delivery_salutation"]=(string)$json->delivery_salutation;
	$result["delivery_firstname"]=(string)$json->delivery_firstname;
	$result["delivery_lastname"]=(string)$json->delivery_lastname;
	$result["delivery_street"]=(string)$json->delivery_street;
	$result["delivery_number"]=(string)$json->delivery_number;
	$result["delivery_zip"]=(string)$json->delivery_zip;
	$result["delivery_state"]=(string)$json->delivery_state;
	$result["delivery_city"]=(string)$json->delivery_city;
	$result["delivery_country"]=(string)$json->delivery_country;
	$result["invoice_companyname"]=(string)$json->invoice_companyname;
	$result["invoice_department"]=(string)$json->invoice_department;
	$result["invoice_salutation"]=(string)$json->invoice_salutation;
	$result["invoice_firstname"]=(string)$json->invoice_firstname;
	$result["invoice_lastname"]=(string)$json->invoice_lastname;
	$result["invoice_street"]=(string)$json->invoice_street;
	$result["invoice_number"]=(string)$json->invoice_number;
	$result["invoice_zip"]=(string)$json->invoice_zip;
	$result["invoice_state"]=(string)$json->invoice_state;
	$result["invoice_city"]=(string)$json->invoice_city;
	$result["invoice_country"]=(string)$json->invoice_country;
	$result["note"]=(string)$json->note;
	$result["email"]=(string)$json->email;
	return($result);
}


/*
 * Set order details
 */
public static function SetOrderDetails($session="",$args=array())
 {
 	$sr=new SleekShopRequest();
 	$json=$sr->set_order_details($session,$args);
 	$json=json_decode($json);
 	return(self::get_order_details($json));
 }


 /*
  * Gets the order details
  */
 public static function GetOrderDetails($session="")
 {
 	$sr=new SleekShopRequest();
 	$json=$sr->get_order_details($session);
 	$json=json_decode($json);
 	return(self::get_order_details($json));
 }


/*
 * Adds the delivery_costs to the order
 */
 public static function AddDeliveryCosts($session="",$delivery_costs=array())
 {
 	$sr=new SleekShopRequest();
 	$json=$sr->add_delivery_costs($session,$delivery_costs);
 }


/*
 * Checks out the order
 */
 public static function Checkout($session="")
 {
 	$sr=new SleekShopRequest();
 	$json=$sr->checkout($session);
 	$json=json_decode($json);
 	$result=array();
    $result["status"]=(string)$json->status;
    $result["id_order"]=(int)$json->id_order;
    $result["session"]=(string)$json->session;
    $result["message"]=(string)$json->message;
    $result["param"]=(string)$json->param;
    return($result);
 }

/*
 * Initiates the Payment
 */
 public static function DoPayment($id_order=0,$args=array())
 {
 	$sr=new SleekShopRequest();
 	$json=$sr->do_payment($id_order,$args);
 	$json=json_decode($json);
 	$result=array();
 	$result["method"]=(string)$json->method;
 	$result["status"]=(string)$json->status;
 	$result["redirect"]=html_entity_decode((string)($json->redirect));
  $result["token"]=($json->token);
 	return($result);
 }


 /*
  * Gets the invoice
  */
  public static function GetInvoice($id_order=0)
  {
  	$sr=new SleekShopRequest();
  	$json=$sr->get_invoice($id_order);
  	$json=json_decode($json);
    if($json->object=="error") return("");
    $invoice=(string)$json->invoice;
  	return(base64_decode($invoice));
  }

  /*
 * Gets the order_confirmation
 */
 public static function GetOrderConfirmation($id_order=0,$args=array())
 {
   $sr=new SleekShopRequest();
   $json=$sr->get_order_confirmation($id_order,$args);
   $json=json_decode($json);
   if($json->object=="error") return("");
   $order_confirmation=(string)$json->order_confirmation;
   return(base64_decode($order_confirmation));
 }

}

?>
