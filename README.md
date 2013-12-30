# Simple PHP cURL wrapper class

## Installation

https://packagist.org/packages/anlutro/curl

`composer require anlutro/curl` - pick the latest version number from Packagist or the GitHub tag list.

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

// add a header before sending a request
$curl->addHeader('Authorization: Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==');

// a response object is returned
var_dump($response->code); // response status code (for example, '200 OK')
echo $response->body;
var_dump($response->headers); // array of headers
var_dump($response->info); // array of curl info
```

## Laravel Service Provider
cURL comes with a Laravel service provider and facade.

Add `anlutro\cURL\Laravel\cURLServiceProvider` to the array of providers in `app/config/app.php`.

Optionally, add `'cURL' => 'anlutro\cURL\Laravel\cURL'` to the array of aliases in the same file.

Replace `$curl->` with `cURL::` in the examples above.
