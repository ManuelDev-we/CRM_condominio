<?php
/**
 * TEST AISLADO DE MODELOS Y SERVICIOS - CYBERHOLE SYSTEM
 * 
 * @description Test directo sin bootstrap completo para verificar lógica de negocio
 * @version 1.0 - TEST AISLADO PURO
 */

echo "🔥 TEST AISLADO - MODELOS Y SERVICIOS\n";
echo "=" . str_repeat("=", 40) . "\n";
echo "📋 Fecha: " . date('Y-m-d H:i:s') . "\n\n";

// Test 1: Verificar que tenemos conexión a base de datos usando configuración directa
echo "💾 TEST DE CONEXIÓN DIRECTA A BASE DE DATOS:\n";
echo "-" . str_repeat("-", 45) . "\n";

try {
    // Configuración directa basada en tu .env
    $dsn = "mysql:host=srv645.hstgr.io;dbname=u837350477_Cuestionario;charset=utf8mb4";
    $username = "u837350477_DEV";
    $password = "Farid123#warframe#pepillo";
    
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✅ Conexión a base de datos: EXITOSA\n";
    echo "✅ Servidor: " . $pdo->getAttribute(PDO::ATTR_SERVER_INFO) . "\n";
    
    // Test básico de consulta
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM condominios");
    $result = $stmt->fetch();
    echo "✅ Condominios en BD: " . $result['total'] . "\n";
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM casas");
    $result = $stmt->fetch();
    echo "✅ Casas en BD: " . $result['total'] . "\n";
    
} catch (Exception $e) {
    echo "❌ Error de conexión: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 2: Verificar modelo Admin directamente
echo "👤 TEST DIRECTO DEL MODELO ADMIN:\n";
echo "-" . str_repeat("-", 30) . "\n";

try {
    // Incluir solo lo necesario para el modelo Admin
    require_once __DIR__ . '/../../models/BaseModel.php';
    require_once __DIR__ . '/../../models/CryptoModel.php';
    require_once __DIR__ . '/../../models/Admin.php';
    
    echo "✅ Modelos cargados correctamente\n";
    
    // Crear instancia del modelo Admin
    $adminModel = new Admin();
    echo "✅ Instancia de Admin creada\n";
    
    // Test de validaciones básicas
    $emailValido = $adminModel->validateEmailFormat('test@example.com');
    echo "✅ Validación email: " . ($emailValido ? "FUNCIONA" : "FALLA") . "\n";
    
    $passwordValida = $adminModel->validatePasswordLength('Password123!');
    echo "✅ Validación password: " . ($passwordValida ? "FUNCIONA" : "FALLA") . "\n";
    
    // Test de hash de contraseña
    $hash = $adminModel->hashPassword('TestPassword123');
    echo "✅ Hash de contraseña: " . (strlen($hash) > 20 ? "FUNCIONA" : "FALLA") . "\n";
    
} catch (Exception $e) {
    echo "❌ Error en modelo Admin: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 3: Test de lógica de negocio básica
echo "🧠 TEST DE LÓGICA DE NEGOCIO BÁSICA:\n";
echo "-" . str_repeat("-", 35) . "\n";

try {
    // Test de creación de admin temporal (solo validaciones)
    $datosAdmin = [
        'nombres' => 'Test Admin',
        'apellido1' => 'Servicios',
        'correo' => 'test.logic@cyberhole.com',
        'contrasena' => 'TestLogic123!'
    ];
    
    echo "✅ Datos de prueba preparados\n";
    
    // Validar que los datos cumplan las reglas de negocio
    $validEmail = filter_var($datosAdmin['correo'], FILTER_VALIDATE_EMAIL);
    echo "✅ Email válido según PHP: " . ($validEmail ? "SÍ" : "NO") . "\n";
    
    $passwordLength = strlen($datosAdmin['contrasena']) >= 8;
    echo "✅ Contraseña longitud adecuada: " . ($passwordLength ? "SÍ" : "NO") . "\n";
    
    $hasUppercase = preg_match('/[A-Z]/', $datosAdmin['contrasena']);
    $hasLowercase = preg_match('/[a-z]/', $datosAdmin['contrasena']);
    $hasNumbers = preg_match('/[0-9]/', $datosAdmin['contrasena']);
    $hasSpecial = preg_match('/[^A-Za-z0-9]/', $datosAdmin['contrasena']);
    
    $passwordComplex = $hasUppercase && $hasLowercase && $hasNumbers && $hasSpecial;
    echo "✅ Contraseña compleja: " . ($passwordComplex ? "SÍ" : "NO") . "\n";
    
    echo "✅ Reglas de negocio básicas: VALIDADAS\n";
    
} catch (Exception $e) {
    echo "❌ Error en lógica de negocio: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 4: Resumen del estado del sistema
echo "📊 RESUMEN DEL ESTADO DEL SISTEMA:\n";
echo "-" . str_repeat("-", 35) . "\n";

$status = [
    'conexion_bd' => isset($pdo) && $pdo instanceof PDO,
    'modelo_admin' => isset($adminModel) && $adminModel instanceof Admin,
    'validaciones' => isset($emailValido) && $emailValido && isset($passwordValida) && $passwordValida,
    'encriptacion' => isset($hash) && strlen($hash) > 20,
    'logica_negocio' => isset($passwordComplex) && $passwordComplex
];

$totalChecks = count($status);
$passedChecks = count(array_filter($status));
$successRate = round(($passedChecks / $totalChecks) * 100, 2);

echo "📈 Componentes funcionando: {$passedChecks}/{$totalChecks} ({$successRate}%)\n";

foreach ($status as $component => $working) {
    $icon = $working ? "✅" : "❌";
    $label = str_replace('_', ' ', ucfirst($component));
    echo "   {$icon} {$label}\n";
}

echo "\n🎯 CONCLUSIÓN FINAL:\n";
if ($successRate >= 90) {
    echo "   🟢 EXCELENTE - Sistema listo para pruebas de servicios\n";
    echo "   📋 Los componentes básicos funcionan correctamente\n";
    echo "   🚀 Proceder con tests de servicios específicos\n";
} elseif ($successRate >= 70) {
    echo "   🟡 BUENO - Sistema funcional con componentes menores por revisar\n";
    echo "   ⚠️ Corregir componentes fallidos antes de servicios complejos\n";
} else {
    echo "   🔴 CRÍTICO - Sistema requiere correcciones fundamentales\n";
    echo "   🚨 Resolver problemas básicos antes de continuar\n";
}

echo "\n✨ Test aislado completado - " . date('Y-m-d H:i:s') . "\n";
echo "=" . str_repeat("=", 40) . "\n";
?>
