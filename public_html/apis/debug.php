<?php
/**
 * Debug simple para verificar la búsqueda de usuarios
 */

define('SECURE_ACCESS', true);
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/crypto.php';

// Configurar para mostrar errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug - Búsqueda de Usuarios</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .result { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 5px; }
        .error { background: #ffe6e6; color: #cc0000; }
        .success { background: #e6f7e6; color: #006600; }
        .info { background: #e6f2ff; color: #0066cc; }
    </style>
</head>
<body>
    <h1>🔍 Debug - Búsqueda de Usuarios</h1>
    
    <?php
    $testEmail = 'test11@gmail.com';
    
    echo "<div class='info'><strong>Probando con email:</strong> $testEmail</div>";
    
    try {
        // Probar conexión a BD
        $db = Database::getConnection();
        echo "<div class='success'>✅ Conexión a BD exitosa</div>";
        
        // Buscar usuario directamente en la tabla
        echo "<h3>1. Búsqueda directa en tabla admin:</h3>";
        $sql = "SELECT id_admin, nombres, apellido1, correo FROM admin";
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $users = $stmt->fetchAll();
        
        echo "<div class='result'>";
        echo "<strong>Usuarios encontrados:</strong> " . count($users) . "<br>";
        foreach ($users as $user) {
            echo "ID: {$user['id_admin']} - Nombres: {$user['nombres']} {$user['apellido1']} - Email: ";
            
            // Intentar desencriptar el email
            try {
                $decryptedEmail = CryptoUtils::decryptEmail($user['correo']);
                if (filter_var($decryptedEmail, FILTER_VALIDATE_EMAIL)) {
                    echo "$decryptedEmail (cifrado)";
                } else {
                    echo $user['correo'] . " (texto plano)";
                }
            } catch (Exception $e) {
                echo $user['correo'] . " (texto plano)";
            }
            echo "<br>";
        }
        echo "</div>";
        
        // Buscar el email específico
        echo "<h3>2. Búsqueda específica de '$testEmail':</h3>";
        
        // Método 1: Búsqueda directa
        $sql = "SELECT * FROM admin WHERE correo = :email LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->execute(['email' => $testEmail]);
        $result1 = $stmt->fetch();
        
        echo "<div class='result'>";
        echo "<strong>Búsqueda directa:</strong> " . ($result1 ? "Encontrado" : "No encontrado") . "<br>";
        if ($result1) {
            echo "ID: {$result1['id_admin']}, Nombres: {$result1['nombres']}";
        }
        echo "</div>";
        
        // Método 2: Búsqueda con email cifrado
        try {
            $encryptedEmail = CryptoUtils::encryptEmail($testEmail);
            $stmt = $db->prepare($sql);
            $stmt->execute(['email' => $encryptedEmail]);
            $result2 = $stmt->fetch();
            
            echo "<div class='result'>";
            echo "<strong>Búsqueda con email cifrado:</strong> " . ($result2 ? "Encontrado" : "No encontrado") . "<br>";
            if ($result2) {
                echo "ID: {$result2['id_admin']}, Nombres: {$result2['nombres']}";
            }
            echo "</div>";
        } catch (Exception $e) {
            echo "<div class='error'>Error al cifrar email: " . $e->getMessage() . "</div>";
        }
        
        // Método 3: Comparar todos los emails
        echo "<h3>3. Comparación detallada:</h3>";
        foreach ($users as $user) {
            echo "<div class='result'>";
            echo "<strong>Usuario ID {$user['id_admin']}:</strong><br>";
            echo "Email en BD: " . htmlspecialchars($user['correo']) . "<br>";
            
            // Intentar desencriptar
            try {
                $decrypted = CryptoUtils::decryptEmail($user['correo']);
                echo "Email desencriptado: $decrypted<br>";
                echo "¿Coincide con '$testEmail'? " . ($decrypted === $testEmail ? "SÍ" : "NO") . "<br>";
            } catch (Exception $e) {
                echo "Email en texto plano<br>";
                echo "¿Coincide con '$testEmail'? " . ($user['correo'] === $testEmail ? "SÍ" : "NO") . "<br>";
            }
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    }
    ?>
    
    <div style="margin-top: 30px; padding: 15px; background: #fff3cd; color: #856404; border-radius: 5px;">
        <strong>Nota:</strong> Este archivo es solo para debug. Elimínalo después de resolver el problema.
    </div>
</body>
</html>
