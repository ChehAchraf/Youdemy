-- Create the database
CREATE DATABASE IF NOT EXISTS youdemy;
USE youdemy;

-- Users table (for students, teachers, and admins)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    firstName VARCHAR(50) NOT NULL,
    lastName VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'teacher', 'admin') NOT NULL,
    isActive BOOLEAN DEFAULT true,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL,
    description TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tags table
CREATE TABLE tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(30) NOT NULL UNIQUE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Courses table
CREATE TABLE courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    thumbnail VARCHAR(255),  -- Path to course thumbnail
    media VARCHAR(255),      -- Path to course content (video/PDF)
    teacherId INT NOT NULL,
    categoryId INT NOT NULL,
    price DECIMAL(10,2) DEFAULT 0.00,
    isApproved BOOLEAN DEFAULT false,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacherId) REFERENCES users(id),
    FOREIGN KEY (categoryId) REFERENCES categories(id)
);

-- Course Tags relationship
CREATE TABLE course_tags (
    courseId INT,
    tagId INT,
    PRIMARY KEY (courseId, tagId),
    FOREIGN KEY (courseId) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (tagId) REFERENCES tags(id) ON DELETE CASCADE
);

-- Enrollments table
CREATE TABLE enrollments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    studentId INT NOT NULL,
    courseId INT NOT NULL,
    enrollDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (studentId) REFERENCES users(id),
    FOREIGN KEY (courseId) REFERENCES courses(id),
    UNIQUE KEY unique_enrollment (studentId, courseId)
);

-- Comments table
CREATE TABLE comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    courseId INT NOT NULL,
    userId INT NOT NULL,
    content TEXT NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (courseId) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (userId) REFERENCES users(id)
);

-- Ratings table
CREATE TABLE ratings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    courseId INT NOT NULL,
    userId INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (courseId) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (userId) REFERENCES users(id),
    UNIQUE KEY unique_rating (userId, courseId)
);

-- Insert default admin user
INSERT INTO users (firstName, lastName, email, password, role) 
VALUES ('Admin', 'User', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert some default categories
INSERT INTO categories (name, description) VALUES
('Web Development', 'Learn web development technologies'),
('Mobile Development', 'Mobile app development courses'),
('Data Science', 'Data science and analytics'),
('Design', 'Graphic and UI/UX design'),
('Business', 'Business and entrepreneurship');

-- Insert some default tags
INSERT INTO tags (name) VALUES
('JavaScript'),
('Python'),
('React'),
('Angular'),
('Node.js'),
('PHP'),
('iOS'),
('Android'),
('Machine Learning'),
('UI/UX');