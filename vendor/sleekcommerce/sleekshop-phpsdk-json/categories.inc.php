<?php

class CategoriesCtl
{

  function __construct()
  {

  }

/*
 * Delivers an array containing all categories with the parent defined by $id_parent
 */
public static function GetCategories($id_parent=0,$lang=DEFAULT_LANGUAGE)
 {
  $sr=new SleekShopRequest();
  $json=$sr->get_categories($id_parent,$lang);
  $json=json_decode($json);
  $result=array();
  foreach($json->categories as $shopcategory)
  {
    $piecearray=array();
    $piecearray["id"]=(int)$shopcategory->id;
    $piecearray["label"]=(string)$shopcategory->label;
    $piecearray["name"]=(string)$shopcategory->name;
    $piecearray["permalink"]=(string)$shopcategory->seo->permalink;
    $piecearray["title"]=(string)$shopcategory->seo->title;
    $piecearray["description"]=(string)$shopcategory->seo->description;
    $piecearray["keywords"]=(string)$shopcategory->seo->keywords;
    $attributes=array();
    foreach($shopcategory->attributes as $attr) {
      $attributes[$attr->name]=$attr->value;
    }
    $piecearray["attributes"]=$attributes;
    isset($shopcategory->attributes->link->value) ? $piecearray["link"]=(string)$shopcategory->attributes->link->value : $piecearray["link"]="";
    isset($shopcategory->attributes->position->value) ? $piecearray["position"]=(string)$shopcategory->attributes->position->value : $piecearray["position"]="";
    $piecearray["children"]=self::GetCategories($piecearray["id"],$lang);
    $result[]=$piecearray;

  }
  return($result);
 }


 /*
 * Get Menu
 */
 public static function GetMenu($language=DEFAULT_LANGUAGE)
 {
 	if(!file_exists(TEMPLATE_PATH . "/cache/" . $language . "-menu.tmp"))
 	 {
        $res=CategoriesCtl::GetCategories(CATEGORIES_ID,$language);
 	    $res=serialize($res);
        file_put_contents(TEMPLATE_PATH . "/cache/" . $language . "-menu.tmp",$res);
 	 }
 	 else {
 	 	$res=file_get_contents(TEMPLATE_PATH . "/cache/" . $language . "-menu.tmp");
 	}
 	return(unserialize($res));
 }


}

?>
