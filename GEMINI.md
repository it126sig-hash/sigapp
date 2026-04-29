apaka# GEMINI.md - SIGAPP Project Context

## Project Overview
**SIGAPP** is a comprehensive real estate and property management system built with **CodeIgniter 4**. It manages the entire lifecycle of property development, from project planning and plot (kavling) management to sales, legal documentation, financial tracking, and construction progress.

### Key Technologies
- **Backend:** PHP 8.1+, CodeIgniter 4.x
- **Database:** MySQL
- **Authentication:** [Myth/Auth](https://github.com/lonnieezell/Myth-Auth)
- **Reporting:** mPDF, dompdf (PDF generation), PhpSpreadsheet (Excel export)
- **Frontend:** jQuery, DataTables (with FixedColumns), Select2, SweetAlert2, Flatpickr, FontAwesome
- **Pattern:** MVC with an additional **Repository Pattern** layer for data access.

---

## Architecture & Directory Structure
The project follows the standard CodeIgniter 4 structure with some custom additions:

- `app/Controllers/`: Handles incoming requests and orchestrates logic via Repositories.
- `app/Models/`: Standard CI4 Models for database interaction.
- `app/Repositories/`: **Custom Layer** that contains complex business logic and database queries, abstracting them from Controllers.
- `app/Views/`: UI templates. Note that some shared modals are located in `app/Views/siteplan/`.
- `app/Config/`: Project configuration (Routes, Database, App settings).
- `public/`: The web root, containing `index.php` and entry points.
- `app-assets/` & `assets/`: Contain CSS, JS, and vendor libraries.
- `.kiro/specs/`: Contains detailed design specifications and requirements for new features or redesigns (e.g., `list-kavling-redesign`).

---

## Core Modules
- **Project Management:** `Proyek`, `Cluster`, `Jalan`, `Tipe`.
- **Plot (Kavling) Management:** Tracking status, pricing, and technical details of individual units.
- **Customer & Transaction:** `Konsumen`, `MKDT` (Manajemen Konsumen dan Transaksi).
- **Finance:** `Keuangan`, `Pajak` (PPH, BPHTB), `CashOut` (Sub-contractor payments).
- **Legal:** `Legal` module for tracking HGB, PBB, PBG, and AJB status.
- **Production:** `Produksi` for tracking construction milestones (ST 0%, 25%, 50%, 75%, 100%).

---

## Building and Running
1.  **Installation:**
    ```bash
    composer install
    ```
2.  **Configuration:**
    - Copy `env` to `.env`.
    - Set `database.default.hostname`, `database.default.database`, `database.default.username`, and `database.default.password`.
    - Set `app.baseURL`.
3.  **Development Server:**
    ```bash
    php spark serve
    ```
4.  **Testing:**
    ```bash
    vendor/bin/phpunit
    ```

---

## Development Conventions
- **Repository Pattern:** Always check for an existing Repository in `app/Repositories/` before adding complex queries to a Controller or Model. Controllers should ideally only call Repository methods.
- **Naming Convention:** Follows standard PHP/CI4 PSR-4 autoloading.
- **Modals:** Many data-entry forms are implemented as modals (e.g., in `app/Views/siteplan/`) to allow editing directly from list views.
- **DataTables:** Extensively used for data grids. The project often uses `FixedColumns` to keep identity columns visible during horizontal scrolling.
- **Roles:** The system uses a role-based access control (RBAC) where specific roles (e.g., Keuangan: 3, MKDT: 4, Legal: 5) determine which tabs or modals a user can access.

---

## Documentation & Specs
Refer to `.kiro/specs/` for detailed functional requirements when working on specific features like the `list-kavling` redesign. These files (design.md, requirements.md, tasks.md) provide the "source of truth" for those specific implementations.
