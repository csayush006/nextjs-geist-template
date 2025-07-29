</main>
    
    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-sm text-gray-600">
                    &copy; <?php echo date("Y"); ?> <?php echo APP_NAME; ?>. All rights reserved.
                </div>
                <div class="mt-2 md:mt-0 text-sm text-gray-500">
                    Last updated: <?php echo date("M j, Y g:i A"); ?>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript for enhanced functionality -->
    <script>
        // Auto-refresh data every 5 minutes on dashboard
        <?php if (basename($_SERVER['PHP_SELF']) == 'dashboard.php'): ?>
        setInterval(function() {
            // Check if user is still active (optional)
            fetch('check_session.php')
                .then(response => response.json())
                .then(data => {
                    if (!data.active) {
                        window.location.href = 'login.php';
                    }
                })
                .catch(error => console.log('Session check failed'));
        }, 300000); // 5 minutes
        <?php endif; ?>

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Loading state for buttons
        function showLoading(button) {
            const originalText = button.innerHTML;
            button.innerHTML = '<span class="inline-block animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>Loading...';
            button.disabled = true;
            
            // Reset after 10 seconds as fallback
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 10000);
        }

        // Add loading state to refresh buttons
        document.querySelectorAll('.refresh-btn').forEach(button => {
            button.addEventListener('click', function() {
                showLoading(this);
            });
        });

        // Confirmation dialogs for destructive actions
        document.querySelectorAll('.confirm-action').forEach(element => {
            element.addEventListener('click', function(e) {
                const message = this.getAttribute('data-confirm') || 'Are you sure?';
                if (!confirm(message)) {
                    e.preventDefault();
                }
            });
        });

        // Table sorting functionality
        function sortTable(columnIndex, tableId = 'data-table') {
            const table = document.getElementById(tableId);
            if (!table) return;
            
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            
            // Determine sort direction
            const header = table.querySelectorAll('th')[columnIndex];
            const isAscending = !header.classList.contains('sort-desc');
            
            // Remove existing sort classes
            table.querySelectorAll('th').forEach(th => {
                th.classList.remove('sort-asc', 'sort-desc');
            });
            
            // Add new sort class
            header.classList.add(isAscending ? 'sort-asc' : 'sort-desc');
            
            // Sort rows
            rows.sort((a, b) => {
                const aText = a.cells[columnIndex].textContent.trim();
                const bText = b.cells[columnIndex].textContent.trim();
                
                // Try to parse as numbers first
                const aNum = parseFloat(aText);
                const bNum = parseFloat(bText);
                
                if (!isNaN(aNum) && !isNaN(bNum)) {
                    return isAscending ? aNum - bNum : bNum - aNum;
                }
                
                // Fall back to string comparison
                return isAscending ? 
                    aText.localeCompare(bText) : 
                    bText.localeCompare(aText);
            });
            
            // Reorder rows in DOM
            rows.forEach(row => tbody.appendChild(row));
        }

        // Add click handlers to sortable headers
        document.querySelectorAll('.sortable').forEach((header, index) => {
            header.style.cursor = 'pointer';
            header.addEventListener('click', () => sortTable(index));
        });

        // Search functionality
        function filterTable(searchTerm, tableId = 'data-table') {
            const table = document.getElementById(tableId);
            if (!table) return;
            
            const rows = table.querySelectorAll('tbody tr');
            const term = searchTerm.toLowerCase();
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        }

        // Add search functionality if search input exists
        const searchInput = document.getElementById('table-search');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                filterTable(this.value);
            });
        }
    </script>

    <!-- Custom CSS for enhanced styling -->
    <style>
        .sort-asc::after {
            content: ' ↑';
            color: #3b82f6;
        }
        
        .sort-desc::after {
            content: ' ↓';
            color: #3b82f6;
        }
        
        .activity-badge {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.75rem;
            font-weight: 500;
        }
        
        .activity-github {
            background-color: #f3f4f6;
            color: #374151;
        }
        
        .activity-leetcode {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .activity-linkedin {
            background-color: #dbeafe;
            color: #1e40af;
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }
        
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3b82f6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>

</body>
</html>
