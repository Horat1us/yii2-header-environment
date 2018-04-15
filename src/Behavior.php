<?php

namespace Horat1us\Yii\HeaderEnvironment;

use yii\base;
use yii\web;
use yii\di;

/**
 * Class Behavior
 * @package Horat1us\HeaderEnvironment
 */
class Behavior extends base\Behavior
{
    public $request = 'request';

    /**
     * @var string Header Name from Request, will be used to load environment JSON
     */
    public $header = 'Set-Environment';

    public function events()
    {
        return [
            base\Controller::EVENT_BEFORE_ACTION => 'loadEnvironment',
        ];
    }

    /**
     * @throws base\InvalidConfigException
     * @throws web\BadRequestHttpException
     */
    public function loadEnvironment(): void
    {
        if (YII_ENV_PROD) {
            // @codeCoverageIgnoreStart
            \Yii::warning("Using " . static::class . " in production environment", static::class);
            // @codeCoverageIgnoreEnd
        }

        /** @var web\Request $request */
        $request = di\Instance::ensure($this->request, web\Request::class);

        $environment = $request->headers->get($this->header);
        if (!is_string($environment)) {
            return;
        }

        $list = json_decode($environment, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new web\BadRequestHttpException(
                "Invalid JSON in {$this->header} header: " . json_last_error_msg()
            );
        }
        if (!is_array($list)) {
            throw new web\BadRequestHttpException(
                "JSON Array should be in {$this->header} header"
            );
        }

        foreach ($list as $key => $value) {
            $env = $key;
            if (!is_null($value)) {
                $env .= "={$value}";
            }
            putenv($env);
        }
    }
}
