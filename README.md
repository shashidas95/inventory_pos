# 🛒 Sophisticated POS & Inventory Management System

A full-featured **Point of Sale (POS) and Inventory Management System** built with **Laravel 11.x**.  

The system features a robust **multi-role architecture** (Admin, Manager, Customer) and manages everything from product stock control to automated invoicing and financial reporting.

---

## 🏗️ Project Architecture

### Backend Structure

- `app/Http/Controllers/`  
  Contains business logic for:
  - POS operations  
  - Invoice management  
  - Stock handling  
  - Authentication  

- `database/migrations/`  
  Defines relational database schema for:
  - Users  
  - Stores  
  - Products  
  - Orders  
  - Invoices  

- `routes/web.php`  
  Handles routing for:
  - Blade views  
  - Application endpoints  

---

### Additional Services

- `backend/` → Dedicated Node.js API server for real-time data handling  
- `frontend/` → Vue.js-based visualization interface  

---

## 🛠️ Tech Stack

### Backend
- PHP (Laravel 11.x)
- Node.js

### Frontend
- Vue.js
- Blade Templating Engine
- JavaScript (ES6+)

### Database
- PostgreSQL / MySQL (Relational Database)

### Authentication & Security
- JWT Authentication (via `JwtTokenVerify` middleware)
- OTP-based Password Reset

---

## 🚀 Key Features

### 1️⃣ Multi-Role Management

**Admin / Manager**
- Full dashboard access
- Store-user assignment control
- Global stock management
- Sales performance analytics

**Customer**
- Product browsing
- Order placement
- Personal invoice history

---

### 2️⃣ POS & Sales Modules

- Real-time checkout system
- Automated receipt generation
- Invoice auto-generation upon order completion
- Sales statistics dashboard with performance tracking

---

### 3️⃣ Inventory & Stock Control

- Full CRUD for:
  - Categories
  - Products
- Admin-restricted edit/show controls
- Real-time stock monitoring
- Automated low-stock alerts
- Multi-store inventory tracking

---

### 4️⃣ Advanced Security

- Secure JWT-based API authentication
- OTP verification for password reset
- Middleware-based route protection
- Role-based access control

---

## 💻 Installation & Setup

### 🔹 Prerequisites

- PHP >= 8.2
- Composer
- Node.js & NPM
- PostgreSQL or MySQL

---

### 🔹 Quick Start

#### 1️⃣ Clone the Repository

```bash
git clone https://github.com/shashidas95/chemical-process-dashboard.git
cd chemical-process-dashboard 
## 💻 Installation & Setup

### 2️⃣ Install Dependencies

```bash
composer install
npm install
npm run dev

```

3️⃣ Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```
Update your database credentials inside the .env file.

4️⃣ Database Migration
```bash
php artisan migrate --seed
```
5️⃣ Run the Application
```bash
php artisan serve
```

The application will run at:
```bash
http://127.0.0.1:8000
```
# 📊 Database Schema Overview

The system uses a normalized relational schema to maintain transactional integrity.

| Table    | Purpose |
|----------|---------|
| users    | Stores user credentials and roles (Admin, Manager, Customer) |
| products | Inventory items with pricing and category references |
| orders   | Transaction records linking customers to specific products |
| invoices | Financial records generated from completed orders |
| stores   | Multi-location store management |


🎯 Engineering Highlights

| Feature | Description |
|---|---|
| Clean MVC Architecture | Follows Laravel best practices |
| Secure JWT Authentication Flow | Implements secure token-based authentication |
| Modular Controller Logic | Separates business logic into modular controllers |
| Scalable Database Design | Uses normalized relational schema |
| Production-Ready Structure | Organized folder and project layout |
| Multi-Role Permission System | Supports role-based access control |
| Financial Lifecycle Management | Manages workflow from Order → Invoice |

👨‍💻 Author

Shashi Kanta Das
DevOps Engineer | Backend Developer
GitHub: https://github.com/shashidas95
