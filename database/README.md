# QuestGo database setup

The database uses MySQL/MariaDB with InnoDB and `utf8mb4`. Importing
`questgo.sql` creates the `questgo` database, its nine tables and sample
categories, destinations and activities. The seed includes destinations in all
28 states and 8 Union Territories, plus additional examples such as Ahmedabad,
Dwarka, Udaipur, Alleppey, Kochi, Manali, Shillong, Varanasi and Hampi.

Destination and activity costs, durations and seasons are illustrative planning
estimates, not guaranteed prices, schedules or availability. Optional distances
are stored only when supplied; `NULL` means no distance estimate is provided.

## XAMPP connection settings

The shared PDO connection is in `backend/db.php`. Its local XAMPP defaults are
host `localhost`, port `3306`, database `questgo`, user `root` and an empty
password. If your local MySQL configuration uses another port or credentials,
update those values in `backend/db.php`. Do not copy credentials into page
files or commit production credentials.

## Import with phpMyAdmin

1. Put the QuestGo project folder under XAMPP's `htdocs` directory.
2. Start Apache and MySQL from the XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin`.
4. Choose **Import**, select `database/questgo.sql`, keep the format as **SQL**,
   then select **Import** at the bottom of the page.
5. The script creates and selects the `questgo` database. Open it in
   phpMyAdmin to check the nine tables and sample rows.
6. Import `database/destination-photographs.sql` to set local photo paths for
   the seeded destinations. The photographs are stored in
   `assets/images/destinations/`; their source pages, creators and licences are
   listed in `assets/images/destinations/ATTRIBUTION.md`.
7. Import `database/discovery-upgrade.sql` to add the Useful Stops and saved
   discovery tables, activity metadata and lesser-known place fields. This
   migration preserves existing records and can be run again safely.

Alternatively, create a database named `questgo` in phpMyAdmin first, select
it, and import the SQL file from that database page.

The SQL file drops and recreates the nine QuestGo tables so it can be used as a
repeatable development reset. **Importing it again deletes all data currently
in those tables. Back up data before re-importing.**

If QuestGo is already installed and you want to add the expanded India
destination examples without resetting user data, import
`database/india-destination-expansion.sql` through phpMyAdmin instead. It adds
the missing destination categories, places and sample activities without
deleting existing records, and can be run again safely. Then import
`database/destination-photographs.sql` to connect destination rows to local
photographs; this update does not delete records or change filters.
After those scripts, import `database/discovery-upgrade.sql` for the
discovery-section schema and initial records.

In the user workspace, open **Explore places** and choose a state or Union
Territory to filter its stored destinations. Select **View details** on a place
to see its image, description, estimated cost and stored activity suggestions.
From the destination card, users can save the place or add it as a stop to one
of their own journeys. These features require a signed-in user account.

## Create the initial administrator

Do not add an administrator password to the SQL file. After importing the
schema, open a terminal in the project folder and run:

```text
php database\create-admin.php
```

The script asks for the administrator's name, email and password, validates
the input, hashes the password with PHP `password_hash()` and inserts the
account using a PDO prepared statement. Choose a unique password of at least
12 characters. Password input is read in the terminal and may be visible as
you type, depending on the terminal; use a trusted local terminal and avoid
capturing or sharing the session.

The script uses the same connection in `backend/db.php` as the website.

The setup script is CLI-only and refuses web requests. Keep it out of public
web access if the project is deployed.

## Run QuestGo with Apache

With Apache and MySQL running in XAMPP, open
`http://localhost/QuestGo/` in a browser. PHP pages can include
`backend/db.php` to use the shared `$pdo` connection. Queries should use PDO
prepared statements, for example:

```php
$statement = $pdo->prepare('SELECT id, name FROM users WHERE email = :email');
$statement->execute(['email' => $email]);
$user = $statement->fetch();
```

## Table and deletion notes

- Journeys, expenses and notes reference their owner. Expenses and notes use a
  composite foreign key to ensure an attached journey belongs to the same user.
- Deleting a user is restricted while they own journeys, expenses or notes.
  Remove or archive those records deliberately before account removal.
- Deleting a journey is restricted while it has stops or linked expenses/notes.
- Deleting a place is restricted while activities or journey stops reference it.
  Saved-place links are removed with their user or place.
- Category rows cannot be removed while places or activities use them.
- `estimated_cost` and `estimated_budget` values are amounts in Indian Rupees.
  Optional distance values are in kilometres; a `NULL` distance means no
  distance estimate is stored.
- Destination and activity records are sample planning data. Costs, seasons,
  permissions, local access and activity details should be verified before
  travel; no live prices, opening hours or availability are provided.
- Useful Stops are stored sourced records, not a live directory. The initial
  data contains one location-verified OpenStreetMap listing; populate other
  categories through the admin page only when the address and details can be
  supported by a source. Attribute OpenStreetMap-derived records to
  OpenStreetMap contributors. Journey filtering matches only its start and
  destination cities, not route proximity.
