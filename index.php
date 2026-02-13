<?php

require 'vendor/autoload.php';
require 'SimplePdo.php';
use Phpml\Math\Statistic\Mean; // Para la media (PARTE D)

//Configurar base de datos
$db_host = '127.0.0.1'; // Cambiar si lo pruebo en Windows
$db_name = 'videojuegos_db';
$db_user = 'moises';  // usuario de clase
$db_pass = 'Peluchin1';

// Registrar la clase SimplePdo en Flight
Flight::register('db', 'SimplePdo', [$db_host, $db_name, $db_user, $db_pass]);

//PARTE A
// GET (Obtener todos los videojuegos)
Flight::route('GET /videojuegos', function(){
    $db = Flight::db();
    $sentencia = $db->query("SELECT * FROM videojuegos");
    $datos = $sentencia->fetchAll();
    
    Flight::json($datos);
});

// GET (Obtener uno por ID)
Flight::route('GET /videojuegos/@id', function($id){
    $db = Flight::db();
    
    // variables de busqueda
    $sql = "SELECT * FROM videojuegos WHERE id = $id";
    $videojuego = $db->query($sql) ->fetch() ;


    if ($videojuego) {
        Flight::json($videojuego);
    } else {
        // Retornar 404 si no existe
        Flight::halt(404, json_encode([
            "error" => "Videojuego no encontrado"
        ]));
    }
});

// POST (Insertar nuevo videojuego)
Flight::route('POST /videojuegos', function(){
    $request = Flight::request();
    $db = Flight::db();

    // Recogemos los encabezados
    $compania = $request->data->compania;
    $consola = $request->data->consola;
    $videojuego = $request->data->videojuego;
    $precio = $request->data->precio;
    $puntuacion = $request->data->puntuacion;

    // Validación 
    if(!$compania || !$consola || !$videojuego) {
        Flight::halt(400, json_encode(["error" => "Faltan datos (compania, consola, videojuego)"]));
    }

        $sql = "INSERT INTO videojuegos (compania, consola, videojuego, precio, puntuacion) VALUES (?, ?, ?, ?, ?)";
        $sentencia = $db->prepare($sql);
        
        $sentencia->execute([$compania, $consola, $videojuego, $precio, $puntuacion]);

        Flight::json([
            "message" => "Videojuego insertado correctamente",
            "id" => $db->lastInsertId()
        ]);

});

//PARTE B (DummyJson)

Flight::route('GET /importar', function() {
    $db = Flight::db();
    
    // 1. Consumir la API externa (Pedimos Laptops)
    $url = 'https://dummyjson.com/products/category/laptops';
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    $insertados = 0;

    // 2. Proceso de mapeo y guardado
    foreach ($data['products'] as $item) {
        
        // mapeo de los datos 

        // La MARCA del portátil pasa a ser la COMPAÑÍA
        $compania = $item['brand']; 
        $consola = "PC Portátil"; 
        // El MODELO del portátil pasa a ser un simulador VIDEOJUEGO 
        $videojuego = "Simulador de " . $item['title'];
        $precio = $item['price']; 
        // El RATING se multiplica por 20 para que sea sobre 100
        $puntuacion = (int)($item['rating'] * 20);

        $sql = "INSERT INTO videojuegos (compania, consola, videojuego, precio, puntuacion) VALUES (?, ?, ?, ?, ?)";
        $post = $db->prepare($sql);
        $post->execute([$compania, $consola, $videojuego, $precio, $puntuacion]);
        
        $insertados++;
    }

    Flight::json([
        "mensaje" => "Importacion completada con exito",
        "registros_insertados" => $insertados,
    ]);
});

// PARTE C

Flight::route('GET /juego-aleatorio', function(){
    
    //URL de la API Freetogame
    $url = "https://www.freetogame.com/api/games?platform=pc";

    $datos = file_get_contents($url);

    //Descargar y convertir JSON
    if ($datos === false) {
            Flight::halt(500, json_encode(["error" => "La API externa no respondió"]));
        }    
    
    $lista_juegos = json_decode($datos, true);

    //variables para elegir juego al azar
    $juego_aleatorio = array_rand($lista_juegos);
    $juego = $lista_juegos[$juego_aleatorio];

    // Devuelve el juego
    Flight::json([
        "servicio" => "Generador de Juego Aleatorio",
        "juego" => $juego
    ]);
});

//PARTE D:

Flight::route('GET /estadisticas', function() {
    $db = Flight::db();
    
    //Guardamos solo Compañía, Puntuación y PRECIO
    $juegos = $db->query("SELECT compania, puntuacion, precio FROM videojuegos")->fetchAll();
 
    // Agrupamos los datos anteriores
    $juegosCompania = [];
    
    foreach ($juegos as $j) {
        $compania = $j['compania'];
        // Guardamos las notas y los precios
        $juegosCompania[$compania]['notas'][]  = (int)$j['puntuacion'];
        $juegosCompania[$compania]['precios'][] = (float)$j['precio'];
    }

    $media = [];

    //recorremos y calculamos las medias con PHP-ML (Mean::arithmetic)
    foreach ($juegosCompania as $compania => $datos) {
        $media[$compania] = [
            "cantidad_juegos" => count($datos['notas']),
            
            "nota_media"      => round(Mean::arithmetic($datos['notas']), 2),   
            "precio_medio"    => round(Mean::arithmetic($datos['precios']), 2) . " €"
        ];
    }

    // Respuesta convertida a JSON
    Flight::json([
        "titulo" => "Datos Estadistico por precios y valoracion",
        "descripcion" => "Calculamos media de juego de cada compania",
        "resultados" => $media
    ]);
});

Flight::start();
?>

