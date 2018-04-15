<?php

namespace Horat1us\Yii\HeaderEnvironment\Tests;

use yii\di\Container;

/**
 * Class TestCase
 * @package Horat1us\HeaderEnvironment\Tests
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * Create new application instance if it doesn't exist.
     */
    public function setUp(): void
    {
        if (isset(\Yii::$app)) {
            return;
        }
        \Yii::$container = new Container();
        \Yii::createObject([
            'class' => \yii\web\Application::class,
            'id' => mt_rand(),
            'basePath' => __DIR__,
            'components' => [
                'request' => \yii\web\Request::class,
            ]
        ]);
    }

    /**
     * Clear created application.
     * @return void
     */
    public function tearDown()
    {
        \Yii::$app = null;
        \Yii::$container = new Container();
        $_FILES = [];
    }
}
