<?php
// students.php
// Student management page for College Student Activity Monitoring System

require_once 'config.php';
require_once 'db.php';

session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$pageTitle = 'Students';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'add') {
            // Add new student
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $github_username = trim($_POST['github_username'] ?? '');
            $leetcode_username = trim($_POST['leetcode_username'] ?? '');
            $linkedin_profile = trim($_POST['linkedin_profile'] ?? '');
            
            if (empty($name) || empty($email)) {
                throw new Exception("Name and email are required.");
            }
            
            $sql = "INSERT INTO students (name, email, github_username, leetcode_username, linkedin_profile) VALUES (?, ?, ?, ?, ?)";
            executeQuery($sql, [$name, $email, $github_username, $leetcode_username, $linkedin_profile]);
            
            $_SESSION['success_message'] = "Student '{$name}' has been added successfully.";
            
        } elseif ($action === 'edit') {
            // Edit existing student
            $id = intval($_POST['id'] ?? 0);
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $github_username = trim($_POST['github_username'] ?? '');
            $leetcode_username = trim($_POST['leetcode_username'] ?? '');
            $linkedin_profile = trim($_POST['linkedin_profile'] ?? '');
            
            if (empty($name) || empty($email) || $id <= 0) {
                throw new Exception("Invalid student data.");
            }
            
            $sql = "UPDATE students SET name = ?, email = ?, github_username = ?, leetcode_username = ?, linkedin_profile = ? WHERE id = ?";
            executeQuery($sql, [$name, $email, $github_username, $leetcode_username, $linkedin_profile, $id]);
            
            $_SESSION['success_message'] = "Student '{$name}' has been updated successfully.";
            
        } elseif ($action === 'delete') {
            // Delete student
            $id = intval($_POST['id'] ?? 0);
            if ($id <= 0) {
                throw new Exception("Invalid student ID.");
            }
            
            // Get student name for confirmation message
            $stmt = executeQuery("SELECT name FROM students WHERE id = ?", [$id]);
            $student = $stmt->fetch();
            
            if ($student) {
                executeQuery("DELETE FROM students WHERE id = ?", [$id]);
                $_SESSION['success_message'] = "Student '{$student['name']}' has been deleted successfully.";
            } else {
                throw new Exception("Student not found.");
            }
        }
        
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
    
    // Redirect to prevent form resubmission
    header("Location: students.php");
    exit;
}

// Get all students
try {
    $students = getStudentsWithActivities();
} catch (Exception $e) {
    error_log("Students page error: " . $e->getMessage());
    $students = [];
    $_SESSION['error_message'] = "Failed to load students data.";
}

include 'header.php';
?>

<!-- Page Header -->
<div class="mb-8">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Students</h1>
            <p class="mt-1 text-sm text-gray-600">Manage student profiles and platform connections</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Add Student
            </button>
        </div>
    </div>
</div>

<!-- Search and Filter -->
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
    <div class="flex-1 max-w-lg">
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <input type="text" id="table-search" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500" placeholder="Search students...">
        </div>
    </div>
    <div class="mt-3 sm:mt-0 sm:ml-4 flex space-x-2">
        <select id="platform-filter" class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 rounded-md">
            <option value="">All Students</option>
            <option value="github">With GitHub</option>
            <option value="leetcode">With LeetCode</option>
            <option value="linkedin">With LinkedIn</option>
            <option value="incomplete">Incomplete Profiles</option>
        </select>
    </div>
</div>

<!-- Students Table -->
<div class="bg-white shadow overflow-hidden sm:rounded-md">
    <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
        <h3 class="text-lg leading-6 font-medium text-gray-900">Student Directory</h3>
        <p class="mt-1 max-w-2xl text-sm text-gray-500">Manage student profiles and their platform connections</p>
    </div>
    
    <?php if (empty($students)): ?>
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No students found</h3>
            <p class="mt-1 text-sm text-gray-500">Get started by adding your first student.</p>
            <div class="mt-6">
                <button onclick="openAddModal()" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Add Student
                </button>
            </div>
        </div>
    <?php else: ?>
        <div class="overflow-x-auto">
            <table id="data-table" class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                        <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GitHub</th>
                        <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">LeetCode</th>
                        <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">LinkedIn</th>
                        <th class="sortable px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Activities</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($students as $student): ?>
                        <?php 
                        $totalActivities = $student['github_activities'] + $student['leetcode_activities'] + $student['linkedin_activities'];
                        $profileComplete = !empty($student['github_username']) && !empty($student['leetcode_username']) && !empty($student['linkedin_profile']);
                        ?>
                        <tr class="hover:bg-gray-50" data-student-id="<?php echo $student['id']; ?>">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full <?php echo $profileComplete ? 'bg-green-100' : 'bg-yellow-100'; ?> flex items-center justify-center">
                                            <span class="text-sm font-medium <?php echo $profileComplete ? 'text-green-700' : 'text-yellow-700'; ?>">
                                                <?php echo strtoupper(substr($student['name'], 0, 2)); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($student['name']); ?></div>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($student['email']); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($student['github_username']): ?>
                                    <div class="flex items-center">
                                        <span class="activity-badge activity-github mr-2">
                                            <?php echo $student['github_activities']; ?> activities
                                        </span>
                                        <a href="https://github.com/<?php echo urlencode($student['github_username']); ?>" target="_blank" class="text-primary-600 hover:text-primary-900 text-sm">
                                            @<?php echo htmlspecialchars($student['github_username']); ?>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-400 text-sm">Not linked</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($student['leetcode_username']): ?>
                                    <div class="flex items-center">
                                        <span class="activity-badge activity-leetcode mr-2">
                                            <?php echo $student['leetcode_activities']; ?> activities
                                        </span>
                                        <span class="text-sm text-gray-600">
                                            <?php echo htmlspecialchars($student['leetcode_username']); ?>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-400 text-sm">Not linked</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ($student['linkedin_profile']): ?>
                                    <div class="flex items-center">
                                        <span class="activity-badge activity-linkedin mr-2">
                                            <?php echo $student['linkedin_activities']; ?> activities
                                        </span>
                                        <a href="<?php echo htmlspecialchars($student['linkedin_profile']); ?>" target="_blank" class="text-primary-600 hover:text-primary-900 text-sm">
                                            Profile
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-400 text-sm">Not linked</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo $totalActivities; ?></div>
                                <?php if (!$profileComplete): ?>
                                    <div class="text-xs text-yellow-600">Incomplete profile</div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($student)); ?>)" class="text-primary-600 hover:text-primary-900">
                                        Edit
                                    </button>
                                    <button onclick="confirmDelete(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['name']); ?>')" class="text-red-600 hover:text-red-900">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<!-- Add/Edit Student Modal -->
