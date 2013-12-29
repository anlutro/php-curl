# Simple PHP cURL wrapper class

## Installation

https://packagist.org/packages/anlutro/curl

`composer require anlutro/curl` - pick the latest version number from Packagist or the GitHub tag list.

## Usage

```php
<?php
$curl = new anlutro\cURL\cURL;

// the raw body from the response is returned
$html = $curl->get('http://www.google.com')->body;

// easily build an url with a query string
$url = $curl->buildUrl('http://www.google.com', ['s' => 'curl']);
$html = $curl->get($url)->body;

// post() takes an array of POST data
$url = $curl->buildUrl('http://api.myservice.com', ['api_key' => 'my_api_key']);
$data = $curl->post($url, ['post' => 'data'])->body;

// add a header before sending a request
$curl->addHeader('Authorization: Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==');

// get headers from the response
$allHeaders = $curl->getHeaders();
$header = $curl->getHeaders('Accept-Charset');
```

## Laravel Service Provider
cURL comes with a Laravel service provider and facade.

Add `anlutro\cURL\Laravel\cURLServiceProvider` to the array of providers in `app/config/app.php`.

And (optionally) add `'cURL' => 'anlutro\cURL\Laravel\cURL'` to the array of aliases in the same file.
