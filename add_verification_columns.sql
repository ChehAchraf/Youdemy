ALTER TABLE users 
ADD COLUMN verification_reason TEXT DEFAULT NULL AFTER verification_status,
ADD COLUMN verified_at DATETIME DEFAULT NULL AFTER verification_reason,
ADD COLUMN verified_by INT DEFAULT NULL AFTER verified_at,
ADD FOREIGN KEY (verified_by) REFERENCES users(id); 