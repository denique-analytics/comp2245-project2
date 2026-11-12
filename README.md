# COMP2245 Project 2

## Fix-It Desk Help Desk Application

A web-based help desk application that allows users to create, manage, assign, and track support tickets.

## Group Members

Denique Whyte – deniquewhyte15@gmail.com

Janae Hill – janaethill09@gmail.com

Jean-Marc De Gannes – soldierdegannes10@gmail.com

## Group Contributions

**Member 1 – Denique Whyte**

- Coordinate the group and GitHub repository.
- Lead database design and create schema.sql.
- Implement login and logout functionality.
- Implement PHP session handling.
- Create shared authentication and security helpers.
- Coordinate integration and final submission.

**Member 2 – Janae Hill**

- Create the ticket listing page (index.php).
- Display tickets with the correct user information.
- Implement All, Open and My Tickets filters.
- Create the new ticket form.
- Implement ticket editing.
- Validate ticket information in PHP.
- Implement ticket-list AJAX functionality.

**Member 3 – Jean-Marc De Gannes**

- Create the individual ticket details page (ticket.php).
- Display ticket information and notes.
- Implement adding notes using AJAX.
- Implement ticket status changes using AJAX.
- Implement Assign to Me functionality.
- Implement ticket deletion with the required permissions.
- Create the admin-only users page and add-user functionality.

## Shared Responsibilities

All three members will work together on:

- Integrating their individual features.
- Maintaining consistent styling and layout.
- Testing the application.
- Preparing the final submission.

## Main Features

- User login and logout.
- Ticket creation and management.
- Ticket assignment and status updates.
- Ticket notes and comments.
- Administrator user management.
- Role-based access and security.
- AJAX functionality for ticket operations.

## Database Setup

1. Install and start MySQL and PHP.
2. Open PowerShell in the project folder.
3. Run the database setup script:

   ```powershell
   Get-Content .\schema.sql | mysql -u root -p
   ```

4. Copy config.example.php to config.php.
5. Update the database connection details in config.php.

## Running the Application

From the project folder, run:

```powershell
php -S localhost:8000
```

Open http://localhost:8000 in your browser.

## Sample Login Accounts

**Administrator**

- Email: admin@fixit.test
- Password: password123

**Members**

- Email: john@fixit.test
- Email: mary@fixit.test
- Password for both: password123

## Technologies Used

- HTML
- CSS
- JavaScript
- PHP
- MySQL
- AJAX
- Git and GitHub