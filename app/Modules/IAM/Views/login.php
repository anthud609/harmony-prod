<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login â€“ Harmony HRMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 3s ease-in-out infinite',
                        'fade-in': 'fadeIn 0.5s ease-out',
                        'slide-up': 'slideUp 0.3s ease-out',
                        'shake': 'shake 0.5s ease-in-out',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-20px)' }
                        },
                        fadeIn: {
                            '0%': { opacity: '0', transform: 'translateY(10px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        },
                        slideUp: {
                            '0%': { transform: 'translateY(20px)', opacity: '0' },
                            '100%': { transform: 'translateY(0)', opacity: '1' }
                        },
                        shake: {
                            '0%, 100%': { transform: 'translateX(0)' },
                            '10%, 30%, 50%, 70%, 90%': { transform: 'translateX(-5px)' },
                            '20%, 40%, 60%, 80%': { transform: 'translateX(5px)' }
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-900 via-purple-900 to-pink-800 overflow-hidden relative">
    <!-- Animated background elements -->
    <div class="absolute inset-0 overflow-hidden">
        <div class="absolute -top-4 -left-4 w-72 h-72 bg-purple-500 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-float"></div>
        <div class="absolute -top-4 -right-4 w-72 h-72 bg-indigo-500 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-float" style="animation-delay: 2s;"></div>
        <div class="absolute -bottom-8 left-20 w-72 h-72 bg-pink-500 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-float" style="animation-delay: 4s;"></div>
    </div>

    <!-- Main container -->
    <div class="relative z-10 min-h-screen flex items-center justify-center p-4 py-8 sm:py-4">
        <div class="w-full max-w-md">
            <!-- Logo and branding -->
            <div class="text-center mb-6 sm:mb-8 animate-fade-in">
                <div class="inline-flex items-center justify-center w-12 h-12 sm:w-16 sm:h-16 bg-white/20 backdrop-blur-sm rounded-2xl mb-3 sm:mb-4 shadow-lg">
                    <svg class="w-6 h-6 sm:w-8 sm:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-white mb-1 sm:mb-2">Harmony HRMS</h1>
                <p class="text-white/70 text-sm">Enterprise Human Resource Management</p>
            </div>

            <!-- Main login card -->
            <div class="bg-white/10 backdrop-blur-md rounded-3xl shadow-2xl border border-white/20 p-6 sm:p-8 animate-slide-up">
                <!-- Show PHP error message if exists -->
                <?php if (isset($_SESSION['flash_error'])) : ?>
                <div class="mb-4 p-3 bg-red-500/20 border border-red-500/30 rounded-xl text-red-200 text-sm animate-shake">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <span><?= e($_SESSION['flash_error']) ?></span>
                    </div>
                </div>
                    <?php unset($_SESSION['flash_error']); ?>
                <?php endif; ?>

                <!-- Auth method toggle -->
                <div class="flex mb-4 sm:mb-6 bg-white/5 rounded-2xl p-1" id="authToggle">
                    <button class="flex-1 py-3 px-4 rounded-xl text-sm font-medium transition-all duration-300 text-white bg-white/20" data-method="credentials">
                        Email & Password
                    </button>
                    <button class="flex-1 py-3 px-4 rounded-xl text-sm font-medium transition-all duration-300 text-white/70 hover:text-white" data-method="sso">
                        SSO Login
                    </button>
                </div>

                <!-- Credentials Login Form -->
<form id="credentialsForm" class="space-y-4 sm:space-y-6" method="POST" action="/login">
    <?= csrf_field() ?>                    <!-- Username field (changed from email to match PHP backend) -->
                    <div class="relative">
                        <label class="block text-sm font-medium text-white/90 mb-2">Username / Email</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                </svg>
                            </div>
                            <input type="text" name="username" id="username" required 
                                   class="w-full pl-10 pr-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-300"
                                   placeholder="Enter username or email" autofocus>
                        </div>
                    </div>

                    <!-- Password field -->
                    <div class="relative">
                        <label class="block text-sm font-medium text-white/90 mb-2">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                </svg>
                            </div>
                            <input type="password" name="password" id="password" required 
                                   class="w-full pl-10 pr-12 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-300"
                                   placeholder="Enter your password">
                            <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center" onclick="togglePassword()">
                                <svg id="eyeIcon" class="h-5 w-5 text-white/50 hover:text-white/80 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember me and forgot password -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" class="w-4 h-4 rounded border-white/20 bg-white/10 text-indigo-600 focus:ring-white/50 focus:ring-2">
                            <span class="ml-2 text-sm text-white/80">Remember me</span>
                        </label>
                        <button type="button" onclick="showForgotPassword()" class="text-sm text-white/80 hover:text-white transition-colors">
                            Forgot password?
                        </button>
                    </div>

                    <!-- Login button -->
                    <button type="submit" class="w-full bg-gradient-to-r from-indigo-500 to-purple-600 text-white py-3 px-6 rounded-xl font-medium hover:from-indigo-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-white/50 transition-all duration-300 transform hover:scale-[1.02] active:scale-[0.98]">
                        Sign In
                    </button>
                </form>

                <!-- SSO Login Form -->
                <div id="ssoForm" class="space-y-4 hidden">
                    <button type="button" onclick="handleSSOLogin('microsoft')" class="w-full flex items-center justify-center px-4 py-3 border border-white/20 rounded-xl text-white hover:bg-white/10 transition-all duration-300 transform hover:scale-[1.02]">
                        <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M11.4 24H0V12.6h11.4V24zM24 24H12.6V12.6H24V24zM11.4 11.4H0V0h11.4v11.4zM24 11.4H12.6V0H24v11.4z"/>
                        </svg>
                        Continue with Microsoft
                    </button>
                    <button type="button" onclick="handleSSOLogin('google')" class="w-full flex items-center justify-center px-4 py-3 border border-white/20 rounded-xl text-white hover:bg-white/10 transition-all duration-300 transform hover:scale-[1.02]">
                        <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                            <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                            <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                            <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        Continue with Google
                    </button>
                    <button type="button" onclick="handleSSOLogin('okta')" class="w-full flex items-center justify-center px-4 py-3 border border-white/20 rounded-xl text-white hover:bg-white/10 transition-all duration-300 transform hover:scale-[1.02]">
                        <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 0C5.383 0 0 5.383 0 12s5.383 12 12 12 12-5.383 12-12S18.617 0 12 0zm0 2.4c5.302 0 9.6 4.298 9.6 9.6s-4.298 9.6-9.6 9.6S2.4 17.302 2.4 12 6.698 2.4 12 2.4z"/>
                        </svg>
                        Continue with Okta
                    </button>
                </div>

                <!-- Demo credentials info -->
                <div class="mt-4 text-center text-white/60 text-xs">
                    <p>Demo users: alice_admin@email.com | bob_editor | charlie_user</p>
                    <p>Password: secret</p>
                </div>
            </div>

            <!-- Security info -->
            <div class="mt-4 sm:mt-6 text-center">
                <p class="text-white/60 text-xs">
                    ðŸ”’ Your connection is secure and encrypted
                </p>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div id="forgotPasswordModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center p-4 hidden z-50">
        <div class="bg-white/10 backdrop-blur-md rounded-3xl shadow-2xl border border-white/20 p-8 w-full max-w-md animate-slide-up">
            <div class="text-center mb-6">
                <h2 class="text-2xl font-bold text-white mb-2">Reset Password</h2>
                <p class="text-white/70 text-sm">Enter your email to receive reset instructions</p>
            </div>
            <form id="forgotPasswordForm" class="space-y-4">
                <div class="relative">
                    <input type="email" id="resetEmail" required 
                           class="w-full px-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-300"
                           placeholder="Enter your email address">
                </div>
                <div class="flex space-x-3">
                    <button type="button" onclick="closeForgotPassword()" class="flex-1 py-3 px-4 border border-white/20 rounded-xl text-white hover:bg-white/10 transition-all duration-300">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 py-3 px-4 bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-xl hover:from-indigo-600 hover:to-purple-700 transition-all duration-300">
                        Send Reset Link
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentAuthMethod = 'credentials';

        // Initialize the app
        document.addEventListener('DOMContentLoaded', function() {
            setupEventListeners();
        });

        function setupEventListeners() {
            // Auth method toggle
            document.querySelectorAll('[data-method]').forEach(button => {
                button.addEventListener('click', () => switchAuthMethod(button.dataset.method));
            });

            // Forgot password form
            document.getElementById('forgotPasswordForm').addEventListener('submit', handleForgotPassword);
        }

        function switchAuthMethod(method) {
            currentAuthMethod = method;
            
            // Update buttons
            document.querySelectorAll('[data-method]').forEach(btn => {
                if (btn.dataset.method === method) {
                    btn.classList.add('bg-white/20', 'text-white');
                    btn.classList.remove('text-white/70');
                } else {
                    btn.classList.remove('bg-white/20', 'text-white');
                    btn.classList.add('text-white/70');
                }
            });

            // Show/hide forms
            if (method === 'credentials') {
                document.getElementById('credentialsForm').classList.remove('hidden');
                document.getElementById('ssoForm').classList.add('hidden');
            } else {
                document.getElementById('credentialsForm').classList.add('hidden');
                document.getElementById('ssoForm').classList.remove('hidden');
            }
        }

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                `;
            } else {
                passwordInput.type = 'password';
                eyeIcon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                `;
            }
        }

        function showForgotPassword() {
            document.getElementById('forgotPasswordModal').classList.remove('hidden');
        }

        function closeForgotPassword() {
            document.getElementById('forgotPasswordModal').classList.add('hidden');
            document.getElementById('resetEmail').value = '';
        }

        async function handleForgotPassword(e) {
            e.preventDefault();
            const email = document.getElementById('resetEmail').value;
            
            if (!email) return;

            // For demo purposes only
            alert('Password reset functionality not implemented in demo');
            closeForgotPassword();
        }

        async function handleSSOLogin(provider) {
            alert(`${provider} SSO not implemented in this demo`);
        }
    </script>
</body>
</html>