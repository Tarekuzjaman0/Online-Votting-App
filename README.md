# National Voting Platform

A secure and responsive online voting platform where users can participate in national polls and discussions. Built with PHP, MySQL, and modern web technologies.

## Features

- User Authentication System
  - Secure registration and login
  - Password hashing and validation
  - Session management
  - Role-based access control (Admin/User)

- Poll Management
  - Create and manage polls
  - Multiple choice options
  - Poll status control (Draft/Active/Closed)
  - Real-time vote counting
  - Prevention of duplicate voting

- Discussion System
  - Comment on active polls
  - Real-time updates
  - User attribution
  - Moderation capabilities

- Admin Dashboard
  - Create and manage polls
  - Monitor voting statistics
  - Manage user comments
  - View poll results

- Security Features
  - SQL injection prevention
  - XSS protection
  - CSRF protection
  - Secure session handling
  - Input validation and sanitization

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

## Installation

1. Clone the repository to your web server directory:
   ```bash
   git clone https://github.com/yourusername/national-voting-platform.git
   ```

2. Create a MySQL database and import the schema:
   ```bash
   mysql -u your_username -p < database.sql
   ```

3. Configure the database connection:
   - Open `config/database.php`
   - Update the database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'your_username');
     define('DB_PASS', 'your_password');
     define('DB_NAME', 'voting_platform');
     ```

4. Set up the web server:
   - Configure your web server to point to the project directory
   - Ensure PHP has write permissions for session handling
   - Enable mod_rewrite if using Apache

5. Default admin credentials:
   - Email: admin@example.com
   - Password: Admin@123

## Security Considerations

- Change the default admin password immediately after installation
- Use HTTPS for secure data transmission
- Regularly update dependencies
- Monitor server logs for suspicious activity
- Implement rate limiting for login attempts
- Use strong password policies

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, please open an issue in the GitHub repository or contact the development team. 