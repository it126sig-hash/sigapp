# SIGAPP Repository Instructions

## Local Application Testing

- Before every web, browser, E2E, manual UI, or authenticated HTTP test that opens the running SIGAPP application, always invoke and read `.agents/skills/sigapp-local-testing/SKILL.md` (`$sigapp-local-testing`).
- Keep that skill active throughout login, test execution, verification, and reporting.
- Do not start browser automation or enter credentials until the skill has been loaded.
- This requirement applies to department-specific tests for Keuangan, MKDT, Planning, Legal, Promosi, and Produksi.
- CLI-only unit or integration tests that do not open the running application do not require the skill.

## Master Orchestration & Multi-Agent Development Workflow

- When tasked with any development, feature addition, bugfix, UI/report change, or refactoring in SIGAPP, always act as the **Master Orchestrator** following `.agents/rules/sigapp-orchestrator.md` and `.agents/skills/sigapp-orchestrator/SKILL.md`.
- Enforce strict separation of responsibilities across the **5 specialist subagents** defined in `.agents/skills/sigapp-orchestrator/references/subagent-definitions.md`:
  1. **`sigapp-explorer`** (Fase 1 & RCA): Read-Only codebase & `codegraph` exploration.
  2. **`sigapp-engineer`** (Fase 2 & Fase 3B Backend): Surgical Backend CI4 implementation (`codeigniter4`, `sigapp-notifikasi`).
  3. **`sigapp-frontend`** (Fase 2 & Fase 3B Frontend/UI): Dedicated Frontend, View, CSS/JS, Modal, & Report implementation strictly following `.agents/skills/sigapp-ui-acuan/SKILL.md` (`sigapp-ui-acuan`).
  4. **`sigapp-qa`** (Fase 3): Independent QA, UI baseline (`sigapp-ui-acuan`), & Playwright E2E auditor (never edits code; issues `PASS`/`FAIL` certificate).
  5. **`sigapp-docs`** (Fase 4 — Mandatory All Modes): Post-PASS module documentation custodian — always records changes and updates `docs/modules/<Modul>/MODULE.md` (`module:sync` + module status, routes, controller, service, repo, model, view, public contracts, key functions, deprecated functions/shims, & change log), plus `MIGRATION_PROGRESS.md` (for `REFACTOR`) and `modul-notifikasi.md`.
