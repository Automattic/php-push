# Repository Instructions

Use Composer scripts as the project task runner.

- Install dependencies with `composer install`.
- Run lint with `composer lint`.
- Run static analysis with `composer psalm`.
- Run the full test suite with `composer test`.
- Run the faster non-end-to-end PHPUnit suite with `composer quicktest`.
- Run end-to-end tests with `composer e2e`.

GitHub Actions CI is defined in `.github/workflows/CI.yml`.
PHP_CodeSniffer rules are configured in `.phpcs.xml`.
