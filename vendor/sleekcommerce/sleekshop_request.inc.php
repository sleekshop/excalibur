<?php
/*
* This file contains functions for communicating with the sleekshop - server
* version: 1.3.0.0
* (c) sleekcommerce - Kaveh Raji
*/
define("LICENCE_USERNAME","demo_NBSqhrcrhMci15Ir9UWI");
define("SERVER","http://demo.sleekshop.net/srv/service/");
define("LICENCE_PASSWORD","s9vmrbwT23B7bmjR4Vmz");

class SleekShopRequest
{
private $server="";
private $licence_username="";
private $licence_password="";
private $post_data=array();

 public function __construct()
 {
  $this->server=SERVER;
  $this->licence_username=LICENCE_USERNAME;
  $this->licence_password=LICENCE_PASSWORD;
  $this->post_data=array("licence_username"=>$this->licence_username,"licence_password"=>$this->licence_password);
 }



  /*
   * This function is for requesting the category names and labels with the parent determined by id_parent
   */
  public function get_categories($id_parent=0,$lang=DEFAULT_LANGUAGE)
  {
   $post_data=$this->post_data;
   $post_data["request"]="get_categories";
   $post_data["id_parent"]=$id_parent;
   $post_data["language"]=$lang;

   return $this->snd_request($this->server,$post_data);
  }


  /*
   * This function derives all products in a given category determined by ist id_category
   * Further we provide the left_limit and right_limit arguments which determine the range of products
   * we are interested in.
   * $lang determines the language
   * Order Column determines the order column
   * The Order determines the order
   */
  public function get_products_in_category($id_category=0,$lang=DEFAULT_LANGUAGE,$order_column="",$order="ASC",$left_limit=0,$right_limit=0,$needed_attributes=array())
  {
   $post_data=$this->post_data;
   $post_data["request"]="get_products_in_category";
   $post_data["id_category"]=$id_category;
   $post_data["language"]=$lang;
   $post_data["order_column"]=$order_column;
   $post_data["order"]=$order;
   $post_data["left_limit"]=$left_limit;
   $post_data["right_limit"]=$right_limit;
   $post_data["needed_attributes"]=json_encode($needed_attributes);
   return $this->snd_request($this->server,$post_data);
  }


  /*
   * This function derives all products and contents in a given category determined by ist id_category
   * Further we provide the left_limit and right_limit arguments which determine the range of products
   * we are interested in.
   * $lang determines the language
   * Order Column determines the order column
   * The Order determines the order
   */
  public function get_shopobjects_in_category($id_category=0,$lang=DEFAULT_LANGUAGE,$order_column="",$order="ASC",$left_limit=0,$right_limit=0,$needed_attributes=array())
  {
   $post_data=$this->post_data;
   $post_data["request"]="get_shopobjects_in_category";
   $post_data["id_category"]=$id_category;
   $post_data["language"]=$lang;
   $post_data["order_column"]=$order_column;
   $post_data["order"]=$order;
   $post_data["left_limit"]=$left_limit;
   $post_data["right_limit"]=$right_limit;
   $post_data["needed_attributes"]=json_encode($needed_attributes);
   return $this->snd_request($this->server,$post_data);
  }


  /*
   * This function derives all contents in a given category determined by ist id_category
   * Further we provide the left_limit and right_limit arguments which determine the range of contents
   * we are interested in.
   * $lang determines the language
   * Order Column determines the order column
   * The Order determines the order
   */
  public function get_contents_in_category($id_category=0,$lang=DEFAULT_LANGUAGE,$order_column="",$order="ASC",$left_limit=0,$right_limit=0,$needed_attributes=array())
  {
   $post_data=$this->post_data;
   $post_data["request"]="get_contents_in_category";
   $post_data["id_category"]=$id_category;
   $post_data["language"]=$lang;
   $post_data["order_column"]=$order_column;
   $post_data["order"]=$order;
   $post_data["left_limit"]=$left_limit;
   $post_data["right_limit"]=$right_limit;
   $post_data["needed_attributes"]=json_encode($needed_attributes);
   return $this->snd_request($this->server,$post_data);
  }


