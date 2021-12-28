<?php

/*
 * Init - file
 *
 * @ Kaveh Raji <kr@sleekcommerce.com>
 */
 define("ROOTPATH", "");
 define("PROJECTPATH", ROOTPATH . "../");
 /*
  * Now including some libaries needed
  */
  include(PROJECTPATH . "vendor/sleekcommerce/conf.inc.php");
  include(PROJECTPATH . "vendor/sleekcommerce/sleekshop-phpsdk-json/sleekshop_request.inc.php");
  include(PROJECTPATH . "vendor/sleekcommerce/sleekshop-phpsdk-json/cart.inc.php");
  include(PROJECTPATH . "vendor/sleekcommerce/sleekshop-phpsdk-json/shopobjects.inc.php");
  include(PROJECTPATH . "vendor/sleekcommerce/sleekshop-phpsdk-json/categories.inc.php");
  include(PROJECTPATH . "vendor/sleekcommerce/sleekshop-phpsdk-json/session.inc.php");
  include(PROJECTPATH . "vendor/sleekcommerce/sleekshop-phpsdk-json/user.inc.php");
  include(PROJECTPATH . "vendor/sleekcommerce/sleekshop-phpsdk-json/order.inc.php");
  include(PROJECTPATH . "vendor/sleekcommerce/sleekshop-phpsdk-json/payment.inc.php");
  include(PROJECTPATH . "vendor/sleekcommerce/mailing.inc.php");

/*
* Setting the language
*/
if(!isset($_COOKIE[TOKEN."_lang"]))
 {
   setcookie(TOKEN."_lang",DEFAULT_LANGUAGE,time()+3600);
   $language=DEFAULT_LANGUAGE;
 }
 else {
   $language=$_COOKIE[TOKEN.'_lang'];
 }

 /*
 * Getting the menu
 */
 //echo $language;
$menu=CategoriesCtl::GetMenu($language);

/*
* Getting the username
*/
isset($_COOKIE["username"]) ? $username=$_COOKIE["username"] : $username="";

/*
* The requested uri
*/
$request_uri="http://".$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

/*
* Getting the cart
*/
$cart=CartCtl::Get(SessionCtl::GetSession());

putenv('LC_MESSAGES='.$language);
setlocale(LC_MESSAGES, $language);
// Specify the location of the translation tables
bindtextdomain($language, PROJECTPATH.'/var/languages');
bind_textdomain_codeset($language, 'UTF-8');
// Choose domain
textdomain($language);


?>
