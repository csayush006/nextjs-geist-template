<?php
// fetchData.php
// Data fetching script for College Student Activity Monitoring System

require_once 'config.php';
require_once 'db.php';

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Set execution time limit for data fetching
set_time_limit(300); // 5 minutes

/**
 * Fetch GitHub data for a given username
 * @param string $githubUsername GitHub username
 * @return array|null Parsed GitHub data or null on failure
 */
function fetchGitHubData($githubUsername) {
    if (empty($githubUsername)) return null;
    
    $url = "https://api.github.com/users/" . urlencode($githubUsername) . "/events/public";
    $ch = curl_init($url);
    
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'CollegeMonitorApp/1.0',
        CURLOPT_HTTPHEADER => [
            'Accept: application/vnd.github.v3+json',
            // Add authorization header if token is available
            GITHUB_TOKEN !== 'your_github_token_here' ? 'Authorization: token ' . GITHUB_TOKEN : ''
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_FOLLOWLOCATION => true
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        error_log("GitHub cURL error for {$githubUsername}: " . $error);
        return null;
    }
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            // Process and return relevant activity data
            $activities = [];
            foreach (array_slice($data, 0, 10) as $event) { // Get last 10 events
                $activities[] = [
                    'type' => $event['type'] ?? 'Unknown',
                    'repo' => $event['repo']['name'] ?? 'Unknown',
                    'date' => $event['created_at'] ?? date('Y-m-d H:i:s'),
                    'public' => $event['public'] ?? true
                ];
            }
            return $activities;
        }
    } elseif ($httpCode === 404) {
        error_log("GitHub user not found: {$githubUsername}");
        return [];
    } elseif ($httpCode === 403) {
        error_log("GitHub API rate limit exceeded for {$githubUsername}");
        return null;
    } else {
        error_log("GitHub API error for {$githubUsername}: HTTP {$httpCode}");
        return null;
    }
    
    return null;
}

/**
 * Fetch LeetCode data for a given username (placeholder implementation)
 * @param string $leetcodeUsername LeetCode username
 * @return array|null Parsed LeetCode data or null on failure
 */
function fetchLeetCodeData($leetcodeUsername) {
    if (empty($leetcodeUsername)) return null;
    
    // Placeholder implementation - LeetCode doesn't have a public API
    // This would typically involve web scraping or using unofficial APIs
    
    // For now, return mock data structure
    $mockActivities = [
        [
            'type' => 'Problem Solved',
            'problem' => 'Two Sum',
            'difficulty' => 'Easy',
            'date' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 7) . ' days')),
            'status' => 'Accepted'
        ],
        [
            'type' => 'Problem Solved',
            'problem' => 'Add Two Numbers',
            'difficulty' => 'Medium',
            'date' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 14) . ' days')),
            'status' => 'Accepted'
        ]
    ];
    
    // Log that this is mock data
    error_log("LeetCode data fetch for {$leetcodeUsername}: Using mock data (API not implemented)");
    
    return $mockActivities;
}

/**
 * Fetch LinkedIn data for a given profile (placeholder implementation)
 * @param string $linkedinProfile LinkedIn profile URL
 * @return array|null Parsed LinkedIn data or null on failure
 */
function fetchLinkedInData($linkedinProfile) {
    if (empty($linkedinProfile)) return null;
    
    // Placeholder implementation - LinkedIn has strict anti-scraping policies
    // This would require official LinkedIn API access
    
    // For now, return mock data structure
    $mockActivities = [
        [
            'type' => 'Post',
            'content' => 'Shared an article about web development',
            'date' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 5) . ' days')),
            'engagement' => rand(5, 50)
        ],
        [
            'type' => 'Connection',
            'content' => 'Connected with industry professionals',
            'date' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 10) . ' days')),
            'count' => rand(1, 5)
        ]
    ];
    
    // Log that this is mock data
    error_log("LinkedIn data fetch for {$linkedinProfile}: Using mock data (API not implemented)");
    
    return $mockActivities;
}

/**
 * Process and store activity data
 * @param int $studentId Student ID
 * @param string $source Data source (GitHub, LeetCode, LinkedIn)
 * @param array $activities Array of activities
 * @return int Number of activities stored
 */
function storeActivities($studentId, $source, $activities) {
    if (empty($activities)) return 0;
    
    $stored = 0;
    foreach ($activities as $activity) {
        $activityData = json_encode($activity);
        $activityType = $activity['type'] ?? 'Unknown';
        $activityDate = isset($activity['date']) ? date('Y-m-d', strtotime($activity['date'])) : date('Y-m-d');
        
        if (insertActivity($studentId, $source, $activityData, $activityType, $activityDate)) {
            $stored++;
        }
    }
    
    return $stored;
}

// Main data fetching logic
try {
    $pdo = getDBConnection();
    $stmt = $pdo->query("SELECT * FROM students ORDER BY name");
    $students = $stmt->fetchAll();
    
    $totalFetched = 0;
    $errors = [];
    
    foreach ($students as $student) {
        $studentActivities = 0;
        
        // Fetch GitHub data
        if (!empty($student['github_username'])) {
            $githubData = fetchGitHubData($student['github_username']);
            if ($githubData !== null) {
                $stored = storeActivities($student['id'], 'GitHub', $githubData);
                $studentActivities += $stored;
                error_log("Stored {$stored} GitHub activities for {$student['name']}");
            } else {
                $errors[] = "Failed to fetch GitHub data for {$student['name']}";
            }
        }
        
        // Fetch LeetCode data
        if (!empty($student['leetcode_username'])) {
            $leetcodeData = fetchLeetCodeData($student['leetcode_username']);
            if ($leetcodeData !== null) {
                $stored = storeActivities($student['id'], 'LeetCode', $leetcodeData);
                $studentActivities += $stored;
                error_log("Stored {$stored} LeetCode activities for {$student['name']}");
            } else {
                $errors[] = "Failed to fetch LeetCode data for {$student['name']}";
            }
        }
        
        // Fetch LinkedIn data
        if (!empty($student['linkedin_profile'])) {
            $linkedinData = fetchLinkedInData($student['linkedin_profile']);
            if ($linkedinData !== null) {
                $stored = storeActivities($student['id'], 'LinkedIn', $linkedinData);
                $studentActivities += $stored;
                error_log("Stored {$stored} LinkedIn activities for {$student['name']}");
            } else {
                $errors[] = "Failed to fetch LinkedIn data for {$student['name']}";
            }
        }
        
        $totalFetched += $studentActivities;
        
        // Add a small delay to avoid overwhelming APIs
        usleep(500000); // 0.5 seconds
    }
    
    // Set success message
    if ($totalFetched > 0) {
        $_SESSION['success_message'] = "Successfully fetched {$totalFetched} activities from " . count($students) . " students.";
    } else {
        $_SESSION['error_message'] = "No new activities were fetched. Please check API configurations.";
    }
    
    // Log any errors
    if (!empty($errors)) {
        error_log("Data fetch errors: " . implode('; ', $errors));
        if (!isset($_SESSION['error_message'])) {
            $_SESSION['error_message'] = "Some data could not be fetched. Check logs for details.";
        }
    }
    
} catch (Exception $e) {
    error_log("Data fetch error: " . $e->getMessage());
    $_SESSION['error_message'] = "Data fetch failed: " . $e->getMessage();
}

// Redirect back to dashboard
header("Location: dashboard.php");
exit;
?>
