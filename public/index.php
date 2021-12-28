<?php

if (!defined("TEMPLATE_PATH")) {
  define("TEMPLATE_PATH", "../templates");
}
require "../vendor/autoload.php";
require "../vendor/sleekcommerce/init.inc.php";

// Prepare app
$app = new \Slim\Slim([
  "templates.path" => TEMPLATE_PATH,
]);

// Create monolog logger and store logger in container as singleton
// (Singleton resources retrieve the same log resource definition each time)
$app->container->singleton("log", function () {
  $log = new \Monolog\Logger("slim-skeleton");
  $log->pushHandler(
    new \Monolog\Handler\StreamHandler(
      "../logs/app.log",
      \Monolog\Logger::DEBUG
    )
  );
  return $log;
});

// Prepare view
$app->view(new \Slim\Views\Twig());
$app->view->parserOptions = [
  "charset" => "utf-8",
  "cache" => realpath(TEMPLATE_PATH . "/cache"),
  "auto_reload" => true,
  "strict_variables" => false,
  "autoescape" => true,
];

$app->view->parserExtensions = [new Twig_Extensions_Extension_I18n()];

//Reloading the menu
$app->get("/reload-menu", function () use (
  $app,
  $language,
  $menu,
  $username,
  $cart
) {
  unlink(TEMPLATE_PATH . "/cache/menu.tmp");
  die("WEBHOOK_EXECUTED");
});

//Reloading the menu
$app->get("/reload-static-files", function () use (
  $app,
  $language,
  $menu,
  $username,
  $cart
) {
  $lang = $app->request->get("language");
  if ($lang == "" or $lang == "all") {
    $lang = DEFAULT_LANGUAGE;
  }
  $id = $app->request->get("id_shopobject");
  $prefix = array_shift(explode("_", $lang));
  $res = ShopobjectsCtl::GetContentDetails($id, $lang);
  $about_us = $res["attributes"]["about_us_footer"]["value"];
  unlink(TEMPLATE_PATH . "/part_about_us_footer_" . $prefix . ".html");
  file_put_contents(
    TEMPLATE_PATH . "/part_about_us_footer_" . $prefix . ".html",
    $about_us
  );
  $logo = $res["attributes"]["logo"]["value"];
  unlink(TEMPLATE_PATH . "/part_logo.html");
  file_put_contents(
    TEMPLATE_PATH . "/part_logo.html",
    "<img src='" . $logo . "' border='0'>"
  );
  $face = $res["attributes"]["facebook_link"]["value"];
  $insta = $res["attributes"]["instagram_link"]["value"];
  unlink(TEMPLATE_PATH . "/part_social_links.html");
  file_put_contents(
    TEMPLATE_PATH . "/part_social_links.html",
    "<a href='" .
      $face .
      "' target='_blank'><img src='../img/facebook.png' width='50px'></a>
    <a href='" .
      $insta .
      "' target='_blank'><img src='../img/insta.jpg' width='50px'></a>"
  );
  echo "WEBHOOK_EXECUTED";
});

$app->post("/add-coupon", function () use (
  $app,
  $language,
  $menu,
  $username,
  $cart
) {
  $app->log->info("Slim-Skeleton '/' route");
  // Render index viewdd
  $coupon = $app->request->post("coupon");
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
  $app->render("cart.html", [
    "coupon_error" => $error,
    "res" => $res,
    "menu" => $menu,
    "username" => $username,
    "cart" => $res,
    "language" => $language,
  ]);
});

$app->get("/get-invoice/:id/:hash", function ($id, $hash) use (
  $app,
  $language,
  $menu,
  $username,
  $cart
) {
  //$app->log->info("Slim-Skeleton "/" route");
  if (!(crypt($id, TOKEN) == base64_decode($hash))) {
    die("PERMISSION_DENIED");
  }
  $invoice = OrderCtl::GetInvoice($id);
  echo utf8_decode($invoice);
  die();
});

//For changing the language
$app->get("/change-lang", function () use (
  $app,
  $language,
  $menu,
  $username,
  $cart
) {
  unlink(TEMPLATE_PATH . "/cache/menu.tmp");
  //$app->log->info("Slim-Skeleton '/' route");
  $language = $app->request->get("lang");
  // Render index viewdd
  $app->setCookie(TOKEN . "_lang", $language, time() + 3600);
  $app->setCookie(TOKEN . "_menu", "", time() + 3600);
  $menu = CategoriesCtl::GetMenu($language);
  $res = ShopobjectsCtl::GetShopObjects(1, $language, "price", "ASC", 0, 0, [
    "name",
    "img1",
    "price",
    "short_description",
  ]);
  $app->redirect("/");
});

