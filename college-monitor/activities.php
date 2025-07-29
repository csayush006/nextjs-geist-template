<?php
// activities.php
// Activity logs page for College Student Activity Monitoring System

require_once 'config.php';
require_once 'db.php';

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$pageTitle = 'Activities';

// Get filter parameters
$studentFilter = $_GET['student'] ?? '';
$sourceFilter = $_GET['source'] ?? '';
$dateFilter = $_GET['date'] ?? '';
$limit = intval($_GET['limit'] ?? 50);

try {
    // Build query with filters
    $whereConditions = [];
    $params = [];
    
    if (!empty($studentFilter)) {
        $whereConditions[] = "s.id = ?";
        $params[] = $studentFilter;
    }
    
    if (!empty($sourceFilter)) {
        $whereConditions[] = "a.source = ?";
        $params[] = $sourceFilter;
    }
    
    if (!empty($dateFilter)) {
        $whereConditions[] = "DATE(a.fetched_at) = ?";
        $params[] = $dateFilter;
    }
    
    $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
    
    $sql = "SELECT 
                a.id,
                a.source,
                a.activity_data,
                a.activity_type,
                a.activity_date,
                a.fetched_at,
                s.name as student_name,
                s.id as student_id
            FROM activities a
            JOIN students s ON a.student_id = s.id
            {$whereClause}
            ORDER BY a.fetched_at DESC
            LIMIT ?";
    
    $params[] = $limit;
    $stmt = executeQuery($sql, $params);
    $activities = $stmt->fetchAll();
    
    // Get students for filter dropdown
    $studentsStmt = executeQuery("SELECT id, name FROM students ORDER BY name");
    $students = $studentsStmt->fetchAll();
    
} catch (Exception $e) {
    error_log("Activities page error: " . $e->getMessage());
    $activities = [];
    $students = [];
    $_SESSION['error_message'] = "Failed to load activities data.";
}

