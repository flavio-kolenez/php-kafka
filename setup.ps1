# Script de setup para ambiente Kafka PHP no Windows

Write-Host "🚀 Configurando ambiente Kafka PHP..." -ForegroundColor Green

function Test-Docker {
    try {
        docker info | Out-Null
        Write-Host "✅ Docker está rodando" -ForegroundColor Green
        return $true
    }
    catch {
        Write-Host "❌ Docker não está rodando. Por favor, inicie o Docker primeiro." -ForegroundColor Red
        exit 1
    }
}

function Start-Environment {
    Write-Host "🏗️ Construindo e iniciando containers..." -ForegroundColor Yellow
    
    try {
        docker-compose -f docker-compose-php.yaml up --build -d
        Write-Host "✅ Containers iniciados com sucesso" -ForegroundColor Green
    }
    catch {
        Write-Host "❌ Erro ao iniciar containers" -ForegroundColor Red
        exit 1
    }
}

function Wait-ForKafka {
    Write-Host "⏳ Aguardando Kafka inicializar..." -ForegroundColor Yellow
    
    for ($i = 1; $i -le 30; $i++) {
        try {
            docker exec kafka kafka-topics --bootstrap-server localhost:9092 --list 2>$null | Out-Null
            Write-Host "✅ Kafka está pronto!" -ForegroundColor Green
            return
        }
        catch {
            Write-Host "Tentativa $i/30..." -ForegroundColor Gray
            Start-Sleep -Seconds 2
        }
    }
    
    Write-Host "❌ Timeout aguardando Kafka" -ForegroundColor Red
    exit 1
}

function Install-PhpDependencies {
    Write-Host "📦 Instalando dependências PHP..." -ForegroundColor Yellow
    
    try {
        docker exec php-kafka-dev composer install
        Write-Host "✅ Dependências PHP instaladas" -ForegroundColor Green
    }
    catch {
        Write-Host "❌ Erro ao instalar dependências PHP" -ForegroundColor Red
        Write-Host "💡 Tentando novamente..." -ForegroundColor Yellow
        docker exec php-kafka-dev composer install --no-dev
    }
}

function Test-Environment {
    Write-Host "🧪 Testando ambiente..." -ForegroundColor Yellow
    
    try {
        $result = docker exec php-kafka-dev php -m | Select-String "rdkafka"
        if ($result) {
            Write-Host "✅ Extensão rdkafka está carregada" -ForegroundColor Green
        } else {
            Write-Host "❌ Extensão rdkafka não encontrada" -ForegroundColor Red
        }
    }
    catch {
        Write-Host "⚠️ Não foi possível verificar a extensão rdkafka" -ForegroundColor Yellow
    }
}

function Main {
    Write-Host "============================================" -ForegroundColor Cyan
    Write-Host "     KAFKA PHP DEVELOPMENT SETUP" -ForegroundColor Cyan
    Write-Host "============================================" -ForegroundColor Cyan
    
    Test-Docker
    Start-Environment
    Wait-ForKafka
    Install-PhpDependencies
    Test-Environment
    
    Write-Host ""
    Write-Host "🎉 Ambiente configurado com sucesso!" -ForegroundColor Green
    Write-Host ""
    Write-Host "📋 Próximos passos:" -ForegroundColor Cyan
    Write-Host "1. Para executar o producer:" -ForegroundColor White
    Write-Host "   docker exec -it php-kafka-dev php producer.php" -ForegroundColor Gray
    Write-Host ""
    Write-Host "2. Para executar o consumer (em outro terminal):" -ForegroundColor White
    Write-Host "   docker exec -it php-kafka-dev php consumer.php" -ForegroundColor Gray
    Write-Host ""
    Write-Host "3. Para acessar o container PHP:" -ForegroundColor White
    Write-Host "   docker exec -it php-kafka-dev bash" -ForegroundColor Gray
    Write-Host ""
    Write-Host "4. Para parar o ambiente:" -ForegroundColor White
    Write-Host "   docker-compose -f docker-compose-php.yaml down" -ForegroundColor Gray
    Write-Host ""
    Write-Host "📚 Consulte o README.md para mais informações" -ForegroundColor Cyan
}

# Executar função principal
Main