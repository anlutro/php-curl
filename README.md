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
