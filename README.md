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

A RESTful API for managing employee attendance built with Laravel 12, featuring full authentication, employee management, attendance tracking, and report generation.

## 🚀 Features

- **Authentication System** (Laravel Sanctum)
  - User Registration
  - Login / Logout
  - Forgot Password
  - Password Reset
  
- **Employee Management**
  - Full CRUD operations
  - Search and pagination
  - Unique employee identifiers
  
- **Attendance Tracking**
  - Check-in (arrival time recording)
  - Check-out (departure time recording)
  - Daily attendance records
  - Filter by employee, date, or date range
  
- **Email Notifications**
  - Queued email notifications on check-in/check-out
  - Password reset emails
  
- **Report Generation**
  - PDF reports (using DomPDF)
  - Excel reports (using Laravel Excel)
  - Daily/date range reports
  
- **API Documentation**
  - OpenAPI 3.0 specification
  - Swagger UI for interactive documentation

## 🛠 Tech Stack

- **Framework:** Laravel 12
- **PHP Version:** 8.2+
- **Database:** MySQL 8.4
- **Cache/Queue:** Redis
- **Authentication:** Laravel Sanctum
- **PDF Generation:** DomPDF
- **Excel Generation:** Laravel Excel (PhpSpreadsheet)
- **Email Testing:** Mailpit
- **API Documentation:** L5-Swagger (OpenAPI 3.0)
- **Containerization:** Laravel Sail (Docker)

## 📋 Prerequisites

- Docker Desktop installed and running
- Git
- Composer (optional, for non-Docker setup)

## 🔧 Local Development Setup

### Using Laravel Sail (Recommended)

1. **Clone the repository**
   ```bash
   git clone https://github.com/fab-ryan/laravel-academic-bridge-challenge.git
   cd laravel-academic-bridge-challenge
   ```

2. **Copy environment file**
   ```bash
   cp .env.example .env
   ```

3. **Install Composer dependencies** (using Docker)
   ```bash
   docker run --rm \
       -u "$(id -u):$(id -g)" \
       -v "$(pwd):/var/www/html" \
       -w /var/www/html \
       laravelsail/php84-composer:latest \
       composer install --ignore-platform-reqs
   ```

4. **Start the Docker containers**
   ```bash
   ./vendor/bin/sail up -d
   ```

5. **Generate application key**
   ```bash
   ./vendor/bin/sail artisan key:generate
   ```

6. **Run database migrations**
   ```bash
   ./vendor/bin/sail artisan migrate
   ```

7. **Seed the database** (optional - creates sample data)
   ```bash
   ./vendor/bin/sail artisan db:seed
   ```

8. **Generate API documentation**
   ```bash
   ./vendor/bin/sail artisan l5-swagger:generate
   ```

9. **Start the queue worker** (for email notifications)
   ```bash
   ./vendor/bin/sail artisan queue:work
   ```

### Traditional Setup (Without Docker)

1. **Clone the repository**
   ```bash
   git clone https://github.com/fab-ryan/laravel-academic-bridge-challenge.git
   cd laravel-academic-bridge-challenge
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure your database** in `.env`
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=attendance_system
   DB_USERNAME=root
   DB_PASSWORD=
   ```

5. **Run migrations and seeders**
   ```bash
   php artisan migrate --seed
   ```

6. **Start the development server**
   ```bash
   php artisan serve
   ```

### Access Points

| Service                     | URL                                         |
| --------------------------- | ------------------------------------------- |
| Application                 | http://localhost (Sail) / http://localhost:8000 |
| API Documentation (Swagger) | http://localhost:8000/api/documentation          |
| Mailpit (Email Testing)     | http://localhost:8025                       |
| MySQL                       | localhost:3306                              |
| Redis                       | localhost:6379                              |

## 📚 API Endpoints

### Authentication

| Method | Endpoint               | Description               |
| ------ | ---------------------- | ------------------------- |
| POST   | `/api/v1/auth/register`        | Register a new user       |
| POST   | `/api/v1/auth/login`           | Login and get token       |
| POST   | `/api/v1/auth/logout`          | Logout (requires auth)    |
| GET    | `/api/v1/auth/user`            | Get authenticated user    |
| POST   | `/api/v1/auth/forgot-password` | Request password reset    |
| POST   | `/api/v1/auth/reset-password`  | Reset password with token |

### Employees (Requires Authentication)

| Method | Endpoint              | Description          |
| ------ | --------------------- | -------------------- |
| GET    | `/api/v1/employees`      | List all employees   |
| POST   | `/api/v1/employees`      | Create an employee   |
| GET    | `/api/v1/employees/{id}` | Get employee details |
| PUT    | `/api/v1/employees/{id}` | Update an employee   |
| DELETE | `/api/v1/employees/{id}` | Delete an employee   |

### Attendance (Requires Authentication)

