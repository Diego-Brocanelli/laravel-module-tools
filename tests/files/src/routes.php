<?php

use Illuminate\Support\Facades\Route;

Route::get('/test', function () {

    return view('skeleton::folder.test-view')->with([
        'title' => 'Teste Um'
    ]);

});

Route::namespace('Bnw\Skeleton\Http\Controllers')->group(function(){

    Route::get('/test/two', 'ExampleController@show')->name('example');

});

