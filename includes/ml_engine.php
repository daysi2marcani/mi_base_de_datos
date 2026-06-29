<?php
/**
 * Motor de Aprendizaje Automático para FinPlan AI
 *
 * Implementa algoritmos de análisis predictivo:
 * - Regresión lineal simple para tendencias
 * - Media móvil para predicción de gastos
 * - Regla 50/30/20 adaptativa para presupuestos
 * - Cálculo de metas de ahorro con proyección temporal
 */
class MLEngine
{
    private PDO $db;
    private int $usuarioId;

    public function __construct(PDO $db, int $usuarioId)
    {
        $this->db = $db;
        $this->usuarioId = $usuarioId;
    }

    /**
     * Regresión lineal simple: y = mx + b
     * Retorna predicción para el siguiente período
     */
    public function regresionLineal(array $valores): array
    {
        $n = count($valores);
        if ($n < 2) {
            return ['prediccion' => $valores[0] ?? 0, 'tendencia' => 'estable', 'confianza' => 50.0];
        }

        $sumX = $sumY = $sumXY = $sumX2 = 0;
        for ($i = 0; $i < $n; $i++) {
            $sumX += $i;
            $sumY += $valores[$i];
            $sumXY += $i * $valores[$i];
            $sumX2 += $i * $i;
        }

        $denominador = ($n * $sumX2 - $sumX * $sumX);
        if ($denominador == 0) {
            return ['prediccion' => end($valores), 'tendencia' => 'estable', 'confianza' => 60.0];
        }

        $m = ($n * $sumXY - $sumX * $sumY) / $denominador;
        $b = ($sumY - $m * $sumX) / $n;

        $prediccion = $m * $n + $b;
        $tendencia = $m > 50 ? 'creciente' : ($m < -50 ? 'decreciente' : 'estable');

        // R² simplificado para confianza
        $mediaY = $sumY / $n;
        $ssTot = $ssRes = 0;
        for ($i = 0; $i < $n; $i++) {
            $ssTot += pow($valores[$i] - $mediaY, 2);
            $ssRes += pow($valores[$i] - ($m * $i + $b), 2);
        }
        $r2 = $ssTot > 0 ? max(0, 1 - ($ssRes / $ssTot)) : 0.5;
        $confianza = round(min(95, max(55, $r2 * 100)), 1);

        return [
            'prediccion'  => round(max(0, $prediccion), 2),
            'pendiente'   => round($m, 2),
            'tendencia'   => $tendencia,
            'confianza'   => $confianza,
        ];
    }

    /**
     * Media móvil ponderada (últimos 3 meses pesan más)
     */
    public function mediaMovilPonderada(array $valores): float
    {
        $n = count($valores);
        if ($n === 0) return 0;
        if ($n === 1) return $valores[0];

        $pesos = [];
        for ($i = 0; $i < $n; $i++) {
            $pesos[] = $i + 1;
        }
        $sumPesos = array_sum($pesos);
        $resultado = 0;
        for ($i = 0; $i < $n; $i++) {
            $resultado += $valores[$i] * ($pesos[$i] / $sumPesos);
        }
        return round($resultado, 2);
    }

