---
name: sigapp-local-testing
description: Mandatory before any local SIGAPP web, browser, E2E, manual UI, or authenticated HTTP test. Use the fixed localhost URL and the correct test account when testing a department flow or logging in as Keuangan, MKDT, Planning, Legal, Promosi, or Produksi in the sigapp.dev workspace.
---

# SIGAPP Local Testing

Use these settings only for the local SIGAPP development project at
`c:\Users\Salivs\Data\laragon\www\sigapp.dev`.

## Activation Requirement

Read and apply this skill before starting any test that opens the running SIGAPP application, sends an authenticated HTTP request to it, or requires a department login. Keep it active for the entire test flow, including verification and reporting.

## Local URL

- Start every web, browser, E2E, or manual UI test at `http://localhost/sigapp.dev`.
- Keep the `/sigapp.dev` base path. Do not silently substitute another local domain, `127.0.0.1`, staging, or production.
- Follow links and redirects from that base URL normally. If the application redirects outside the local origin unexpectedly, stop and report it before entering credentials.
- CLI unit and integration tests that do not open the application remain CLI tests; do not invent a browser step just to use the URL.

## Department Test Accounts

Choose the account that matches the department being tested:

| Department | Modules Covered | Username | Password |
| --- | --- | --- | --- |
| **Keuangan** | Keuangan, Pajak, CashOut, PencairanJaminan | `nisalis` | `nisalis123` |
| **MKDT** | MKDT, Konsumen, Transaksi | `citra` | `citra123` |
| **Planning** | Proyek, Cluster, Jalan, Tipe, Kavling, Siteplan | `purnama` | `purnama123` |
| **Legal** | Legal, HGB, PBB, PBG, AJB | `marlina` | `marlina123` |
| **Promosi** | MGM, Promosi, Event | `benny` | `benny123` |
| **Produksi** | Produksi, BPB, SPK, Gudang, TiketMasalah | `alika` | `alikas123` |

When the requested department is unclear and the behavior is department-specific, infer it from the feature owner or acceptance criteria when reliable; otherwise ask which department to use. Do not guess credentials for departments not listed here.

## Testing Rules

- Treat all listed credentials as local test credentials. Never use them on a non-local origin.
- Enter passwords only into the local login form. Do not copy them into source files, test artifacts, screenshots, commit messages, reports, or chat summaries.
- Begin at the base URL and use the application's actual login flow; do not assume a login route that has not been observed.
- For tests involving multiple departments, log out between accounts or use isolated browser contexts. Confirm the visible user or department after each login before evaluating the feature.
- If login fails, capture the visible error and relevant non-secret context. Do not reset passwords, change users, or mutate authentication data unless explicitly requested.
- Report which department was tested, the local URL, the tested behavior, and the observable result. Never repeat the password in the report.
