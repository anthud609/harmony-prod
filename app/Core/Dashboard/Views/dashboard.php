<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard – Harmony HRMS</title>

  <!-- enable class-based dark mode -->
  <style>
    /* Ensure dark mode works with Tailwind CDN */
    .dark {
      color-scheme: dark;
    }
    
    /* Disable transitions on page load to prevent flash */
    .no-transitions * {
      transition: none !important;
    }
    
    /* Smooth theme transitions */
    html.theme-transition,
    html.theme-transition *,
    html.theme-transition *:before,
    html.theme-transition *:after {
      transition: background-color 150ms ease-in-out, 
                  border-color 150ms ease-in-out,
                  color 150ms ease-in-out,
                  fill 150ms ease-in-out,
                  stroke 150ms ease-in-out !important;
      transition-delay: 0 !important;
    }
  </style>
  <script>
    // Apply dark mode class before page renders to prevent flash
    document.documentElement.classList.add('no-transitions');
    if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  </script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class'
    }
  </script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 antialiased">

  <!-- Mobile menu overlay -->
  <div id="mobileMenuOverlay"
       class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden transition-opacity duration-300"
       onclick="toggleMobileMenu()"></div>

  <!-- Header -->
  <header class="fixed top-0 left-0 right-0 bg-white dark:bg-gray-800 shadow-sm z-30 transition-all duration-300">
    <div class="flex items-center justify-between h-16 px-4 lg:px-6">

      <!-- left: toggles + logo -->
      <div class="flex items-center">
        <button onclick="toggleMobileMenu()"
                class="lg:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors mr-2">
          <i class="fas fa-bars text-gray-700 dark:text-gray-300"></i>
        </button>
        <button onclick="toggleSidebar()"
                class="hidden lg:block p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors mr-4">
          <i class="fas fa-bars text-gray-700 dark:text-gray-300"></i>
        </button>
        <div class="flex items-center space-x-3">
          <div class="w-9 h-9 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg flex items-center justify-center shadow-sm">
            <i class="fas fa-building text-white"></i>
          </div>
          <h1 class="text-xl font-bold text-gray-800 dark:text-gray-100 hidden sm:block">Harmony HRMS</h1>
        </div>
      </div>

      <!-- center: search -->
      <div class="hidden md:flex flex-1 max-w-md mx-8">
        <div class="relative w-full">
          <input type="text"
                 placeholder="Search employees, documents…"
                 class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all">
          <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500"></i>
        </div>
      </div>

      <!-- right: actions -->
      <div class="flex items-center space-x-2">
        <!-- search (mobile) -->
        <button class="md:hidden p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
          <i class="fas fa-search text-gray-700 dark:text-gray-300"></i>
        </button>

        <!-- notifications -->
        <button class="relative p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
          <i class="fas fa-bell text-gray-700 dark:text-gray-300"></i>
          <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
        </button>

        <!-- theme toggle -->
        <button id="themeToggle" onclick="toggleDarkMode()"
                class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
          <i id="themeIcon" class="fas fa-moon text-gray-700 dark:text-gray-300"></i>
        </button>

        <!-- user menu -->
        <div class="relative ml-3">
          <button onclick="toggleUserMenu()"
                  class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
            <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
              <span class="text-white text-sm font-medium">J</span>
            </div>
            <div class="hidden sm:block text-left">
              <p class="text-sm font-medium text-gray-700 dark:text-gray-200">John</p>
              <p class="text-xs text-gray-500 dark:text-gray-400">Admin</p>
            </div>
            <i class="fas fa-chevron-down text-gray-400 dark:text-gray-500 text-xs"></i>
          </button>

          <!-- dropdown -->
          <div id="userMenu"
               class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 hidden">
            <div class="py-2">
              <a href="#"
                 class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                <i class="fas fa-user mr-3 text-gray-400 dark:text-gray-500"></i> My Profile
              </a>
              <a href="#"
                 class="flex items-center px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700">
                <i class="fas fa-cog mr-3 text-gray-400 dark:text-gray-500"></i> Settings
              </a>
              <hr class="my-2 border-gray-200 dark:border-gray-700">
              <a href="/logout"
                 class="flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-950">
                <i class="fas fa-sign-out-alt mr-3"></i> Sign Out
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <!-- Sidebar -->
  <aside id="sidebar"
         class="fixed left-0 top-16 h-[calc(100vh-4rem)] w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 transform -translate-x-full lg:translate-x-0 transition-all duration-300 z-20 overflow-hidden">
    <nav class="flex flex-col h-full">
      <div class="px-3 py-4">
        <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Navigation</p>
      </div>
      <div class="flex-1 overflow-y-auto px-3 space-y-1">
        <a href="#"
           class="flex items-center space-x-3 px-3 py-2 rounded-lg bg-indigo-50 dark:bg-indigo-900 text-indigo-600 dark:text-indigo-300 transition-all duration-200">
          <i class="fas fa-home w-5 text-center"></i>
          <span class="sidebar-text text-sm font-medium">Dashboard</span>
          <span class="sidebar-badge ml-auto bg-indigo-100 dark:bg-indigo-700 text-indigo-600 dark:text-indigo-300 px-2 py-0.5 rounded text-xs font-medium">New</span>
        </a>

        <!-- Employees -->
        <div>
          <button onclick="toggleDropdown('employeesDropdown')"
                  class="w-full flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200">
            <i class="fas fa-users w-5 text-center"></i>
            <span class="sidebar-text text-sm font-medium">Employees</span>
            <i id="employeesDropdownIcon" class="fas fa-chevron-down ml-auto text-xs transition-transform duration-200 sidebar-text"></i>
          </button>
          <div id="employeesDropdown" class="hidden pl-10 space-y-1 mt-1">
            <a href="#"
               class="flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200 text-sm">
              <i class="fas fa-list w-5 text-center text-xs"></i>
              <span class="sidebar-text">All Employees</span>
            </a>
            <a href="#"
               class="flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200 text-sm">
              <i class="fas fa-user-plus w-5 text-center text-xs"></i>
              <span class="sidebar-text">Add Employee</span>
            </a>
            <a href="#"
               class="flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200 text-sm">
              <i class="fas fa-sitemap w-5 text-center text-xs"></i>
              <span class="sidebar-text">Departments</span>
            </a>
          </div>
        </div>

        <!-- Attendance -->
        <div>
          <button onclick="toggleDropdown('attendanceDropdown')"
                  class="w-full flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200">
            <i class="fas fa-clock w-5 text-center"></i>
            <span class="sidebar-text text-sm font-medium">Attendance</span>
            <i id="attendanceDropdownIcon" class="fas fa-chevron-down ml-auto text-xs transition-transform duration-200 sidebar-text"></i>
          </button>
          <div id="attendanceDropdown" class="hidden pl-10 space-y-1 mt-1">
            <a href="#"
               class="flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200 text-sm">
              <i class="fas fa-calendar-check w-5 text-center text-xs"></i>
              <span class="sidebar-text">Today</span>
            </a>
            <a href="#"
               class="flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200 text-sm">
              <i class="fas fa-history w-5 text-center text-xs"></i>
              <span class="sidebar-text">History</span>
            </a>
          </div>
        </div>

        <a href="#"
           class="flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200">
          <i class="fas fa-calendar-alt w-5 text-center"></i>
          <span class="sidebar-text text-sm font-medium">Leave</span>
          <span class="ml-auto w-2 h-2 bg-orange-500 rounded-full"></span>
        </a>

        <a href="#"
           class="flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200">
          <i class="fas fa-money-check-alt w-5 text-center"></i>
          <span class="sidebar-text text-sm font-medium">Payroll</span>
        </a>

        <a href="#"
           class="flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200">
          <i class="fas fa-chart-bar w-5 text-center"></i>
          <span class="sidebar-text text-sm font-medium">Reports</span>
        </a>

        <a href="#"
           class="flex items-center space-x-3 px-3 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all duration-200">
          <i class="fas fa-cog w-5 text-center"></i>
          <span class="sidebar-text text-sm font-medium">Settings</span>
        </a>
      </div>

      <div class="p-4 border-t border-gray-200 dark:border-gray-700">
        <button class="w-full flex items-center justify-center px-4 py-2 text-sm text-gray-600 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
          <i class="fas fa-headset mr-2"></i>
          <span class="sidebar-text">Get Support</span>
        </button>
      </div>
    </nav>
  </aside>

  <!-- Main content -->
  <main id="mainContent" class="pt-16 lg:pl-64 transition-all duration-300">
    <div class="p-6 max-w-7xl mx-auto">
      <!-- page header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-gray-100 mb-2">
          Welcome back, John! 👋
        </h1>
        <p class="text-gray-600 dark:text-gray-400">
          Here's what's happening with your team today.
        </p>
      </div>

      <!-- stats -->
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
          <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-100">284</h3>
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
          <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-100">270</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">Present Today</p>
        </div>

        <!-- On Leave -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
          <div class="flex items-center justify-between mb-4">
            <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900 rounded-lg flex items-center justify-center">
              <i class="fas fa-calendar-times text-orange-600 dark:text-orange-400"></i>
            </div>
            <span class="text-xs font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">
              3 pending approvals
            </span>
          </div>
          <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-100">14</h3>
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
          <h3 class="text-2xl font-bold text-gray-800 dark:text-gray-100">8</h3>
          <p class="text-sm text-gray-600 dark:text-gray-400">New Applications</p>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100 mb-4">Quick Actions</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <button class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-all hover:border-indigo-500 dark:hover:border-indigo-400">
            <i class="fas fa-user-plus text-2xl text-indigo-600 dark:text-indigo-400 mb-2"></i>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Add Employee</p>
          </button>
          <button class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-all hover:border-indigo-500 dark:hover:border-indigo-400">
            <i class="fas fa-calendar-plus text-2xl text-green-600 dark:text-green-400 mb-2"></i>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Request Leave</p>
          </button>
          <button class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-all hover:border-indigo-500 dark:hover:border-indigo-400">
            <i class="fas fa-clock text-2xl text-orange-600 dark:text-orange-400 mb-2"></i>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Mark Attendance</p>
          </button>
          <button class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 hover:shadow-md transition-all hover:border-indigo-500 dark:hover:border-indigo-400">
            <i class="fas fa-file-download text-2xl text-purple-600 dark:text-purple-400 mb-2"></i>
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">Generate Report</p>
          </button>
        </div>
      </div>

      <!-- Recent Activities -->
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="p-6 border-b border-gray-200 dark:border-gray-700">
          <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">Recent Activities</h2>
        </div>
        <div class="p-6">
          <div class="space-y-4">
            <div class="flex items-start space-x-3">
              <div class="w-8 h-8 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center flex-shrink-0">
                <i class="fas fa-user-plus text-blue-600 dark:text-blue-400 text-xs"></i>
              </div>
              <div>
                <p class="text-sm text-gray-800 dark:text-gray-200">
                  <span class="font-medium">Sarah Johnson</span> joined as Senior Developer
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">2 hours ago</p>
              </div>
            </div>
            <div class="flex items-start space-x-3">
              <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center flex-shrink-0">
                <i class="fas fa-check text-green-600 dark:text-green-400 text-xs"></i>
              </div>
              <div>
                <p class="text-sm text-gray-800 dark:text-gray-200">
                  Leave request approved for <span class="font-medium">Michael Chen</span>
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">5 hours ago</p>
              </div>
            </div>
            <div class="flex items-start space-x-3">
              <div class="w-8 h-8 bg-orange-100 dark:bg-orange-900 rounded-full flex items-center justify-center flex-shrink-0">
                <i class="fas fa-calendar text-orange-600 dark:text-orange-400 text-xs"></i>
              </div>
              <div>
                <p class="text-sm text-gray-800 dark:text-gray-200">
                  <span class="font-medium">Emma Davis</span> requested leave for Dec 25-27
                </p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Yesterday</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script>
    // Wait for DOM to be ready
    document.addEventListener('DOMContentLoaded', function() {
      // Theme initialization
      const themeIcon = document.getElementById('themeIcon');
      
      // Set initial icon state based on current theme
      function updateThemeIcon() {
        if (document.documentElement.classList.contains('dark')) {
          themeIcon.className = 'fas fa-sun text-gray-700 dark:text-gray-300';
        } else {
          themeIcon.className = 'fas fa-moon text-gray-700 dark:text-gray-300';
        }
      }
      
      // Initial icon update
      updateThemeIcon();

      // Make toggleDarkMode globally available
      window.toggleDarkMode = function() {
        // Toggle dark class on html element
        if (document.documentElement.classList.contains('dark')) {
          document.documentElement.classList.remove('dark');
          localStorage.theme = 'light';
        } else {
          document.documentElement.classList.add('dark');
          localStorage.theme = 'dark';
        }
        
        // Update icon
        updateThemeIcon();
      }
    });

    // Sidebar state management
    let sidebarOpen = true;
    
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const main = document.getElementById('mainContent');
      sidebarOpen = !sidebarOpen;
      
      if (sidebarOpen) {
        sidebar.classList.remove('w-16');
        sidebar.classList.add('w-64');
        main.classList.remove('lg:pl-16');
        main.classList.add('lg:pl-64');
        
        // Show text elements
        document.querySelectorAll('.sidebar-text').forEach(el => {
          el.classList.remove('hidden');
        });
        document.querySelectorAll('.sidebar-badge').forEach(el => {
          el.classList.remove('hidden');
        });
        
        // Restore dropdowns if they were open
        if (localStorage.getItem('employeesDropdownOpen') === 'true') {
          document.getElementById('employeesDropdown').classList.remove('hidden');
        }
        if (localStorage.getItem('attendanceDropdownOpen') === 'true') {
          document.getElementById('attendanceDropdown').classList.remove('hidden');
        }
      } else {
        sidebar.classList.remove('w-64');
        sidebar.classList.add('w-16');
        main.classList.remove('lg:pl-64');
        main.classList.add('lg:pl-16');
        
        // Hide text elements
        document.querySelectorAll('.sidebar-text').forEach(el => {
          el.classList.add('hidden');
        });
        document.querySelectorAll('.sidebar-badge').forEach(el => {
          el.classList.add('hidden');
        });
        
        // Hide dropdowns when sidebar is collapsed
        document.getElementById('employeesDropdown').classList.add('hidden');
        document.getElementById('attendanceDropdown').classList.add('hidden');
      }
    }

    // Mobile menu
    let mobileMenuOpen = false;
    
    function toggleMobileMenu() {
      mobileMenuOpen = !mobileMenuOpen;
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('mobileMenuOverlay');
      
      if (mobileMenuOpen) {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
      } else {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
      }
    }

    // Dropdown management
    function toggleDropdown(id) {
      const dropdown = document.getElementById(id);
      const icon = document.getElementById(id + 'Icon');
      
      // Close all other dropdowns first
      const allDropdowns = ['employeesDropdown', 'attendanceDropdown'];
      allDropdowns.forEach(dropdownId => {
        if (dropdownId !== id) {
          document.getElementById(dropdownId).classList.add('hidden');
          document.getElementById(dropdownId + 'Icon').classList.remove('rotate-180');
          localStorage.setItem(dropdownId + 'Open', 'false');
        }
      });
      
      // Toggle current dropdown
      if (dropdown.classList.contains('hidden')) {
        dropdown.classList.remove('hidden');
        icon.classList.add('rotate-180');
        localStorage.setItem(id + 'Open', 'true');
      } else {
        dropdown.classList.add('hidden');
        icon.classList.remove('rotate-180');
        localStorage.setItem(id + 'Open', 'false');
      }
    }

    // User menu
    function toggleUserMenu() {
      const menu = document.getElementById('userMenu');
      menu.classList.toggle('hidden');
    }

    // Close user menu when clicking outside
    document.addEventListener('click', function(e) {
      const userMenuButton = e.target.closest('[onclick="toggleUserMenu()"]');
      const userMenu = document.getElementById('userMenu');
      
      if (!userMenuButton && !userMenu.contains(e.target)) {
        userMenu.classList.add('hidden');
      }
      
      // Close sidebar dropdowns when clicking outside sidebar
      const sidebar = document.getElementById('sidebar');
      const dropdownButton = e.target.closest('[onclick*="toggleDropdown"]');
      
      if (!sidebar.contains(e.target) || (!dropdownButton && !e.target.closest('#employeesDropdown') && !e.target.closest('#attendanceDropdown'))) {
        // Close all dropdowns
        const allDropdowns = ['employeesDropdown', 'attendanceDropdown'];
        allDropdowns.forEach(dropdownId => {
          document.getElementById(dropdownId).classList.add('hidden');
          document.getElementById(dropdownId + 'Icon').classList.remove('rotate-180');
          localStorage.setItem(dropdownId + 'Open', 'false');
        });
      }
    });

    // Initialize dropdowns based on saved state
    window.addEventListener('DOMContentLoaded', function() {
      // Only open one dropdown at a time, prefer the first one that was saved as open
      if (localStorage.getItem('employeesDropdownOpen') === 'true') {
        toggleDropdown('employeesDropdown');
      } else if (localStorage.getItem('attendanceDropdownOpen') === 'true') {
        toggleDropdown('attendanceDropdown');
      }
    });

    // Handle window resize
    window.addEventListener('resize', function() {
      if (window.innerWidth >= 1024 && mobileMenuOpen) {
        toggleMobileMenu();
      }
    });
  </script>

</body>
</html>