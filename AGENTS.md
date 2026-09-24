# SIGAPP Repository Instructions

## Local Application Testing

- Before every web, browser, E2E, manual UI, or authenticated HTTP test that opens the running SIGAPP application, always invoke and read `$sigapp-local-testing`.
- Keep that skill active throughout login, test execution, verification, and reporting.
- Do not start browser automation or enter credentials until the skill has been loaded.
- This requirement applies to department-specific tests for Keuangan, MKDT, Planning, Legal, Promosi, and Produksi.
- CLI-only unit or integration tests that do not open the running application do not require the skill.
