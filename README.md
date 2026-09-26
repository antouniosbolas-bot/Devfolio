# DevFolio

DevFolio turns verified GitHub work into a professional portfolio.

## Current foundation

- Laravel 11 on PHP 8.2+
- Laravel Breeze with Livewire 3 and Volt
- Tailwind CSS, Vite, and Alpine-ready frontend
- Email/password authentication
- GitHub OAuth account association
- SQLite for the default local test environment; MySQL and Redis are configured in `.env.example`

## Local setup

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate

# Configure DB_* and Redis values in .env for production-like development.
php artisan migrate

$env:Path = "C:\Program Files\nodejs;" + $env:Path
npm.cmd install
npm.cmd run build
php artisan serve
```

For queues, run `php artisan queue:work` with Redis configured.
Qwen uses the OpenAI-compatible DashScope endpoint by default. Set the exact endpoint and model for your Qwen account in `.env`; the API key is sent only by the server.

## GitHub OAuth

Create an OAuth App in GitHub with the callback URL:

```text
http://127.0.0.1:8000/auth/github/callback
```

Set `GITHUB_CLIENT_ID`, `GITHUB_CLIENT_SECRET`, and `GITHUB_REDIRECT_URI` in `.env`. The OAuth flow requests `read:user`, `user:email`, and `repo` scopes so later synchronization can read the authenticated developer's repositories.

The GitHub OAuth app's Authorization callback URL must match `GITHUB_REDIRECT_URI` exactly. For the default local setup, use:

```text
http://127.0.0.1:8000/auth/github/callback
```

Register that exact value in the GitHub OAuth App. Do not use `localhost`, a trailing slash, another port, or `http`/`https` interchangeably. The connection page displays the configured callback to help diagnose GitHub's `redirect_uri is not associated with this application` error.

DevFolio requests only the `read:user` and `user:email` OAuth scopes. Repository synchronization is restricted to public repositories; private-repository access is not requested.

Do not paste the client secret into source control. Add it only to the local `.env` file.

## External service credentials

The project keeps credentials in environment variables only:

- GitHub OAuth: `GITHUB_CLIENT_ID`, `GITHUB_CLIENT_SECRET`, `GITHUB_REDIRECT_URI`
- Qwen content generation: `QWEN_API_KEY`, `QWEN_BASE_URL`, `QWEN_MODEL`, `QWEN_TIMEOUT`, `QWEN_RETRIES`
- OpenRouter remains supported as a backwards-compatible Qwen-compatible endpoint when `QWEN_*` settings are not configured.
- Stripe environment values are reserved; billing routes and UI are not implemented yet.
- Redis queues/cache: `REDIS_HOST`, `REDIS_PASSWORD`, `REDIS_PORT`

## Testing

```powershell
php artisan test
```

The test suite uses the local SQLite database configured by `phpunit.xml`, so it does not require MySQL or Redis.

## License

This project is private application software. Review the repository's licensing policy before distributing it.
