Rest Client
===========
Esta extensión es ayuda a consumir servicios API REST (GET, POST) con curl

Installation
------------

La forma preferida para instalar esta extensión es mediante [composer](http://getcomposer.org/download/).

Ejecutando

```
php composer.phar require --prefer-dist yokysantiago/yii2-rest-client "*"
```

o agregando

```
"yokysantiago/yii2-rest-client": "*"
```

a la sección de require en su archivo `composer.json`.


Uso
-----

Una vez instalada la extensión, simplemente usela en su código así:

```php

use \yokysantiago\ms\rest\client\RESTServices;

/**
 * 1er parámetro string Url a la cual se consultará el servicio web (requerido)
 * 2do parámetro boolean verificación SSL en el Host (opcional)
 * 3er parámetro boolean verificación SSL en el Host (opcional)
 * 4to parámetro boolean identificación de envio múltiple (opcional)
 */
$resultado = (new \yokysantiago\ms\rest\client\RESTServices('https://example.com/v1/api', 1,1))
            ->setearCabeceras([
                'Authorization' => 'Bearer tokenExample',
                'Content-Type' => 'application/json'
            ])
            ->obtenerInformacionPOST([
                'id' => 1,
                'name' => 'Test'
            ]);
```