<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class BeneficioController extends Controller
{
    //

    public function index()
    {
        // Logic to retrieve and return a list of benefits
        $beneficios = collect(Http::get('https://run.mocky.io/v3/8f75c4b5-ad90-49bb-bc52-f1fc0b4aad02')->json());
        $filtros = collect(Http::get('https://run.mocky.io/v3/b0ddc735-cfc9-410e-9365-137e04e33fcf')->json());
        $fichas = collect(Http::get('https://run.mocky.io/v3/4654cafa-58d8-4846-9256-79841b29a687')->json());

        return response()->json([
            'beneficios' => $beneficios,
            'filtros' => $filtros,
            'fichas' => $fichas
        ]);
    }
}
