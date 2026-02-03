# RL Admin - Improvement Suggestions

This document outlines recommended improvements for the `rl_admin` PHP admin panel project, organized by priority and category.

---

## 1. Critical Security Issues

### 1.1 SQL Injection Vulnerabilities
**Location:** `add_user_post.php`, `core/db.php`, and multiple files

**Current Issue:**
```php
// add_user_post.php line 28
mysqli_query($con, "INSERT INTO users (email, password, salt, name, tlf, stilling, image)
VALUES ('$email', '$nypass', '$salt', '$name', '$tlf', '$stilling', 'user.png')");
```

**Recommendation:**
- Use prepared statements consistently throughout the application
- The `DB.php` class already uses PDO prepared statements - use it everywhere instead of mixing `mysqli_*` functions
- Remove all raw SQL string concatenation

```php
// Recommended approach
$db = DB::getInstance();
$db->insert('users', [
    'email' => $email,
    'password' => $nypass,
    'salt' => $salt,
    'name' => $name,
    'tlf' => $tlf,
    'stilling' => $stilling,
    'image' => 'user.png'
]);
```

### 1.2 Hardcoded Database Credentials
**Location:** `core/init.php`, `api/config.php`

**Current Issue:**
```php
'mysql' => array(
    'host'     => 'localhost',
    'username' => 'root',
    'password' => 'root',
    'db'       => 'radio'
),
```

**Recommendation:**
- Use environment variables (`.env` file) for sensitive configuration
- Add `.env.example` to version control, not the actual `.env`
- Use a library like `vlucas/phpdotenv`

```php
// Example .env file
DB_HOST=localhost
DB_USER=your_user
DB_PASS=your_secure_password
DB_NAME=radio

// Example usage
$config = [
    'mysql' => [
        'host'     => $_ENV['DB_HOST'],
        'username' => $_ENV['DB_USER'],
        'password' => $_ENV['DB_PASS'],
        'db'       => $_ENV['DB_NAME']
    ]
];
```

### 1.3 Weak Password Hashing
**Location:** `classes/Hash.php`, `classes/User.php`

**Current Issue:** Using SHA-256 with salt (deprecated approach)

**Recommendation:**
- Use PHP's built-in `password_hash()` and `password_verify()` functions
- These use bcrypt by default and handle salting automatically

```php
// Creating password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Verifying password
if (password_verify($inputPassword, $storedHash)) {
    // Login successful
}
```

### 1.4 API Authentication Missing
**Location:** `api/tasks.php`, `api/columns.php`, etc.

**Current Issue:** API endpoints have no authentication - anyone can access them.

**Recommendation:**
- Implement API authentication (JWT tokens or session-based)
- Add CSRF protection for state-changing operations
- Remove or restrict `Access-Control-Allow-Origin: *`

### 1.5 Display Errors in Production
**Location:** `core/init.php`

**Current Issue:**
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

**Recommendation:**
- Never display errors in production
- Log errors to a file instead
- Add environment detection

```php
if ($_ENV['APP_ENV'] === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', '/path/to/error.log');
}
```

---

## 2. Code Architecture Improvements

### 2.1 Inconsistent Database Access
**Current Issue:** Mixing PDO (`DB.php`) with mysqli (`core/db.php`, `db_con.php`)

**Recommendation:**
- Standardize on a single database abstraction
- Remove duplicate database connection files
- Use PDO exclusively (better security, more features)

### 2.2 Duplicate/Redundant Files
**Files to consolidate:**
- `core/db.php`, `core/db_.php`, `core/db_con.php`, `core/db_con_live.php`, `core/db_config.php`, `core/db_live.php`
- `classes/Seatmap kopi.php`, `classes/Seatmap.php`, `classes/Seatmap__.php`
- `api/config.php` and `api/kanban/config.php`

**Recommendation:**
- Create a single configuration system
- Use environment variables to switch between development/production

### 2.3 Add Autoloading with Composer
**Recommendation:**
- Use Composer for autoloading and dependency management
- Migrate from manual `require_once` statements

```json
// composer.json
{
    "autoload": {
        "psr-4": {
            "RlAdmin\\": "classes/"
        }
    },
    "require": {
        "vlucas/phpdotenv": "^5.0"
    }
}
```

### 2.4 Implement MVC Pattern Properly
**Current Issue:** Views mixed with business logic in PHP files

**Recommendation:**
- Separate views into a `views/` directory
- Create controllers in a `controllers/` directory
- Keep models (classes) in `models/` directory

---

## 3. Code Quality Improvements

### 3.1 Add Input Validation
**Location:** Multiple files

**Recommendation:**
- Expand the `Validate` class with more validation rules
- Validate all user inputs before processing
- Add email format validation, phone number validation, etc.

### 3.2 Fix XSS Vulnerabilities
**Current Issue:** Not all output is escaped

**Recommendation:**
- Use `htmlspecialchars()` or the `escape()` function for ALL output
- Consider using a templating engine like Twig that auto-escapes