// Define routes
$app->get("/", function () use ($app, $language, $menu, $username, $cart) {
  // Sample log message
  $app->log->info("Slim-Skeleton '/' route");
  // Render index viewdd
  $res = ShopobjectsCtl::GetShopObjects(
    START_ID,
    $language,
    "price",
    "ASC",
    0,
    0,
    ["name", "img1", "price", "short_description"]
  );
  $app->render("index.html", [
    "res" => $res,
    "menu" => $menu,
    "username" => $username,
    "cart" => $cart,
    "language" => $language,
  ]);
});

$app->get("/content/:obj", function ($obj) use (
  $app,
  $request_uri,
  $language,
  $menu,
  $username,
  $cart
) {
  // Sample log message
  $app->log->info("Slim-Skeleton '/' route");
  // Render index viewdd
  $res = ShopObjectsCtl::SeoGetContentDetails($obj);
  $app->render("content.html", [
    "res" => $res,
    "menu" => $menu,
    "username" => $username,
    "cart" => $cart,
    "request_uri" => $request_uri,
    "language" => $language,
  ]);
});

$app->get("/category/:obj", function ($obj) use (
  $app,
  $request_uri,
  $language,
  $menu,
  $username,
  $cart
) {
  // Sample log message
  $app->log->info("Slim-Skeleton '/' route");
  // Render index viewdd
  if (is_numeric($obj)) {
    $res = ShopobjectsCtl::GetShopobjects(
      $obj,
      $language,
      "price",
      "ASC",
      0,
      0,
      ["name", "short_description", "price", "img1"]
    );
  } else {
    $res = ShopobjectsCtl::SeoGetShopobjects($obj, "price", "ASC", 0, 0, [
      "name",
      "short_description",
      "price",
      "img1",
    ]);
  }
  $app->render("show_category.html", [
    "res" => $res,
    "menu" => $menu,
    "username" => $username,
    "cart" => $cart,
    "request_uri" => $request_uri,
    "language" => $language,
  ]);
});

$app->get("/express-checkout", function () use (
  $app,
  $request_uri,
  $language,
  $menu,
  $username,
  $cart
) {
  // Sample log message
  $app->log->info("Slim-Skeleton '/' route");
  // Render index viewdd
  if ($username != "") {
  $res = UserCtl::GetUserData(SessionCtl::GetSession());
  } else {
    $res = [];
  }
  
  $payment_methods = PaymentCtl::GetPaymentMethods();
  $app->render("express.html", [
    "userdata" => $res,
    "menu" => $menu,
    "username" => $username,
    "cart" => $cart,
    "request_uri" => $request_uri,
    "language" => $language,
    "payment_methods" => $payment_methods
  ]);
});

$app->post("/add_to_cart", function () use (
  $app,
  $request_uri,
  $language,
  $menu,
  $username,
  $cart
) {
  // Sample log message
  $app->log->info("Slim-Skeleton '/' route");
  // Render index viewdd
  $id_product = $app->request->post("id_product");
  $pic = $app->request->post("pic");
  $quantity = $app->request->post("quantity");
  $res = CartCtl::Add(
    SessionCtl::GetSession(),
    $id_product,
    $quantity,
    "price",
    "name",
    "short_description",
    $language,
    "PRODUCT",
    0,
    [["lang" => $language, "name" => "pic", "value" => $pic]]
  );
  //$app->render('cart.html',array("res"=>$res,"menu"=>$menu,"username"=>$username,"cart"=>$res,"request_uri"=>$request_uri,"language"=>$language));
  //$app->render('index.html',array("res"=>$res));
  $app->redirect("/cart");
});

$app->post("/login", function () use (
  $app,
  $request_uri,
  $language,
  $menu,
  $username,
  $cart
) {
  $username = $app->request->post("username");
  $passwd = $app->request->post("password");
  $res = UserCtl::Login(SessionCtl::GetSession(), $username, $passwd);
  if ($res["status"] == "SUCCESS") {
    $username = $res["username"];
    setcookie("username", $username);
    $res = UserCtl::GetUserData(SessionCtl::GetSession());
    $profile = $app->request->post("profile");
    if ($profile != 1) {
      $app->render("userdata.html", [
        "userdata" => $res,
        "menu" => $menu,
        "username" => $username,
        "cart" => $cart,
        "request_uri" => $request_uri,
        "language" => $language,
      ]);
    } else {
      $res = UserCtl::GetUserOrders(SessionCtl::GetSession());
      $app->render("profile.html", [
        "res" => $res,
        "menu" => $menu,
        "username" => $username,
        "cart" => $cart,
        "request_uri" => $request_uri,
        "language" => $language,
      ]);
    }
  } else {
    $app->render("login.html", [
      "error" => 1,
      "res" => $res,
      "menu" => $menu,
      "username" => "",
      "cart" => $cart,
      "request_uri" => $request_uri,
      "language" => $language,
    ]);
  }
});