  /*
   * This function dumps all products and child - categories inherited in an category determined by its id
   * Further it is possible to influence the product listing like order, leftlimit and so on
   */
  public function dump_category($id_category=0,$lang=DEFAULT_LANGUAGE,$order_column="",$order="ASC",$left_limit=0,$right_limit=0,$needed_attributes=array())
  {
   $post_data=$this->post_data;
   $post_data["request"]="dump_category";
   $post_data["id_category"]=$id_category;
   $post_data["language"]=$lang;
   $post_data["order_column"]=$order_column;
   $post_data["order"]=$order;
   $post_data["left_limit"]=$left_limit;
   $post_data["right_limit"]=$right_limit;
   $post_data["needed_attributes"]=json_encode($needed_attributes);

   return $this->snd_request($this->server,$post_data);
  }



 /*
  * This function delivers an xml - containing all neccessary - infos of a specific product determined by id_product
  * We also need to deliver the lang
  */
  public function get_product_details($id_product=0,$lang=DEFAULT_LANGUAGE,$needed_attributes=array())
  {
   $post_data=$this->post_data;
   $post_data["request"]="get_product_details";
   $post_data["language"]=$lang;
   $post_data["id_product"]=$id_product;
   $post_data["needed_attributes"]=json_encode($needed_attributes);
   return $this->snd_request($this->server,$post_data);
  }


 /*
  * This function delivers an xml - containing all neccessary - infos of a specific content determined by id_content
  * We also need to deliver the lang
  */
  public function get_content_details($id_content=0,$lang=DEFAULT_LANGUAGE)
  {
   $post_data=$this->post_data;
   $post_data["request"]="get_content_details";
   $post_data["language"]=$lang;
   $post_data["id_content"]=$id_content;
   return $this->snd_request($this->server,$post_data);
  }



  /*
   * This function returns a valid session _ code which can be user for cart - action etc...
   */
  public function get_new_session()
  {
   $post_data=$this->post_data;
   $post_data["request"]="get_new_session";
   return $this->snd_request($this->server,$post_data);
  }


  /*
   * This function adds a product into the cart determined by the session arg.
   * Further we need to deliver the id_product
   * the quantity
   * the price_field
   * the description_field
   * and the language
   * There also can be added several types of elements - default is product.
   * $id_parent_element determines the parent - element of the inserted element
   * attributes is an array containing assoc - array in the following manner : array("lang"=>"LANG","name"=>"NAME","value"=>"VALUE");
   */
  public function add_to_cart($session="",$id_product=0,$quantity=0,$price_field="",$name_field="",$description_field="",$language=DEFAULT_LANGUAGE,$element_type="PRODUCT",$id_parent_element=0,$attributes=array())
  {
   $post_data=$this->post_data;
   $post_data["request"]="add_to_cart";
   $post_data["session"]=$session;
   $post_data["id_shopobject"]=$id_product;
   $post_data["id_parent_element"]=$id_parent_element;
   $post_data["element_type"]=$element_type;
   $post_data["quantity"]=$quantity;
   $post_data["price_field"]=$price_field;
   $post_data["name_field"]=$name_field;
   $post_data["description_field"]=$description_field;
   $post_data["language"]=$language;
   $post_data["attributes"]=json_encode($attributes);
   return $this->snd_request($this->server,$post_data);
  }


  /*
   * This function deletes a piece of a product determined by its element_id from the
   * remote session - cart determined by session
   */
  public function sub_from_cart($session="",$id_element=0)
  {
   $post_data=$this->post_data;
   $post_data["request"]="sub_from_cart";
   $post_data["session"]=$session;
   $post_data["id_element"]=$id_element;
   return $this->snd_request($this->server,$post_data);
  }


  /*
   * This function delivers the cart and its contents
   */
  public function get_cart($session="")
  {
   $post_data=$this->post_data;
   $post_data["request"]="get_cart";
   $post_data["session"]=$session;
   return $this->snd_request($this->server,$post_data);
  }


