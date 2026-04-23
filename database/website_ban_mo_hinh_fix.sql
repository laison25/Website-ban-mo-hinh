CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'customer') NOT NULL DEFAULT 'customer',
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS users;
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    category VARCHAR(100) NOT NULL,
    studio VARCHAR(100) DEFAULT '',
    description TEXT,
    price DECIMAL(12,2) NOT NULL DEFAULT 0,
    old_price DECIMAL(12,2) NOT NULL DEFAULT 0,
    stock INT NOT NULL DEFAULT 0,
    rating INT NOT NULL DEFAULT 5,
    reviews INT NOT NULL DEFAULT 0,
    sku VARCHAR(80) DEFAULT '',
    size_label VARCHAR(80) DEFAULT '',
    image_path VARCHAR(255) NOT NULL,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    customer_name VARCHAR(120) NOT NULL,
    customer_email VARCHAR(120) NOT NULL,
    phone VARCHAR(30) NOT NULL,
    address TEXT NOT NULL,
    note TEXT,
    payment_method VARCHAR(30) NOT NULL DEFAULT 'COD',
    status VARCHAR(30) NOT NULL DEFAULT 'pending',
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(180) NOT NULL,
    price DECIMAL(12,2) NOT NULL DEFAULT 0,
    quantity INT NOT NULL DEFAULT 1,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
);

INSERT INTO users (full_name, username, email, password_hash, role, status) VALUES
('Admin Demo', 'admin', 'admin@example.com', '$2y$12$nTvTAl6Xu2t5fVR9kV898emLRrVyGgDES1M6wYeDeDBnkfjhwHRii', 'admin', 1),
('Khách Hàng Demo', 'user', 'user@example.com', '$2y$12$nTvTAl6Xu2t5fVR9kV898emLRrVyGgDES1M6wYeDeDBnkfjhwHRii', 'customer', 1);

