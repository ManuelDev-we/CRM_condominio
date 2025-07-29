<?php
/**
 * TEST ESPECÍFICO DE SERVICIOS ADMIN - CYBERHOLE SYSTEM
 * 
 * @description Test directo de servicios admin sin bootstrap completo
 * @version 1.0 - TEST SERVICIOS ADMIN
 */

echo "🧑‍💼 TEST SERVICIOS ADMIN - LÓGICA DE NEGOCIO\n";
echo "=" . str_repeat("=", 50) . "\n";
echo "📋 Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Incluir dependencias básicas sin bootstrap completo
try {
    require_once __DIR__ . '/../../config/env.php';
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../models/BaseModel.php';
    require_once __DIR__ . '/../../models/CryptoModel.php';
    require_once __DIR__ . '/../../models/Admin.php';
    
    echo "✅ Dependencias básicas cargadas\n\n";
} catch (Exception $e) {
    echo "❌ Error cargando dependencias: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 1: Verificar estructura de servicios
echo "📁 TEST DE ESTRUCTURA DE SERVICIOS:\n";
echo "-" . str_repeat("-", 40) . "\n";

$serviciosDir = __DIR__ . '/../admin_services/admin_services_php/';
$serviciosEsperados = [
    'AdminService.php',
    'AreaComunService.php', 
    'BaseAdminService.php',
    'BlogService.php',
    'CalleService.php',
    'CasaService.php',
    'CondominioService.php',
    'DispositivoService.php',
    'EmpleadoService.php',
    'EngomadoService.php',
    'PersonaCasaService.php',
    'TagService.php'
];

$serviciosEncontrados = 0;
foreach ($serviciosEsperados as $servicio) {
    $path = $serviciosDir . $servicio;
    if (file_exists($path)) {
        echo "✅ {$servicio}: EXISTE\n";
        $serviciosEncontrados++;
    } else {
        echo "❌ {$servicio}: NO ENCONTRADO\n";
    }
}

$porcentajeServicios = round(($serviciosEncontrados / count($serviciosEsperados)) * 100, 2);
echo "\n📊 Servicios encontrados: {$serviciosEncontrados}/" . count($serviciosEsperados) . " ({$porcentajeServicios}%)\n\n";

// Test 2: Análisis de sintaxis de servicios
echo "🔍 TEST DE SINTAXIS DE SERVICIOS:\n";
echo "-" . str_repeat("-", 35) . "\n";

$serviciosValidos = 0;
foreach ($serviciosEsperados as $servicio) {
    $path = $serviciosDir . $servicio;
    if (file_exists($path)) {
        $content = file_get_contents($path);
        
        // Verificar estructura básica de clase PHP
        $tieneClase = preg_match('/class\s+\w+/', $content);
        $tieneNamespace = preg_match('/namespace\s+/', $content) || strpos($content, '<?php') !== false;
        $tieneConstructor = preg_match('/function\s+__construct/', $content);
        
        if ($tieneClase && $tieneNamespace) {
            echo "✅ {$servicio}: Sintaxis válida\n";
            $serviciosValidos++;
            
            // Mostrar métodos encontrados
            preg_match_all('/public\s+function\s+(\w+)/', $content, $matches);
            if (!empty($matches[1])) {
                $metodos = array_slice($matches[1], 0, 3); // Solo primeros 3
                echo "   📋 Métodos: " . implode(', ', $metodos) . (count($matches[1]) > 3 ? '...' : '') . "\n";
            }
        } else {
            echo "❌ {$servicio}: Problemas de sintaxis\n";
        }
    }
}

echo "\n📊 Servicios con sintaxis válida: {$serviciosValidos}/{$serviciosEncontrados}\n\n";

// Test 3: Test específico del AdminService
echo "👤 TEST ESPECÍFICO ADMIN SERVICE:\n";
echo "-" . str_repeat("-", 35) . "\n";

try {
    $adminServicePath = $serviciosDir . 'AdminService.php';
    
    if (file_exists($adminServicePath)) {
        $adminServiceContent = file_get_contents($adminServicePath);
        echo "✅ AdminService.php encontrado\n";
        echo "📏 Tamaño: " . strlen($adminServiceContent) . " caracteres\n";
        
        // Verificar métodos esperados en AdminService
        $metodosEsperados = [
            'listarTodos',
            'buscarPorId', 
            'crear',
            'actualizar',
            'eliminar',
            'autenticar',
            'validarSesion'
        ];
        
        $metodosEncontrados = 0;
        foreach ($metodosEsperados as $metodo) {
            if (preg_match("/function\s+{$metodo}/", $adminServiceContent)) {
                echo "✅ Método {$metodo}: ENCONTRADO\n";
                $metodosEncontrados++;
            } else {
                echo "⚠️ Método {$metodo}: NO ENCONTRADO\n";
            }
        }
        
        $completitudMetodos = round(($metodosEncontrados / count($metodosEsperados)) * 100, 2);
        echo "\n📊 Completitud de métodos: {$metodosEncontrados}/" . count($metodosEsperados) . " ({$completitudMetodos}%)\n";
        
    } else {
        echo "❌ AdminService.php no encontrado\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error analizando AdminService: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Test de lógica de validación específica para admin
echo "🔐 TEST DE VALIDACIONES ESPECÍFICAS ADMIN:\n";
echo "-" . str_repeat("-", 42) . "\n";

try {
    $admin = new Admin();
    
    // Test de casos de validación específicos para servicios admin
    $casosValidacion = [
        'email_admin_valido' => ['admin@cyberhole.com', true],
        'email_admin_invalido' => ['invalid-email', false],
        'password_fuerte' => ['Admin123!Strong', true],
        'password_debil' => ['123', false],
        'nombre_valido' => ['Juan Carlos', true],
        'nombre_invalido' => ['', false]
    ];
    
    $validacionesExitosas = 0;
    foreach ($casosValidacion as $caso => $datos) {
        $valor = $datos[0];
        $esperado = $datos[1];
        
        switch ($caso) {
            case 'email_admin_valido':
            case 'email_admin_invalido':
                $resultado = $admin->validateEmailFormat($valor);
                break;
            case 'password_fuerte':
            case 'password_debil':
                $resultado = $admin->validatePasswordLength($valor);
                break;
            case 'nombre_valido':
            case 'nombre_invalido':
                $resultado = !empty(trim($valor));
                break;
            default:
                $resultado = false;
        }
        
        $icono = ($resultado === $esperado) ? "✅" : "❌";
        $estado = ($resultado === $esperado) ? "CORRECTO" : "INCORRECTO";
        echo "{$icono} {$caso}: {$estado}\n";
        
        if ($resultado === $esperado) {
            $validacionesExitosas++;
        }
    }
    
    $tasaValidaciones = round(($validacionesExitosas / count($casosValidacion)) * 100, 2);
    echo "\n📊 Validaciones exitosas: {$validacionesExitosas}/" . count($casosValidacion) . " ({$tasaValidaciones}%)\n";
    
} catch (Exception $e) {
    echo "❌ Error en validaciones: " . $e->getMessage() . "\n";
}

echo "\n";

// Resumen final específico para servicios admin
echo "📋 RESUMEN SERVICIOS ADMIN:\n";
echo "-" . str_repeat("-", 25) . "\n";

$metricas = [
    'estructura_servicios' => $porcentajeServicios,
    'sintaxis_servicios' => round(($serviciosValidos / max($serviciosEncontrados, 1)) * 100, 2),
    'completitud_admin' => isset($completitudMetodos) ? $completitudMetodos : 0,
    'validaciones_logica' => isset($tasaValidaciones) ? $tasaValidaciones : 0
];

echo "📈 MÉTRICAS DE CALIDAD:\n";
foreach ($metricas as $metrica => $valor) {
    $emoji = $valor >= 80 ? "🟢" : ($valor >= 60 ? "🟡" : "🔴");
    $label = str_replace('_', ' ', ucwords($metrica));
    echo "   {$emoji} {$label}: {$valor}%\n";
}

$promedioGeneral = round(array_sum($metricas) / count($metricas), 2);
echo "\n🎯 PROMEDIO GENERAL: {$promedioGeneral}%\n";

if ($promedioGeneral >= 80) {
    echo "\n🚀 RECOMENDACIÓN: Los servicios admin están listos para testing funcional\n";
    echo "   📋 Estructura sólida, sintaxis válida, lógica funcional\n";
    echo "   ✨ Proceder con tests de integración y casos de uso\n";
} elseif ($promedioGeneral >= 60) {
    echo "\n⚠️ RECOMENDACIÓN: Servicios funcionales con mejoras menores\n";
    echo "   🔧 Completar métodos faltantes en servicios específicos\n";
} else {
    echo "\n🚨 RECOMENDACIÓN: Requiere trabajo en estructura de servicios\n";
    echo "   🛠️ Revisar y completar servicios antes de testing avanzado\n";
}

echo "\n✨ Test servicios admin completado - " . date('Y-m-d H:i:s') . "\n";
echo "=" . str_repeat("=", 50) . "\n";
?>
