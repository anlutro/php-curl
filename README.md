# php-curl

The smallest possible OOP wrapper for PHP's curl capabilities.

## Installation

	$ composer require anlutro/curl

## Usage

```php
<?php
$curl = new anlutro\cURL\cURL;

$response = $curl->get('http://www.google.com');

// easily build an url with a query string
$url = $curl->buildUrl('http://www.google.com', ['s' => 'curl']);
$response = $curl->get($url);

// post() takes an array of POST data
$response = $curl->post($url, ['api_key' => 'my_key', 'post' => 'data']);

// add "json" to the start of the method to post as JSON
$response = $curl->jsonPut($url, ['post' => 'data']);

// add "raw" to the start of the method to post raw data
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

If you need to send headers or set cURL options you can manipulate a request object directly. `send()` finalizes the request and returns the result.

```php
<?php
// newRequest, newJsonRequest and newRawRequest returns a Request object
$response = $curl->newRequest('post', $url, ['foo' => 'bar'])
	->setHeader('content-type', 'application/json')
	->setHeader('Accept', 'json')
	->setOption(CURLOPT_VERBOSE, true)
	->setOption(CURLOPT_CAINFO, '/path/to/cert')
	->send();
```

You can also use `setHeaders(array $headers)` and `setOptions(array $options)` to replace all the existing options.

Note that some curl options are reset when you call `send()`. Look at the source code of the method `cURL::prepareMethod` for a full overview of which options are overwritten.

### Laravel

The package comes with a facade you can use if you prefer the static method calls.

Optionally, add `'cURL' => 'anlutro\cURL\Laravel\cURL'` to the array of aliases in `config/app.php`.

Replace `$curl->` with `cURL::` in the examples above.

## Contact

Open an issue on GitHub if you have any problems or suggestions.

## License

The contents of this repository is released under the [MIT license](http://opensource.org/licenses/MIT).
