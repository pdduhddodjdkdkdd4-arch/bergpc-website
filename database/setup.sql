-- ============================================
-- Berg PC Website Database Setup Script
-- Database: bergpcc
-- Charset: utf8mb4
-- ============================================

-- 创建表结构

CREATE TABLE IF NOT EXISTS lawyers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    title VARCHAR(255),
    bio TEXT,
    image VARCHAR(500),
    image_type ENUM('local','url') DEFAULT 'local',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS form_submissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    submission_id VARCHAR(50) UNIQUE NOT NULL,
    form_id VARCHAR(20) NOT NULL,
    form_name VARCHAR(255),
    data LONGTEXT NOT NULL,
    ip VARCHAR(50),
    user_agent VARCHAR(500),
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_form_id (form_id),
    INDEX idx_submitted_at (submitted_at),
    INDEX idx_submission_id (submission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS forms (
    id VARCHAR(20) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    honeypot_field VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 插入律师数据

INSERT INTO lawyers (name, slug, title, bio, image, image_type, created_at, updated_at) VALUES
('Geoffrey Berg', 'geoffrey-berg', 'Trial Lawyer, Managing Partner', 'Geoffrey Berg is the founding partner of Berg PC. He is a seasoned trial lawyer with extensive experience in business litigation, crypto litigation, and complex commercial disputes. His dedication to his clients and relentless pursuit of justice have earned him recognition as one of the top lawyers in America.', '../../wp-content/uploads/2025/04/DSC02815-scaled-e1744302932965-1024x684.jpg', 'local', '2025-01-01 00:00:00', '2025-05-01 00:00:00'),
('Kathryn E. Nelson', 'kathryn-e-nelson', 'Trial Lawyer, Partner', 'Kathryn E. Nelson is a partner at Berg PC, specializing in trial litigation. With a sharp analytical mind and a compassionate approach to client advocacy, she has successfully represented clients in a wide range of complex legal matters.', '../../wp-content/uploads/2025/04/Kathryn-Nelson094-Edit-2.jpg', 'local', '2025-01-01 00:00:00', '2025-05-01 00:00:00'),
('Tomas Francisco Tijerina', 'tomas-francisco-tijerina', 'Business & Transactional Lawyer, Of Counsel', 'Tomas Francisco Tijerina serves as Of Counsel at Berg PC, bringing expertise in business and transactional law. His deep understanding of corporate transactions and regulatory compliance makes him an invaluable asset to the firm''s clients.', '../../wp-content/uploads/2025/04/tomas-francisco-tijerina.jpg', 'local', '2025-01-01 00:00:00', '2025-05-01 00:00:00'),
('Tracy Moberg', 'tracy-moberg', 'Business Manager', 'Tracy Moberg has been an integral part of Berg PC since 2019, when she joined as a Senior Paralegal. With a strong background in both corporate and criminal justice, Tracy brings a wealth of experience to her role. Prior to joining the firm, she served as a Probation Officer in Fort Bend County, Texas, following the completion of her B.S. in Criminology. Tracy''s comprehensive skill set and dedication continue to make her a valued member of the Berg PC team.', '../../wp-content/uploads/2025/04/tracy-moberg.jpg', 'local', '2025-01-01 00:00:00', '2025-05-01 00:00:00');

-- 插入表单配置

INSERT INTO forms (id, name, honeypot_field) VALUES
('2528', 'Crypto Fraud & Recovery', 'wpforms[fields][3]'),
('4147', 'Meta Crypto Scam Ads Investigation', 'wpforms[fields][3]'),
('3952', 'Coinbase Data Breach', 'wpforms[fields][3]'),
('4296', 'Business Litigation', 'wpforms[fields][1]'),
('4002', 'Crypto Business Transactions', 'wpforms[fields][1]'),
('4275', 'Crypto Litigation', 'wpforms[fields][1]'),
('3600', 'vCard Disclaimer', 'wpforms[fields][1]'),
('3778', 'Lawyer List Disclaimer', 'wpforms[fields][1]');
