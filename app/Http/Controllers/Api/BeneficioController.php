<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\BeneficiosService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
/**
 * @OA\Tag(
 *     name="Beneficios",
 *     description="API para gestión de beneficios sociales"
 * )
 */
class BeneficioController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/v1/beneficios-filtrados",
     *     operationId="getBeneficios",
     *     tags={"Beneficios"},
     *     summary="Obtener lista de beneficios agrupados por año",
     *     description="Retorna una lista de beneficios procesados, filtrados y agrupados por año en orden descendente",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de beneficios obtenida exitosamente",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="code",
     *                 type="integer",
     *                 example=200,
     *                 description="Código de estado HTTP"
     *             ),
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true,
     *                 description="Indica si la operación fue exitosa"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 description="Array de beneficios agrupados por año",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="year",
     *                         type="integer",
     *                         example=2024,
     *                         description="Año de los beneficios"
     *                     ),
     *                     @OA\Property(
     *                         property="num",
     *                         type="integer",
     *                         example=5,
     *                         description="Número total de beneficios en el año"
     *                     ),
     *                     @OA\Property(
     *                         property="beneficios",
     *                         type="array",
     *                         description="Lista de beneficios del año",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(
     *                                 property="id_programa",
     *                                 type="integer",
     *                                 example=1,
     *                                 description="ID del programa del beneficio"
     *                             ),
     *                             @OA\Property(
     *                                 property="monto",
     *                                 type="number",
     *                                 format="integer",
     *                                 example=150000,
     *                                 description="Monto del beneficio"
     *                             ),
     *                             @OA\Property(
     *                                 property="fecha_recepcion",
     *                                 type="string",
     *                                 format="date",
     *                                 example="10/10/2025",
     *                                 description="Fecha de recepción del beneficio"
     *                             ),
     *                             @OA\Property(
     *                                 property="fecha",
     *                                 type="string",
     *                                 format="date",
     *                                 example="2023-10-10",
     *                                 description="Fecha del beneficio"
     *                             ),
     *                             @OA\Property(
     *                                 property="ano",
     *                                 type="string",
     *                                 example="2024",
     *                                 description="Año extraído de la fecha"
     *                             ),
     *                             @OA\Property(
     *                                 property="view",
     *                                 type="boolean",
     *                                 example=true,
     *                                 description="Indica si el beneficio es visible"
     *                             ),
     *                             @OA\Property(
     *                                 property="ficha",
     *                                 type="object",
     *                                 nullable=true,
     *                                 description="Información de la ficha asociada al beneficio",
     *                                 @OA\Property(
     *                                     property="id",
     *                                     type="integer",
     *                                     example=10,
     *                                     description="ID de la ficha"
     *                                 ),
     *                                 @OA\Property(
     *                                     property="nombre",
     *                                     type="string",
     *                                     example="Subsidio Familiar (SUF)",
     *                                     description="Nombre de la ficha"
     *                                 ),
     *                                 @OA\Property(
     *                                     property="url",
     *                                     type="string",
     *                                     example="subsidio_familiar_suf",
     *                                     description="url de la ficha"
     *                                 ),
     *                                 @OA\Property(
     *                                     property="categoria",
     *                                     type="string",
     *                                     example="bonos",
     *                                     description="Categoría de la ficha"
     *                                 ),
     *                                 @OA\Property(
     *                                     property="descripcion",
     *                                     type="string",
     *                                     example="Programa de subsidio para vivienda",
     *                                     description="Descripción de la ficha"
     *                                 )
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error interno del servidor",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="code",
     *                 type="integer",
     *                 example=500
     *             ),
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Error interno del servidor"
     *             )
     *         )
     *     )
     * )
     */
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
