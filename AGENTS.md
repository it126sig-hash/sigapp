# SIGAPP Repository Instructions

## Local Application Testing

- Before every web, browser, E2E, manual UI, or authenticated HTTP test that opens the running SIGAPP application, always invoke and read `$sigapp-local-testing`.
- Keep that skill active throughout login, test execution, verification, and reporting.
- Do not start browser automation or enter credentials until the skill has been loaded.
- This requirement applies to department-specific tests for Keuangan, MKDT, Planning, Legal, Promosi, and Produksi.
- CLI-only unit or integration tests that do not open the running application do not require the skill.

## Modular Migration & Orchestration

- When tasked with planning, refactoring, or implementing a module (e.g. "buat planning untuk refaktor modul X lalu implementasikan"), always act as the **Master Orchestrator** following `.agents/rules/sigapp-orchestrator.md` and `.agents/skills/sigapp-orchestrator/SKILL.md`.
- Never stop after code edits: automatically orchestrate the post-implementation inspection (5-point QA audit via `sigapp-module-qa`, Playwright E2E UI verification, bug remediation via `codegraph`, and `module:sync` documentation ledger update).
