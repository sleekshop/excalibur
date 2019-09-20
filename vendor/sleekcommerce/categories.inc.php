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
    $piecearray["permalink"]=(string)$shopcategory->seo->permalink;
    $piecearray["title"]=(string)$shopcategory->seo->title;
    $piecearray["description"]=(string)$shopcategory->seo->description;
    $piecearray["keywords"]=(string)$shopcategory->seo->keywords;
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
 	if(!file_exists("../templates/cache/menu.tmp") OR true)
 	 {
 		 $res=CategoriesCtl::GetCategories(CATEGORIES_ID,$language);
 	   $res=serialize($res);
 		 file_put_contents("../templates/cache/menu.tmp",$res);
 	 }
 	 else {
 	 	$res=file_get_contents("../templates/cache/menu.tmp");
 		if($_COOKIE[TOKEN."_menu"]!=strlen($res))
 		 {
 	    unlink("../templates/cache/menu.tmp");
 			self::GetMenu($language);
 		 }
 	}
 	setcookie(TOKEN."_menu",strlen($res));
 	return(unserialize($res));
 }


}

?>
