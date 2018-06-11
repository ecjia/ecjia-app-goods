<?php

namespace Ecjia\App\Goods;

use Royalcms\Component\App\AppServiceProvider;

class GoodsServiceProvider extends  AppServiceProvider
{
    
    public function boot()
    {
        $this->package('ecjia/app-goods');
    }
    
    public function register()
    {
        
    }
    
    
    
}