## Laravel + Vue Todo Application

This is a **full‑stack Todo application** built with:

- **Backend**: Laravel (latest 11.x series, via `laravel/framework` dependency)
- **Frontend**: Vue 3 + Vite in the Laravel `resources` folder
- **Styles**: Tailwind CSS
- **Database**: MySQL

It demonstrates:

- **CRUD** for todos
- **Mark complete / incomplete**
- **Tags** for todos
- **Due dates** with sorting
- **Search & filter** (by text, status, tag, and due date)
- **User authentication** (register, login, logout – token based)
- **RESTful JSON API** with validation and proper HTTP status codes
- **Automated tests** for the main API flows

---

## 1. Getting Started

### 1.1. Requirements

- PHP 8.2+
- Composer
- Node 20+ and npm (or yarn / pnpm)
- MySQL 8+ (or compatible)

### 1.2. Installation Steps

1. **Install PHP dependencies:**
   ```bash
   composer install
   ```
   This will pull in `laravel/framework` 11.x, `laravel/sanctum`, and other required packages.

2. **Configure environment:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   
   Update `.env` with your database credentials:
   - `DB_CONNECTION=mysql`
   - `DB_HOST=127.0.0.1`
   - `DB_PORT=3306`
   - `DB_DATABASE=laravel_todo`
   - `DB_USERNAME=root`
   - `DB_PASSWORD=`

3. **Run migrations:**
   ```bash
   php artisan migrate
   ```
   This creates:
   - `users` – application users
   - `todos` – todos associated to a user
   - `tags` – simple tags
   - `tag_todo` – pivot table for many‑to‑many between todos and tags

4. **Install frontend dependencies:**
   ```bash
   npm install
   ```

5. **Start the development servers:**
   
   Terminal 1 (Laravel backend):
   ```bash
   php artisan serve
   ```
   
   Terminal 2 (Vite frontend):
   ```bash
   npm run dev
   ```

6. **Access the application:**
      - Open http://localhost:8000 in

---

## 2. Frontend (Vue + Vite + Tailwind)

The Vue 3 SPA lives under `resources/js` and is bootstrapped via Vite:

- Entry: `resources/js/app.js`
- Root component: `resources/js/App.vue`
- Main feature UI: `resources/js/components/TodoApp.vue`
- API client: `resources/js/bootstrap.js` (Axios configuration)

### 2.1. Development

Run the dev server:
```bash
npm run dev
```

### 2.2. Production Build

To build assets for production:
```bash
npm run build
```

### 2.3. Styling

Tailwind CSS is configured via:
- `tailwind.config.js` – Tailwind configuration
- `postcss.config.js` – PostCSS configuration
- `resources/css/app.css` – Main CSS entry point with Tailwind directives

---

## 3. API Overview

The API is defined in `routes/api.php` and implemented in controllers under `app/Http/Controllers`.

All **todo endpoints** are protected by auth (`auth:sanctum`); you must be logged in.

### 3.1. Auth endpoints

- **POST** `/api/register`
  - Body: `{ "name": "John", "email": "john@example.com", "password": "secret", "password_confirmation": "secret" }`
  - Response: `201 Created` with `user` and `token`.

- **POST** `/api/login`
  - Body: `{ "email": "john@example.com", "password": "secret" }`
  - Response: `200 OK` with `user` and `token`.

- **POST** `/api/logout` (auth required)
  - Revokes current token, returns `204 No Content`.

The SPA stores the token in memory (and `localStorage`) and sends it via `Authorization: Bearer <token>` headers using Axios.

### 3.2. Todo endpoints

All under `/api/todos` and protected:

- **GET** `/api/todos`
  - Query params:
    - `search`: text search on title / description
    - `status`: `all` (default), `completed`, `pending`
    - `tag`: tag slug or id
    - `due_from`, `due_to`: ISO date strings to filter by due date range
    - `sort`: `due_date_asc` (default), `due_date_desc`, `created_at_desc`
  - Returns paginated list of todos with tags.

