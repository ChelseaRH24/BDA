<?php
// Datos de conexión
$host = 'localhost';       // Servidor donde está tu BD
$db   = 'rootfm'; // Nombre de tu base de datos
$user = 'root';         // Usuario de la BD
$pass = '';      // Contraseña del usuario
$charset = 'utf8mb4';      // Codificación recomendada

// Cadena de conexión DSN (Data Source Name)
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

// Opciones de configuración
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Activa excepciones para errores
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devuelve resultados como array asociativo
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Desactiva emulación de consultas preparadas
];

try {
    // Creamos el objeto $pdo que representa la conexión
    $pdo = new PDO($dsn, $user, $pass, $options);

    
} catch (\PDOException $e) {
    // Si hay error en la conexión, lo mostramos
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>