# 🚀 BureauCrazy – The Intelligent Grant & Deadline Navigator for Non-Profits

**BureauCrazy** is a containerized Full-Stack Monorepo specifically engineered to eliminate administrative friction and compliance risks for **Non-Governmental Organizations (NGOs), social welfare associations, and public sector institutions**. 

Managing public funding and grants is tied to strict, bureaucratic deadlines and convoluted policy guidelines. Delayed progress reports or incorrect financial statements routinely result in catastrophic financial clawbacks and audit failures. **BureauCrazy solves this problem through autonomous AI-driven deadline extraction, dynamic risk prioritization, and automated calendar synchronization via standard protocols.**

---

## 👑 Technical Governance & AI-Driven Architecture

This repository is not a collection of loosely stitched, AI-generated code snippets. It is the result of a rigorous **AI Governance workflow**, demonstrating the tactical mindset of a **Technical Lead and Software Architect**:
* **Architectural Sovereignty:** The fundamental system design, relational PostgreSQL data models, stateless API endpoints, and a precision-focused, cent-based integer budget model were architected completely independently. Generative AI tools (Gemini & DeepSeek) were strictly directed as execution resources for fine-grained sub-tasks.
* **Rigorous Gatekeeping:** No code entered the codebase unchallenged. AI-generated proposals were repeatedly refined, structured, and refactored until they met enterprise-grade maintainability, complete type safety, and strict **SOLID principles**.
* **Economic Efficiency (Factor 2.6x):** The entire functional full-stack platform was conceptualized, built, and thoroughly tested in **22 hours**—a scope for which market benchmarks allocate between **56 to 60 hours** of manual development.

---

## 🛠️ Tech Stack & Monorepo Topology

The architecture enforces strict monorepo discipline. The root folder contains zero operational code; all runtime dependencies remain entirely isolated inside their respective application services.

* **Backend:** PHP 8.3+ | Symfony 7 (Stateless REST API Engine) | Doctrine ORM
* **Database:** PostgreSQL 16 (Clean schema initialization via physical baseline migrations)
* **Frontend:** Reactive Single Page Application (SPA) | Vite Build Pipeline | Modern ES6+ JavaScript | Tailwind CSS
* **Web Server:** Nginx (Acts as a reverse proxy and handles cross-origin preflight requests)
* **AI Engine:** DeepSeek API (OpenAI-compatible Chat Completion Schnittstelle)
* **Infrastructure:** Multi-Container Orchestration via **Docker & Docker Compose**

---

## 🏁 Architectural & Functional Milestones

### 📦 1. Containerized Multi-Service Infrastructure & Domain Core
* Implemented a fully isolated Docker infrastructure routing dedicated services for PHP-FPM, Nginx, PostgreSQL, and Node.
* Relational modeling via Doctrine ORM. `FundingProject` utilizes cryptographically secure **UUIDv7 keys** to prevent enumeration attacks and an integer cent-model to ensure exact financial mathematics.
* `Deadline` entities feature a ManyToOne cascade with `orphanRemoval`, a strict `DeadlineType` backed enum, and native Symfony validation constraints ensuring future-dated entries.

### 🌐 2. Stateless REST API & Cross-Origin (CORS) Control
* Engineered stateless API controllers (`/api/v1/projects` and `/api/v1/deadlines`), guarded against malformed payloads by Symfony's `ValidatorInterface`.
* The server-side Nginx proxy intercepts `OPTIONS` preflight requests at the gateway level, returning an immediate `204 No Content` status to cleanly bypass Cross-Origin Resource Sharing (CORS) blocks before they hit the PHP runtime.

### 🛡️ 3. Native Security Architecture & Stateless 2FA Protection
* Intentionally avoided heavy, opaque third-party security bundles to minimize supply-chain vulnerabilities.
* Built a lean, high-performance security capsule leveraging native PHP 8.3 typing and a custom `SecurityManager` via TDD.
* The stateless, two-stage authentication flow (`Api/v1/SecurityController`) includes an automated brute-force protection circuit (`failed2faAttempts`), immediately locking compromised accounts upon invalid 2FA entries.

