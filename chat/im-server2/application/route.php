<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/1/11
 * Time: 14:11
 */
use  \Lee\Core\Route;

Route::get('/test',function(){
     return   324;
});

Route::get('/api/abc','api@index@index');


