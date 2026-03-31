# LGK FINANCE (PHP + MySQL)

Sistema web financeiro doméstico com design profissional em verde, responsivo e com módulos completos de controle financeiro.

## Requisitos
- PHP 8.1+
- MySQL 8+
- Apache (XAMPP/WAMP/Laragon)

## Instalação local
1. Copie a pasta para o diretório web (ex.: `htdocs/lgkfinance`).
2. Crie o banco e dados iniciais executando `sql/lgk_finance.sql` no phpMyAdmin/MySQL CLI.
3. Ajuste credenciais em `config/config.php`.
4. Garanta permissão de escrita em `uploads/`.
5. Acesse `http://localhost/lgkfinance/login.php`.

## Login padrão
- **E-mail:** `admin@lgkfinance.local`
- **Senha:** `123456`

## Módulos incluídos
- Login/logout com sessão e proteção de rotas.
- Dashboard com resumos, alertas e gráficos.
- CRUD de categorias e responsáveis.
- CRUD/listagem/filtros/paginação em contas a pagar.
- CRUD/listagem/filtros em contas a receber.
- Recorrências com geração automática mensal.
- Parcelamentos com avanço de parcela.
- Relatórios com filtros por período e impressão/PDF.
- Calendário financeiro mensal.
- Gestão de usuários e permissões básicas.
- Logs de ações principais.

## Estrutura
- `config/`: configuração e conexão PDO
- `includes/`: autenticação, layout e helpers
- `modules/`: páginas de cada módulo
- `assets/`: CSS e JS
- `sql/`: script completo do banco
- `uploads/`: anexos
