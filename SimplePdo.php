<?php

class SimplePdo extends PDO {
    
    public function __construct($host, $dbname, $user, $pass) {
        try {
            // Configuración del DSN (Data Source Name)
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
            
            // Llamamos al constructor padre (PDO)
            parent::__construct($dsn, $user, $pass);
            
            // Configurar para que lance excepciones en caso de error
            $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Configurar para que los resultados vengan como array asociativo por defecto
            $this->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            die(json_encode(["error" => "Error de conexión: " . $e->getMessage()]));
        }
    }
}
?>