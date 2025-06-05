<?php

namespace App\Services;

use Illuminate\Support\Collection;

class BeneficiosService
{
    public function transformar(Collection $beneficios, Collection $filtros, Collection $fichas): Collection
    {
        return $beneficios->map(function ($beneficio) use ($filtros, $fichas) {
            $filtro = $filtros->firstWhere('id_programa', $beneficio['id_programa']);
            if (!$filtro) return null;

            if (
                $beneficio['monto'] < $filtro['min'] ||
                $beneficio['monto'] > $filtro['max']
            ) {
                return null;
            }

            $ficha = $fichas->firstWhere('id', $filtro['ficha_id']);

            if (!$ficha) return null;

            return [
                'id_programa' => $beneficio['id_programa'],
                'monto' => $beneficio['monto'],
                'fecha_recepcion' => $beneficio['fecha_recepcion'],
                'fecha' => $beneficio['fecha'],
                'ano' => date('Y', strtotime($beneficio['fecha'])),
                'view' => $beneficio['view'] ?? true,
                'ficha' => $ficha,
            ];
        })->filter();
    }

    public function agruparPorAnio(Collection $beneficios): Collection
    {
        return $beneficios->groupBy('ano');
    }

    public function formatear(Collection $agrupados): Collection
    {
        return $agrupados
            ->map(function ($items, $anio) {
                return [
                    'year' => (int) $anio,
                    'num' => $items->count(),
                    'beneficios' => $items->values(),
                ];
            })
            ->sortByDesc('year')
            ->values();
    }
}
