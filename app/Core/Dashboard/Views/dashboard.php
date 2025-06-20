<div class="p-6 max-w-7xl mx-auto">
    <!-- Page header -->
    <div class="mb-8">
        <h1 id="welcomeMessage" class="text-3xl font-bold text-gray-800 dark:text-gray-100 mb-2">
            Welcome back, <?= htmlspecialchars($user['firstName'] ?? 'Guest') ?>! 👋
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
            Here's what's happening with your team today.
        </p>
    </div>

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
            <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-100"><?= $stats['totalEmployees'] ?></h3>
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
            <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-100"><?= $stats['presentToday'] ?></h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">Present Today</p>
        </div>

        <!-- Continue with other stats... -->
    </div>

    <!-- Quick Actions -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4">Quick Actions</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <button class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-all hover:border-indigo-500 dark:hover:border-indigo-400">
                <i class="fas fa-user-plus text-2xl text-indigo-600 dark:text-indigo-400 mb-2"></i>
                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Add Employee</p>
            </button>
            <!-- Continue with other actions... -->
        </div>
    </div>

    <!-- Recent Activities -->
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Recent Activities</h2>
        </div>
        <div class="p-6">
            <div class="space-y-4">
                <?php foreach ($recentActivities as $activity): ?>
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 bg-<?= $activity['color'] ?>-100 dark:bg-<?= $activity['color'] ?>-900 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="<?= $activity['icon'] ?> text-<?= $activity['color'] ?>-600 dark:text-<?= $activity['color'] ?>-400 text-xs"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-800 dark:text-gray-200">
                            <?= $activity['message'] ?>
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400"><?= $activity['time'] ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>