INSERT INTO products (name, category, studio, description, price, old_price, stock, rating, reviews, sku, size_label, image_path, is_featured) VALUES
('Ash Ketchum & Greninja Family', 'Resin Figure', 'EGG Studio', 'Mô hình Ash Ketchum và Greninja theo phong cách resin, lấy cảm hứng từ bố cục ở trang chi tiết sản phẩm trong Figma.', 6400000, 7000000, 5, 4, 180, 'ASH-GRE-001', 'W: 32cm x H: 42cm', 'assets/images/products/product-1.jpg', 1),
('Eevee Evolution Statue', 'Nendoroid', 'Dream Resin', 'Bản tượng Eevee cùng dàn tiến hóa với bố cục lớn, màu sắc bắt mắt, phù hợp dùng làm banner hoặc trang đăng nhập.', 5200000, 6100000, 4, 5, 120, 'EEVEE-001', 'H: 38cm', 'assets/images/products/product-2.jpg', 1),
('Aquila Pokemon Diorama', 'Prize Figure', 'WST Studio', 'Thiết kế diorama chiến đấu với hiệu ứng năng lượng nổi bật, phù hợp khu Flash Sales.', 1200000, 1450000, 10, 5, 90, 'AQUILA-002', 'H: 25cm', 'assets/images/products/product-3.jpg', 1),
('Charizard Battle Explosion', 'Scale Figure', 'Fantasy Studio', 'Mô hình Charizard hiệu ứng lửa cỡ lớn, phù hợp làm best seller.', 17400000, 18500000, 3, 5, 56, 'CHAR-004', 'H: 58cm', 'assets/images/products/product-4.jpg', 1),
('Bruno Pokemon Egg Studio', 'Resin Figure', 'Egg Studio', 'Sản phẩm resin cao cấp với tone màu sáng và nhiều chi tiết.', 14500000, 15500000, 6, 4, 32, 'BRUNO-005', 'H: 40cm', 'assets/images/products/product-5.jpg', 0),
('World Champion Edition', 'Scale Figure', 'PC House Studio', 'Phiên bản World Champion với hiệu ứng ánh sáng, phù hợp section related items.', 10000000, 11200000, 4, 5, 41, 'WORLD-006', 'H: 44cm', 'assets/images/products/product-6.jpg', 0),
('Pikachu Cherry Robot', 'Prize Figure', 'Pika Studio', 'Mẫu figure dễ thương màu hồng, hợp mục Best Selling Products.', 1700000, 1900000, 11, 4, 23, 'PINK-007', 'H: 22cm', 'assets/images/products/product-7.jpg', 0),
('Greninja Water Burst', 'Resin Figure', 'GR Studio', 'Mô hình Greninja hiệu ứng nước theo layout chi tiết sản phẩm trong Figma.', 12600000, 13800000, 7, 5, 67, 'GREN-008', 'H: 45cm', 'assets/images/products/product-8.jpg', 0),
('Mewtwo Psychic Storm', 'Resin Figure', 'Origin Studio', 'Mewtwo tạo dáng chiến đấu với hiệu ứng năng lượng tím, hợp trưng bày trung tâm bộ sưu tập.', 8900000, 9800000, 8, 5, 74, 'MEWTWO-009', 'H: 36cm', 'assets/images/products/product-1.jpg', 1),
('Lucario Aura Sphere', 'Scale Figure', 'Blue Aura Studio', 'Lucario tung Aura Sphere với base ánh sáng xanh, chi tiết sắc nét và dáng đứng mạnh mẽ.', 6800000, 7600000, 9, 5, 61, 'LUCARIO-010', 'H: 34cm', 'assets/images/products/product-2.jpg', 1),
('Snorlax Picnic Day', 'Nendoroid', 'Happy Resin', 'Snorlax phiên bản picnic dễ thương, màu sắc tươi sáng, phù hợp góc decor bàn làm việc.', 2400000, 2900000, 15, 4, 38, 'SNORLAX-011', 'H: 20cm', 'assets/images/products/product-3.jpg', 0),
('Gengar Shadow Smile', 'Prize Figure', 'Night Studio', 'Gengar tạo hình nụ cười đặc trưng cùng hiệu ứng bóng tối, kích thước gọn dễ sưu tầm.', 1950000, 2300000, 13, 4, 45, 'GENGAR-012', 'H: 21cm', 'assets/images/products/product-4.jpg', 0),
('Rayquaza Sky Guardian', 'Resin Figure', 'Dragon Cloud Studio', 'Rayquaza uốn lượn trên nền mây, bản resin lớn dành cho người thích mô hình hoành tráng.', 15800000, 17200000, 4, 5, 29, 'RAYQUAZA-013', 'H: 62cm', 'assets/images/products/product-5.jpg', 1),
('Bulbasaur Garden Set', 'Mini Figure', 'Leaf House', 'Set Bulbasaur phong cách vườn cây nhỏ xinh, thích hợp mua theo combo hoặc làm quà tặng.', 950000, 1200000, 20, 4, 57, 'BULBA-014', 'H: 12cm', 'assets/images/products/product-6.jpg', 0),
('Squirtle Water Squad', 'Mini Figure', 'Wave Studio', 'Squirtle cùng hiệu ứng nước trong trẻo, thiết kế vui nhộn và dễ phối với nhiều mẫu khác.', 1100000, 1350000, 18, 4, 52, 'SQUIRTLE-015', 'H: 14cm', 'assets/images/products/product-7.jpg', 0),
('Vaporeon Crystal Lake', 'Scale Figure', 'Dream Resin', 'Vaporeon nằm trên mặt hồ pha lê, tone xanh dịu và phần base trong suốt bắt mắt.', 4300000, 5000000, 7, 5, 36, 'VAPOREON-016', 'H: 26cm', 'assets/images/products/product-8.jpg', 0),
('Dragonite Mail Delivery', 'Nendoroid', 'Sky Post Studio', 'Dragonite phiên bản giao thư vui vẻ, dáng tròn đáng yêu và màu cam nổi bật.', 3200000, 3700000, 10, 5, 42, 'DRAGONITE-017', 'H: 24cm', 'assets/images/products/product-1.jpg', 0),
('Jigglypuff Stage Idol', 'Prize Figure', 'Melody Studio', 'Jigglypuff đứng trên sân khấu mini kèm micro, hợp với bộ sưu tập figure dễ thương.', 1250000, 1500000, 16, 4, 33, 'JIGGLY-018', 'H: 16cm', 'assets/images/products/product-2.jpg', 0);
