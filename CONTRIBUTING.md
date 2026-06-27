# Contributing to IROYIN

Thank you for your interest in contributing! This document explains how to report issues, suggest improvements, and submit code changes.

---

## Ways to Contribute

- **Bug reports** — found something broken? Open an issue
- **Feature requests** — have an idea? Open an issue to discuss it first
- **Code contributions** — fix a bug or implement an agreed-upon feature via pull request
- **Documentation** — improve the README, add examples, fix typos
- **Translations** — the admin panel is in Brazilian Portuguese; translations are welcome

---

## Reporting Bugs

Before opening an issue:

1. Check if the bug is already reported in [Issues](https://github.com/marcosFigueredo/iroyin/issues)
2. Check the [Troubleshooting section](README.md#troubleshooting) in the README

When opening a bug report, include:

- IROYIN version (git tag or commit hash)
- PHP version (`php -v`)
- Laravel version (`php artisan --version`)
- OS and web server
- Steps to reproduce
- Expected vs. actual behavior
- Relevant output from `storage/logs/laravel.log`

---

## Suggesting Features

Open an issue with the `enhancement` label. Describe:

- The problem you are trying to solve
- Your proposed solution
- Alternatives you considered

Please open an issue **before** starting implementation on a large feature — this avoids duplicated effort and ensures the change fits the project direction.

---

## Submitting a Pull Request

### 1. Fork and clone

```bash
git clone https://github.com/YOUR_USERNAME/iroyin.git
cd iroyin
git checkout -b feature/your-feature-name
```

### 2. Set up the development environment

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# configure .env with your local database
php artisan migrate
php artisan db:seed
```

### 3. Make your changes

- Keep changes focused — one concern per PR
- Follow the existing code style (PSR-12 for PHP)
- Do not introduce new dependencies without discussing first
- Do not add error handling for scenarios that cannot happen
- Do not add comments that explain *what* the code does — only *why* when non-obvious

### 4. Run the test suite

```bash
php artisan test
```

All 23 tests must pass before submitting.

### 5. Commit and push

```bash
git add .
git commit -m "Short description of the change"
git push origin feature/your-feature-name
```

### 6. Open the pull request

- Target branch: `main`
- Title: short and descriptive
- Description: what changed and why; reference any related issue (`Closes #123`)

---

## Code Style

- **PHP**: PSR-12. Use `array_map`, `collect()`, and Eloquent scopes over raw loops where readable
- **Blade**: keep logic out of views; pass computed data from controllers
- **JavaScript**: vanilla ES6+, no frameworks. Keep kiosk JS modules single-responsibility
- **CSS**: add to `public/assets/css/style.css`; use the existing comment-block structure for new sections

---

## Commit Message Format

```
Short imperative summary (max 72 chars)

Optional longer description explaining the motivation and context.
Reference issues with: Closes #N
```

---

## License

By submitting a pull request you agree that your contribution will be licensed under the [MIT License](LICENSE).
