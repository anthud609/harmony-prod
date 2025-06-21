<?php
// File: app/Core/Layout/Layouts/MainLayout.php
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Harmony HRMS') ?></title>
    
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
        const userTheme = '<?= e($user['preferredTheme'] ?? 'system') ?>';
        
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
    
    <?php if (isset($additionalStyles)) : ?>
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
        <?php if (! empty($breadcrumbs) || ! empty($pageTitle) || ! empty($pageActions)) : ?>
            <?php $this->component('pageHeader'); ?>
        <?php endif; ?>
        
        <!-- Page Content -->
        <div class="<?= (! empty($breadcrumbs) || ! empty($pageTitle) || ! empty($pageActions)) ? '' : 'pt-6' ?>">
            <?= $content ?>
        </div>
    </main>

    <!-- Scripts Component -->
    <?php $this->component('scripts'); ?>
    
    <!-- Session Handler Script -->
     <script>

        // Store base URL for JavaScript
        window.BASE_URL = '<?= base_url() ?>';
// Debug Session Handler - Extensive Logging Version
window.SessionHandler = {
    warningTime: <?= config('session.warning_time', 5) * 60 ?>, // Convert minutes to seconds
    checkInterval: 30000, // Check every 30 seconds instead of 5
  
    warningShown: false,
    timer: null,
    sessionStartTime: Date.now(),
    
    init() {
        console.log('ðŸš€ Session Handler Initializing...');
        console.log('Config:', {
            warningTime: this.warningTime,
            checkInterval: this.checkInterval,
            expectedTimeout: '5 minutes activity + 1 minute warning = 6 minutes total'
        });
        
        this.startMonitoring();
        this.setupActivityListeners();
        
        // Do an immediate check
        this.checkSessionStatus();
    },
    
    startMonitoring() {
        if (this.timer) {
            clearInterval(this.timer);
        }
        
        console.log('â° Starting session monitoring, checking every', this.checkInterval/1000, 'seconds');
        
        this.timer = setInterval(() => {
            this.checkSessionStatus();
        }, this.checkInterval);
    },
    
    async checkSessionStatus() {
        const checkTime = new Date().toLocaleTimeString();
        console.log(`ðŸ” [${checkTime}] Checking session status...`);
        
        try {
            const response = await fetch('/api/session-status', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            console.log('ðŸ“¡ Response status:', response.status);
            
            if (!response.ok) {
                console.error('âŒ Session check failed - not authenticated');
                this.handleSessionExpired();
                return;
            }
            
            const data = await response.json();
            console.log('ðŸ“Š Session data:', data);
            
            const remainingTime = data.remainingTime || 0;
            const remainingMinutes = Math.floor(remainingTime / 60);
            const remainingSeconds = remainingTime % 60;
            
            console.log(`â³ Remaining time: ${remainingMinutes}m ${remainingSeconds}s (${remainingTime} total seconds)`);
            console.log(`âš ï¸  Warning will show at: ${this.warningTime} seconds`);
            
            if (remainingTime <= 0) {
                console.error('ðŸ’€ Session expired (remaining time: 0)');
                this.handleSessionExpired();
            } else if (remainingTime <= this.warningTime && !this.warningShown) {
                console.warn('âš ï¸ Showing warning - remaining time:', remainingTime);
                this.showWarning(remainingTime);
            } else if (remainingTime > this.warningTime && this.warningShown) {
                console.log('âœ… Session extended - hiding warning');
                this.hideWarning();
            } else {
                console.log('âœ… Session OK');
            }
            
        } catch (error) {
            console.error('ðŸš¨ Session check error:', error);
        }
    },
    
    setupActivityListeners() {
        console.log('ðŸ‘‚ Setting up activity listeners...');
        
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
        let lastExtension = 0;
        
        events.forEach(event => {
            document.addEventListener(event, (e) => {
                const now = Date.now();
                // Only log and extend every 30 seconds to avoid spam
                if (now - lastExtension > 30000) {
                    console.log('ðŸ–±ï¸ User activity detected:', event);
                    lastExtension = now;
                    this.extendSession();
                }
            }, { passive: true });
        });
    },
    
    async extendSession() {
        console.log('ðŸ“¤ Extending session...');
        
        try {
            const response = await fetch('/api/extend-session', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': window.CsrfToken ? window.CsrfToken.getToken() : ''
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                console.log('âœ… Session extended successfully:', data);
                
                if (this.warningShown) {
                    this.hideWarning();
                }
            } else {
                console.error('âŒ Failed to extend session:', response.status);
            }
        } catch (error) {
            console.error('ðŸš¨ Extension error:', error);
        }
    },
    
    showWarning(remainingTime) {
        console.log('ðŸš¨ SHOWING WARNING - Remaining time:', remainingTime, 'seconds');
        this.warningShown = true;
        
        // Rest of the warning code...
        const warningHtml = `
            <div id="session-warning" class="fixed top-20 right-4 max-w-sm bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg shadow-lg p-4 z-50">
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
                        <p id="warning-message" class="mt-1 text-sm text-orange-700 dark:text-orange-300">
                            Your session will expire in ${remainingTime} seconds. 
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
        
        const existingWarning = document.getElementById('session-warning');
        if (existingWarning) {
            existingWarning.remove();
        }
        
        document.body.insertAdjacentHTML('beforeend', warningHtml);
        this.updateCountdown(remainingTime);
    },
    
    updateCountdown(remainingTime) {
        const countdownInterval = setInterval(() => {
            remainingTime--;
            
            const warningEl = document.getElementById('session-warning');
            if (!warningEl || remainingTime <= 0) {
                clearInterval(countdownInterval);
                if (remainingTime <= 0) {
                    this.handleSessionExpired();
                }
                return;
            }
            
            const messageEl = document.getElementById('warning-message');
            if (messageEl) {
                messageEl.textContent = `Your session will expire in ${remainingTime} seconds. Move your mouse or press any key to stay logged in.`;
            }
        }, 1000);
    },
    
    hideWarning() {
        console.log('âœ… Hiding warning');
        this.warningShown = false;
        const warning = document.getElementById('session-warning');
        if (warning) {
            warning.remove();
        }
    },
    
    handleSessionExpired() {
        console.error('ðŸ’€ SESSION EXPIRED - Redirecting to login...');
        
        if (this.timer) {
            clearInterval(this.timer);
        }
        
        this.hideWarning();
        
        // Show expired modal
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
                            Your session has expired. Please log in again.
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
        
        setTimeout(() => {
            window.location.href = '/login';
        }, 3000);
    },
    
    logout() {
        console.log('ðŸ‘‹ User logged out');
        window.location.href = '/logout';
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('ðŸ“„ DOM Ready - Initializing Session Handler');
    SessionHandler.init();
});
     </script>
    
    <?php if (isset($additionalScripts)) : ?>
        <?= $additionalScripts ?>
    <?php endif; ?>
</body>
</html>