```php
// Current (in some places)
echo $user->data()->name;

// Should be
echo escape($user->data()->name);
```

### 3.3 Add CSRF Protection
**Recommendation:**
- Generate and validate CSRF tokens for all forms
- The `Token` class exists but doesn't seem to be used consistently

```php
// In form
<input type="hidden" name="token" value="<?php echo Token::generate(); ?>">

// In processing
if (!Token::check(Input::get('token'))) {
    die('Invalid CSRF token');
}
```

### 3.4 Remove .DS_Store Files
**Location:** Multiple directories

**Recommendation:**
- Add `.DS_Store` to `.gitignore`
- Remove existing `.DS_Store` files from repository

```bash
# Add to .gitignore
.DS_Store
*/.DS_Store

# Remove from repo
git rm --cached **/.DS_Store
```

---

## 4. UI/UX Improvements

### 4.1 Fix Broken Image Paths
**Location:** `dashboard.php`, `nav.php`

**Current Issue:**
```php
<img src="../img/user/user.png" ...>
```
This path goes outside the project directory.

**Recommendation:**
- Use consistent paths like `assets/images/user.png`
- Create a helper function for asset URLs

### 4.2 Add Proper Error Handling in UI
**Recommendation:**
- Show user-friendly error messages
- Add loading states for AJAX operations
- Implement proper form validation feedback

### 4.3 Improve Accessibility
**Recommendation:**
- Add proper ARIA labels
- Ensure keyboard navigation works
- Add alt text to all images
- Ensure proper color contrast

### 4.4 Mobile Responsiveness
**Current:** Has `responsive.css` but may need testing

**Recommendation:**
- Test on various device sizes
- Ensure sidebar navigation works well on mobile
- Test data tables on small screens

---

## 5. Performance Improvements

### 5.1 Database Query Optimization
**Location:** `dashboard.php` and others

**Current Issue:** Multiple separate queries in loops

**Recommendation:**
- Use JOINs instead of multiple queries
- Implement query caching
- Add database indexes for frequently queried columns

### 5.2 Asset Optimization
**Recommendation:**
- Minify CSS and JavaScript files
- Combine multiple CSS/JS files to reduce HTTP requests
- Use a build tool like Webpack or Vite
- Implement browser caching headers

### 5.3 Remove Unused Assets
**Location:** `assets/plugins/tcpdf/examples/`, many unused font files

**Recommendation:**
- Remove example files from TCPDF
- Only include fonts that are actually used
- This will significantly reduce repository size

---

## 6. Development Workflow

### 6.1 Add README.md
**Recommendation:**
Create a README with:
- Project description
- Installation instructions
- Configuration steps
- Database setup
- Development guidelines

### 6.2 Add .gitignore
**Create `.gitignore` with:**
```
.env
.DS_Store
*.log
vendor/
node_modules/
```

### 6.3 Add Database Migrations
**Recommendation:**
- Create SQL migration files for database schema
- Include a setup script for new developers
- Document the database structure

### 6.4 Add Code Standards
**Recommendation:**
- Use PSR-12 coding standards
- Add a linter configuration (PHP_CodeSniffer)
- Consider adding pre-commit hooks

---

## 7. Feature Improvements

### 7.1 Implement Proper Logout
**Location:** `nav.php`

**Current Issue:** Logout link does nothing
```php
<a ... href="javascript:;">Logout</a>
```

**Recommendation:**
- Create a proper logout endpoint
- The `User::logout()` method exists - use it

### 7.2 Add User Roles and Permissions
**Current:** `hasPermission()` exists but permissions system seems incomplete

**Recommendation:**
- Complete the role-based access control system
- Add middleware for route protection
- Create an admin UI for managing permissions

### 7.3 Add Activity Logging
**Recommendation:**
- Log user actions (login, create, update, delete)
- Create an audit trail table
- Add admin interface to view logs

### 7.4 Implement Search Functionality
**Location:** `dashboard.php`

**Current Issue:** Search input exists but doesn't work

**Recommendation:**
- Implement actual search functionality
- Add AJAX-based search for better UX

---

## Summary: Priority Action Items

### Immediate (Security Critical)
1. Fix SQL injection vulnerabilities
2. Move credentials to environment variables
3. Upgrade password hashing to `password_hash()`
4. Add API authentication
5. Disable error display in production

### Short-term (Code Quality)
1. Consolidate database access methods
2. Add CSRF protection to all forms
3. Fix XSS vulnerabilities
4. Remove duplicate files
5. Fix broken image paths

### Medium-term (Architecture)
1. Add Composer for autoloading
2. Implement proper MVC structure
3. Add database migrations
4. Create documentation

### Long-term (Features)
1. Complete role-based permissions
2. Add activity logging
3. Implement search functionality
4. Performance optimization

---

*This document was generated as part of a code review. Implementing these changes will significantly improve the security, maintainability, and quality of the application.*
