<?php
$config = new RdKafka\Conf();
$config->set('bootstrap.servers', 'kafka:9092');
$config->set('group.id', 'consumer_orders');
$config->set('auto.offset.reset', 'earliest');

$consumer = new RdKafka\Consumer($config);
$consumer->subscribe(['orders']);

echo "📦 Waiting orders...";

while (true) {
  $msg = $consumer->consume(1000);

  switch ($msg->err) {
    case RD_KAFKA_RESP_ERR_NO_ERROR:
      $pedido = json_decode($msg->payload, true);
      echo "🆕 Pedido recebido: {$pedido['pedido_id']} de {$pedido['cliente']} - {$pedido['produto']} ({$pedido['quantidade']}x) R$ {$pedido['valor']}\n";
      break;

    case RD_KAFKA_RESP_ERR__PARTITION_EOF:
    case RD_KAFKA_RESP_ERR__TIMED_OUT:
      break;

    default:
      echo "❌ Erro: " . rd_kafka_err2str($msg->err) . "\n";
      break;
  }
}