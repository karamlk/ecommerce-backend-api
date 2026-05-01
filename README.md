# Ecommerce Backend API — Laravel 11

<p align="center">
  <b>Production-ready REST API with AOP architecture, concurrency control, and scalable queue processing</b>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-11-red?style=for-the-badge&logo=laravel">
  <img src="https://img.shields.io/badge/PHP-8%2B-blue?style=for-the-badge&logo=php">
  <img src="https://img.shields.io/badge/MySQL-Database-orange?style=for-the-badge&logo=mysql">
  <img src="https://img.shields.io/badge/Redis-Queue-critical?style=for-the-badge&logo=redis">
  <img src="https://img.shields.io/badge/Horizon-Monitoring-purple?style=for-the-badge">
  <img src="https://img.shields.io/badge/Auth-Sanctum-green?style=for-the-badge">
  <img src="https://img.shields.io/badge/License-MIT-yellow?style=for-the-badge">
</p>

---

## Overview

A scalable Laravel REST API powering a full e-commerce platform with a clean Service Layer architecture, Aspect-Oriented Programming (AOP) for cross-cutting concerns, concurrency-safe processing, and a Redis queue system monitored via Horizon.

---

## Architecture

### Service Layer + AOP

Business logic is isolated in dedicated services. Cross-cutting concerns are handled via a Decorator-based AOP simulation:

- **Logging Aspect** — activity and error logging
- **Performance Aspect** — execution time, memory, query count
- **Tracing Aspect** — full request lifecycle (start → end)
- **Error Handling Aspect** — centralized exception management
- **Transaction Aspect** — atomic DB operations

Services contain pure business logic only — no logging, no error handling, no transaction management mixed in.

---

### Concurrency & Data Integrity

| Mechanism | Purpose |
|---|---|
| `Cache::lock()` | Global checkout throttling |
| `DB::transaction()` | Atomic operations |
| `lockForUpdate()` | Row-level locking |

Prevents overselling and ensures consistency under concurrent requests.

---

### Observability

Three dedicated log streams:

| Log Type | Purpose |
|---|---|
| **Tracing** | Request lifecycle (start → end) |
| **Activity** | Business events and failures |
| **Performance** | Execution time, memory, query count |

Enables before/after performance comparison and simplifies debugging of concurrency issues.

---

### Queue System

Redis-backed queues with Laravel Horizon for monitoring. Handles OTP email delivery via Gmail SMTP. Decouples heavy operations from the request cycle and improves response time.

---

## Features

**Authentication** — Registration, login, OTP email verification, Sanctum token-based auth

**User Profile** — View, update profile, change avatar

**Product Browsing** — Categories → Stores → Products, product details, search across products and stores

**Favorites** — Add/remove favorites, organized by category

**Cart** — Add/update/remove items, 50-item capacity limit, concurrency-safe updates

**Orders** — Place and cancel orders, view history, automatic stock updates on delivery and cancellation

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 11 |
| Language | PHP 8+ |
| Database | MySQL |
| Queues | Redis |
| Monitoring | Laravel Horizon |
| Auth | Sanctum |
| Email | Gmail SMTP |
| API Testing | Postman |

---

## Installation

### 1. Clone

```bash
git clone https://github.com/karamlk/ecommerce-backend-api.git
cd ecommerce-backend-api
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` with your database and Gmail SMTP credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_gmail_app_password
MAIL_ENCRYPTION=tls
```

### 4. Run Migrations

```bash
php artisan migrate
```

### 5. Seed the Database

```bash
php artisan db:seed
```

### 6. Start Services

```bash
sudo service redis-server start
php artisan serve
```

### 7. Start Queue Worker

Basic worker:

```bash
php artisan queue:work
```

Or Laravel Horizon (recommended):

```bash
php artisan horizon
```

Horizon dashboard: `http://localhost:8000/horizon`

---

## API Documentation

Import the Postman collection included in the repository:

`postman/Ecommerce-Backend-api.postman_collection.json`

All protected endpoints require:
```bash
Authorization: Bearer {token}
```

---

## Notes

Product, store, and user images are not included in the repository. Seeded data references files under `storage/product_photos/`, `storage/profile_photos/`, and `storage/store_photos/`. Missing images fall back to a placeholder automatically.
