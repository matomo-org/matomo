# Slim Framework for PHP 5

Slim is a micro framework for PHP 5 that helps you quickly write simple yet powerful RESTful web applications and APIs. Slim is easy to use for both beginners and professionals. Slim favors cleanliness over terseness and common cases over edge cases. Its interface is simple, intuitive, and extensively documented — both online and in the code itself. Thank you for choosing Slim for your next project. I think you're going to love it.

## Features

* Clean and simple [DSL](http://en.wikipedia.org/wiki/Domain-specific_language) for writing powerful web applications
* HTTP routing
    * Supports all standard and custom HTTP request methods
    * Route parameters and conditions
    * Route redirects
    * Route passing
    * Route halting
    * Route middleware
    * Named routes and `urlFor()` helper
* Easy configuration
* Easy templating with custom Views (e.g. Twig, Mustache, Smarty)
* Flash messaging
* Signed cookies with AES-256 encryption
* HTTP caching (ETag and Last-Modified)
* Logging
* Error handling
    * Custom Not Found handler
    * Custom Error handler
    * Debugging
* Built upon the Rack protocol
* Extensible middleware and hook architecture
* Supports PHP >= 5.2.0

## "Hello World" application (PHP >= 5.3)

The Slim Framework for PHP 5 supports anonymous functions. This is the preferred method to define Slim application routes. This example assumes you have setup URL rewriting with your web server (see below).

```php
<?php
require 'Slim/Slim.php';
$app = new Slim();
$app->get('/hello/:name', function ($name) {
    echo "Hello, $name!";
});
$app->run();
```

## "Hello World" application (PHP < 5.3)

If you are running PHP < 5.3, the second argument to the application's `get()` instance method is the name of a callable function instead of an anonymous function. This example assumes you have setup URL rewriting with your web server (see below).

```php
<?php
require 'Slim/Slim.php';
$app = new Slim();
$app->get('/hello/:name', 'hello');
function hello($name) {
    echo "Hello, $name!";
}
$app->run();
```

## Get Started

### Install Slim

Download the Slim Framework for PHP 5 and unzip the downloaded file into your virtual host's public directory. Slim will work in a sub-directory, too.

### Setup your webserver

#### Apache

Ensure the `.htaccess` and `index.php` files are in the same public-accessible directory. The `.htaccess` file should contain this code:

    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [QSA,L]

#### Nginx

Your nginx configuration file should contain this code (along with other settings you may need) in your `location` block:

    if (!-f $request_filename) {
        rewrite ^ /index.php last;
    }

This assumes that Slim's `index.php` is in the root folder of your project (www root).

#### lighttpd ####

Your lighttpd configuration file should contain this code (along with other settings you may need). This code requires lighttpd >= 1.4.24.

    url.rewrite-if-not-file = ("^" => "/index.php")

This assumes that Slim's `index.php` is in the root folder of your project (www root).

### Build Your Application

Your Slim application will be defined in `index.php`. First, `require` the Slim Framework:

```php
require 'Slim/Slim.php';
```

Next, initialize the Slim application:

```php
$app = new Slim();
```

Next, define your application's routes:

```php
$app->get('/hello/:name', function ($name) {
    echo "Hello $name";
});
```

Finally, run your Slim application:

```php
    $app->run();
```

For more information about building an application with the Slim Framework, refer to the [official documentation](http://github.com/codeguy/Slim/wiki/Slim-Framework-Documentation).

## Documentation 

* [Stable Branch Documentation](http://www.slimframework.com/documentation/stable)
* [Development Branch Documentation](http://www.slimframework.com/documentation/develop)

## Community

### Forum and Knowledgebase

Visit Slim's official forum and knowledge base at <http://help.slimframework.com> where you can find announcements, chat with fellow Slim users, ask questions, help others, or show off your cool Slim Framework apps.

### Twitter

Follow [@slimphp](http://www.twitter.com/slimphp) on Twitter to receive the very latest news and updates about the framework.

### IRC

You can find me, Josh Lockhart, hanging out in the ##slim chat room during the day. Feel free to say hi, ask questions, or just hang out. If you're on a Mac, check out Colloquy; if you're on a PC, check out mIRC; if you're on Linux, I'm sure you already know what you're doing.

## Resources

Additional resources (ie. custom Views and plugins) are available online in a separate repository.

<https://github.com/codeguy/Slim-Extras>

Here are more links that may also be useful.

* Road Map:       <http://github.com/codeguy/Slim/wiki/Road-Map>
* Source Code:    <http://github.com/codeguy/Slim/>

## About the Author

Slim is created and maintained by Josh Lockhart, a web developer by day at [New Media Campaigns](http://www.newmediacampaigns.com), and a [hacker by night](http://github.com/codeguy).

Slim is in active development, and test coverage is continually improving.

## Open Source License

Slim is released under the MIT public license.

<http://www.slimframework.com/license>
