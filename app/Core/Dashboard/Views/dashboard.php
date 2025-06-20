<!-- File: app/Core/Dashboard/Views/dashboard.php (XSS Protected) -->
<div class="p-6 max-w-7xl mx-auto">
    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Employees -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 dark:text-blue-400"></i>
                </div>
                <span class="text-xs font-medium text-green-600 dark:text-green-400 bg-green-100 dark:bg-green-900 px-2 py-1 rounded">
                    +12% from last month
                </span>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-100"><?= e($stats['totalEmployees']) ?></h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">Total Employees</p>
        </div>

        <!-- Present Today -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-green-100 dark:bg-green-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-check text-green-600 dark:text-green-400"></i>
                </div>
                <span class="text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-100 dark:bg-blue-900 px-2 py-1 rounded">
                    95% attendance
                </span>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-100"><?= e($stats['presentToday']) ?></h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">Present Today</p>
        </div>

        <!-- On Leave -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar-times text-orange-600 dark:text-orange-400"></i>
                </div>
                <span class="text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
                    5% of workforce
                </span>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-100"><?= e($stats['onLeave']) ?></h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">On Leave</p>
        </div>

        <!-- New Applications -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900 rounded-lg flex items-center justify-center">
                    <i class="fas fa-file-alt text-purple-600 dark:text-purple-400"></i>
                </div>
                <span class="text-xs font-medium text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900 px-2 py-1 rounded">
                    Urgent
                </span>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-100"><?= e($stats['newApplications']) ?></h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">New Applications</p>
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Recent Activities</h2>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <?php foreach ($recentActivities as $activity) : ?>
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 bg-<?= ecss($activity['color']) ?>-100 dark:bg-<?= ecss($activity['color']) ?>-900 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="<?= attr($activity['icon']) ?> text-<?= ecss($activity['color']) ?>-600 dark:text-<?= ecss($activity['color']) ?>-400 text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-800 dark:text-gray-200">
<?= e($activity['message']) ?>
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400"><?= e($activity['time']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<!-- Fixed JavaScript with proper escaping -->
<script>
function exportDashboard() {
    // Implement export functionality
    alert('Export functionality would be implemented here');
}

function refreshDashboard() {
    // Implement refresh functionality
    location.reload();
}

function resetDashboard() {
    if (confirm('Are you sure you want to reset the dashboard to default settings?')) {
        // Implement reset functionality
        alert('Dashboard reset functionality would be implemented here');
    }
}

function openAddWidgetModal() {
    // Implement add widget modal
    alert('Add widget modal would be implemented here');
}
</script>