- **POST** `/api/todos`
  - Body: `{ "title": "...", "description": "...", "due_date": "YYYY-MM-DD", "completed": false, "tags": ["work", "home"] }`
  - Validation:
    - `title`: required, max length
    - `due_date`: nullable, date
    - `tags`: array of strings

- **GET** `/api/todos/{id}`

- **PUT/PATCH** `/api/todos/{id}`

- **DELETE** `/api/todos/{id}`

- **PATCH** `/api/todos/{id}/toggle`
  - Toggles the `completed` flag.

### 3.3. Tag endpoints

- **GET** `/api/tags`
  - Returns available tags for the current user (based on their todos).

---

## 4. Frontend Features

Implemented in `TodoApp.vue`:

- **Authentication UI**
  - Simple register / login forms with validation and error display
  - Persists token and user in local storage

- **Todo list**
  - Create, update, delete todos
  - Toggle complete / incomplete
  - Attach multiple tags (create as you type)
  - Optional due date (calendar input)

- **Search & filter**
  - Text search against title/description
  - Filter by:
    - completion status
    - tag
    - due date range
  - Sort by due date or created date

- **UI / UX**
  - Clean card‑based layout
  - Uses Tailwind utility classes
  - Mobile‑first responsive layout
  - Inline validation messages

---

## 5. Testing

This project includes comprehensive **feature tests** using PHPUnit. All tests are located in the `tests/Feature` directory.

### 5.1. Test Files

- **AuthTest.php**: Tests user registration, login, logout, and authentication flows
- **TodoTest.php**: Tests todo CRUD operations, filtering, searching, tagging, and authorization

### 5.2. Running Tests

#### Run All Tests

Run the complete test suite:
```bash
php artisan test
```

This will execute all tests in both `tests/Feature` and `tests/Unit` directories.

#### Run Specific Test File

Run tests from a specific test class:
```bash
# Run all authentication tests
php artisan test --filter AuthTest

# Run all todo-related tests
php artisan test --filter TodoTest
```

#### Run Specific Test Method

Run a single test method:
```bash
# Run only the "can create todo" test
php artisan test --filter test_can_create_todo

# Run only the "user can register" test
php artisan test --filter test_user_can_register
```

#### Run Tests with Verbose Output

Get more detailed output:
```bash
php artisan test -v
# or
php artisan test --verbose
```

#### Run Tests and Stop on First Failure

```bash
php artisan test --stop-on-failure
```

### 5.3. Test Coverage

The test suite includes comprehensive coverage:

#### Authentication Tests (AuthTest.php)
- ✅ User registration with valid data
- ✅ User registration validation (invalid data)
- ✅ User login with correct credentials
- ✅ User login with invalid credentials
- ✅ Authenticated user can get profile
- ✅ User can logout successfully

#### Todo Tests (TodoTest.php)
- ✅ Can create todo with all fields
- ✅ Cannot create todo without required title
- ✅ Can list todos with pagination
- ✅ Can filter todos by status (completed/pending)
- ✅ Can search todos by title/description
- ✅ Can update todo
- ✅ Can delete todo
- ✅ Can toggle todo completion status
- ✅ Users cannot access other users' todos (authorization)
- ✅ Can create todo with tags

### 5.4. Test Environment

Tests run in a separate testing environment:
- **Database**: Uses SQLite in-memory database (`:memory:`)
- **Environment**: `APP_ENV=testing`
- **Cache/Session**: Uses array driver (no persistence)
- **Isolation**: Each test runs in a transaction that is rolled back after completion

### 5.5. Expected Test Results

When all tests pass, you should see output like:
```
PASS  Tests\Feature\AuthTest
✓ user can register
✓ user cannot register with invalid data
✓ user can login
...

PASS  Tests\Feature\TodoTest
✓ can create todo
✓ cannot create todo without title
✓ can list todos
...

Tests:    16 passed (42 assertions)
Duration: 1.23s
```

### 5.6. Troubleshooting

#### Issue: "Test directory not found"
If you see an error about missing test directories, create them:
```bash
mkdir tests\Unit
```

