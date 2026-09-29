-- =====================================================
-- Database: db_pancong
-- Sistem Pemesanan Pancong Lumer Selindung Baru
-- 8 Tabel Relasional | Charset utf8mb4
-- Cara pakai: buat DB lalu import file ini (phpMyAdmin / mysql)
--   CREATE DATABASE db_pancong; USE db_pancong; SOURCE database.sql;
-- Akun demo:
--   Admin    : admin    / admin123
--   Customer : customer / customer123
--   (password tersimpan hash bcrypt; login.php juga mendukung
--    fallback plaintext saat pertama kali migrasi)
-- =====================================================

CREATE DATABASE IF NOT EXISTS db_pancong
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE db_pancong;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS order_item_toppings;
DROP TABLE IF EXISTS order_items;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS topping_recommendations;
DROP TABLE IF EXISTS toppings;
DROP TABLE IF EXISTS products;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

-- -----------------------------------------------------
-- 1. users
-- -----------------------------------------------------
CREATE TABLE users (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL,
  password VARCHAR(255) NOT NULL,
  nama_lengkap VARCHAR(100) NOT NULL,
  email VARCHAR(100) DEFAULT NULL,
  role ENUM('customer','admin') NOT NULL DEFAULT 'customer',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 2. categories
-- -----------------------------------------------------
CREATE TABLE categories (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  kode_kategori VARCHAR(30) NOT NULL,
  nama_kategori VARCHAR(60) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categories_kode (kode_kategori)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 3. products
-- -----------------------------------------------------
CREATE TABLE products (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  category_id INT UNSIGNED NOT NULL,
  nama_menu VARCHAR(100) NOT NULL,
  deskripsi VARCHAR(255) DEFAULT NULL,
  harga_dasar INT UNSIGNED NOT NULL DEFAULT 0,
  gambar VARCHAR(255) DEFAULT NULL,
  is_available TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_products_category (category_id),
  CONSTRAINT fk_products_category
    FOREIGN KEY (category_id) REFERENCES categories (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 4. toppings
-- -----------------------------------------------------
CREATE TABLE toppings (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama_topping VARCHAR(80) NOT NULL,
  harga INT UNSIGNED NOT NULL DEFAULT 0,
  is_available TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 5. topping_recommendations (aturan asosiasi)
-- -----------------------------------------------------
CREATE TABLE topping_recommendations (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  base_topping_id INT UNSIGNED NOT NULL,
  recommended_topping_id INT UNSIGNED NOT NULL,
  pesan_alasan VARCHAR(255) NOT NULL DEFAULT '',
  PRIMARY KEY (id),
  KEY idx_rekom_base (base_topping_id),
  KEY idx_rekom_rec (recommended_topping_id),
  CONSTRAINT fk_rekom_base
    FOREIGN KEY (base_topping_id) REFERENCES toppings (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_rekom_rec
    FOREIGN KEY (recommended_topping_id) REFERENCES toppings (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  UNIQUE KEY uq_rekom_pair (base_topping_id, recommended_topping_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 6. orders
-- -----------------------------------------------------
CREATE TABLE orders (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  no_antrean VARCHAR(20) NOT NULL,
  urutan_harian INT UNSIGNED NOT NULL DEFAULT 0,
  user_id INT UNSIGNED DEFAULT NULL,
  nama_pemesan VARCHAR(100) NOT NULL,
  no_wa VARCHAR(20) NOT NULL,
  tipe_order ENUM('dine_in','takeaway') NOT NULL DEFAULT 'takeaway',
  metode_bayar ENUM('qris','tunai') NOT NULL DEFAULT 'qris',
  bukti_bayar VARCHAR(255) DEFAULT NULL,
  status_bayar ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  status_pesanan ENUM('pending','dimasak','siap_ambil','selesai','batal') NOT NULL DEFAULT 'pending',
  total_bayar INT UNSIGNED NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_orders_antrean (no_antrean),
  KEY idx_orders_tanggal (created_at),
  KEY idx_orders_status (status_pesanan, status_bayar),
  CONSTRAINT fk_orders_user
    FOREIGN KEY (user_id) REFERENCES users (id)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 7. order_items
-- -----------------------------------------------------
CREATE TABLE order_items (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_id INT UNSIGNED NOT NULL,
  product_id INT UNSIGNED NOT NULL,
  porsi ENUM('1_loyang','half_loyang') NOT NULL DEFAULT '1_loyang',
  kematangan ENUM('matang','setengah_matang','tiga_perempat') DEFAULT NULL,
  qty INT UNSIGNED NOT NULL DEFAULT 1,
  subtotal INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_items_order (order_id),
  KEY idx_items_product (product_id),
  CONSTRAINT fk_items_order
    FOREIGN KEY (order_id) REFERENCES orders (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_items_product
    FOREIGN KEY (product_id) REFERENCES products (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- 8. order_item_toppings
-- -----------------------------------------------------
CREATE TABLE order_item_toppings (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  order_item_id INT UNSIGNED NOT NULL,
  topping_id INT UNSIGNED NOT NULL,
  harga_topping INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_oit_item (order_item_id),
  CONSTRAINT fk_oit_item
    FOREIGN KEY (order_item_id) REFERENCES order_items (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_oit_topping
    FOREIGN KEY (topping_id) REFERENCES toppings (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- SEED DATA
-- =====================================================

-- Users: password disimpan PLAINTEXT saat seed agar demo sidang langsung jalan.
-- login.php otomatis meng-upgrade ke bcrypt (password_hash) saat login sukses
-- pertama kali, sehingga tabel tetap aman setelah dipakai.
INSERT INTO users (username, password, nama_lengkap, email, role) VALUES
('admin',    'admin123',    'Kasir Gerai Selindung Baru', 'kasir@panconglumer.test',     'admin'),
('customer', 'customer123', 'Pelanggan Demo',             'pelanggan@panconglumer.test', 'customer');

INSERT INTO categories (kode_kategori, nama_kategori) VALUES
('1_loyang',    'Pancong 1 Loyang'),
('half_loyang', 'Pancong 1/2 Loyang'),
('topping',     'Topping Tambahan');

INSERT INTO products (category_id, nama_menu, deskripsi, harga_dasar, gambar, is_available) VALUES
((SELECT id FROM categories WHERE kode_kategori='1_loyang'), 'Pancong Choco Crunchy', 'Cokelat lumer + crumble renyah, best seller.', 18000, NULL, 1),
((SELECT id FROM categories WHERE kode_kategori='1_loyang'), 'Pancong Cheese Crunchy', 'Keju parut melimpah + crumble gurih.', 20000, NULL, 1),
((SELECT id FROM categories WHERE kode_kategori='1_loyang'), 'Pancong Tiramisu', 'Krim tiramisu + taburan cocoa premium.', 20000, NULL, 1),
((SELECT id FROM categories WHERE kode_kategori='1_loyang'), 'Pancong Matcha', 'Matcha creamy + crumble manis seimbang.', 20000, NULL, 1),
((SELECT id FROM categories WHERE kode_kategori='1_loyang'), 'Pancong Taro Red Crumb', 'Taro lembut + red crumb manis.', 22000, NULL, 1);

INSERT INTO toppings (nama_topping, harga, is_available) VALUES
('Keju Parut',   5000, 1),
('Choco Chips',  5000, 1),
('Oreo Crumb',   6000, 1),
('Red Crumb',    6000, 1),
('Susu Cokelat', 3000, 1),
('Susu Putih',   3000, 1);

-- Aturan asosiasi: base -> rekomendasi
INSERT INTO topping_recommendations (base_topping_id, recommended_topping_id, pesan_alasan) VALUES
((SELECT id FROM toppings WHERE nama_topping='Keju Parut'),   (SELECT id FROM toppings WHERE nama_topping='Choco Chips'),  'Keju gurih paling cocok dipadukan Choco Chips manis.'),
((SELECT id FROM toppings WHERE nama_topping='Choco Chips'),  (SELECT id FROM toppings WHERE nama_topping='Keju Parut'),   'Choco + Keju adalah kombinasi terfavorit pelanggan.'),
((SELECT id FROM toppings WHERE nama_topping='Oreo Crumb'),   (SELECT id FROM toppings WHERE nama_topping='Susu Cokelat'),  'Oreo Crumb makin lumer disiram Susu Cokelat.'),
((SELECT id FROM toppings WHERE nama_topping='Red Crumb'),    (SELECT id FROM toppings WHERE nama_topping='Susu Putih'),    'Red Crumb + Susu Putih memberi rasa creamy seimbang.'),
((SELECT id FROM toppings WHERE nama_topping='Keju Parut'),   (SELECT id FROM toppings WHERE nama_topping='Susu Cokelat'),  'Keju + Susu Cokelat: gurih-manis ala cafe.'),
((SELECT id FROM toppings WHERE nama_topping='Oreo Crumb'),   (SELECT id FROM toppings WHERE nama_topping='Keju Parut'),    'Oreo + Keju: renyah, gurih, dan manis dalam satu gigitan.');
