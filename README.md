# Game Portal - RESTful API

**System Version**: `1.3.1`

## 🎯 Project Goals & Objectives

The **Game Portal RESTful API** is a high-performance backend engine built on top of the **CodeIgniter 4** framework. It serves as the core infrastructure for an online gaming portal.

The primary goal is to provide a secure, scalable, and modular ecosystem to manage all essential gaming portal operations, including:
- **Player Authentication & Access Management**: Player registration, authentication, profile management, and secure session handling via **CodeIgniter Shield**.
- **Game Catalog Management**: Organization, categorization, and listing of available games.
- **Game Sessions & Matchmaking**: Initialization and tracking of active game sessions and player matches.
- **Wallet & Transactions**: Management of player balances, deposits, withdrawals, and in-game reward distribution.
- **Leaderboards & Rankings**: Player scores, gameplay statistics, and global/per-game leaderboards.
- **Internationalization (i18n)**: Native multi-language support (`en`, `pt-BR`, `es`) for system responses and messages.

---

## 🏗️ System Architecture

This project strictly adheres to a 4-layered architecture (**Controllers -> Services -> Repositories -> Models**) to ensure full separation of concerns, testability, and maintainability:

```
[ HTTP Request ]
       │
       ▼
[ Controllers ]  ──► Payload Validation, Shield Auth & Standard JSON Response
       │
       ▼
[   Services  ]  ──► Core Business Logic, Gameplay Calculations, Wallet & Rewards
       │
       ▼
[ Repositories]  ──► Data Access, Complex Database Queries & DB Transactions
       │
       ▼
[    Models   ]  ──► Declarative Schema & Database Entity Abstraction
```

For detailed architectural guidelines and coding standards, refer to [.agents/DesignPatterns.md](file:///d:/codes/xampp/jogos/api/.agents/DesignPatterns.md).

---

## 🔒 Authentication & Security

Security and player access control are natively handled by **CodeIgniter Shield**:
- **Stateful Session Authentication**: Server-side session persistence protecting secure endpoints via the `session` filter.
- **Route Filters**: Protection of sensitive API routes using authentication middleware.
- **Standardized Error Responses**: Internationalized, user-friendly authorization and access denial messages.

---

## 🌐 Standardized HTTP / JSON Response Format

All API endpoints return a unified JSON response structure:

### Success Response (`HTTP 200 / 201`)
```json
{
  "status": "success",
  "message": "Player profile retrieved successfully.",
  "data": {
    "id": 1024,
    "username": "shadow_gamer",
    "email": "player@portal.com",
    "level": 15,
    "xp": 4500
  },
  "meta": {
    "timestamp": "2026-08-22T15:48:00-03:00"
  }
}
```

### Error Response (`HTTP 400 / 401 / 422 / 500`)
```json
{
  "status": "error",
  "message": "Validation failed.",
  "errors": {
    "email": "The email field must contain a valid email address."
  },
  "meta": {
    "timestamp": "2026-08-22T15:48:00-03:00"
  }
}
```

---

## 🛠️ Tech Stack & Requirements

- **PHP**: `^8.2`
- **Framework**: CodeIgniter `^4.7`
- **Authentication**: CodeIgniter Shield `^1.4`
- **Database**: MySQL / MariaDB
- **Required PHP Extensions**: `intl`, `mbstring`, `json`, `mysqlnd`, `curl`

---

## 🚀 Installation & Setup

1. **Clone the Repository & Install Dependencies**:
   ```bash
   composer install
   ```

2. **Environment Configuration**:
   Copy the template file `env` to `.env` and set your database and base URL credentials:
   ```bash
   cp env .env
   ```
   Adjust settings inside `.env`:
   ```ini
   app.baseURL = 'http://localhost:8080/'
   database.default.hostname = localhost
   database.default.database = portal_jogos
   database.default.username = root
   database.default.password = [PASSWORD]
   database.default.DBDriver = MySQLi
   ```

3. **Run Database Migrations**:
   ```bash
   php spark migrate
   ```

4. **Start Local Development Server**:
   ```bash
   php spark serve
   ```

---

## 📈 System Evolution & Version History

System updates, feature releases, and version tracking (SemVer) are maintained in [.agents/relatorio_evolucao.md](file:///d:/codes/xampp/jogos/api/.agents/relatorio_evolucao.md).


