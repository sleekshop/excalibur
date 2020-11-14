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
  include(PROJECTPATH . "vendor/sleekcommerce/sleekshop-phpsdk-json/conf.inc.php");
  include(PROJECTPATH . "vendor/sleekcommerce/sleekshop-phpsdk-json/sleekshop_request.inc.php");
  include(PROJECTPATH . "vendor/sleekcommerce/sleekshop-phpsdk-json/cart.inc.php");
  include(PROJECTPATH . "vendor/sleekcommerce/sleekshop-phpsdk-json/shopobjects.inc.php");
  include(PROJECTPATH . "vendor/sleekcommerce/sleekshop-phpsdk-json/categories.inc.php");
  include(PROJECTPATH . "vendor/sleekcommerce/sleekshop-phpsdk-json/session.inc.php");
  include(PROJECTPATH . "vendor/sleekcommerce/sleekshop-phpsdk-json/user.inc.php");
  include(PROJECTPATH . "vendor/sleekcommerce/sleekshop-phpsdk-json/order.inc.php");
  include(PROJECTPATH . "vendor/sleekcommerce/sleekshop-phpsdk-json/payment.inc.php");
?>
