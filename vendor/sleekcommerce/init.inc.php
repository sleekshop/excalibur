<?php

/*
 * Init - file
 *
 * @ Kaveh Raji <kr@sleekcommerce.com>
 */
 define("ROOTPATH",$_SERVER["DOCUMENT_ROOT"] . "/");
 define("PROJECTPATH", ROOTPATH . "../");
 /*
  * Now including some libaries needed
  */
  include(PROJECTPATH . "vendor/sleekcommerce/conf.inc.php");
  include(PROJECTPATH . "vendor/sleekcommerce/sleekshop_request.inc.php");
  include(PROJECTPATH . "vendor/sleekcommerce/cart.inc.php");
  include(PROJECTPATH . "vendor/sleekcommerce/shopobjects.inc.php");
  include(PROJECTPATH . "vendor/sleekcommerce/categories.inc.php");
  include(PROJECTPATH . "vendor/sleekcommerce/session.inc.php");
  include(PROJECTPATH . "vendor/sleekcommerce/user.inc.php");
  include(PROJECTPATH . "vendor/sleekcommerce/order.inc.php");
  include(PROJECTPATH . "vendor/sleekcommerce/payment.inc.php");

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
$menu=CategoriesCtl::GetMenu();

/*
* Getting the username
*/
$username=$_COOKIE["username"];

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
