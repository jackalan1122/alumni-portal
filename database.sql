-- Create Database
CREATE DATABASE IF NOT EXISTS alumni_portal;
USE alumni_portal;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('alumni', 'student', 'admin') DEFAULT 'student',
    graduation_year VARCHAR(4),
    major VARCHAR(100),
    current_position VARCHAR(150),
    company VARCHAR(150),
    bio TEXT,
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_user_type (user_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Job Listings Table
CREATE TABLE job_listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    company VARCHAR(150) NOT NULL,
    location VARCHAR(150) NOT NULL,
    job_type ENUM('full-time', 'part-time', 'contract', 'remote') DEFAULT 'full-time',
    salary_range VARCHAR(100),
    description TEXT NOT NULL,
    requirements TEXT,
    benefits TEXT,
    posted_by INT NOT NULL,
    posted_by_name VARCHAR(200),
    status ENUM('active', 'closed', 'draft') DEFAULT 'active',
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (posted_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_posted_by (posted_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Job Applications Table
CREATE TABLE job_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    user_id INT NOT NULL,
    cover_letter TEXT,
    resume_path VARCHAR(255),
    status ENUM('pending', 'reviewed', 'accepted', 'rejected') DEFAULT 'pending',
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (job_id) REFERENCES job_listings(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_application (job_id, user_id),
    INDEX idx_user_id (user_id),
    INDEX idx_job_id (job_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Saved Jobs Table
CREATE TABLE saved_jobs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    job_id INT NOT NULL,
    saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (job_id) REFERENCES job_listings(id) ON DELETE CASCADE,
    UNIQUE KEY unique_saved (user_id, job_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Events Table
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME,
    location VARCHAR(200),
    event_type ENUM('virtual', 'physical', 'hybrid') DEFAULT 'physical',
    max_attendees INT,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_event_date (event_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Event Registrations Table
CREATE TABLE event_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_registration (event_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Profile Views Table
CREATE TABLE profile_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile_user_id INT NOT NULL,
    viewer_user_id INT,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (profile_user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_profile_user (profile_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Activity Log Table
CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    activity_type ENUM('application', 'profile_view', 'job_post', 'event_registration') NOT NULL,
    activity_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Default Admin User (password: admin123)
INSERT INTO users (first_name, last_name, email, password, user_type) 
VALUES ('Admin', 'User', 'admin@alumni.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert Sample Jobs
INSERT INTO job_listings (title, company, location, job_type, salary_range, description, requirements, posted_by, posted_by_name) VALUES
('Senior Software Engineer', 'TechVision Solutions', 'San Francisco, CA', 'full-time', '$120,000 - $160,000', 'We are seeking an experienced Senior Software Engineer to join our growing team.', 'Bachelor degree in Computer Science\n5+ years experience\nProficiency in React, Node.js', 1, 'Admin User, Class of 2015'),
('Marketing Manager', 'BrandCraft Agency', 'New York, NY', 'full-time', '$85,000 - $110,000', 'Join our dynamic marketing team to develop strategic campaigns.', 'Marketing degree\n3+ years experience\nDigital marketing expertise', 1, 'Admin User, Class of 2018'),
('Product Designer', 'Creative Labs Inc', 'Remote', 'remote', '$90,000 - $120,000', 'Design intuitive user experiences for our SaaS products.', '4+ years design experience\nFigma proficiency\nStrong portfolio', 1, 'Admin User, Class of 2017');

-- Insert Sample Events
INSERT INTO events (title, description, event_date, event_time, location, event_type, created_by) VALUES
('Tech Career Fair 2024', 'Annual technology career networking event', '2024-12-20', '10:00:00', 'Virtual', 'virtual', 1),
('Alumni Networking Mixer', 'Connect with fellow alumni in your area', '2024-12-25', '18:00:00', 'San Francisco, CA', 'physical', 1),
('Resume Workshop', 'Learn to craft the perfect resume', '2025-01-05', '14:00:00', 'Virtual', 'virtual', 1);