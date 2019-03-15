# 介绍
请求[laravel](https://laravel.com/)api接口时，会在`storage/docs`目录下生成[eolinker](https://www.eolinker.com/)文档`json文件`,可直接在**eolinker**中导入。

# 安装
1. 利用composer安装工具
    ```bash
    composer require maplesnow/laravel2eolinker
    ```
    
2. 更新composer后，将服务注册到 `config/app.php` 中的`providers`数组中
    ```php
    \MapleSnow\EolinkerDoc\ServiceProvider::class
    ```

    **Laravel 5.5** 有了依赖自动发现功能, 所有不需要再注册`ServiceProvider`.
    
# 分组中间件
将中间件注册到`app/Http/Kernel.php`
```php
protected $middlewareGroups = [
    'web' => [
       // ...
    ],

    'api' => [
        // ...
        \MapleSnow\EolinkerDoc\HandleRequest::class
    ],
];
```
    