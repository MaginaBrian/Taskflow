# TaskFlow — Task Manager API + Vue.js Frontend

Laravel + Vue.js Task Management Application for the Laravel Engineer Intern Take-Home Assignment.

---

## Tech Stack

| Layer    | Technology              |
|----------|-------------------------|
| Backend  | Laravel 11, MySQL       |
| Frontend | Vue 3, Pinia, Vite      |
| Hosting  | Railway (backend + DB) / Vercel (frontend) |

---

## Local Setup

### Prerequisites
- PHP 8.2+, Composer
- Node.js 18+, npm
- MySQL 8+

---

### Backend (Laravel)

```bash
# 1. Navigate to laravel directory
cd laravel

# 2. Install dependencies
composer install

# 3. Copy env and set your MySQL credentials
cp .env.example .env
# Edit .env → set DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 4. Generate app key
php artisan key:generate

# 5. Run migrations
php artisan migrate

# 6. (Optional) Seed with sample data
php artisan db:seed

# 7. Start the dev server
php artisan serve
# → http://localhost:8000
```

---

### Frontend (Vue.js)

```bash
# In a new terminal
cd vue-frontend

# Install dependencies
npm install

# Start dev server (proxies /api to Laravel)
npm run dev
# → http://localhost:5173
```

> The Vite dev server proxies all `/api/*` requests to `http://localhost:8000`, so no CORS issues locally.

---

## API Endpoints

Base URL (local): `http://localhost:8000/api`

### 1. Create Task
```
POST /api/tasks
Content-Type: application/json

{
  "title": "Fix login bug",
  "due_date": "2026-04-05",
  "priority": "high"
}
```
**Rules:**
- `title` + `due_date` combination must be unique
- `priority`: `low` | `medium` | `high`
- `due_date`: today or future

**Response 201:**
```json
{
  "message": "Task created successfully.",
  "data": {
    "id": 1,
    "title": "Fix login bug",
    "due_date": "2026-04-05",
    "priority": "high",
    "status": "pending",
    "created_at": "2026-03-30T10:00:00.000000Z",
    "updated_at": "2026-03-30T10:00:00.000000Z"
  }
}
```

---

### 2. List Tasks
```
GET /api/tasks
GET /api/tasks?status=pending
GET /api/tasks?status=in_progress
GET /api/tasks?status=done
```
Sorted: **high → medium → low** priority, then **due_date ascending**.

**Response 200:**
```json
{
  "message": "Tasks retrieved successfully.",
  "data": [ ... ],
  "total": 5
}
```

---

### 3. Update Task Status
```
PATCH /api/tasks/{id}/status
Content-Type: application/json

{ "status": "in_progress" }
```
**Valid transitions only:** `pending → in_progress → done`  
Cannot skip or revert.

**Response 200:**
```json
{
  "message": "Task status updated successfully.",
  "data": { ... }
}
```

**Error 422 (invalid transition):**
```json
{
  "message": "Invalid status transition. From 'pending', you can only move to 'in_progress'.",
  "current_status": "pending",
  "valid_next_status": "in_progress"
}
```

---

### 4. Delete Task
```
DELETE /api/tasks/{id}
```
**Only `done` tasks can be deleted.**

**Response 200:**
```json
{ "message": "Task deleted successfully." }
```

**Error 403:**
```json
{
  "message": "Forbidden. Only tasks with status \"done\" can be deleted.",
  "current_status": "in_progress"
}
```

---

### 5. Daily Report (Bonus)
```
GET /api/tasks/report?date=2026-03-30
```

**Response 200:**
```json
{
  "date": "2026-03-30",
  "summary": {
    "high":   { "pending": 2, "in_progress": 1, "done": 0 },
    "medium": { "pending": 1, "in_progress": 0, "done": 3 },
    "low":    { "pending": 0, "in_progress": 0, "done": 1 }
  }
}
```

---

## Deployment (Railway)

Railway supports Laravel + MySQL in one project.

### Step 1 — Create project
```bash
npm install -g @railway/cli
railway login
railway init
```

### Step 2 — Add MySQL Plugin
In the Railway dashboard → **New** → **Database** → **MySQL**

### Step 3 — Set environment variables
In Railway → your Laravel service → **Variables**:
```
APP_KEY=base64:...          # from php artisan key:generate
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQL_HOST}}
DB_PORT=${{MySQL.MYSQL_PORT}}
DB_DATABASE=${{MySQL.MYSQL_DATABASE}}
DB_USERNAME=${{MySQL.MYSQL_USER}}
DB_PASSWORD=${{MySQL.MYSQL_PASSWORD}}
```

### Step 4 — Deploy
```bash
# From the laravel/ directory
railway up
```

Railway will detect the PHP/Laravel project automatically.

### Step 5 — Run migrations on Railway
```bash
railway run php artisan migrate --force
railway run php artisan db:seed --force
```

### Frontend — Vercel
```bash
cd vue-frontend

# Set the API URL to your Railway backend
echo "VITE_API_URL=https://your-app.up.railway.app/api" > .env.production

# Build
npm run build

# Deploy
npx vercel deploy dist --prod
```

---

## Project Structure

```
taskmanager/
├── laravel/
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/TaskController.php
│   │   │   └── Requests/
│   │   │       ├── CreateTaskRequest.php
│   │   │       └── UpdateTaskStatusRequest.php
│   │   └── Models/Task.php
│   ├── database/
│   │   ├── migrations/
│   │   │   └── ..._create_tasks_table.php
│   │   └── seeders/
│   │       ├── DatabaseSeeder.php
│   │       └── TaskSeeder.php
│   ├── routes/api.php
│   ├── config/cors.php
│   └── .env.example
│
└── vue-frontend/
    ├── src/
    │   ├── App.vue
    │   ├── main.js
    │   ├── router.js
    │   ├── assets/main.css
    │   ├── stores/taskStore.js
    │   ├── components/
    │   │   ├── TaskCard.vue
    │   │   ├── CreateTaskModal.vue
    │   │   └── StatsBar.vue
    │   └── views/
    │       ├── TasksView.vue
    │       └── ReportView.vue
    ├── index.html
    ├── vite.config.js
    └── package.json
```

---

## Testing with curl

```bash
BASE=http://localhost:8000/api

# Create task
curl -X POST $BASE/tasks \
  -H "Content-Type: application/json" \
  -d '{"title":"Deploy hotfix","due_date":"2026-04-01","priority":"high"}'

# List all tasks
curl $BASE/tasks

# Filter by status
curl "$BASE/tasks?status=pending"

# Advance status
curl -X PATCH $BASE/tasks/1/status \
  -H "Content-Type: application/json" \
  -d '{"status":"in_progress"}'

# Mark done
curl -X PATCH $BASE/tasks/1/status \
  -H "Content-Type: application/json" \
  -d '{"status":"done"}'

# Delete (must be done)
curl -X DELETE $BASE/tasks/1

# Daily report
curl "$BASE/tasks/report?date=2026-04-01"
```

---

## Business Rules Summary

| Rule | Implementation |
|------|---------------|
| Title unique per due_date | `unique` DB constraint + form request validation |
| due_date ≥ today | `after_or_equal:today` validation rule |
| Priority: low/medium/high | `enum` column + `Rule::in()` validation |
| Status flow: pending→in_progress→done | `canTransitionTo()` method on Task model |
| Delete only done tasks | Check in controller, return 403 if not done |
| Sort: priority desc, due_date asc | `FIELD(priority, 'high','medium','low')` + `orderBy` |
| Report grouped by priority+status | `GROUP BY` query + structured PHP array |
