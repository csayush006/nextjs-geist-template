<?php
// db.php
// Database connection handler for College Student Activity Monitoring System

require_once 'config.php';

/**
 * Get database connection using PDO
 * @return PDO Database connection instance
 */
function getDBConnection() {
    static $pdo = null;
    
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            die("Database connection failed. Please check your configuration.");
        }
    }
    
    return $pdo;
}

/**
 * Execute a prepared statement with parameters
 * @param string $sql SQL query
 * @param array $params Parameters for the query
 * @return PDOStatement
 */
function executeQuery($sql, $params = []) {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Query execution error: " . $e->getMessage());
        throw new Exception("Database query failed.");
    }
}

/**
 * Get all students with their latest activity summary
 * @return array Array of student records
 */
function getStudentsWithActivities() {
    $sql = "SELECT 
                s.id, 
                s.name, 
                s.email,
                s.github_username, 
                s.leetcode_username, 
                s.linkedin_profile,
                (SELECT COUNT(*) FROM activities WHERE student_id = s.id AND source = 'GitHub' AND fetched_at > DATE_SUB(NOW(), INTERVAL 7 DAY)) as github_activities,
                (SELECT COUNT(*) FROM activities WHERE student_id = s.id AND source = 'LeetCode' AND fetched_at > DATE_SUB(NOW(), INTERVAL 7 DAY)) as leetcode_activities,
                (SELECT COUNT(*) FROM activities WHERE student_id = s.id AND source = 'LinkedIn' AND fetched_at > DATE_SUB(NOW(), INTERVAL 7 DAY)) as linkedin_activities,
                (SELECT fetched_at FROM activities WHERE student_id = s.id ORDER BY fetched_at DESC LIMIT 1) as last_updated
            FROM students s 
            ORDER BY s.name";
    
    $stmt = executeQuery($sql);
    return $stmt->fetchAll();
}

/**
 * Get detailed activities for a specific student
 * @param int $studentId Student ID
 * @param int $limit Number of activities to fetch
 * @return array Array of activity records
 */
function getStudentActivities($studentId, $limit = 50) {
    $sql = "SELECT 
                source, 
                activity_data, 
                activity_type, 
                activity_date, 
                fetched_at 
            FROM activities 
            WHERE student_id = ? 
            ORDER BY fetched_at DESC 
            LIMIT ?";
    
    $stmt = executeQuery($sql, [$studentId, $limit]);
    return $stmt->fetchAll();
}

/**
 * Insert new activity record
 * @param int $studentId Student ID
 * @param string $source Activity source (GitHub, LeetCode, LinkedIn)
 * @param string $activityData Activity data in JSON format
 * @param string $activityType Type of activity
 * @param string $activityDate Date of the activity
 * @return bool Success status
 */
function insertActivity($studentId, $source, $activityData, $activityType = null, $activityDate = null) {
    $sql = "INSERT INTO activities (student_id, source, activity_data, activity_type, activity_date) 
            VALUES (?, ?, ?, ?, ?)";
    
    try {
        executeQuery($sql, [$studentId, $source, $activityData, $activityType, $activityDate]);
        return true;
    } catch (Exception $e) {
        error_log("Failed to insert activity: " . $e->getMessage());
        return false;
    }
}

/**
 * Validate admin credentials
 * @param string $username Admin username
 * @param string $password Plain text password
 * @return array|false Admin data or false if invalid
 */
function validateAdmin($username, $password) {
    $sql = "SELECT id, username, password_hash FROM admin WHERE username = ?";
    $stmt = executeQuery($sql, [$username]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password_hash'])) {
        return $admin;
    }
    
    return false;
}

?>
