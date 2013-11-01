# Simple PHP cURL wrapper class

```php
<?php
$curl = new anlutro\cURL\cURL;

// the raw body from the response is returned
$html = $curl->get('http://www.google.com');

// parameters: url, query string, post parameters. delete() has the same syntax
$data = $curl->post('http://api.myservice.com', ['api_key' => 'my_api_key'], ['post' => 'data']);

// add a header before sending a request
$curl->addHeader('Authorization: Basic QWxhZGRpbjpvcGVuIHNlc2FtZQ==');

// get headers from the response
$allHeaders = $curl->getHeaders();
$header = $curl->getHeaders('Accept-Charset');
```

Comes with a Laravel service provider and facade. Add `anlutro\cURL\Laravel\cURLServiceProvider` to the array of providers in `app/config/app.php`, and (optionally) add `'cURL' => 'anlutro\cURL\Laravel\cURL'` to the array of aliases in the same file.
