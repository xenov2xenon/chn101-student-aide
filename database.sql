-- CHN101 Student Aide Database Schema
-- Import this in phpMyAdmin

CREATE DATABASE IF NOT EXISTS chn101_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE chn101_db;

-- Lessons Table
CREATE TABLE lessons (
    lesson_id INT AUTO_INCREMENT PRIMARY KEY,
    lesson_title VARCHAR(255) NOT NULL,
    lesson_number INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Students Table
CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    preferred_language ENUM('english','bangla') DEFAULT 'english',
    current_streak INT DEFAULT 0,
    longest_streak INT DEFAULT 0,
    total_points INT DEFAULT 0,
    last_quiz_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Flashcards Table (Official)
CREATE TABLE flashcards (
    flashcard_id INT AUTO_INCREMENT PRIMARY KEY,
    lesson_id INT NOT NULL,
    chinese_text VARCHAR(100) NOT NULL,
    pinyin VARCHAR(255) NOT NULL,
    meaning_english VARCHAR(500) NOT NULL,
    meaning_bangla VARCHAR(500),
    is_official TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_id) REFERENCES lessons(lesson_id) ON DELETE CASCADE
);

-- Personal Flashcards Table
CREATE TABLE personal_flashcards (
    personal_fc_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    lesson_id INT NOT NULL,
    chinese_text VARCHAR(100) NOT NULL,
    pinyin VARCHAR(255),
    meaning_english VARCHAR(500),
    meaning_bangla VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(lesson_id) ON DELETE CASCADE
);

-- Quiz Questions Table
CREATE TABLE quiz_questions (
    question_id INT AUTO_INCREMENT PRIMARY KEY,
    lesson_id INT NOT NULL,
    question_text_english TEXT NOT NULL,
    question_text_bangla TEXT,
    correct_answer_english VARCHAR(255) NOT NULL,
    correct_answer_bangla VARCHAR(255),
    option_b_english VARCHAR(255) NOT NULL,
    option_b_bangla VARCHAR(255),
    option_c_english VARCHAR(255) NOT NULL,
    option_c_bangla VARCHAR(255),
    option_d_english VARCHAR(255) NOT NULL,
    option_d_bangla VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lesson_id) REFERENCES lessons(lesson_id) ON DELETE CASCADE
);

-- Quiz Attempts Table
CREATE TABLE quiz_attempts (
    attempt_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    lesson_id INT NOT NULL,
    score INT NOT NULL,
    total_questions INT NOT NULL,
    attempt_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(lesson_id) ON DELETE CASCADE
);

-- Quiz Answers (per attempt)
CREATE TABLE quiz_answers (
    answer_id INT AUTO_INCREMENT PRIMARY KEY,
    attempt_id INT NOT NULL,
    question_id INT NOT NULL,
    selected_answer VARCHAR(255),
    is_correct TINYINT(1) DEFAULT 0,
    FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(attempt_id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES quiz_questions(question_id) ON DELETE CASCADE
);

-- Student Lesson Progress
CREATE TABLE student_progress (
    progress_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    lesson_id INT NOT NULL,
    lessons_completed INT DEFAULT 0,
    best_score INT DEFAULT 0,
    last_attempt TIMESTAMP NULL,
    UNIQUE KEY unique_student_lesson (student_id, lesson_id),
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(lesson_id) ON DELETE CASCADE
);

-- Admin Table
CREATE TABLE admins (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================
-- SAMPLE DATA
-- =====================

INSERT INTO admins (username, password_hash) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- password: password

INSERT INTO lessons (lesson_title, lesson_number, description) VALUES
('Greetings & Introductions', 1, 'Learn basic Chinese greetings and how to introduce yourself'),
('Numbers & Counting', 2, 'Master Chinese numbers from 1 to 100'),
('Colors & Shapes', 3, 'Learn vocabulary for colors and basic shapes'),
('Family Members', 4, 'Vocabulary for family relationships'),
('Food & Drinks', 5, 'Common food and drink vocabulary');

INSERT INTO students (username, email, password_hash, preferred_language, current_streak, total_points) VALUES
('RahimBD', 'rahim@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bangla', 7, 450),
('KarimCN', 'karim@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'english', 5, 320),
('NahalEdu', 'nahal@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'english', 12, 780),
('AishaBD', 'aisha@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bangla', 3, 180),
('TanvirLearn', 'tanvir@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'english', 9, 560);

INSERT INTO flashcards (lesson_id, chinese_text, pinyin, meaning_english, meaning_bangla) VALUES
(1,'你好','Nǐ hǎo','Hello','হ্যালো'),
(1,'谢谢','Xièxiè','Thank you','ধন্যবাদ'),
(1,'再见','Zàijiàn','Goodbye','বিদায়'),
(1,'对不起','Duìbuqǐ','Sorry','দুঃখিত'),
(1,'我叫','Wǒ jiào','My name is','আমার নাম'),
(2,'一','Yī','One','এক'),
(2,'二','Èr','Two','দুই'),
(2,'三','Sān','Three','তিন'),
(2,'四','Sì','Four','চার'),
(2,'五','Wǔ','Five','পাঁচ'),
(3,'红色','Hóngsè','Red','লাল'),
(3,'蓝色','Lánsè','Blue','নীল'),
(3,'绿色','Lǜsè','Green','সবুজ'),
(4,'爸爸','Bàba','Father','বাবা'),
(4,'妈妈','Māma','Mother','মা'),
(5,'米饭','Mǐfàn','Rice','ভাত'),
(5,'水','Shuǐ','Water','পানি');

INSERT INTO quiz_questions (lesson_id, question_text_english, question_text_bangla, correct_answer_english, correct_answer_bangla, option_b_english, option_b_bangla, option_c_english, option_c_bangla, option_d_english, option_d_bangla) VALUES
(1,'What does 你好 (Nǐ hǎo) mean?','你好 (Nǐ hǎo) এর অর্থ কী?','Hello','হ্যালো','Goodbye','বিদায়','Thank you','ধন্যবাদ','Sorry','দুঃখিত'),
(1,'How do you say Thank you in Chinese?','চীনা ভাষায় ধন্যবাদ কীভাবে বলে?','谢谢 (Xièxiè)','谢谢 (Xièxiè)','你好 (Nǐ hǎo)','你好 (Nǐ hǎo)','再见 (Zàijiàn)','再见 (Zàijiàn)','对不起 (Duìbuqǐ)','对不起 (Duìbuqǐ)'),
(1,'What does 再见 mean?','再见 এর অর্থ কী?','Goodbye','বিদায়','Hello','হ্যালো','Sorry','দুঃখিত','Thank you','ধন্যবাদ'),
(2,'What is 三 (Sān) in English?','三 (Sān) ইংরেজিতে কী?','Three','তিন','One','এক','Two','দুই','Four','চার'),
(2,'How do you write 5 in Chinese?','চীনা ভাষায় ৫ কীভাবে লেখে?','五 (Wǔ)','五 (Wǔ)','四 (Sì)','四 (Sì)','六 (Liù)','六 (Liù)','三 (Sān)','三 (Sān)');
