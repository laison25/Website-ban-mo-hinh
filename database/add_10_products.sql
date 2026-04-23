USE website_ban_mo_hinh;

UPDATE products
SET image_path = 'assets/images/products/product-2.jpg'
WHERE sku = 'EEVEE-001'
  AND image_path = 'assets/images/products/product-2.svg';

INSERT INTO products (name, category, studio, description, price, old_price, stock, rating, reviews, sku, size_label, image_path, is_featured)
SELECT new_products.name,
       new_products.category,
       new_products.studio,
       new_products.description,
       new_products.price,
       new_products.old_price,
       new_products.stock,
       new_products.rating,
       new_products.reviews,
       new_products.sku,
       new_products.size_label,
       new_products.image_path,
       new_products.is_featured
FROM (
    SELECT 'Mewtwo Psychic Storm' AS name, 'Resin Figure' AS category, 'Origin Studio' AS studio, 'Mewtwo tạo dáng chiến đấu với hiệu ứng năng lượng tím, hợp trưng bày trung tâm bộ sưu tập.' AS description, 8900000 AS price, 9800000 AS old_price, 8 AS stock, 5 AS rating, 74 AS reviews, 'MEWTWO-009' AS sku, 'H: 36cm' AS size_label, 'assets/images/products/product-1.jpg' AS image_path, 1 AS is_featured
    UNION ALL SELECT 'Lucario Aura Sphere', 'Scale Figure', 'Blue Aura Studio', 'Lucario tung Aura Sphere với base ánh sáng xanh, chi tiết sắc nét và dáng đứng mạnh mẽ.', 6800000, 7600000, 9, 5, 61, 'LUCARIO-010', 'H: 34cm', 'assets/images/products/product-2.jpg', 1
    UNION ALL SELECT 'Snorlax Picnic Day', 'Nendoroid', 'Happy Resin', 'Snorlax phiên bản picnic dễ thương, màu sắc tươi sáng, phù hợp góc decor bàn làm việc.', 2400000, 2900000, 15, 4, 38, 'SNORLAX-011', 'H: 20cm', 'assets/images/products/product-3.jpg', 0
    UNION ALL SELECT 'Gengar Shadow Smile', 'Prize Figure', 'Night Studio', 'Gengar tạo hình nụ cười đặc trưng cùng hiệu ứng bóng tối, kích thước gọn dễ sưu tầm.', 1950000, 2300000, 13, 4, 45, 'GENGAR-012', 'H: 21cm', 'assets/images/products/product-4.jpg', 0
    UNION ALL SELECT 'Rayquaza Sky Guardian', 'Resin Figure', 'Dragon Cloud Studio', 'Rayquaza uốn lượn trên nền mây, bản resin lớn dành cho người thích mô hình hoành tráng.', 15800000, 17200000, 4, 5, 29, 'RAYQUAZA-013', 'H: 62cm', 'assets/images/products/product-5.jpg', 1
    UNION ALL SELECT 'Bulbasaur Garden Set', 'Mini Figure', 'Leaf House', 'Set Bulbasaur phong cách vườn cây nhỏ xinh, thích hợp mua theo combo hoặc làm quà tặng.', 950000, 1200000, 20, 4, 57, 'BULBA-014', 'H: 12cm', 'assets/images/products/product-6.jpg', 0
    UNION ALL SELECT 'Squirtle Water Squad', 'Mini Figure', 'Wave Studio', 'Squirtle cùng hiệu ứng nước trong trẻo, thiết kế vui nhộn và dễ phối với nhiều mẫu khác.', 1100000, 1350000, 18, 4, 52, 'SQUIRTLE-015', 'H: 14cm', 'assets/images/products/product-7.jpg', 0
    UNION ALL SELECT 'Vaporeon Crystal Lake', 'Scale Figure', 'Dream Resin', 'Vaporeon nằm trên mặt hồ pha lê, tone xanh dịu và phần base trong suốt bắt mắt.', 4300000, 5000000, 7, 5, 36, 'VAPOREON-016', 'H: 26cm', 'assets/images/products/product-8.jpg', 0
    UNION ALL SELECT 'Dragonite Mail Delivery', 'Nendoroid', 'Sky Post Studio', 'Dragonite phiên bản giao thư vui vẻ, dáng tròn đáng yêu và màu cam nổi bật.', 3200000, 3700000, 10, 5, 42, 'DRAGONITE-017', 'H: 24cm', 'assets/images/products/product-1.jpg', 0
    UNION ALL SELECT 'Jigglypuff Stage Idol', 'Prize Figure', 'Melody Studio', 'Jigglypuff đứng trên sân khấu mini kèm micro, hợp với bộ sưu tập figure dễ thương.', 1250000, 1500000, 16, 4, 33, 'JIGGLY-018', 'H: 16cm', 'assets/images/products/product-2.jpg', 0
) AS new_products
WHERE NOT EXISTS (
    SELECT 1
    FROM products
    WHERE products.sku = new_products.sku
);
