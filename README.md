Gelform CMS - A single file, single class CMS for MVC frameworks
======================================================

Created by Corey Maass at Gelform Inc

[![More info and demo](http://gelform.com/gelformcms)](http://gelform.com/gelformcms)

## Description

Why does the world need another CMS? When working with most MVC frameworks, it's a huge pain to wedge another cms into my existing views. With this class, you define 3 constants, add one folder and you're done. Create includes, and put them in your views wherever you like.

## How to set it up

1. Create a route in your application that can accept GET and POST requests. 

For example, in the Slim Framework, I added this route:
    $app->map('/cms/', array(new Controller_Cms(), 'index'))
    ->via('GET', 'POST');

Or in Zend, add a controller called CmsController and add an action called indexAction();

2. Add an assets folder accessible from the web and make it writeable. So  if your webroot is /public, you might add a folder called /cms (so your path would look like /public/cms). Then "chmod 777 cms" so it's writeable. 

3. Back in your controller action, define 3 constants:
    // set this to be any string, or pull it from a config
    define('GELFORMCMS_PASSWORD', 'password'); 

    // set this to the path of the assets folder you created in step 2. 
    define('GELFORMCMS_PATH', APPLICATION_DIR . 'public_html/cms');

    // Redundant, I know, but set this to the absolute path to the 
    // same assets folder.
    define('GELFORMCMS_URI', '/cms');

4. Include the GelformCMS class:
    require APPLICATION_DIR . 'model/gelformcms.php';

5. That's it! Visit the route you created, and you shuld be asked to sign in.

## How to use it

1. Sign in using the password you set in the constant GELFORMCMS_PASSWORD
2. A "section" is just an html blob. Click the button to "create a new section"
3. Give it a name, and add HTML to your hearts content.
4. Use the "images" button to upload images, or select images you've uploaded, previously. They will be uploaded to a "img"
folder in the assets folder your created. 
5. Save it. 
6. Now at the bottom, below the HTML form, you'll see a link for the "PHP include statement". Copy this, and put it in your view scripts wherever you want the HTML to render.

## Technical stuff

When you run it the first time, it will add 3 new folders in the folder you created. If you get an error, make sure the folder
is writeable.

The CMS uses jquery, TinyMCE and Twitter Bootstrap, loaded from CDNs. So you'll need an internet connection for presentation,
image upload and some behavior. The core of the app should work without it, however. And it looks pretty good on mobile!

## To do

* option to import CSS into TinyMCE
* limit number of revisions (delete after a certain amount)
* multiple users, user management
* put sections in buckets, collections

## License

The GelformCMS is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
