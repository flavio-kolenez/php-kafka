<?php

/**
 * Consumer PHP equivalente ao tracker.py
 * Usa a extensão rdkafka para PHP
 */

// Configuração do consumer usando High Level API (mais moderna)
$consumerConfig = new RdKafka\Conf();
$consumerConfig->set('bootstrap.servers', 'kafka:9092');
$consumerConfig->set('group.id', 'order_tracker_php');
$consumerConfig->set('auto.offset.reset', 'earliest');

// Callback para rebalanceamento de partições
$consumerConfig->setRebalanceCb(function (RdKafka\KafkaConsumer $kafka, $err, array $partitions = null) {
    switch ($err) {
        case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
            echo "📋 Atribuído às partições\n";
            $kafka->assign($partitions);
            break;

        case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
            echo "🔄 Partições revogadas\n";
            $kafka->assign(NULL);
            break;

        default:
            echo "❌ Erro de rebalanceamento: " . rd_kafka_err2str($err) . "\n";
            $kafka->assign(NULL);
            break;
    }
});

// Callback para erros
$consumerConfig->setErrorCb(function ($kafka, $err, $reason) {
    echo "❌ Erro: " . rd_kafka_err2str($err) . " - $reason\n";
});

// Criar consumer
$consumer = new RdKafka\KafkaConsumer($consumerConfig);

// Subscrever ao tópico 'vendas'
$consumer->subscribe(['vendas']);

echo "🔍 Consumidor iniciado, aguardando mensagens...\n";

$running = true;

try {
    while ($running) {
        // Consumir mensagem com timeout de 1 segundo
        $message = $consumer->consume(1000);
        
        switch ($message->err) {
            case RD_KAFKA_RESP_ERR_NO_ERROR:
                $value = $message->payload;
                $order = json_decode($value, true);
                
                if ($order) {
                    echo "Pedido recebido: ID={$order['order_id']}, Usuário={$order['user']}, Item={$order['item']}, Quantidade={$order['quantity']}, Preço={$order['price']}\n";
                } else {
                    echo "⚠️ Erro ao decodificar JSON: $value\n";
                }
                break;
                
            case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                // Fim da partição, continuar aguardando
                break;
                
            case RD_KAFKA_RESP_ERR__TIMED_OUT:
                // Timeout, continuar aguardando
                break;
                
            default:
                echo "Erro no consumidor: " . rd_kafka_err2str($message->err) . "\n";
                $running = false;
                break;
        }
        
        // Verificar se foi solicitada interrupção (simulação do KeyboardInterrupt)
        // Em um ambiente real, você pode usar signals ou outros mecanismos
        if (connection_aborted()) {
            $running = false;
        }
    }
} catch (Exception $e) {
    echo "Interrompido pelo usuário 😅\n";
} finally {
    echo "🛑 Fechando consumer...\n";
    $consumer->close();
}

?>