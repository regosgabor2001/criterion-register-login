# Secure Registration & Login Application

A custom-built PHP MVC architecture implementing user authentication, token-based security, request throttling, and secure session management. Built strictly adhering to modern HTML5, CSS3, and strict Content Security Policy (CSP) guidelines.

---

## Technical Stack & Developer Environment

* **Execution Runtime:** PHP 8.4
* **Web Server:** Apache
* **Database Management System:** MySQL 8.0
* **Infrastructure Automation:** Docker & Docker Compose
* **Dependency Manager:** Composer

## Prerequisites & Installation

### 1. Extract and Navigate to Project Root
Ensure all project files are structured within your target directory, then navigate to the root folder via terminal:
```bash
cd criterion-register-login

2. Launch the Infrastructure Pipeline (Docker Compose)

Bash
docker compose up -d --build

3. Database Initialization (Schema Seed)

Bash
docker compose exec -T db mysql -u root -psecret criterion_db < database_dump.sql

4. Install Composer Dependencies

Bash
docker compose exec app composer install
The application is accessible on this address:
http://localhost:8080

Running the Automated Test Suite

Bash
docker compose exec app ./vendor/bin/phpunit tests