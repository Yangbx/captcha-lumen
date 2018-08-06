<?php

namespace Yangbx\CaptchaLumen;


use Cache;
use Exception;
use Illuminate\Config\Repository;
use Illuminate\Hashing\BcryptHasher as Hasher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;


/**
 * Class Captcha
 * @package Mews\Captcha
 */
class Captcha
{

    /**
     * @var Filesystem
     */
    protected $files;

    /**
     * @var Repository
     */
    protected $config;

    /**
     * @var ImageManager
     */
    protected $imageManager;


    /**
     * @var Hasher
     */
    protected $hasher;

    /**
     * @var Str
     */
    protected $str;

    /**
     * @var ImageManager->canvas
     */
    protected $canvas;

    /**
     * @var ImageManager->image
     */
    protected $image;

    /**
     * @var array
     */
    protected $backgrounds = [];

    /**
     * @var array
     */
    protected $fonts = [];

    /**
     * @var array
     */
    protected $fontColors = [];

    /**
     * @var int
     */
    protected $length = 5;

    /**
     * @var int
     */
    protected $width = 120;

    /**
     * @var int
     */
    protected $height = 36;

    /**
     * @var int
     */
    protected $angle = 15;

    /**
     * @var int
     */
    protected $lines = 3;

    /**
     * @var string
     */
    protected $characters;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var int
     */
    protected $contrast = 0;

    /**
     * @var int
     */
    protected $quality = 90;

    /**
     * @var int
     */
    protected $sharpen = 0;

    /**
     * @var int
     */
    protected $blur = 0;

    /**
     * @var bool
     */
    protected $bgImage = true;

    /**
     * @var string
     */
    protected $bgColor = '#ffffff';

    /**
     * @var bool
     */
    protected $invert = false;

    /**
     * @var bool
     */
    protected $sensitive = false;

    protected $captchaConf;
    /**
     * Constructor
     *
     * @param Filesystem $files
     * @param Repository $config
     * @param ImageManager $imageManager
     * @param Hasher $hasher
     * @param Str $str
     * @throws Exception
     * @internal param Validator $validator
     */
    public function __construct(
        Filesystem $files,
        Repository $config,
        ImageManager $imageManager,
        Hasher $hasher,
        Str $str
    )
    {
        $this->captchaConf = config('captcha');
        $this->sensitive = isset($this->captchaConf['sensitive']) ? $this->captchaConf['sensitive'] : false;
        $this->files = $files;
        $this->config = $config;
        $this->imageManager = $imageManager;
        $this->hasher = $hasher;
        $this->str = $str;
        $this->characters = isset($this->captchaConf['captcha_characters']) ? $this->captchaConf['captcha_characters'] : '2346789abcdefghjmnpqrtuxyzABCDEFGHJMNPQRTUXYZ';
    }

    /**
     * @param string $config
     * @return void
     */
    protected function configure($config)
    {
        if (!empty($this->captchaConf) && isset($this->captchaConf[$config]))
        {
            foreach($this->captchaConf[$config] as $key => $val)
            {
                $this->{$key} = $val;
            }
        }
    }
    /**
     * Image backgrounds
     *
     * @return string
     */
    protected function background()
    {
        return $this->backgrounds[rand(0, count($this->backgrounds) - 1)];
    }

    /**
     * Generate captcha text by id
     * @param $captchaId
     * @return string
     */
    protected function generateById($captchaId)
    {
        $characters = str_split($this->characters);
        $bag = '';
        for($i = 0; $i < $this->length; $i++)
        {
            $bag .= $characters[rand(0, count($characters) - 1)];
        }
        $captchaId = 'captcha_'.$captchaId;

        $cacheTime = isset($this->captchaConf['useful_time']) ? $this->captchaConf['useful_time'] : 5;
        Cache::put($captchaId,$bag,$cacheTime);
        return $bag;
    }

