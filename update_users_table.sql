-- Add verification status if it doesn't exist
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS verification_status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending';

-- Add verification reason if it doesn't exist
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS verification_reason TEXT DEFAULT NULL;

-- Add verified_at timestamp if it doesn't exist
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS verified_at DATETIME DEFAULT NULL;

-- Add verified_by if it doesn't exist
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS verified_by INT DEFAULT NULL,
ADD FOREIGN KEY IF NOT EXISTS (verified_by) REFERENCES users(id);

-- Add timestamps if they don't exist
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;