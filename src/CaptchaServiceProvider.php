<?php

namespace Yangbx\CaptchaLumen;

use Yangbx\CaptchaLumen\Captcha;
use Illuminate\Support\ServiceProvider;

/**
 * Class CaptchaServiceProvider
 * @package Mews\Captcha
 */
class CaptchaServiceProvider extends ServiceProvider {

    /**
     * Boot the service provider.
     *
     * @return null
     */
    public function boot()
    {
        // HTTP routing
        $this->app->router->get('captchaInfo[/{type}]', 'Yangbx\CaptchaLumen\LumenCaptchaController@getCaptchaInfo');
        $this->app->router->get('captcha/{type}/{captchaId}', 'Yangbx\CaptchaLumen\LumenCaptchaController@getCaptcha');

        // Validator extensions
        $this->app['validator']->extend('captcha', function($attribute, $value, $parameters)
        {
            $captchaId=$parameters[0];
            return app('captcha')->checkCaptchaById($value,$captchaId);
        });

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // Bind captcha
        $this->app->bind('captcha', function($app)
        {
            return new Captcha(
                $app['Illuminate\Filesystem\Filesystem'],
                $app['Illuminate\Config\Repository'],
                $app['Intervention\Image\ImageManager'],
                $app['Illuminate\Hashing\BcryptHasher'],
                $app['Illuminate\Support\Str']
            );
        });
    }

}
