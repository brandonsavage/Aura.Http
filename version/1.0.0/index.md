---
title: Aura for PHP -- Build and send HTTP requests and responses
layout: default
---

Aura HTTP
=========

The Aura HTTP package provides objects to build and send HTTP requests and
responses, including `multipart/form-data` requests, with streaming of file
resources when using the `curl` adapter.

This package is compliant with [PSR-0][], [PSR-1][], and [PSR-2][]. If you
notice compliance oversights, please send a patch via pull request.

[PSR-0]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
[PSR-1]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-1-basic-coding-standard.md
[PSR-2]: https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md

Getting Started
===============

Instantiation
-------------

The easiest way to get started is to use the `scripts/instance.php` script to
instantiate an HTTP `Manager` object.

{% highlight php %}
<?php
$http = include '/path/to/Aura.Http/scripts/instance.php';
{% endhighlight %}

You can then create new `Request` and `Response` objects, and send them via
the `Manager`.

{% highlight php %}
<?php
// send a response
$response = $http->newResponse();
$response->headers->set('Content-Type', 'text/plain');
$response->setContent('Hello World!');
$http->send($response);

// make a request and get a response stack
$request = $http->newRequest();
$request->setUrl('http://example.com');
$stack = $http->send($request);
echo $stack[0]->content;
{% endhighlight %}

HTTP Responses
==============

Instantiation
-------------

Use the `Manager` to create a new HTTP response.

{% highlight php %}
<?php
$response = $http->newResponse();
{% endhighlight %}

Setting And Getting Content
---------------------------

To set the content of the `Response`, use `setContent()`.

{% highlight php %}
<?php
$html = '<html>'
      . '<head><title>Test</title></head>'
      . '<body>Hello World!</body>'
      . '</html>';
$response->setContent($html);
{% endhighlight %}

Instead of a string, the content may be a file resource; when the response is
sent, the file will be streamed out via `fread()`.

To get the content, use `getContent()` or access the `$content` property.


Setting And Getting Headers
---------------------------

To set headers, access the `$headers` property, and use its `set()` method.

{% highlight php %}
<?php
$response->headers->set('Header-Label', 'header value');
{% endhighlight %}

You can also set all the headers at once by passing an array of key-value
pairs where the key is the header label and the value is one or more header
values.

{% highlight php %}
<?php
$response->headers->setAll([
    'Header-One' => 'header one value',
    'Header-Two' => [
        'header two value A',
        'header two value B',
        'header two value C',
    ],
]);
{% endhighlight %}

> N.b.: Header labels are sanitized and normalized, so if you enter a label
> `header_foo` it will be converted to `Header-Foo`.

To get the headers, use `getHeaders()` or access the `$headers` property and
use the `get()` method.

{% highlight php %}
<?php
// set a header
$reponse->headers->set('Content-Type', 'text/plain');

// get a header
$header = $reponse->headers->get('Content-Type');

// $header->label is 'Content-Type'
// $header->value is 'text/plain'
{% endhighlight %}

Setting and Getting Cookies
---------------------------

To set cookies, access the `$cookies` property . Pass the cookie name and an
array of information about the cookie (including its value).

{% highlight php %}
<?php
$response->cookies->set('cookie_name', [
    'value'    => 'cookie value', // cookie value
    'expire'   => time() + 3600,  // expiration time in unix epoch seconds
    'path'     => '/path',        // server path for the cookie
    'domain'   => 'example.com',  // domain for the cookie
    'secure'   => false,          // send by ssl only?
    'httponly' => true,           // send by http/https only?
]);
{% endhighlight %}

