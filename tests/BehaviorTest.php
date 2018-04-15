<?php

namespace Horat1us\HeaderEnvironment\Tests;

use Horat1us\HeaderEnvironment;
use yii\base;

/**
 * Class BehaviorTest
 * @package Horat1us\HeaderEnvironment\Tests
 */
class BehaviorTest extends TestCase
{
    /** @var HeaderEnvironment\Behavior */
    protected $behavior;

    public function setUp(): void
    {
        parent::setUp();
        $this->behavior = \Yii::$container->get(HeaderEnvironment\Behavior::class);
    }

    public function testMissingHeader()
    {
        $env = $_ENV;
        $this->behavior->loadEnvironment();
        $this->assertEquals($env, $_ENV);
    }

    /**
     * @expectedException \yii\web\BadRequestHttpException
     * @expectedExceptionMessage Invalid JSON in Set-Environment header: Syntax error
     */
    public function testInvalidJson()
    {
        \Yii::$app->request->headers->set('Set-Environment', '<xml></xml>');
        $this->behavior->loadEnvironment();
    }

    /**
     * @expectedException \yii\web\BadRequestHttpException
     * @expectedExceptionMessage JSON Array should be in Set-Environment header
     */
    public function testNotObjectJson()
    {
        \Yii::$app->request->headers->set('Set-Environment', json_encode('some string'));
        $this->behavior->loadEnvironment();
    }

    public function testLoadingEnvironment()
    {
        $environment = [
            'key' => 'value',
            'number' => 5,
        ];

        \Yii::$app->request->headers->set('Set-Environment', json_encode($environment));
        $this->behavior->loadEnvironment();

        foreach ($environment as $key => $value) {
            $this->assertEquals((string)$value, getenv($key));
        }
    }

    public function testCleaningEnvironment()
    {
        $environment = [
            'key' => null,
        ];
        putenv('key=1');
        $this->assertEquals(1, getenv('key'));
        \Yii::$app->request->headers->set('Set-Environment', json_encode($environment));
        $this->behavior->loadEnvironment();
        $this->assertFalse(getenv('key'));
    }

    public function testEvents()
    {
        $events = $this->behavior->events();
        $this->assertArrayHasKey(base\Controller::EVENT_BEFORE_ACTION, $events);
        $this->assertEquals($events[base\Controller::EVENT_BEFORE_ACTION], 'loadEnvironment');
    }
}
