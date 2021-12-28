<?php

class CartCtl
{

  function __construct()
  {

  }




 private static function get_cart_array($json="")
 {
 	$result=array();
 	$result["sum"]=(float)$json->sum;
 	isset($json->last_inserted_element_id) ? $result["last_inserted_id"]=(int)$json->last_inserted_element_id : $result["last_inserted_id"]=0;
 	$contents=array();
 	foreach((array)$json->contents as $element)
 	{
 		$piece=array();
 		$piece["type"]=(string)$element->type;
 		$piece["id"]=(int)$element->id;
 		$piece["id_product"]=(int)$element->id_product;
 		$piece["quantity"]=(float)$element->quantity;
 		$piece["price"]=(float)$element->price;
 		$piece["sum_price"]=(float)$element->sum_price;
 		$piece["name"]=(string)$element->name;
 		$piece["description"]=(string)$element->description;
 		$attributes=array();
 		foreach($element->attributes as $attr)
 		{
 			$attributes[(string)$attr->name]=(string)$attr->value;
 		}
 		$piece["attributes"]=$attributes;
 		$contents[]=$piece;
 	}
  $coupons=array();
  $coupons["sum"]=(float)$json->coupons->sum;
  foreach((array)$json->coupons->positions as $pos)
   {
    $piece=array();
    $piece["id"]=0;
    $piece["id_product"]=0;
    $piece["quantity"]=1;
    $piece["price"]=(float)$pos->used_amount*-1;
    $piece["sum_price"]=(float)$pos->used_amount*-1;
    $piece["name"]=(string)$pos->name;
    $piece["description"]=" ";
    $contents[]=$piece;
   }
 	$delivery_costs=array();
 	$delivery_costs["sum"]=(float)$json->delivery_costs->sum;
 	foreach((array)$json->delivery_costs->positions as $pos)
 	 {
 	  $piece=array();
 	  $piece["name"]=(string)$pos->name;
 	  $piece["price"]=(float)$pos->price;
 	  $piece["tax"]=(float)$pos->tax;
 	  $delivery_costs[(string)$pos->name]=$piece;
 	 }
 	$result["contents"]=$contents;
 	$result["delivery_costs"]=$delivery_costs;
  $result["sum"]=$result["sum"]-$coupons["sum"];
 	return($result);
 }



/*
 * Adds an element to the cart
 */
public static function Add($session="",$id_product=0,$quantity=0,$price_field="",$name_field="",$description_field="",$language=DEFAULT_LANGUAGE,$element_type="PRODUCT_GR",$id_parent_element=0,$attributes=array())
 {
  $sr=new SleekShopRequest();
  $json=$sr->add_to_cart($session,$id_product,$quantity,$price_field,$name_field,$description_field,$language,$element_type,$id_parent_element,$attributes);
  $json=json_decode($json);
  $cart=self::get_cart_array($json);
  return($cart);
 }


 /*
  * Deletes an element from the cart
  */
 public static function Del($session="",$id_element=0)
 {
 	$sr=new SleekShopRequest();
 	$json=$sr->sub_from_cart($session,$id_element);
 	$json=json_decode($json);
 	$cart=self::get_cart_array($json);
 	return($cart);
 }


 /*
  * Returns the current cart
  */
 public static function Get($session="")
 {
 	$sr=new SleekShopRequest();
 	$json=$sr->get_cart($session);
  $json=json_decode($json);
  if($json->object=="error")
  {
    SessionCtl::SetSession("");
    return(array());
  }
 	$cart=self::get_cart_array($json);
 	return($cart);
 }

/*
 * Gets a new cart from the server
 */
public static function Refresh($session="")
{
	$sr=new SleekShopRequest();
	$json=$sr->get_cart($session);
	$json=json_decode($json);
	$cart=self::get_cart_array($json);
	return($cart);
}

}

?>
