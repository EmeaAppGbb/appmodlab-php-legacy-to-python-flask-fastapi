-- CityPulse Events Database Schema (MySQL 5.7)

CREATE DATABASE IF NOT EXISTS citypulse_events;
USE citypulse_events;

-- Users table with MD5 password hashing (insecure!)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(32) NOT NULL,  -- MD5 hash is only 32 characters
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role ENUM('user', 'organizer', 'admin') DEFAULT 'user',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Venues table
CREATE TABLE venues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL,
    city VARCHAR(50) NOT NULL,
    capacity INT NOT NULL,
    amenities TEXT,
    contact_email VARCHAR(100),
    latitude DECIMAL(10, 8),
    longitude DECIMAL(11, 8)
) ENGINE=InnoDB;

-- Categories table
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) UNIQUE NOT NULL,
    icon VARCHAR(10)
) ENGINE=InnoDB;

-- Organizers table
CREATE TABLE organizers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    company_name VARCHAR(100) NOT NULL,
    description TEXT,
    website VARCHAR(255),
    verified TINYINT(1) DEFAULT 0,
    commission_rate DECIMAL(5, 2) DEFAULT 10.00,
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- Events table
CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    venue_id INT,
    organizer_id INT NOT NULL,
    event_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    category INT,
    max_capacity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    status ENUM('active', 'cancelled', 'completed') DEFAULT 'active',
    image_path VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (venue_id) REFERENCES venues(id),
    FOREIGN KEY (organizer_id) REFERENCES organizers(id),
    FOREIGN KEY (category) REFERENCES categories(id)
) ENGINE=InnoDB;

