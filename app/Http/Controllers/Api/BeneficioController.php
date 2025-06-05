<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\BeneficiosService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class BeneficioController extends Controller
{
    public function index(BeneficiosService $service)
    {
        $beneficios = Cache::remember('beneficios', 60, function () {
            return collect(Http::get('https://run.mocky.io/v3/8f75c4b5-ad90-49bb-bc52-f1fc0b4aad02')->json('data'));
        });

        $filtros = Cache::remember('filtros', 60, function () {
            return collect(Http::get('https://run.mocky.io/v3/b0ddc735-cfc9-410e-9365-137e04e33fcf')->json('data'));
        });

        $fichas = Cache::remember('fichas', 60, function () {
            return collect(Http::get('https://run.mocky.io/v3/4654cafa-58d8-4846-9256-79841b29a687')->json('data'));
        });

        
        $filtrados = $service->transformar($beneficios, $filtros, $fichas);
        $agrupados = $service->agruparPorAnio($filtrados);
        $resultado = $service->formatear($agrupados);

        return response()->json(
            ['code' => 200,'success' => true, 'data' => $resultado],
        );
    }
}
