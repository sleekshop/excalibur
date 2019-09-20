/*
 * This file is a short setup - instruction for the excalibur framework. A shop - framework built to interact with sleekshop.
 *
 * (c) 2017 - Kaveh Raji <kr@sleekshop.io>
 *
 */




For the template to work properly you need to change some settings.

1. Change the configuration - settings in the /vendor/sleekcommerce/conf.inc.php - file if neccessary.

2. Change the SERVER setting in the /vendor/sleekcommerce/sleekshop_request.inc.php - file and put in your backend - Api node.

3. Further you need to create some classes in your backend:

3.1 product / type:PRODUCT
with the following fields, whereas name/type is used. You are free in choosing the label in the corresponding language.
- name/char
- short_description/txt
- description/txt
- price/float
- img1/img
- img2/img
- img3/img
- img4/img
- related_items/products
- vendor/char
- type/char
- tags/txt

3.2 colorprod / type:PRODUCT
with the following fields, whereas name/type is used. You are free in choosing the label in the corresponding language.
- name/char
- short_description/txt
- description/txt
- price/float
- img1/img
- img2/img
- color/char
- img3/img
- img4/img
- related_items/products
- featured_products/products
- vendor/char
- type/char
- tags/txt

3.3 sizeprod / type:PRODUCT
with the following fields, whereas name/type is used. You are free in choosing the label in the corresponding language.
- name/char
- short_description/txt
- description/txt
- price/float
- img1/img
- img2/img
- img3/img
- img4/img
- related_items/products
- vendor/char
- type/char
- tags/txt
- size/char

3.4 teaser_pic / type:CONTENT
with the following fields, whereas name/type is used. You are free in choosing the label in the corresponding language.
- img/img
- headline/char
- text/txt

3.5 content / type:CONTENT
with the following fields, whereas name/type is used. You are free in choosing the label in the corresponding language.
- headline/char
- content/txt
- img/img

4. You also need some categories in the backend

4.1 Start
This category is the major category an inherits products and content for the start - page.

4.2 Categories
This is the category which contains all categories in the menu

4.3
You have to change the category - ids of the start - and - categories  in the /vendor/conf.inc.php
