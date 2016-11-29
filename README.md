# Captcha for Lumen

本項目修改 [Captcha for Laravel 5](https://github.com/mewebstudio/captcha) 和 [lumen-captcha](https://github.com/aishan/lumen-captcha)



## Preview
![Preview](http://i.imgur.com/HYtr744.png)

## Install
* 此 Package 必須開啟 Cache 才能使用，因為驗證碼與綁定驗證碼的 uuid 都是保存在 Cache 的。

```
composer require yangbx/captcha-lumen
```


## How to use

在`bootstrap/app.php`中註冊Captcha Service Provider：

```php
    $app->register(Yangbx\CaptchaLumen\CaptchaServiceProvider::class);
    class_alias('Yangbx\CaptchaLumen\Facades\Captcha','Captcha');
```


## Set

在`bootstrap/app.php`中可以設定各種自定義類型的驗證碼屬性，更多詳細設定請查看 [Captcha for Laravel 5](https://github.com/mewebstudio/captcha)
```php
/**
 * captcha set
 */
config(['captcha'=>
    [
        'useful_time' => 5, //驗證碼有效時間（分鐘）
        'captcha_characters' => '2346789abcdefghjmnpqrtuxyzABCDEFGHJMNPQRTUXYZ',
        'sensitive' => false, //驗證碼是否判斷大小寫
        'login'   => [ //驗證碼樣式
            'length'    => 4, //驗證碼字數
            'width'     => 120, //圖片寬度
            'height'    => 36, //字體大小和圖片高度
            'angle'     => 10, //字體傾斜度
            'lines'     => 2, //橫線數
            'quality'   => 90, //品質
            'invert'    =>false, //反相
            'bgImage'   =>true, //背景圖
            'bgColor'   =>'#ffffff',
            'fontColors'=>['#339900','#ff3300','#9966ff','#3333ff'],//字體顏色
        ],
    ]
]);
```
如果不配置設定檔，默認就是default，驗證碼有效時限為5分鐘。
## Example
因為 Lumen 都是無狀態的 API，所以驗證碼圖片都會綁上一個 UUID，先獲得驗證碼的 UUID 跟圖片的 URL，驗證時再一併發送驗證碼與 UUID。
### Generate
獲得驗證碼：
```
{Domain}/captchaInfo/{type?}
```
`type`就是在 config 中定義的 Type，如果不指定`type`，默認為`default`樣式，Response：
```json
{
  "captchaUrl": "http://{Domain}/captcha/default/782fdc90-3406-f2a9-9573-444ea3dc4d5c",
  "captchaUuid": "782fdc90-3406-f2a9-9573-444ea3dc4d5c"
}
```
`captchaUrl`為驗證碼圖片的 URL，`captchaUuid`為綁定驗證碼圖片的uuid。

#### validate
在發送 Request 時將驗證碼與 UUID 一併送回 Server 端，在接收參數時做驗證即可：
```php
public function checkCaptcha(Request $request, $type = 'default',$captchaUuid)
{
    $this->validate($request,[
        'captcha'=>'required|captcha:'.$captchaUuid
    ]);
    ...
}
```


## Links
* [Intervention Image](https://github.com/Intervention/image)
* [L5 Captcha on Github](https://github.com/mewebstudio/captcha)
* [L5 Captcha on Packagist](https://packagist.org/packages/mews/captcha)
* [For L4 on Github](https://github.com/mewebstudio/captcha/tree/master-l4)
* [License](http://www.opensource.org/licenses/mit-license.php)
* [Laravel website](http://laravel.com)
* [Laravel Turkiye website](http://www.laravel.gen.tr)
* [MeWebStudio website](http://www.mewebstudio.com)
