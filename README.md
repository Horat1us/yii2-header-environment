# Yii2 Header Behavior
[![Latest Stable Version](https://poser.pugx.org/horat1us/yii2-header-environment/v/stable.png)](https://packagist.org/packages/horat1us/yii2-header-environment)
[![Total Downloads](https://poser.pugx.org/horat1us/yii2-header-environment/downloads.png)](https://packagist.org/packages/horat1us/yii2-header-environment)
[![Build Status](https://travis-ci.org/Horat1us/yii2-header-environment.svg?branch=master)](https://travis-ci.org/horat1us/yii2-header-environment)
[![codecov](https://codecov.io/gh/horat1us/yii2-header-environment/branch/master/graph/badge.svg)](https://codecov.io/gh/horat1us/yii2-header-environment)

Purpose of this package is to load environment variables from HTTP headers.  
Case: your API receives hash and need to check it by secret key stored in environment.
Your test case can not directly use `putenv` because API and Test API Client is executed in different flows.

**Notice: you should use this package only in test environment!**

## Installation
```bash
composer require horat1us/yii2-header-environment --dev # Don't forget to do not use it in production
```

## Usage
Controller:
```php
<?php

namespace App\Controllers;

use yii\web;
use Horat1us\HeaderEnvironment;

class SiteController extends web\Controller
{
    public function behaviors()
    {
        $behaviors = []; // Some your production behaviors
        if(YII_ENV_TEST) {
            $behaviors['environment'] = [
                'class' => HeaderEnvironment\Behavior::class,
                'header' => 'Set-Environment', // default
            ];
        } 
    }

    public function actionIndex()
    {
        $request = \Yii::$app->request;

        $salt = $request->post('salt');
        $sign = $request->post('sign');

        $secret = getenv('SECRET');

        \Yii::$app->response->statusCode = md5($salt . $secret) === $sign
            ? 200
            : 400;
    }
}
```
Test Case:
```php
<?php

namespace App\Tests;

class ApiTest {
    public function testSignChecking(\ApiTester $I) {
        $secret = 'persist-secret';
        $salt = mt_rand();
        $sign = md5($salt . $secret);
        
        $I->haveHttpHeader('Set-Environment', json_encode([
            'SECRET' => $secret,
        ]));
        $I->sendPOST('/site/index', [
            'salt' => $salt,
            'sign' => $sign,
        ]);
        $I->seeResponseCodeIs(200);

        $I->haveHttpHeader('Set-Environment', json_encode([
            'SECRET' => null, // Delete environment
        ]));
        $I->sendPOST('/site/index', [
            'salt' => $salt,
            'sign' => $sign,
        ]);
        $I->seeResponseCodeIs(400);
    }
}

```

## Author
- [Alexander <horat1us> Letnikow](mailto:reclamme@gmail.com)

## License
[MIT](./LICENSE)
