<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->group(['prefix' => 'card'], function () use ($router) {
    $router->post('credit', [
        'as' => 'card.credit',
        'uses' => 'CardController@CreditCard'
    ]);

    $router->post('debit', [
        'as' => 'card.debit',
        'uses' => 'CardController@DebitCard'
    ]);
});

$router->post('sale', [
    'as' => 'sale.get',
    'uses' => 'CardController@getSale'
]);

$router->post('notification', [
    'as' => 'notification',
    'uses' => 'NotificationController@index'
]);

$router->get('/testebin', ['uses' => 'CardController@getBin']);

$router->get('/', function () {
    return view('payment');
});