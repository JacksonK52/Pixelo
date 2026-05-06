<?php

use Illuminate\Support\Facades\Route;
use pixelo\Http\Controllers\AvatarController;

Route::get(config('pixelo.route_prefix', 'avatar'), [
    AvatarController::class, 'generate'
])->name('avatar.generate');