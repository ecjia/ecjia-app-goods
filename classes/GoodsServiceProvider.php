<?php

namespace Ecjia\App\Goods;

use Royalcms\Component\App\AppParentServiceProvider;

class GoodsServiceProvider extends  AppParentServiceProvider
{
    
    public function boot()
    {
        $this->package('ecjia/app-goods', null, dirname(__DIR__));
    }
    
    public function register()
    {
        
    }
    
    
    
}