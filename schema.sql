-- COMP2245 Project 2: Fix-It Desk
-- Complete database setup and starting data

-- 1. Recreate the database
DROP DATABASE IF EXISTS helpdesk;

CREATE DATABASE helpdesk
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE helpdesk;


-- 2. Create the users table
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'member') NOT NULL DEFAULT 'member',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- 3. Create the tickets table
CREATE TABLE tickets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('Low', 'Medium', 'High') NOT NULL DEFAULT 'Medium',
    status ENUM('Open', 'In progress', 'Closed') NOT NULL DEFAULT 'Open',
    created_by INT UNSIGNED NOT NULL,
    assigned_to INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (created_by) REFERENCES users(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id)
        ON DELETE SET NULL
);


-- 4. Create the notes table
CREATE TABLE notes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    created_by INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (ticket_id) REFERENCES tickets(id)
        ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
);


-- 5. Insert the administrator and two members
INSERT INTO users (firstname, lastname, email, password, role)
VALUES
(
    'Admin',
    'User',
    'admin@fixit.test',
    '$2y$12$885UotL0C3Aq3T0U9Jgch.H/U9IHdJHTQPO1gIfiY1tGUrtunN0L.',
    'admin'
),
(
    'John',
    'Brown',
    'john@fixit.test',
    '$2y$12$885UotL0C3Aq3T0U9Jgch.H/U9IHdJHTQPO1gIfiY1tGUrtunN0L.',
    'member'
),
(
    'Mary',
    'Smith',
    'mary@fixit.test',
    '$2y$12$885UotL0C3Aq3T0U9Jgch.H/U9IHdJHTQPO1gIfiY1tGUrtunN0L.',
    'member'
);


-- 6. Insert five sample tickets
INSERT INTO tickets
(title, description, priority, status, created_by, assigned_to)
VALUES
(
    'Computer not starting',
    'The office computer will not turn on.',
    'High',
    'Open',
    2,
    3
),
(
    'Printer not working',
    'The printer is showing an error when printing.',
    'Medium',
    'In progress',
    3,
    2
),
(
    'Forgotten password',
    'I cannot remember my account password.',
    'Low',
    'Closed',
    2,
    2
),
(
    'Internet connection problem',
    'The internet connection keeps disconnecting.',
    'High',
    'Open',
    3,
    NULL
),
(
    'Software installation request',
    'I need help installing the required office software.',
    'Medium',
    'In progress',
    2,
    3
);


-- 7. Insert sample notes
INSERT INTO notes (ticket_id, body, created_by)
VALUES
(
    1,
    'The computer was checked but still will not start.',
    3
),
(
    1,
    'A replacement power cable will be tested.',
    2
),
(
    2,
    'The printer driver needs to be updated.',
    2
),
(
    3,
    'The password has been successfully reset.',
    3
);


-- 8. Create the application database user
CREATE USER IF NOT EXISTS 'comp2245'@'localhost'
IDENTIFIED BY 'comp2245-demo';

ALTER USER 'comp2245'@'localhost'
IDENTIFIED BY 'comp2245-demo';

GRANT SELECT, INSERT, UPDATE, DELETE
ON helpdesk.*
TO 'comp2245'@'localhost';
