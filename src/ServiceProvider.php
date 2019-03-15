<?php
/**
 * Created by PhpStorm.
 * User: MapleSnow
 * Date: 2019/3/15
 * Time: 1:50 PM
 */

namespace MapleSnow\EolinkerDoc;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\Foundation\Application as LaravelApplication;
use Exception;

class ServiceProvider extends BaseServiceProvider{

    public function register()
    {
        //
    }

    /**
     * @throws Exception
     */
    public function boot()
    {
        $this->check();
    }


    /**
     * @throws Exception
     */
    protected function check() {
        if(!$this->app instanceof LaravelApplication){
            throw new Exception("laravel application is off");
        }
    }
}