-- Tickets table
CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    ticket_type ENUM('general', 'vip') DEFAULT 'general',
    price DECIMAL(10, 2) NOT NULL,
    purchase_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    paypal_txn_id VARCHAR(100),
    qr_code VARCHAR(100) UNIQUE,
    FOREIGN KEY (event_id) REFERENCES events(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- Reviews table
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- Insert demo data

-- Demo users (passwords: admin123, pass123)
INSERT INTO users (username, email, password, name, phone, role) VALUES
('admin', 'admin@citypulse.com', '0192023a7bbd73250516f069df18b500', 'Admin User', '555-0001', 'admin'),
('organizer1', 'organizer1@events.com', '4e6419f9c9d70b3e71071bc2c2e48b04', 'Event Masters Inc', '555-0002', 'organizer'),
('organizer2', 'organizer2@events.com', '4e6419f9c9d70b3e71071bc2c2e48b04', 'City Events Co', '555-0003', 'organizer'),
('user1', 'user1@example.com', '4e6419f9c9d70b3e71071bc2c2e48b04', 'John Smith', '555-0004', 'user'),
('user2', 'user2@example.com', '4e6419f9c9d70b3e71071bc2c2e48b04', 'Jane Doe', '555-0005', 'user'),
('user3', 'user3@example.com', '4e6419f9c9d70b3e71071bc2c2e48b04', 'Bob Johnson', '555-0006', 'user');

-- Categories
INSERT INTO categories (name, slug, icon) VALUES
('Music', 'music', '🎵'),
('Sports', 'sports', '⚽'),
('Arts & Culture', 'arts-culture', '🎨'),
('Food & Drink', 'food-drink', '🍴'),
('Technology', 'technology', '💻'),
('Business', 'business', '💼'),
('Health & Wellness', 'health-wellness', '🧘'),
('Education', 'education', '📚');

-- Venues
INSERT INTO venues (name, address, city, capacity, amenities, contact_email, latitude, longitude) VALUES
('Grand Concert Hall', '123 Main Street', 'New York', 2000, 'Parking, Bar, Wheelchair Access', 'info@grandconcerthall.com', 40.7128, -74.0060),
('City Sports Arena', '456 Arena Blvd', 'Los Angeles', 5000, 'Parking, Food Court, VIP Boxes', 'contact@citysportsarena.com', 34.0522, -118.2437),
('Downtown Theater', '789 Theater Ave', 'Chicago', 500, 'Bar, Coat Check', 'hello@downtowntheater.com', 41.8781, -87.6298),
('Tech Hub Conference Center', '321 Innovation Dr', 'San Francisco', 1000, 'WiFi, Catering, A/V Equipment', 'events@techhub.com', 37.7749, -122.4194),
('Riverside Amphitheater', '654 River Rd', 'Austin', 3000, 'Outdoor Seating, Bar, Parking', 'booking@riverside.com', 30.2672, -97.7431),
('Metropolitan Convention Center', '987 Convention Way', 'Seattle', 10000, 'Multiple Halls, Catering, Parking', 'info@metroconvention.com', 47.6062, -122.3321),
('Art Gallery Loft', '147 Gallery St', 'Portland', 200, 'Gallery Space, Wine Bar', 'contact@artgalleryloft.com', 45.5051, -122.6750),
('Community Center', '258 Community Ln', 'Denver', 800, 'Kitchen, Parking, AV Equipment', 'info@communitycenter.com', 39.7392, -104.9903);

-- Organizers
INSERT INTO organizers (user_id, company_name, description, website, verified, commission_rate) VALUES
(2, 'Event Masters Inc', 'Premier event organization company specializing in large-scale concerts and festivals', 'https://eventmasters.com', 1, 8.00),
(3, 'City Events Co', 'Community-focused event planning for local entertainment', 'https://cityevents.com', 1, 10.00);

-- Events (mix of past, current, and future)
INSERT INTO events (title, description, venue_id, organizer_id, event_date, start_time, end_time, category, max_capacity, price, status, image_path) VALUES
('Summer Music Festival 2024', 'Join us for the biggest music festival of the summer featuring 20+ artists across 3 stages. Food trucks, craft beer, and unforgettable performances!', 5, 1, '2024-07-15', '14:00:00', '23:00:00', 1, 3000, 89.99, 'active', ''),
('Tech Innovation Summit', 'Annual technology conference bringing together industry leaders, startups, and innovators. Keynotes, workshops, and networking opportunities.', 4, 2, '2024-06-20', '09:00:00', '18:00:00', 5, 1000, 299.00, 'active', ''),
('Classical Orchestra Night', 'An evening of classical masterpieces performed by the City Symphony Orchestra. Featuring works by Mozart, Beethoven, and Tchaikovsky.', 1, 1, '2024-05-25', '19:30:00', '22:00:00', 1, 1500, 45.00, 'active', ''),
('Local Art Exhibition Opening', 'Discover emerging artists in our annual art exhibition. Wine and cheese reception included.', 7, 2, '2024-05-18', '18:00:00', '21:00:00', 3, 200, 25.00, 'active', ''),
('City Marathon 2024', 'Run for a cause! Annual city marathon with 5K, 10K, and full marathon options. All proceeds support local charities.', 2, 1, '2024-09-10', '07:00:00', '14:00:00', 2, 5000, 50.00, 'active', ''),
('Food & Wine Festival', 'Taste your way through 50+ local restaurants and wineries. Live music, cooking demos, and culinary workshops.', 6, 2, '2024-08-05', '12:00:00', '20:00:00', 4, 2000, 75.00, 'active', ''),
('Startup Pitch Competition', 'Watch innovative startups pitch to top investors. Networking reception to follow.', 4, 2, '2024-06-10', '15:00:00', '19:00:00', 6, 500, 35.00, 'active', ''),
('Yoga in the Park', 'Outdoor yoga session for all levels. Bring your mat and join us for mindfulness and movement.', 5, 1, '2024-05-30', '08:00:00', '09:30:00', 7, 300, 15.00, 'active', ''),
('Jazz Night Live', 'Smooth jazz performances featuring local and touring musicians. Full bar and small plates available.', 3, 1, '2024-06-01', '20:00:00', '23:30:00', 1, 450, 40.00, 'active', ''),
('Business Leadership Workshop', 'Full-day workshop on leadership strategies for modern businesses. Includes lunch and course materials.', 4, 2, '2024-06-15', '09:00:00', '17:00:00', 6, 200, 199.00, 'active', ''),
('Comedy Show Extravaganza', 'Night of laughs with 5 stand-up comedians. 18+ only.', 3, 1, '2024-05-22', '20:00:00', '22:30:00', 3, 500, 30.00, 'active', ''),
('Film Festival Opening Night', 'Opening night of the annual independent film festival. Red carpet, premiere screening, and after-party.', 3, 2, '2024-07-01', '18:00:00', '23:00:00', 3, 500, 55.00, 'active', ''),
('Rock Legends Tribute Concert', 'Tribute bands perform classic rock hits from the 70s and 80s. Full bar and dancing.', 1, 1, '2024-06-25', '19:00:00', '23:00:00', 1, 2000, 65.00, 'active', ''),
('Career Fair 2024', 'Connect with 100+ employers. Bring your resume and professional attire.', 6, 2, '2024-05-28', '10:00:00', '16:00:00', 8, 3000, 0.00, 'active', ''),
('Halloween Masquerade Ball', 'Dress to impress at our annual masquerade ball. DJ, open bar, costume contest with prizes.', 1, 1, '2024-10-31', '21:00:00', '02:00:00', 3, 1500, 85.00, 'active', ''),
('Winter Wonderland Concert', 'Holiday concert featuring traditional and contemporary holiday music.', 1, 1, '2024-12-15', '19:00:00', '21:30:00', 1, 2000, 50.00, 'active', ''),
('New Year Eve Gala', 'Ring in the new year with champagne, dinner, live music, and fireworks at midnight!', 6, 2, '2024-12-31', '20:00:00', '01:00:00', 3, 1000, 150.00, 'active', ''),
('Spring Garden Tour', 'Guided tour of botanical gardens in peak bloom. Expert commentary included.', 7, 2, '2024-05-20', '10:00:00', '14:00:00', 8, 150, 20.00, 'active', ''),
('Electronic Music Festival', '12 hours of electronic music with international DJs. 21+ only.', 5, 1, '2024-08-20', '18:00:00', '06:00:00', 1, 3000, 95.00, 'active', ''),
('Coding Bootcamp Info Session', 'Learn about our intensive 12-week coding bootcamp. Q&A with instructors and alumni.', 4, 2, '2024-05-16', '18:00:00', '20:00:00', 5, 100, 0.00, 'active', '');

-- Sample tickets (some completed purchases)
INSERT INTO tickets (event_id, user_id, ticket_type, price, payment_status, paypal_txn_id, qr_code) VALUES
(1, 4, 'general', 89.99, 'completed', 'TXN-ABC123', 'QR-FEST001'),
(1, 5, 'vip', 89.99, 'completed', 'TXN-ABC124', 'QR-FEST002'),
(2, 4, 'general', 299.00, 'completed', 'TXN-ABC125', 'QR-TECH001'),
(3, 6, 'general', 45.00, 'completed', 'TXN-ABC126', 'QR-ORCH001'),
(5, 4, 'general', 50.00, 'pending', NULL, 'QR-MARA001'),
(9, 5, 'general', 40.00, 'completed', 'TXN-ABC127', 'QR-JAZZ001'),
(11, 6, 'general', 30.00, 'completed', 'TXN-ABC128', 'QR-COME001');

-- Sample reviews
INSERT INTO reviews (event_id, user_id, rating, comment) VALUES
(3, 4, 5, 'Absolutely incredible performance! The orchestra was flawless and the venue acoustics were perfect.'),
(3, 5, 4, 'Great event, though parking was a bit difficult to find.'),
(9, 5, 5, 'Best jazz night in the city! Intimate venue, great musicians, and wonderful atmosphere.'),
(11, 6, 4, 'Hilarious show! All the comedians were fantastic. Would recommend!');
