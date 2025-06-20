// File: app/Core/Layout/Layouts/MainLayout.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Harmony HRMS') ?></title>
    
    <!-- Styles -->
    <style>
        .dark { color-scheme: dark; }
        .no-transitions * { transition: none !important; }
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
    
    <!-- Theme Script -->
    <script>
        document.documentElement.classList.add('no-transitions');
        const userTheme = '<?= htmlspecialchars($user['preferredTheme'] ?? 'system') ?>';
        
        if (userTheme === 'dark') {
            document.documentElement.classList.add('dark');
        } else if (userTheme === 'light') {
            document.documentElement.classList.remove('dark');
        } else {
            if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
                document.documentElement.classList.add('dark');
            }
        }
    </script>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <?php if (isset($additionalStyles)): ?>
        <?= $additionalStyles ?>
    <?php endif; ?>

    <?= csrf_meta() ?>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100 antialiased">
    <!-- Mobile menu overlay -->
    <div id="mobileMenuOverlay"
         class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden hidden transition-opacity duration-300"
         onclick="toggleMobileMenu()"></div>

    <!-- Header Component -->
    <?php $this->component('header'); ?>

    <!-- Sidebar Component -->
    <?php $this->component('sidebar'); ?>

    <!-- Main content -->
    <main id="mainContent" class="pt-16 lg:pl-64 transition-all duration-300">
        <!-- Page Header Component -->
        <?php if (!empty($breadcrumbs) || !empty($pageTitle) || !empty($pageActions)): ?>
            <?php $this->component('pageHeader'); ?>
        <?php endif; ?>
        
        <!-- Page Content -->
        <div class="<?= (!empty($breadcrumbs) || !empty($pageTitle) || !empty($pageActions)) ? '' : 'pt-6' ?>">
            <?= $content ?>
        </div>
    </main>

    <!-- Scripts Component -->
    <?php $this->component('scripts'); ?>
    
    <!-- Session Handler Script -->
    <script>
    // Session Handler Implementation
    window.SessionHandler = {
        warningTime: 300, // Show warning 5 minutes before timeout
        checkInterval: 60000, // Check every minute
        warningShown: false,
        timer: null,
        
        init() {
            this.startMonitoring();
            this.setupActivityListeners();
        },
        
        startMonitoring() {
            // Clear any existing timer
            if (this.timer) {
                clearInterval(this.timer);
            }
            
            // Check session status periodically
            this.timer = setInterval(() => {
                this.checkSessionStatus();
            }, this.checkInterval);
            
            // Check immediately
            this.checkSessionStatus();
        },
        
        async checkSessionStatus() {
            try {
                const response = await fetch('/api/session-status', {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                
                if (!response.ok) {
                    // Session expired
                    this.handleSessionExpired();
                    return;
                }
                
                const data = await response.json();
                const remainingTime = data.remainingTime || 0;
                
                if (remainingTime <= 0) {
                    this.handleSessionExpired();
                } else if (remainingTime <= this.warningTime && !this.warningShown) {
                    this.showWarning(remainingTime);
                } else if (remainingTime > this.warningTime && this.warningShown) {
                    this.hideWarning();
                }
            } catch (error) {
                console.error('Session check failed:', error);
            }
        },
        
        setupActivityListeners() {
            // Extend session on user activity
            const events = ['mousedown', 'keypress', 'scroll', 'touchstart'];
            let lastActivity = Date.now();
            const activityThreshold = 30000; // 30 seconds
            
            events.forEach(event => {
                document.addEventListener(event, () => {
                    const now = Date.now();
                    if (now - lastActivity > activityThreshold) {
                        lastActivity = now;
                        this.extendSession();
                    }
                }, { passive: true });
            });
        },
        
        async extendSession() {
            try {
                await fetch('/api/extend-session', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-Token': window.CsrfToken.getToken()
                    }
                });
                
                // Reset warning if shown
                if (this.warningShown) {
                    this.hideWarning();
                }
            } catch (error) {
                console.error('Failed to extend session:', error);
            }
        },
        
        showWarning(remainingTime) {
            this.warningShown = true;
            
            const minutes = Math.ceil(remainingTime / 60);
            const warningHtml = `
                <div id="session-warning" class="fixed top-20 right-4 max-w-sm bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg shadow-lg p-4 z-50 animate-slide-in">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-orange-800 dark:text-orange-200">
                                Session Expiring Soon
                            </h3>
                            <p class="mt-1 text-sm text-orange-700 dark:text-orange-300">
                                Your session will expire in ${minutes} minute${minutes > 1 ? 's' : ''}. 
                                Move your mouse or press any key to stay logged in.
                            </p>
                            <div class="mt-3 flex space-x-2">
                                <button onclick="SessionHandler.extendSession()" 
                                        class="text-sm bg-orange-600 text-white px-3 py-1.5 rounded hover:bg-orange-700 transition-colors">
                                    Stay Logged In
                                </button>
                                <button onclick="SessionHandler.logout()" 
                                        class="text-sm text-orange-800 dark:text-orange-200 px-3 py-1.5 rounded hover:bg-orange-100 dark:hover:bg-orange-800/30 transition-colors">
                                    Log Out
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Remove any existing warning
            const existingWarning = document.getElementById('session-warning');
            if (existingWarning) {
                existingWarning.remove();
            }
            
            // Add new warning
            document.body.insertAdjacentHTML('beforeend', warningHtml);
            
            // Update countdown
            this.updateCountdown(remainingTime);
        },
        
        updateCountdown(remainingTime) {
            const countdownInterval = setInterval(() => {
                remainingTime--;
                const minutes = Math.ceil(remainingTime / 60);
                
                const warningEl = document.getElementById('session-warning');
                if (!warningEl || remainingTime <= 0) {
                    clearInterval(countdownInterval);
                    return;
                }
                
                const timeText = warningEl.querySelector('p');
                if (timeText) {
                    timeText.textContent = `Your session will expire in ${minutes} minute${minutes > 1 ? 's' : ''}. Move your mouse or press any key to stay logged in.`;
                }
            }, 1000);
        },
        
        hideWarning() {
            this.warningShown = false;
            const warning = document.getElementById('session-warning');
            if (warning) {
                warning.classList.add('animate-slide-out');
                setTimeout(() => warning.remove(), 300);
            }
        },
        
        handleSessionExpired() {
            // Clear timer
            if (this.timer) {
                clearInterval(this.timer);
            }
            
            // Show modal
            const modalHtml = `
                <div id="session-expired-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 z-[100]">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-md w-full p-6">
                        <div class="text-center">
                            <div class="mx-auto w-12 h-12 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-.833-1.964-.833-2.732 0L4.732 16c-.77.833.192 2 1.732 2z"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                                Session Expired
                            </h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                                Your session has expired due to inactivity. Please log in again to continue.
                            </p>
                            <button onclick="window.location.href='/login'" 
                                    class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 transition-colors">
                                Go to Login
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            
            // Redirect after 5 seconds
            setTimeout(() => {
                window.location.href = '/login';
            }, 5000);
        },
        
        logout() {
            window.location.href = '/logout';
        }
    };
    
    // Initialize session handler when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        SessionHandler.init();
    });
    
    // Add CSS for animations
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slide-in {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slide-out {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .animate-slide-in {
            animation: slide-in 0.3s ease-out;
        }
        
        .animate-slide-out {
            animation: slide-out 0.3s ease-out;
        }
    `;
    document.head.appendChild(style);
    </script>
    
    <?php if (isset($additionalScripts)): ?>
        <?= $additionalScripts ?>
    <?php endif; ?>
</body>
</html>