# Kafka PHP Development Environment

Este projeto configura um ambiente de desenvolvimento PHP com Apache Kafka usando as bibliotecas `rdkafka` e `librdkafka`.

## 📋 Arquivos Convertidos

- **producer.py** → **producer.php** - Producer de mensagens Kafka
- **tracker.py** → **consumer.php** - Consumer de mensagens Kafka

## 🚀 Como usar

### 1. Construir e iniciar os containers

```powershell
# No Windows PowerShell
docker-compose -f docker-compose-php.yaml up --build -d
```

### 2. Verificar se os containers estão rodando

```powershell
docker-compose -f docker-compose-php.yaml ps
```

### 3. Instalar dependências PHP

```powershell
# Acessar o container PHP
docker exec -it php-kafka-dev bash

# Dentro do container, instalar dependências
composer install
```

### 4. Executar o producer

```bash
# Dentro do container PHP
php producer.php
```

### 5. Executar o consumer (em outro terminal)

```powershell
# Abrir novo terminal PowerShell e acessar o container
docker exec -it php-kafka-dev bash

# Executar o consumer
php consumer.php
```

## 📁 Estrutura do projeto

```
├── docker-compose-php.yaml  # Configuração Docker com PHP e Kafka
├── Dockerfile.php           # Dockerfile para container PHP com rdkafka
├── composer.json           # Dependências PHP
├── producer.php            # Producer PHP (equivalente ao producer.py)
├── consumer.php            # Consumer PHP (equivalente ao tracker.py)
└── README.md               # Este arquivo
```

## 🔧 Bibliotecas incluídas

- **librdkafka**: Biblioteca C nativa para Kafka (v2.3.0)
- **rdkafka**: Extensão PHP para librdkafka
- **Composer**: Gerenciador de dependências PHP

## 📊 Funcionalidades

### Producer (producer.php)
- Gera 10 pedidos aleatórios
- Envia mensagens para o tópico 'vendas'
- Callback de confirmação de entrega
- UUID único para cada pedido
- **Diferenças do Python**: Usa `RdKafka\Producer` em vez de `confluent_kafka.Producer`

### Consumer (consumer.php)
- Consome mensagens do tópico 'vendas'
- Group ID: 'order_tracker_php'
- High Level API com rebalanceamento automático
- **Diferenças do Python**: Usa `RdKafka\KafkaConsumer` em vez de `confluent_kafka.Consumer`

## 🔄 Principais diferenças Python vs PHP

| Aspecto | Python | PHP |
|---------|--------|-----|
| Biblioteca | `confluent-kafka` | `rdkafka` (extensão) |
| Producer | `confluent_kafka.Producer` | `RdKafka\Producer` |
| Consumer | `confluent_kafka.Consumer` | `RdKafka\KafkaConsumer` |
| Config | `dict` | `RdKafka\Conf` |
| Callback | Função direta | `setDrMsgCb()` |
| UUID | `uuid.uuid4()` | Função customizada |
| JSON | `json.dumps/loads` | `json_encode/decode` |

## 🐛 Debugging

### Verificar se rdkafka está carregado
```bash
# Dentro do container
php -m | grep rdkafka
```

### Verificar versão da librdkafka
```bash
# Dentro do container
php -r "echo 'librdkafka version: ' . rd_kafka_version_str() . PHP_EOL;"
```

### Verificar logs do Kafka
```powershell
docker logs kafka
```

### Listar tópicos do Kafka
```powershell
docker exec kafka kafka-topics --bootstrap-server localhost:9092 --list
```

### Verificar mensagens no tópico
```powershell
docker exec kafka kafka-console-consumer --bootstrap-server localhost:9092 --topic vendas --from-beginning
```

## 🛑 Parar o ambiente

```powershell
docker-compose -f docker-compose-php.yaml down
```

## 💡 Dicas de desenvolvimento

1. **Primeira execução**: Aguarde alguns segundos após `docker-compose up` para o Kafka inicializar
2. **Performance**: O librdkafka é otimizado para alta performance e baixa latência
3. **Desenvolvimento**: O diretório atual é montado no container para desenvolvimento em tempo real
4. **Erros de lint**: Os erros de lint no VS Code são normais - as classes/constantes só existem no container

## 🚀 Próximos passos

Para testar a conversão:

1. Execute o ambiente: `docker-compose -f docker-compose-php.yaml up -d`
2. Acesse o container: `docker exec -it php-kafka-dev bash`
3. Execute o producer: `php producer.php`
4. Em outro terminal, execute o consumer: `php consumer.php`
5. Verifique se as mensagens são enviadas e recebidas corretamente

## 🆘 Troubleshooting

### Container não inicia
- Verifique se o Docker está rodando
- Verifique se as portas 9092 não estão em uso

### Erro de conexão Kafka
- Verifique se o container kafka está rodando: `docker ps`
- Aguarde alguns segundos para o Kafka inicializar completamente

### Extensão rdkafka não encontrada
- Reconstrua o container: `docker-compose -f docker-compose-php.yaml up --build`