The information array keys mimic the [setcookies()](http://php.net/setcookies)
parameter names. You only need to provide the parts of the array that you
need; the remainder will be filled in with `null` defaults for you.

You can also set all the cookies at once by passing an array of key-value
pairs, where the key is the cookie name and the value is a cookie information
array.

{% highlight php %}
<?php
$response->cookies->setAll([
    'cookie_foo' => [
        'value' => 'value for cookie foo',
    ],
    'cookie_bar' => [
        'value' => 'value for cookie bar',
    ],
]);
{% endhighlight %}

To get cookies, use `getCookies()` or access the `$cookies` property and use
the `get()` method.

{% highlight php %}
<?php
$cookie = $response->cookies->get('cookie_foo');
{% endhighlight %}


Setting and Getting the Status
------------------------------

To set the HTTP response status, use `setStatusCode()` and `setStatusText()`.
The `setStatusCode()` method automatically sets the text for known codes.

{% highlight php %}
<?php
// automatically sets the status text to 'Not Modified'
$response->setStatusCode(304);

// change the status text to something else
$response->setStatusText('Same As It Ever Was');
{% endhighlight %}

> N.b.: By default, a new `Response` starts with a status of `'200 OK'`.

To get the response status, use `getStatusCode()` and `getStatusText()`.


Sending the Response
--------------------

Once you have set the content, headers, cookies, and status, you can send the
response using the HTTP `Manager` object.

{% highlight php %}
<?php
$http->send($response);
{% endhighlight %}

This will send all the headers using [header()](http://php.net/header) and all
the cookies using [setcookie()](http://php.net/setcookie).

If the content is a string, it will be `echo`-ed; if the content is a file
resource, it will be streamed out with `fread()`.

> N.b.: You can only send the `Response` once. If you try to send it again,
> or if you try to send another response of any sort with headers on it, you
> will get a `HeadersSent` exception.


HTTP Requests
=============

Instantiation
-------------

Use the `Manager` to create a new HTTP request.

{% highlight php %}
<?php
$request = $http->newRequest();
{% endhighlight %}

Setting and Getting Headers and Cookies
---------------------------------------

You can set and get headers and cookies just as with a `Response` object,
described above.


Setting and Getting Content
---------------------------

You can set and get content just as with a `Response` object, described above.

> N.b.: Content will be sent only if the request method is `POST` or `PUT`.

If the `Request` content is a string, it will be sent as-is.

If the `Request` content is a file resource, it will be read from disk and
sent.

If the content is an array, it will be converted to `x-www-form-urlencoded` or
`multipart/form-data`. The array may specify files to be uploaded by prefixing
the array value with `@`.

> **WARNING:** Be sure to sanitize user data to make sure only values intended
> as file uploads begin with `@`.

{% highlight php %}
<?php
// set content directly as a string
$request->setContent(json_encode([
    'foo' => 'bar',
    'baz' => 'dib',
]));

// set content to a file to be be streamed out
$fh = fopen('/path/to/file');
$request->setContent($fh);

// set content to an array of data, which will be converted
// to x-www-form-urlencoded or multipart/form-data.
$request->setContent([
    'foo' => 'bar',
    'baz' => 'dib',
]);

// set content to an array of data with files to be uploaded
// (note the use of '@' to indicate a file).
$request->setContent([
    'foo' => 'bar',
    'baz' => 'dib',
    'zim' => '@/path/to/file'
]);
{% endhighlight %}


Setting URL and Method
----------------------

To set the URL and method, do the following:

{% highlight php %}
<?php
$request->setUrl('http://example.com');
$request->setMethod(Request::METHOD_POST);
{% endhighlight %}

(By default, all requests use a `Request::METHOD_GET` method to begin with.)


Setting Authentication
----------------------

To set authentication credentials, pick the authentication type, then set
a username and password.

{% highlight php %}
<?php
$request->setAuth(Request::AUTH_BASIC);
$request->setUsername('username');
$request->setPassword('password');
{% endhighlight %}

Available authentication types are `Request::AUTH_BASIC` and
`Request::AUTH_DIGEST`.


Sending the Request
-------------------

You can send the request via the `Manager` object; it returns a `ResponseStack`.

{% highlight php %}
<?php
$stack = $http->send($request);
// $stack[0]->headers contains the headers of the last response
// $stack[0]->content contains the content of the last response
{% endhighlight %}

The `$stack` is an `Aura\Http\Message\Response\Stack` containing all the
responses, including redirects. The stack order is last in first out. Each
item in the stack is an `Aura\Http\Message\Response` object.


Further Examples
----------------

Making a GET request to the Github API to list Aura's repositories:

{% highlight php %}
<?php
$request->setUrl('https://api.github.com/orgs/auraphp/repos');
$stack = $http->send($request);
$repos = json_decode($stack[0]->content);
foreach ($repos as $repo) {
    echo $repo->name . PHP_EOL;
}
{% endhighlight %}

Making a custom POST request:

{% highlight php %}
<?php
$request->setUrl('http://example.com/submit.php');
$request->setMethod(Request::METHOD_POST);
$request->setContent(json_encode(['hello' => 'world']));
$request->headers->set('Content-Type', 'application/json');
$stack = $http->send($request);
{% endhighlight %}

Saving the response content to a file:

{% highlight php %}
<?php
$fp = fopen('/path/to/download.ext', 'wb+');
$request->setUrl('http://example.com/download.ext');
$request->setSaveToStream($fp);
$stack = $http->send($request);
// $stack[0]->content will be a file stream
{% endhighlight %}


HTTP Transport and Adapters
===========================

The HTTP `Manager` uses a `Transport` object to send requests.  You can
specify various options for the transport.

{% highlight php %}
// use a cookie jar for all requests
$http->transport->options->setCookieJar('/path/to/cookie.jar');

// the maximum number of request redirects
$http->transport->options->setMaxRedirects(10);

// the request timeout in seconds
$http->transport->options->setTimeout(10);

// the proxy host, port, username, and password
$http->transport->options->setProxy('proxy.example.com');
$http->transport->options->setProxyPort('12345');
$http->transport->options->setProxyUsername('username');
$http->transport->options->setProxyPassword('password');

// ssl options
$http->transport->options->setSslCafile('/path/to/cafile');
$http->transport->options->setSslCapath('capath');
$http->transport->options->setSslLocalCert('/path/to/local.crt');
$http->transport->options->setSslPassphrase('passphrase');
$http->transport->options->setSslVerifyPeer(true);
{% endhighlight %}

The transport uses an `Adapter` to handle the actual sending of requests.
There are two adapters available:

- `Aura\Http\Request\Adapter\Curl`, which is used automatically when the
  `curl` extension is loaded.  This adapter will stream file resources
  directly to and from disk, without loading the entire file into memory.

- `Aura\Http\Request\Adapter\Stream`, which is the fallback if `curl` is not
  loaded. This adapter is not suitable for sending or receiving large files.
  Each file will loaded into memory. This is a limitation in PHP HTTP streams.