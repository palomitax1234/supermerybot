<?php
$TOKEN = "8068279759:AAH6xuza5EYgecewXcVWoKG0W-vvf0aMKtQ";
$API   = "https://api.telegram.org/bot$TOKEN/";

// Leer update de Telegram
$update = json_decode(file_get_contents("php://input"), true);
if(!$update || !isset($update["message"])) { http_response_code(200); exit; }

$message = $update["message"];
$chat_id = $message["chat"]["id"];
$text    = strtolower($message["text"]);

// Diccionario productos → pasillos
$pasillos = [
    "carne" => 1, "queso" => 1, "jamon" => 1,
    "leche" => 2, "yogurth" => 2, "cereal" => 2,
    "bebidas" => 3, "jugos" => 3,
    "pan" => 4, "pasteles" => 4, "tortas" => 4,
    "detergente" => 5, "lavaloza" => 5
];

// Lógica
$respuesta = "Lo siento, no entiendo la pregunta.";
foreach ($pasillos as $producto => $pasillo) {
    if (strpos($text, $producto) !== false) {
        $respuesta = "El producto '$producto' se encuentra en el pasillo $pasillo.";
        break;
    }
}
if ($text == "/start") {
    $respuesta = "¡Hola! Soy el bot del supermercado 🛒. Escríbeme un producto y te diré en qué pasillo está.";
}

// Enviar respuesta con cURL
$ch = curl_init($API."sendMessage");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, ['chat_id'=>$chat_id, 'text'=>$respuesta]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_exec($ch);
curl_close($ch);

http_response_code(200);
exit;
