<?php
require '../vendor/autoload.php';

Flight::route('/', function () {
   echo '¡Hola, Flight!';
});

Flight::start();
