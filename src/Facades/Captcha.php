<?php

namespace Yangbx\CaptchaLumen\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mews\Captcha
 */
class Captcha extends Facade {

    /**
     * @return string
     */
    protected static function getFacadeAccessor() { return 'captcha'; }

}
