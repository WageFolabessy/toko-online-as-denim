<?php

use App\Http\Controllers\RajaOngkirController;
use Illuminate\Support\Facades\Route;


Route::get('/rajaongkir', [RajaOngkirController::class, 'index']);
