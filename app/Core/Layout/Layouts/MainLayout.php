// File: app/Core/Layout/Layouts/MainLayout.php (Updated)
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
    <main id="mainContent" class=" lg:pl-64 transition-all duration-300">
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
    
    <?php if (isset($additionalScripts)): ?>
        <?= $additionalScripts ?>
    <?php endif; ?>
</body>
</html>
