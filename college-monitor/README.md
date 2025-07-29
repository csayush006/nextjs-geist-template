# College Student Activity Monitoring System

A web-based PHP application that enables college administrators to monitor students' personal learning activities across GitHub, LeetCode, and LinkedIn platforms without requiring manual student input.

## Features

- **Admin Authentication**: Secure login system for college administrators
- **Student Management**: Add, edit, and manage student profiles with platform connections
- **Activity Monitoring**: Automated fetching of student activities from:
  - GitHub (repositories, commits, contributions)
  - LeetCode (problem solving activities)
  - LinkedIn (professional activities and posts)
- **Dashboard Analytics**: Visual overview of student engagement and activity statistics
- **Responsive Design**: Modern UI built with Tailwind CSS
- **Real-time Data**: Automated data fetching with manual refresh options

## Technology Stack

- **Backend**: PHP (no frameworks)
- **Database**: MySQL
- **Frontend**: HTML, CSS (Tailwind CSS), JavaScript
- **APIs**: GitHub API, LeetCode (web scraping), LinkedIn (web scraping)

## Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- cURL extension enabled
- PDO MySQL extension enabled

### Setup Instructions

1. **Clone/Download the project**
   ```bash
   git clone <repository-url>
   cd college-monitor
   ```

2. **Database Setup**
   - Create a MySQL database named `college_monitor`
   - Import the database schema:
   ```bash
   mysql -u your_username -p college_monitor < setup.sql
   ```

3. **Configuration**
   - Edit `config.php` with your database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'college_monitor');
   define('DB_USER', 'your_db_username');
   define('DB_PASS', 'your_db_password');
   ```

4. **API Configuration** (Optional)
   - Add your GitHub Personal Access Token in `config.php`:
   ```php
   define('GITHUB_TOKEN', 'your_github_token_here');
   ```
   - LinkedIn and LeetCode integrations use web scraping (API tokens not required)

5. **Web Server Setup**
   - Point your web server document root to the project directory
   - Ensure PHP has write permissions for session handling
   - Enable URL rewriting if needed

6. **Access the Application**
   - Navigate to your web server URL
   - Login with default credentials:
     - **Username**: admin
     - **Password**: admin123

## Usage

### Admin Login
1. Access the application through your web browser
2. Use the default admin credentials or create new ones
3. You'll be redirected to the main dashboard

### Managing Students
1. Navigate to the "Students" section
2. Click "Add Student" to add new student profiles
3. Fill in student details including:
   - Full name and email (required)
   - GitHub username
   - LeetCode username
   - LinkedIn profile URL

### Monitoring Activities
1. From the dashboard, click "Refresh Data" to fetch latest activities
2. View activity summaries for each student
3. Click on individual students for detailed activity logs
4. Use search and filter options to find specific students or activities

### Data Fetching
The system automatically fetches data from:
- **GitHub**: Public repositories, commits, issues, pull requests
- **LeetCode**: Problem solving statistics and recent submissions
- **LinkedIn**: Posts, connections, and professional updates

## File Structure

```
college-monitor/
├── config.php          # Configuration settings
├── db.php              # Database connection and functions
├── header.php          # Common header template
├── footer.php          # Common footer template
├── login.php           # Admin login page
├── logout.php          # Logout handler
├── dashboard.php       # Main dashboard
├── students.php        # Student management
├── fetchData.php       # Data fetching script
├── setup.sql           # Database schema
└── README.md           # This file
```

## API Integration

### GitHub API
- Uses GitHub's REST API v3
- Requires Personal Access Token for higher rate limits
- Fetches public events and repository data
- Rate limit: 5,000 requests/hour (authenticated)

### LeetCode Integration
- Uses web scraping (no official API available)
- Fetches problem solving statistics
- Mock data implementation included for development

### LinkedIn Integration
- Uses web scraping (official API has restrictions)
- Fetches professional activity data
- Mock data implementation included for development

## Security Features

- Password hashing using PHP's `password_hash()`
- SQL injection prevention with prepared statements
- Session management with timeout
- Input validation and sanitization
- CSRF protection considerations

## Customization

### Adding New Platforms
1. Create new fetch function in `fetchData.php`
2. Update database schema to include new platform
3. Modify UI components to display new platform data

### Styling Customization
- The application uses Tailwind CSS via CDN
- Modify classes in PHP templates for styling changes
- Custom CSS can be added to `footer.php`

### Database Modifications
- Update `setup.sql` for schema changes
- Modify functions in `db.php` for new queries
- Update forms and validation accordingly

## Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check database credentials in `config.php`
   - Ensure MySQL service is running
   - Verify database exists and user has proper permissions

2. **API Rate Limits**
   - GitHub: Add Personal Access Token
   - Implement caching for frequently accessed data
   - Add delays between API calls

3. **Session Issues**
   - Check PHP session configuration
   - Ensure proper file permissions
   - Verify session timeout settings

4. **Data Not Fetching**
   - Check API credentials and tokens
   - Verify network connectivity
   - Review error logs for specific issues

### Error Logs
- PHP errors are logged to system error log
- Application-specific logs in `fetchData.php`
- Check web server error logs for additional information

## Development

### Adding Features
1. Follow the existing code structure
2. Use prepared statements for database queries
3. Implement proper error handling
4. Add input validation for user data

### Testing
1. Test with sample student data
2. Verify API integrations work correctly
3. Check responsive design on different devices
4. Test session management and security features

## Production Deployment

### Security Checklist
- [ ] Change default admin password
- [ ] Disable PHP error display
- [ ] Enable HTTPS
- [ ] Set secure session cookies
- [ ] Implement rate limiting
- [ ] Regular security updates

### Performance Optimization
- [ ] Enable PHP OPcache
- [ ] Implement database query caching
- [ ] Optimize API call frequency
- [ ] Use CDN for static assets
- [ ] Enable gzip compression

## Support

For issues and questions:
1. Check the troubleshooting section
2. Review error logs
3. Verify configuration settings
4. Test with minimal data set

## License

This project is developed for educational purposes. Please ensure compliance with platform terms of service when scraping data.

## Contributing

1. Fork the repository
2. Create feature branch
3. Make changes with proper testing
4. Submit pull request with detailed description

---

**Note**: This application includes placeholder implementations for LeetCode and LinkedIn data fetching. Update the API integration logic in `fetchData.php` when you have access to proper API credentials or implement actual web scraping solutions.
