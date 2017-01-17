<?php

/*
|--------------------------------------------------------------------------
| Code4life Routes
|--------------------------------------------------------------------------
|
| This file is where you may override any of the routes that are included
| with Code4life.
|
*/

Route::group(['as' => 'codeforlife.'], function () {
    event('codeforlife.routing', app('router'));

    $namespacePrefix = '\\'.config('codeforlife.controllers.namespace', 'NCH\\Codeforlife\\Http\\Controllers').'\\';

    Route::get('login', ['uses' => $namespacePrefix.'CodeforlifeAuthController@login', 'as' => 'login']);
    Route::post('login', ['uses' => $namespacePrefix.'CodeforlifeAuthController@postLogin', 'as' => 'postlogin']);

    Route::group(['middleware' => ['admin.user']], function () use ($namespacePrefix) {
        event('codeforlife.admin.routing', app('router'));

        // Main Admin and Logout Route
        Route::get('/', ['uses' => $namespacePrefix.'CodeforlifeController@index', 'as' => 'dashboard']);
        Route::get('logout', ['uses' => $namespacePrefix.'CodeforlifeController@logout', 'as' => 'logout']);
        Route::post('upload', ['uses' => $namespacePrefix.'CodeforlifeController@upload', 'as' => 'upload']);
        Route::get('upgrade', ['uses' => $namespacePrefix.'CodeforlifeUpgradeController@index', 'as' => 'upgrade']);

        Route::get('profile', ['uses' => $namespacePrefix.'CodeforlifeController@profile', 'as' => 'profile']);

        try {
            foreach (\NCH\Codeforlife\Models\DataType::all() as $dataTypes) {
                Route::resource($dataTypes->slug, $namespacePrefix.'CodeforlifeBreadController');
            }
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException("Custom routes hasn't been configured because: ".$e->getMessage(), 1);
        } catch (\Exception $e) {
            // do nothing, might just be because table not yet migrated.
        }

        // Role Routes
        Route::resource('roles', $namespacePrefix.'CodeforlifeRoleController');

        // Menu Routes
        Route::group([
            'as'     => 'menus.',
            'prefix' => 'menus/{menu}',
        ], function () use ($namespacePrefix) {
            Route::get('builder', ['uses' => $namespacePrefix.'CodeforlifeMenuController@builder', 'as' => 'builder']);
            Route::post('order', ['uses' => $namespacePrefix.'CodeforlifeMenuController@order_item', 'as' => 'order']);

            Route::group([
                'as'     => 'item.',
                'prefix' => 'item',
            ], function () use ($namespacePrefix) {
                Route::delete('{id}', ['uses' => $namespacePrefix.'CodeforlifeMenuController@delete_menu', 'as' => 'destroy']);
                Route::post('/', ['uses' => $namespacePrefix.'CodeforlifeMenuController@add_item', 'as' => 'add']);
                Route::put('/', ['uses' => $namespacePrefix.'CodeforlifeMenuController@update_item', 'as' => 'update']);
            });
        });

        // Settings
        Route::group([
            'as'     => 'settings.',
            'prefix' => 'settings',
        ], function () use ($namespacePrefix) {
            Route::get('/', ['uses' => $namespacePrefix.'CodeforlifeSettingsController@index', 'as' => 'index']);
            Route::post('/', ['uses' => $namespacePrefix.'CodeforlifeSettingsController@store', 'as' => 'store']);
            Route::put('/', ['uses' => $namespacePrefix.'CodeforlifeSettingsController@update', 'as' => 'update']);
            Route::delete('{id}', ['uses' => $namespacePrefix.'CodeforlifeSettingsController@delete', 'as' => 'delete']);
            Route::get('{id}/move_up', ['uses' => $namespacePrefix.'CodeforlifeSettingsController@move_up', 'as' => 'move_up']);
            Route::get('{id}/move_down', ['uses' => $namespacePrefix.'CodeforlifeSettingsController@move_down', 'as' => 'move_down']);
            Route::get('{id}/delete_value', ['uses' => $namespacePrefix.'CodeforlifeSettingsController@delete_value', 'as' => 'delete_value']);
        });

        // Admin Media
        Route::group([
            'as'     => 'media.',
            'prefix' => 'media',
        ], function () use ($namespacePrefix) {
            Route::get('/', ['uses' => $namespacePrefix.'CodeforlifeMediaController@index', 'as' => 'index']);
            Route::post('files', ['uses' => $namespacePrefix.'CodeforlifeMediaController@files', 'as' => 'files']);
            Route::post('new_folder', ['uses' => $namespacePrefix.'CodeforlifeMediaController@new_folder', 'as' => 'new_folder']);
            Route::post('delete_file_folder', ['uses' => $namespacePrefix.'CodeforlifeMediaController@delete_file_folder', 'as' => 'delete_file_folder']);
            Route::post('directories', ['uses' => $namespacePrefix.'CodeforlifeMediaController@get_all_dirs', 'as' => 'get_all_dirs']);
            Route::post('move_file', ['uses' => $namespacePrefix.'CodeforlifeMediaController@move_file', 'as' => 'move_file']);
            Route::post('rename_file', ['uses' => $namespacePrefix.'CodeforlifeMediaController@rename_file', 'as' => 'rename_file']);
            Route::post('upload', ['uses' => $namespacePrefix.'CodeforlifeMediaController@upload', 'as' => 'upload']);
        });

        // Database Routes
        Route::group([
            'as'     => 'database.',
            'prefix' => 'database',
        ], function () use ($namespacePrefix) {
            Route::post('bread/create', ['uses' => $namespacePrefix.'CodeforlifeDatabaseController@addBread', 'as' => 'create_bread']);
            Route::post('bread/', ['uses' => $namespacePrefix.'CodeforlifeDatabaseController@storeBread', 'as' => 'store_bread']);
            Route::get('bread/{id}/edit', ['uses' => $namespacePrefix.'CodeforlifeDatabaseController@addEditBread', 'as' => 'edit_bread']);
            Route::put('bread/{id}', ['uses' => $namespacePrefix.'CodeforlifeDatabaseController@updateBread', 'as' => 'update_bread']);
            Route::delete('bread/{id}', ['uses' => $namespacePrefix.'CodeforlifeDatabaseController@deleteBread', 'as' => 'delete_bread']);
        });

        Route::resource('database', $namespacePrefix.'CodeforlifeDatabaseController');
    });
});
