# Simple PHP cURL wrapper class

## Installation

https://packagist.org/packages/anlutro/curl

`composer require anlutro/curl` - pick the latest version number from Packagist or the GitHub tag list.

Note that while on version 0.x I will not guarantee backwards compatibility between minor versions. Specify the version in your composer.json if you don't want/need to keep up with new features, or use a more mature library.

## Usage

```php
<?php
$curl = new anlutro\cURL\cURL;

$response = $curl->get('http://www.google.com');

// easily build an url with a query string
$url = $curl->buildUrl('http://www.google.com', ['s' => 'curl']);
$response = $curl->get($url);

// post() takes an array of POST data
$url = $curl->buildUrl('http://api.myservice.com', ['api_key' => 'my_api_key']);
$response = $curl->post($url, ['post' => 'data']);

// add "json" to the start of the method to post as JSON
$response = $curl->jsonPut($url, ['post' => 'data']);

// add "raw" to the start of the method to post raw data
$response = $curl->rawPost($url, '<?xml version...');

// a response object is returned
var_dump($response->code); // response status code (for example, '200 OK')
echo $response->body;
var_dump($response->headers); // array of headers
var_dump($response->info); // array of curl info
?>
```

If you need to send headers or set cURL options you can manipulate a request object instead. `send()` finalizes the request and returns the result.

```php
<?php
// newRequest or newJsonRequest returns a Request object
$result = $curl->newRequest('post', $url, ['foo' => 'bar'])
	->setHeader('content-type', 'application/json')
	->setHeader('Accept', 'json')
	->setOptions([CURLOPT_VERBOSE => true])
	->send();
?>
```

## Laravel Service Provider
cURL comes with a Laravel service provider and facade.

Add `anlutro\cURL\Laravel\cURLServiceProvider` to the array of providers in `app/config/app.php`.

Optionally, add `'cURL' => 'anlutro\cURL\Laravel\cURL'` to the array of aliases in the same file.

Replace `$curl->` with `cURL::` in the examples above.