$app->post("/express-login", function () use (
  $app,
  $request_uri,
  $language,
  $menu,
  $username,
  $cart
) {
  $username = $app->request->post("username");
  $passwd = $app->request->post("password");
  $res = UserCtl::Login(SessionCtl::GetSession(), $username, $passwd);
  if ($res["status"] == "SUCCESS") {
    $username = $res["username"];
    setcookie("username", $username);
    $res = UserCtl::GetUserData(SessionCtl::GetSession());
    $profile = $app->request->post("profile");
    
    $app->render("express.html", [
      "userdata" => $res,
      "menu" => $menu,
      "username" => $username,
      "cart" => $cart,
      "request_uri" => $request_uri,
      "language" => $language,
    ]);
      
  } else {
    $app->render("express.html", [
      "error" => 'login',
      "res" => $res,
      "menu" => $menu,
      "username" => "",
      "cart" => $cart,
      "request_uri" => $request_uri,
      "language" => $language,
    ]);
  }
});

$app->post("/userdata", function () use (
  $app,
  $request_uri,
  $language,
  $menu,
  $username,
  $cart
) {
  $error = [];
  $error_count = 0;
  
  if ($app->request->post("diffaddress") == "diffadd") {
       $dsalutation=$app->request->post("dsalutation");
       $dfirstname=$app->request->post("dfirstname");
       $dlastname=$app->request->post("dlastname");
       $dcompany=$app->request->post("dcompanyname");
       $ddepartment=$app->request->post("ddepartment");
       $dstate=$app->request->post("dstate");
       $dcountry=$app->request->post("dcountry");
       $dstreet=$app->request->post("dstreet");
       $dnumber=$app->request->post("dnumber");
       $dzip=$app->request->post("dzip");
       $dcity=$app->request->post("dcity");
   } else {
       $dsalutation=$app->request->post("salutation");
       $dfirstname=$app->request->post("firstname");
       $dlastname=$app->request->post("lastname");
       $dcompany=$app->request->post("companyname");
       $ddepartment=$app->request->post("department");
       $dstate=$app->request->post("state");
       $dcountry=$app->request->post("country");
       $dstreet=$app->request->post("street");
       $dnumber=$app->request->post("number");
       $dzip=$app->request->post("zip");
       $dcity=$app->request->post("city");
   }
  
   $salutation=$app->request->post("salutation");
   $firstname=$app->request->post("firstname");
   $lastname=$app->request->post("lastname");
   $street=$app->request->post("street");
   $number=$app->request->post("number");
   $zip=$app->request->post("zip");
   $city=$app->request->post("city");
   $state=$app->request->post("state");
   $country=$app->request->post("country");
   $notes=$app->request->post("notes");
   $email=$app->request->post("email");
   $phone=$app->request->post("phone");
   $company=$app->request->post("companyname");
   $department=$app->request->post("department");
  
  
   if ($app->request->post("diffaddress") == "diffadd") {
       $userdata["dsalutation"]=$dsalutation;
       $userdata["dfirstname"]=$dfirstname;
       $userdata["dlastname"]=$dlastname;
       $userdata["dcompany"]=$dcompany;
       $userdata["ddepartment"]=$ddepartment;
       $userdata["dstreet"]=$dstreet;
       $userdata["dnumber"]=$dnumber;
       $userdata["dzip"]=$dzip;
       $userdata["dcity"]=$dcity;
       $userdata["dstate"]=$dstate;
       $userdata["dcountry"]=$dcountry;
   } else {
       $userdata["dsalutation"]=$salutation;
       $userdata["dfirstname"]=$firstname;
       $userdata["dlastname"]=$lastname;
       $userdata["dcompany"]=$company;
       $userdata["ddepartment"]=$department;
       $userdata["dstreet"]=$street;
       $userdata["dnumber"]=$number;
       $userdata["dzip"]=$zip;
       $userdata["dcity"]=$city;
       $userdata["dstate"]=$state;
       $userdata["dcountry"]=$country;
   }
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
   $userdata["phone"]=$phone;
  
   $args=array(
       "delivery_companyname"=>$userdata["dcompany"],
       "delivery_department"=>$userdata["ddepartment"],
       "delivery_salutation"=>$userdata["dsalutation"],
       "delivery_firstname"=>$userdata["dfirstname"],
       "delivery_lastname"=>$userdata["dlastname"],
       "delivery_street"=>$userdata["dstreet"],
       "delivery_number"=>$userdata["dnumber"],
       "delivery_zip"=>$userdata["dzip"],
       "delivery_state"=>$userdata["dstate"],
       "delivery_city"=>$userdata["dcity"],
       "delivery_country"=>$userdata["dcountry"],
       "invoice_companyname"=>$userdata["company"],
       "invoice_department"=>$userdata["department"],
       "invoice_salutation"=>$userdata["salutation"],
       "invoice_firstname"=>$userdata["firstname"],
       "invoice_lastname"=>$userdata["lastname"],
       "invoice_street"=>$userdata["street"],
       "invoice_number"=>$userdata["number"],
       "invoice_zip"=>$userdata["zip"],
       "invoice_state"=>$userdata["state"],
       "invoice_city"=>$userdata["city"],
       "invoice_country"=>$userdata["country"],
       "note"=>$userdata["notes"],
       "email"=>$userdata["email"]
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
    $app->render("userdata.html", [
      "userdata" => $userdata,
      "error" => $error,
      "error_count" => $error_count,
      "language" => $language,
    ]);
  } else {
    $args = [
      "companyname" => $userdata["company"],
      "department" => $userdata["department"],
      "salutation" => $userdata["salutation"],
      "firstname" => $userdata["firstname"],
      "lastname" => $userdata["lastname"],
      "street" => $userdata["street"],
      "number" => $userdata["number"],
      "zip" => $userdata["zip"],
      "state" => $userdata["state"],
      "city" => $userdata["city"],
      "country" => $userdata["country"],
      "email" => $userdata["email"],
    ];
    UserCtl::SetUserData(SessionCtl::GetSession(), $args);
    $payment_methods = PaymentCtl::GetPaymentMethods();
    $app->render("payment_methods.html", [
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

$app->post("/express-checkout", function () use (
  $app,
  $request_uri,
  $language,
  $menu,
  $username,
  $cart
) {
  $error = [];
  $error_count = 0;
  
  if ($app->request->post("diffaddress") == "diffadd") {
       $dsalutation=$app->request->post("dsalutation");
       $dfirstname=$app->request->post("dfirstname");
       $dlastname=$app->request->post("dlastname");
       $dcompany=$app->request->post("dcompanyname");
       $ddepartment=$app->request->post("ddepartment");
       $dstate=$app->request->post("dstate");
       $dcountry=$app->request->post("dcountry");
       $dstreet=$app->request->post("dstreet");
       $dnumber=$app->request->post("dnumber");
       $dzip=$app->request->post("dzip");
       $dcity=$app->request->post("dcity");
   } else {
       $dsalutation=$app->request->post("salutation");
       $dfirstname=$app->request->post("firstname");
       $dlastname=$app->request->post("lastname");
       $dcompany=$app->request->post("companyname");
       $ddepartment=$app->request->post("department");
       $dstate=$app->request->post("state");
       $dcountry=$app->request->post("country");
       $dstreet=$app->request->post("street");
       $dnumber=$app->request->post("number");
       $dzip=$app->request->post("zip");
       $dcity=$app->request->post("city");
   }
  
   $salutation=$app->request->post("salutation");
   $firstname=$app->request->post("firstname");
   $lastname=$app->request->post("lastname");
   $street=$app->request->post("street");
   $number=$app->request->post("number");
   $zip=$app->request->post("zip");
   $city=$app->request->post("city");
   $state=$app->request->post("state");
   $country=$app->request->post("country");
   $notes=$app->request->post("notes");
   $email=$app->request->post("email");
   $phone=$app->request->post("phone");
   $company=$app->request->post("companyname");
   $department=$app->request->post("department");
  
  
   if ($app->request->post("diffaddress") == "diffadd") {
       $userdata["dsalutation"]=$dsalutation;
       $userdata["dfirstname"]=$dfirstname;
       $userdata["dlastname"]=$dlastname;
       $userdata["dcompany"]=$dcompany;
       $userdata["ddepartment"]=$ddepartment;
       $userdata["dstreet"]=$dstreet;
       $userdata["dnumber"]=$dnumber;
       $userdata["dzip"]=$dzip;
       $userdata["dcity"]=$dcity;
       $userdata["dstate"]=$dstate;
       $userdata["dcountry"]=$dcountry;
   } else {
       $userdata["dsalutation"]=$salutation;
       $userdata["dfirstname"]=$firstname;
       $userdata["dlastname"]=$lastname;
       $userdata["dcompany"]=$company;
       $userdata["ddepartment"]=$department;
       $userdata["dstreet"]=$street;
       $userdata["dnumber"]=$number;
       $userdata["dzip"]=$zip;
       $userdata["dcity"]=$city;
       $userdata["dstate"]=$state;
       $userdata["dcountry"]=$country;
   }
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
   $userdata["phone"]=$phone;
  
   $args=array(
       "delivery_companyname"=>$userdata["dcompany"],
       "delivery_department"=>$userdata["ddepartment"],
       "delivery_salutation"=>$userdata["dsalutation"],
       "delivery_firstname"=>$userdata["dfirstname"],
       "delivery_lastname"=>$userdata["dlastname"],
       "delivery_street"=>$userdata["dstreet"],
       "delivery_number"=>$userdata["dnumber"],
       "delivery_zip"=>$userdata["dzip"],
       "delivery_state"=>$userdata["dstate"],
       "delivery_city"=>$userdata["dcity"],
       "delivery_country"=>$userdata["dcountry"],
       "invoice_companyname"=>$userdata["company"],
       "invoice_department"=>$userdata["department"],
       "invoice_salutation"=>$userdata["salutation"],
       "invoice_firstname"=>$userdata["firstname"],
       "invoice_lastname"=>$userdata["lastname"],
       "invoice_street"=>$userdata["street"],
       "invoice_number"=>$userdata["number"],
       "invoice_zip"=>$userdata["zip"],
       "invoice_state"=>$userdata["state"],
       "invoice_city"=>$userdata["city"],
       "invoice_country"=>$userdata["country"],
       "note"=>$userdata["notes"],
       "email"=>$userdata["email"]
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
    $app->render("userdata.html", [
      "userdata" => $userdata,
      "error" => $error,
      "error_count" => $error_count,
      "language" => $language,
    ]);
  } else {
    $args = [
      "companyname" => $userdata["company"],
      "department" => $userdata["department"],
      "salutation" => $userdata["salutation"],
      "firstname" => $userdata["firstname"],
      "lastname" => $userdata["lastname"],
      "street" => $userdata["street"],
      "number" => $userdata["number"],
      "zip" => $userdata["zip"],
      "state" => $userdata["state"],
      "city" => $userdata["city"],
      "country" => $userdata["country"],
      "email" => $userdata["email"],
    ];
    UserCtl::SetUserData(SessionCtl::GetSession(), $args);
    
    $id_payment = $app->request->post("id_payment");
    $order = OrderCtl::SetOrderDetails(SessionCtl::GetSession(), [
      "id_payment_method" => $id_payment,
      "id_delivery_method" => 1,
    ]);
    $app->render("order_summary.html", [
      "order" => $order,
      "cart" => $cart,
      "language" => $language,
      "username" => $username,
    ]);
    
    
  }
});

$app->post("/profile-userdata", function () use (
  $app,
  $request_uri,
  $language,
  $menu,
  $username,
  $cart
) {
  $error = [];
  $error_count = 0;
  $salutation = $app->request->post("salutation");
  $firstname = $app->request->post("firstname");
  $lastname = $app->request->post("lastname");
  $company = $app->request->post("company");
  $department = $app->request->post("department");
  $street = $app->request->post("street");
  $number = $app->request->post("number");
  $zip = $app->request->post("zip");
  $city = $app->request->post("city");
  $state = $app->request->post("state");
  $country = $app->request->post("country");
  $notes = $app->request->post("notes");
  $email = $app->request->post("email");
  $userdata["salutation"] = $salutation;
  $userdata["firstname"] = $firstname;
  $userdata["lastname"] = $lastname;
  $userdata["companyname"] = $company;
  $userdata["department"] = $department;
  $userdata["street"] = $street;
  $userdata["number"] = $number;
  $userdata["zip"] = $zip;
  $userdata["city"] = $city;
  $userdata["state"] = $state;
  $userdata["country"] = $country;
  $userdata["notes"] = $notes;
  $userdata["email"] = $email;

  $args = [
    "delivery_companyname" => $userdata["company"],
    "delivery_department" => $userdata["department"],
    "delivery_salutation" => $userdata["salutation"],
    "delivery_firstname" => $userdata["firstname"],
    "delivery_lastname" => $userdata["lastname"],
    "delivery_street" => $userdata["street"],
    "delivery_number" => $userdata["number"],
    "delivery_zip" => $userdata["zip"],
    "delivery_state" => $userdata["state"],
    "delivery_city" => $userdata["city"],
    "delivery_country" => $userdata["country"],
    "invoice_companyname" => $userdata["company"],
    "invoice_department" => $userdata["department"],
    "invoice_salutation" => $userdata["salutation"],
    "invoice_firstname" => $userdata["firstname"],
    "invoice_lastname" => $userdata["lastname"],
    "invoice_street" => $userdata["street"],
    "invoice_number" => $userdata["number"],
    "invoice_zip" => $userdata["zip"],
    "invoice_state" => $userdata["state"],
    "invoice_city" => $userdata["city"],
    "invoice_country" => $userdata["country"],
    "note" => $userdata["note"],
    "email" => $userdata["email"],
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
    $app->render("profile_userdata.html", [
      "userdata" => $userdata,
      "error" => $error,
      "error_count" => $error_count,
    ]);
  } else {
    $args = [
      "companyname" => $userdata["companyname"],
      "department" => $userdata["department"],
      "salutation" => $userdata["salutation"],
      "firstname" => $userdata["firstname"],
      "lastname" => $userdata["lastname"],
      "street" => $userdata["street"],
      "number" => $userdata["number"],
      "zip" => $userdata["zip"],
      "state" => $userdata["state"],
      "city" => $userdata["city"],
      "country" => $userdata["country"],
      "email" => $userdata["email"],
    ];
    $res = UserCtl::SetUserData(SessionCtl::GetSession(), $args);

    //$payment_methods=PaymentCtl::GetPaymentMethods();
    $app->render("profile_userdata.html", [
      "userdata" => $userdata,
      "request_uri" => $request_uri,
      "language" => $language,
      "menu" => $menu,
      "username" => $username,
      "cart" => $cart,
      "language" => $language,
    ]);
  }
});

$app->post("/profile-password", function () use (
  $app,
  $request_uri,
  $language,
  $menu,
  $username,
  $cart
) {
  $error_count = 0;
  $passwd1 = $app->request->post("passwd1");
  $passwd2 = $app->request->post("passwd2");
  $passwd3 = $app->request->post("passwd3");
  $res = UserCtl::SetUserPassword(
    SessionCtl::GetSession(),
    $passwd1,
    $passwd2,
    $passwd3
  );
  if ($res["status"] != "SUCCESS") {
    $error = $res["status"];
    $error_count++;
  }
  $app->render("profile_password.html", [
    "request_uri" => $request_uri,
    "language" => $language,
    "menu" => $menu,
    "username" => $username,
    "cart" => $cart,
    "error_count" => $error_count,
    "error" => $error,
    "already_sent" => 1,
    "language" => $language,
  ]);
});

$app->post("/checkout", function () use (
  $app,
  $request_uri,
  $language,
  $menu,
  $username,
  $cart
) {
  $cart = CartCtl::Get(SessionCtl::GetSession());
  $token = $app->request->post("token");
  $card_number = $app->request->post("card_number");
  $cvc = $app->request->post("cvc");
  $exp_month = $app->request->post("exp_month");
  $exp_year = $app->request->post("exp_year");
  $delivery_costs = [["Delivery", $cart["delivery_costs"]["sum"], 0.19]];
  OrderCtl::AddDeliveryCosts(SessionCtl::GetSession(), $delivery_costs);
  $res = OrderCtl::Checkout(SessionCtl::GetSession());
  if ($res["status"] == "success") {
    /*
     * Send the order - email
     */
    $order = OrderCtl::GetOrderDetails(SessionCtl::GetSession());
    $subject = "Bestellbestätigung";
    $invoice_link =
      "https://" .
      $_SERVER["HTTP_HOST"] .
      "/get-invoice/" .
      $res["id_order"] .
      "/" .
      base64_encode(crypt($res["id_order"], TOKEN));
    $msg = OrderCtl::GetOrderConfirmation($res["id_order"], [
      "invoice_link" => $invoice_link,
    ]);
    send_html_mail(
      $order["email"],
      utf8_decode($subject),
      utf8_decode($msg),
      ORDER_SENDER
    );
    send_html_mail(
      ORDER_SENDER,
      utf8_decode($subject),
      utf8_decode($msg),
      ORDER_SENDER
    );
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
  } elseif (
    $res["status"] == "error" and
    $res["message"] == "SHOPOBJECT_NOT_AVAILABLE"
  ) {
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
  $app->render("checkout.html", [
    "token" => $token,
    "redirect" => $redirect,
    "res" => $res,
    "request_uri" => $request_uri,
    "language" => $language,
    "menu" => $menu,
    "username" => $username,
    "cart" => $cart,
  ]);
});

$app->post("/register", function () use (
  $app,
  $request_uri,
  $language,
  $menu,
  $username,
  $cart
) {
  $user = $app->request->post("user");
  $email = $app->request->post("email");
  $passwd1 = $app->request->post("password");
  $passwd2 = $app->request->post("password2");
  $already_sent = $app->request->post("already_sent");
  $error = 0;
  if ($already_sent == 1) {
    $res = UserCtl::RegisterUser(
      [
        "username" => $user,
        "email" => $email,
        "passwd1" => $passwd1,
        "passwd2" => $passwd2,
      ],
      $language
    );
    if ($res["status"] == "SUCCESS") {
      UserCtl::VerifyUser($res["id_user"], $res["session_id"]);
      $app->render("login.html", [
        "error" => 0,
        "user" => $user,
        "email" => $email,
        "cart" => $cart,
        "language" => $language,
        "menu" => $menu,
      ]);
    } else {
      $error++;
      $error_msg = $res["status"];
      $app->render("register.html", [
        "error_msg" => $error_msg,
        "error" => 1,
        "user" => $user,
        "email" => $email,
        "cart" => $cart,
        "language" => $language,
        "menu" => $menu,
      ]);
    }
  }
});

$app->post("/express-register", function () use (
  $app,
  $request_uri,
  $language,
  $menu,
  $username,
  $cart
) {
  $user = $app->request->post("user");
  $email = $app->request->post("email");
  $passwd1 = $app->request->post("password");
  $passwd2 = $app->request->post("password2");
  $already_sent = $app->request->post("already_sent");
  $error = 0;
  if ($already_sent == 1) {
    $res = UserCtl::RegisterUser(
      [
        "username" => $user,
        "email" => $email,
        "passwd1" => $passwd1,
        "passwd2" => $passwd2,
      ],
      $language
    );
    if ($res["status"] == "SUCCESS") {
      UserCtl::VerifyUser($res["id_user"], $res["session_id"]);
      $app->render("express.html", [
        "error" => 0,
        "user" => $user,
        "email" => $email,
        "cart" => $cart,
        "language" => $language,
        "menu" => $menu,
      ]);
    } else {
      $error++;
      $error_msg = $res["status"];
      $app->render("express.html", [
        "error_msg" => $error_msg,
        "error" => 1,
        "user" => $user,
        "email" => $email,
        "cart" => $cart,
        "language" => $language,
        "menu" => $menu,
      ]);
    }
  }
});

$app->get("/search", function () use (
  $app,
  $request_uri,
  $language,
  $menu,
  $username,
  $cart
) {
  $app->log->info("Slim-Skeleton '/' route");
  // Render index viewdd
  $searchstring = $app->request->get("searchstring");
  $app->request->get("page") != ""
    ? ($page = $app->request->get("page"))
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
  $res = ShopobjectsCtl::SearchProducts(
    $constraint,
    $left_limit,
    $right_limit,
    [],
    "ASC",
    $language,
    ["name", "img1", "price", "short_description"]
  );
  $res["pages"] = ceil($res["count"] / CATEGORY_PRODUCT_COUNT);
  if (count($res["products"]) == 0) {
    $res = 0;
  }
  $app->render("show_category.html", [
    "res" => $res,
    "menu" => $menu,
    "username" => $username,
    "cart" => $cart,
    "request_uri" => $request_uri,
    "language" => $language,
  ]);
});

$app->post("/order_summary", function () use (
  $app,
  $request_uri,
  $language,
  $menu,
  $username,
  $cart
) {
  $token = $app->request->post("token");
  $id_payment = $app->request->post("id_payment");
  $card_number = $app->request->post("card_number");
  $exp_month = $app->request->post("exp_month");
  $exp_year = $app->request->post("exp_year");
  $cvc = $app->request->post("cvc");
  $order = OrderCtl::SetOrderDetails(SessionCtl::GetSession(), [
    "id_payment_method" => $id_payment,
    "id_delivery_method" => 1,
  ]);
  $app->render("order_summary.html", [
    "order" => $order,
    "token" => $token,
    "cart" => $cart,
    "language" => $language,
    "card_number" => $card_number,
    "exp_month" => $exp_month,
    "exp_year" => $exp_year,
    "cvc" => $cvc,
  ]);
});

$app->get("/:obj", function ($obj) use (
  $app,
  $request_uri,
  $language,
  $menu,
  $username,
  $cart
) {
  // Sample log message
  $app->log->info("Slim-Skeleton '/' route");

  if ($obj == "cart") {
    $res = CartCtl::Get(SessionCtl::GetSession());
    if (empty($res["contents"])) {
      $res = 0;
    }
    $app->render("cart.html", [
      "res" => $res,
      "menu" => $menu,
      "username" => $username,
      "cart" => $cart,
      "request_uri" => $request_uri,
      "language" => $language,
    ]);
  } elseif ($obj == "checkout") {
    $error = $app->request->get("error");
    $app->render("checkout.html", [
      "res" => $res,
      "menu" => $menu,
      "username" => $username,
      "cart" => $res,
      "request_uri" => $request_uri,
      "language" => $language,
      "error" => $error,
    ]);
  } elseif ($obj == "del_from_cart") {
    $res = CartCtl::Del(SessionCtl::GetSession(), $app->request->get("id"));
    if (count($res["contents"]) == 0) {
      $res = 0;
    }
    //$app->render('cart.html',array("res"=>$res,"menu"=>$menu,"username"=>$username,"cart"=>$res,"request_uri"=>$request_uri,"language"=>$language));
    $app->redirect("/cart");
  } elseif ($obj == "your-data") {
    if ($username != "") {
      $res = UserCtl::GetUserData(SessionCtl::GetSession());
      $app->render("userdata.html", [
        "userdata" => $res,
        "menu" => $menu,
        "username" => $username,
        "cart" => $cart,
        "request_uri" => $request_uri,
        "language" => $language,
      ]);
    } else {
      $app->render("your_data.html", [
        "menu" => $menu,
        "username" => $username,
        "cart" => $cart,
        "request_uri" => $request_uri,
        "language" => $language,
      ]);
    }
  } elseif ($obj == "login") {
    $profile = $app->request->get("profile");
    $app->render("login.html", [
      "menu" => $menu,
      "username" => $username,
      "cart" => $cart,
      "request_uri" => $request_uri,
      "profile" => $profile,
      "language" => $language,
    ]);
  } elseif ($obj == "logout") {
    UserCtl::Logout(SessionCtl::GetSession());
    setcookie("username", "");
    $app->render("logout.html", [
      "menu" => $menu,
      "username" => "",
      "cart" => $cart,
      "request_uri" => $request_uri,
      "language" => $language,
    ]);
  } elseif ($obj == "profile") {
    if ($username == "") {
      $app->render("login.html", [
        "res" => $res,
        "menu" => $menu,
        "username" => $username,
        "cart" => $cart,
        "request_uri" => $request_uri,
        "language" => $language,
      ]);
    } else {
      $res = UserCtl::GetUserOrders(SessionCtl::GetSession());
      $app->render("profile.html", [
        "res" => $res,
        "menu" => $menu,
        "username" => $username,
        "cart" => $cart,
        "request_uri" => $request_uri,
        "language" => $language,
      ]);
    }
  } elseif ($obj == "register") {
    $app->render("register.html", [
      "menu" => $menu,
      "username" => $username,
      "cart" => $cart,
      "request_uri" => $request_uri,
      "language" => $language,
    ]);
  } elseif ($obj == "userdata") {
    //$order_data=OrderCtl::GetOrderDetails(SessionCtl::GetSession());
    $app->render("userdata.html", [
      "menu" => $menu,
      "cart" => $cart,
      "username" => $username,
      "language" => $language,
    ]);
  } elseif ($obj == "order_summary") {
    $token = $app->request->get("token");
    $id_payment = $app->request->get("id_payment");
    $order = OrderCtl::SetOrderDetails(SessionCtl::GetSession(), [
      "id_payment_method" => $id_payment,
      "id_delivery_method" => 1,
    ]);
    $app->render("order_summary.html", [
      "order" => $order,
      "token" => $token,
      "cart" => $cart,
      "language" => $language,
      "username" => $username,
    ]);
  } elseif ($obj == "profile-userdata") {
    if ($username == "") {
      $app->render("login.html", [
        "res" => $res,
        "menu" => $menu,
        "username" => $username,
        "cart" => $cart,
        "request_uri" => $request_uri,
        "language" => $language,
      ]);
    } else {
      $res = UserCtl::GetUserData(SessionCtl::GetSession());
      $app->render("profile_userdata.html", [
        "userdata" => $res,
        "menu" => $menu,
        "username" => $username,
        "cart" => $cart,
        "request_uri" => $request_uri,
        "language" => $language,
      ]);
    }
  } elseif ($obj == "profile-password") {
    if ($username == "") {
      $app->render("login.html", [
        "res" => $res,
        "menu" => $menu,
        "username" => $username,
        "cart" => $cart,
        "request_uri" => $request_uri,
        "language" => $language,
      ]);
    } else {
      $app->render("profile_password.html", [
        "menu" => $menu,
        "username" => $username,
        "cart" => $cart,
        "request_uri" => $request_uri,
        "language" => $language,
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
        if (
          $res["attributes"]["color"]["value"] !=
          $variation["attributes"]["color"]["value"]
        ) {
          $colors[$variation["attributes"]["color"]["value"]] =
            $variation["id"];
        }
      }
      asort($colors);
      $res["colors"] = $colors;
    }
    if (isset($res["attributes"]["size"]["value"]) && $res["attributes"]["size"]["value"] != "") {
      $sizes = [];
      $sizes[$res["attributes"]["size"]["value"]] = $res["id"];
      foreach ($res["variations"] as $variation) {
        if (
          $variation["attributes"]["color"]["value"] ==
          $res["attributes"]["color"]["value"]
        ) {
          if (
            $res["attributes"]["size"]["value"] !=
            $variation["attributes"]["size"]["value"]
          ) {
            $sizes[$variation["attributes"]["size"]["value"]] =
              $variation["id"];
          }
        }
      }
      ksort($sizes);
      $res["sizes"] = $sizes;
    }
    $tags = explode(",", $res["attributes"]["tags"]["value"]);
    $res["attributes"]["tags"]["arr"] = $tags;
    $app->render("show_product.html", [
      "res" => $res,
      "menu" => $menu,
      "username" => $username,
      "cart" => $cart,
      "request_uri" => $request_uri,
      "language" => $language,
    ]);
  }
});

// Run app
$app->run();
