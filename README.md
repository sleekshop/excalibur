# README #

### What is this repository for? ###

* Strong framework for building e-commerce solutions with sleekshop based on:
- bootstrap
- slim-framework
- jquery
- twig as template - engine
- based on PHP
* Version 3.0.0

## packages

- slim/twig-view
- slim/psr7
- slim/slim:"4.*"

### How do I get set up? ###

* Change some configuration - files
* Set up the needed classes in the backend
* All described in the file /setup/readme.txt

### How does the Express Checkout work?

For the express checkout to work, you'll need to configure the mollie payment gateway in sleekshop.

After this payment method has been enabled, you can start using the express checkout.

To disable the new express checkout, you can simply comment out the link in cart.html line 67.

With the latest version of Excalibur, we have achieved numerous new changes and benefits.

Starting of with the latest version support of php and all vendor packages.

## setup

Setting up Excalibur is easy. Let's start in a few steps.

1. create Sleekshop instance (copy your license data into a local text file)
2. paste your license data into the following file: `/vendor/sleekcommerce/sleekshop-phpsdk-json/sleekshop_request.inc.php`
3. route the traffic to the index.php in the public directory

> Please note that only the public directory should act as the root directory. Also the .htaccess file must be in the webspace so Apache will do the correct traffic redirections.

*Installation instructions for Apache and Nginx will follow.*

## changes v3 vs v2

In the new version of Excalibur (v3), we build on the latest packages from Slim and Twig.

Nevertheless, we keep the old file structure and functionality in detail, so that we can provide you with the easiest transition.

The biggest changes to version 2 are as follows:

- templates now have the .twig file extension
- routes now look slightly different and have $request, $response and $args variables
- the response is now passed to the route controller and generated in the function. 
- translation language files now have to be added to the `index.php`

*the following snippet is one route example:*

```php
$app->get("/blog", function ($request, $response, $args) use ($app, $language, $menu, $username, $cart) {

  		$res = ShopobjectsCtl::GetShopObjects( 5, $language, "prio", "DESC", 0, 0);

  		$view = Twig::fromRequest($request);

  		return $view->render($response, 'blog.twig', [
	  		"res" => 		$res,
	  		"menu" => 		$menu,
	  		"username" => 	$username,
	  		"cart" => 		$cart,
			"language" => 	$language
  		]);
})->setName('blog');
```

Very big advancements are also the new `TwigExtensions`

TwigExtension provides these functions to your Twig templates:

- `url_for()` - returns the URL for a given route. e.g.: /hello/world
- `full_url_for()` - returns the URL for a given route. e.g.: http://www.example.com/hello/world
- `is_current_url()` - returns true is the provided route name and parameters are valid for the current path.
- `current_url()` - returns the current path, with or without the query string.
- `get_uri()` - returns the UriInterface object from the incoming ServerRequestInterface object
- `base_path()` - returns the base path.
> You can `use url_for` to generate complete URLs to any Slim application named route and use `is_current_url` to determine if you need to mark a link as active as shown in this example Twig template:

```html
{% extends "layout.html" %}

{% block body %}
<h1>User List</h1>
<ul>
    <li><a href="{{ url_for('profile', { 'name': 'josh' }) }}" {% if is_current_url('profile', { 'name': 'josh' }) %}class="active"{% endif %}>Josh</a></li>
    <li><a href="{{ url_for('profile', { 'name': 'andrew' }) }}">Andrew</a></li>
</ul>
{% endblock %}
```