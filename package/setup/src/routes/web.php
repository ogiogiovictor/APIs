<?php

use Bitfumes\Setup\Http\Controllers\PrepareSetupController;
use Illuminate\Support\Facades\Route;



Route::get('config', function() {

    return view('setup::process');
});

Route::post('processSetupConfig', [PrepareSetupController::class, 'prepareIntegration']);