#### Issue: Database connection errors
Tests use SQLite in-memory database. Make sure your `phpunit.xml` is configured correctly (it should be by default).

#### Issue: Tests failing due to factories
If you see errors about factories, make sure all factory files exist:
- `database/factories/UserFactory.php`
- `database/factories/TodoFactory.php`

#### Issue: Permission errors
Make sure the `storage` and `bootstrap/cache` directories are writable:
```bash
chmod -R 775 storage bootstrap/cache
```

### 5.7. Writing New Tests

To add new tests:

1. Create or edit a test file in `tests/Feature/`
2. Extend `Tests\TestCase`
3. Use `RefreshDatabase` trait for database tests
4. Follow the naming convention: `test_method_name()`

Example:
```php
public function test_can_do_something(): void
{
    // Arrange
    $user = User::factory()->create();
    
    // Act
    $response = $this->actingAs($user)->get('/api/something');
    
    // Assert
    $response->assertStatus(200);
}
```

### 5.8. Test Commands Reference

| Command | Description |
|---------|-------------|
| `php artisan test` | Run all tests |
| `php artisan test --filter TestName` | Run specific test class |
| `php artisan test --filter test_method_name` | Run specific test method |
| `php artisan test -v` | Verbose output |
| `php artisan test --stop-on-failure` | Stop on first failure |
| `php artisan test tests/Feature` | Run only feature tests |
| `php artisan test tests/Unit` | Run only unit tests |

---

## 6. Architecture & Design Notes

### 6.1. Backend Architecture

- **Separation of concerns**
  - Controllers are thin; they delegate to Eloquent models / query scopes
  - Validation handled through `FormRequest` classes
  - Business logic encapsulated in model scopes and relationships

- **Authentication**
  - Uses **Laravel Sanctum** for token-based authentication
  - Tokens stored in `personal_access_tokens` table
  - Frontend stores token in `localStorage` and sends via `Authorization: Bearer` header

- **Database Design**
  - `users` – User accounts
  - `todos` – Todo items (belongs to User)
  - `tags` – Tag definitions
  - `tag_todo` – Pivot table for many-to-many relationship

- **Query Scopes**
  - `search()` – Full-text search on title/description
  - `status()` – Filter by completion status
  - `byTag()` – Filter by tag slug
  - `dueBetween()` – Filter by due date range
  - `sortByParam()` – Dynamic sorting

### 6.2. Frontend Architecture

- **Component Structure**
  - `App.vue` – Root component with authentication UI
  - `TodoApp.vue` – Main todo management component
  - `bootstrap.js` – Axios configuration and interceptors

- **State Management**
  - Uses Vue 3 Composition API with `ref()` and `reactive()`
  - Local state management (no Vuex/Pinia needed for this scope)

- **API Communication**
  - Centralized Axios instance in `bootstrap.js`
  - Automatic token injection
  - 401 error handling with automatic logout

### 6.3. Code Quality

- **Backend**
  - Type hints on all methods
  - Form Request validation
  - Proper HTTP status codes
  - Authorization checks on all protected routes

- **Frontend**
  - Form validation with error display
  - Loading states
  - User-friendly error messages
  - Responsive design with Tailwind CSS

### 6.4. Extensibility

Easy to extend with:
- Priorities (add `priority` column to todos)
- Reminders (add `reminder_at` column)
- Sharing (add `shared_with` pivot table)
- Categories (add `category_id` foreign key)
- Attachments (add `attachments` table)

---

## 7. How to Re‑create from Scratch (Summary)

If you want to rebuild this project yourself:

1. `composer create-project laravel/laravel todo-app`
2. `cd todo-app`
3. Require Sanctum and other packages as in `composer.json`.
4. Copy the `app`, `routes`, `database/migrations`, `resources/js`, `resources/css`, `vite.config.js`, `tailwind.config.js`, and `tests` from this repo.
5. Run `php artisan migrate`, `npm install`, `npm run dev`.

This repo focuses on the **application logic and structure** while staying close to Laravel 11 + Vue 3 best practices.


