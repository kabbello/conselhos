-- Cria o banco de dados para testes automatizados.
-- Executado automaticamente pelo MySQL na inicialização do container.
--
-- O banco de produção (conselhos) é criado via MYSQL_DATABASE no docker-compose.
-- Este script cria o banco de testes (conselhos_test) e concede permissões ao usuário da app.

CREATE DATABASE IF NOT EXISTS `conselhos_test`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON `conselhos_test`.* TO 'conselhos'@'%';

FLUSH PRIVILEGES;
