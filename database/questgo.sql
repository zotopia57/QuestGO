CREATE DATABASE IF NOT EXISTS questgo
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE questgo;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS travel_notes;
DROP TABLE IF EXISTS expenses;
DROP TABLE IF EXISTS saved_places;
DROP TABLE IF EXISTS journey_stops;
DROP TABLE IF EXISTS activities;
DROP TABLE IF EXISTS places;
DROP TABLE IF EXISTS journeys;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(254) NOT NULL,
    `password` VARCHAR(255) NOT NULL COMMENT 'PHP password_hash() output; never store a plain-text password',
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_role_status (role, status)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    category_name VARCHAR(80) NOT NULL,
    category_type ENUM('destination', 'activity') NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categories_name_type (category_name, category_type),
    KEY idx_categories_type (category_type)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE journeys (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    start_location VARCHAR(150) NOT NULL,
    destination VARCHAR(150) NOT NULL,
    departure_date DATE NOT NULL,
    return_date DATE NOT NULL,
    travellers TINYINT UNSIGNED NOT NULL DEFAULT 1,
    estimated_budget DECIMAL(12,2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT 'Estimated amount in INR',
    status ENUM('draft', 'planning', 'completed', 'cancelled') NOT NULL DEFAULT 'draft',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_journeys_id_user (id, user_id),
    KEY idx_journeys_user_departure (user_id, departure_date),
    KEY idx_journeys_status (status),
    CONSTRAINT fk_journeys_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_journeys_travellers CHECK (travellers >= 1),
    CONSTRAINT chk_journeys_dates CHECK (return_date >= departure_date)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE places (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    city VARCHAR(120) NOT NULL,
    state_or_ut VARCHAR(120) NOT NULL,
    region ENUM('North', 'South', 'East', 'West', 'Central', 'Northeast') NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    description TEXT NOT NULL,
    image_url VARCHAR(2048) DEFAULT NULL,
    estimated_cost DECIMAL(12,2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT 'Typical estimated expenses in INR',
    suggested_duration VARCHAR(80) NOT NULL,
    distance DECIMAL(8,2) UNSIGNED DEFAULT NULL COMMENT 'Optional distance in kilometres; NULL when not verified',
    best_visiting_season VARCHAR(120) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_places_name (name),
    KEY idx_places_state (state_or_ut),
    KEY idx_places_state_city (state_or_ut, city),
    KEY idx_places_region (region),
    KEY idx_places_category (category_id),
    CONSTRAINT fk_places_category
        FOREIGN KEY (category_id) REFERENCES categories (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activities (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    place_id INT UNSIGNED NOT NULL,
    category_id INT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    duration_minutes SMALLINT UNSIGNED NOT NULL,
    estimated_cost DECIMAL(12,2) UNSIGNED NOT NULL DEFAULT 0.00 COMMENT 'Estimated activity cost in INR',
    interests VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_activities_place (place_id),
    KEY idx_activities_category (category_id),
    KEY idx_activities_duration_cost (duration_minutes, estimated_cost),
    CONSTRAINT fk_activities_place
        FOREIGN KEY (place_id) REFERENCES places (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_activities_category
        FOREIGN KEY (category_id) REFERENCES categories (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_activities_duration CHECK (duration_minutes > 0)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE journey_stops (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    journey_id INT UNSIGNED NOT NULL,
    place_id INT UNSIGNED NOT NULL,
    visit_date DATE DEFAULT NULL,
    stop_order SMALLINT UNSIGNED NOT NULL,
    notes TEXT DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_journey_stops_order (journey_id, stop_order),
    UNIQUE KEY uq_journey_stops_place (journey_id, place_id),
    KEY idx_journey_stops_place (place_id),
    CONSTRAINT fk_journey_stops_journey
        FOREIGN KEY (journey_id) REFERENCES journeys (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_journey_stops_place
        FOREIGN KEY (place_id) REFERENCES places (id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE saved_places (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    place_id INT UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_saved_places_user_place (user_id, place_id),
    KEY idx_saved_places_place (place_id),
    CONSTRAINT fk_saved_places_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_saved_places_place
        FOREIGN KEY (place_id) REFERENCES places (id)
        ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE expenses (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    journey_id INT UNSIGNED DEFAULT NULL,
    title VARCHAR(150) NOT NULL,
    category VARCHAR(80) NOT NULL,
    amount DECIMAL(12,2) UNSIGNED NOT NULL COMMENT 'Expense amount in INR',
    expense_date DATE NOT NULL,
    notes TEXT DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_expenses_user_date (user_id, expense_date),
    KEY idx_expenses_journey_user (journey_id, user_id),
    CONSTRAINT fk_expenses_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_expenses_journey_owner
        FOREIGN KEY (journey_id, user_id) REFERENCES journeys (id, user_id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT chk_expenses_amount CHECK (amount >= 0.00)
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE travel_notes (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED NOT NULL,
    journey_id INT UNSIGNED DEFAULT NULL,
    title VARCHAR(150) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_travel_notes_user_updated (user_id, updated_at),
    KEY idx_travel_notes_journey_user (journey_id, user_id),
    CONSTRAINT fk_travel_notes_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_travel_notes_journey_owner
        FOREIGN KEY (journey_id, user_id) REFERENCES journeys (id, user_id)
        ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARACTER SET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categories (id, category_name, category_type) VALUES
(1, 'Historical', 'destination'),
(2, 'Religious', 'destination'),
(3, 'Nature', 'destination'),
(4, 'Adventure', 'destination'),
(5, 'Beaches', 'destination'),
(6, 'Hill Stations', 'destination'),
(7, 'Wildlife', 'destination'),
(8, 'Culture', 'destination'),
(9, 'Food', 'activity'),
(10, 'Shopping', 'activity'),
(11, 'Culture', 'activity'),
(12, 'Nature', 'activity'),
(13, 'History', 'activity'),
(14, 'Adventure', 'activity'),
(15, 'Entertainment', 'activity');

INSERT INTO places
(id, name, city, state_or_ut, region, category_id, description, image_url, estimated_cost, suggested_duration, distance, best_visiting_season)
VALUES
(1, 'Tirupati', 'Tirupati', 'Andhra Pradesh', 'South', 2, 'A major pilgrimage destination in the Tirumala hills, known for the Sri Venkateswara Temple.', NULL, 4500.00, '1-2 days', NULL, 'September-March'),
(2, 'Tawang Monastery', 'Tawang', 'Arunachal Pradesh', 'Northeast', 2, 'A historic Buddhist monastery set among the high Himalayan landscapes of Arunachal Pradesh.', NULL, 6500.00, '2-3 days', NULL, 'April-October'),
(3, 'Kaziranga National Park', 'Kohora', 'Assam', 'Northeast', 7, 'A renowned grassland and wetland landscape known for its wildlife and conservation areas.', NULL, 5500.00, '2-3 days', NULL, 'November-April'),
(4, 'Mahabodhi Temple', 'Bodh Gaya', 'Bihar', 'East', 2, 'A UNESCO-listed Buddhist pilgrimage site associated with the life of Gautama Buddha.', NULL, 2500.00, '1-2 days', NULL, 'October-March'),
(5, 'Chitrakote Falls', 'Jagdalpur', 'Chhattisgarh', 'Central', 3, 'A broad horseshoe-shaped waterfall on the Indravati River, especially dramatic in the monsoon.', NULL, 3000.00, '1-2 days', NULL, 'July-February'),
(6, 'Old Goa Churches', 'Old Goa', 'Goa', 'West', 1, 'A collection of historic churches and convents that reflect Goa’s Portuguese-era architecture.', NULL, 3500.00, '1 day', NULL, 'November-February'),
(7, 'Rann of Kutch', 'Dhordo', 'Gujarat', 'West', 3, 'A vast seasonal salt marsh with distinctive landscapes and local craft traditions.', NULL, 7000.00, '2-3 days', NULL, 'November-February'),
(8, 'Kurukshetra', 'Kurukshetra', 'Haryana', 'North', 2, 'An important heritage and pilgrimage area with sacred sites, museums and water tanks.', NULL, 3000.00, '1-2 days', NULL, 'October-March'),
(9, 'Shimla', 'Shimla', 'Himachal Pradesh', 'North', 6, 'A historic Himalayan hill station with mountain views, forest walks and colonial-era streets.', NULL, 6500.00, '3-4 days', NULL, 'March-June'),
(10, 'Deoghar', 'Deoghar', 'Jharkhand', 'East', 2, 'A prominent pilgrimage town and the location of the Baidyanath Jyotirlinga temple complex.', NULL, 3000.00, '1-2 days', NULL, 'October-March'),
(11, 'Mysuru Palace', 'Mysuru', 'Karnataka', 'South', 1, 'A richly decorated royal residence and a defining landmark of Mysuru’s cultural heritage.', NULL, 4000.00, '1-2 days', NULL, 'October-March'),
(12, 'Munnar', 'Munnar', 'Kerala', 'South', 6, 'A highland retreat among tea gardens, forested hills and winding mountain roads.', NULL, 7000.00, '2-4 days', NULL, 'September-March'),
(13, 'Khajuraho Group of Monuments', 'Khajuraho', 'Madhya Pradesh', 'Central', 1, 'A group of historic temples known for their detailed stone sculpture and architecture.', NULL, 4500.00, '2 days', NULL, 'October-March'),
(14, 'Ajanta Caves', 'Aurangabad', 'Maharashtra', 'West', 1, 'Rock-cut Buddhist cave monuments featuring historic murals, sculpture and architecture.', NULL, 5000.00, '1-2 days', NULL, 'November-March'),
(15, 'Loktak Lake', 'Moirang', 'Manipur', 'Northeast', 3, 'A distinctive freshwater lake known for its floating phumdis and surrounding wetlands.', NULL, 4000.00, '1-2 days', NULL, 'October-March'),
(16, 'Living Root Bridges', 'Cherrapunji', 'Meghalaya', 'Northeast', 4, 'Living bridges formed from trained tree roots, reached by scenic trails in the Khasi Hills.', NULL, 5500.00, '2-3 days', NULL, 'October-April'),
(17, 'Aizawl', 'Aizawl', 'Mizoram', 'Northeast', 8, 'A hilltop city offering Mizo culture, local markets and views across the surrounding hills.', NULL, 4500.00, '2-3 days', NULL, 'October-March'),
(18, 'Kohima War Cemetery', 'Kohima', 'Nagaland', 'Northeast', 1, 'A hillside memorial and heritage site connected with the Battle of Kohima during the Second World War.', NULL, 5000.00, '2 days', NULL, 'October-May'),
(19, 'Konark Sun Temple', 'Konark', 'Odisha', 'East', 1, 'A historic temple complex designed as a monumental stone chariot dedicated to the Sun God.', NULL, 4000.00, '1-2 days', NULL, 'October-February'),
(20, 'Golden Temple', 'Amritsar', 'Punjab', 'North', 2, 'A celebrated Sikh place of worship known for its sacred pool and welcoming community kitchen.', NULL, 3500.00, '1-2 days', NULL, 'October-March'),
(21, 'Jaipur', 'Jaipur', 'Rajasthan', 'West', 1, 'A heritage city of forts, historic palaces, lively bazaars and distinctive pink-hued streets.', NULL, 6500.00, '2-3 days', NULL, 'October-March'),
(22, 'Gangtok', 'Gangtok', 'Sikkim', 'Northeast', 6, 'A Himalayan capital with monastery visits, mountain viewpoints and local food culture.', NULL, 7000.00, '3-4 days', NULL, 'March-June'),
(23, 'Madurai Meenakshi Temple', 'Madurai', 'Tamil Nadu', 'South', 2, 'A major historic temple complex known for its colourful gopurams and active place in local life.', NULL, 4000.00, '1-2 days', NULL, 'October-March'),
(24, 'Charminar', 'Hyderabad', 'Telangana', 'South', 1, 'A landmark monument surrounded by old-city lanes, traditional markets and food streets.', NULL, 4500.00, '1-2 days', NULL, 'October-February'),
(25, 'Unakoti', 'Kailashahar', 'Tripura', 'Northeast', 1, 'An archaeological and pilgrimage site with large rock-cut figures set in a forested landscape.', NULL, 4000.00, '1-2 days', NULL, 'October-March'),
(26, 'Taj Mahal', 'Agra', 'Uttar Pradesh', 'North', 1, 'A celebrated marble mausoleum and UNESCO World Heritage Site on the banks of the Yamuna.', NULL, 5000.00, '1-2 days', NULL, 'October-March'),
(27, 'Nainital', 'Nainital', 'Uttarakhand', 'North', 6, 'A Himalayan lake town surrounded by forested hills and walking routes.', NULL, 6000.00, '2-3 days', NULL, 'March-June'),
(28, 'Darjeeling', 'Darjeeling', 'West Bengal', 'East', 6, 'A hill town known for tea gardens, mountain views, heritage rail and local culture.', NULL, 6500.00, '3-4 days', NULL, 'March-May; October-November'),
(29, 'Cellular Jail', 'Sri Vijaya Puram', 'Andaman and Nicobar Islands', 'South', 1, 'A national memorial that shares the history of the islands and India’s freedom movement.', NULL, 5500.00, '2-3 days', NULL, 'October-May'),
(30, 'Rock Garden', 'Chandigarh', 'Chandigarh', 'North', 8, 'An extensive sculpture garden created from reused materials and arranged among pathways and courtyards.', NULL, 3000.00, '1 day', NULL, 'October-March'),
(31, 'Diu Fort', 'Diu', 'Dadra and Nagar Haveli and Daman and Diu', 'West', 1, 'A coastal fort with historic ramparts and views across the Arabian Sea.', NULL, 3500.00, '1-2 days', NULL, 'November-February'),
(32, 'Humayun’s Tomb', 'New Delhi', 'Delhi', 'North', 1, 'A garden tomb and architectural landmark that forms part of Delhi’s historic landscape.', NULL, 4000.00, '1 day', NULL, 'October-March'),
(33, 'Srinagar Dal Lake', 'Srinagar', 'Jammu and Kashmir', 'North', 3, 'A Himalayan lake destination known for houseboats, gardens and mountain views.', NULL, 7500.00, '3-4 days', NULL, 'April-October'),
(34, 'Pangong Tso', 'Leh', 'Ladakh', 'North', 3, 'A high-altitude lake in a stark mountain landscape; visitors should plan for altitude and seasonal road conditions.', NULL, 9000.00, '2-3 days', NULL, 'May-September'),
(35, 'Agatti Island', 'Agatti', 'Lakshadweep', 'South', 5, 'A coral island with clear lagoons and a quiet coastal setting; travel permissions and transport require advance checking.', NULL, 12000.00, '3-4 days', NULL, 'October-May'),
(36, 'Promenade Beach', 'Puducherry', 'Puducherry', 'South', 5, 'A seafront promenade close to Puducherry’s historic French Quarter and local cafés.', NULL, 4000.00, '2-3 days', NULL, 'October-March');

INSERT INTO activities
(place_id, category_id, title, description, duration_minutes, estimated_cost, interests)
VALUES
(1, 11, 'Temple visit and hill-town walk', 'Explore the pilgrimage area and its surrounding public viewpoints; confirm current visitor guidance before travelling.', 150, 0.00, 'culture,religion,walking'),
(3, 12, 'Wetland and wildlife interpretation', 'Spend time learning about the park landscape and conservation through authorised visitor facilities.', 180, 1200.00, 'wildlife,nature,photography'),
(6, 13, 'Old Goa heritage walk', 'Walk among historic churches and public heritage spaces at a relaxed pace.', 120, 100.00, 'history,architecture,culture'),
(7, 11, 'Craft and culture visit in Kutch', 'Browse local craft traditions and learn about the region’s communities and seasonal landscape.', 150, 500.00, 'culture,crafts,shopping'),
(9, 12, 'Ridge and forest walk', 'Take a leisurely walk along public paths and viewpoints in the Shimla hills.', 90, 0.00, 'nature,walking,photography'),
(11, 13, 'Mysuru Palace visit', 'Explore the public visitor areas of the palace and learn about the region’s royal history.', 120, 200.00, 'history,architecture,culture'),
(12, 12, 'Tea garden viewpoint visit', 'Enjoy highland scenery and learn about tea cultivation at a visitor-accessible garden.', 120, 300.00, 'nature,tea,photography'),
(13, 13, 'Khajuraho temple architecture walk', 'Study the temple carvings and architecture on a self-paced heritage visit.', 150, 250.00, 'history,architecture,art'),
(16, 14, 'Living root bridge trail', 'Walk a locally known trail to see living root bridges; check trail access and conditions locally.', 240, 300.00, 'nature,walking,adventure'),
(20, 11, 'Heritage and community kitchen visit', 'Visit the temple complex respectfully and learn about the community kitchen tradition.', 120, 0.00, 'culture,food,history'),
(21, 13, 'Jaipur old-city heritage walk', 'Discover historic lanes, architecture and public spaces around the walled city.', 120, 250.00, 'history,walking,architecture'),
(21, 9, 'Taste a Rajasthani thali', 'Try a regional meal at a local restaurant and explore traditional flavours.', 75, 450.00, 'food,culture'),
(21, 10, 'Browse Jaipur bazaars', 'Explore traditional market lanes and local crafts; prices vary by shop and item.', 90, 0.00, 'shopping,crafts,culture'),
(23, 13, 'Meenakshi Temple architecture visit', 'View the temple’s public areas and distinctive towers while respecting local customs.', 120, 100.00, 'history,architecture,religion'),
(24, 9, 'Old Hyderabad food walk', 'Sample regional snacks and meals around the historic city; confirm venue hours locally.', 120, 600.00, 'food,culture,walking'),
(26, 13, 'Taj Mahal grounds visit', 'Visit the public monument complex and take time to appreciate its architecture and gardens.', 180, 550.00, 'history,architecture,photography'),
(27, 12, 'Lakeside walk in Nainital', 'Take an easy walk beside the lake and explore nearby public viewpoints.', 75, 0.00, 'nature,walking,relaxation'),
(28, 12, 'Tea garden visit in Darjeeling', 'Learn about tea growing and enjoy mountain scenery at a visitor-accessible estate.', 120, 300.00, 'nature,tea,photography'),
(32, 13, 'Humayun’s Tomb garden walk', 'Explore the garden complex and its Mughal-era architecture at an unhurried pace.', 120, 350.00, 'history,architecture,walking'),
(36, 11, 'Puducherry heritage quarter walk', 'Walk through the historic quarter and along the seafront, taking breaks at local cafés.', 120, 250.00, 'culture,architecture,walking');

INSERT INTO categories (category_name, category_type)
SELECT 'Shopping', 'destination'
WHERE NOT EXISTS (
    SELECT 1 FROM categories WHERE category_name = 'Shopping' AND category_type = 'destination'
);

INSERT INTO categories (category_name, category_type)
SELECT 'Entertainment', 'destination'
WHERE NOT EXISTS (
    SELECT 1 FROM categories WHERE category_name = 'Entertainment' AND category_type = 'destination'
);

INSERT INTO categories (category_name, category_type)
SELECT 'Food & Culture', 'destination'
WHERE NOT EXISTS (
    SELECT 1 FROM categories WHERE category_name = 'Food & Culture' AND category_type = 'destination'
);

INSERT INTO places
(name, city, state_or_ut, region, category_id, description, image_url, estimated_cost, suggested_duration, distance, best_visiting_season)
SELECT d.name, d.city, d.state_or_ut, d.region, c.id, d.description, NULL, d.estimated_cost, d.suggested_duration, NULL, d.best_visiting_season
FROM (
    SELECT 'Ahmedabad' AS name, 'Ahmedabad' AS city, 'Gujarat' AS state_or_ut, 'West' AS region, 'Historical' AS category_name,
        'A major city with a historic walled centre, stepwells, museums and a lively food culture.' AS description, 4500.00 AS estimated_cost, '1-2 days' AS suggested_duration, 'October-March' AS best_visiting_season
    UNION ALL SELECT 'Statue of Unity', 'Kevadia', 'Gujarat', 'West', 'Historical',
        'A large national monument and visitor area near the Narmada River; check local visitor guidance before travelling.', 5000.00, '1 day', 'October-February'
    UNION ALL SELECT 'Dwarka', 'Dwarka', 'Gujarat', 'West', 'Religious',
        'A coastal pilgrimage town associated with the Dwarkadhish Temple and the traditions of Krishna.', 4000.00, '1-2 days', 'October-March'
    UNION ALL SELECT 'Somnath', 'Prabhas Patan', 'Gujarat', 'West', 'Religious',
        'A coastal pilgrimage destination centred on the Somnath temple complex and its historic setting.', 3500.00, '1 day', 'October-March'
    UNION ALL SELECT 'Udaipur', 'Udaipur', 'Rajasthan', 'West', 'Historical',
        'A lakeside heritage city known for historic palaces, old neighbourhoods and views of the Aravalli hills.', 7000.00, '2-3 days', 'October-March'
    UNION ALL SELECT 'Jaisalmer', 'Jaisalmer', 'Rajasthan', 'West', 'Historical',
        'A desert heritage city with a living fort, sandstone havelis and historic lanes.', 7500.00, '2-3 days', 'October-March'
    UNION ALL SELECT 'Jodhpur', 'Jodhpur', 'Rajasthan', 'West', 'Historical',
        'A historic city of blue-painted old-town lanes, monumental architecture and traditional markets.', 6000.00, '2-3 days', 'October-March'
    UNION ALL SELECT 'Alleppey Backwaters', 'Alappuzha', 'Kerala', 'South', 'Nature',
        'A network of backwaters, canals and settlements; transport and visitor arrangements should be checked locally.', 6500.00, '1-2 days', 'October-March'
    UNION ALL SELECT 'Kochi', 'Kochi', 'Kerala', 'South', 'Culture',
        'A port city with historic neighbourhoods, waterfront streets, museums and a blend of regional influences.', 5500.00, '2 days', 'October-March'
    UNION ALL SELECT 'Wayanad', 'Kalpetta', 'Kerala', 'South', 'Nature',
        'A hill district with forest landscapes, waterfalls and rural communities; access to natural sites varies by season.', 7000.00, '2-3 days', 'October-May'
    UNION ALL SELECT 'Panaji', 'Panaji', 'Goa', 'West', 'Culture',
        'Goa’s riverside capital, with heritage streets, museums, local markets and nearby waterfront areas.', 4500.00, '1-2 days', 'November-February'
    UNION ALL SELECT 'Calangute Beach', 'Calangute', 'Goa', 'West', 'Beaches',
        'A well-known North Goa beach and coastal neighbourhood; beach conditions and services vary locally.', 5500.00, '1-2 days', 'November-February'
    UNION ALL SELECT 'Palolem Beach', 'Canacona', 'Goa', 'West', 'Beaches',
        'A crescent-shaped South Goa beach with a coastal village setting; check seasonal sea conditions before swimming.', 6000.00, '1-2 days', 'November-February'
    UNION ALL SELECT 'Manali', 'Manali', 'Himachal Pradesh', 'North', 'Hill Stations',
        'A Himalayan town with mountain scenery, riverside walks and access to nearby valleys; road and weather conditions change seasonally.', 8000.00, '3-4 days', 'March-June; October-February'
    UNION ALL SELECT 'Dharamshala and McLeod Ganj', 'Dharamshala', 'Himachal Pradesh', 'North', 'Culture',
        'A hillside destination known for Tibetan cultural institutions, monasteries and views of the Dhauladhar range.', 7000.00, '2-3 days', 'March-June; September-November'
    UNION ALL SELECT 'Spiti Valley', 'Kaza', 'Himachal Pradesh', 'North', 'Adventure',
        'A high-altitude cold-desert valley with remote settlements and mountain landscapes; acclimatisation and seasonal road checks are essential.', 12000.00, '5-7 days', 'June-September'
    UNION ALL SELECT 'Shillong', 'Shillong', 'Meghalaya', 'Northeast', 'Culture',
        'A hill city with Khasi cultural influences, music, markets and viewpoints across the surrounding plateau.', 6000.00, '2-3 days', 'October-April'
    UNION ALL SELECT 'Cherrapunji (Sohra)', 'Sohra', 'Meghalaya', 'Northeast', 'Nature',
        'A plateau destination known for waterfalls, caves and living root bridges; trail access and weather vary through the year.', 6500.00, '2-3 days', 'October-April'
    UNION ALL SELECT 'Dawki and Umngot River', 'Dawki', 'Meghalaya', 'Northeast', 'Nature',
        'A border-area river and village landscape in the Khasi Hills; river conditions and permitted activities must be checked locally.', 5000.00, '1 day', 'October-April'
    UNION ALL SELECT 'Varanasi Ghats', 'Varanasi', 'Uttar Pradesh', 'North', 'Religious',
        'A historic city on the Ganga with riverfront ghats, temples and long-standing cultural traditions.', 4500.00, '2-3 days', 'October-March'
    UNION ALL SELECT 'Hampi', 'Hosapete', 'Karnataka', 'South', 'Historical',
        'A UNESCO-listed archaeological landscape of temple complexes, historic ruins and distinctive boulder-strewn terrain.', 5000.00, '2-3 days', 'October-February'
) AS d
JOIN categories c ON c.category_name = d.category_name AND c.category_type = 'destination'
WHERE NOT EXISTS (
    SELECT 1 FROM places p WHERE p.name = d.name AND p.state_or_ut = d.state_or_ut
);

INSERT INTO activities (place_id, category_id, title, description, duration_minutes, estimated_cost, interests)
SELECT p.id, c.id, a.title, a.description, a.duration_minutes, a.estimated_cost, a.interests
FROM (
    SELECT 'Ahmedabad' AS place_name, 'Gujarat' AS state_or_ut, 'Culture' AS category_name,
        'Old Ahmedabad heritage walk' AS title, 'Explore public heritage streets and architecture at a relaxed pace; local access and timings should be checked.' AS description, 120 AS duration_minutes, 200.00 AS estimated_cost, 'history,architecture,walking,culture' AS interests
    UNION ALL SELECT 'Statue of Unity', 'Gujarat', 'Culture', 'Kevadia monument and visitor-area visit', 'Explore the monument visitor area and surrounding public spaces; entry arrangements and fees are estimates and should be checked before travel.', 180, 600.00, 'history,architecture,culture,walking'
    UNION ALL SELECT 'Dwarka', 'Gujarat', 'Culture', 'Dwarka temple-town heritage visit', 'Visit public temple-town areas respectfully and learn about local traditions; follow current visitor guidance.', 120, 100.00, 'religion,history,culture,walking'
    UNION ALL SELECT 'Somnath', 'Gujarat', 'Culture', 'Somnath coastal heritage walk', 'Explore public areas around the historic coastal temple precinct and nearby seafront.', 90, 100.00, 'religion,history,culture,walking'
    UNION ALL SELECT 'Udaipur', 'Rajasthan', 'Culture', 'Udaipur old-city and lakefront walk', 'Take a self-paced walk through public old-city lanes and lakefront viewpoints.', 120, 250.00, 'history,architecture,walking,photography'
    UNION ALL SELECT 'Jaisalmer', 'Rajasthan', 'History', 'Jaisalmer fort and heritage-lane walk', 'Explore public lanes in the living fort and nearby sandstone heritage streets.', 150, 200.00, 'history,architecture,walking,photography'
    UNION ALL SELECT 'Jodhpur', 'Rajasthan', 'Food', 'Jodhpur market and local-food walk', 'Browse public market lanes and sample regional food; prices and venue hours vary.', 120, 500.00, 'food,culture,shopping,walking'
    UNION ALL SELECT 'Alleppey Backwaters', 'Kerala', 'Nature', 'Alappuzha canal-side walk', 'Walk along accessible public waterfront areas and observe backwater life; do not assume boat availability.', 90, 100.00, 'nature,walking,photography,relaxation'
    UNION ALL SELECT 'Kochi', 'Kerala', 'Culture', 'Fort Kochi heritage-street walk', 'Explore public heritage streets and waterfront areas at a relaxed pace.', 120, 200.00, 'history,architecture,culture,walking'
    UNION ALL SELECT 'Wayanad', 'Kerala', 'Nature', 'Wayanad landscape and nature visit', 'Spend time at a locally accessible nature viewpoint; site access and conditions should be verified.', 150, 300.00, 'nature,photography,walking,relaxation'
    UNION ALL SELECT 'Panaji', 'Goa', 'Culture', 'Panaji heritage-quarter walk', 'Explore public streets and heritage architecture in central Panaji.', 90, 100.00, 'culture,architecture,walking,photography'
    UNION ALL SELECT 'Calangute Beach', 'Goa', 'Nature', 'Calangute shoreline stroll', 'Take a relaxed walk along public shoreline areas; check local sea conditions and advisories.', 60, 0.00, 'nature,walking,relaxation,photography'
    UNION ALL SELECT 'Palolem Beach', 'Goa', 'Nature', 'Palolem coastal walk', 'Walk along public coastal areas and observe the shoreline; swimming and water activities are not included.', 60, 0.00, 'nature,walking,relaxation,photography'
    UNION ALL SELECT 'Manali', 'Himachal Pradesh', 'Nature', 'Manali riverside and town walk', 'Take a short walk through publicly accessible town and riverside areas; check local weather and path conditions.', 90, 100.00, 'nature,walking,photography'
    UNION ALL SELECT 'Dharamshala and McLeod Ganj', 'Himachal Pradesh', 'Culture', 'McLeod Ganj cultural-centre visit', 'Explore public cultural and heritage areas respectfully; follow visitor guidance at religious sites.', 120, 200.00, 'culture,history,religion,walking'
    UNION ALL SELECT 'Spiti Valley', 'Himachal Pradesh', 'Adventure', 'Kaza village and viewpoint walk', 'Take a short acclimatisation-friendly walk near Kaza; altitude, weather and access need local verification.', 90, 100.00, 'adventure,nature,walking,photography'
    UNION ALL SELECT 'Shillong', 'Meghalaya', 'Food', 'Shillong market and local-food visit', 'Browse public market areas and sample local dishes; prices and opening times vary.', 90, 400.00, 'food,culture,shopping,walking'
    UNION ALL SELECT 'Cherrapunji (Sohra)', 'Meghalaya', 'Nature', 'Sohra waterfall viewpoint visit', 'Visit a locally accessible public viewpoint; visibility, weather and access conditions vary.', 90, 100.00, 'nature,photography,walking'
    UNION ALL SELECT 'Dawki and Umngot River', 'Meghalaya', 'Nature', 'Dawki riverside visit', 'Spend time at public riverfront areas; water conditions and activities must be checked locally.', 75, 100.00, 'nature,photography,relaxation'
    UNION ALL SELECT 'Varanasi Ghats', 'Uttar Pradesh', 'History', 'Varanasi riverfront heritage walk', 'Walk along public ghats and historic lanes respectfully; ceremonies and local access are not guaranteed.', 120, 150.00, 'history,religion,culture,walking,photography'
    UNION ALL SELECT 'Hampi', 'Karnataka', 'History', 'Hampi monument landscape walk', 'Explore a self-paced route through publicly accessible archaeological areas; site rules and access may vary.', 180, 300.00, 'history,architecture,walking,photography'
) AS a
JOIN places p ON p.name = a.place_name AND p.state_or_ut = a.state_or_ut
JOIN categories c ON c.category_name = a.category_name AND c.category_type = 'activity'
WHERE NOT EXISTS (
    SELECT 1 FROM activities existing WHERE existing.place_id = p.id AND existing.title = a.title
);


-- Assign unique, locally served Wikimedia Commons photographs to seeded destinations.
USE questgo;
SET NAMES utf8mb4;

UPDATE places
SET image_url = CASE id
    WHEN 1 THEN 'assets/images/destinations/tirupati.jpg'
    WHEN 2 THEN 'assets/images/destinations/tawang-monastery.jpg'
    WHEN 3 THEN 'assets/images/destinations/kaziranga-national-park.jpg'
    WHEN 4 THEN 'assets/images/destinations/mahabodhi-temple.jpg'
    WHEN 5 THEN 'assets/images/destinations/chitrakote-falls.jpg'
    WHEN 6 THEN 'assets/images/destinations/old-goa-churches.jpg'
    WHEN 7 THEN 'assets/images/destinations/rann-of-kutch.jpg'
    WHEN 8 THEN 'assets/images/destinations/kurukshetra.jpg'
    WHEN 9 THEN 'assets/images/destinations/shimla.jpg'
    WHEN 10 THEN 'assets/images/destinations/deoghar.jpg'
    WHEN 11 THEN 'assets/images/destinations/mysuru-palace.jpg'
    WHEN 12 THEN 'assets/images/destinations/munnar.jpg'
    WHEN 13 THEN 'assets/images/destinations/khajuraho-group-of-monuments.jpg'
    WHEN 14 THEN 'assets/images/destinations/ajanta-caves.jpg'
    WHEN 15 THEN 'assets/images/destinations/loktak-lake.jpg'
    WHEN 16 THEN 'assets/images/destinations/living-root-bridges.jpg'
    WHEN 17 THEN 'assets/images/destinations/aizawl.jpg'
    WHEN 18 THEN 'assets/images/destinations/kohima-war-cemetery.jpg'
    WHEN 19 THEN 'assets/images/destinations/konark-sun-temple.jpg'
    WHEN 20 THEN 'assets/images/destinations/golden-temple.jpg'
    WHEN 21 THEN 'assets/images/destinations/jaipur.jpg'
    WHEN 22 THEN 'assets/images/destinations/gangtok.jpg'
    WHEN 23 THEN 'assets/images/destinations/madurai-meenakshi-temple.jpg'
    WHEN 24 THEN 'assets/images/destinations/charminar.jpg'
    WHEN 25 THEN 'assets/images/destinations/unakoti.jpg'
    WHEN 26 THEN 'assets/images/destinations/taj-mahal.jpg'
    WHEN 27 THEN 'assets/images/destinations/nainital.jpg'
    WHEN 28 THEN 'assets/images/destinations/darjeeling.jpg'
    WHEN 29 THEN 'assets/images/destinations/cellular-jail.jpg'
    WHEN 30 THEN 'assets/images/destinations/rock-garden.jpg'
    WHEN 31 THEN 'assets/images/destinations/diu-fort.jpg'
    WHEN 32 THEN 'assets/images/destinations/humayun-s-tomb.jpg'
    WHEN 33 THEN 'assets/images/destinations/srinagar-dal-lake.jpg'
    WHEN 34 THEN 'assets/images/destinations/pangong-tso.jpg'
    WHEN 35 THEN 'assets/images/destinations/agatti-island.jpg'
    WHEN 36 THEN 'assets/images/destinations/promenade-beach.jpg'
    WHEN 38 THEN 'assets/images/destinations/ahmedabad.jpg'
    WHEN 39 THEN 'assets/images/destinations/statue-of-unity.jpg'
    WHEN 40 THEN 'assets/images/destinations/dwarka.jpg'
    WHEN 41 THEN 'assets/images/destinations/somnath.jpg'
    WHEN 42 THEN 'assets/images/destinations/udaipur.jpg'
    WHEN 43 THEN 'assets/images/destinations/jaisalmer.jpg'
    WHEN 44 THEN 'assets/images/destinations/jodhpur.jpg'
    WHEN 45 THEN 'assets/images/destinations/alleppey-backwaters.jpg'
    WHEN 46 THEN 'assets/images/destinations/kochi.jpg'
    WHEN 47 THEN 'assets/images/destinations/wayanad.jpg'
    WHEN 48 THEN 'assets/images/destinations/panaji.jpg'
    WHEN 49 THEN 'assets/images/destinations/calangute-beach.jpg'
    WHEN 50 THEN 'assets/images/destinations/palolem-beach.jpg'
    WHEN 51 THEN 'assets/images/destinations/manali.jpg'
    WHEN 52 THEN 'assets/images/destinations/dharamshala-and-mcleod-ganj.jpg'
    WHEN 53 THEN 'assets/images/destinations/spiti-valley.jpg'
    WHEN 54 THEN 'assets/images/destinations/shillong.jpg'
    WHEN 55 THEN 'assets/images/destinations/cherrapunji-sohra.jpg'
    WHEN 56 THEN 'assets/images/destinations/dawki-and-umngot-river.jpg'
    WHEN 57 THEN 'assets/images/destinations/varanasi-ghats.jpg'
    WHEN 58 THEN 'assets/images/destinations/hampi.jpg'
    ELSE image_url
END
WHERE id IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 38, 39, 40, 41, 42, 43, 44, 45, 46, 47, 48, 49, 50, 51, 52, 53, 54, 55, 56, 57, 58);

