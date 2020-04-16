<?php
require '../vendor/autoload.php';
require '../vendor/sleekcommerce/init.inc.php';

// Prepare app
$app = new \Slim\Slim(array(
    'templates.path' => '../templates',
));

// Create monolog logger and store logger in container as singleton
// (Singleton resources retrieve the same log resource definition each time)
$app->container->singleton('log', function () {
    $log = new \Monolog\Logger('slim-skeleton');
    $log->pushHandler(new \Monolog\Handler\StreamHandler('../logs/app.log', \Monolog\Logger::DEBUG));
    return $log;
});

// Prepare view
$app->view(new \Slim\Views\Twig());
$app->view->parserOptions = array(
    'charset' => 'utf-8',
    'cache' => realpath('../templates/cache'),
    'auto_reload' => true,
    'strict_variables' => false,
    'autoescape' => true
);


$app->view->parserExtensions = array(new Twig_Extension_I18n());

//Reloading the menu
$app->get('/reload-menu', function () use ($app,$language,$menu,$username,$cart) {
    unlink("../templates/cache/menu.tmp");
    die("WEBHOOK_EXECUTED");
});

//Reloading the menu
$app->get('/reload-static-files', function () use ($app,$language,$menu,$username,$cart) {
    $lang=$app->request->get("language");
    if($lang=="") $lang=DEFAULT_LANGUAGE;
    $id=$app->request->get("id_shopobject");
    $prefix=array_shift(explode("_",$language));
    $res=ShopobjectsCtl::GetContentDetails($id,$lang);
    $about_us=$res["attributes"]["about_us_footer"]["value"];
    unlink("../templates/part_about_us_footer_".$prefix.".html");
    file_put_contents("../templates/part_about_us_footer_".$prefix.".html",$about_us);
    $logo=$res["attributes"]["logo"]["value"];
    unlink("../templates/part_logo.html");
    file_put_contents("../templates/part_logo.html","<img src='".$logo."' border='0'>");
    $face=$res["attributes"]["facebook_link"]["value"];
    $insta=$res["attributes"]["instagram_link"]["value"];
    unlink("../templates/part_social_links.html");
    file_put_contents("../templates/part_social_links.html","<a href='".$face."' target='_blank'><img src='../img/facebook.png' width='50px'></a>
    <a href='".$insta."' target='_blank'><img src='../img/insta.jpg' width='50px'></a>");
    die("WEBHOOK_EXECUTED");
});

$app->get("/get-invoice/:id/:hash", function ($id,$hash) use ($app,$language,$menu,$username,$cart) {
   //$app->log->info("Slim-Skeleton "/" route");
   if(!(crypt($id,TOKEN)==base64_decode($hash))) die("PERMISSION_DENIED");
   $invoice=OrderCtl::GetInvoice($id);
   echo $invoice;
   die();
});


//For changing the language
$app->get('/change-lang', function () use ($app,$language,$menu,$username,$cart) {


    unlink("../templates/cache/menu.tmp");
    //$app->log->info("Slim-Skeleton '/' route");
    $language=$app->request->get("lang");
    // Render index viewdd
    $app->setCookie(TOKEN."_lang",$language,time()+3600);
    $app->setCookie(TOKEN."_menu","",time()+3600);
    $menu=CategoriesCtl::GetMenu($language);
    $res=ShopobjectsCtl::GetShopObjects(1,$language,"price","ASC",0,0,array("name","img1","price","short_description"));
    $app->render('index.html',array("res"=>$res,"menu"=>$menu,"username"=>$username,"cart"=>$cart,"language"=>$language));
});

// Define routes
$app->get('/', function () use ($app,$language,$menu,$username,$cart) {
    // Sample log message
    $app->log->info("Slim-Skeleton '/' route");
    // Render index viewdd
    $res=ShopobjectsCtl::GetShopObjects(START_ID,$language,"price","ASC",0,0,array("name","img1","price","short_description"));
    $app->render('index.html',array("res"=>$res,"menu"=>$menu,"username"=>$username,"cart"=>$cart,"language"=>$language));
});


$app->get('/content/:obj', function ($obj) use ($app,$request_uri,$language,$menu,$username,$cart) {
    // Sample log message
    $app->log->info("Slim-Skeleton '/' route");
    // Render index viewdd
    $res=ShopObjectsCtl::SeoGetContentDetails($obj);
    $app->render('content.html',array("res"=>$res,"menu"=>$menu,"username"=>$username,"cart"=>$cart,"request_uri"=>$request_uri,"language"=>$language));
});



$app->get('/category/:obj', function ($obj) use ($app,$request_uri,$language,$menu,$username,$cart) {
    // Sample log message
    $app->log->info("Slim-Skeleton '/' route");
    // Render index viewdd
        if(is_numeric($obj))
        {
          $res=ShopobjectsCtl::GetShopobjects($obj,$language,"price","ASC",0,0,array("name","short_description","price","img1"));
        }
        else {
          $res=ShopobjectsCtl::SeoGetShopobjects($obj,"price","ASC",0,0,array("name","short_description","price","img1"));
        }
        $app->render('show_category.html',array("res"=>$res,"menu"=>$menu,"username"=>$username,"cart"=>$cart,"request_uri"=>$request_uri,"language"=>$language));
});




$app->post('/add_to_cart', function () use ($app,$language,$menu,$username,$cart) {
    // Sample log message
    $app->log->info("Slim-Skeleton '/' route");
    // Render index viewdd
      $id_product=$app->request->post("id_product");
      $pic=$app->request->post("pic");
      $quantity=$app->request->post("quantity");
      $res=CartCtl::Add(SessionCtl::GetSession(),$id_product,$quantity,"price","name","short_description",$language,"PRODUCT",0,array(array("lang"=>$language,"name"=>"pic","value"=>$pic)));
      $app->render('cart.html',array("res"=>$res,"menu"=>$menu,"username"=>$username,"cart"=>$res,"request_uri"=>$request_uri,"language"=>$language));
    //$app->render('index.html',array("res"=>$res));
});

$app->post('/login', function() use ($app,$language,$menu,$username,$cart) {

     $username=$app->request->post("username");
     $passwd=$app->request->post("password");
     $res=UserCtl::Login(SessionCtl::GetSession(),$username,$passwd);
     if($res['status']=="SUCCESS")
     {
     $username=$res["username"];
     setcookie("username",$username);
     $res=UserCtl::GetUserData(SessionCtl::GetSession());
     $profile=$app->request->post("profile");
     if($profile!=1)
     {
       $app->render('userdata.html',array("userdata"=>$res,"menu"=>$menu,"username"=>$username,"cart"=>$cart,"request_uri"=>$request_uri,"language"=>$language));
     }
     else {
       $res=UserCtl::GetUserOrders(SessionCtl::GetSession());
       $app->render('profile.html',array("res"=>$res,"menu"=>$menu,"username"=>$username,"cart"=>$cart,"request_uri"=>$request_uri,"language"=>$language));
     }

    }
    else {
      $app->render('login.html',array("error"=>1,"res"=>$res,"menu"=>$menu,"username"=>"","cart"=>$cart,"request_uri"=>$request_uri,"language"=>$language));
    }

});


$app->post('/userdata', function() use ($app,$request_uri,$language,$menu,$username,$cart)
 {


      $error=array();
      $error_count=0;
      $salutation=$app->request->post("salutation");
      $firstname=$app->request->post("firstname");
      $lastname=$app->request->post("lastname");
      $company=$app->request->post("companyname");
      $department=$app->request->post("department");
      $street=$app->request->post("street");
      $number=$app->request->post("number");
      $zip=$app->request->post("zip");
      $city=$app->request->post("city");
      $state=$app->request->post("state");
      $country=$app->request->post("country");
      $notes=$app->request->post("notes");
      $email=$app->request->post("email");
      $userdata["salutation"]=$salutation;
      $userdata["firstname"]=$firstname;
      $userdata["lastname"]=$lastname;
      $userdata["company"]=$company;
      $userdata["department"]=$department;
      $userdata["street"]=$street;
      $userdata["number"]=$number;
      $userdata["zip"]=$zip;
      $userdata["city"]=$city;
      $userdata["state"]=$state;
      $userdata["country"]=$country;
      $userdata["notes"]=$notes;
      $userdata["email"]=$email;

      $args=array("delivery_companyname"=>$userdata["company"],"delivery_department"=>$userdata["department"],"delivery_salutation"=>$userdata["salutation"],
      		"delivery_firstname"=>$userdata["firstname"],"delivery_lastname"=>$userdata["lastname"],"delivery_street"=>$userdata["street"],"delivery_number"=>$userdata["number"],"delivery_zip"=>$userdata["zip"],
      		"delivery_state"=>$userdata["state"],"delivery_city"=>$userdata["city"],"delivery_country"=>$userdata["country"],
      		"invoice_companyname"=>$userdata["company"],"invoice_department"=>$userdata["department"],"invoice_salutation"=>$userdata["salutation"],
      		"invoice_firstname"=>$userdata["firstname"],"invoice_lastname"=>$userdata["lastname"],"invoice_street"=>$userdata["street"],"invoice_number"=>$userdata["number"],"invoice_zip"=>$userdata["zip"],
      		"invoice_state"=>$userdata["state"],"invoice_city"=>$userdata["city"],"invoice_country"=>$userdata["country"],"note"=>$userdata["note"],"email"=>$userdata["email"]);
      $order_data=OrderCtl::SetOrderDetails(SessionCtl::GetSession(),$args);


      if($email=="") $error["email"]="has-error";
      if($firstname=="") $error["firstname"]="has-error";
      if($lastname=="") $error["lastname"]="has-error";
      if($street=="") $error["street"]="has-error";
      if($number=="") $error["number"]="has-error";
      if($zip=="") $error["zip"]="has-error";
      if($city=="") $error["city"]="has-error";
      if($state=="") $error["state"]="has-error";
      if($country=="") $error["country"]="has-error";

      if(count($error)!=0)
      {
       $error_count++;
       $app->render('userdata.html',array("userdata"=>$userdata,"error"=>$error,"error_count"=>$error_count,"language"=>$language));
      }
      else {
        $args=array("companyname"=>$userdata["company"],"department"=>$userdata["department"],"salutation"=>$userdata["salutation"],
      			"firstname"=>$userdata["firstname"],"lastname"=>$userdata["lastname"],"street"=>$userdata["street"],"number"=>$userdata["number"],"zip"=>$userdata["zip"],
      			"state"=>$userdata["state"],"city"=>$userdata["city"],"country"=>$userdata["country"],
      			"email"=>$userdata["email"]);
      	UserCtl::SetUserData(SessionCtl::GetSession(),$args);
      	$payment_methods=PaymentCtl::GetPaymentMethods();
        $app->render("payment_methods.html",array("payment_methods"=>$payment_methods,"request_uri"=>$request_uri,"language"=>$language,"menu"=>$menu,"username"=>$username,"cart"=>$cart,"language"=>$language));
      }

 });



 $app->post('/profile-userdata', function() use ($app,$request_uri,$language,$menu,$username,$cart)
  {

       $error=array();
       $error_count=0;
       $salutation=$app->request->post("salutation");
       $firstname=$app->request->post("firstname");
       $lastname=$app->request->post("lastname");
       $company=$app->request->post("company");
       $department=$app->request->post("department");
       $street=$app->request->post("street");
       $number=$app->request->post("number");
       $zip=$app->request->post("zip");
       $city=$app->request->post("city");
       $state=$app->request->post("state");
       $country=$app->request->post("country");
       $notes=$app->request->post("notes");
       $email=$app->request->post("email");
       $userdata["salutation"]=$salutation;
       $userdata["firstname"]=$firstname;
       $userdata["lastname"]=$lastname;
       $userdata["companyname"]=$company;
       $userdata["department"]=$department;
       $userdata["street"]=$street;
       $userdata["number"]=$number;
       $userdata["zip"]=$zip;
       $userdata["city"]=$city;
       $userdata["state"]=$state;
       $userdata["country"]=$country;
       $userdata["notes"]=$notes;
       $userdata["email"]=$email;

       $args=array("delivery_companyname"=>$userdata["company"],"delivery_department"=>$userdata["department"],"delivery_salutation"=>$userdata["salutation"],
       		"delivery_firstname"=>$userdata["firstname"],"delivery_lastname"=>$userdata["lastname"],"delivery_street"=>$userdata["street"],"delivery_number"=>$userdata["number"],"delivery_zip"=>$userdata["zip"],
       		"delivery_state"=>$userdata["state"],"delivery_city"=>$userdata["city"],"delivery_country"=>$userdata["country"],
       		"invoice_companyname"=>$userdata["company"],"invoice_department"=>$userdata["department"],"invoice_salutation"=>$userdata["salutation"],
       		"invoice_firstname"=>$userdata["firstname"],"invoice_lastname"=>$userdata["lastname"],"invoice_street"=>$userdata["street"],"invoice_number"=>$userdata["number"],"invoice_zip"=>$userdata["zip"],
       		"invoice_state"=>$userdata["state"],"invoice_city"=>$userdata["city"],"invoice_country"=>$userdata["country"],"note"=>$userdata["note"],"email"=>$userdata["email"]);


       if($email=="") $error["email"]="has-error";
       if($firstname=="") $error["firstname"]="has-error";
       if($lastname=="") $error["lastname"]="has-error";
       if($street=="") $error["street"]="has-error";
       if($number=="") $error["number"]="has-error";
       if($zip=="") $error["zip"]="has-error";
       if($city=="") $error["city"]="has-error";
       if($state=="") $error["state"]="has-error";
       if($country=="") $error["country"]="has-error";

       if(count($error)!=0)
       {
        $error_count++;
        $app->render('profile_userdata.html',array("userdata"=>$userdata,"error"=>$error,"error_count"=>$error_count));
       }
       else {
         $args=array("companyname"=>$userdata["companyname"],"department"=>$userdata["department"],"salutation"=>$userdata["salutation"],
       			"firstname"=>$userdata["firstname"],"lastname"=>$userdata["lastname"],"street"=>$userdata["street"],"number"=>$userdata["number"],"zip"=>$userdata["zip"],
       			"state"=>$userdata["state"],"city"=>$userdata["city"],"country"=>$userdata["country"],
       			"email"=>$userdata["email"]);
       	$res=UserCtl::SetUserData(SessionCtl::GetSession(),$args);

       	//$payment_methods=PaymentCtl::GetPaymentMethods();
         $app->render("profile_userdata.html",array("userdata"=>$userdata,"request_uri"=>$request_uri,"language"=>$language,"menu"=>$menu,"username"=>$username,"cart"=>$cart,"language"=>$language));
       }

  });

  $app->post('/profile-password', function() use ($app,$request_uri,$language,$menu,$username,$cart)
   {
     $error_count=0;
     $passwd1=$app->request->post("passwd1");
     $passwd2=$app->request->post("passwd2");
     $passwd3=$app->request->post("passwd3");
     $res=UserCtl::SetUserPassword(SessionCtl::GetSession(),$passwd1,$passwd2,$passwd3);
     if($res["status"]!="SUCCESS")
      {
 	     $error=$res["status"];
 	     $error_count++;
      }
      $app->render("profile_password.html",array("request_uri"=>$request_uri,"language"=>$language,"menu"=>$menu,"username"=>$username,"cart"=>$cart,"error_count"=>$error_count,"error"=>$error,"already_sent"=>1,"language"=>$language));
   });



$app->post('/checkout', function() use ($app,$request_uri,$language,$menu,$username,$cart)
{
  $cart=CartCtl::Get(SessionCtl::GetSession());
  $token=$app->request->post("token");
  $card_number=$app->request->post("card_number");
  $cvc=$app->request->post("cvc");
  $exp_month=$app->request->post("exp_month");
  $exp_year=$app->request->post("exp_year");
  $delivery_costs=array(array("Delivery",$cart["delivery_costs"]["sum"],0.19));
  OrderCtl::AddDeliveryCosts(SessionCtl::GetSession(),$delivery_costs);
  $res=OrderCtl::Checkout(SessionCtl::GetSession());
  if($res["status"]=="success")
  {
   /*
    * Send the order - email
    */
    $order=OrderCtl::GetOrderDetails(SessionCtl::GetSession());
    $subject="Danke, wir haben Ihre Bestellung erhalten";
    $msg="Vielen Dank, wir haben Ihre Bestellung erhalten.\n\n";
       $msg.="Folgende Produkte haben Sie bestellt:\n\n";

    foreach($cart["contents"] as $e)
    {
     $msg.= "Artikel-ID:" . $e["id_product"] . "\n" . $e["name"] . "\n". $e["description"] . "\n". "Anzahl:" . $e["quantity"] . "\nPreis: " .number_format($e["price"],2) .  " EUR\nSumme: " . number_format($e["sum_price"],2) . " EUR\n";
     $msg.="-----------------------------------------------------------------\n";
    }

    $msg.="Summe: " . number_format($cart["sum"],2) . " EUR\n\n";
    $msg.="Bezahlung: " . $order["order_payment_method"]."\n\n";
    $msg.="Lieferung: " . $order["order_delivery_method"]."\n\n";
    $msg.="Ihre Daten:\n";
    $msg.=$order["delivery_salutation"] . " " . $order["delivery_firstname"] . " " . $order["delivery_lastname"] . "\n";
    $msg.=$order["delivery_street"] . " " . $order["delivery_number"] . "\n";
    $msg.=$order["delivery_zip"] . " " . $order["delivery_city"] . " " . $order["delivery_country"] . "\n";
    $msg.="E-Mail: " . $order["email"] . "\n";
    $msg.="Anmerkungen:\n".$order["notes"]."\n\n";
    $msg.="Link zur Rechnung: https://".$_SERVER["HTTP_HOST"]."/get-invoice/".$res["id_order"]."/".base64_encode(crypt($res["id_order"],TOKEN));

    send_plain_mail($order["email"],utf8_decode($subject),utf8_decode($msg),ORDER_SENDER);
    send_plain_mail(ORDER_SENDER,utf8_decode($subject),utf8_decode($msg),ORDER_SENDER);
   /*
    * End of email - sending
    */
   $id_order=$res["id_order"];
   $session=$res["session"];
   SessionCtl::SetSession($session);
   setcookie('cart',"");
   $cart=array();
   $res=OrderCtl::DoPayment($id_order,array());
   if($res["status"]=="Success" AND $res["redirect"]!="")
   {
     $redirect=html_entity_decode($res["redirect"]);
   }
  }
  elseif($res["status"]=="error" AND $res["message"]=="SHOPOBJECT_NOT_AVAILABLE")
  {

   $cart=CartCtl::Refresh(SessionCtl::GetSession());

   foreach($cart["contents"] as $prod)
   {
     if($prod["type"]=="DELIVERY_COSTS")
     {
       $cart=CartCtl::Del(SessionCtl::GetSession(),$prod["id"]);
     }
   }
   $tpl->assign("cart",$cart);
   $tpl->assign("missing_id",$res["param"]);
   $pages=array("product_not_available");
  }
  $app->render("checkout.html",array("token"=>$token,"redirect"=>$redirect,"res"=>$res,"payment_methods"=>$payment_methods,"request_uri"=>$request_uri,"language"=>$language,"menu"=>$menu,"username"=>$username,"cart"=>$cart));

});


$app->post('/register', function() use ($app,$request_uri,$language,$menu,$username,$cart)
{
  $user=$app->request->post("user");
  $email=$app->request->post("email");
  $passwd1=$app->request->post("password");
  $passwd2=$app->request->post("password2");
  $already_sent=$app->request->post("already_sent");
  $error=0;
  if($already_sent==1)
  {
  	$res=UserCtl::RegisterUser(array("username"=>$user,"email"=>$email,"passwd1"=>$passwd1,"passwd2"=>$passwd2),$language);
  	if($res["status"]=="SUCCESS")
  	{
  		UserCtl::VerifyUser($res["id_user"],$res["session_id"]);
  		$app->render("login.html",array("error_msg"=>$error_msg,"error"=>0,"user"=>$user,"email"=>$email,"cart"=>$cart,"language"=>$language,"menu"=>$menu));
  	}
  	else
  	{
  		$error++;
  		$error_msg=$res["status"];
      $app->render("register.html",array("error_msg"=>$error_msg,"error"=>1,"user"=>$user,"email"=>$email,"cart"=>$cart,"language"=>$language,"menu"=>$menu));
  	}
  }
});


$app->get('/search', function () use ($app,$request_uri,$language,$menu,$username,$cart) {
    $app->log->info("Slim-Skeleton '/' route");
    // Render index viewdd
    $searchstring=$app->request->get("searchstring");
    $app->request->get("page")!="" ? $page=$app->request->get("page") : $page=1;
    $constraint=array(array("OR"=>array("name"=>array("LIKE",$searchstring)),array("short_description"=>array("LIKE",$searchstring)),array("description"=>array("LIKE",$searchstring)),array("vendor"=>array("LIKE",$searchstring)),array("tags"=>array("LIKE",$searchstring))));
    $left_limit=0;
    $right_limit=20;
    $res=ShopobjectsCtl::SearchProducts($constraint,$left_limit,$right_limit,array(),"ASC",$language,array("name","img1","price","short_description"));
    $res["pages"]=ceil($res["count"]/CATEGORY_PRODUCT_COUNT);
    if(count($res["products"])==0) $res=0;
    $app->render('show_category.html',array("res"=>$res,"menu"=>$menu,"username"=>$username,"cart"=>$cart,"request_uri"=>$request_uri,"language"=>$language));
});


$app->post('/order_summary', function() use ($app,$request_uri,$language,$menu,$username,$cart)
{
 $token=$app->request->post("token");
 $id_payment=$app->request->post("id_payment");
 $card_number=$app->request->post("card_number");
 $exp_month=$app->request->post("exp_month");
 $exp_year=$app->request->post("exp_year");
 $cvc=$app->request->post("cvc");
 $order=OrderCtl::SetOrderDetails(SessionCtl::GetSession(),array("id_payment_method"=>$id_payment,"id_delivery_method"=>1));
 $app->render('order_summary.html',array("order"=>$order,"token"=>$token,"cart"=>$cart,"language"=>$language,"card_number"=>$card_number,"exp_month"=>$exp_month,"exp_year"=>$exp_year,"cvc"=>$cvc));
});

$app->get('/:obj', function ($obj) use ($app,$request_uri,$language,$menu,$username,$cart) {
    // Sample log message
    $app->log->info("Slim-Skeleton '/' route");

       if($obj=="cart")
        {
         $res=CartCtl::Get(SessionCtl::GetSession());
         if(empty($res["contents"])) $res=0;
          $app->render('cart.html',array("res"=>$res,"menu"=>$menu,"username"=>$username,"cart"=>$cart,"request_uri"=>$request_uri,"language"=>$language));
        }
        elseif($obj=="checkout")
         {
           $error=$app->request->get("error");
           $app->render('checkout.html',array("res"=>$res,"menu"=>$menu,"username"=>$username,"cart"=>$res,"request_uri"=>$request_uri,"language"=>$language,"error"=>$error));
         }
        elseif($obj=="del_from_cart")
         {
           $res=CartCtl::Del(SessionCtl::GetSession(),$app->request->get("id"));
           if(count($res["contents"])==0) $res=0;
           $app->render('cart.html',array("res"=>$res,"menu"=>$menu,"username"=>$username,"cart"=>$res,"request_uri"=>$request_uri,"language"=>$language));
         }
         elseif($obj=="your-data")
          {
            if($_COOKIE["username"]!="")
             {
                $res=UserCtl::GetUserData(SessionCtl::GetSession());
                $app->render('userdata.html',array("userdata"=>$res,"menu"=>$menu,"username"=>$username,"cart"=>$cart,"request_uri"=>$request_uri,"language"=>$language));
             }
             else {
              $app->render('your_data.html',array("res"=>$res,"menu"=>$menu,"username"=>$username,"cart"=>$cart,"request_uri"=>$request_uri,"language"=>$language));
             }
          }
          elseif($obj=="login")
           {
             $profile=$app->request->get("profile");
             $app->render('login.html',array("res"=>$res,"menu"=>$menu,"username"=>$username,"cart"=>$cart,"request_uri"=>$request_uri,"profile"=>$profile,"language"=>$language));
           }
           elseif($obj=="logout")
            {
              UserCtl::Logout(SessionCtl::GetSession());
              setcookie('username',"");
              $app->render('logout.html',array("res"=>$res,"menu"=>$menu,"username"=>"","cart"=>$cart,"request_uri"=>$request_uri,"language"=>$language));
            }
          elseif($obj=="profile")
           {
             if($username=="")
             {
               $app->render('login.html',array("res"=>$res,"menu"=>$menu,"username"=>$username,"cart"=>$cart,"request_uri"=>$request_uri,"language"=>$language));
             }
             else {
               $res=UserCtl::GetUserOrders(SessionCtl::GetSession());
               $app->render('profile.html',array("res"=>$res,"menu"=>$menu,"username"=>$username,"cart"=>$cart,"request_uri"=>$request_uri,"language"=>$language));
             }
           }
          elseif($obj=="register")
           {
             $app->render("register.html",array("res"=>$res,"menu"=>$menu,"username"=>$username,"cart"=>$cart,"request_uri"=>$request_uri,"language"=>$language));
           }
           elseif($obj=="userdata")
            {
              //$order_data=OrderCtl::GetOrderDetails(SessionCtl::GetSession());
              $app->render('userdata.html',array("userdata"=>$order_data,"menu"=>$menu,"cart"=>$cart,"username"=>$username,"language"=>$language));
            }
           elseif($obj=="order_summary")
           {
             $token=$app->request->get("token");
             $id_payment=$app->request->get("id_payment");
             $order=OrderCtl::SetOrderDetails(SessionCtl::GetSession(),array("id_payment_method"=>$id_payment,"id_delivery_method"=>1));
             $app->render('order_summary.html',array("order"=>$order,"token"=>$token,"cart"=>$cart,"language"=>$language,"username"=>$username));
           }
           elseif($obj=="profile-userdata"){
             if($username=="")
             {
               $app->render('login.html',array("res"=>$res,"menu"=>$menu,"username"=>$username,"cart"=>$cart,"request_uri"=>$request_uri,"language"=>$language));
             }
             else {
               $res=UserCtl::GetUserData(SessionCtl::GetSession());
               $app->render('profile_userdata.html',array("userdata"=>$res,"menu"=>$menu,"username"=>$username,"cart"=>$cart,"request_uri"=>$request_uri,"language"=>$language));
             }
           }
           elseif($obj=="profile-password")
            {
              if($username=="")
              {
                $app->render('login.html',array("res"=>$res,"menu"=>$menu,"username"=>$username,"cart"=>$cart,"request_uri"=>$request_uri,"language"=>$language));
              }
              else {
              $app->render('profile_password.html',array("menu"=>$menu,"username"=>$username,"cart"=>$cart,"request_uri"=>$request_uri,"language"=>$language));
              }
            }
       else {
         if(is_numeric($obj))
         {
           $res=ShopobjectsCtl::GetProductDetails($obj);
         }
         else {
           $res=ShopobjectsCtl::SeoGetProductDetails($obj);
         }
         if($res["class"]=="sizeprod")
          {
	         $sizes=array();
	         $sizes[$res["attributes"]["size"]["value"]]=$res["id"];
	         foreach($res["variations"] as $variation)
          	{
		         $sizes[$variation["attributes"]["size"]["value"]]=$variation["id"];
	          }
	         asort($sizes);
	         $res["sizes"]=$sizes;
          }
        if($res["class"]=="colorprod")
         {
	        $colors=array();
	        $colors[$res["attributes"]["color"]["value"]]=$res["id"];
	        foreach($res["variations"] as $variation)
	         {
		        $colors[$variation["attributes"]["color"]["value"]]=$variation["id"];
           }
	        asort($colors);
	        $res["colors"]=$colors;
         }
        $tags=explode(",",$res["attributes"]["tags"]["value"]);
        $res["attributes"]["tags"]["arr"]=$tags;
        $app->render('show_product.html',array("res"=>$res,"menu"=>$menu,"username"=>$username,"cart"=>$cart,"request_uri"=>$request_uri,"language"=>$language));
       }
});



// Run app
$app->run();