### 🧠 4. Autonomous AI Extraction & Domain Hydration
* Integrated the `DeepSeekService` to parse unstructured, bureaucratic funding guidelines. The network layer is entirely isolated during test execution using a decoupled `MockHttpClient` to prevent API token costs and environment flakiness.
* The `AnalysisService` processes free-text data against a deterministic JSON-Schema prompt design, automatically hydrating extracted timeline structures directly into the PostgreSQL domain.

### 📅 5. SOLID Calendar Synchronization & Risk Prioritization
* Implemented the `ICalGeneratorService` decoupled behind a native `ICalGeneratorInterface` enforcing the **Dependency Inversion Principle (SOLID)** to generate standard RFC 5545 iCalendar streams.
* Upgraded the AI parsing pipeline to execute a three-tier risk evaluation via a `RiskLevel` backed enum (`high`, `medium`, `low`), feeding color-coded Tailwind utility badges directly into the responsive dashboard grid.
* Hardened the frontend UI test suite (`frontend/tests/main.test.js`) under **Vitest & JSDOM** by completely stubbing out the global network interface (`window.fetch`), ensuring a 100% quiet pipeline free of unmocked network noise.

### 💎 6. High-Level Static Analysis & Code Purity (100% Clean)
* **PHPStan Level 6 with Zero Exceptions:** Complete baseline removal. The `ignoreErrors` array is physically empty (**`[]`**). Strictest parameters (`treatPhpDocTypesAsCertain` and `reportUnmatchedIgnoredErrors`) are set to `true`.
* Eliminated obsolete dynamic guard switches (`method_exists`, `is_object`) and replaced them with robust compile-time safe code patterns (universal string-casting of UUID objects across all controllers).
* Globally enforced `rememberWithPossiblyImpureFunctionValues: false` to force PHPStan to accurately evaluate inline entity state mutations (e.g., incremental 2FA lockouts) within the same request lifecycle, catching runtime bugs deterministically.

---

## 🎛️ Automated DevOps Pipeline & DX (Makefile)

To maximize Developer Experience (DX) and enforce quality gates before code push, the entire container landscape is driven by a single, comprehensive `Makefile` at the root:

### 🐋 Container Lifecycle
* `make up` – Spins up the entire Docker environment quietly in detached mode.
* `make down` – Powers down all containers safely while preserving persistent database volumes.
* `make status` – Displays the real-time operational status and port bindings of all services.

### 🐘 Backend Governance & Validation (Symfony)
* `make check` – **The critical pre-commit validation target.** Sequentially executes the PSR12 style linter, static PHPStan analysis, and both backend and frontend test suites.
* `make phpstan` – Triggers the static analysis engine at strict Level 6 strictness.
* `make lint` – Audits code compliance against the PSR12 standard using PHP CodeSniffer.
* `make lint-fix` – Automatically repairs cosmetic format violations using `phpcbf`.
* `make test` – Executes the complete PHPUnit test suite.
* `make test-file file=...` – Runs a specific, isolated test suite file on demand.
* `make db-migrate` – Executes all pending database schema migrations for dev and test environments.
* `make test-db-fresh` – Rebuilds the isolated test database completely from scratch.
* `make cache-clear` – Physically purges the Symfony cache directories for dev and test environments.
* `make composer-require pkg="..."` – Installs PHP packages path-safely inside the active runtime container.

### 🪐 Frontend Infrastructure (Vite & Vitest)
* `make frontend-install pkg="..."` – Installs npm dependencies safely within the isolated Node volume.
* `make frontend-dev` – Launches the Vite development server in the foreground.
* `make frontend-test` – Executes the Vitest/JSDOM suite under silent, fully-stubbed conditions.
* `make frontend-logs` – Streams live frontend container outputs to trace HMR (Hot Module Replacement) cycles.

### 🔧 Permissions & Environment Healing
* `make fix-perms` – **Resolves WSL2 and Docker write-permission conflicts instantly.** Recursively applies the `775` (folders) and `664` (files) mask while strictly protecting `.git/` metadata and `node_modules`. Ensures all framework binaries are executable and white-lists the runtime path.