    /**
     * Create captcha image by id
     * @param string $config
     * @param $captchaId
     * @return mixed
     */
    public function createById($config = 'default',$captchaId)
    {

        $this->backgrounds = $this->files->files(__DIR__ . '/../assets/backgrounds');
        $this->fonts = $this->files->files(__DIR__ . '/../assets/fonts');
        preg_match('"\((.+?)\)"', app()->version(), $matches);
        if ($matches[1] > '5.4') {
            $this->fonts = array_map(function($file) {
                return $file->getPathName();
            }, $this->fonts);
        } else {
            $this->fonts = array_values($this->fonts); //reset fonts array index
        }
        $this->configure($config);
        $this->text = $this->generateById($captchaId);
        $this->canvas = $this->imageManager->canvas(
            $this->width,
            $this->height,
            $this->bgColor
        );

        if ($this->bgImage)
        {
            $this->image = $this->imageManager->make($this->background())->resize(
                $this->width,
                $this->height
            );
            $this->canvas->insert($this->image);
        }
        else
        {
            $this->image = $this->canvas;
        }

        if ($this->contrast != 0)
        {
            $this->image->contrast($this->contrast);
        }

        $this->text();

        $this->lines();

        if ($this->sharpen)
        {
            $this->image->sharpen($this->sharpen);
        }
        if ($this->invert)
        {
            $this->image->invert($this->invert);
        }
        if ($this->blur)
        {
            $this->image->blur($this->blur);
        }

        return $this->image->response('png', $this->quality);
    }

    /**
     * Captcha check by id
     * @param $value
     * @param $captchaId
     * @return bool
     */
    function checkCaptchaById($value,$captchaId)
    {
        $captcha = 'captcha_'.$captchaId;
        if(!Cache::has($captcha))
        {
            return false;
        }
        $key = Cache::get($captcha);
        if(!$this->sensitive){
            $value = strtolower($value);
            $key = strtolower($key);
        }
        if($value == $key){
            Cache::forget($captcha);
            return true;
        }else{
            return false;
        }
    }

    /**
     * Writing captcha text
     */
    protected function text()
    {
        $marginTop = $this->image->height() / $this->length;

        $i = 0;
        foreach(str_split($this->text) as $char)
        {
            $marginLeft = ($i * $this->image->width() / $this->length);

            $this->image->text($char, $marginLeft, $marginTop, function($font) {
                $font->file($this->font());
                $font->size($this->fontSize());
                $font->color($this->fontColor());
                $font->align('left');
                $font->valign('top');
                $font->angle($this->angle());
            });

            $i++;
        }
    }

    /**
     * Image fonts
     *
     * @return string
     */
    protected function font()
    {
        return $this->fonts[rand(0, count($this->fonts) - 1)];
    }

    /**
     * Random font size
     *
     * @return integer
     */
    protected function fontSize()
    {
        return rand($this->image->height() - 10, $this->image->height());
    }

    /**
     * Random font color
     *
     * @return array
     */
    protected function fontColor()
    {
        if ( ! empty($this->fontColors))
        {
            $color = $this->fontColors[rand(0, count($this->fontColors) - 1)];
        }
        else
        {
            $color = [rand(0, 255), rand(0, 255), rand(0, 255)];
        }

        return $color;
    }

    /**
     * Angle
     *
     * @return int
     */
    protected function angle()
    {
        return rand((-1 * $this->angle), $this->angle);
    }

    /**
     * Random image lines
     *
     * @return \Intervention\Image\Image
     */
    protected function lines()
    {
        for($i = 0; $i <= $this->lines; $i++)
        {
            $this->image->line(
                rand(0, $this->image->width()) + $i * rand(0, $this->image->height()),
                rand(0, $this->image->height()),
                rand(0, $this->image->width()),
                rand(0, $this->image->height()),
                function ($draw) {
                    $draw->color($this->fontColor());
                }
            );
        }
        return $this->image;
    }

}
