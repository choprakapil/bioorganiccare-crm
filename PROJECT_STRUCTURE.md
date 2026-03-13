# Project Structure: ClinicPro CRM

This repository contains the full source code for the ClinicPro CRM ecosystem.

## Root Directory
- `backend/`        : Laravel API source code (PHP)
- `frontend/`       : React CRM Dashboard source code (Vite)
- `landing/`        : React Landing Page source code (Vite)
- `database/`       : Clean database schemas
- `deploy/`         : Build and deployment utility scripts
- `.gitignore`      : Global ignore rules for build artifacts
- `README.md`       : Global project documentation

## Component Breakdown

### 1. Backend (Laravel)
- **Framework**: Laravel 11.x
- **Core Logic**: Models, Controllers, and API routes.
- **Environment**: Managed via `backend/.env`.

### 2. Frontend (CRM)
- **Framework**: React 19 + Vite
- **Styling**: Tailwind CSS
- **Features**: Patient management, billing, clinical records.

### 3. Landing (Website)
- **Framework**: React 19 + Vite
- **Animations**: GSAP + ScrollTrigger
- **Lead Capture**: Integrated with Backend API.

### 4. Database
- `database/schema/crm_complete.sql`: Unified schema containing all tables.

## Build Policy
- **ZERO artifacts** are stored in version control.
- `node_modules` and `vendor` must be installed at build time.
- `dist` and `build` folders are generated only during deployment.
