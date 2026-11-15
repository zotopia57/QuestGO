# QuestGo

QuestGo is an India-focused travel planning website built with PHP 8+, MySQL
or MariaDB, Bootstrap 5, HTML, CSS and JavaScript. It uses one shared account
system with separate user and administrator roles.

## Features

- Public home, registration and login pages.
- User dashboard and account profile/password management.
- Journey planning, journey stops, destination discovery and saved places.
- Sourced Useful Stops with destination, city and category filters; saveable
  stop and activity suggestions.
- A rule-based free-time matcher for preset time windows, INR budget, activity
  category and interests.
- A separate lesser-known places collection with place details, travel tips,
  access notes, local photographs where available, and journey/save actions.
- Emergency Information with India's 112 emergency number; QuestGo is not an
  emergency service and does not provide live location-based assistance.
- Personal travel expenses and notes.
- Separate administrator dashboard, reports, and user/place/activity/category
  management, including sourced stop records and lesser-known place details.
- Destination data organised by state or Union Territory, city, region and
  category, including examples in all 28 states and 8 Union Territories.
- INR costs, kilometre distances when supplied, and DD-MM-YYYY date display.
- Responsive layouts, local destination photographs with credits, and regional
  SVG illustrations.

Destination/activity costs, duration and seasons are sample planning estimates,
not guaranteed prices or availability. QuestGo does not provide live traffic,
GPS, opening hours, transport schedules, ticket prices or service availability.

## Requirements

- XAMPP with Apache, PHP 8+ and MySQL/MariaDB.
- PHP extensions `pdo_mysql` and `mbstring`.
- A modern browser with JavaScript enabled.

## Run locally with XAMPP

1. Copy the project folder into `C:\xampp\htdocs\QuestGo`.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin`.
4. Import `database/questgo.sql` using the **Import** tab. This creates the
   `questgo` database, its original application tables, categories, 57 sample
   destinations and 41 activities.
5. To connect the destination records to the local photo files, import
   `database/destination-photographs.sql`. The photographs and their individual
   credits/licences are in `assets/images/destinations/`.
6. If the database is already installed and you only want the expanded
   destination/activity examples, import
   `database/india-destination-expansion.sql`, then run
   `database/destination-photographs.sql`. Both scripts preserve existing user
   data.
7. Import `database/discovery-upgrade.sql` through phpMyAdmin to add the
   sourced-stop directory, saved-stop/activity links, activity metadata and
   lesser-known destination fields. This is additive and safe to rerun. It adds
   three destination records with matching local photographs, additional
   category-specific activity ideas and one location-verified OpenStreetMap
   fuel-station sample. Add other stop records only with a source that
   supports their address and details.
8. From a trusted terminal in the project directory, create an administrator:

   ```text
   C:\xampp\php\php.exe database\create-admin.php
   ```

   The command prompts for administrator credentials and stores a hash, not a
   plain-text password. Public registration always creates a normal user.
9. Visit `http://localhost/QuestGo/`.

The local database connection is configured in `backend/db.php` and defaults to
host `localhost`, port `3306`, database `questgo`, user `root` and an empty
password. Change it there if your local MySQL setup differs. Do not use the
default local credentials for a public deployment.

## Project layout

```text
QuestGo/
├── assets/
│   ├── css/          # Shared and page-specific stylesheets
│   ├── images/       # Local destination photographs and regional illustrations
│   └── js/           # Shared interactions and portal data rendering
├── backend/
│   ├── admin/        # Role-protected administrator handlers
│   ├── user/         # Owner-scoped user handlers
│   ├── auth.php      # Sessions, roles, CSRF, validation and responses
│   └── db.php        # Shared PDO database connection
├── database/
│   ├── questgo.sql   # Schema and initial sample data (reset/import script)
│   ├── india-destination-expansion.sql
│   ├── discovery-upgrade.sql
│   ├── create-admin.php
│   └── README.md     # phpMyAdmin and database setup details
├── includes/         # Shared headers, footers, navigation and layouts
├── pages/
│   ├── admin/
│   └── user/
└── index.php
```

## Database notes

`database/questgo.sql` drops and recreates the original application tables when
imported, so importing it again deletes existing application records. Back up
the database first. For an existing installation, use the additive India data
migration and discovery upgrade rather than re-importing the reset script.

Prepared statements, password hashing, server-side role checks, CSRF tokens
and owner-scoped record access are used for application operations. Useful
Stops listings are stored sourced records, not a live directory; always
verify that essential services are open and available. Journey filtering uses
only the selected journey's start and destination cities and does not claim
that a stop lies on the route. OpenStreetMap-derived records should be
attributed to OpenStreetMap contributors. The current seed set is intentionally
small because unverified business names, addresses, contact details and
photographs are not invented.

More detailed database and phpMyAdmin guidance is in
[`database/README.md`](database/README.md).
