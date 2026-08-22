<!-- 
  Skill / Architecture Documentation: Game Portal RESTful API
  Version: 1.1.0
  Date: 2026-08-22
  Framework: CodeIgniter 4
-->

# Game Portal RESTful API - Design Patterns & Architectural Guidelines

## 1. Overview & System Scope
This project is a high-performance **Game Portal RESTful API** built on CodeIgniter 4. It serves as the backend engine for managing player authentication, game catalog management, player profiles, wallet/transactions, leaderboards, and game sessions.

Strict separation of concerns, scalability, maintainability, and security are enforced through a layered architecture pattern: **Controllers -> Services -> Repositories -> Models**.

---

## 2. Authentication & Security (CodeIgniter Shield)
Player authentication, access management, and security controls are strictly managed by **CodeIgniter Shield** using **Session-Based Authentication**.

### Guidelines:
- **Stateful Session Authentication**: Authentication for players relies on server-side session persistence managed by CodeIgniter Shield (`session` authenticator). Because session state is stored on the server (in database, files, or Redis memory), this is a **Stateful** authentication model.
- **Middleware & Route Filters**: All protected endpoints must be secured using Shield's session route filter (`filter('session')` or `filter('auth')`).
- **Player Identity**: Controllers must extract the authenticated player's identity via Shield (`auth()->user()` or `auth()->id()`) and pass player identifiers down to the Service layer.
- **Session Lifecycle**: Login, logout, session regeneration, password resets, and account registration flows must leverage Shield's native session handlers and security extensions.

---

## 3. Layered Architecture & Responsibilities

```
+-----------------------------------------------------------------------+
|                         HTTP Request (Client)                         |
+-----------------------------------------------------------------------+
                                    |
                                    v
+-----------------------------------------------------------------------+
|  Controllers (App\Controllers)                                        |
|  - Receives HTTP Requests & route parameters                          |
|  - Validates input payloads and query parameters                      |
|  - Enforces Shield Auth filters                                       |
|  - Formats standard JSON responses & HTTP status codes                |
+-----------------------------------------------------------------------+
                                    |
                                    v
+-----------------------------------------------------------------------+
|  Services (App\Services)                                              |
|  - Implements core business logic & game domain rules                 |
|  - Handles gameplay calculations, scoring, player balance updates     |
|  - Manages cross-repository workflows and transactions                |
|  - Completely decoupled from HTTP request/response objects            |
+-----------------------------------------------------------------------+
                                    |
                                    v
+-----------------------------------------------------------------------+
|  Repositories (App\Repositories)                                      |
|  - Handles data access, complex queries, and persistence              |
|  - Executes database joins, filtering, pagination, and raw SQL        |
|  - Manages database transaction boundaries (begin, commit, rollback)  |
|  - Isolates database interaction logic from business services          |
+-----------------------------------------------------------------------+
                                    |
                                    v
+-----------------------------------------------------------------------+
|  Models (App\Models)                                                  |
|  - Abstraction of database tables and entity attributes               |
|  - Defines table names, primary keys, allowed fields, timestamps      |
|  - CodeIgniter 4 Model definitions & entity mappings                  |
+-----------------------------------------------------------------------+
```

### 3.1 Controllers (`App\Controllers`)
- **Role**: Entry point for HTTP requests.
- **Responsibilities**:
  - Accept HTTP methods (`GET`, `POST`, `PUT`, `PATCH`, `DELETE`).
  - Validate incoming payloads against validation rules schemas.
  - Delegate execution strictly to domain **Services**.
  - Return standardized JSON responses with proper HTTP status codes.
- **Strict Rules**:
  - **NO** business logic inside Controllers.
  - **NO** direct database queries, Query Builder usage, or Model calls in Controllers.

### 3.2 Services (`App\Services`)
- **Role**: Business logic and domain orchestration engine.
- **Responsibilities**:
  - Implement business rules (e.g., player level progression, reward distribution, match verification, wallet updates).
  - Orchestrate data requirements using one or multiple **Repositories**.
  - Throw domain-specific exceptions on business rule violations.
- **Strict Rules**:
  - Services must be independent of HTTP context (`Request` or `Response` objects should never be passed into Services).
  - Pure input parameters (primitives, DTOs, arrays) must be used.

### 3.3 Repositories (`App\Repositories`)
- **Role**: Data access, query building, and persistence layer.
- **Responsibilities**:
  - Perform CRUD operations and custom database queries.
  - Handle complex query joins, aggregations, filtering, and pagination.
  - Control database transactions (`$this->db->transBegin()`, `$this->db->transCommit()`, `$this->db->transRollback()`).
- **Strict Rules**:
  - Repositories interact directly with Models or CodeIgniter's Database Connection.
  - No business logic rule evaluation should reside in Repositories.

### 3.4 Models (`App\Models`)
- **Role**: Database table schema abstraction and entity mapping.
- **Responsibilities**:
  - Extend `CodeIgniter\Model`.
  - Define `$table`, `$primaryKey`, `$allowedFields`, `$useTimestamps`, `$createdField`, `$updatedField`, and `$deletedField`.
  - Handle type casting, soft deletes, and entity transformations.
- **Strict Rules**:
  - Keep Models clean as data declarative abstractions.

---

## 4. API Standardization & Response Format

All RESTful endpoints must return a unified JSON response schema:

### Success Response Example (HTTP 200 / 201):
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
    "timestamp": "2026-08-22T14:55:30Z"
  }
}
```

### Error Response Example (HTTP 400 / 401 / 422 / 500):
```json
{
  "status": "error",
  "message": "Validation failed.",
  "errors": {
    "email": "The email field must contain a valid email address."
  },
  "meta": {
    "timestamp": "2026-08-22T14:55:30Z"
  }
}
```

---

## 5. File & System Versioning Standards (SemVer)
- **Versioning Standard**: Semantic Versioning 2.0.0 (`MAJOR.MINOR.PATCH`).
- **File Metadata**: All skill documentation, configuration files, and core architectural components must maintain explicit version tags.
- **Change Log Maintenance**: System evolution, architectural updates, and version increments must be logged in `.agents/relatorio_evolucao.md`.