include 'header.php';
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Activity Logs</h1>
            <p class="mt-1 text-sm text-gray-600">Detailed view of all student learning activities</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="fetchData.php" class="refresh-btn inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Refresh Data
            </a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="bg-white shadow rounded-lg p-6 mb-6">
    <h3 class="text-lg font-medium text-gray-900 mb-4">Filters</h3>
    <form method="GET" action="activities.php" class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
            <label for="student" class="block text-sm font-medium text-gray-700 mb-1">Student</label>
            <select name="student" id="student" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <option value="">All Students</option>
                <?php foreach ($students as $student): ?>
                    <option value="<?php echo $student['id']; ?>" <?php echo $studentFilter == $student['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($student['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div>
            <label for="source" class="block text-sm font-medium text-gray-700 mb-1">Platform</label>
            <select name="source" id="source" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <option value="">All Platforms</option>
                <option value="GitHub" <?php echo $sourceFilter === 'GitHub' ? 'selected' : ''; ?>>GitHub</option>
                <option value="LeetCode" <?php echo $sourceFilter === 'LeetCode' ? 'selected' : ''; ?>>LeetCode</option>
                <option value="LinkedIn" <?php echo $sourceFilter === 'LinkedIn' ? 'selected' : ''; ?>>LinkedIn</option>
            </select>
        </div>
        
        <div>
            <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
            <input type="date" name="date" id="date" value="<?php echo htmlspecialchars($dateFilter); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
        </div>
        
        <div>
            <label for="limit" class="block text-sm font-medium text-gray-700 mb-1">Limit</label>
            <select name="limit" id="limit" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <option value="25" <?php echo $limit === 25 ? 'selected' : ''; ?>>25</option>
                <option value="50" <?php echo $limit === 50 ? 'selected' : ''; ?>>50</option>
                <option value="100" <?php echo $limit === 100 ? 'selected' : ''; ?>>100</option>
                <option value="200" <?php echo $limit === 200 ? 'selected' : ''; ?>>200</option>
            </select>
        </div>
        
        <div class="flex items-end space-x-2">
            <button type="submit" class="px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Apply Filters
            </button>
            <a href="activities.php" class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Clear
            </a>
        </div>
    </form>
</div>

<!-- Activities List -->
<div class="bg-white shadow overflow-hidden sm:rounded-md">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Recent Activities</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">
            Showing <?php echo count($activities); ?> activities
            <?php if (!empty($studentFilter) || !empty($sourceFilter) || !empty($dateFilter)): ?>
                (filtered)
            <?php endif; ?>
        </p>
    </div>
    
    <?php if (empty($activities)): ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No activities found</h3>
            <p class="mt-1 text-sm text-gray-500">
                <?php if (!empty($studentFilter) || !empty($sourceFilter) || !empty($dateFilter)): ?>
                    Try adjusting your filters or refresh the data.
                <?php else: ?>
                    Activities will appear here once data is fetched from student platforms.
                <?php endif; ?>
            </p>
            <div class="mt-6">
                <a href="fetchData.php" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    Fetch Activities
                </a>
            </div>
        </div>
    <?php else: ?>
        <ul class="divide-y divide-gray-200">
            <?php foreach ($activities as $activity): ?>
                <?php 
                $activityData = json_decode($activity['activity_data'], true);
                $isValidJson = json_last_error() === JSON_ERROR_NONE;
                ?>
                <li class="px-6 py-4 hover:bg-gray-50">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <!-- Platform Badge -->
                            <div class="flex-shrink-0">
                                <span class="activity-badge activity-<?php echo strtolower($activity['source']); ?>">
                                    <?php echo htmlspecialchars($activity['source']); ?>
                                </span>
                            </div>
                            
                            <!-- Activity Details -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2">
                                    <p class="text-sm font-medium text-gray-900 truncate">
                                        <?php echo htmlspecialchars($activity['student_name']); ?>
                                    </p>
                                    <?php if ($activity['activity_type']): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <?php echo htmlspecialchars($activity['activity_type']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="mt-1">
                                    <?php if ($isValidJson && is_array($activityData)): ?>
                                        <!-- Structured Activity Data -->
                                        <?php if ($activity['source'] === 'GitHub' && isset($activityData['repo'])): ?>
                                            <p class="text-sm text-gray-600">
                                                <span class="font-medium"><?php echo htmlspecialchars($activityData['type'] ?? 'Activity'); ?></span>
                                                in <span class="font-mono text-xs bg-gray-100 px-1 rounded"><?php echo htmlspecialchars($activityData['repo']); ?></span>
                                            </p>
                                        <?php elseif ($activity['source'] === 'LeetCode' && isset($activityData['problem'])): ?>
                                            <p class="text-sm text-gray-600">
                                                Solved <span class="font-medium"><?php echo htmlspecialchars($activityData['problem']); ?></span>
                                                <?php if (isset($activityData['difficulty'])): ?>
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium 
                                                        <?php echo $activityData['difficulty'] === 'Easy' ? 'bg-green-100 text-green-800' : 
                                                                   ($activityData['difficulty'] === 'Medium' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                                                        <?php echo htmlspecialchars($activityData['difficulty']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </p>
                                        <?php elseif ($activity['source'] === 'LinkedIn' && isset($activityData['content'])): ?>
                                            <p class="text-sm text-gray-600">
                                                <?php echo htmlspecialchars(substr($activityData['content'], 0, 100)); ?>
                                                <?php if (strlen($activityData['content']) > 100): ?>...<?php endif; ?>
                                            </p>
                                        <?php else: ?>
                                            <p class="text-sm text-gray-600">
                                                <?php echo htmlspecialchars(substr($activity['activity_data'], 0, 100)); ?>
                                                <?php if (strlen($activity['activity_data']) > 100): ?>...<?php endif; ?>
                                            </p>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <!-- Raw Activity Data -->
                                        <p class="text-sm text-gray-600">
                                            <?php echo htmlspecialchars(substr($activity['activity_data'], 0, 100)); ?>
                                            <?php if (strlen($activity['activity_data']) > 100): ?>...<?php endif; ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Timestamp -->
                        <div class="flex-shrink-0 text-right">
                            <p class="text-sm text-gray-500">
                                <?php echo date('M j, Y', strtotime($activity['fetched_at'])); ?>
                            </p>
                            <p class="text-xs text-gray-400">
                                <?php echo date('g:i A', strtotime($activity['fetched_at'])); ?>
                            </p>
                        </div>
                    </div>
                    
                    <!-- Expandable Details -->
                    <div class="mt-2">
                        <button onclick="toggleDetails(<?php echo $activity['id']; ?>)" class="text-xs text-primary-600 hover:text-primary-800">
                            View Details
                        </button>
                        <div id="details-<?php echo $activity['id']; ?>" class="hidden mt-2 p-3 bg-gray-50 rounded-md">
                            <pre class="text-xs text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($activity['activity_data']); ?></pre>
                        </div>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>

<!-- Pagination Info -->
<?php if (count($activities) >= $limit): ?>
    <div class="mt-6 text-center">
        <p class="text-sm text-gray-600">
            Showing first <?php echo $limit; ?> activities. 
            <a href="?<?php echo http_build_query(array_merge($_GET, ['limit' => $limit + 50])); ?>" class="text-primary-600 hover:text-primary-800">
                Load more
            </a>
        </p>
    </div>
<?php endif; ?>

<script>
function toggleDetails(activityId) {
    const details = document.getElementById('details-' + activityId);
    details.classList.toggle('hidden');
}

// Auto-submit form when filters change
document.querySelectorAll('#student, #source, #date, #limit').forEach(element => {
    element.addEventListener('change', function() {
        // Optional: Auto-submit on change
        // this.form.submit();
    });
});
</script>

<?php include 'footer.php'; ?>
