# User Management System Platform

A user management system with authentication, task assignment, and an admin panel. Built with PHP and MySQL.

⚠️ Note: This project is still under development. Some features may not be fully implemented.

## 🚀 Features
- ✅ User registration and login
- ✅ Admin panel for user management
- ✅ Task assignment to employees
- ✅ User status verification (banned, paid, active)
- ✅ Reports and statistics

## 📂 Installation

### 1. Clone the Repository
```sh
git clone https://github.com/your-username/your-repo.git
cd your-repo
```

### 2. Set Up the Database
- Create a database in MySQL: `usermanagement`
- Import the SQL file:
```sh
mysql -u root -p usermanagement < database/usermanagement.sql
```

### 3. Configure the Database Connection in `db_config.php`
```php
$conn = new mysqli("localhost", "root", "", "usermanagement");
```

### 4. Run the Project in XAMPP
- Place the project files in `htdocs`
- Start Apache and MySQL in XAMPP
- Open the browser and visit: `http://localhost/usermanagement/`

## 🛠️ Technologies Used
- PHP 8+
- MySQL
- HTML, CSS, JavaScript
- FontAwesome icons

## 📜 License
MIT License © 2025 Pr0ksz

