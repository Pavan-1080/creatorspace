# CreatorSpace — Creative Portfolio Website

CreatorSpace is a PHP and MySQL-based creative portfolio website where visitors can explore projects and administrators can manage portfolio content.

## Features

- User registration with email OTP verification
- Secure login and logout using PHP sessions
- Password hashing using PHP's `password_hash()`
- Role-based access for users and administrators
- User dashboard with project, article, and user statistics
- Admin panel to add and delete projects
- Project search and category filtering
- Chart.js analytics showing projects by category
- Responsive interface using HTML, CSS, and JavaScript
- MySQL database with foreign keys and indexes

## Technologies Used

- HTML5
- CSS3
- JavaScript
- PHP
- MySQL
- PHPMailer
- Chart.js
- XAMPP

## Local Installation

1. Install and start Apache and MySQL through XAMPP.
2. Place the project folder inside `htdocs`.
3. Create a MySQL database named `creatorspace`.
4. Create the required database tables.
5. Configure the database connection in `db.php`.
6. Configure SMTP credentials in `.env.php`.
7. Install PHPMailer dependencies if they are not included.
8. Open `http://localhost/creatorspace/` in your browser.

## Database Tables

- `users` — user accounts, roles, and email verification
- `otp_verifications` — OTP hashes and expiration times
- `projects` — portfolio project details
- `articles` — article content
- `contact_messages` — contact form submissions

## Security

- Passwords are stored as hashes.
- Database queries use prepared statements where applicable.
- Administrator pages check the authenticated user's role.
- Email credentials must not be committed to the public repository.

## Project Status

Developed as a capstone project for the ApexPlanet Full Stack Web Development Internship.

## Author

Pavan Kumar
