<?php
// File: app/Modules/IAM/Controllers/InboxController.php

namespace App\Modules\IAM\Controllers;

use App\Core\Http\Request;
use App\Core\Http\Response;
use App\Core\Layout\LayoutManager;
use App\Core\Security\SessionManager;
use App\Core\Traits\LoggerTrait;
use App\Modules\IAM\Models\Message;

class InboxController
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

        // Get messages
        $messages = $this->getMessages($userId);
        $stats = $this->getMessageStats($userId);

        // Prepare data for the view
        $data = [
            'title' => 'Inbox – Harmony HRMS',
            'pageTitle' => 'Inbox',
            'pageDescription' => 'View and manage your messages',
            'messages' => $messages,
            'stats' => $stats,
            'helpLink' => 'https://docs.harmonyhrms.com/inbox',
            'pageId' => 'inbox',
            'isFavorite' => in_array('inbox', $user['favorites'] ?? []),
        ];

        // Set breadcrumbs and actions
        $breadcrumbs = [
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Inbox'],
        ];
        
        $pageActions = [
            [
                'label' => 'Compose',
                'icon' => 'fas fa-plus',
                'variant' => 'primary',
                'onclick' => 'openComposeModal(); return false;',
            ],
            [
                'type' => 'dropdown',
                'id' => 'inbox-actions',
                'label' => 'Actions',
                'icon' => 'fas fa-ellipsis-h',
                'variant' => 'secondary',
                'items' => [
                    [
                        'label' => 'Mark all as read',
                        'icon' => 'fas fa-check-double',
                        'onclick' => 'markAllAsRead(); return false;',
                    ],
                    [
                        'label' => 'Archive read',
                        'icon' => 'fas fa-archive',
                        'onclick' => 'archiveRead(); return false;',
                    ],
                    'divider',
                    [
                        'label' => 'Empty trash',
                        'icon' => 'fas fa-trash',
                        'onclick' => 'emptyTrash(); return false;',
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
            ->render(__DIR__ . '/../Views/inbox.php');
        $content = ob_get_clean();

        return (new Response())
            ->setStatusCode(200)
            ->setHeader('Content-Type', 'text/html')
            ->setContent($content);
    }

    private function getMessages(?string $userId): array
    {
        if (!$userId) {
            return [];
        }

        try {
            $messages = Message::with('sender')
                ->where('recipient_id', $userId)
                ->where('is_archived', false)
                ->orderBy('created_at', 'desc')
                ->paginate(20);

            return $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'sender' => [
                        'name' => $message->sender->full_name,
                        'email' => $message->sender->email,
                        'avatar' => $this->getAvatar($message->sender),
                    ],
                    'subject' => $message->subject,
                    'preview' => $message->preview,
                    'body' => $message->body,
                    'time' => $message->created_at->diffForHumans(),
                    'timestamp' => $message->created_at->format('Y-m-d H:i:s'),
                    'is_read' => $message->is_read,
                    'is_starred' => $message->is_starred,
                    'has_attachments' => $message->has_attachments,
                    'attachments_count' => $message->attachments_count ?? 0,
                ];
            })->toArray();

        } catch (\Exception $e) {
            $this->logError('Failed to load messages', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    private function getMessageStats(?string $userId): array
    {
        if (!$userId) {
            return [
                'total' => 0,
                'unread' => 0,
                'starred' => 0,
                'archived' => 0,
            ];
        }

        try {
            return [
                'total' => Message::where('recipient_id', $userId)->count(),
                'unread' => Message::where('recipient_id', $userId)->where('is_read', false)->count(),
                'starred' => Message::where('recipient_id', $userId)->where('is_starred', true)->count(),
                'archived' => Message::where('recipient_id', $userId)->where('is_archived', true)->count(),
            ];
        } catch (\Exception $e) {
            $this->logError('Failed to get message stats', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return [
                'total' => 0,
                'unread' => 0,
                'starred' => 0,
                'archived' => 0,
            ];
        }
    }

    private function getAvatar($user): array
    {
        $gradients = [
            'from-green-400 to-blue-500',
            'from-purple-400 to-pink-500',
            'from-orange-400 to-red-500',
            'from-indigo-400 to-purple-500',
        ];
        
        $index = hexdec(substr(md5($user->id), 0, 2)) % count($gradients);
        
        return [
            'initials' => strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)),
            'gradient' => $gradients[$index],
        ];
    }
}