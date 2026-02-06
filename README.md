# Employee Attendance Management System

<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
</p>

<p align="center">
  <a href="https://github.com/fab-ryan/laravel-academic-bridge-challenge/actions/workflows/tests.yml"><img src="https://github.com/fab-ryan/laravel-academic-bridge-challenge/actions/workflows/tests.yml/badge.svg" alt="Tests"></a>
  <a href="#"><img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat-square&logo=laravel" alt="Laravel Version"></a>
  <a href="#"><img src="https://img.shields.io/badge/PHP-8.4+-777BB4?style=flat-square&logo=php" alt="PHP Version"></a>
  <a href="#"><img src="https://img.shields.io/badge/License-MIT-green?style=flat-square" alt="License"></a>
</p>

A RESTful API for managing employee attendance built with Laravel 12, featuring authentication, employee management, attendance tracking, and report generation.

---

## 🚀 Features

| Category | Features |
|----------|----------|
| **Authentication** | Registration, Login/Logout, Forgot & Reset Password (Sanctum) |
| **Employees** | Full CRUD, Search, Pagination, Unique Identifiers |
| **Attendance** | Check-in/Check-out, Daily Records, Date & Employee Filters |
| **Notifications** | Queued Emails on Check-in/Check-out, Password Reset |
| **Reports** | PDF (DomPDF), Excel (Laravel Excel), Date Range Support |
| **Documentation** | OpenAPI 3.0 Specification, Swagger UI |

---

## 🛠 Tech Stack

| Component | Technology |
|-----------|------------|
| Framework | Laravel 12 |
| PHP | 8.4+ |
| Database | MySQL 8.4 |
| Cache/Queue | Redis |
| Authentication | Laravel Sanctum |
| PDF Generation | DomPDF |
| Excel Generation | Laravel Excel |
| Email Testing | Mailpit |
| API Docs | L5-Swagger |
| Containerization | Laravel Sail (Docker) |

---

## 📋 Prerequisites

- Docker Desktop (for Sail setup)
- Git
- PHP 8.4+ & Composer (for traditional setup)

---

## 🔧 Installation

### Option 1: Laravel Sail (Recommended)

```bash
# Clone repository
git clone https://github.com/fab-ryan/laravel-academic-bridge-challenge.git
cd laravel-academic-bridge-challenge

# Copy environment file
cp .env.example .env

# Install dependencies via Docker
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php84-composer:latest \
    composer install --ignore-platform-reqs

# Start containers
./vendor/bin/sail up -d

# Setup application
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
./vendor/bin/sail artisan l5-swagger:generate
./vendor/bin/sail artisan queue:work &
```

### Option 2: Traditional Setup

```bash
# Clone and install
git clone https://github.com/fab-ryan/laravel-academic-bridge-challenge.git
cd laravel-academic-bridge-challenge
composer install

# Configure environment
cp .env.example .env
php artisan key:generate

# Setup database (configure .env first)
php artisan migrate --seed

# Start server
php artisan serve
```

---

## ⚙️ Environment Variables

Key variables to configure in `.env`:

```env
# Application
APP_URL=http://localhost
APP_FRONTEND_URL=http://localhost:5173

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=attendance_system
DB_USERNAME=root
DB_PASSWORD=

# Queue (for email notifications)
QUEUE_CONNECTION=database

# Mail (Mailpit for development)
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

---

## 🌐 Access Points

| Service | URL |
|---------|-----|
| Application | http://localhost:8000 (Sail) / http://localhost:8000 (artisan serve) |
| API Documentation | http://localhost/api/documentation |
| Mailpit | http://localhost:8025 |
| MySQL | localhost:3306 |
| Redis | localhost:6379 |

---

## 📚 API Reference

**Base URL:** `/api/v1`

### Authentication

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/auth/register` | Register new user |
| POST | `/auth/login` | Login & get token |
| POST | `/auth/logout` | Logout (auth required) |
| GET | `/auth/user` | Get current user |
| POST | `/auth/forgot-password` | Request password reset |
| POST | `/auth/reset-password` | Reset password |

### Employees (Auth Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/employees` | List all employees |
| POST | `/employees` | Create employee |
| GET | `/employees/{id}` | Get employee |
| PUT | `/employees/{id}` | Update employee |
| DELETE | `/employees/{id}` | Delete employee |

### Attendance (Auth Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/attendances` | List records (filterable) |
| POST | `/attendances/check-in` | Record arrival |
| POST | `/attendances/check-out` | Record departure |
| GET | `/attendances/{id}` | Get record details |
| GET | `/attendances/employee/{id}/today` | Get today's record |

### Reports (Auth Required)

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/reports/attendance/pdf` | Download PDF report |
| GET | `/reports/attendance/excel` | Download Excel report |

**Report Query Parameters:**
- `date` - Specific date (Y-m-d)
- `from_date` / `to_date` - Date range (Y-m-d)

---

## 🔐 Authentication

Uses **Laravel Sanctum** token-based authentication.

```bash
# Login
curl -X POST http://localhost/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email": "admin@example.com", "password": "password"}'

# Authenticated request
curl -X GET http://localhost/api/v1/employees \
  -H "Authorization: Bearer <token>" \
  -H "Accept: application/json"
```

**Rate Limit:** 60 requests/minute

---

## 📊 Default Credentials

After seeding: `admin@example.com` / `password`

---

## 🧪 Testing

```bash
# All tests
./vendor/bin/sail artisan test

# With coverage
php artisan test --coverage

# Specific test
php artisan test tests/Feature/AuthenticationTest.php
```

---

## 🐳 Sail Commands

```bash
./vendor/bin/sail up -d          # Start
./vendor/bin/sail down           # Stop
./vendor/bin/sail logs           # View logs
./vendor/bin/sail shell          # Container shell
./vendor/bin/sail artisan <cmd>  # Run artisan
./vendor/bin/sail composer <cmd> # Run composer
```

---

## 📁 Project Structure

```
app/
├── Enums/              # AttendanceType enum
├── Exports/            # Excel export classes
├── Http/
│   ├── Controllers/Api # API controllers
│   └── Requests/       # Form request validation
├── Models/             # Eloquent models
├── Notifications/      # Email notifications
└── OpenApi/Schemas/    # OpenAPI definitions

database/
├── factories/          # Model factories
├── migrations/         # Database migrations
└── seeders/            # Seeders

routes/api.php          # API routes
resources/views/reports # PDF templates
tests/                  # Feature & Unit tests
```

---

## 📝 Code Quality

```bash
./vendor/bin/pint           # Code formatting
```

---

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'Add amazing feature'`)
4. Push branch (`git push origin feature/amazing-feature`)
5. Open Pull Request

---

## 👤 Author

**Fab Ryan** - [GitHub](https://github.com/fab-ryan)

---

## 📄 License

MIT License - see [LICENSE](https://opensource.org/licenses/MIT)
