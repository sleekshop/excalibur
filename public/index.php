<?php
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Formatter\MessageFormatter;
use Symfony\Component\Translation\IdentityTranslator;
use Symfony\Component\Translation\Loader\MoFileLoader;
use Twig\TwigFunction;

if (!defined("SHOP_CONF_PATH")){
  define("SHOP_CONF_PATH","../vendor/sleekcommerce");
}

if (!defined("TEMPLATE_PATH")) {
  define("TEMPLATE_PATH", "../templates");
}

require __DIR__ . '/../vendor/autoload.php';
require SHOP_CONF_PATH . "/shop_conf.inc.php";
require "../vendor/sleekcommerce/init.inc.php";

// Create App
$app = AppFactory::create();

// Create Twig
$twig = Twig::create('../templates', ['cache' => false]);

// Create Translator
$translator = new Translator($language, new MessageFormatter(new IdentityTranslator()));
$translator->addLoader('mo', new MoFileLoader());
$translator->addResource('mo', '../var/languages/de_DE/LC_MESSAGES/de_DE.mo', 'de_DE');
$translator->addResource('mo', '../var/languages/de_DE/LC_MESSAGES/en_EN.mo', 'en_EN');
$twig->addExtension(new TranslationExtension($translator));


$environment = $twig->getEnvironment();
$environment->addFunction(new TwigFunction('__', function ($message) {
	return __($message);
}));

// Add Twig-View Middleware
$app->add(TwigMiddleware::create($app, $twig));

// Define routes

