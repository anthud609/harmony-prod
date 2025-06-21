<?php
// File: app/Modules/IAM/Controllers/NotificationsController.php

namespace App\Modules\IAM\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Layout\LayoutManager;
use App\Core\Security\SessionManager;
use App\Core\Traits\LoggerTrait;
use App\Modules\IAM\Models\Notification;

class NotificationsController
{
    use LoggerTrait;

    protected LayoutManager $layoutManager;
    protected SessionManager $sessionManager;

    public function __construct(
        LayoutManager $layoutManager,
        SessionManager $sessionManager
    ) {
        $this->layoutManager = $layoutManager;
        $this->sessionManager = $sessionManager;
    }

    public function index(Request $request): Response
    {
        // Check if user is logged in
        if (!$this->sessionManager->isLoggedIn()) {
            return (new Response())->redirect('/login');
        }

        // Get user from session
        $user = $this->sessionManager->getUser();
        $userId = $user['id'] ?? null;

        // Get filter from query params
        $filter = $request->getQuery('filter', 'all');

        // Get notifications
        $notifications = $this->getNotifications($userId, $filter);
        $stats = $this->getNotificationStats($userId);

        // Prepare data for the view
        $data = [
            'title' => 'Notifications – Harmony HRMS',
            'pageTitle' => 'Notifications',
            'pageDescription' => 'Stay updated with your notifications',
            'notifications' => $notifications,
            'stats' => $stats,
            'currentFilter' => $filter,
            'helpLink' => 'https://docs.harmonyhrms.com/notifications',
            'pageId' => 'notifications',
            'isFavorite' => in_array('notifications', $user['favorites'] ?? []),
        ];

        // Set breadcrumbs and actions
        $breadcrumbs = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Notifications'],
        ];
        
        $pageActions = [
            [
                'label' => 'Mark all as read',
                'icon' => 'fas fa-check-double',
                'variant' => 'secondary',
                'onclick' => 'markAllNotificationsAsRead(); return false;',
            ],
            [
                'type' => 'dropdown',
                'id' => 'notification-settings',
                'label' => 'Settings',
                'icon' => 'fas fa-cog',
                'variant' => 'secondary',
                'items' => [
                    [
                        'label' => 'Email notifications',
                        'icon' => 'fas fa-envelope',
                        'url' => '/settings/notifications#email',
                    ],
                    [
                        'label' => 'Push notifications',
                        'icon' => 'fas fa-bell',
                        'url' => '/settings/notifications#push',
                    ],
                    'divider',
                    [
                        'label' => 'Clear all',
                        'icon' => 'fas fa-trash',
                        'onclick' => 'clearAllNotifications(); return false;',
                        'danger' => true,
                    ],
                ],
            ],
        ];

        // Capture layout output
        ob_start();
        $this->layoutManager
            ->setLayout('main')
            ->with($data)
            ->setBreadcrumbs($breadcrumbs)
            ->setPageActions($pageActions)
            ->render(__DIR__ . '/../Views/notifications.php');
        $content = ob_get_clean();

        return (new Response())
            ->setStatusCode(200)
            ->setHeader('Content-Type', 'text/html')
            ->setContent($content);
    }

    private function getNotifications(?string $userId, string $filter = 'all'): array
    {
        if (!$userId) {
            return [];
        }

        try {
            $query = Notification::where('user_id', $userId);

            // Apply filter
            switch ($filter) {
                case 'unread':
                    $query->where('is_read', false);
                    break;
                case 'starred':
                    $query->where('is_starred', true);
                    break;
                case 'archived':
                    $query->where('is_archived', true);
                    break;
                default:
                    $query->where('is_archived', false);
            }

            $notifications = $query->orderBy('created_at', 'desc')->paginate(20);

            return $notifications->map(function ($notification) {
                $displayData = $this->getDisplayData($notification->type);
                
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'icon' => $displayData['icon'],
                    'color' => $displayData['color'],
                    'title' => $this->getNotificationTitle($notification->type),
                    'message' => $notification->message,
                    'time' => $notification->created_at->diffForHumans(),
                    'timestamp' => $notification->created_at->format('Y-m-d H:i:s'),
                    'is_read' => $notification->is_read,
                    'is_starred' => $notification->is_starred ?? false,
                    'url' => $notification->url,
                    'data' => $notification->data ?? [],
                ];
            })->toArray();

        } catch (\Exception $e) {
            $this->logError('Failed to load notifications', [
                'user_id' => $userId,
                'filter' => $filter,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    private function getNotificationStats(?string $userId): array
    {
        if (!$userId) {
            return [
                'total' => 0,
                'unread' => 0,
                'starred' => 0,
                'today' => 0,
            ];
        }

        try {
            return [
                'total' => Notification::where('user_id', $userId)->count(),
                'unread' => Notification::where('user_id', $userId)->where('is_read', false)->count(),
                'starred' => Notification::where('user_id', $userId)->where('is_starred', true)->count(),
                'today' => Notification::where('user_id', $userId)
                    ->whereDate('created_at', now()->toDateString())
                    ->count(),
            ];
        } catch (\Exception $e) {
            $this->logError('Failed to get notification stats', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [
                'total' => 0,
                'unread' => 0,
                'starred' => 0,
                'today' => 0,
            ];
        }
    }

    private function getNotificationTitle(string $type): string
    {
        $titles = [
            'leave_approved' => 'Leave Request Approved',
            'leave_rejected' => 'Leave Request Rejected',
            'leave_pending' => 'Leave Request Pending',
            'meeting_scheduled' => 'Meeting Scheduled',
            'meeting_reminder' => 'Meeting Reminder',
            'birthday' => 'Birthday Reminder',
            'anniversary' => 'Work Anniversary',
            'new_team_member' => 'New Team Member',
            'payroll_processed' => 'Payroll Processed',
            'document_shared' => 'Document Shared',
            'task_assigned' => 'Task Assigned',
            'system_update' => 'System Update',
            'security_alert' => 'Security Alert',
        ];

        return $titles[$type] ?? 'Notification';
    }

    private function getDisplayData(string $type): array
    {
        $typeMap = [
            'leave_approved' => ['icon' => 'fas fa-check-circle', 'color' => 'green'],
            'leave_rejected' => ['icon' => 'fas fa-times-circle', 'color' => 'red'],
            'leave_pending' => ['icon' => 'fas fa-clock', 'color' => 'orange'],
            'meeting_scheduled' => ['icon' => 'fas fa-calendar-plus', 'color' => 'indigo'],
            'meeting_reminder' => ['icon' => 'fas fa-bell', 'color' => 'blue'],
            'birthday' => ['icon' => 'fas fa-birthday-cake', 'color' => 'pink'],
            'anniversary' => ['icon' => 'fas fa-gift', 'color' => 'purple'],
            'new_team_member' => ['icon' => 'fas fa-user-plus', 'color' => 'blue'],
            'payroll_processed' => ['icon' => 'fas fa-money-check-alt', 'color' => 'green'],
            'document_shared' => ['icon' => 'fas fa-share-alt', 'color' => 'indigo'],
            'task_assigned' => ['icon' => 'fas fa-tasks', 'color' => 'orange'],
            'system_update' => ['icon' => 'fas fa-sync-alt', 'color' => 'blue'],
            'security_alert' => ['icon' => 'fas fa-shield-alt', 'color' => 'red'],
            'default' => ['icon' => 'fas fa-info-circle', 'color' => 'gray']
        ];

        return $typeMap[$type] ?? $typeMap['default'];
    }
}