    /**
     * Obtiene totales mensuales por tipo
     */
    public function getTotalesMensuales(string $tipo, int $meses = 6): array
    {
        $stmt = $this->db->prepare("
            SELECT DATE_FORMAT(t.fecha, '%Y-%m') AS mes,
                   SUM(t.monto) AS total
            FROM transacciones t
            JOIN categorias c ON t.categoria_id = c.id
            WHERE t.usuario_id = ? AND c.tipo = ?
              AND t.fecha >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            GROUP BY DATE_FORMAT(t.fecha, '%Y-%m')
            ORDER BY mes ASC
        ");
        $stmt->execute([$this->usuarioId, $tipo, $meses]);
        return $stmt->fetchAll();
    }

    /**
     * Gastos por categoría en los últimos meses
     */
    public function getGastosPorCategoria(int $meses = 3): array
    {
        $stmt = $this->db->prepare("
            SELECT c.id, c.nombre, c.icono, c.color,
                   SUM(t.monto) AS total,
                   COUNT(t.id) AS transacciones
            FROM transacciones t
            JOIN categorias c ON t.categoria_id = c.id
            WHERE t.usuario_id = ? AND c.tipo = 'gasto'
              AND t.fecha >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            GROUP BY c.id, c.nombre, c.icono, c.color
            ORDER BY total DESC
        ");
        $stmt->execute([$this->usuarioId, $meses]);
        return $stmt->fetchAll();
    }

    /**
     * Gastos mensuales por categoría (para tendencias)
     */
    public function getGastosMensualesPorCategoria(int $categoriaId, int $meses = 6): array
    {
        $stmt = $this->db->prepare("
            SELECT DATE_FORMAT(fecha, '%Y-%m') AS mes, SUM(monto) AS total
            FROM transacciones
            WHERE usuario_id = ? AND categoria_id = ?
              AND fecha >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            GROUP BY DATE_FORMAT(fecha, '%Y-%m')
            ORDER BY mes ASC
        ");
        $stmt->execute([$this->usuarioId, $categoriaId, $meses]);
        return $stmt->fetchAll();
    }

    /**
     * Análisis completo con sugerencias de presupuesto (Regla 50/30/20 adaptativa)
     */
    public function analizarPresupuesto(): array
    {
        $ingresosMensuales = $this->getTotalesMensuales('ingreso');
        $gastosMensuales = $this->getTotalesMensuales('gasto');

        $valoresIngreso = array_column($ingresosMensuales, 'total');
        $valoresGasto = array_column($gastosMensuales, 'total');

        $predIngreso = $this->regresionLineal(array_map('floatval', $valoresIngreso));
        $predGasto = $this->regresionLineal(array_map('floatval', $valoresGasto));

        $ingresoPromedio = $this->mediaMovilPonderada(array_map('floatval', $valoresIngreso));
        $gastoPromedio = $this->mediaMovilPonderada(array_map('floatval', $valoresGasto));

        $ingresoEstimado = $predIngreso['prediccion'] > 0 ? $predIngreso['prediccion'] : $ingresoPromedio;
        $gastoEstimado = $predGasto['prediccion'] > 0 ? $predGasto['prediccion'] : $gastoPromedio;

        // Regla 50/30/20 adaptada según historial real
        $gastosPorCat = $this->getGastosPorCategoria(3);
        $totalGastosCat = array_sum(array_column($gastosPorCat, 'total'));
        $mesesAnalizados = max(1, 3);

        $necesidades = 0;
        $deseos = 0;
        $categoriasNecesidad = ['Vivienda', 'Alimentación', 'Transporte', 'Salud', 'Servicios', 'Educación'];

        $sugerencias = [];
        foreach ($gastosPorCat as $cat) {
            $promedioMensual = round($cat['total'] / $mesesAnalizados, 2);
            $historico = $this->getGastosMensualesPorCategoria($cat['id']);
            $valores = array_map('floatval', array_column($historico, 'total'));
            $regresion = $this->regresionLineal($valores);
            $prediccion = $regresion['prediccion'] > 0 ? $regresion['prediccion'] : $promedioMensual;

            // Ajuste ML: reducir 5% si tendencia creciente en deseos
            $esNecesidad = in_array($cat['nombre'], $categoriasNecesidad);
            if (!$esNecesidad && $regresion['tendencia'] === 'creciente') {
                $prediccion = round($prediccion * 0.95, 2);
            }

            $sugerencias[] = [
                'categoria_id'   => $cat['id'],
                'categoria'      => $cat['nombre'],
                'icono'          => $cat['icono'],
                'color'          => $cat['color'],
                'promedio_actual' => $promedioMensual,
                'sugerido_ml'    => $prediccion,
                'tendencia'      => $regresion['tendencia'],
                'confianza'      => $regresion['confianza'],
                'tipo_gasto'     => $esNecesidad ? 'necesidad' : 'deseo',
            ];

            if ($esNecesidad) {
                $necesidades += $promedioMensual;
            } else {
                $deseos += $promedioMensual;
            }
        }

        $ahorroActual = max(0, $ingresoPromedio - $gastoPromedio);
        $porcentajeAhorro = $ingresoPromedio > 0 ? round(($ahorroActual / $ingresoPromedio) * 100, 1) : 0;

        // Meta de ahorro sugerida: mínimo 20% del ingreso
        $ahorroSugerido = round($ingresoEstimado * 0.20, 2);
        $ahorroOptimo = round($ingresoEstimado * 0.25, 2);

        $confianzaGlobal = round(
            ($predIngreso['confianza'] + $predGasto['confianza']) / 2,
            1
        );

        return [
            'ingreso_promedio'     => $ingresoPromedio,
            'gasto_promedio'       => $gastoPromedio,
            'ingreso_predicho'     => $ingresoEstimado,
            'gasto_predicho'       => $gastoEstimado,
            'balance_mensual'      => round($ingresoEstimado - $gastoEstimado, 2),
            'tendencia_ingresos'   => $predIngreso['tendencia'],
            'tendencia_gastos'     => $predGasto['tendencia'],
            'porcentaje_ahorro'    => $porcentajeAhorro,
            'ahorro_sugerido'      => $ahorroSugerido,
            'ahorro_optimo'        => $ahorroOptimo,
            'distribucion'         => [
                'necesidades' => round($necesidades, 2),
                'deseos'      => round($deseos, 2),
                'ahorro'      => $ahorroActual,
            ],
            'sugerencias_categoria' => $sugerencias,
            'confianza_global'     => $confianzaGlobal,
            'algoritmos'           => [
                'Regresión Lineal Simple',
                'Media Móvil Ponderada',
                'Regla 50/30/20 Adaptativa',
            ],
        ];
    }

    /**
     * Sugerencias de metas de ahorro basadas en capacidad financiera
     */
    public function sugerirMetasAhorro(): array
    {
        $analisis = $this->analizarPresupuesto();
        $capacidadAhorro = max(0, $analisis['balance_mensual']);

        if ($capacidadAhorro <= 0) {
            $capacidadAhorro = $analisis['ahorro_sugerido'];
        }

        $metasSugeridas = [
            [
                'nombre'          => 'Fondo de emergencia',
                'monto_objetivo'  => round($analisis['gasto_promedio'] * 3, 2),
                'descripcion'     => '3 meses de gastos como colchón financiero',
                'prioridad'       => 'alta',
                'meses_estimados' => $capacidadAhorro > 0
                    ? ceil(($analisis['gasto_promedio'] * 3) / $capacidadAhorro)
                    : 12,
                'confianza'       => 88.0,
            ],
            [
                'nombre'          => 'Ahorro a corto plazo',
                'monto_objetivo'  => round($capacidadAhorro * 6, 2),
                'descripcion'     => 'Meta de 6 meses de ahorro acumulado',
                'prioridad'       => 'media',
                'meses_estimados' => 6,
                'confianza'       => 82.0,
            ],
            [
                'nombre'          => 'Inversión personal',
                'monto_objetivo'  => round($analisis['ingreso_promedio'] * 0.5, 2),
                'descripcion'     => 'Capital para educación o emprendimiento',
                'prioridad'       => 'media',
                'meses_estimados' => $capacidadAhorro > 0
                    ? ceil(($analisis['ingreso_promedio'] * 0.5) / $capacidadAhorro)
                    : 18,
                'confianza'       => 75.0,
            ],
        ];

        // Proyección de metas existentes
        $stmt = $this->db->prepare("
            SELECT * FROM metas_ahorro WHERE usuario_id = ?
        ");
        $stmt->execute([$this->usuarioId]);
        $metasExistentes = $stmt->fetchAll();

        $proyecciones = [];
        foreach ($metasExistentes as $meta) {
            $faltante = $meta['monto_objetivo'] - $meta['monto_actual'];
            $mesesRestantes = $capacidadAhorro > 0
                ? ceil($faltante / $capacidadAhorro)
                : null;

            $probabilidad = 100;
            if ($meta['fecha_limite'] && $mesesRestantes) {
                $fechaLimite = new DateTime($meta['fecha_limite']);
                $hoy = new DateTime();
                $mesesDisponibles = max(1, ($fechaLimite->diff($hoy)->days) / 30);
                $probabilidad = min(100, round(($mesesDisponibles / $mesesRestantes) * 100, 1));
            }

            $proyecciones[] = [
                'meta_id'           => $meta['id'],
                'nombre'            => $meta['nombre'],
                'monto_objetivo'    => (float) $meta['monto_objetivo'],
                'monto_actual'      => (float) $meta['monto_actual'],
                'faltante'          => round($faltante, 2),
                'meses_estimados'   => $mesesRestantes,
                'probabilidad_exito' => $probabilidad,
                'recomendacion'     => $probabilidad >= 80
                    ? 'En buen camino, mantén el ritmo de ahorro'
                    : ($probabilidad >= 50
                        ? 'Considera aumentar el ahorro mensual un 15%'
                        : 'Meta difícil de alcanzar; ajusta fecha o monto'),
            ];
        }

        return [
            'capacidad_ahorro_mensual' => $capacidadAhorro,
            'metas_sugeridas'          => $metasSugeridas,
            'proyecciones_existentes'  => $proyecciones,
        ];
    }

    /**
     * Guarda resultado del análisis en historial
     */
    public function guardarAnalisis(string $tipo, array $resultado, float $confianza): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO analisis_ml (usuario_id, tipo_analisis, resultado, confianza)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $this->usuarioId,
            $tipo,
            json_encode($resultado, JSON_UNESCAPED_UNICODE),
            $confianza,
        ]);
    }
}
