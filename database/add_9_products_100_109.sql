USE website_ban_mo_hinh;

INSERT INTO products (name, category, studio, description, price, old_price, stock, rating, reviews, sku, size_label, image_path, is_featured)
SELECT p.name, p.category, p.studio, p.description, p.price, p.old_price, p.stock, p.rating, p.reviews, p.sku, p.size_label, p.image_path, p.is_featured
FROM (
    SELECT 'Mewtwo Psychic Storm' AS name, 'Resin Figure' AS category, 'Origin Studio' AS studio, 'Mewtwo battle figure with purple psychic energy effect, suitable for display centerpieces.' AS description, 8900000 AS price, 9800000 AS old_price, 8 AS stock, 5 AS rating, 74 AS reviews, 'MEWTWO-009' AS sku, 'H: 36cm' AS size_label, 'assets/images/products/100.jpg' AS image_path, 1 AS is_featured
    UNION ALL SELECT 'Lucario Aura Sphere', 'Scale Figure', 'Blue Aura Studio', 'Lucario figure with Aura Sphere pose, sharp details and strong display base.', 6800000, 7600000, 9, 5, 61, 'LUCARIO-010', 'H: 34cm', 'assets/images/products/101.jpg', 1
    UNION ALL SELECT 'Snorlax Picnic Day', 'Nendoroid', 'Happy Resin', 'Cute Snorlax picnic version, bright colors, suitable for desk decoration.', 2400000, 2900000, 15, 4, 38, 'SNORLAX-011', 'H: 20cm', 'assets/images/products/102.jpg', 0
    UNION ALL SELECT 'Gengar Shadow Smile', 'Prize Figure', 'Night Studio', 'Compact Gengar figure with signature smile and shadow effect.', 1950000, 2300000, 13, 4, 45, 'GENGAR-012', 'H: 21cm', 'assets/images/products/103.jpg', 0
    UNION ALL SELECT 'Rayquaza Sky Guardian', 'Resin Figure', 'Dragon Cloud Studio', 'Large Rayquaza resin statue with cloud base for premium collectors.', 15800000, 17200000, 4, 5, 29, 'RAYQUAZA-013', 'H: 62cm', 'assets/images/products/104.jpg', 1
    UNION ALL SELECT 'Bulbasaur Garden Set', 'Mini Figure', 'Leaf House', 'Small Bulbasaur garden style set, good for combo purchase or gifts.', 950000, 1200000, 20, 4, 57, 'BULBA-014', 'H: 12cm', 'assets/images/products/105.jpg', 0
    UNION ALL SELECT 'Squirtle Water Squad', 'Mini Figure', 'Wave Studio', 'Squirtle figure with clear water effect and playful display pose.', 1100000, 1350000, 18, 4, 52, 'SQUIRTLE-015', 'H: 14cm', 'assets/images/products/106.jpg', 0
    UNION ALL SELECT 'Vaporeon Crystal Lake', 'Scale Figure', 'Dream Resin', 'Vaporeon figure on crystal lake base with soft blue tone.', 4300000, 5000000, 7, 5, 36, 'VAPOREON-016', 'H: 26cm', 'assets/images/products/107.jpg', 0
    UNION ALL SELECT 'Dragonite Mail Delivery', 'Nendoroid', 'Sky Post Studio', 'Round and cheerful Dragonite mail delivery version with orange color highlight.', 3200000, 3700000, 10, 5, 42, 'DRAGONITE-017', 'H: 24cm', 'assets/images/products/109.jpg', 0
) AS p
WHERE NOT EXISTS (
    SELECT 1 FROM products WHERE products.sku = p.sku
);
