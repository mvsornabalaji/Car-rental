# Car Rental System

A comprehensive web-based car rental management system built with PHP, MySQL, and Bootstrap. This system allows users to browse vehicles, make bookings, and provides admin functionality to manage the entire rental operation.

## Features

### For Users (Registered & Guest)
- **User Registration & Login**: Secure user authentication system
- **Vehicle Browsing**: View available vehicles with detailed information
- **Search & Filter**: Search vehicles by brand, model, price range, transmission, and fuel type
- **Vehicle Details**: Detailed view of each vehicle with specifications
- **Booking System**: Make vehicle reservations with date selection
- **Booking History**: View and track booking status
- **Profile Management**: Update personal information and change password
- **Testimonials**: Add and view customer testimonials
- **Contact Form**: Send inquiries to the rental company
- **Responsive Design**: Mobile-friendly interface

### For Administrators
- **Admin Dashboard**: Overview of system statistics and recent activities
- **Vehicle Management**: Add, edit, delete, and manage vehicle inventory
- **Brand Management**: Manage vehicle brands and categories
- **Booking Management**: View, confirm, cancel, and manage all bookings
- **User Management**: View and manage registered users
- **Testimonial Management**: Approve/reject customer testimonials
- **Contact Query Management**: Handle customer inquiries
- **Content Management**: Update website content (About, Terms, Privacy)
- **Subscriber Management**: Manage newsletter subscribers
- **Password Management**: Change admin password

## Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript, jQuery, Bootstrap 5
- **Backend**: PHP 5.5+
- **Database**: MySQL
- **Server**: XAMPP (Apache + MySQL + PHP)

## Installation & Setup

### Prerequisites
- XAMPP server installed and running
- PHP 5.5 or higher
- MySQL 5.6 or higher
- Web browser

### Step 1: Download and Extract
1. Download the project files
2. Extract to your XAMPP htdocs folder: `C:\xampp\htdocs\car-rental\`

### Step 2: Database Setup
1. Start XAMPP Control Panel
2. Start Apache and MySQL services
3. Open phpMyAdmin: `http://localhost/phpmyadmin`
4. Create a new database named `car_rental`
5. Import the database schema from `database/car_rental.sql`

### Step 3: Configuration
1. Open `config/database.php`
2. Update database connection details if needed:
   ```php
   $host = 'localhost';
   $username = 'root';
   $password = '';
   $database = 'car_rental';
   ```

### Step 4: File Permissions
1. Create upload directories:
   ```
   mkdir uploads
   mkdir uploads/vehicles
   mkdir uploads/users
   ```
2. Ensure write permissions for upload directories

### Step 5: Access the Application
1. Open your web browser
2. Navigate to: `http://localhost/car-rental/`

## Default Login Credentials

### Admin Access
- **URL**: `http://localhost/car-rental/admin/`
- **Username**: `admin`
- **Password**: `password`
- **Email**: `admin@carrental.com`

### User Registration
- Users can register through the main website
- No default user accounts are created

## Directory Structure

```
car-rental/
├── admin/                 # Admin panel files
│   ├── login.php         # Admin login
│   ├── dashboard.php     # Admin dashboard
│   ├── vehicles.php      # Vehicle management
│   ├── bookings.php      # Booking management
│   ├── users.php         # User management
│   └── logout.php        # Admin logout
├── assets/               # Static assets
│   ├── css/             # Stylesheets
│   ├── js/              # JavaScript files
│   └── images/          # Images
├── config/              # Configuration files
│   └── database.php     # Database connection
├── database/            # Database files
│   └── car_rental.sql   # Database schema
├── uploads/             # Upload directories
│   ├── vehicles/        # Vehicle images
│   └── users/           # User profile images
├── user/                # User panel files
│   ├── dashboard.php    # User dashboard
│   ├── bookings.php     # User bookings
│   └── profile.php      # User profile
├── ajax/                # AJAX handlers
├── index.php            # Homepage
├── login.php            # User login
├── register.php         # User registration
├── vehicles.php         # Vehicle listing
├── contact.php          # Contact page
├── about.php            # About page
└── logout.php           # User logout
```

## Key Features Explained

### Vehicle Management
- Add new vehicles with images, specifications, and pricing
- Categorize vehicles by brand, transmission, fuel type
- Set availability status (available, rented, maintenance)
- Manage vehicle inventory

### Booking System
- Date-based booking with pickup and return dates
- Automatic total calculation based on daily rates
- Booking status tracking (pending, confirmed, cancelled, completed)
- Admin approval system for bookings

### User Management
- Secure user registration with email validation
- Password hashing for security
- User profile management
- Account status management (active/inactive)

### Admin Dashboard
- Real-time statistics (users, bookings, vehicles, queries)
- Recent activity overview
- Quick action buttons
- Comprehensive management interface

## Security Features

- **Password Hashing**: All passwords are hashed using PHP's password_hash()
- **SQL Injection Prevention**: Prepared statements and input sanitization
- **Session Management**: Secure session handling
- **Input Validation**: Client and server-side validation
- **File Upload Security**: Restricted file types and size limits

## Customization

### Styling
- Modify `assets/css/style.css` for custom styling
- Bootstrap 5 classes for responsive design
- Custom color scheme and branding

### Content
- Update page content through admin panel
- Modify static content in respective PHP files
- Update contact information and business details

### Features
- Add new vehicle categories
- Implement payment gateway integration
- Add email notifications
- Implement SMS notifications
- Add vehicle reviews and ratings

## Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check XAMPP services are running
   - Verify database credentials in `config/database.php`
   - Ensure database exists

2. **Upload Errors**
   - Check directory permissions
   - Verify upload directory exists
   - Check file size limits in PHP configuration

3. **Page Not Found**
   - Ensure files are in correct XAMPP htdocs directory
   - Check Apache configuration
   - Verify file permissions

4. **Login Issues**
   - Clear browser cache and cookies
   - Check session configuration
   - Verify database tables exist

### Performance Optimization

1. **Database Optimization**
   - Add indexes to frequently queried columns
   - Optimize database queries
   - Regular database maintenance

2. **Image Optimization**
   - Compress vehicle images
   - Use appropriate image formats
   - Implement lazy loading

3. **Caching**
   - Implement browser caching
   - Use CDN for static assets
   - Database query caching

## Support

For technical support or feature requests:
- Check the documentation
- Review the code comments
- Contact the development team

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Version History

- **v1.0.0** - Initial release with core features
- Basic vehicle management
- User registration and booking system
- Admin dashboard
- Responsive design

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## Acknowledgments

- Bootstrap for the responsive framework
- Font Awesome for icons
- jQuery for JavaScript functionality
- MySQL for database management
