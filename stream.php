<?php
// Verificar si se ha pasado un ID de video
if (!isset($_GET['v'])) {
    die('Error: No video ID provided.');
}

// Obtener el ID de video desde la URL
$video_id = htmlspecialchars($_GET['v']);

// Paso 1: Solicitar el ticket usando el ID del archivo
$login = 'bbeb5f8e5d4c1e0b31c9';  // Login de tu cuenta de Streamtape
$key = '9OQwqwW8B8HoXr';  // Llave API de tu cuenta de Streamtape
$apiUrl = "https://api.streamtape.com/file/dlticket?file=$video_id&login=$login&key=$key";

// Inicializar cURL para obtener el ticket de descarga
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

// Hacer la solicitud a la API de Streamtape
$response = curl_exec($ch);
curl_close($ch);

// Verificar si se obtuvo contenido
if (!$response) {
    die('Error: Unable to fetch the API response.');
}

// Decodificar la respuesta JSON
$data = json_decode($response, true);

// Verificar si se obtuvo el ticket correctamente
if ($data['status'] != 200 || !isset($data['result']['ticket'])) {
    die('Error: Unable to retrieve the download ticket.');
}

// Obtener el ticket y el tiempo de espera
$ticket = $data['result']['ticket'];
$wait_time = $data['result']['wait_time'];

// Esperar el tiempo necesario antes de hacer la siguiente solicitud
sleep($wait_time);

// Paso 2: Obtener el enlace de descarga usando el ticket
$downloadUrl = "https://api.streamtape.com/file/dl?file=$video_id&ticket=$ticket&captcha_response=";

// Inicializar cURL para obtener el enlace de descarga final
$ch = curl_init($downloadUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

// Hacer la solicitud a la API para el enlace de descarga
$downloadResponse = curl_exec($ch);
curl_close($ch);

// Verificar si se obtuvo contenido
if (!$downloadResponse) {
    die('Error: Unable to fetch the download response.');
}

// Decodificar la respuesta JSON
$downloadData = json_decode($downloadResponse, true);

// Verificar si se obtuvo el enlace de descarga correctamente
if ($downloadData['status'] != 200 || !isset($downloadData['result']['url'])) {
    die('Error: Unable to retrieve the download link.');
}

// Obtener el enlace final de descarga
$finalDownloadUrl = $downloadData['result']['url'];

// Paso 3: Descargar el archivo usando el servidor como proxy
$ch = curl_init($finalDownloadUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');

// Obtener las cabeceras del archivo remoto
$headers = get_headers($finalDownloadUrl, 1);
$fileName = isset($headers['Content-Disposition']) ? basename($headers['Content-Disposition']) : 'downloaded_file.mp4';
$fileSize = $headers['Content-Length'];

// Configurar las cabeceras para la descarga
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . $fileSize);

// Descargar y pasar el archivo al cliente
$fp = fopen('php://output', 'w');
curl_setopt($ch, CURLOPT_FILE, $fp);
curl_exec($ch);
curl_close($ch);
fclose($fp);
exit();
?>
