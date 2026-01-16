# AtoZ Order Management System

A robust Laravel 11 application for managing products, staff, and orders with automated shipping integration, asynchronous processing, and secure webhooks.

## 🚀 Project Setup Instructions

### Prerequisites
- PHP 8.2+
- Composer
- MySQL
- Laragon

### Installation
1.  **Clone the Repository**:
    ```bash
    git clone <https://github.com/haider7575/AtoZCoder.git>
    cd AtoZCoder
    ```
2.  **Install Dependencies**:
    ```bash
    composer install
    ```
3.  **Environment Configuration**:
    - Copy `.env.example` to `.env`:
      ```bash
      cp .env.example .env
      ```
    - Generate Application Key:
      ```bash
      php artisan key:generate
      ```
    - Configure your database credentials in `.env` (`DB_DATABASE=atozcoder`).
4.  **Database Migration & Seeding**:
    ```bash
    php artisan migrate --seed
    ```
5.  **Run the Application**:
    ```bash
    php artisan serve
    ```
6.  **Background Processing (Required for Shipments)**:
    - Set `QUEUE_CONNECTION=database` in `.env`.
    - Run the queue worker:
      ```bash
      php artisan queue:work
      ```

---

## 🛠 Architecture & Design Decisions

### 1. Server-Side Rendering (Blade)
The application transitioned from a JS-heavy Axios approach to **Server-Side Rendering (SSR)** using Laravel Blade. This ensures:
- Faster initial page loads.
- Simpler state management via Laravel Sessions.
- Improved SEO and developer productivity.

### 2. Role-Based Access Control (RBAC)
- **Admin**: Full access to Products, Staff management, and Order creation.
- **Staff**: Restricted to viewing assigned orders and updating their statuses.
- Implementation: Laravel **Gates** and **Form Requests** ensure strict authorization at both Route and Logic levels.

### 3. Asynchronous Workflow
- **Order Confirmation**: When an admin confirms an order, the system dispatches a `ProcessShipment` job.
- **Benefit**: The UI remains responsive; the "heavy lifting" of talking to external Shipping APIs happens in the background.

### 4. Secure Webhooks
- **Endpoint**: `/api/webhook/shipping`
- **Security**: Uses **HMAC-SHA256 signature validation** via the `X-Signature` header to ensure payloads originate only from trusted sources.

---

## 📡 API Endpoints

### Authentication (API)
- `POST /api/login` - Returns Sanctum Token.
- `POST /api/logout` - Revokes token.

### Products
- `GET /api/products` - List products (Paginated).
- `POST /api/products` - Create product (Admin only).
- `PUT /api/products/{id}` - Update product (Admin only).
- `DELETE /api/products/{id}` - Delete product (Admin only).

### Orders
- `GET /api/orders` - List all (Admin) or assigned (Staff) orders.
- `POST /api/orders` - Create order (Admin only).
- `PATCH /api/orders/{id}/status` - Update status (Admin or Assigned Staff).

### Webhooks
- `POST /api/webhook/shipping` - Public endpoint for shipment events (`delivered`, `failed`). Requires `X-Signature`.

---

## 🚚 Shipping API Integration
The application demonstrates integration with a service layer `ShippingService`.
- **API Used**: [JSONPlaceholder](https://jsonplaceholder.typicode.com/) (Mock REST API).
- **Process**:
  1. Job `ProcessShipment` calls `ShippingService`.
  2. Service sends a `POST` request with order details.
  3. On success, a `Tracking Number` and `Label URL` are generated and stored in the `shipments` table.
  4. Webhooks can then update these shipment statuses remotely.
