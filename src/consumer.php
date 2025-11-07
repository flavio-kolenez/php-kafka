<?php

$consumerConfig = new RdKafka\Conf();
$consumerConfig->set('bootstrap.servers', 'kafka:9092');
$consumerConfig->set('group.id', 'order_tracker_php');
$consumerConfig->set('auto.offset.reset', 'earliest');

$consumer = new RdKafka\KafkaConsumer($consumerConfig);
$consumer->subscribe(['orders']);

echo 'Waiting new orders...';

while (true) {
    $message = $consumer->consume(1000); // espera até 1 segundo por mensagem

    switch ($message->err) {
        case RD_KAFKA_RESP_ERR_NO_ERROR:
            $order = json_decode($message->payload, true);

            echo "\n📦 Novo pedido recebido!\n";
            echo "ID: {$order['order_id']}\n";
            echo "Cliente: {$order['client']}\n";
            echo "Produto: {$order['product']}\n";
            echo "Quantidade: {$order['qty']}\n";
            echo "Valor: R$ {$order['value']}\n";
            echo "Data: {$order['date']}\n";
            break;

        case RD_KAFKA_RESP_ERR__PARTITION_EOF:
            // Fim da partição, só ignora
            break;

        case RD_KAFKA_RESP_ERR__TIMED_OUT:
            // Timeout, volta a tentar
            break;

        default:
            echo "Erro no consumer: " . rd_kafka_err2str($message->err) . "\n";
            break;
    }
}