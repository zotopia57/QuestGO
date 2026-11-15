USE questgo;

SET NAMES utf8mb4;

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

