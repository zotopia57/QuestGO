USE questgo;
SET NAMES utf8mb4;

ALTER TABLE places
    ADD COLUMN IF NOT EXISTS is_hidden_gem TINYINT(1) NOT NULL DEFAULT 0,
    ADD COLUMN IF NOT EXISTS discovery_status ENUM('lesser_known', 'established') NOT NULL DEFAULT 'lesser_known',
    ADD COLUMN IF NOT EXISTS things_to_do TEXT DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS travel_tips TEXT DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS access_notes TEXT DEFAULT NULL;

ALTER TABLE activities
    ADD COLUMN IF NOT EXISTS image_url VARCHAR(2048) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS best_time VARCHAR(160) DEFAULT NULL;

CREATE TABLE IF NOT EXISTS useful_stop_categories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_name VARCHAR(80) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_useful_stop_categories_name (category_name)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO useful_stop_categories (category_name) VALUES
    ('Restaurants and local food'),
    ('Cafes and roadside dhabas'),
    ('Fuel stations'),
    ('EV charging stations'),
    ('Public toilets and washrooms'),
    ('Parking facilities'),
    ('Rest stops'),
    ('ATMs'),
    ('Pharmacies'),
    ('Hospitals and clinics'),
    ('Police stations'),
    ('Tourist information centres');