<div id="studentModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Add Student</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="studentForm" method="POST" action="students.php" class="space-y-4">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="id" id="studentId" value="">
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                        <input type="text" id="name" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                        <input type="email" id="email" name="email" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="github_username" class="block text-sm font-medium text-gray-700 mb-1">GitHub Username</label>
                        <input type="text" id="github_username" name="github_username" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="username">
                    </div>
                    
                    <div>
                        <label for="leetcode_username" class="block text-sm font-medium text-gray-700 mb-1">LeetCode Username</label>
                        <input type="text" id="leetcode_username" name="leetcode_username" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="username">
                    </div>
                    
                    <div>
                        <label for="linkedin_profile" class="block text-sm font-medium text-gray-700 mb-1">LinkedIn Profile URL</label>
                        <input type="url" id="linkedin_profile" name="linkedin_profile" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500" placeholder="https://linkedin.com/in/username">
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        <span id="submitText">Add Student</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Confirmation Form -->
<form id="deleteForm" method="POST" action="students.php" style="display: none;">
    <input type="hidden" name="action" value="delete">
    <input type="hidden" name="id" id="deleteId" value="">
</form>

<script>
// Modal functions
function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Student';
    document.getElementById('formAction').value = 'add';
    document.getElementById('submitText').textContent = 'Add Student';
    document.getElementById('studentForm').reset();
    document.getElementById('studentId').value = '';
    document.getElementById('studentModal').classList.remove('hidden');
    document.getElementById('name').focus();
}

function openEditModal(student) {
    document.getElementById('modalTitle').textContent = 'Edit Student';
    document.getElementById('formAction').value = 'edit';
    document.getElementById('submitText').textContent = 'Update Student';
    document.getElementById('studentId').value = student.id;
    document.getElementById('name').value = student.name;
    document.getElementById('email').value = student.email;
    document.getElementById('github_username').value = student.github_username || '';
    document.getElementById('leetcode_username').value = student.leetcode_username || '';
    document.getElementById('linkedin_profile').value = student.linkedin_profile || '';
    document.getElementById('studentModal').classList.remove('hidden');
    document.getElementById('name').focus();
}

function closeModal() {
    document.getElementById('studentModal').classList.add('hidden');
}

function confirmDelete(id, name) {
    if (confirm(`Are you sure you want to delete student "${name}"? This action cannot be undone and will also delete all associated activity data.`)) {
        document.getElementById('deleteId').value = id;
        document.getElementById('deleteForm').submit();
    }
}

// Close modal when clicking outside
document.getElementById('studentModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});

// Enhanced filtering
document.getElementById('platform-filter').addEventListener('change', function() {
    const filter = this.value;
    const rows = document.querySelectorAll('#data-table tbody tr');
    
    rows.forEach(row => {
        let show = true;
        
        if (filter === 'github') {
            show = row.querySelector('td:nth-child(2)').textContent.includes('activities');
        } else if (filter === 'leetcode') {
            show = row.querySelector('td:nth-child(3)').textContent.includes('activities');
        } else if (filter === 'linkedin') {
            show = row.querySelector('td:nth-child(4)').textContent.includes('activities');
        } else if (filter === 'incomplete') {
            show = row.textContent.includes('Incomplete profile');
        }
        
        row.style.display = show ? '' : 'none';
    });
});

// Form validation
document.getElementById('studentForm').addEventListener('submit', function(e) {
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    
    if (!name || !email) {
        e.preventDefault();
        alert('Name and email are required fields.');
        return false;
    }
    
    // Validate email format
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(email)) {
        e.preventDefault();
        alert('Please enter a valid email address.');
        return false;
    }
    
    // Validate LinkedIn URL format if provided
    const linkedinUrl = document.getElementById('linkedin_profile').value.trim();
    if (linkedinUrl && !linkedinUrl.startsWith('https://linkedin.com/') && !linkedinUrl.startsWith('https://www.linkedin.com/')) {
        e.preventDefault();
        alert('LinkedIn profile URL should start with https://linkedin.com/ or https://www.linkedin.com/');
        return false;
    }
});
</script>

<?php include 'footer.php'; ?>
