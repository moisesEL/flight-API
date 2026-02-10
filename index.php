<?php

require 'vendor/autoload.php';
require 'SimplePdo.php';
use Phpml\Math\Statistic\Mean; // Para la media (PARTE D)

//Configurar base de datos
$db_host = '127.0.0.1';
$db_name = 'videojuegos_db';
$db_user = 'root';
$db_pass = '';

// 2. Registrar la clase SimplePdo en Flight
Flight::register('db', 'SimplePdo', [$db_host, $db_name, $db_user, $db_pass]);

//PARTE A

// A.1: GET /recursos (Obtener todos los videojuegos)
Flight::route('GET /videojuegos', function(){
    $db = Flight::db();
    $sentencia = $db->query("SELECT * FROM videojuegos");
    $datos = $sentencia->fetchAll();
    
    Flight::json($datos);
});

// A.2: GET /recursos/{id} (Obtener uno por ID)
Flight::route('GET /videojuegos/@id', function($id){
    $db = Flight::db();
    
    // Usamos sentencias preparadas por seguridad
    $sentencia = $db->prepare("SELECT * FROM videojuegos WHERE id = ?");
    $sentencia->execute([$id]);
    $dato = $sentencia->fetch();

    if ($dato) {
        Flight::json($dato);
    } else {
        // Retornar 404 si no existe
        Flight::halt(404, json_encode([
            "status" => "error",
            "message" => "Videojuego no encontrado"
        ]));
    }
});

// A.3: POST /recursos (Insertar nuevo videojuego)
Flight::route('POST /videojuegos', function(){
    $request = Flight::request();
    $db = Flight::db();

    // Recogemos los datos del cuerpo de la petición (JSON o Form)
    $compania = $request->data->compania;
    $consola = $request->data->consola;
    $videojuego = $request->data->videojuego;
    $precio = $request->data->precio;
    $puntuacion = $request->data->puntuacion;

    // Validación básica
    if(!$compania || !$consola || !$videojuego) {
        Flight::halt(400, json_encode(["error" => "Faltan datos (compania, consola, videojuego)"]));
    }

    try {
        $sql = "INSERT INTO videojuegos (compania, consola, videojuego) VALUES (?, ?, ?)";
        $sentencia = $db->prepare($sql);
        $sentencia->execute([$compania, $consola, $videojuego]);

        Flight::json([
            "status" => "success",
            "message" => "Videojuego insertado correctamente",
            "id" => $db->lastInsertId()
        ]);
    } catch (Exception $e) {
        Flight::halt(500, json_encode(["error" => $e->getMessage()]));
    }
});

// --- PARTE B: Consumo de API Externa (DummyJson) ---

Flight::route('GET /importar-dummy', function(){
    $db = Flight::db();

    // 1. Consumir la API de DummyJson
    // Pedimos 5 productos de categoría 'laptops' para simular consolas/PCs
    $url = 'https://dummyjson.com/products/category/laptops?limit=5';
    
    // Usamos file_get_contents para leer la URL (método nativo sencillo)
    $json_data = file_get_contents($url);
    
    if($json_data === false) {
        Flight::halt(500, json_encode(["error" => "No se pudo conectar con DummyJson"]));
    }

    $respuesta = json_decode($json_data, true); // Convertir JSON a Array asociativo
    $productos = $respuesta['products']; // DummyJson devuelve la lista dentro de "products"

    $guardados = 0;

    // 2. Procesar, Mapear y Modificar datos
    foreach($productos as $prod) {
        
        // --- MAPEO DE CAMPOS ---
        // La API trae "brand", nosotros queremos "compania"
        // La API trae "category", nosotros queremos "consola"
        // La API trae "title", nosotros queremos "videojuego"

        $compania = $prod['brand'] ?? 'Generico'; 
        
        // --- MODIFICACIÓN DE VALOR (Requisito de la práctica) ---
        // Vamos a poner la consola en MAYÚSCULAS para cumplir el requisito
        $consola = strtoupper($prod['category']); 
        
        // Vamos a añadir un prefijo al nombre del juego
        $videojuego = "Edición Coleccionista: " . $prod['title'];

        // 3. Insertar en nuestra Base de Datos
        try {
            $sql = "INSERT INTO videojuegos (compania, consola, videojuego) VALUES (?, ?, ?)";
            $stmt = $db->prepare($sql);
            $stmt->execute([$compania, $consola, $videojuego]);
            $guardados++;
        } catch (Exception $e) {
            // Si falla uno, continuamos con el siguiente (o podrías parar)
            continue;
        }
    }

    // 4. Responder al cliente
    Flight::json([
        "status" => "success",
        "origen" => "DummyJson",
        "mensaje" => "Se han importado y transformado $guardados registros correctamente."
    ]);
});

// --- PARTE C: Servicio "Juego Sorpresa" (Simplificado) ---

Flight::route('GET /juego-sorpresa', function(){
    
    // 1. URL de la API (Pedimos todos los juegos de PC)
    $url = "https://www.freetogame.com/api/games?platform=pc";

    // 2. Truco para que la API no nos bloquee (User-Agent simple)
    $opciones = ['http' => ['header' => "User-Agent: MiScriptPHP"]];
    $contexto = stream_context_create($opciones);

    // 3. Descargar y convertir JSON
    $datos = file_get_contents($url, false, $contexto);
    $lista_juegos = json_decode($datos, true);

    // 4. Lógica simple: Elegir UN juego al azar de la lista
    $indice_azar = array_rand($lista_juegos);
    $juego_seleccionado = $lista_juegos[$indice_azar];

    // 5. Devolver ese juego directamente
    Flight::json([
        "servicio" => "Generador de Juego Aleatorio",
        "juego" => $juego_seleccionado
    ]);
});

// --- PARTE D: Estadística con PHP-ML (Precio Medio por Compañía) ---

Flight::route('GET /estadisticas', function() {
    $db = Flight::db();
    
    // 1. Recuperamos Compañía, Puntuación y PRECIO
    $juegos = $db->query("SELECT compania, puntuacion, precio FROM videojuegos")->fetchAll();
    
    if (empty($juegos)) {
        Flight::halt(404, json_encode(["error" => "No hay datos para analizar"]));
    }

    // 2. Agrupamos los datos
    $datosPorCompania = [];
    
    foreach ($juegos as $j) {
        $compania = $j['compania'];
        // Guardamos las notas y los precios en listas
        $datosPorCompania[$compania]['notas'][]  = (int)$j['puntuacion'];
        $datosPorCompania[$compania]['precios'][] = (float)$j['precio'];
    }

    $analisis = [];

    // 3. Calculamos las medias con PHP-ML (Mean::arithmetic)
    foreach ($datosPorCompania as $compania => $valores) {
        $analisis[$compania] = [
            "cantidad_juegos" => count($valores['notas']),
            
            "nota_media"      => round(Mean::arithmetic($valores['notas']), 2),   
            "precio_medio"    => round(Mean::arithmetic($valores['precios']), 2) . " €"
        ];
    }

    // 4. Respuesta JSON
    Flight::json([
        "titulo" => "Analisis Estadistico por precios y nota media (valoracion)",
        "descripcion" => "Calculamos cuánto cuesta de media un juego de cada compañía usando PHP-ML",
        "resultados" => $analisis
    ]);
});


// Iniciar Flight
Flight::start();
?>