| Method | Endpoint                               | Description               |
| ------ | -------------------------------------- | ------------------------- |
| GET    | `/api/v1/attendances`                     | List attendance records   |
| POST   | `/api/v1/attendances/check-in`            | Record employee arrival   |
| POST   | `/api/v1/attendances/check-out`           | Record employee departure |
| GET    | `/api/v1/attendances/{id}`                | Get attendance details    |
| GET    | `/api/v1/attendances/employee/{id}/today` | Get today's attendance    |

### Reports (Requires Authentication)

| Method | Endpoint                        | Description           |
| ------ | ------------------------------- | --------------------- |
| GET    | `/api/v1/reports/attendance/pdf`   | Download PDF report   |
| GET    | `/api/v1/reports/attendance/excel` | Download Excel report |

**Query parameters for reports:**
- `date` - Specific date (Y-m-d)
- `from_date` - Start date (Y-m-d)
- `to_date` - End date (Y-m-d)

### API Versioning

The API also supports versioning with `/api/v1/` prefix:

| Version      | Base URL     |
| ------------ | ------------ |
| v1 (current) | `/api/v1/`   |
| Legacy       | `/api/`      |

## 🔐 Authentication

This API uses Laravel Sanctum for token-based authentication. To access protected endpoints:

1. **Register or Login** to get an access token
2. **Include the token** in the Authorization header:
   ```
   Authorization: Bearer <your-token>
   ```

### Example: Login Request
```bash
curl -X POST http://localhost/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "email": "admin@example.com",
    "password": "password"
  }'
```

### Example: Authenticated Request
```bash
curl -X GET http://localhost/api/v1/employees \
  -H "Authorization: Bearer <your-token>" \
  -H "Accept: application/json"
```

### Rate Limiting

- **API Rate Limit**: 60 requests per minute
- Rate limit headers are included in all responses

## 📧 Email Testing

Emails are captured by Mailpit in the development environment. Access the Mailpit interface at:
- **URL:** http://localhost:8025

All attendance notifications and password reset emails will appear here.

### Email Configuration
```env
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## 🧪 Running Tests

```bash
# Run all tests
./vendor/bin/sail artisan test

# Or without Docker
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/AuthenticationTest.php
```

## 📊 Default Credentials

After running `db:seed`, you can use:
- **Email:** admin@example.com
- **Password:** password

## 🔄 Queue Processing

Email notifications are sent via queues. Make sure the queue worker is running:

```bash
# Start queue worker (Sail)
./vendor/bin/sail artisan queue:work

# Or without Docker
php artisan queue:work

# Run in the background
./vendor/bin/sail artisan queue:work --daemon &
```

### Queue Configuration
```env
QUEUE_CONNECTION=database
```

## 📝 API Documentation

Interactive API documentation is available via Swagger UI:
- **URL:** http://localhost/api/documentation

To regenerate the documentation after making changes:
```bash
./vendor/bin/sail artisan l5-swagger:generate
```

## 🐳 Docker Commands Reference

```bash
# Start containers
./vendor/bin/sail up -d

# Stop containers
./vendor/bin/sail down

# View logs
./vendor/bin/sail logs

# Access container shell
./vendor/bin/sail shell

# Run artisan commands
./vendor/bin/sail artisan <command>

# Run composer commands
./vendor/bin/sail composer <command>

# Run npm commands
./vendor/bin/sail npm <command>
```

## 📁 Project Structure

```
├── app/
│   ├── Enums/                # Enums (AttendanceType, etc.)
│   ├── Exports/              # Excel export classes
│   ├── Http/
│   │   ├── Controllers/Api/  # API Controllers
│   │   └── Requests/         # Form Request validation
│   ├── Models/               # Eloquent models
│   ├── Notifications/        # Email notifications
│   └── OpenApi/Schemas/      # OpenAPI schema definitions
├── config/                   # Configuration files
├── database/
│   ├── factories/            # Model factories
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── resources/views/reports/  # PDF report templates
├── routes/
│   └── api.php               # API routes
├── tests/
│   ├── Feature/              # Feature tests
│   └── Unit/                 # Unit tests
└── storage/api-docs/         # Generated API documentation
```

## 📝 Code Quality

### Linting
```bash
./vendor/bin/pint
```

### Static Analysis
```bash
./vendor/bin/phpstan analyse
```

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 👥 Authors

- **Fab Ryan** - *Initial work* - [fab-ryan](https://github.com/fab-ryan)

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🙏 Acknowledgments

- [Laravel](https://laravel.com) - The PHP Framework
- [Laravel Sanctum](https://laravel.com/docs/sanctum) - API Authentication
- [L5-Swagger](https://github.com/DarkaOnLine/L5-Swagger) - OpenAPI Documentation
- [Maatwebsite Excel](https://laravel-excel.com/) - Excel Export
- [DomPDF](https://github.com/barryvdh/laravel-dompdf) - PDF Generation
