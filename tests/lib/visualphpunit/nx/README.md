# NX

## Table of Contents

- [Philosophy](#philosophy)
- [Statistics](#statistics)
- [Getting Started](#getting-started)
  * [Installing](#installing)
  * [Configuring Your Hosts File](#confhosts)
  * [Configuring Your Web Server](#confserver) ([nginx](#nginx), or [Apache](#apache))
  * [Restart Your Web Server](#restartserver)
- [Tutorial](#tutorial)
- [API](#api)
  * [Request](#request)
  * [Response](#response)
  * [Dispatcher](#dispatcher)
  * [Router](#router)
- [NXtra](#nxtra)
- [Contributing](#contributing)
- [Credits](#credits)
- [License](#license)

## <a name='philosophy'></a>Philosophy

> Il semble que la perfection soit atteinte non quand il n'y a plus rien à ajouter, mais quand il n'y a plus rien à retrancher.

> -- Antoine de Saint-Exupéry

[PHP](http://codeigniter.com/) [suffers](http://framework.zend.com/) [from](http://symfony.com/) [a](http://www.yiiframework.com/) [plethora](http://cakephp.org/) [of](http://lithify.me/) [frameworks](http://bcosca.github.com/fatfree/).  So why another one?

Rather than offer a "full stack" solution, NX focuses on only that which is _absolutely essential_.  All web applications need to handle incoming requests and serve well-formed responses.  To that end, NX provides a simple, lightweight solution.  Because it leaves out what are essentially _application-specific components_ (ORMs, MV* patterns, form helpers, template engines, etc.), it's also blazingly fast.  More importantly, it offers a rock-solid foundation that _never gets in your way_.  Best of all, the few components NX does implement are completely modular and easily replaceable.

## <a name='statistics'></a>Statistics

```
Lines of Code (LOC):                                624
  Cyclomatic Complexity / Lines of Code:           0.14
Comment Lines of Code (CLOC):                       256
Non-Comment Lines of Code (NCLOC):                  368

Namespaces:                                           1
Interfaces:                                           0
Classes:                                              4
  Abstract:                                           0 (0.00%)
  Concrete:                                           4 (100.00%)
  Average Class Length (NCLOC):                      89
Methods:                                             11
  Scope:
    Non-Static:                                      11 (100.00%)
    Static:                                           0 (0.00%)
  Visibility:
    Public:                                           5 (45.45%)
    Non-Public:                                       6 (54.55%)
  Average Method Length (NCLOC):                     32
  Cyclomatic Complexity / Number of Methods:       5.73

Anonymous Functions:                                  0
Functions:                                            0

Constants:                                            0
  Global constants:                                   0
  Class constants:                                    0
```

## <a name='getting-started'></a>Getting Started

### <a name='installing'></a>Installing

```bash
# create a directory for your project
mkdir project && cd project
# install NX and a basic application template
curl -L https://raw.github.com/NSinopoli/nxtra/master/resource/script/install-nx.sh | sh
```

### <a name='confhosts'></a>Configuring Your Hosts File

Choose a server name for your project, and edit your /etc/hosts file accordingly:

```
127.0.0.1    project
```

### <a name='confserver'></a>Configuring Your Web Server

#### <a name='nginx'></a>nginx

Place this code block within the `http {}` block in your nginx.conf file:

```nginx

    server {
	    server_name     project;
	    root            /srv/http/project/app/public;
	    index           index.php;

	    access_log      /var/log/nginx/project_access.log;
	    error_log       /var/log/nginx/project_error.log;

	    location / {
            try_files $uri /index.php;
	    }

	    location ~ \.php$ {
            fastcgi_pass    unix:/var/run/php-fpm/php-fpm.sock;
            fastcgi_index   index.php;
            fastcgi_param   SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include         fastcgi_params;
	    }
    }
```

Note that you will have to change the `server_name` to the name you used above in your hosts file. You will also have to adjust the directories according to where you checked out the code. In this configuration, /srv/http/project/ is the project root. The public-facing part of your application, on the other hand, is located in app/public within the project root (so in this example, it's /srv/http/project/app/public).

What's happening here, exactly? The `try_files` directive will check to see if the resouce at $uri exists in the filesystem (in this example, within /srv/http/project/app/public). If it does, that file is served by nginx. If it doesn't, it's then routed to /index.php, whereupon the framework takes responsibility for handling the request. The `try_files` directive is great for serving static content - there's no need to pass requests for js, css, or image files through the framework.

#### <a name='apache'></a>Apache

In your httpd.conf file, locate your `DocumentRoot`. It will look something like this:

```apache
DocumentRoot "/srv/http"
```

Now find the `<Directory>` tag that corresponds to your `DocumentRoot`. It will look like this:

```apache
<Directory "/srv/http">
```

Within that tag, change the `AllowOverride` setting:

```apache
AllowOverride All
```

Ensure that your `DirectoryIndex` setting contains index.php:

```apache
DirectoryIndex index.php
```

Now uncomment the following line:

```apache
Include conf/extra/httpd-vhosts.conf
```

Edit your conf/extra/httpd-vhosts.conf file and add the following code block:

```apache
<VirtualHost *:80>
    DocumentRoot "/srv/http/project/app/public"
    ServerName project
    ErrorLog "/var/log/httpd/project_error.log"
    CustomLog "/var/log/httpd/project_access.log" common
    <Directory /srv/http/project/app/public>
        Options +FollowSymLinks
    </Directory>
</VirtualHost>
```

Note that you will have to change the `ServerName` to the name you used above in your hosts file. You will also have to adjust the directories ( in `DocumentRoot`, as well as the `<Directory>` tag) according to where you checked out the code. In this configuration, /srv/http/project/ is the project root. The public-facing part of your application, on the other hand, is located in app/public within the project root (so in this example, it's /srv/http/project/app/public).

Within your project's public root, create an .htaccess file (in our case, it'd be located at /srv/http/project/app/public/.htaccess) and paste the following block inside:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !favicon.ico$
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

### <a name='restartserver'></a>Restart Your Web Server

Restart your web server, and then point your browser at the server name you chose above. If you see the familiar "Hello, World!", then you've configured everything correctly!


## <a name='tutorial'></a>Tutorial

TODO


## <a name='api'></a>API


### <a name='request'></a>Request

The Request class is responsible for organizing all data pertaining to an incoming HTTP request.

#### Public Properties

```php
<?php

$request = new \nx\core\Request();

// Get all of the POST/PUT/DELETE data as an array
$data = $request->data;

// Get all of the GET data as an array
$query = $request->query;

// Get the parameters collected from the request uri
$params = $request->params;

// Get the request uri
$url = $request->url;

?>
```

#### Request Parameters

```php
<?php

// Given the following route
$routes = array(
    array('delete', '/entry/[i:id]', function($request) {
        // do something...
    })
);

// If a DELETE request comes in on /entry/42, the $request object supplied to the
// callback function will have access to the 'id' parameter (defined in the uri)
// through $request->params['id']

$routes = array(
    array('delete', '/entry/[i:id]', function($request) {
        return "Are you sure you want to delete entry {$request->params['id']}?";
    })
);

?>
```

#### Environment Variables

All variables collected from PHP's superglobals $_SERVER and $_ENV are available as case-insensitive public properties.

```php
<?php

// Get PHP_SELF
$self = $request->php_self;
// Get the HTTP_USER_AGENT
$user_agent = $request->http_user_agent;

?>
```

See PHP's documentation on [$_SERVER](http://www.php.net/manual/en/reserved.variables.server.php) and [$_ENV](http://www.php.net/manual/en/reserved.variables.environment.php) to see which data is available.

#### Checking Request Characteristics
Sometimes it's useful to know what sort of request you're dealing with:

```php
<?php

// Is this a DELETE request?
if ( $request->is('delete') ) {
    // do something
}

// Are we dealing with a mobile user?
if ( $request->is('mobile') ) {
    // do something
}

?>
```

The full list of request characteristics is as follows:

```
'ajax' - xhr
'delete' - DELETE REQUEST_METHOD
'flash' - "Shockwave Flash" HTTP_USER_AGENT
'get' - GET REQUEST_METHOD
'head' - HEAD REQUEST_METHOD
'mobile' - any one of the following HTTP_USER_AGENTS:
           'Android', 'AvantGo', 'Blackberry', 'DoCoMo', 'iPod',
           'iPhone', 'J2ME', 'NetFront', 'Nokia', 'MIDP', 'Opera Mini',
           'PalmOS', 'PalmSource', 'Plucker', 'portalmmm',
           'ReqwirelessWeb', 'SonyEricsson', 'Symbian', 'UP.Browser',
           'Windows CE', 'Xiino'
'options' - OPTIONS REQUEST_METHOD
'post' - POST REQUEST_METHOD
'put' - PUT REQUEST_METHOD
'ssl' - HTTPS
```


#### Staying RESTful

Because HTML forms don't support PUT or DELETE, you can use a hidden field in your form (named "_method") to manually override the request method.

```html
<form method='post' action='/'>
    <input type='hidden' name='_method' value='put' />
    <input type='text' name='name' />
    <input type='submit' />
</form>
```

You can then match the request as normal:

```php
<?php

$routes = array(
    array('put', '/', function($request) {
        // This will output the value supplied to the
        // 'name' textbox in the form above
        return var_dump($request->data['name']);
    })
);

?>
```


### <a name='response'></a>Response

The Response class is used to output an HTTP response.  It consists of three parts: a status code, HTTP headers, and a body.

#### Rendering a Response

```php
<?php

// Render just the body
$result = "Hello, World!";

$response = new \nx\core\Response();
$response->render($result);

?>
```

```php
<?php

// Exercise more fine-grained control
$result = array(
   'body'    => "Goodbye, World!",
   'status'  => 410,
   'headers' => array('Last-Modified: Tue, 24 Apr 2012 12:45:26 GMT')
);
$response = new \nx\core\Response();
$response->render($result);

?>
```

##### Status Code

The status code should be an integer associated with the HTTP status code.  Here's the list of supported status codes:

```
100 - Continue
101 - Switching Protocols
200 - OK
201 - Created
202 - Accepted
203 - Non-Authoritative Information
204 - No Content
205 - Reset Content
206 - Partial Content
300 - Multiple Choices
301 - Moved Permanently
302 - Found
303 - See Other
304 - Not Modified
305 - Use Proxy
307 - Temporary Redirect
400 - Bad Request
401 - Unauthorized
402 - Payment Required
403 - Forbidden
404 - Not Found
405 - Method Not Allowed
406 - Not Acceptable
407 - Proxy Authentication Required
408 - Request Time-out
409 - Conflict
410 - Gone
411 - Length Required
412 - Precondition Failed
413 - Request Entity Too Large
414 - Request-URI Too Large
415 - Unsupported Media Type
416 - Requested range not satisfiable
417 - Expectation Failed
500 - Internal Server Error
501 - Not Implemented
502 - Bad Gateway
503 - Service Unavailable
504 - Gateway Time-out
```

If a status code is not provided, the response will default to using 200 OK.

##### Headers

Headers should be an array of well-formed HTTP headers.  See [Wikipedia's entry on HTTP header responses](https://en.wikipedia.org/wiki/HTTP_header#Responses) for more information.  If no headers are provided, the response will default to using a "Content-Type: text/html; charset=utf-8" header.

##### Body

The body contains the message data.  The body is 'chunked' (see [this article](http://wonko.com/post/seeing_poor_performance_using_phps_echo_statement_heres_why) and [this one as well](http://weblog.rubyonrails.org/2011/4/18/why-http-streaming/) for more information) according to the buffer_size set in the constructor (defaults to 8192 bytes).

```php
<?php

// Decrease the chunk size
$buffer_size = 4096;
$response = new \nx\core\Response(compact('buffer_size'));

?>
```


### <a name='dispatcher'></a>Dispatcher

The dispatcher is responsible for connecting requests with responses.

#### Handling an Incoming Request
The dispatcher passes an incoming [Request](#request) (along with predefined routes) to the [Router](#router), whereupon an array is returned containing the parameters collected from the request uri as well as the callback function provided in the matched route.

#### Rendering a Response
The acquired callback function is called by the dispatcher, whose return value is then passed to the [Response](#response) class for rendering.


### <a name='router'></a>Router

Every incoming request has an associated uri and method.  The router's responsibility is to match the request uri and method to a predefined route.

#### Defining Routes

##### Construction

Routes are constructed using the following format:

```php
<?php

$routes = array(
    array(
        mixed $request_method, string $request_uri, callable $callback
    ),
    ...
);

?>
```

##### Parameters

```
$request_method can be a string (one of 'GET', 'POST', 'PUT', or 'DELETE'), or an array containing a combination of request
methods.  Note that these are case-insensitive.
```
```
$request_uri is a regex-like pattern, providing support for optional match types.

Valid match types are as follows:
[i] - integer
[a] - alphanumeric
[h] - hexadecimal
[*] - anything

Match types can be combined with parameter names, which will be captured:
[i:id] - will match an integer, storing it within the returned 'params' array under the 'id' key
[a:name] - will match an alphanumeric value, storing it within the returned 'params' array under the 'name' key

Here are some examples to help illustrate:
/post/[i:id] - will match on /post/32 (with the returned 'params' array containing an 'id' key with a value of 32), but will
not match on /post/today
/find/[h:serial] - will match on /find/ae32 (with the returned 'params' array containing a 'serial' key with a value
of 'ae32'), but will not match on /find/john
```
```
$callback is a valid callback function, which can take a Request object as its first parameter.  The return value should be
a proper Response (i.e., either a string [the body of the response] or an array [containing any combination of body, headers,
and status]).
```

##### Notes

Routes are processed in the order in which they are defined.  Because only one route is used as the match, you'll want to define the routes so that they follow each other with decreased specificity (i.e., most specific at the top, most general at the bottom).

##### Examples

```php
<?php

$routes = array(

    // Matches on a GET request to /
    array('get', '/', function($request) {
        return 'Hello, World!';
    }),

    // Matches on a GET or POST request to /login
    array(array('get', 'post'), '/login', function($request) {
        return 'This is the login page.  Check back later!';
    }),

    // Matches on a DELETE request to /entry/{id}
    array('delete', '/entry/[i:id]', function($request) {
        return "Are you sure you want to delete entry {$request->params['id']}?";
    }),

    // Matches all other GET requests - we can use this to capture 404s
    array('get', '*', function($request) {
        return array(
            'status' => 404,
            'body'   => '<h1>404 Not Found</h1>'
        );
    })
);

?>
```

##### Connecting Application Routes to NX

Routes are not directly passed to the Router.  Instead, they are passed to the [Dispatcher](#dispatcher) (along with a [Request](#request) object):

```php
<?php

$request = new \nx\core\Request();
$routes = array(
    array('get', '/', function($request) {
        return 'Hello, World!';
    })
);
$dispatcher = new \nx\core\Dispatcher();
$dispatcher->handle($request, $routes);

?>
```


## <a name='nxtra'></a>NXtra

Several common application libraries were built while developing and using NX.  Rather than include them in the core framework, they have been placed into a separate repository.  Check out [NXtra](http://git.io/nxtra) for more information.


## <a name='contributing'></a>Contributing

Please submit any feedback you may have on the project's [issue tracker](https://github.com/NSinopoli/nx/issues).


## <a name='credits'></a>Credits

Huge thanks to [Chris O'Hara](https://github.com/chriso), whose [router](https://github.com/chriso/klein.php) was adapted for use in NX.

Special thanks to [Andrew Ettinger](https://github.com/sillydeveloper), whose [ploof](https://github.com/sillydeveloper/ploof) framework provided the initial inspiration.


## <a name='license'></a>License

NX is licensed under [The BSD License](http://opensource.org/licenses/BSD-3-Clause).

```
Copyright (c) 2011-2012, Nick Sinopoli <nsinopoli@gmail.com>.
All rights reserved.

Redistribution and use in source and binary forms, with or without
modification, are permitted provided that the following conditions
are met:

 * Redistributions of source code must retain the above copyright
   notice, this list of conditions and the following disclaimer.

 * Redistributions in binary form must reproduce the above copyright
   notice, this list of conditions and the following disclaimer in
   the documentation and/or other materials provided with the
   distribution.

 * Neither the name of Nick Sinopoli nor the names of his
   contributors may be used to endorse or promote products derived
   from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
"AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
POSSIBILITY OF SUCH DAMAGE.
```
