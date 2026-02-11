<?php

class SimplePdo extends PDO {
    
    public function __construct($host, $dbname, $user, $pass) {
        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
        
        $opciones = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Errores y excepciones
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Datos como array asociativo
            PDO::ATTR_EMULATE_PREPARES   => false,                  
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8mb4'"   // Fuerza caracteres especiales (@, tildes)
        ];

        try {
            parent::__construct($dsn, $user, $pass, $opciones);
            
        } catch (PDOException $e) {
            (json_encode(["error" => "Error de conexión: " . $e->getMessage()]));
        }
    }
}
?>