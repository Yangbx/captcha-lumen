<?php

if ( ! function_exists('captcha')) {

    /**
     * @param string $config
     * @return mixed
     */
    function captcha($config = 'default')
    {
        return app('captcha')->create($config);
    }
}

if ( ! function_exists('captcha_src')) {
    /**
     * @param string $config
     * @return string
     */
    function captcha_src($config = 'default')
    {
        return app('captcha')->src($config);
    }
}

if ( ! function_exists('captcha_img')) {

    /**
     * @param string $config
     * @return mixed
     */
    function captcha_img($config = 'default')
    {
        return app('captcha')->img($config);
    }
}



if (! function_exists('generate_uuid')){
    /**
     * get uuid
     * @return string
     */
    function generate_uuid(){
        $charId = md5(uniqid(rand(), true));
        $hyphen = chr(45);// "-"
        $uuid = substr($charId, 0, 8).$hyphen
            .substr($charId, 8, 4).$hyphen
            .substr($charId,12, 4).$hyphen
            .substr($charId,16, 4).$hyphen
            .substr($charId,20,12);
        return $uuid;
    }
}
