ALTER TABLE users 
ADD COLUMN specialization VARCHAR(255) DEFAULT NULL AFTER role,
ADD COLUMN verification_status ENUM('pending', 'approved', 'rejected') DEFAULT NULL AFTER specialization; 