// Service Route

	//Reloading the menu
	$app->get("/reload-menu", function () use ($app, $language, $menu, $username, $cart) {

  		unlink(TEMPLATE_PATH . "/cache/menu.tmp");
  		die("WEBHOOK_EXECUTED");

	});

	//Reloading the menu
	$app->get("/reload-static-files", function ($request, $response) use ($app, $language, $menu, $username, $cart) {

		$lang = $request->getQueryParams()['language'] ?? '';

		if ($lang == "" or $lang == "all") {
		$lang = DEFAULT_LANGUAGE;
		}
		$id = $request->getQueryParams()['id_shopobject'] ?? '';

		$langstmp = explode("_", $lang);
		$prefix = array_shift($langstmp);

		$res = ShopobjectsCtl::GetContentDetails($id, $lang);
print_r($res);
		$about_us = $res["attributes"]["about_us_footer"]["value"];
		if (is_file(TEMPLATE_PATH . "/part_about_us_footer_" . $prefix . ".twig")) unlink(TEMPLATE_PATH . "/part_about_us_footer_" . $prefix . ".twig");
		file_put_contents(TEMPLATE_PATH . "/part_about_us_footer_" . $prefix . ".twig", $about_us);

		$logo = $res["attributes"]["logo"]["value"];
		if (is_file(TEMPLATE_PATH . "/part_logo.twig")) unlink(TEMPLATE_PATH . "/part_logo.twig");
		file_put_contents(TEMPLATE_PATH . "/part_logo.twig", "<img src='" . $logo . "' class='img-fluid' alt='' />");
    /*
    favicon - section
    */
    $favicon = $res["attributes"]["favicon"]["value"];
    if (is_file(TEMPLATE_PATH . "/part_favicon.twig")) unlink(TEMPLATE_PATH . "/part_favicon.twig");
		file_put_contents(TEMPLATE_PATH . "/part_favicon.twig", "<link rel='icon' type='image/png' href='".$favicon."'>");

		if (is_file(TEMPLATE_PATH . "/part_social_links.twig")) unlink(TEMPLATE_PATH . "/part_social_links.twig");
    /*
    Setting up the social - media icons
    */
    $insta = $res["attributes"]["instagram_link"]["value"];
    $tiktok = $res["attributes"]["tiktok_link"]["value"];
    $pinterest = $res["attributes"]["pinterest_link"]["value"];
    $twitter = $res["attributes"]["twitter_link"]["value"];
    $youtube = $res["attributes"]["youtube_link"]["value"];
    $face = $res["attributes"]["facebook_link"]["value"];
		$spotify = $res["attributes"]["spotify_link"]["value"];
    $linkedin = $res["attributes"]["linkedin_link"]["value"];


    $linkstr="";
    if($insta!="") $linkstr.="<a href='" . $insta .	"' target='_blank'><img src='../img/instagram.png' width='30px'></a> ";
    if($tiktok!="") $linkstr.="<a href='" . $tiktok .	"' target='_blank'><img src='../img/tiktok.png' width='30px'></a> ";
    if($pinterest!="") $linkstr.="<a href='" . $pinterest .	"' target='_blank'><img src='../img/pinterest.png' width='30px'></a> ";
    if($twitter!="") $linkstr.="<a href='" . $twitter .	"' target='_blank'><img src='../img/twitter.png' width='30px'></a> ";
    if($youtube!="") $linkstr.="<a href='" . $youtube .	"' target='_blank'><img src='../img/youtube.png' width='30px'></a> ";
    if($face!="") $linkstr.="<a href='" . $face .	"' target='_blank'><img src='../img/facebook.png' width='30px'></a> ";
    if($spotify!="") $linkstr.="<a href='" . $spotify .	"' target='_blank'><img src='../img/spotify.png' width='30px'></a> ";
    if($linkedin!="") $linkstr.="<a href='" . $linkedin .	"' target='_blank'><img src='../img/linkedin.png' width='30px'></a> ";
		file_put_contents(TEMPLATE_PATH . "/part_social_links.twig", $linkstr );

    /*
    * setting language switcher
    */
    $language_switcher=$res["attributes"]["language_switcher"]["value"];
    $switcher="{%macro sys_language_switcher()%}display:none;{%endmacro%}";
    if($language_switcher!="false") $switcher="{%macro sys_language_switcher()%}display:block;{%endmacro%}";
		file_put_contents(TEMPLATE_PATH . "/tpl_vars.twig", $switcher);

    /*
  * now the shop_conf.inc.php
  */
  $res["attributes"]["start_id"]["value"]!="" ? $start_id=$res["attributes"]["start_id"]["value"] : $start_id=1;
  $res["attributes"]["categories_id"]["value"]!="" ? $categories_id=$res["attributes"]["categories_id"]["value"] : $categories_id=2;
  $res["attributes"]["order_sender"]["value"]!="" ? $order_sender=$res["attributes"]["order_sender"]["value"] : $order_sender="info@sleekshop.io";
  $conf='<?php
  /*
   * ShopConf - file
   *
   * @ Kaveh Raji <kr@sleekcommerce.com>
   */
   //The Start-Category
   define("START_ID",'.$start_id.');
   //The categories id
   define("CATEGORIES_ID",'.$categories_id.');
   //The sender and receiver of the orders
   define("ORDER_SENDER","'.$order_sender.'");
   ?>';
   file_put_contents(SHOP_CONF_PATH . "/shop_conf.inc.php", $conf);
   echo "WEBHOOK_EXECUTED";
   die();
	});


	$app->get("/", function ($request, $response, $args) use ($app, $language, $menu, $username, $cart) {

  		$res = ShopobjectsCtl::GetShopObjects( START_ID, $language, "prio", "DESC", 0, 0, ["name", "img1", "price", "short_description"] );

  		$view = Twig::fromRequest($request);

  		return $view->render($response, 'index.twig', [
	  		"res" => 		$res,
	  		"menu" => 		$menu,
	  		"username" => 	$username,
	  		"cart" => 		$cart,
			"language" => 	$language,
  		]);

	})->setName('home');


	// Cart

	$app->post("/add-coupon", function ($request, $response, $args) use ($app, $language, $menu, $username, $cart) {

	  // Render index view
	  $coupon = $_POST['coupon'];
	  $sr = new SleekShopRequest();
	  $json = $sr->add_coupons(SessionCtl::GetSession(), [[$coupon, "Gutschein"]]);
	  $json = json_decode($json);
	  $error = "";
	  if ($json->object == "error") {
		$error = $json->message;
	  } else {
		// code...
	  }
	  $res = CartCtl::Get(SessionCtl::GetSession());

	  	$view = Twig::fromRequest($request);
		return $view->render($response, 'cart.twig', [
			"coupon_error" => 	$error,
			"res" => 			$res,
			"menu" => 			$menu,
			"username" => 		$username,
			"cart" => 			$res,
			"language" => 		$language,
		]);

	});


	$app->get("/get-invoice/{id}/{hash}", function ($request, $response, $args) use ($app, $language, $menu, $username, $cart) {

	  if (!(crypt($args['id'], TOKEN) == base64_decode($args['hash']))) {
		die("PERMISSION_DENIED");
	  }
	  $invoice = OrderCtl::GetInvoice($args['id']);
	  echo utf8_decode($invoice);
	  die();

	});

	//For changing the language
	$app->get("/change-lang", function ($request, $response) use ($app,$language,$menu,$username,$cart) {

	  unlink(TEMPLATE_PATH . "/cache/menu.tmp");

	  $language = $request->getQueryParams()['lang'] ?? '';

	  // Render index view
	  setcookie(TOKEN . "_lang", $language, time() + 3600);
	  setcookie(TOKEN . "_menu", "", time() + 3600);
	  $menu = CategoriesCtl::GetMenu($language);
	  $res = ShopobjectsCtl::GetShopObjects(1, $language, "price", "ASC", 0, 0, ["name", "img1", "price", "short_description"]);

	  $response = $response->withStatus(302);
	  return $response->withHeader('Location', '/');

	});

	$app->get("/content/{obj}", function ($request, $response, $args) use ($app, $request_uri, $language, $menu, $username, $cart) {

		// Render index view
		$res = ShopObjectsCtl::SeoGetContentDetails($args['obj']);

		if ($res == null) {
		  $response = $response->withStatus(302);
		  return $response->withHeader('Location', '/404');
		}

		$view = Twig::fromRequest($request);
		return $view->render($response, 'content_v1.twig', [
			"res" => 			$res,
			"menu" => 		$menu,
			"username" => 	$username,
			"cart" => 		$cart,
			"request_uri" => 	$request_uri,
			"language" => 	$language,
		]);

	  });

	$app->get("/page/{obj}", function ($request, $response, $args) use ($app, $request_uri, $language, $menu, $username, $cart) {

		// Render index view
		$res = ShopObjectsCtl::SeoGetShopobjects($args['obj'], "prio", "DESC");

		if ($res == null) {
			$response = $response->withStatus(302);
			return $response->withHeader('Location', '/404');
		}

		$view = Twig::fromRequest($request);
		return $view->render($response, 'content.twig', [
			"res" => 			$res,
			"menu" => 		$menu,
			"username" => 	$username,
			"cart" => 		$cart,
			"request_uri" => 	$request_uri,
			"language" => 	$language,
		]);

	});

	$app->get("/category/{obj}", function ($request, $response, $args) use ($app, $request_uri, $language, $menu, $username, $cart) {

	  // Render index view
	  if (is_numeric($args['obj'])) {
		$res = ShopobjectsCtl::GetShopobjects($args['obj'], $language, "price", "ASC", 0, 0, ["name", "short_description", "price", "img1"]);
	  } else {
		$res = ShopobjectsCtl::SeoGetShopobjects($args['obj'], "price", "ASC", 0, 0, [ "name", "short_description", "price", "img1" ]);
	  }

	  $view = Twig::fromRequest($request);
	  return $view->render($response, 'show_category.twig', [
		"res" => 			$res,
		"menu" => 			$menu,
		"username" => 		$username,
		"cart" => 			$cart,
		"request_uri" => 	$request_uri,
		"language" => 		$language,
	  ]);

	});

	$app->get("/express-checkout", function ($request, $response, $args) use ($app, $request_uri, $language, $menu, $username, $cart) {

	  // Render index view
	  if ($username != "") {
	  $res = UserCtl::GetUserData(SessionCtl::GetSession());
	  } else {
		$res = [];
	  }

	  $payment_methods = PaymentCtl::GetPaymentMethods();

	  $view = Twig::fromRequest($request);
	  return $view->render($response, 'express.twig', [
		"userdata" => 			$res,
		"menu" => 				$menu,
		"username" => 			$username,
		"cart" => 				$cart,
		"request_uri" => 		$request_uri,
		"language" => 			$language,
		"payment_methods" => 	$payment_methods
	  ]);

	});

	$app->post("/add_to_cart", function ($request, $response, $args) use ($app, $request_uri, $language, $menu, $username, $cart) {

	  // Render index viewdd
	  $id_product = $_POST['id_product'];
	  $pic = $_POST['pic'];
	  $quantity = $_POST['quantity'];

	  $res = CartCtl::Add(SessionCtl::GetSession(), $id_product, $quantity,	"price", "name", "short_description", $language, "PRODUCT", 0, [
		  [
			  "lang" => $language,
			  "name" => "pic",
			  "value" => $pic
		  ]]
	  );

	  $response = $response->withStatus(302);
	  return $response->withHeader('Location', '/cart');

	});

	$app->post("/login", function ($request, $response, $args) use ($app, $request_uri, $language, $menu, $username, $cart) {

	  $username = $_POST['username'];
	  $passwd = $_POST['password'];
	  $res = UserCtl::Login(SessionCtl::GetSession(), $username, $passwd);

	  if ($res["status"] == "SUCCESS") {

		$username = $res["username"];
		setcookie("username", $username);
		$res = UserCtl::GetUserData(SessionCtl::GetSession());
		$profile = $_POST['profile'];
		if ($profile != 1) {

			$view = Twig::fromRequest($request);
			return $view->render($response, 'userdata.twig', [
				"userdata" => 		$res,
				"menu" => 			$menu,
				"username" => 		$username,
				"cart" => 			$cart,
				"request_uri" => 	$request_uri,
				"language" => 		$language,
			]);

		} else {

		  $res = UserCtl::GetUserOrders(SessionCtl::GetSession());

		  $view = Twig::fromRequest($request);
			return $view->render($response, 'profile.twig', [
				"res" => 			$res,
				"menu" => 			$menu,
				"username" => 		$username,
				"cart" => 			$cart,
				"request_uri" => 	$request_uri,
				"language" => 		$language,
			]);

		}

	  } else {

		$view = Twig::fromRequest($request);
		return $view->render($response, 'login.twig', [
		  "error" => 		1,
		  "res" => 			$res,
		  "menu" => 		$menu,
		  "username" => 	"",
		  "cart" => 		$cart,
		  "request_uri" => 	$request_uri,
		  "language" => 	$language,
		]);

	  }

	});

	$app->post("/express-login", function ($request, $response, $args) use ($app, $request_uri, $language, $menu, $username, $cart) {

	  $username = $_POST['username'];
	  $passwd = $_POST['password'];
	  $res = UserCtl::Login(SessionCtl::GetSession(), $username, $passwd);

	  if ($res["status"] == "SUCCESS") {

		$username = $res["username"];
		setcookie("username", $username);
		$res = UserCtl::GetUserData(SessionCtl::GetSession());
		$profile = $_POST['profile'];

		$view = Twig::fromRequest($request);
		return $view->render($response, 'express.twig', [
			"userdata" => 		$res,
			"menu" => 			$menu,
			"username" => 		$username,
			"cart" => 			$cart,
			"request_uri" => 	$request_uri,
			"language" => 		$language,
		]);

	  } else {

		$view = Twig::fromRequest($request);
		return $view->render($response, 'express.twig', [
			"error" => 			'login',
			"res" => 			$res,
			"menu" => 			$menu,
			"username" => 		"",
			"cart" => 			$cart,
			"request_uri" => 	$request_uri,
			"language" => 		$language,
		]);

	  }

	});

	$app->post("/userdata", function ($request, $response, $args) use ($app, $request_uri, $language, $menu, $username, $cart) {

	  $error = [];
	  $error_count = 0;

	  if (isset($_POST['diffaddress']) && $_POST['diffaddress'] == "diffadd") {
		   $dsalutation = 	$_POST['dsalutation'];
		   $dfirstname = 	$_POST['dfirstname'];
		   $dlastname = 	$_POST['dlastname'];
		   $dcompany = 		$_POST['dcompanyname'];
		   $ddepartment = 	$_POST['ddepartment'];
		   $dstate = 		$_POST['dstate'];
		   $dcountry = 		$_POST['dcountry'];
		   $dstreet = 		$_POST['dstreet'];
		   $dnumber = 		$_POST['dnumber'];
		   $dzip = 			$_POST['dzip'];
		   $dcity = 		$_POST['dcity'];
	   } else {
		   $dsalutation =	$_POST['salutation'];
		   $dfirstname = 	$_POST['firstname'];
		   $dlastname = 	$_POST['lastname'];
		   $dcompany = 		$_POST['companyname'];
		   $ddepartment = 	$_POST['department'];
		   $dstate = 		$_POST['state'];
		   $dcountry = 		$_POST['country'];
		   $dstreet = 		$_POST['street'];
		   $dnumber = 		$_POST['number'];
		   $dzip = 			$_POST['zip'];
		   $dcity = 		$_POST['city'];
	   }

	   $salutation =	$_POST['salutation'];
	   $firstname = 	$_POST['firstname'];
	   $lastname = 		$_POST['lastname'];
	   $street = 		$_POST['street'];
	   $number = 		$_POST['number'];
	   $zip = 			$_POST['zip'];
	   $city = 			$_POST['city'];
	   $state = 		$_POST['state'];
	   $country = 		$_POST['country'];
	   $notes = 		$_POST['notes'];
	   $email = 		$_POST['email'];
	   //$phone = 		$_POST['phone'];
	   $company = 		$_POST['companyname'];
	   $department = 	$_POST['department'];


	   if (isset($_POST['diffaddress']) && $_POST['diffaddress'] == "diffadd") {
		   $userdata["dsalutation"] = 	$dsalutation;
		   $userdata["dfirstname"] =	$dfirstname;
		   $userdata["dlastname"] =		$dlastname;
		   $userdata["dcompany"] =		$dcompany;
		   $userdata["ddepartment"] =	$ddepartment;
		   $userdata["dstreet"] =		$dstreet;
		   $userdata["dnumber"] =		$dnumber;
		   $userdata["dzip"] =			$dzip;
		   $userdata["dcity"] =			$dcity;
		   $userdata["dstate"] =		$dstate;
		   $userdata["dcountry"] =		$dcountry;
	   } else {
		   $userdata["dsalutation"] =	$salutation;
		   $userdata["dfirstname"] =	$firstname;
		   $userdata["dlastname"] =		$lastname;
		   $userdata["dcompany"] =		$company;
		   $userdata["ddepartment"] =	$department;
		   $userdata["dstreet"] =		$street;
		   $userdata["dnumber"]	= 		$number;
		   $userdata["dzip"] =			$zip;
		   $userdata["dcity"] =			$city;
		   $userdata["dstate"] =		$state;
		   $userdata["dcountry"] =		$country;
	   }
	   $userdata["salutation"] =	$salutation;
	   $userdata["firstname"] =		$firstname;
	   $userdata["lastname"] =		$lastname;
	   $userdata["company"] = 		$company;
	   $userdata["department"] =	$department;
	   $userdata["street"] =		$street;
	   $userdata["number"] =		$number;
	   $userdata["zip"] =			$zip;
	   $userdata["city"] =			$city;
	   $userdata["state"] =			$state;
	   $userdata["country"] =		$country;
	   $userdata["notes"] =			$notes;
	   $userdata["email"] =			$email;
	   //$userdata["phone"] =			$phone;

	   $args=array(
		   "delivery_companyname" => 	$userdata["dcompany"],
		   "delivery_department" =>		$userdata["ddepartment"],
		   "delivery_salutation" =>		$userdata["dsalutation"],
		   "delivery_firstname" =>		$userdata["dfirstname"],
		   "delivery_lastname" =>		$userdata["dlastname"],
		   "delivery_street" =>			$userdata["dstreet"],
		   "delivery_number" =>			$userdata["dnumber"],
		   "delivery_zip" =>			$userdata["dzip"],
		   "delivery_state" =>			$userdata["dstate"],
		   "delivery_city" =>			$userdata["dcity"],
		   "delivery_country" => 		$userdata["dcountry"],
		   "invoice_companyname" =>		$userdata["company"],
		   "invoice_department" =>		$userdata["department"],
		   "invoice_salutation" =>		$userdata["salutation"],
		   "invoice_firstname" =>		$userdata["firstname"],
		   "invoice_lastname" =>		$userdata["lastname"],
		   "invoice_street" =>			$userdata["street"],
		   "invoice_number" =>			$userdata["number"],
		   "invoice_zip" =>				$userdata["zip"],
		   "invoice_state" =>			$userdata["state"],
		   "invoice_city" =>			$userdata["city"],
		   "invoice_country" =>			$userdata["country"],
		   "note" =>					$userdata["notes"],
		   "email" =>					$userdata["email"]
	   );

	  $order_data = OrderCtl::SetOrderDetails(SessionCtl::GetSession(), $args);

	  if ($email == "") {
		$error["email"] = "has-error";
	  }
	  if ($firstname == "") {
		$error["firstname"] = "has-error";
	  }
	  if ($lastname == "") {
		$error["lastname"] = "has-error";
	  }
	  if ($street == "") {
		$error["street"] = "has-error";
	  }
	  if ($number == "") {
		$error["number"] = "has-error";
	  }
	  if ($zip == "") {
		$error["zip"] = "has-error";
	  }
	  if ($city == "") {
		$error["city"] = "has-error";
	  }
	  if ($state == "") {
		$error["state"] = "has-error";
	  }
	  if ($country == "") {
		$error["country"] = "has-error";
	  }

	  if (count($error) != 0) {
		$error_count++;

		$view = Twig::fromRequest($request);
		return $view->render($response, 'userdata.twig', [
			"userdata" => $userdata,
			"error" => $error,
			"error_count" => $error_count,
			"language" => $language,
		]);

	  } else {
		$args = [
		  "companyname" => 	$userdata["company"],
		  "department" => 	$userdata["department"],
		  "salutation" => 	$userdata["salutation"],
		  "firstname" => 	$userdata["firstname"],
		  "lastname" => 	$userdata["lastname"],
		  "street" => 		$userdata["street"],
		  "number" => 		$userdata["number"],
		  "zip" => 			$userdata["zip"],
		  "state" => 		$userdata["state"],
		  "city" => 		$userdata["city"],
		  "country" => 		$userdata["country"],
		  "email" => 		$userdata["email"],
		];
		UserCtl::SetUserData(SessionCtl::GetSession(), $args);
		$payment_methods = PaymentCtl::GetPaymentMethods();

		$view = Twig::fromRequest($request);
		return $view->render($response, 'payment_methods.twig', [
			"payment_methods" => $payment_methods,
		  	"request_uri" => $request_uri,
		  	"language" => $language,
		  	"menu" => $menu,
		  	"username" => $username,
		  	"cart" => $cart,
		  	"language" => $language,
		]);

	  }

	});

	$app->post("/express-checkout", function ($request, $response, $args) use ($app, $request_uri, $language, $menu, $username, $cart) {

	  $error = [];
	  $error_count = 0;

	  if (isset($_POST['diffaddress']) && $_POST['diffaddress'] == "diffadd") {
		   $dsalutation = 	$_POST['dsalutation'];
		   $dfirstname = 	$_POST['dfirstname'];
		   $dlastname = 	$_POST['dlastname'];
		   $dcompany = 		$_POST['dcompanyname'];
		   $ddepartment = 	$_POST['ddepartment'];
		   $dstate = 		$_POST['dstate'];
		   $dcountry = 		$_POST['dcountry'];
		   $dstreet = 		$_POST['dstreet'];
		   $dnumber = 		$_POST['dnumber'];
		   $dzip = 			$_POST['dzip'];
		   $dcity = 		$_POST['dcity'];
	   } else {
		   $dsalutation =	$_POST['salutation'];
		   $dfirstname = 	$_POST['firstname'];
		   $dlastname = 	$_POST['lastname'];
		   $dcompany = 		$_POST['companyname'];
		   $ddepartment = 	$_POST['department'];
		   $dstate = 		$_POST['state'];
		   $dcountry = 		$_POST['country'];
		   $dstreet = 		$_POST['street'];
		   $dnumber = 		$_POST['number'];
		   $dzip = 			$_POST['zip'];
		   $dcity = 		$_POST['city'];
	   }

	   $salutation =	$_POST['salutation'];
	   $firstname = 	$_POST['firstname'];
	   $lastname = 		$_POST['lastname'];
	   $street = 		$_POST['street'];
	   $number = 		$_POST['number'];
	   $zip = 			$_POST['zip'];
	   $city = 			$_POST['city'];
	   $state = 		$_POST['state'];
	   $country = 		$_POST['country'];
	   $notes = 		$_POST['notes'] ?? '';
	   $email = 		$_POST['email'];
	   $phone = 		$_POST['phone'] ?? '';
	   $company = 		$_POST['companyname'] ?? '';
	   $department = 	$_POST['department'] ?? '';


	   if (isset($_POST['diffaddress']) && $_POST['diffaddress'] == "diffadd") {
		   $userdata["dsalutation"] = 	$dsalutation;
		   $userdata["dfirstname"] =	$dfirstname;
		   $userdata["dlastname"] =		$dlastname;
		   $userdata["dcompany"] =		$dcompany;
		   $userdata["ddepartment"] =	$ddepartment;
		   $userdata["dstreet"] =		$dstreet;
		   $userdata["dnumber"] =		$dnumber;
		   $userdata["dzip"] =			$dzip;
		   $userdata["dcity"] =			$dcity;
		   $userdata["dstate"] =		$dstate;
		   $userdata["dcountry"] =		$dcountry;
	   } else {
		   $userdata["dsalutation"] =	$salutation;
		   $userdata["dfirstname"] =	$firstname;
		   $userdata["dlastname"] =		$lastname;
		   $userdata["dcompany"] =		$company;
		   $userdata["ddepartment"] =	$department;
		   $userdata["dstreet"] =		$street;
		   $userdata["dnumber"]	= 		$number;
		   $userdata["dzip"] =			$zip;
		   $userdata["dcity"] =			$city;
		   $userdata["dstate"] =		$state;
		   $userdata["dcountry"] =		$country;
	   }
	   $userdata["salutation"] =	$salutation;
	   $userdata["firstname"] =		$firstname;
	   $userdata["lastname"] =		$lastname;
	   $userdata["company"] = 		$company;
	   $userdata["department"] =	$department;
	   $userdata["street"] =		$street;
	   $userdata["number"] =		$number;
	   $userdata["zip"] =			$zip;
	   $userdata["city"] =			$city;
	   $userdata["state"] =			$state;
	   $userdata["country"] =		$country;
	   $userdata["notes"] =			$notes;
	   $userdata["email"] =			$email;
	   $userdata["phone"] =			$phone;

	   $args=array(
		   "delivery_companyname" => 	$userdata["dcompany"],
		   "delivery_department" =>		$userdata["ddepartment"],
		   "delivery_salutation" =>		$userdata["dsalutation"],
		   "delivery_firstname" =>		$userdata["dfirstname"],
		   "delivery_lastname" =>		$userdata["dlastname"],
		   "delivery_street" =>			$userdata["dstreet"],
		   "delivery_number" =>			$userdata["dnumber"],
		   "delivery_zip" =>			$userdata["dzip"],
		   "delivery_state" =>			$userdata["dstate"],
		   "delivery_city" =>			$userdata["dcity"],
		   "delivery_country" => 		$userdata["dcountry"],
		   "invoice_companyname" =>		$userdata["company"],
		   "invoice_department" =>		$userdata["department"],
		   "invoice_salutation" =>		$userdata["salutation"],
		   "invoice_firstname" =>		$userdata["firstname"],
		   "invoice_lastname" =>		$userdata["lastname"],
		   "invoice_street" =>			$userdata["street"],
		   "invoice_number" =>			$userdata["number"],
		   "invoice_zip" =>				$userdata["zip"],
		   "invoice_state" =>			$userdata["state"],
		   "invoice_city" =>			$userdata["city"],
		   "invoice_country" =>			$userdata["country"],
		   "note" =>					$userdata["notes"],
		   "email" =>					$userdata["email"]
	   );

	  $order_data = OrderCtl::SetOrderDetails(SessionCtl::GetSession(), $args);

	  if ($email == "") {
		$error["email"] = "has-error";
	  }
	  if ($firstname == "") {
		$error["firstname"] = "has-error";
	  }
	  if ($lastname == "") {
		$error["lastname"] = "has-error";
	  }
	  if ($street == "") {
		$error["street"] = "has-error";
	  }
	  if ($number == "") {
		$error["number"] = "has-error";
	  }
	  if ($zip == "") {
		$error["zip"] = "has-error";
	  }
	  if ($city == "") {
		$error["city"] = "has-error";
	  }
	  if ($state == "") {
		$error["state"] = "has-error";
	  }
	  if ($country == "") {
		$error["country"] = "has-error";
	  }

	  if (count($error) != 0) {
		$error_count++;

		$view = Twig::fromRequest($request);
		return $view->render($response, 'express.twig', [
			"userdata" => $userdata,
		  	"error" => $error,
		  	"error_count" => $error_count,
		  	"language" => $language,
		]);

	  } else {
		$args = [
		  "companyname" => 	$userdata["company"],
		  "department" => 	$userdata["department"],
		  "salutation" => 	$userdata["salutation"],
		  "firstname" => 	$userdata["firstname"],
		  "lastname" => 	$userdata["lastname"],
		  "street" => 		$userdata["street"],
		  "number" => 		$userdata["number"],
		  "zip" => 			$userdata["zip"],
		  "state" => 		$userdata["state"],
		  "city" => 		$userdata["city"],
		  "country" => 		$userdata["country"],
		  "email" => 		$userdata["email"],
		];
		UserCtl::SetUserData(SessionCtl::GetSession(), $args);

		$id_payment = $_POST['id_payment'];
		$order = OrderCtl::SetOrderDetails(SessionCtl::GetSession(), [
		  "id_payment_method" => 	$id_payment,
		  "id_delivery_method" => 	1,
		]);

		$view = Twig::fromRequest($request);
		return $view->render($response, 'order_summary.twig', [
			"order" => 		$order,
		  	"cart" => 		$cart,
		  	"language" => 	$language,
		  	"username" => 	$username,
		]);

	  }

	});

	$app->post("/profile-userdata", function ($request, $response, $args) use ($app, $request_uri, $language, $menu, $username, $cart) {

	  $error = [];
	  $error_count = 0;

	  $salutation = 	$_POST['salutation'];
	  $firstname = 		$_POST['firstname'];
	  $lastname = 		$_POST['lastname'];
	  $company = 		$_POST['company'];
	  $department = 	$_POST['department'];
	  $street = 		$_POST['street'];
	  $number = 		$_POST['number'];
	  $zip = 			$_POST['zip'];
	  $city = 			$_POST['city'];
	  $state = 			$_POST['state'];
	  $country = 		$_POST['country'];
	  $notes = 			$_POST['notes'];
	  $email = 			$_POST['email'];

	  $userdata["salutation"] = 	$salutation;
	  $userdata["firstname"] = 		$firstname;
	  $userdata["lastname"] = 		$lastname;
	  $userdata["companyname"] = 	$company;
	  $userdata["department"] = 	$department;
	  $userdata["street"] = 		$street;
	  $userdata["number"] = 		$number;
	  $userdata["zip"] = 			$zip;
	  $userdata["city"] = 			$city;
	  $userdata["state"] = 			$state;
	  $userdata["country"] = 		$country;
	  $userdata["notes"] = 			$notes;
	  $userdata["email"] = 			$email;

	  $args = [
		"delivery_companyname" => 	$userdata["company"] ?? '',
		"delivery_department" => 	$userdata["department"] ?? '',
		"delivery_salutation" => 	$userdata["salutation"] ?? '',
		"delivery_firstname" => 	$userdata["firstname"] ?? '',
		"delivery_lastname" => 		$userdata["lastname"] ?? '',
		"delivery_street" => 		$userdata["street"] ?? '',
		"delivery_number" => 		$userdata["number"] ?? '',
		"delivery_zip" => 			$userdata["zip"] ?? '',
		"delivery_state" => 		$userdata["state"] ?? '',
		"delivery_city" => 			$userdata["city"] ?? '',
		"delivery_country" => 		$userdata["country"] ?? '',
		"invoice_companyname" => 	$userdata["company"] ?? '',
		"invoice_department" => 	$userdata["department"] ?? '',
		"invoice_salutation" => 	$userdata["salutation"] ?? '',
		"invoice_firstname" => 		$userdata["firstname"] ?? '',
		"invoice_lastname" => 		$userdata["lastname"] ?? '',
		"invoice_street" => 		$userdata["street"] ?? '',
		"invoice_number" => 		$userdata["number"] ?? '',
		"invoice_zip" => 			$userdata["zip"] ?? '',
		"invoice_state" => 			$userdata["state"] ?? '',
		"invoice_city" => 			$userdata["city"] ?? '',
		"invoice_country" => 		$userdata["country"] ?? '',
		"note" => 					$userdata["note"] ?? '',
		"email" => 					$userdata["email"] ?? '',
	  ];

	  if ($email == "") {
		$error["email"] = "has-error";
	  }
	  if ($firstname == "") {
		$error["firstname"] = "has-error";
	  }
	  if ($lastname == "") {
		$error["lastname"] = "has-error";
	  }
	  if ($street == "") {
		$error["street"] = "has-error";
	  }
	  if ($number == "") {
		$error["number"] = "has-error";
	  }
	  if ($zip == "") {
		$error["zip"] = "has-error";
	  }
	  if ($city == "") {
		$error["city"] = "has-error";
	  }
	  if ($state == "") {
		$error["state"] = "has-error";
	  }
	  if ($country == "") {
		$error["country"] = "has-error";
	  }

	  if (count($error) != 0) {
		$error_count++;

		$view = Twig::fromRequest($request);
		return $view->render($response, 'profile_userdata.twig', [
			"userdata" => 		$userdata,
			"error" => 			$error,
			"error_count" => 	$error_count,
		]);

	  } else {
		$args = [
		  "companyname" => 	$userdata["companyname"],
		  "department" => 	$userdata["department"],
		  "salutation" => 	$userdata["salutation"],
		  "firstname" => 	$userdata["firstname"],
		  "lastname" => 	$userdata["lastname"],
		  "street" => 		$userdata["street"],
		  "number" => 		$userdata["number"],
		  "zip" => 			$userdata["zip"],
		  "state" => 		$userdata["state"],
		  "city" => 		$userdata["city"],
		  "country" => 		$userdata["country"],
		  "email" => 		$userdata["email"],
		];
		$res = UserCtl::SetUserData(SessionCtl::GetSession(), $args);

		//$payment_methods=PaymentCtl::GetPaymentMethods();

		$view = Twig::fromRequest($request);
		return $view->render($response, 'profile_userdata.twig', [
			"userdata" => 		$userdata,
			"request_uri" => 	$request_uri,
			"language" => 		$language,
			"menu" => 			$menu,
			"username" => 		$username,
			"cart" => 			$cart,
			"language" => 		$language,
			"success" => 		true
		]);

	  }

	});

	$app->post("/profile-password", function ($request, $response, $args) use ($app, $request_uri, $language, $menu, $username, $cart) {

	  $error_count = 0;
	  $passwd1 = $_POST['passwd1'];
	  $passwd2 = $_POST['passwd2'];
	  $passwd3 = $_POST['passwd3'];

	  $res = UserCtl::SetUserPassword(SessionCtl::GetSession(), $passwd1, $passwd2, $passwd3);

	  if ($res["status"] != "SUCCESS") {
		$error = $res["status"];
		$error_count++;
	  }

		$view = Twig::fromRequest($request);
		return $view->render($response, 'profile_password.twig', [
			"request_uri" => 	$request_uri,
			"language" => 		$language,
			"menu" => 			$menu,
			"username" => 		$username,
			"cart" => 			$cart,
			"error_count" => 	$error_count,
			"error" => 			$error,
			"already_sent" => 	1,
			"language" => 		$language,
		]);

	});

	$app->post("/checkout", function ($request, $response, $args) use ($app, $request_uri, $language, $menu, $username, $cart) {

	  $cart = CartCtl::Get(SessionCtl::GetSession());

	  $token = 			$_POST['token'];
	  $card_number = 	$_POST['card_number'];
	  $cvc = 			$_POST['cvc'];
	  $exp_month = 		$_POST['exp_month'];
	  $exp_year = 		$_POST['exp_year'];

	  $delivery_costs = [["Delivery", $cart["delivery_costs"]["sum"], 0.19]];
	  OrderCtl::AddDeliveryCosts(SessionCtl::GetSession(), $delivery_costs);

	  $res = OrderCtl::Checkout(SessionCtl::GetSession());
	  if ($res["status"] == "success") {
		/*
		 * Send the order - email
		 */
		$order = OrderCtl::GetOrderDetails(SessionCtl::GetSession());
		$subject = "Bestellbestätigung";
		$invoice_link = "https://" . $_SERVER["HTTP_HOST"] . "/get-invoice/" . $res["id_order"] . "/" . base64_encode(crypt($res["id_order"], TOKEN));
		$msg = OrderCtl::GetOrderConfirmation($res["id_order"], [
		  "invoice_link" => $invoice_link,
		]);
		send_html_mail($order["email"], utf8_decode($subject), utf8_decode($msg), ORDER_SENDER);
		send_html_mail(ORDER_SENDER, utf8_decode($subject), utf8_decode($msg), ORDER_SENDER);

		$id_order = $res["id_order"];
		$session = $res["session"];

		SessionCtl::SetSession($session);
		setcookie("cart", "");
		$cart = [];

		$res = OrderCtl::DoPayment($id_order, [
		  "success_url" => "https://" . $_SERVER["HTTP_HOST"] . "/checkout",
		  "cancel_url" => "https://" . $_SERVER["HTTP_HOST"] . "/checkout?error=1",
		]);

		$redirect = "";

		if ($res["status"] == "Success" and $res["redirect"] != "") {
		  $redirect = html_entity_decode($res["redirect"]);
		}

	  } elseif ($res["status"] == "error" and $res["message"] == "SHOPOBJECT_NOT_AVAILABLE") {

		$cart = CartCtl::Refresh(SessionCtl::GetSession());

		foreach ($cart["contents"] as $prod) {
		  if ($prod["type"] == "DELIVERY_COSTS") {
			$cart = CartCtl::Del(SessionCtl::GetSession(), $prod["id"]);
		  }
		}

		$tpl->assign("cart", $cart);
		$tpl->assign("missing_id", $res["param"]);
		$pages = ["product_not_available"];

	  }

	  $view = Twig::fromRequest($request);
		return $view->render($response, 'checkout.twig', [
			"token" => 			$token,
			"redirect" => 		$redirect,
			"res" =>	 		$res,
			"request_uri" => 	$request_uri,
			"language" => 		$language,
			"menu" => 			$menu,
			"username" => 		$username,
			"cart" => 			$cart,
		]);

	});

	$app->post("/register", function ($request, $response, $args) use ($app, $request_uri, $language, $menu, $username, $cart) {

	  $user = 			$_POST['user'];
	  $email = 			$_POST['email'];
	  $passwd1 = 		$_POST['password'];
	  $passwd2 = 		$_POST['password2'];
	  $already_sent = 	$_POST['already_sent'];
	  $error = 			0;

	  if ($already_sent == 1) {
		$res = UserCtl::RegisterUser([
			"username" => 	$user,
			"email" => 		$email,
			"passwd1" => 	$passwd1,
			"passwd2" => 	$passwd2,
		  ],
		  $language
		);
		if ($res["status"] == "SUCCESS") {

		  UserCtl::VerifyUser($res["id_user"], $res["session_id"]);

			$view = Twig::fromRequest($request);
			return $view->render($response, 'login.twig', [
				"error" => 		0,
				"user" => 		$user,
				"email" => 		$email,
				"cart" => 		$cart,
				"language" => 	$language,
				"menu" => 		$menu,
			]);

		} else {

		  $error++;
		  $error_msg = $res["status"];

		  $view = Twig::fromRequest($request);
			return $view->render($response, 'register.twig', [
				"error_msg" => 	$error_msg,
				"error" => 		1,
				"user" => 		$user,
				"email" => 		$email,
				"cart" => 		$cart,
				"language" => 	$language,
				"menu" => 		$menu,
			]);

		}
	  }

	});

	$app->post("/express-register", function ($request, $response, $args) use ($app, $request_uri, $language, $menu, $username,$cart) {

	  $user = 			$_POST['user'];
	  $email = 			$_POST['email'];
	  $passwd1 = 		$_POST['password'];
	  $passwd2 = 		$_POST['password2'];
	  $already_sent = 	$_POST['already_sent'];
	  $error = 			0;

	  if ($already_sent == 1) {

		$res = UserCtl::RegisterUser(
		  [
			"username" => 	$user,
			"email" => 		$email,
			"passwd1" => 	$passwd1,
			"passwd2" => 	$passwd2,
		  ],
		  $language
		);

		if ($res["status"] == "SUCCESS") {

		  UserCtl::VerifyUser($res["id_user"], $res["session_id"]);

		  	$view = Twig::fromRequest($request);
			return $view->render($response, 'express.twig', [
				"error" => 		0,
				"user" => 		$user,
				"email" => 		$email,
				"cart" => 		$cart,
				"language" => 	$language,
				"menu" => 		$menu,
			]);

		} else {

		  $error++;
		  $error_msg = $res["status"];

		  	$view = Twig::fromRequest($request);
			return $view->render($response, 'express.twig', [
				"error_msg" => 	$error_msg,
				"error" => 		'register',
				"user" => 		$user,
				"email" => 		$email,
				"cart" => 		$cart,
				"language" => 	$language,
				"menu" => 		$menu,
			]);

		}
	  }

	});

	$app->get("/search", function ($request, $response, $args) use ($app, $request_uri, $language, $menu, $username, $cart) {

	  // Render index view
	  $searchstring = $request->getQueryParams()['searchstring'] ?? '';
	  ($request->getQueryParams()['page'] ?? '' != "")
		? ($page = $request->getQueryParams()['page'])
		: ($page = 1);

	  $constraint = [
		[
		  "OR" => ["name" => ["LIKE", $searchstring]],
		  ["short_description" => ["LIKE", $searchstring]],
		  ["description" => ["LIKE", $searchstring]],
		  ["vendor" => ["LIKE", $searchstring]],
		  ["tags" => ["LIKE", $searchstring]],
		],
	  ];
	  $left_limit = 0;
	  $right_limit = 20;
	  $res = ShopobjectsCtl::SearchProducts($constraint, $left_limit, $right_limit, [], "ASC", $language, ["name", "img1", "price", "short_description"]);

	  $res["pages"] = ceil($res["count"] / CATEGORY_PRODUCT_COUNT);

	  if (count($res["products"]) == 0) {
		$res = 0;
	  }

	  	$view = Twig::fromRequest($request);
		return $view->render($response, 'show_category.twig', [
			"res" => 			$res,
			"menu" => 			$menu,
			"username" => 		$username,
			"cart" => 			$cart,
			"request_uri" => 	$request_uri,
			"language" => 		$language,
		]);

	});

	$app->post("/order_summary", function ($request, $response, $args) use ($app, $request_uri, $language, $menu, $username, $cart) {

	  $token = 			$_POST['token'];
	  $id_payment = 	$_POST['id_payment'];
	  $card_number = 	$_POST['card_number'];
	  $exp_month = 		$_POST['exp_month'];
	  $exp_year = 		$_POST['exp_year'];
	  $cvc = 			$_POST['cvc'];

	  $order = OrderCtl::SetOrderDetails(SessionCtl::GetSession(), [
		"id_payment_method" => 	$id_payment,
		"id_delivery_method" => 1,
	  ]);

	  $view = Twig::fromRequest($request);
	  return $view->render($response, 'order_summary.twig', [
		"order" => 			$order,
		"token" => 			$token,
		"cart" => 			$cart,
		"language" => 		$language,
		"card_number" => 	$card_number,
		"exp_month" => 		$exp_month,
		"exp_year" => 		$exp_year,
		"cvc" => 			$cvc,
	  ]);

	});

	$app->get("/{obj}", function ($request, $response, $args) use ($app, $request_uri, $language, $menu, $username, $cart) {

		$obj = $args['obj'] ?? '';

	  	if ($obj == "cart") {

			$res = CartCtl::Get(SessionCtl::GetSession());
			if (empty($res["contents"])) {
		  		$res = 0;
			}

			$view = Twig::fromRequest($request);
			return $view->render($response, 'cart.twig', [
				"res" => 			$res,
				"menu" =>	 		$menu,
				"username" => 		$username,
				"cart" => 			$cart,
				"request_uri" => 	$request_uri,
				"language" => 		$language,
			]);

	  	} elseif ($obj == "checkout") {
			$error = $request->getQueryParams()['error'] ?? '';

			$view = Twig::fromRequest($request);
			return $view->render($response, 'checkout.twig', [
				"res" => 			$res ?? '',
				"menu" => 			$menu,
				"username" => 		$username,
				"cart" => 			$res ?? '',
				"request_uri" => 	$request_uri,
				"language" => 		$language,
				"error" => 			$error,
			]);

	  	} elseif ($obj == "del_from_cart") {

			$res = CartCtl::Del(SessionCtl::GetSession(), $request->getQueryParams()['id'] ?? '');
			if (count($res["contents"]) == 0) {
		  		$res = 0;
			}

			$response = $response->withStatus(302);
			return $response->withHeader('Location', '/cart');

	  	} elseif ($obj == "your-data") {

			if ($username != "") {
		  		$res = UserCtl::GetUserData(SessionCtl::GetSession());

		  		$view = Twig::fromRequest($request);
				return $view->render($response, 'userdata.twig', [
					"userdata" => 		$res,
					"menu" => 			$menu,
					"username" => 		$username,
					"cart" => 			$cart,
					"request_uri" => 	$request_uri,
					"language" => 		$language,
				]);

			} else {

				$view = Twig::fromRequest($request);
				return $view->render($response, 'your_data.twig', [
					"menu" => 			$menu,
					"username" => 		$username,
					"cart" => 			$cart,
					"request_uri" => 	$request_uri,
					"language" => 		$language,
				]);

			}

	  	} elseif ($obj == "login") {

			$profile = $request->getQueryParams()['profile'] ?? '';

			$view = Twig::fromRequest($request);
			return $view->render($response, 'login.twig', [
				"menu" => 			$menu,
				"username" => 		$username,
				"cart" => 			$cart,
				"request_uri" => 	$request_uri,
				"profile" => 		$profile,
				"language" => 		$language,
			]);

	  	} elseif ($obj == "logout") {
			UserCtl::Logout(SessionCtl::GetSession());
			setcookie("username", "");

			$view = Twig::fromRequest($request);
			return $view->render($response, 'logout.twig', [
				"menu" => 			$menu,
		  		"username" => 		"",
		  		"cart" => 			$cart,
		  		"request_uri" => 	$request_uri,
			  	"language" => 		$language,
			]);

	  	} elseif ($obj == "profile") {
			if ($username == "") {

				$view = Twig::fromRequest($request);
				return $view->render($response, 'login.twig', [
					"res" => 			$res,
					"menu" => 			$menu,
					"username" => 		$username,
					"cart" => 			$cart,
					"request_uri" => 	$request_uri,
					"language" => 		$language,
				]);

			} else {

				$res = UserCtl::GetUserOrders(SessionCtl::GetSession());

				$view = Twig::fromRequest($request);
				return $view->render($response, 'profile.twig', [
					"res" => 			$res,
					"menu" => 			$menu,
					"username" => 		$username,
					"cart" => 			$cart,
					"request_uri" => 	$request_uri,
					"language" => 		$language,
				]);

			}

	  	} elseif ($obj == "register") {

			$view = Twig::fromRequest($request);
			return $view->render($response, 'register.twig', [
				"menu" => 			$menu,
				"username" => 		$username,
				"cart" => 			$cart,
				"request_uri" => 	$request_uri,
				"language" => 		$language,
			]);

	  	} elseif ($obj == "userdata") {

			$view = Twig::fromRequest($request);
			return $view->render($response, 'userdata.twig', [
				"menu" => 		$menu,
				"cart" => 		$cart,
				"username" => 	$username,
				"language" => 	$language,
			]);

	  	} elseif ($obj == "order_summary") {

			$token = $request->getQueryParams()['token'] ?? '';
			$id_payment = $request->getQueryParams()['id_payment'] ?? '';

			$order = OrderCtl::SetOrderDetails(SessionCtl::GetSession(), [
		  		"id_payment_method" => 	$id_payment,
		  		"id_delivery_method" => 1,
			]);

			$view = Twig::fromRequest($request);
			return $view->render($response, 'order_summary.twig', [
				"order" => 		$order,
		  		"token" => 		$token,
		  		"cart" => 		$cart,
		  		"language" => 	$language,
		  		"username" => 	$username,
			]);

	  	} elseif ($obj == "profile-userdata") {

			if ($username == "") {

				$view = Twig::fromRequest($request);
				return $view->render($response, 'login.twig', [
					"res" => 			$res,
					"menu" => 			$menu,
					"username" => 		$username,
					"cart" => 			$cart,
					"request_uri" => 	$request_uri,
					"language" => 		$language,
				]);

			} else {

				$res = UserCtl::GetUserData(SessionCtl::GetSession());

		  		$view = Twig::fromRequest($request);
				return $view->render($response, 'profile_userdata.twig', [
					"userdata" => 		$res,
					"menu" => 			$menu,
					"username" =>	 	$username,
					"cart" => 			$cart,
					"request_uri" => 	$request_uri,
					"language" => 		$language,
				]);

			}

		} elseif ($obj == "404") {

			$view = Twig::fromRequest($request);
			return $view->render($response, '404.twig', [
				"menu" => 			$menu,
				"username" => 		$username,
				"cart" => 			$cart,
				"request_uri" => 	$request_uri,
				"language" => 		$language,
			]);

		} elseif ($obj == "index.php") {

			$view = Twig::fromRequest($request);
			return $view->render($response, '404.twig', [
				"menu" => 			$menu,
				"username" => 		$username,
				"cart" => 			$cart,
				"request_uri" => 	$request_uri,
				"language" => 		$language,
			]);

	  	} elseif ($obj == "profile-password") {

			if ($username == "") {

				$view = Twig::fromRequest($request);
				return $view->render($response, 'login.twig', [
					"res" => 			$res,
					"menu" => 			$menu,
					"username" => 		$username,
					"cart" => 			$cart,
					"request_uri" => 	$request_uri,
					"language" => 		$language,
				]);

			} else {

				$view = Twig::fromRequest($request);
				return $view->render($response, 'profile_password.twig', [
					"menu" => 			$menu,
					"username" => 		$username,
					"cart" => 			$cart,
					"request_uri" => 	$request_uri,
					"language" => 		$language,
				]);

			}

		} else {

			if (is_numeric($obj)) {
		  		$res = ShopobjectsCtl::GetProductDetails($obj, $language);
			} else {
		  		$res = ShopobjectsCtl::SeoGetProductDetails($obj);
			}

			if (isset($res["attributes"]["color"]["value"]) && $res["attributes"]["color"]["value"] != "") {

				$colors = [];
		  		$colors[$res["attributes"]["color"]["value"]] = $res["id"];

				foreach ($res["variations"] as $variation) {
					if ($res["attributes"]["color"]["value"] != $variation["attributes"]["color"]["value"]) {
			  			$colors[$variation["attributes"]["color"]["value"]] = $variation["id"];
					}
		  		}
		  		asort($colors);
		  		$res["colors"] = $colors;

			}

			if (isset($res["attributes"]["size"]["value"]) && $res["attributes"]["size"]["value"] != "") {

				$sizes = [];
		  		$sizes[$res["attributes"]["size"]["value"]] = $res["id"];
		  		foreach ($res["variations"] as $variation) {

					if ($variation["attributes"]["color"]["value"] == $res["attributes"]["color"]["value"]) {

						if ($res["attributes"]["size"]["value"] != $variation["attributes"]["size"]["value"]) {
							$sizes[$variation["attributes"]["size"]["value"]] = $variation["id"];
			  			}
					}
		  		}

		  		ksort($sizes);
		  		$res["sizes"] = $sizes;

			}

			$tags = explode(",", $res["attributes"]["tags"]["value"]);
			$res["attributes"]["tags"]["arr"] = $tags;

			$view = Twig::fromRequest($request);
			return $view->render($response, 'show_product.twig', [
				"res" => 			$res,
		  		"menu" => 			$menu,
				"username" => 		$username,
		  		"cart" => 			$cart,
		  		"request_uri" => 	$request_uri,
		  		"language" => 		$language,
			]);

	  	}

	});



// Run app
$app->run();

?>
