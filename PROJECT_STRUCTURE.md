# Project Structure

## Directory Overview

```
laravel-vue-todo/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php      # Authentication endpoints
│   │   │   ├── TodoController.php      # Todo CRUD operations
│   │   │   └── TagController.php       # Tag management
│   │   └── Requests/
│   │       ├── Auth/
│   │       │   ├── LoginRequest.php
│   │       │   └── RegisterRequest.php
│   │       └── Todo/
│   │           ├── StoreTodoRequest.php
│   │           └── UpdateTodoRequest.php
│   └── Models/
│       ├── User.php                    # User model with Sanctum
│       ├── Todo.php                    # Todo model with scopes
│       └── Tag.php                     # Tag model
├── bootstrap/
│   └── app.php                         # Application bootstrap
├── config/
│   └── sanctum.php                     # Sanctum configuration
├── database/
│   ├── factories/
│   │   ├── UserFactory.php
│   │   └── TodoFactory.php
│   └── migrations/
│       ├── 2024_01_01_000000_create_todos_table.php
│       ├── 2024_01_01_000001_create_tags_table.php
│       └── 2024_01_01_000002_create_tag_todo_table.php
├── public/
│   ├── index.php                       # Entry point
│   └── .htaccess
├── resources/
│   ├── css/
│   │   └── app.css                     # Tailwind CSS entry
│   ├── js/
│   │   ├── app.js                      # Vue app entry
│   │   ├── bootstrap.js                 # Axios configuration
│   │   ├── App.vue                     # Root component
│   │   └── components/
│   │       └── TodoApp.vue              # Main todo component
│   └── views/
│       └── welcome.blade.php            # Main HTML template
├── routes/
│   ├── api.php                         # API routes
│   ├── web.php                         # Web routes
│   └── console.php                     # Console routes
├── tests/
│   ├── Feature/
│   │   ├── AuthTest.php                # Auth tests
│   │   └── TodoTest.php                # Todo tests
│   └── TestCase.php
├── composer.json                        # PHP dependencies
├── package.json                        # Node dependencies
├── vite.config.js                      # Vite configuration
├── tailwind.config.js                  # Tailwind configuration
├── postcss.config.cjs                  # PostCSS configuration
├── phpunit.xml                         # PHPUnit configuration
├── README.md                           # Main documentation
├── SETUP.md                            # Quick setup guide
└── .gitignore                          # Git ignore rules
```

## Key Files Explained

### Backend (Laravel)

- **Models**: Eloquent models with relationships and query scopes
- **Controllers**: Handle HTTP requests and return JSON responses
- **Requests**: Form request validation classes
- **Migrations**: Database schema definitions
- **Routes**: API endpoint definitions

### Frontend (Vue.js)

- **app.js**: Vue application entry point
- **bootstrap.js**: Axios configuration and interceptors
- **App.vue**: Root component with authentication UI
- **TodoApp.vue**: Main todo management component with CRUD operations

### Configuration

- **vite.config.js**: Vite bundler configuration for Vue
- **tailwind.config.js**: Tailwind CSS configuration
- **sanctum.php**: Laravel Sanctum authentication configuration

## API Endpoints

### Authentication
- `POST /api/register` - Register new user
- `POST /api/login` - Login user
- `POST /api/logout` - Logout user (auth required)
- `GET /api/me` - Get current user (auth required)

### Todos
- `GET /api/todos` - List todos with filters (auth required)
- `POST /api/todos` - Create todo (auth required)
- `GET /api/todos/{id}` - Get single todo (auth required)
- `PUT /api/todos/{id}` - Update todo (auth required)
- `DELETE /api/todos/{id}` - Delete todo (auth required)
- `PATCH /api/todos/{id}/toggle` - Toggle completion (auth required)

### Tags
- `GET /api/tags` - List user's tags (auth required)

## Database Schema

### users
- id, name, email, password, email_verified_at, remember_token, timestamps

### todos
- id, user_id, title, description, completed, due_date, timestamps

### tags
- id, name, slug, timestamps

### tag_todo (pivot)
- id, todo_id, tag_id, timestamps

