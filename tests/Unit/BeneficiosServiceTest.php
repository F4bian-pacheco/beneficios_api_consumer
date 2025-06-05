<?php

namespace Tests\Unit\Services;

use App\Services\BeneficiosService;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class BeneficiosServiceTest extends TestCase
{
    private BeneficiosService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new BeneficiosService();
    }

    /** @test */
    public function transformar_retorna_beneficios_transformados_correctamente()
    {
        // Arrange
        $beneficios = collect([
            [
                'id_programa' => 1,
                'monto' => 100,
                'fecha_recepcion' => '2023-01-15',
                'fecha' => '2023-01-01',
                'view' => false
            ]
        ]);

        $filtros = collect([
            [
                'id_programa' => 1,
                'min' => 50,
                'max' => 200,
                'ficha_id' => 10
            ]
        ]);

        $fichas = collect([
            [
                'id' => 10,
                'nombre' => 'Ficha Test',
                'descripcion' => 'Descripción test'
            ]
        ]);

        // Act
        $resultado = $this->service->transformar($beneficios, $filtros, $fichas);

        // Assert
        $this->assertCount(1, $resultado);
        $beneficioTransformado = $resultado->first();
        
        $this->assertEquals(1, $beneficioTransformado['id_programa']);
        $this->assertEquals(100, $beneficioTransformado['monto']);
        $this->assertEquals('2023-01-15', $beneficioTransformado['fecha_recepcion']);
        $this->assertEquals('2023-01-01', $beneficioTransformado['fecha']);
        $this->assertEquals('2023', $beneficioTransformado['ano']);
        $this->assertFalse($beneficioTransformado['view']);
        $this->assertEquals($fichas->first(), $beneficioTransformado['ficha']);
    }

    /** @test */
    public function transformar_filtra_beneficios_sin_filtro_correspondiente()
    {
        // Arrange
        $beneficios = collect([
            ['id_programa' => 1, 'monto' => 100, 'fecha' => '2023-01-01', 'fecha_recepcion' => '2023-01-15'], // Con filtro
            ['id_programa' => 2, 'monto' => 150, 'fecha' => '2023-02-01', 'fecha_recepcion' => '2023-02-15'], // Sin filtro
        ]);

        $filtros = collect([
            ['id_programa' => 1, 'min' => 50, 'max' => 200, 'ficha_id' => 10]
        ]);

        $fichas = collect([
            ['id' => 10, 'nombre' => 'Ficha Test']
        ]);

        // Act
        $resultado = $this->service->transformar($beneficios, $filtros, $fichas);

        // Assert
        $this->assertCount(1, $resultado);
        $this->assertEquals(1, $resultado->first()['id_programa']);
    }

    /** @test */
    public function transformar_filtra_beneficios_con_monto_menor_al_minimo()
    {
        // Arrange
        $beneficios = collect([
            ['id_programa' => 1, 'monto' => 30, 'fecha' => '2023-01-01', 'fecha_recepcion' => '2023-01-15'], // Muy bajo
            ['id_programa' => 1, 'monto' => 100, 'fecha' => '2023-02-01', 'fecha_recepcion' => '2023-02-15'] // Válido
        ]);

        $filtros = collect([
            ['id_programa' => 1, 'min' => 50, 'max' => 200, 'ficha_id' => 10]
        ]);

        $fichas = collect([
            ['id' => 10, 'nombre' => 'Ficha Test']
        ]);

        // Act
        $resultado = $this->service->transformar($beneficios, $filtros, $fichas);

        // Assert
        $this->assertCount(1, $resultado);
        $this->assertEquals(100, $resultado->first()['monto']);
    }

    /** @test */
    public function transformar_filtra_beneficios_con_monto_mayor_al_maximo()
    {
        // Arrange
        $beneficios = collect([
            ['id_programa' => 1, 'monto' => 100, 'fecha' => '2023-01-01', 'fecha_recepcion' => '2023-01-15'], // Válido
            ['id_programa' => 1, 'monto' => 250, 'fecha' => '2023-02-01', 'fecha_recepcion' => '2023-02-15'] // Muy alto
        ]);

        $filtros = collect([
            ['id_programa' => 1, 'min' => 50, 'max' => 200, 'ficha_id' => 10]
        ]);

        $fichas = collect([
            ['id' => 10, 'nombre' => 'Ficha Test']
        ]);

        // Act
        $resultado = $this->service->transformar($beneficios, $filtros, $fichas);

        // Assert
        $this->assertCount(1, $resultado);
        $this->assertEquals(100, $resultado->first()['monto']);
    }

    /** @test */
    public function transformar_maneja_ficha_no_encontrada()
    {
        // Arrange
        $beneficios = collect([
            ['id_programa' => 2, 'monto' => 150, 'fecha' => '2023-02-01', 'fecha_recepcion' => '2023-02-15']
        ]);

        $filtros = collect([
            ['id_programa' => 1, 'min' => 50, 'max' => 200, 'ficha_id' => 999] // ID inexistente
        ]);

        $fichas = collect([
            ['id' => 10, 'nombre' => 'Ficha Test']
        ]);

        // Act
        $resultado = $this->service->transformar($beneficios, $filtros, $fichas);

        // Assert
        $this->assertCount(0, $resultado);
        $this->assertTrue($resultado->isEmpty());
    }

    /** @test */
    public function agruparPorAnio_agrupa_beneficios_correctamente()
    {
        // Arrange
        $beneficios = collect([
            ['id_programa' => 1, 'ano' => '2023', 'monto' => 100],
            ['id_programa' => 2, 'ano' => '2023', 'monto' => 150],
            ['id_programa' => 3, 'ano' => '2024', 'monto' => 200]
        ]);

        // Act
        $resultado = $this->service->agruparPorAnio($beneficios);

        // Assert
        $this->assertCount(2, $resultado);
        $this->assertTrue($resultado->has('2023'));
        $this->assertTrue($resultado->has('2024'));
        $this->assertCount(2, $resultado->get('2023'));
        $this->assertCount(1, $resultado->get('2024'));
    }

    /** @test */
    public function formatear_formatea_beneficios_agrupados_correctamente()
    {
        // Arrange
        $agrupados = collect([
            '2023' => collect([
                ['id_programa' => 1, 'monto' => 100],
                ['id_programa' => 2, 'monto' => 150]
            ]),
            '2024' => collect([
                ['id_programa' => 3, 'monto' => 200]
            ])
        ]);

        // Act
        $resultado = $this->service->formatear($agrupados);

        // Assert
        $this->assertCount(2, $resultado);
        
        // Verificar orden descendente por año
        $primerItem = $resultado->first();
        $segundoItem = $resultado->last();
        
        $this->assertEquals(2024, $primerItem['year']);
        $this->assertEquals(1, $primerItem['num']);
        $this->assertCount(1, $primerItem['beneficios']);
        
        $this->assertEquals(2023, $segundoItem['year']);
        $this->assertEquals(2, $segundoItem['num']);
        $this->assertCount(2, $segundoItem['beneficios']);
    }


    /** @test */
    public function integracion_completa_del_servicio()
    {
        // Arrange
        $beneficios = collect([
            [
                'id_programa' => 1,
                'monto' => 100,
                'fecha_recepcion' => '2023-01-15',
                'fecha' => '2023-01-01',
                'view' => true
            ],
            [
                'id_programa' => 1,
                'monto' => 150,
                'fecha_recepcion' => '2024-01-15',
                'fecha' => '2024-01-01',
                'view' => false
            ],
            [
                'id_programa' => 1,
                'monto' => 50, // Será filtrado por estar fuera del rango
                'fecha_recepcion' => '2023-02-15',
                'fecha' => '2023-02-01'
            ]
        ]);

        $filtros = collect([
            [
                'id_programa' => 1,
                'min' => 80,
                'max' => 200,
                'ficha_id' => 10
            ]
        ]);

        $fichas = collect([
            [
                'id' => 10,
                'nombre' => 'Ficha Programa 1',
                'descripcion' => 'Descripción del programa'
            ]
        ]);

        // Act
        $transformados = $this->service->transformar($beneficios, $filtros, $fichas);
        $agrupados = $this->service->agruparPorAnio($transformados);
        $formateados = $this->service->formatear($agrupados);

        // Assert
        $this->assertCount(2, $formateados); // 2 años diferentes
        
        // Verificar orden por año descendente
        $this->assertEquals(2024, $formateados->first()['year']);
        $this->assertEquals(2023, $formateados->last()['year']);
        
        // Verificar conteos
        $this->assertEquals(1, $formateados->first()['num']); // 2024: 1 beneficio
        $this->assertEquals(1, $formateados->last()['num']);  // 2023: 1 beneficio (uno fue filtrado)
    }
}