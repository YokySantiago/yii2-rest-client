Rest Client
===========
This one is a Extension with curl to consume Rest APIs

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yokysantiago/yii2-rest-client "*"
```

or add

```
"yokysantiago/yii2-rest-client": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
/**
 * Inicialización de clase
 * 
 * @param $strURL Url a la cual se consultará el servicio web
 * @param $boolVerificarHostSSL verificación SSL en el Host
 * @param $boolVerificarPeerSSL verificación SSL en el Host
 * @param $esMultiCurl bool identificación de envio múltiple
 */
\yokysantiago\ms\rest\client\RESTServices('URL', 1,1);
```