 /*
  * This function sets variable values in the actual session - order
  */
  public function set_order_details($session="",$args=array())
  {
   $post_data=$this->post_data;
   $post_data["request"]="set_order_details";
   $post_data["session"]=$session;
   foreach($args as $key=>$value)
   {
    if($key=="attributes") $value=json_encode($value);
    $post_data[$key]=$value;
   }
   return $this->snd_request($this->server,$post_data);
  }

  /*
   * This function returns the actual order-details
   */
  public function get_order_details($session="")
  {
   $post_data=$this->post_data;
   $post_data["request"]="get_order_details";
   $post_data["session"]=$session;
   return $this->snd_request($this->server,$post_data);
  }


 /*
  * This function checks out the actual cart of the session and creates an order
  */
  public function checkout($session="")
  {
   $post_data=$this->post_data;
   $post_data["request"]="checkout";
   $post_data["session"]=$session;
   return $this->snd_request($this->server,$post_data);
  }

  /*
   * This function returns the available payment - methods
   */
  public function get_payment_methods()
  {
   $post_data=$this->post_data;
   $post_data["request"]="get_payment_methods";
   return($this->snd_request($this->server,$post_data));
  }


 /*
 * This function inits the payment
 */
  public function do_payment($id_order=0,$args=array())
  {
   $post_data=$this->post_data;
   $post_data["request"]="do_payment";
   $post_data["id_order"]=$id_order;
   $post_data["args"]=json_encode($args);
   return($this->snd_request($this->server,$post_data));
  }


  /*
   * This function allows us to search in the product - data
   */
  public function search_products($constraint=array(),$left_limit=0,$right_limit=0,$order_columns=array(),$order_type="ASC",$lang=DEFAULT_LANGUAGE,$needed_attributes=array())
   {
    $post_data=$this->post_data;
    $post_data["request"]="search_products";
    $i=0;
    $post_data["constraint"]=json_encode($constraint);
    $post_data["left_limit"]=$left_limit;
    $post_data["right_limit"]=$right_limit;
    $post_data["order_columns"]=json_encode($order_columns);
    $post_data["needed_attributes"]=json_encode($needed_attributes);
    $post_data["order_type"]=$order_type;
    $post_data["language"]=$lang;
    return($this->snd_request($this->server,$post_data));
   }

   /*
    * This function allows us to search in the content - data
    */
   public function search_contents($constraint=array(),$left_limit=0,$right_limit=0,$order_columns=array(),$order_type="ASC",$lang=DEFAULT_LANGUAGE,$needed_attributes=array())
    {
     $post_data=$this->post_data;
     $post_data["request"]="search_contents";
     $i=0;
     $post_data["constraint"]=json_encode($constraint);
     $post_data["left_limit"]=$left_limit;
     $post_data["right_limit"]=$right_limit;
     $post_data["order_columns"]=json_encode($order_columns);
     $post_data["needed_attributes"]=json_encode($needed_attributes);
     $post_data["order_type"]=$order_type;
     $post_data["language"]=$lang;
     return($this->snd_request($this->server,$post_data));
    }

    /*
     * This function allows us to search distinct in the product - data
     */
    public function search_distinct_products($constraint=array(),$field="",$lang=DEFAULT_LANGUAGE)
     {
      $post_data=$this->post_data;
      $post_data["request"]="search_distinct_products";
      $i=0;
      $post_data["constraint"]=json_encode($constraint);
      $post_data["field"]=$field;
      $post_data["language"]=$lang;
      return($this->snd_request($this->server,$post_data));
     }

  /*
   * This function is for registering a new user
   */
  public function register_user($args=array(),$language=DEFAULT_LANGUAGE)
   {
    $post_data=$this->post_data;
    $post_data["request"]="register_user";
    $post_data["args"]=json_encode($args);
    $post_data["language"]=$language;
    return($this->snd_request($this->server,$post_data));
   }

   /*
    * This function resets the user_password
    */
   public function reset_user_password($args=array())
   {
    $post_data=$this->post_data;
    $post_data["request"]="reset_user_password";
    $post_data["args"]=json_encode($args);
    return $this->snd_request($this->server,$post_data);
   }

