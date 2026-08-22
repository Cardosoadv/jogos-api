# Relatório de Evolução do Sistema

**Versão Atual do Sistema**: `1.4.0`
**Data**: 2026-08-22

---

## Histórico de Alterações e Versões

### [v1.4.0] - 2026-08-22
#### Adicionado:
- **Módulo de Jogos (CRUD completo com imagem de capa)**:
  - Criada a tabela `games` (migration `2026-08-22-210000_CreateGamesTable`) com `title`, `description`, `category`, `cover_image`, `active` e timestamps.
  - Criados `App\Entities\Game`, `App\Models\GameModel` (com validação de `title`, `description`, `category`, `cover_image`, `active`), `App\Repositories\GameRepository` e `App\Services\GameService` (validação de payload com mensagens de erro por campo em `create`/`update`).
  - Criado `App\Controllers\Api\Game` expondo em `/api/games`:
    - `GET /api/games` e `GET /api/games/(:id)` — catálogo **público**, sem autenticação.
    - `POST /api/games`, `PUT`/`PATCH /api/games/(:id)`, `DELETE /api/games/(:id)` e `POST /api/games/(:id)/cover` — **restritos a administradores**.
  - **Controle de acesso**: adicionada a permissão `games.manage` em `app/Config/AuthGroups.php`, concedida aos grupos `admin` e `superadmin` (via `games.*`). O controller verifica `auth()->user()->can('games.manage')` e responde `403 Forbidden` para jogadores comuns.
  - Upload de imagem de capa segue o mesmo padrão do avatar do jogador: `multipart/form-data`, validação de tipo/tamanho (`is_image`, `mime_in`, `max_size[2048]`) e armazenamento em `public/uploads/games/`.
  - Adicionados arquivos de idioma `Game.php` em `en`, `pt-BR` e `es`.
  - Validado manualmente o fluxo completo (registro de jogador, promoção a admin, geração de token de acesso, criação/edição/upload de capa/exclusão) e a suíte PHPUnit (`vendor/bin/phpunit`) permanece verde.
- **Correção**:
  - Corrigido bug nas rotas de `api/players` em `app/Routes/Routes.php`: as rotas referenciavam o controller inexistente `PlayerController` quando a classe real é `App\Controllers\Api\Player`, o que quebrava todo o módulo de Jogadores em tempo de execução. Rotas corrigidas para `Player::*`.
- **Configurações do Sistema**:
  - Atualizada a versão do sistema em `composer.json`, `README.md` e `relatorio_evolucao.md` para `1.4.0`.

### [v1.3.1] - 2026-08-22
#### Adicionado / Alterado:
- **Tradução da Documentação (i18n)**:
  - Traduzido o arquivo [README.md](file:///d:/codes/xampp/jogos/api/README.md) integralmente para o inglês (Game Portal RESTful API), mantendo alinhamento com padrões internacionais de documentação.
- **Configurações do Sistema**:
  - Atualizada a versão do sistema em `composer.json`, `README.md` e `relatorio_evolucao.md` para `1.3.1`.

### [v1.3.0] - 2026-08-22
#### Adicionado / Alterado:
- **Documentação e Metadados do Projeto**:
  - Reestruturado completamente o [README.md](file:///d:/codes/xampp/jogos/api/README.md) detalhando os objetivos principais do **Portal de Jogos RESTful API** (gestão de jogadores, catálogo de jogos, carteira/transações, leaderboards, sessões de jogo e i18n), arquitetura em 4 camadas (`Controllers -> Services -> Repositories -> Models`), autenticação com CodeIgniter Shield, padronização de respostas JSON e instruções de setup.
  - Atualizado o [composer.json](file:///d:/codes/xampp/jogos/api/composer.json) definindo o nome oficial do pacote (`portal-jogos/api`), descrição alinhada ao propósito do motor backend e palavras-chave de identificação.
- **Configurações do Sistema**:
  - Atualizada a versão do sistema em `composer.json`, `README.md` e `relatorio_evolucao.md` para `1.3.0`.

### [v1.2.0] - 2026-08-22
#### Adicionado / Alterado:
- **Internacionalização / Multi-idiomas (i18n)**:
  - Substituídas as mensagens em string fixa em [BaseService.php](file:///d:/codes/xampp/jogos/api/app/Services/BaseService.php) pelo helper nativo `lang()` do CodeIgniter 4.
  - Criados arquivos de tradução em `app/Language/`:
    - `app/Language/en/BaseService.php` (Inglês)
    - `app/Language/pt-BR/BaseService.php` (Português do Brasil)
    - `app/Language/es/BaseService.php` (Espanhol)
  - Ativada a negociação de locale (`$negotiateLocale = true`) e configurados idiomas suportados (`['en', 'pt-BR', 'es']`) em [App.php](file:///d:/codes/xampp/jogos/api/app/Config/App.php).
- **Configurações do Sistema**:
  - Atualizada a versão do sistema em `composer.json`, `README.md` e `relatorio_evolucao.md` para `1.2.0`.

### [v1.1.0] - 2026-08-22
#### Alterado / Corrigido:
- **Skill / Arquitetura `.agents/DesignPatterns.md` (v1.1.0)**:
  - Ajustado o modelo de autenticação para **Autenticação Baseada em Sessão (Stateful)** usando o autenticador de sessão do CodeIgniter Shield (`session`).
  - Esclarecida a diferenciação entre autenticação por sessão (Stateful) e autenticação por tokens (Stateless).
- **Configurações do Sistema**:
  - Atualizada a versão do sistema em `composer.json` e `README.md` para `1.1.0`.

### [v1.0.0] - 2026-08-22
#### Adicionado / Atualizado:
- **Skill / Arquitetura `.agents/DesignPatterns.md` (v1.0.0)**:
  - Definidas as diretrizes de arquitetura para o projeto **Portal de Jogos RESTful API**.
  - Autenticação e controle de acesso dos jogadores delegados estritamente ao **CodeIgniter Shield** (Tokens REST / JWT).
  - Estabelecido o padrão de arquitetura em camadas (**Controllers -> Services -> Repositories -> Models**):
    - **Controllers**: Manipulação de requisições HTTP, validação inicial e formatação de respostas JSON padronizadas.
    - **Services**: Concentração de todas as regras de negócio e lógica do domínio de jogos.
    - **Repositories**: Abstração de banco de dados, consultas complexas e gerenciamento de transações.
    - **Models**: Abstração e representação declarativa de tabelas do banco de dados (CodeIgniter Models).
  - Padronização das respostas JSON da API (`status`, `message`, `data`, `errors`, `meta`).
- **Configurações do Sistema**:
  - Atualizada a versão do sistema em `composer.json` para `1.0.0`.
  - Atualizada a versão e documentação inicial em `README.md` para `v1.0.0`.
