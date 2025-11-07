<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Not allowed method']);
    exit;
}

$input_raw = file_get_contents('php://input');
$input = json_decode($input_raw, true);

if (!$input || !isset($input['client'], $input['product'], $input['qty'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data!', 'raw' => $input_raw]);
    exit;
}

try {
    $config = new RdKafka\Conf();
    $config->set('bootstrap.servers', 'kafka:9092');
    $producer = new RdKafka\Producer($config);
    $topic = $producer->newTopic('orders');

    $order = [
        'order_id' => uuidv4(),
        'client' => $input['client'],
        'product' => $input['product'],
        'qty' => intval($input['qty']),
        'value' => round(rand(10000, 200000) / 100, 2),
        'date' => date('d-m-Y')
    ];

    $topic->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($order));
    $producer->flush(10000);

    echo json_encode([
        "status" => "success",
        "message" => "Pedido enviado com sucesso!",
        "order" => $order
    ]);
} catch (Exception $e) {
    echo json_encode([
        "error" => "Falha ao enviar pedido: " . $e->getMessage()
    ]);
}
function uuidv4()
{
    $data = random_bytes(16);
    $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
    $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}
