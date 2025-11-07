<?php

require_once('utils.php');

/**
 * Producer PHP equivalente ao producer.py
 * Usa a extensão rdkafka para PHP
 */



// Configuração do producer
$producerConfig = new RdKafka\Conf();
$producerConfig->set('bootstrap.servers', 'kafka:9092');

// Callback para relatório de entrega
$producerConfig->setDrMsgCb(function ($kafka, $message) {
    if ($message->err) {
        echo "Mensagem de entrega falhou: " . rd_kafka_err2str($message->err) . "\n";
    } else {
        $payload = json_decode($message->payload, true);
        echo "🎁 Mensagem entregue para {$message->topic_name} [ID: {$payload['order_id']}]\n";
        echo "Offset: {$message->offset}\n";
    }
});

// Criar producer
$producer = new RdKafka\Producer($producerConfig);

// Obter o tópico
$topic = $producer->newTopic('vendas');

// Listas para gerar dados aleatórios
$users = ['Cesar', 'Ana', 'João', 'Maria', 'Pedro', 'Julia', 'Carlos', 'Lucia'];
$items = ['notebook', 'mouse', 'teclado', 'monitor', 'headset', 'webcam', 'smartphone', 'tablet'];

echo "🚀 Iniciando producer PHP...\n\n";

// Loop para gerar 10 mensagens aleatórias
for ($i = 0; $i < 10; $i++) {
    $order = [
        'order_id' => generateUuid(),
        'user' => $users[array_rand($users)],
        'item' => $items[array_rand($items)],
        'quantity' => rand(1, 20),
        'price' => round(rand(5000, 200000) / 100, 2) // Gera preço entre 50.00 e 2000.00
    ];
    
    $value = json_encode($order);
    
    echo "📤 Enviando pedido " . ($i + 1) . "/10...\n";
    
    // Produzir mensagem
    $topic->produce(RD_KAFKA_PARTITION_UA, 0, $value);
    
    // Poll para processar callbacks
    $producer->poll(0);
    
    // Pequena pausa para demonstração
    usleep(500000); // 0.5 segundos
}

echo "\n⏳ Aguardando entrega de todas as mensagens...\n";

// Flush para garantir que todas as mensagens sejam enviadas
$producer->flush(10000); // 10 segundos timeout

echo "✅ Todas as mensagens foram processadas!\n";

?>