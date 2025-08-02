
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create students table for storing student details
CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    github_username VARCHAR(50),
    leetcode_username VARCHAR(50),
    linkedin_profile VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create activities table for storing fetched activities
CREATE TABLE IF NOT EXISTS activities (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    source ENUM('GitHub','LeetCode','LinkedIn','Aggregate') NOT NULL,
    activity_data TEXT,
    activity_type VARCHAR(100),
    activity_date DATE,
    fetched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_student_source (student_id, source),
    INDEX idx_fetched_at (fetched_at)
);

-- Insert default admin user (password: admin123)
-- Note: In production, use a stronger password and hash
INSERT INTO admin (username, password_hash) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi') 
ON DUPLICATE KEY UPDATE username = username;

-- Insert sample students for testing
INSERT INTO students (name, email, github_username, leetcode_username, linkedin_profile) VALUES 
('John Doe', 'john.doe@college.edu', 'johndoe', 'johndoe123', 'https://linkedin.com/in/johndoe'),
('Jane Smith', 'jane.smith@college.edu', 'janesmith', 'janesmith456', 'https://linkedin.com/in/janesmith'),
('Mike Johnson', 'mike.johnson@college.edu', 'mikejohnson', 'mikej789', 'https://linkedin.com/in/mikejohnson')
ON DUPLICATE KEY UPDATE name = VALUES(name);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS idx_students_github ON students(github_username);
CREATE INDEX IF NOT EXISTS idx_students_leetcode ON students(leetcode_username);
