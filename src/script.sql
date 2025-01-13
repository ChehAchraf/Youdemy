-- Create the database
CREATE DATABASE IF NOT EXISTS youdemy DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE youdemy;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    firstName VARCHAR(50) NOT NULL,
    lastName VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'teacher', 'admin') NOT NULL,
    isActive BOOLEAN DEFAULT true,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Categories table
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tags table
CREATE TABLE tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL UNIQUE,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Media files table
CREATE TABLE media_files (
    id INT PRIMARY KEY AUTO_INCREMENT,
    fileName VARCHAR(255) NOT NULL,
    fileType ENUM('video', 'image', 'document') NOT NULL,
    filePath VARCHAR(255) NOT NULL,
    fileSize INT NOT NULL,
    uploadedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Courses table
CREATE TABLE courses (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    thumbnailId INT,
    price DECIMAL(10,2) NOT NULL,
    teacherId INT NOT NULL,
    categoryId INT NOT NULL,
    isApproved BOOLEAN DEFAULT false,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacherId) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (categoryId) REFERENCES categories(id) ON DELETE CASCADE,
    FOREIGN KEY (thumbnailId) REFERENCES media_files(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Course sections table
CREATE TABLE course_sections (
    id INT PRIMARY KEY AUTO_INCREMENT,
    courseId INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    orderIndex INT NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (courseId) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Course lessons table
CREATE TABLE course_lessons (
    id INT PRIMARY KEY AUTO_INCREMENT,
    sectionId INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    duration INT,
    orderIndex INT NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sectionId) REFERENCES course_sections(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Lesson content table
CREATE TABLE lesson_content (
    id INT PRIMARY KEY AUTO_INCREMENT,
    lessonId INT NOT NULL,
    mediaId INT NOT NULL,
    contentType ENUM('video', 'document', 'image') NOT NULL,
    orderIndex INT NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (lessonId) REFERENCES course_lessons(id) ON DELETE CASCADE,
    FOREIGN KEY (mediaId) REFERENCES media_files(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Course Tags junction table
CREATE TABLE course_tags (
    id INT PRIMARY KEY AUTO_INCREMENT,
    courseId INT NOT NULL,
    tagId INT NOT NULL,
    createdAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (courseId) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (tagId) REFERENCES tags(id) ON DELETE CASCADE,
    UNIQUE KEY unique_course_tag (courseId, tagId)
) ENGINE=InnoDB;

-- Enrollments table
CREATE TABLE enrollments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    studentId INT NOT NULL,
    courseId INT NOT NULL,
    enrollDate TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    progress INT DEFAULT 0,
    status ENUM('active', 'completed', 'dropped') DEFAULT 'active',
    FOREIGN KEY (studentId) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (courseId) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (studentId, courseId)
) ENGINE=InnoDB;

-- Add indexes
CREATE INDEX idx_courses_teacher ON courses(teacherId);
CREATE INDEX idx_courses_category ON courses(categoryId);
CREATE INDEX idx_courses_thumbnail ON courses(thumbnailId);
CREATE INDEX idx_course_sections_course ON course_sections(courseId);
CREATE INDEX idx_course_lessons_section ON course_lessons(sectionId);
CREATE INDEX idx_lesson_content_lesson ON lesson_content(lessonId);
CREATE INDEX idx_lesson_content_media ON lesson_content(mediaId);
CREATE INDEX idx_enrollments_student ON enrollments(studentId);
CREATE INDEX idx_enrollments_course ON enrollments(courseId);
CREATE INDEX idx_course_tags_course ON course_tags(courseId);
CREATE INDEX idx_course_tags_tag ON course_tags(tagId);