  /*
   * This function is for verifying the user
   */
  public function verify_user($id_user=0,$session_id="")
   {
    $post_data=$this->post_data;
    $post_data["request"]="verify_user";
    $post_data["id_user"]=$id_user;
    $post_data["session_id"]=$session_id;
    return($this->snd_request($this->server,$post_data));
   }

   /*
    * This function is for login
    */
   public function login_user($session="",$username="",$password="")
   {
    $post_data=$this->post_data;
    $post_data["request"]="login_user";
    $post_data["session"]=$session;
    $post_data["username"]=$username;
    $post_data["password"]=$password;
    return($this->snd_request($this->server,$post_data));
   }

   /*
    * This function is for logout
    */
   public function logout_user($session="")
   {
    $post_data=$this->post_data;
    $post_data["request"]="logout_user";
    $post_data["session"]=$session;
    return($this->snd_request($this->server,$post_data));
   }

     /*
    * This function is for setting a new user - password
    */
   public function set_user_password($session="",$old_password,$new_password1,$new_password2)
   {
    $post_data=$this->post_data;
    $post_data["request"]="set_user_password";
    $post_data["session"]=$session;
    $post_data["old_passwd"]=$old_password;
    $post_data["new_passwd1"]=$new_password1;
    $post_data["new_passwd2"]=$new_password2;
    return($this->snd_request($this->server,$post_data));
   }


  /*
   * This function delivers the user orders
   */
   public function get_user_orders($session="")
   {
    $post_data=$this->post_data;
    $post_data["request"]="get_user_orders";
    $post_data["session"]=$session;
    return($this->snd_request($this->server,$post_data));
   }


 /*
  * This method delivers all user_data available
  */
  public function get_user_data($session="")
  {
   $post_data=$this->post_data;
    $post_data["request"]="get_user_data";
    $post_data["session"]=$session;
    return($this->snd_request($this->server,$post_data));
  }

  /*
   * For setting user data
   */
  public function set_user_data($session="",$args=array())
   {
    $post_data=$this->post_data;
    $post_data["request"]="set_user_data";
    $post_data["session"]=$session;
    $post_data["attributes"]=json_encode($args);
    return($this->snd_request($this->server,$post_data));
   }


 /*
  * This function delivers an xml - containing all neccessary - infos of a specific product determined by permalink
  * We also need to deliver the lang
  */
  public function seo_get_product_details($permalink="",$needed_attributes=array())
  {
   $post_data=$this->post_data;
   $post_data["request"]="seo_get_product_details";
   $post_data["permalink"]=$permalink;
   $post_data["needed_attributes"]=json_encode($needed_attributes);
   return $this->snd_request($this->server,$post_data);
  }

  /*
  * This function delivers an xml - containing all neccessary - infos of a specific product determined by permalink
  * We also need to deliver the lang
  */
  public function seo_get_content_details($permalink="")
  {
   $post_data=$this->post_data;
   $post_data["request"]="seo_get_content_details";
   $post_data["permalink"]=$permalink;
   return $this->snd_request($this->server,$post_data);
  }

  /*
  * This function is for querying the aggregate - request
  */
  public function aggregate($pipe="")
   {
     $post_data=$this->post_data;
     $post_data["request"]="aggregate";
     $post_data["pipe"]=json_encode($pipe);
     return $this->snd_request($this->server,$post_data);
   }


   /*
     * This function is for getting the invoice of an order
     */
     public function get_invoice($id_order=0)
      {
        $post_data=$this->post_data;
        $post_data["request"]="get_invoice";
        $post_data["id_order"]=$id_order;
        return $this->snd_request($this->server,$post_data);
      }

 /*
   * This function derives all products in a given category determined by ist permalink
   * Further we provide the left_limit and right_limit arguments which determine the range of products
   * we are interested in.
   * $lang determines the language
   * Order Column determines the order column
   * The Order determines the order
   */
  public function seo_get_products_in_category($permalink="",$order_column="",$order="ASC",$left_limit=0,$right_limit=0,$needed_attributes=array())
  {
   $post_data=$this->post_data;
   $post_data["request"]="seo_get_products_in_category";
   $post_data["permalink"]=$permalink;
   $post_data["order_column"]=$order_column;
   $post_data["order"]=$order;
   $post_data["left_limit"]=$left_limit;
   $post_data["right_limit"]=$right_limit;
   $post_data["needed_attributes"]=json_encode($needed_attributes);
   return $this->snd_request($this->server,$post_data);
  }


