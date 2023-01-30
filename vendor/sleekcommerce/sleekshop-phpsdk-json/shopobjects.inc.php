<?php

class ShopobjectsCtl
{

  function __construct()
  {

  }


/*
 * Derives the availability status as a label
 */
private static function get_availability_label($qty=0,$qty_warning=0,$allow_override=0,$active=0)
{
	if($active==0 OR $allow_override==1) return("success");
	if($qty<$qty_warning AND $qty>0) return("warning");
	if($qty==0) return("danger");
	return("success");
}

private static function get_shopobject_from_json($so="")
{
	$piecearray=array();
	$piecearray["id"]=(int)$so->id;
	$piecearray["class"]=(string)$so->class;
	$piecearray["name"]=(string)$so->name;
	$piecearray["permalink"]=(string)$so->seo->permalink;
	$piecearray["title"]=(string)$so->seo->title;
	$piecearray["description"]=(string)$so->seo->description;
	$piecearray["keywords"]=(string)$so->seo->keywords;

    if (isset($so->availability->quantity))
	{
		$piecearray["availability_quantity"]=(string)$so->availability->quantity;
  		$piecearray["availability_quantity_warning"]=(string)$so->availability->quantity_warning;
  		$piecearray["availability_allow_override"]=(string)$so->availability->allow_override;
  		$piecearray["availability_active"]=(string)$so->availability->active;
		$piecearray["availability_label"]=self::get_availability_label($piecearray["availability_quantity"],$piecearray["availability_quantity_warning"],$piecearray["availability_allow_override"],$piecearray["availability_active"]);
	}
	$piecearray["creation_date"]=(string)$so->creation_date;
	$attributes=array();
	foreach((array)$so->attributes as $attribute)
	{
		$attr=array();
		$attr["type"]=(string)$attribute->type;
		$attr["id"]=(int)$attribute->id;
		$attr["name"]=(string)$attribute->name;
		$attr["label"]=(string)$attribute->label;
    if($attr["type"]!="PRODUCTS")
     {
      $attr["value"]=(string)$attribute->value;
      $attr["value"]=htmlspecialchars_decode($attr["value"]);
      if($attr["type"]=="TXT") $attr["value"]=str_replace("\n","<br>",$attr["value"]);
     }

    //$attr["value"]=html_entity_decode($attr["value"]);
		if((string)$attribute->type=="IMG")
		{
			$width=intval((string)$attribute->width);
			$height=intval((string)$attribute->height);
      $factor=1;
			if($height!=0){$factor=PRODUCT_IMAGE_THUMB_HEIGHT/$height;}
			$width=intval($width*$factor);
			$height=intval($height*$factor);
			$attr["width"]=$width;
			$attr["height"]=$height;
		}
		if((string)$attribute->type=="PRODUCTS")
		{
			$prods=$attribute->value;
			$prods_array=array();
			foreach((array)$prods as $prod)
			{
				$piece=self::get_shopobject_from_json($prod);
				$prods_array[]=$piece;
			}
			$attr["value"]=$prods_array;
		}
		$attributes[(string)$attribute->name]=$attr;
	}
	$variations=array();
	foreach((array)$so->variations as $var)
	{
		$variation=self::get_shopobject_from_json($var);
		$variations[]=$variation;
	}
	$piecearray["attributes"]=$attributes;
	$piecearray["variations"]=$variations;
	return($piecearray);
}



private static function get_products_from_json($json="")
{
	$result=array();
	foreach((array)$json as $so)
	{
     $result[(string)$so->name]=self::get_shopobject_from_json($so);
	}
	return($result);
}

private static function get_contents_from_json($json="")
{
	$result=array();
  $prev="not_set";
  $current="not_set";
  $prevkey=0;
  $layoutindex=0;
  $layoutmax=1;
  $index=0;
  $result["chain"]=array();
	foreach((array)$json as $so)
	{
		$result[(string)$so->name]=self::get_shopobject_from_json($so);
		$result["byclass"][(string)$so->class][]=$result[(string)$so->name];
		if(isset($so->attributes->layout)){
    $current=(string)$so->attributes->layout->value;
    $result["layouts"][(string)$so->attributes->layout->value]=1;
    $current!=$prev ? $layoutindex=0 : $layoutindex++;
    $layoutmax=$layoutindex+1;
    foreach($result["chain"] as $k=>$v)
    {
      if($result["chain"][$k]["index"]>$index-$layoutmax) $result["chain"][$k]["layoutmax"]=$layoutmax;
    }
    $result["chain"][$result[$so->name]["id"]]=array("prev"=>$prev,"current"=>$current,"next"=>"not_set","index"=>$index,"layoutindex"=>$layoutindex,"layoutmax"=>$layoutmax);
    if($prevkey>0) $result["chain"][$prevkey]["next"]=$result[$so->name]["attributes"]["layout"]["value"];
    $prevkey=$result[$so->name]["id"];
    $prev=$result[$so->name]["attributes"]["layout"]["value"];
    $index++;
    }
	}
	return($result);
}



/*
 * Delivers an array containing all categories with the parent defined by $id_parent
 */
public static function GetShopobjects($id_category=0,$lang=DEFAULT_LANGUAGE,$order_columns=array(),$order="ASC",$left_limit=0,$right_limit=0,$needed_attributes=array())
 {
  $sr=new SleekShopRequest();
  $json=$sr->get_shopobjects_in_category($id_category,$lang,$order_columns,$order,$left_limit,$right_limit,$needed_attributes);
  $json=json_decode($json);
  $result=array();
  $result["id_category"]=(int)$json->category->id;
  $result["name"]=(string)$json->category->name;
  $result["permalink"]=(string)$json->category->seo->permalink;
  $result["title"]=(string)$json->category->seo->title;
  $result["description"]=(string)$json->category->seo->description;
  $result["keywords"]=(string)$json->category->seo->keywords;
  $attributes=array();
  foreach((array)$json->category->attributes as $attr)
  {
	$attributes[$attr->name]=$attr->value;
  }
  $result["attributes"]=$attributes;
  $result["products"]=self::get_products_from_json($json->products);
  $result["products_count"]=(int)$json->products_count;
	$result["contents"]=self::get_contents_from_json($json->contents);
  $result["contents_count"]=(int)$json->contents_count;
  return($result);
}


/*
 * Delivers an array containing all categories with the parent defined by $permalink
*/
public static function SeoGetShopobjects($permalink,$order_columns=array(),$order="ASC",$left_limit=0,$right_limit=0,$needed_attributes=array())
{
	$sr=new SleekShopRequest();
	$json=$sr->seo_get_shopobjects_in_category($permalink,$order_columns,$order,$left_limit,$right_limit,$needed_attributes);
	$json=json_decode($json);
	$result=array();
	$result["id_category"]=(int)$json->category->id;
	$result["name"]=(string)$json->category->name;
	$result["permalink"]=(string)$json->category->seo->permalink;
	$result["title"]=(string)$json->category->seo->title;
	$result["description"]=(string)$json->category->seo->description;
	$result["keywords"]=(string)$json->category->seo->keywords;
	$attributes=array();
	foreach((array)$json->category->attributes as $attr)
	{
		$attributes[(string)$attr->name]=(string)$attr->value;
	}
	$result["attributes"]=$attributes;
	$result["products"]=self::get_products_from_json($json->products);
  $result["products_count"]=(int)$json->products_count;
	$result["contents"]=self::get_contents_from_json($json->contents);
  $result["contents_count"]=(int)$json->contents_count;
	return($result);
}





/*
 * Delivers the shopobject - details of a given shopobject determined by its id
 */
public static function GetProductDetails($id_product=0,$lang=DEFAULT_LANGUAGE)
{
	$sr=new SleekShopRequest();
	$json=$sr->get_product_details($id_product,$lang);
	$json=json_decode($json);
	$result=self::get_shopobject_from_json($json);
	return($result);
}


/*
 * Delivers the shopobject - details of a given shopobject determined by its id
*/
public static function GetContentDetails($id_content=0,$lang=DEFAULT_LANGUAGE)
{
	$sr=new SleekShopRequest();
	$json=$sr->get_content_details($id_content,$lang);
	$json=json_decode($json);
	$result=self::get_shopobject_from_json($json);
	return($result);
}



/*
 * Delivers Shopobject - Details given a permalink
 */
public static function SeoGetProductDetails($permalink="")
{
	$sr=new SleekShopRequest();
	$json=$sr->seo_get_product_details($permalink);
	$json=json_decode($json);
	$result=self::get_shopobject_from_json($json);
	return($result);
}


/*
 * Delivers Shopobject - Details given a permalink
*/
public static function SeoGetContentDetails($permalink="")
{
	$sr=new SleekShopRequest();
	$json=$sr->seo_get_content_details($permalink);
	$json=json_decode($json);
	if (isset($json->object) && $json->object == 'error') {
		$result = null;
	} else {
        $result=self::get_shopobject_from_json($json);
    }
	return($result);
}


/*
 * Search
*/
public static function SearchProducts($constraint=array(),$left_limit=0,$right_limit=0,$order_columns=array(),$order_type="ASC",$lang=DEFAULT_LANGUAGE,$needed_attributes=array())
{
	$sr=new SleekShopRequest();
	$json=$sr->search_products($constraint,$left_limit,$right_limit,$order_columns,$order_type,$lang,$needed_attributes);
	$json=json_decode($json);
    $result["products"]=self::get_products_from_json($json->result);
    $result["count"]=(int)$json->count;
    return($result);
}


}

?>
