# php-curl

[![Build Status](https://travis-ci.org/anlutro/php-curl.png?branch=master)](https://travis-ci.org/anlutro/php-curl)
[![Latest Stable Version](https://poser.pugx.org/anlutro/curl/v/stable.svg)](https://github.com/anlutro/php-curl/releases)
[![Latest Unstable Version](https://poser.pugx.org/anlutro/curl/v/unstable.svg)](https://github.com/anlutro/php-curl/branches/active)
[![License](https://poser.pugx.org/anlutro/curl/license.svg)](http://opensource.org/licenses/MIT)


The smallest possible OOP wrapper for PHP's curl capabilities.

Note that this is **not** meant as a high-level abstraction. You should still know how "pure PHP" curl works, you need to know the curl options to set, and you need to know some HTTP basics.

If you're looking for a more user-friendly abstraction, check out [Guzzle](http://guzzle.readthedocs.org/en/latest/).

## Installation

	$ composer require anlutro/curl

## Usage

```php
$curl = new anlutro\cURL\cURL;

$response = $curl->get('http://www.google.com');

// easily build an url with a query string
$url = $curl->buildUrl('http://www.google.com', ['s' => 'curl']);
$response = $curl->get($url);

// the post, put and patch methods takes an array of POST data
$response = $curl->post($url, ['api_key' => 'my_key', 'post' => 'data']);

// add "json" to the start of the method to convert the data to a JSON string
// and send the header "Content-Type: application/json"
$response = $curl->jsonPost($url, ['post' => 'data']);

// if you don't want any conversion to be done to the provided data, for example
// if you want to post an XML string, add "raw" to the start of the method
$response = $curl->rawPost($url, '<?xml version...');

// raw request are also the easiest way to upload files
$file = curl_file_create('/path/to/file');
$response = $curl->rawPost($url, ['file' => $file]);

// a response object is returned
var_dump($response->statusCode); // response status code integer (for example, 200)
var_dump($response->statusText); // full response status (for example, '200 OK')
echo $response->body;
var_dump($response->headers); // array of headers
var_dump($response->info); // array of curl info
```

If you need to send headers or set [cURL options](http://php.net/manual/en/function.curl-setopt.php) you can manipulate a request object directly. `send()` finalizes the request and returns the result.

```php
// newRequest, newJsonRequest and newRawRequest returns a Request object
$request = $curl->newRequest('post', $url, ['foo' => 'bar'])
	->setHeader('Accept-Charset', 'utf-8')
	->setHeader('Accept-Language', 'en-US')
	->setOption(CURLOPT_CAINFO, '/path/to/cert')
	->setOption(CURLOPT_FOLLOWLOCATION, true);
$response = $request->send();
```

You can also use `setHeaders(array $headers)` and `setOptions(array $options)` to replace all the existing options.

Note that some curl options are reset when you call `send()`. Look at the source code of the method `cURL::prepareMethod` for a full overview of which options are overwritten.

HTTP basic authentication:

```php
$request = $curl->newRequest('post', $url, ['foo' => 'bar'])
	->setUser($username)->setPass($password);
```

### Laravel

The package comes with a facade you can use if you prefer the static method calls over dependency injection.

You do **not** need to add a service provider.

Optionally, add `'cURL' => 'anlutro\cURL\Laravel\cURL'` to the array of aliases in `config/app.php`.

Replace `$curl->` with `cURL::` in the examples above.

## Contact

Open an issue on GitHub if you have any problems or suggestions.

## License

The contents of this repository is released under the [MIT license](http://opensource.org/licenses/MIT).
