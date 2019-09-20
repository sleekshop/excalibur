<?php

class PaymentCtl
{

  function __construct()
  {

  }



public static function GetPaymentMethods()
{
	$sr=new SleekShopRequest();
	$json=$sr->get_payment_methods();
	$json=json_decode($json);
	$result=array();
	foreach($json as $method)
	{
    if($method!="payment_methods")
    {
		$piecearray=array();
		$piecearray["id"]=(int)$method->id;
		$piecearray["name"]=(string)$method->name;
		foreach((array)$method->attributes as $key=>$attr)
		{
			$piecearray["attributes"][$key]=(string)$attr;
		}
		$result[(string)$method->name]=$piecearray;
   }
	}
	return($result);
}


public static function DoPayment($id_order=0,$args=array())
{

}


}

?>
