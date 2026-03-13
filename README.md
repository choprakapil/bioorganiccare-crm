# ClinicPro CRM Suite

A comprehensive practice management suite including a Laravel backend, CRM Dashboard, and Landing Page.

## 🏗️ Architecture
- **Backend**: Laravel PHP API
- **Frontend (CRM)**: React + Vite + Tailwind
- **Landing Page**: React + Vite + GSAP

## 📂 Project Structure
```text
/
├── backend/               → Laravel API source
├── frontend/              → CRM Dashboard source
├── landing/               → Landing page source
├── database/schema/       → Unified SQL schema
└── deploy/                → Build & Deployment scripts
```

## 🛠️ Quick Start
Refer to `deploy/deploy-notes.md` for full instructions.

1. **Setup Backend**:
   - `cd backend && composer install && cp .env.example .env`
2. **Setup Frontend**:
   - `cd frontend && npm install && cp .env.example .env`
3. **Setup Landing**:
   - `cd landing && npm install && cp .env.example .env`

## 🚀 Building for Production
```bash
bash deploy/build-frontend.sh
bash deploy/build-landing.sh
```

## 📄 Documentation
- `PROJECT_STRUCTURE.md`: Detailed architectural breakdown.
- `WALKTHROUGH.md`: Summary of the latest architectural reset.
