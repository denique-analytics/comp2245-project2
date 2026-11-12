
USE helpdesk;

-- Add the administrator and two regular members
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


-- Add five sample tickets
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


-- Add sample notes to tickets
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
