<?php
/******************************
 * Supermercado Maria Bot
 * PHP + Telegram Webhook
 * Con logs para Render
 ******************************/

// ====== CONFIG ======
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Usa variable de entorno TELEGRAM_TOKEN si existe; si no, la constante de abajo
define('TOKEN', getenv('TELEGRAM_TOKEN') ?: '8068279759:AAHEByyxqmaHapKC40Eqmi_Ux6OyncaScBY');
$API = "https://api.telegram.org/bot" . TOKEN;

// Health check simple (útil para probar desde el navegador)
if (isset($_GET['ping']) || isset($_GET['health'])) {
    header('Content-Type: text/plain; charset=UTF-8');
    echo "alive";
    exit;
}

// ====== LECTURA DE UPDATE ======
$raw = file_get_contents("php://input");
error_log("Entrada recibida: " . $raw);

$update = json_decode($raw, true);
if (!$update || !isset($update['message'])) {
    // No hay mensaje; evita warnings cuando Telegram prueba el webhook
    error_log("Sin 'message' en el update. Fin.");
    http_response_code(200);
    exit;
}

$message  = $update['message'];
$chat_id  = $message['chat']['id'] ?? null;
$text     = $message['text'] ?? '';
$textNorm = mb_strtolower(trim($text), 'UTF-8');

// ====== LÓGICA DEL BOT ======
// Mapeo de productos -> pasillos
$pasillos = [
    "carne"      => 1, "queso"    => 1, "jamon"     => 1,
    "leche"      => 2, "yogurth"  => 2, "yogurt"    => 2, "cereal" => 2,
    "bebidas"    => 3, "jugos"    => 3,
    "pan"        => 4, "pasteles" => 4, "tortas"    => 4,
    "detergente" => 5, "lavaloza" => 5
];

$respuesta = "Lo siento, no entiendo la pregunta.";

// Mensaje de bienvenida
if ($textNorm === "/start") {
    $respuesta = "¡Hola! Soy el bot del supermercado 🛒.\n".
                 "Escríbeme el nombre de un producto (por ejemplo: leche, pan, detergente) y te diré en qué pasillo está.";
} else {
    // Buscar cualquier palabra del catálogo dentro del texto
    foreach ($pasillos as $producto => $pasillo) {
        if (mb_strpos($textNorm, $producto, 0, 'UTF-8') !== false) {
            $respuesta = "El producto '$producto' se encuentra en el pasillo $pasillo.";
            break;
        }
    }
}

// ====== ENVÍO DE RESPUESTA ======
if ($chat_id) {
    $payload = [
        'chat_id' => $chat_id,
        'text'    => $respuesta
    ];

    $ch = curl_init("$API/sendMessage");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result = curl_exec($ch);
    $err    = curl_error($ch);
    curl_close($ch);

    error_log("Respuesta enviada: " . $respuesta);
    if ($err) {
        error_log("Error cURL: " . $err);
    } else {
        error_log("sendMessage OK: " . $result);
    }
}

// Importante devolver 200 para que Telegram no reintente
http_response_code(200);
echo "OK";
