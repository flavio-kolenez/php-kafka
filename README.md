# Kafka PHP Development Environment with Frontend

Este projeto configura um ambiente de desenvolvimento PHP com Apache Kafka usando as bibliotecas `rdkafka` e `librdkafka`, incluindo um frontend para criação de pedidos.

## 📋 Sobre o Projeto

O projeto consiste em:
- **Frontend**: Interface web para criação de pedidos (HTML/CSS/JS)
- **API**: Endpoint PHP que recebe requisições do frontend (`src/api/index.php`)
- **Producer**: Envia mensagens para o Kafka quando pedidos são criados
- **Consumer**: Processa mensagens do Kafka em tempo real

## 🚀 Inicializando o Ambiente

```powershell
# Construir e iniciar os containers
docker-compose -f docker-compose-php.yaml up --build -d

# Verificar se os containers estão rodando
docker-compose -f docker-compose-php.yaml ps
```

## 📁 Estrutura do projeto

```
├── docker-compose-php.yaml  # Configuração Docker com PHP e Kafka
├── Dockerfile.php           # Dockerfile para container PHP com rdkafka
├── composer.json            # Dependências PHP
├── src/
│   ├── index.html           # Frontend para criação de pedidos
│   ├── api/
│   │   └── index.php        # API endpoint que recebe requisições do frontend
│   ├── producer.php         # Producer Kafka
│   └── consumer.php         # Consumer Kafka
└── README.md                # Este arquivo
```

## 🔄 Fluxo do Sistema

1. **Frontend** (`src/index.html`) - Usuário preenche formulário de pedido
2. **Fetch API** - Frontend faz requisição POST para `src/api/index.php`
3. **API Endpoint** - Recebe dados e chama o producer Kafka
4. **Producer** - Envia mensagem para tópico 'vendas'
5. **Consumer** - Processa mensagem em tempo real

## 🖥️ Configuração de Terminais

Para o projeto funcionar completamente, você precisa de **2 terminais**:

**Terminal 1 - Consumer (Processamento)**
```bash
docker exec -it php-kafka-dev bash
php src/consumer.php
# Deixe este terminal rodando para processar mensagens
```

**Terminal 2 - Servidor Web (API + Frontend)**
```bash
docker exec -it php-kafka-dev bash
php -S 0.0.0.0:8080 -t /var/www/html
# Deixe este terminal rodando para servir a aplicação
```

## 📊 Funcionalidades

### Frontend (index.html)
- Interface para criar pedidos com cliente, produto e quantidade
- Validação de formulário
- Feedback visual de sucesso/erro
- Requisições AJAX usando `fetch()` para `src/api/index.php`

### API (src/api/index.php)
- Recebe requisições POST do frontend
- Valida dados do pedido
- Chama o producer para enviar ao Kafka
- Retorna resposta JSON para o frontend

### Producer (src/producer.php)
- Envia mensagens para o tópico 'vendas'
- Callback de confirmação de entrega
- UUID único para cada pedido

### Consumer (src/consumer.php)
- Consome mensagens do tópico 'vendas' em tempo real
- Group ID: 'order_tracker_php'
- Processa pedidos automaticamente

## 🔧 Bibliotecas incluídas

- **librdkafka**: Biblioteca C nativa para Kafka (v2.3.0)
- **rdkafka**: Extensão PHP para librdkafka
- **Composer**: Gerenciador de dependências PHP

## 🐛 Debugging

### Verificar se rdkafka está carregado
```bash
php -m | grep rdkafka
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

## 💡 Testando o Sistema Completo

1. **Inicie o ambiente**: `docker-compose -f docker-compose-php.yaml up -d`
2. **Terminal 1**: Execute o consumer e deixe rodando
3. **Terminal 2**: Execute o servidor web e deixe rodando  
4. **Acesse**: `http://localhost:8080/src/index.html`
5. **Teste**: Crie um pedido no frontend e veja o processamento no Terminal 1

## 🆘 Troubleshooting

### Frontend não carrega
- Verifique se o servidor PHP está rodando no Terminal 2
- Acesse: `http://localhost:8080/src/index.html`

### Erro de conexão na API
- Verifique se o consumer está rodando no Terminal 1
- Verifique logs do container: `docker logs php-kafka-dev`

### Container não inicia
- Verifique se o Docker está rodando
- Verifique se as portas 8080 e 9092 não estão em uso