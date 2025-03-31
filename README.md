# API de Colaboradores

Este projeto é uma API REST desenvolvida em Laravel para gerenciamento de colaboradores. Ele permite que gestores cadastrem, editem, listem e excluam colaboradores, além de possibilitar a importação em massa via arquivo CSV e envio de notificações por e-mail.

## Tecnologias Utilizadas
- PHP 8+
- Laravel 10
- SQLite
- JWT Authentication (tymon/jwt-auth)
- PHPUnit
- OpenAPI (Swagger)
- Cache em database/tabela (SQLite)
- Filas em database/tabela (SQLite) - (queue) para processamento assíncrono

## Funcionalidades
- Autenticação via JWT
- CRUD completo de colaboradores
- Upload de CSV para importação em massa
- Notificação por e-mail após importação
- Cache de consultas para melhorar a performance
- Testes unitários e de integração com PHPUnit

## Requisitos
- PHP 8.2+
- Laravel 12+
- SQLite
- Composer e dependências...

## Instalação

1. Clone o repositório:
   ```bash
   git clone https://github.com/uesleirodrigues/teste-convenia.git
   cd seu-repositorio
   ```

2. Instale as dependências do PHP:
   ```bash
   composer install
   ```

3. Copie o arquivo de ambiente e configure-o:
   ```bash
   cp .env.example .env
   ```
   Atualize o `.env` com as credenciais do banco de dados e configurações de e-mail.

4. Gere a chave da aplicação:
   ```bash
   php artisan key:generate
   ```

5. Execute as migrations e seeders:
   ```bash
   php artisan migrate --seed
   ```

6. Configure o JWT:
   ```bash
   php artisan jwt:secret
   ```

7. Inicie o servidor:
   ```bash
   php artisan serve
   ```

7. Inicie o worker para trabalhar na fila de jobs:
   ```bash
   php artisan queue:work
   ```

## Uso da API

### Autenticação
- **Login**: `POST /api/login`
- **Logout**: `POST /api/logout`

### Colaboradores
- **Listar**: `GET /api/collaborators`
- **Criar**: `POST /api/collaborators`
- **Atualizar**: `PUT /api/collaborators/{id}`
- **Excluir**: `DELETE /api/collaborators/{id}`
- **Importar CSV**: `POST /api/collaborators/import`

## Testes
Para rodar os testes unitários e de integração, utilize:
```bash
php artisan test
```
Para visualizar a cobertura de testes:
```bash
php artisan test --coverage
```

## Documentação da API
A API possui documentação via Swagger no link abaixo:
```
http://localhost:8000/api/documentation
```