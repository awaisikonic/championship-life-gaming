Championship Life Gaming Module (WordPress Plugin)

An interactive, Duolingo-inspired daily gaming module built as a standalone WordPress plugin.
The system allows admins to configure structured daily challenges (levels, mini-games, questionnaires), while users progress through a fixed calendar-based journey with streaks, rewards, and completion milestones.

📌 Project Status

Current Phase:
✅ Step 4 — REST API implemented
⏳ Step 5 — Frontend wiring (next)

This repository contains the backend foundation, admin tooling, database schema, progress engine, and REST API required to safely wire the frontend templates without rework.

🧠 Core Concepts

Day = Level (fixed calendar based)

Week Pack = Level Pack (e.g., Day 1–7)

Mini-games are sequential per day

Mini-games = Questionnaires (MCQ, multi-select, slider, text)

Replay allowed (no points, no streak)

Streak increments on any mini-game completion

Admin-driven content, user-driven progression

🧱 Implemented Roadmap (Aligned with Plan)
✅ Step 1 — Template Integration Plan (Design-level)

All provided Tailwind HTML templates reviewed

Screen flow mapped:

Entry → User Info → Purpose → Levels → Mini-games → Questionnaire → Day Complete → Pack Complete

Data requirements for each screen identified

Templates preserved as-is (no markup changes planned)

✅ Step 2 — Plugin Foundation + Admin UI
Plugin Architecture

Clean bootstrap via CLG_Plugin

Activation / deactivation hooks

Modular class structure

Custom Post Types

Day (auto-generated)

Week Pack

Mini-game

Questionnaire

Question

Theme Preset

Admin Features

Calendar-based Day generation

Week Pack builder (range-based)

Day editor with drag-and-drop mini-game assignment

Sequential mini-game enforcement

Questionnaire builder with supported question types

Theme presets + reward settings

Demo seed importer (Week 1 / Day 1 sample data)

✅ Step 3 — Gameplay Data Layer (Progress Engine)
Custom Database Tables

wp_cl_day_minigames — Day ↔ Mini-game mapping

wp_clg_user_progress — Mini-game progress

wp_clg_user_day_progress — Day completion

wp_clg_user_week_pack_progress — Pack completion

wp_clg_points_ledger — Reward history

wp_clg_streak — Streak tracking

Progress Logic

Attempts tracking

Completion timestamps

Replay detection (no rewards)

Streak calculation

Reward ledger (points only when eligible)

✅ Step 4 — REST API (Frontend Gameplay Only)

All endpoints implemented to match the planned API contract:

Auth & Profile

GET /clg/v1/me

POST /clg/v1/profile

GET /clg/v1/purposes

POST /clg/v1/profile/purpose

Gameplay

GET /clg/v1/levels

GET /clg/v1/days/{day_id}/minigames

GET /clg/v1/questionnaires/{id}?day_id=&minigame_id=

POST /clg/v1/answers/submit

Error Handling

401 Unauthorized

404 Not Found

422 Validation error

409 Conflict (admin changes mid-session)

500 Server error

⏳ Pending Work
🔜 Step 5 — Frontend Wiring

[cl_game] shortcode app shell

SPA-style screen transitions

Horizontal levels scroll + snap

Mini-game circles with lock/unlock states

Questionnaire renderer (4 types)

Completion screens (Day / Pack)

🔜 Step 6 — QA & Polish

Timezone rollover tests

Replay abuse prevention

Admin edits after user progress

Mobile responsiveness

Performance & caching pass

🛠️ Installation (Local)

Clone the repository:

git clone https://github.com/awaisikonic/championship-life-gaming.git

Copy into WordPress plugins directory:

wp-content/plugins/championship-life-gaming

Activate via WP Admin → Plugins

On activation:

Custom tables are created

Default options are initialized

🧪 Development Notes

Frontend not wired yet by design (API-first approach)

All templates will be integrated without changing markup

REST API is considered the single source of truth for gameplay

Admin UI is intentionally completed before frontend work

📂 Repository Structure
championship-life-gaming/
├── admin/ # Admin UI, metaboxes, settings
├── assets/ # JS/CSS (frontend wiring pending)
├── includes/ # Core classes (DB, API, Progress, CPT)
├── seeds/ # Demo seed JSON
├── championship-life-gaming.php
├── README.md
└── .gitignore

📅 Versioning Strategy (Planned)

v0.1 — Admin & DB ready ✅

v0.2 — REST API ready ✅

v0.3 — Frontend wired

v1.0 — Production release

👤 Maintainer

Awais (IKONIC)
Custom WordPress & Interactive Systems Development
