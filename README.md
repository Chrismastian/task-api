# TaskFlow API

REST API for **TaskFlow**, a task-management app. Laravel 10 + Sanctum token auth.

**Live API:** `https://task-api-production.up.railway.app` (updated after deploy)

## Stack

| Layer | Tech |
|-------|------|
| Framework | Laravel 10 |
| Auth | Laravel Sanctum (bearer tokens) |
| Database | MySQL 8 |
| Tests | PHPUnit — 8 feature tests, 18 assertions |
| Deploy | Railway (Nixpacks) |

## Endpoints

| Method | Path | Auth | Description |
|--------|------|------|-------------|
| POST | `/api/auth/register` | — | Create account, returns token |
| POST | `/api/auth/login` | — | Exchange credentials for a token |
| GET | `/api/me` | ✔ | Current user |
| POST | `/api/auth/logout` | ✔ | Revoke current token |
| GET | `/api/tasks` | ✔ | List tasks (`?status=`, `?priority=`, `?search=`) |
| POST | `/api/tasks` | ✔ | Create task |
| GET | `/api/tasks/{id}` | ✔ | Show one task |
| PUT | `/api/tasks/{id}` | ✔ | Update task |
| DELETE | `/api/tasks/{id}` | ✔ | Delete task |

## Design notes

- **Ownership isolation** — a task belonging to another user returns **404, not 403**, so the API never confirms that someone else's task id exists.
- **Status and `completed_at` are kept in sync in the model** (`booted()` hook), so they cannot disagree no matter which endpoint changes them.
- **One token per device name** — logging in again on the same device revokes the previous token instead of piling tokens up.
- **Sorting** — tasks are ordered by priority (high → low), then due date.

## Run locally

```bash
composer install
cp .env.example .env && php artisan key:generate
php artisan migrate
php artisan serve
```

```bash
php artisan test
```

## Example

```bash
TOKEN=$(curl -s -X POST $API/api/auth/login \
  -H "Content-Type: application/json" -H "Accept: application/json" \
  -d '{"email":"you@example.com","password":"password123"}' | jq -r .token)

curl -s $API/api/tasks -H "Authorization: Bearer $TOKEN" -H "Accept: application/json"
```