 /*
   * This function derives all contents in a given category determined by ist permalink
   * Further we provide the left_limit and right_limit arguments which determine the range of contents
   * we are interested in.
   * $lang determines the language
   * Order Column determines the order column
   * The Order determines the order
   */
  public function seo_get_contents_in_category($permalink="",$order_column="",$order="ASC",$left_limit=0,$right_limit=0,$needed_attributes=array())
  {
   $post_data=$this->post_data;
   $post_data["request"]="seo_get_contents_in_category";
   $post_data["permalink"]=$permalink;
   $post_data["order_column"]=$order_column;
   $post_data["order"]=$order;
   $post_data["left_limit"]=$left_limit;
   $post_data["right_limit"]=$right_limit;
   $post_data["needed_attributes"]=json_encode($needed_attributes);
   return $this->snd_request($this->server,$post_data);
  }



 /*
   * This function derives all shopobjects in a given category determined by ist permalink
   * Further we provide the left_limit and right_limit arguments which determine the range of objects
   * we are interested in.
   * $lang determines the language
   * Order Column determines the order column
   * The Order determines the order
   */
  public function seo_get_shopobjects_in_category($permalink="",$order_column="",$order="ASC",$left_limit=0,$right_limit=0,$needed_attributes=array())
  {
   $post_data=$this->post_data;
   $post_data["request"]="seo_get_shopobjects_in_category";
   $post_data["permalink"]=$permalink;
   $post_data["order_column"]=$order_column;
   $post_data["order"]=$order;
   $post_data["left_limit"]=$left_limit;
   $post_data["right_limit"]=$right_limit;
   $post_data["needed_attributes"]=json_encode($needed_attributes);
   return $this->snd_request($this->server,$post_data);
  }


  /*
   * This function adds deliverycosts rows to the cart permanently
   */
  public function add_delivery_costs($session="",$delivery_costs=array())
  {
   $post_data=$this->post_data;
   $post_data["request"]="add_delivery_costs";
   $post_data["session"]=$session;
   $post_data["delivery_costs"]=json_encode($delivery_costs);
   return $this->snd_request($this->server,$post_data);
  }


 /*
 * This function sends a post - request
 */
 /*
 * This function sends a post - request
 */
 private function snd_request( $url, $postdata, $useragent = 'PHPPost/1.0' )
 {


     $url_info = parse_url( $url );
     $senddata = '';

     /* post data must be an array */
     if( !is_array( $postdata ) ){
         //return false;
     }

     /* open in secure socket layer or not */
     if( $url_info['scheme'] == 'https' ){

         $fp = fsockopen( 'ssl://' . $url_info['host'], 443, $errno, $errstr, 30);
     }
     else{

         $fp = fsockopen( $url_info['host'], 80, $errno, $errstr, 30);

     }

     /* make sure opened ok */
     if( !$fp )
     {
          echo "Es ist ein Fehler aufgetreten: $errno $errstr";
          return false;
     }

     /* loop postdata and convert it */

    $senddata=$postdata;
   $senddata=http_build_query($senddata);

     /* HTTP POST headers */
     $out = "POST " . (isset($url_info['path'])?$url_info['path']:'/') .
         (isset($url_info['query'])?'?' . $url_info['query']:'') . " HTTP/1.0" . "\r\n";
     $out .= "Host: " . $url_info['host'] . "\r\n";
     $out .= "Content-type: application/x-www-form-urlencoded\r\n";
     $out .= "Content-Length: " . strlen( $senddata ) . "\r\n";

     $out .= "Connection: close" . "\r\n\r\n";
     $out .= $senddata;
     $contents="";
     fwrite($fp, $out);
     /* read any response */
     for( ;!feof( $fp ); )
         $contents .= fgets($fp, 1024);

     /* seperate content and headers */
     list($headers, $content) = explode( "\r\n\r\n", $contents, 2 );
     fclose($fp);
     unset($fp);
     return $content;
 }


 }


 ?>
