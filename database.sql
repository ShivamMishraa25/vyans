-- Create database (optional; you can create manually if preferred)
CREATE DATABASE IF NOT EXISTS vyans CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vyans;

-- Drop tables if they exist (for clean setup)
DROP TABLE IF EXISTS post_relations;
DROP TABLE IF EXISTS posts;
DROP TABLE IF EXISTS admin;
DROP TABLE IF EXISTS hero;
DROP TABLE IF EXISTS post_images;

-- Posts table
CREATE TABLE posts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title_hi VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL UNIQUE,
  content_hi TEXT NOT NULL,
  category VARCHAR(50) NOT NULL,
  cover_image_path VARCHAR(255) DEFAULT NULL,
  tags VARCHAR(255) DEFAULT NULL,
  author_name VARCHAR(100) DEFAULT NULL,
  is_top_article TINYINT(1) NOT NULL DEFAULT 0,
  gallery_count INT NOT NULL DEFAULT 0,
  -- New categorization flags
  isBiography TINYINT(1) NOT NULL DEFAULT 0,
  isNews TINYINT(1) NOT NULL DEFAULT 0,
  isLaw TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admin table (single user)
CREATE TABLE admin (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Junction table for related articles
CREATE TABLE post_relations (
  post_id INT NOT NULL,
  related_post_id INT NOT NULL,
  PRIMARY KEY (post_id, related_post_id),
  CONSTRAINT fk_pr_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
  CONSTRAINT fk_pr_related FOREIGN KEY (related_post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Hero table
CREATE TABLE hero (
  id TINYINT NOT NULL PRIMARY KEY DEFAULT 1,
  image_path VARCHAR(255) DEFAULT NULL,
  intro_text TEXT DEFAULT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Gallery images for posts
CREATE TABLE post_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  post_id INT NOT NULL,
  image_path VARCHAR(255) NOT NULL,
  caption VARCHAR(255) DEFAULT NULL,
  sort_order INT DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (post_id),
  CONSTRAINT fk_post_images_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Mock posts (Hindi)
INSERT INTO posts (title_hi, slug, content_hi, category, cover_image_path, tags, is_top_article)
VALUES
(N'भारत की अर्थव्यवस्था में नई ऊर्जा', 'bharat-arthvyavastha-nayi-urja', N'भारत की अर्थव्यवस्था में हाल के महीनों में नई ऊर्जा देखी गई है। निर्यात, विनिर्माण और MSME सेक्टर में सुधार के संकेत मिले हैं। विशेषज्ञों का मानना है कि बुनियादी ढांचे पर निवेश और स्टार्टअप इकोसिस्टम में बढ़ोतरी आने वाले वर्षों में विकास को गति देगी।', 'अर्थव्यवस्था', 'uploads/images/sample1.jpg', 'भारत,अर्थव्यवस्था,विकास,MSME,स्टार्टअप', 1),
(N'खेल जगत में भारत का दबदबा', 'khel-jagat-me-bharat-ka-dabdaba', N'ओलंपिक और एशियाई खेलों में भारतीय खिलाड़ियों का प्रदर्शन शानदार रहा है। नई खेल नीतियों और ग्रासरूट स्तर पर प्रशिक्षण से खेलों का स्तर निरंतर ऊँचा हो रहा है।', 'खेल', 'uploads/images/sample2.jpg', 'खेल,ओलंपिक,एशियाई खेल,भारत', 0),
(N'टेक्नोलॉजी से बदलती जिंदगी', 'technology-se-badalti-zindagi', N'आर्टिफिशियल इंटेलिजेंस, क्लाउड कंप्यूटिंग और 5G तकनीक हमारे दैनिक जीवन में तेजी से परिवर्तन ला रही है। शिक्षा, स्वास्थ्य और कृषि जैसे क्षेत्रों में तकनीक का उपयोग बढ़ रहा है।', 'टेक्नोलॉजी', 'uploads/images/sample3.jpg', 'टेक,AI,5G,क्लाउड', 0);

-- Mock admin user (username: admin, password: password)
-- Hash below is for "password" using PASSWORD_DEFAULT (bcrypt) from PHP manual
INSERT INTO admin (username, password_hash)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Mock relations (assuming inserted IDs are 1,2,3)
INSERT INTO post_relations (post_id, related_post_id) VALUES
(1, 2),
(1, 3);

-- Seed a default hero row
INSERT INTO hero (id, image_path, intro_text) VALUES
(1, 'uploads/images/sample1.jpg', N'द व्यान्स में आपका स्वागत है। ताज़ा ख़बरें, विश्लेषण और प्रेरक कहानियाँ, सब कुछ एक ही जगह।');

-- Migration (run manually on existing DB):
-- ALTER TABLE posts ADD COLUMN gallery_count INT NOT NULL DEFAULT 0;
-- CREATE TABLE post_images (
--   id INT AUTO_INCREMENT PRIMARY KEY,
--   post_id INT NOT NULL,
--   image_path VARCHAR(255) NOT NULL,
--   caption VARCHAR(255) DEFAULT NULL,
--   sort_order INT DEFAULT NULL,
--   created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
--   INDEX (post_id),
--   CONSTRAINT fk_post_images_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- Optional: backfill gallery_count
-- UPDATE posts p SET gallery_count = (SELECT COUNT(*) FROM post_images pi WHERE pi.post_id = p.id);
-- Migration for existing databases:
-- ALTER TABLE posts ADD COLUMN isBiography TINYINT(1) NOT NULL DEFAULT 0 AFTER gallery_count;
-- ALTER TABLE posts ADD COLUMN isNews TINYINT(1) NOT NULL DEFAULT 0 AFTER isBiography;
-- ALTER TABLE posts ADD COLUMN isLaw TINYINT(1) NOT NULL DEFAULT 0 AFTER isNews;
-- ALTER TABLE posts ADD COLUMN author_name VARCHAR(100) DEFAULT NULL AFTER tags;


-- handle multiple images
-- add another link for showing posts of biography and one more. also show this tab on homepage somewhere.
-- share links must work. add twitter sharing link.
-- add meta tags and favicon
-- add read all articles button at the bottom of homepage main section.