#  Ecommerce  – Laravel REST API Backend

A RESTful API backend for a ecommerce application, built using **Laravel**. This API allows users to browse products by category and store, manage their profiles, place and edit orders, maintain a favorites list, and verify their account via OTP email using **Gmail SMTP**.    
Laravel Sanctum is used for secure token-based authentication,
and the system also supports **Redis** queues for handling background jobs (like OTP emails and notifications) and **Laravel Horizon** for monitoring and managing queued jobs.

---

##  Features

- **Authentication**
  - User registration and login
  - OTP email verification via **Gmail SMTP**
  - Token-based API security (Laravel Sanctum)

- **User Profile**
  - View and update profile info
  - Upload or change avatar image

- **Product Browsing**
  - Browse product categories
  - View stores under each category
  - Browse products in a store
  - View product details
  - Search by product or store name

- **Favorites**
  - Add/remove products to/from favorites
  - View favorites by product category

- **Cart & Orders**
  - Add/remove/edit products in the cart
  - Place new orders
  - Edit or cancel orders
  - View past orders

- **Stock Handling**
  - Product stock is updated when:
    - An order is delivered
    - An order is canceled or removed

- **Background Jobs**
  - Queued email sending via Redis Queue
  - Queue monitoring with Laravel Horizon    

---

##  Tech Stack

- **Laravel 11**
- **MySQL**
- **Redis** (for queue processing)
- **Laravel Horizon** (queue monitoring dashboard)
- **Laravel Sanctum**
- **Gmail SMTP** (OTP delivery)
- **Postman** (API testing)

---

## Notes

- All routes for **authentication**, **profile**, **favorites**, **orders**, and **cart** are protected by `auth:sanctum`.  
  Make sure the user is authenticated before accessing them.

- Use **Laravel Sanctum** tokens for authentication in all secured requests.

---

### ⚙️ Installation

## 1. Clone the Repository

```bash
git clone https://github.com/karamlk/ecommerce-backend-api.git
cd ecommerce-backend-api
```

## 2. Install Dependencies

```bash
composer install
```

## 3. Configure Environment Variables

```bash
cp .env.example .env
php artisan key:generate
```

Then edit `.env` and configure your database and Gmail SMTP credentials:

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

Make sure Redis is running:

```bash
sudo service redis-server start
```
Then run the Laravel server:

```bash
php artisan serve
```

### 7. Start Queue Worker or Horizon

You can use either:
- Basic queue worker:

```bash
php artisan queue:work
```

- Or start Laravel Horizon (recommended):
```bash
php artisan horizon
```
Visit the Horizon dashboard at:
http://localhost:8000/horizon

---

## 🖼️ Image Note

📷 **Product**, **Store**, and **User** images are **not included** in this repository.

The seeded data references image files located under:

- `storage/product_photos/`
- `storage/profile_photos/`
- `storage/store_photos/`

If these image files are missing, the application will automatically display a **placeholder image** using a public service such as [https://placehold.co].

---

## 📝 API Documentation

All API endpoints with examples are included in the Postman collection.  

You can import it directly in Postman:

1. Open Postman.
2. Click **Import** → **File** → Select `postman/Ecommerce-Backend-api.postman_collection.json`.
3. Start testing the endpoints.

The collection file is located in the repository at: `postman/Ecommerce-Backend-api.postman_collection.json`.