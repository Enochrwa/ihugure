-- IHUGURE APP Database Schema
-- Created for local development

SET FOREIGN_KEY_CHECKS = 0;

-- Migration tracking table (used by services/db.php migrate())
CREATE TABLE IF NOT EXISTS migration (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    checksum VARCHAR(255),
    addtime TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Languages (used for question/answer localization)
CREATE TABLE IF NOT EXISTS language (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    code VARCHAR(10)
);

-- Question categories
CREATE TABLE IF NOT EXISTS category (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL
);

-- Investment amounts (entry fee for games)
CREATE TABLE IF NOT EXISTS investment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    amount DECIMAL(10,2) NOT NULL
);

-- Users (telephone-based auth)
CREATE TABLE IF NOT EXISTS user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    telephone VARCHAR(20) NOT NULL UNIQUE,
    account_type ENUM('D') DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Games
CREATE TABLE IF NOT EXISTS game (
    id INT AUTO_INCREMENT PRIMARY KEY,
    languageid INT NOT NULL,
    investmentid INT NOT NULL,
    categoryid INT NOT NULL,
    userid INT NOT NULL,
    amount_invested DECIMAL(10,2) NOT NULL,
    useremail VARCHAR(100) NOT NULL,
    payment_status VARCHAR(50) DEFAULT NULL,
    state ENUM('PLAYING','LOST','CANWITHDRAW','WON','TIMEOUT','OUT_OF_SCREEN') DEFAULT NULL,
    level INT DEFAULT 0,
    questions_answered INT DEFAULT 0,
    current_compensation DECIMAL(10,2) DEFAULT 0,
    amount_gained DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (languageid) REFERENCES language(id),
    FOREIGN KEY (investmentid) REFERENCES investment(id),
    FOREIGN KEY (categoryid) REFERENCES category(id),
    FOREIGN KEY (userid) REFERENCES user(id)
);

-- Inquiries (question topics)
CREATE TABLE IF NOT EXISTS inquiry (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    complexity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inquiry <-> Category mapping
CREATE TABLE IF NOT EXISTS inquirycategory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inquiryid INT NOT NULL,
    categoryid INT NOT NULL,
    FOREIGN KEY (inquiryid) REFERENCES inquiry(id),
    FOREIGN KEY (categoryid) REFERENCES category(id)
);

-- Questions
CREATE TABLE IF NOT EXISTS question (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    inquiryid INT NOT NULL,
    languageid INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inquiryid) REFERENCES inquiry(id),
    FOREIGN KEY (languageid) REFERENCES language(id)
);

-- Answers
CREATE TABLE IF NOT EXISTS answer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    correct TINYINT(1) DEFAULT 0,
    inquiryid INT NOT NULL,
    questionid INT NOT NULL,
    languageid INT NOT NULL,
    FOREIGN KEY (inquiryid) REFERENCES inquiry(id),
    FOREIGN KEY (questionid) REFERENCES question(id),
    FOREIGN KEY (languageid) REFERENCES language(id)
);

-- Game responses (one per question per game)
CREATE TABLE IF NOT EXISTS game_response (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_time INT NOT NULL,
    questionid INT NOT NULL,
    gameid INT NOT NULL,
    inquiryid INT NOT NULL,
    investmentid INT NOT NULL,
    userid INT NOT NULL,
    amount_compensated DECIMAL(10,2) DEFAULT 0,
    state ENUM('PENDING','CORRECT','WRONG','LOST','TIMEOUT') DEFAULT 'PENDING',
    answerid INT DEFAULT NULL,
    answer_time INT DEFAULT NULL,
    used_time INT DEFAULT NULL,
    level INT DEFAULT 0,
    FOREIGN KEY (gameid) REFERENCES game(id),
    FOREIGN KEY (questionid) REFERENCES question(id),
    FOREIGN KEY (inquiryid) REFERENCES inquiry(id),
    FOREIGN KEY (userid) REFERENCES user(id)
);

-- Game response <-> answer mapping (all possible answers for a response)
CREATE TABLE IF NOT EXISTS game_responseanswer (
    id INT AUTO_INCREMENT PRIMARY KEY,
    answerid INT NOT NULL,
    game_responseid INT NOT NULL,
    FOREIGN KEY (answerid) REFERENCES answer(id),
    FOREIGN KEY (game_responseid) REFERENCES game_response(id)
);

-- Game logs
CREATE TABLE IF NOT EXISTS game_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event VARCHAR(100) NOT NULL,
    info TEXT,
    gameid INT DEFAULT NULL,
    details TEXT,
    ip VARCHAR(45),
    session VARCHAR(100),
    addtime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (gameid) REFERENCES game(id)
);

SET FOREIGN_KEY_CHECKS = 1;