CREATE TABLE IF NOT EXISTS useful_stops (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_id INT UNSIGNED NOT NULL,
    place_id INT UNSIGNED DEFAULT NULL,
    name VARCHAR(180) NOT NULL,
    address VARCHAR(500) NOT NULL,
    city VARCHAR(120) NOT NULL,
    state_or_ut VARCHAR(120) NOT NULL,
    description TEXT NOT NULL,
    image_url VARCHAR(2048) DEFAULT NULL,
    contact_phone VARCHAR(40) DEFAULT NULL,
    distance_km DECIMAL(8,2) UNSIGNED DEFAULT NULL COMMENT 'Only populated when supported by a recorded source',
    source_url VARCHAR(2048) NOT NULL,
    source_name VARCHAR(120) NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_useful_stops_name_location (name, city, state_or_ut),
    KEY idx_useful_stops_category_location (category_id, state_or_ut, city),
    KEY idx_useful_stops_place (place_id),
    CONSTRAINT fk_useful_stops_category FOREIGN KEY (category_id)
        REFERENCES useful_stop_categories (id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_useful_stops_place FOREIGN KEY (place_id)
        REFERENCES places (id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS saved_useful_stops (
    user_id INT UNSIGNED NOT NULL,
    useful_stop_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, useful_stop_id),
    CONSTRAINT fk_saved_useful_stops_user FOREIGN KEY (user_id)
        REFERENCES users (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_saved_useful_stops_stop FOREIGN KEY (useful_stop_id)
        REFERENCES useful_stops (id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS saved_activities (
    user_id INT UNSIGNED NOT NULL,
    activity_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, activity_id),
    CONSTRAINT fk_saved_activities_user FOREIGN KEY (user_id)
        REFERENCES users (id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_saved_activities_activity FOREIGN KEY (activity_id)
        REFERENCES activities (id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categories (category_name, category_type)
SELECT additions.category_name, 'activity'
FROM (
    SELECT 'Nature and outdoors' AS category_name
    UNION ALL SELECT 'Food and local experiences'
    UNION ALL SELECT 'Photography'
    UNION ALL SELECT 'Relaxation'
    UNION ALL SELECT 'Family-friendly'
    UNION ALL SELECT 'Culture and heritage'
) AS additions
WHERE NOT EXISTS (
    SELECT 1 FROM categories c
    WHERE c.category_name = additions.category_name AND c.category_type = 'activity'
);

INSERT INTO places
    (name, city, state_or_ut, region, category_id, description, image_url, estimated_cost,
     suggested_duration, distance, best_visiting_season, is_hidden_gem, things_to_do,
     travel_tips, access_notes)
SELECT 'Ziro Valley', 'Ziro', 'Arunachal Pradesh', 'Northeast', c.id,
       'A highland valley known for Apatani cultural landscapes, paddy fields and pine-covered hills. It is a distinctive regional destination, not an undiscovered place.',
       'assets/images/destinations/ziro-valley.jpg', 6000.00, '2-3 days', NULL, 'March-October', 1,
       'Explore the town and valley viewpoints; learn about local Apatani culture through respectful, locally guided visits.',
       'Check current road conditions and seasonal weather. Ask permission before photographing residents or private land.',
       'Some visitors may require an Inner Line Permit for Arunachal Pradesh. Check current official requirements before travel.'
FROM categories c
WHERE c.category_name = 'Nature' AND c.category_type = 'destination'
  AND NOT EXISTS (SELECT 1 FROM places p WHERE p.name = 'Ziro Valley' AND p.state_or_ut = 'Arunachal Pradesh')
LIMIT 1;

INSERT INTO places
    (name, city, state_or_ut, region, category_id, description, image_url, estimated_cost,
     suggested_duration, best_visiting_season, is_hidden_gem, discovery_status, things_to_do,
     travel_tips, access_notes)
SELECT 'Orchha', 'Orchha', 'Madhya Pradesh', 'Central', c.id,
       'An established heritage town on the Betwa River, known for its palace and temple complex. It is included as a quieter alternative, not an undiscovered attraction.',
       'assets/images/destinations/orchha.jpg', 5000.00, '1-2 days', 'October-March', 1, 'established',
       'Explore the heritage precincts, riverside areas and town at a relaxed pace.',
       'Respect site rules and local residents; check opening arrangements for heritage buildings before visiting.',
       'Orchha is an established heritage destination, clearly labelled as such in this collection.'
FROM categories c
WHERE c.category_name = 'Historical' AND c.category_type = 'destination'
  AND NOT EXISTS (SELECT 1 FROM places p WHERE p.name = 'Orchha' AND p.state_or_ut = 'Madhya Pradesh')
LIMIT 1;

INSERT INTO places
    (name, city, state_or_ut, region, category_id, description, image_url, estimated_cost,
     suggested_duration, best_visiting_season, is_hidden_gem, discovery_status, things_to_do,
     travel_tips, access_notes)
SELECT 'Gokarna', 'Gokarna', 'Karnataka', 'South', c.id,
       'An established coastal town with beaches and a pilgrimage centre. It is a known visitor destination, not an undiscovered secret.',
       'assets/images/destinations/gokarna.jpg', 6000.00, '2-3 days', 'October-March', 1, 'established',
       'Explore public town and shoreline areas; follow local guidance and sea-safety advisories.',
       'Check sea and weather conditions before beach walks or water activities and respect local customs.',
       'Coastal access and sea conditions vary. Do not assume swimming or boat services are available.'
FROM categories c
WHERE c.category_name = 'Beaches' AND c.category_type = 'destination'
  AND NOT EXISTS (SELECT 1 FROM places p WHERE p.name = 'Gokarna' AND p.state_or_ut = 'Karnataka')
LIMIT 1;

UPDATE places
SET is_hidden_gem = 1,
    things_to_do = 'Explore the rock-cut carvings and forested surroundings; check local visitor guidance and access before setting out.',
    travel_tips = 'Allow time for the journey to Kailashahar and wear footwear suitable for uneven outdoor paths.',
    access_notes = 'The site is in a remote part of Tripura. Confirm transport and local access arrangements before travel.'
WHERE name = 'Unakoti' AND state_or_ut = 'Tripura';

UPDATE places
SET is_hidden_gem = 1,
    things_to_do = 'Walk the heritage precincts, visit the riverside and explore the town at a relaxed pace.',
    travel_tips = 'Respect site rules and local residents; check opening arrangements for heritage buildings before visiting.',
    access_notes = 'Orchha is an established heritage destination. It is presented as a less-mainstream alternative, not an undiscovered secret.'
WHERE name = 'Orchha' AND state_or_ut = 'Madhya Pradesh';

UPDATE places
SET is_hidden_gem = 1, discovery_status = 'established',
    things_to_do = 'Explore public riverside areas and the surrounding Khasi Hills; check local visitor guidance before planning water activities.',
    travel_tips = 'Check weather and river conditions before travelling; follow local safety advice near the water.',
    access_notes = 'Dawki is an established visitor destination. River activity availability and conditions are not live-checked.'
WHERE name = 'Dawki and Umngot River' AND state_or_ut = 'Meghalaya';

INSERT INTO activities
    (place_id, category_id, title, description, duration_minutes, estimated_cost, interests, image_url, best_time)
SELECT p.id, c.id, a.title, a.description, a.duration_minutes, a.estimated_cost, a.interests, NULL, a.best_time
FROM (
    SELECT 'Jaipur' AS place_name, 'Rajasthan' AS state_or_ut, 'Photography' AS category_name,
           'Jaipur old-city photography walk' AS title,
           'A self-paced walk through public streets and market lanes; ask permission before photographing people.' AS description,
           90 AS duration_minutes, 100.00 AS estimated_cost, 'photography,walking,architecture,culture' AS interests,
           'Early morning or late afternoon; check local conditions.' AS best_time
    UNION ALL SELECT 'Hampi', 'Karnataka', 'Culture and heritage',
           'Hampi heritage landscape walk', 'Explore publicly accessible paths around the historic landscape and follow monument rules.',
           180, 300.00, 'history,walking,architecture,photography', 'October-February; avoid the hottest part of the day.'
    UNION ALL SELECT 'Dawki and Umngot River', 'Meghalaya', 'Nature and outdoors',
           'Dawki riverside nature visit', 'Spend time at publicly accessible riverfront areas. This suggestion does not include or guarantee boating.',
           60, 100.00, 'nature,walking,photography,relaxation', 'October-April; check weather and river conditions.'
    UNION ALL SELECT 'Munnar', 'Kerala', 'Relaxation',
           'Munnar tea-country viewpoint visit', 'Take a relaxed visit to an accessible public viewpoint and enjoy the surrounding highland scenery.',
           120, 200.00, 'nature,photography,relaxation,walking', 'October-March; visibility and access vary.'
    UNION ALL SELECT 'Kochi', 'Kerala', 'Food and local experiences',
           'Fort Kochi local-food exploration', 'Explore public streets and choose food vendors independently; prices and opening times vary.',
           120, 500.00, 'food,culture,walking,shopping', 'Year-round; check venue hours directly.'
    UNION ALL SELECT 'Varanasi Ghats', 'Uttar Pradesh', 'Photography',
           'Varanasi riverfront photography walk', 'Walk through public riverfront areas respectfully and follow local photography restrictions.',
           90, 100.00, 'photography,history,culture,walking', 'Early morning; respect ceremonies and privacy.'
    UNION ALL SELECT 'Mysuru Palace', 'Karnataka', 'Family-friendly',
           'Mysuru Palace family heritage visit', 'An easy-paced family visit around the palace visitor areas. Check current visitor rules, access arrangements and opening hours directly.',
           120, 500.00, 'family,history,culture,architecture', 'October-March; verify current visitor arrangements.'
    UNION ALL SELECT 'Panaji', 'Goa', 'Entertainment',
           'Panaji riverside evening outing', 'Enjoy an easy-paced evening around public waterfront areas and nearby streets. This is a general suggestion; no event or service availability is implied.',
           60, 0.00, 'relaxation,walking,culture,family', 'November-February; check local weather.'
    UNION ALL SELECT 'Calangute Beach', 'Goa', 'Relaxation',
           'Calangute 30-minute shoreline visit', 'Take a short walk along accessible public shoreline areas. Swimming and water activities are not included; follow local sea-safety advice.',
           30, 0.00, 'nature,walking,relaxation,photography', 'November-February; check local sea conditions.'
) AS a
JOIN places p ON p.name = a.place_name AND p.state_or_ut = a.state_or_ut
JOIN categories c ON c.category_name = a.category_name AND c.category_type = 'activity'
WHERE NOT EXISTS (
    SELECT 1 FROM activities existing
    WHERE existing.place_id = p.id AND existing.title = a.title
);

INSERT INTO useful_stops
    (category_id, place_id, name, address, city, state_or_ut, description, source_url, source_name)
SELECT c.id, p.id, 'Indian Oil fuel station (New Ramgarh Road)',
       'New Ramgarh Road, Deewan Colony, Jaipur, Rajasthan 302001',
       'Jaipur', 'Rajasthan',
       'A named fuel station mapped at this address. Map data may be out of date; verify current operation and services before relying on it.',
       'https://www.openstreetmap.org/node/6163167185', 'OpenStreetMap'
FROM useful_stop_categories c
JOIN places p ON p.city = 'Jaipur' AND p.state_or_ut = 'Rajasthan'
WHERE c.category_name = 'Fuel stations'
  AND NOT EXISTS (
      SELECT 1 FROM useful_stops s
      WHERE s.name = 'Indian Oil fuel station (New Ramgarh Road)'
        AND s.city = 'Jaipur' AND s.state_or_ut = 'Rajasthan'
  )
LIMIT 1;
