<?php
// File: database/run-chat-migrations-fixed.php
// Run this script to create all chat-related tables (FIXED VERSION)

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../bootstrap/app.php';
require_once __DIR__ . '/../bootstrap/database.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

echo "🚀 Running Chat System Migrations (Fixed Version)...\n\n";

$schema = Capsule::schema();

try {
    // First, let's check the current structure of the messages table
    echo "Checking messages table structure...\n";
    $messageColumns = $schema->getColumnListing('messages');
    echo "Current messages table columns: " . implode(', ', $messageColumns) . "\n\n";

    // 1. Create chats table
    if (!$schema->hasTable('chats')) {
        echo "Creating chats table...\n";
        $schema->create('chats', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('type', ['direct', 'group'])->default('direct');
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            $table->string('avatar_url')->nullable();
            $table->uuid('last_message_id')->nullable();
            $table->timestamp('last_message_at')->nullable();
            $table->json('settings')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            
            $table->index('type');
            $table->index('last_message_at');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
        echo "✅ chats table created\n\n";
    } else {
        echo "⏭️  chats table already exists\n\n";
    }

    // 2. Create chat_participants table
    if (!$schema->hasTable('chat_participants')) {
        echo "Creating chat_participants table...\n";
        $schema->create('chat_participants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('chat_id');
            $table->uuid('user_id');
            $table->enum('role', ['admin', 'member'])->default('member');
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_muted')->default(false);
            $table->integer('unread_count')->default(0);
            $table->timestamp('last_read_at')->nullable();
            $table->timestamp('joined_at')->default(Capsule::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('left_at')->nullable();
            $table->json('notification_settings')->nullable();
            $table->timestamps();
            
            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->unique(['chat_id', 'user_id']);
            $table->index(['user_id', 'is_pinned']);
            $table->index(['user_id', 'unread_count']);
        });
        echo "✅ chat_participants table created\n\n";
    } else {
        echo "⏭️  chat_participants table already exists\n\n";
    }

    // 3. Update messages table for chat support
    if ($schema->hasTable('messages')) {
        echo "Updating messages table for chat support...\n";
        
        $hasChanges = false;
        
        $schema->table('messages', function (Blueprint $table) use ($schema, &$hasChanges) {
            if (!$schema->hasColumn('messages', 'chat_id')) {
                $table->uuid('chat_id')->nullable()->after('id');
                $hasChanges = true;
            }
            
            if (!$schema->hasColumn('messages', 'reply_to_id')) {
                $table->uuid('reply_to_id')->nullable()->after('sender_id');
                $hasChanges = true;
            }
            
            // Check if we have 'body' or 'message' column
            $hasBody = $schema->hasColumn('messages', 'body');
            $hasMessage = $schema->hasColumn('messages', 'message');
            
            if (!$hasBody && $hasMessage) {
                // Rename 'message' to 'body'
                $table->renameColumn('message', 'body');
                $hasChanges = true;
            } elseif (!$hasBody && !$hasMessage) {
                // Add 'body' column if neither exists
                $table->text('body')->after('reply_to_id');
                $hasChanges = true;
            }
            
            if (!$schema->hasColumn('messages', 'type')) {
                // Add type column after determining the correct position
                if ($schema->hasColumn('messages', 'body')) {
                    $table->enum('type', ['text', 'image', 'file', 'system'])->default('text')->after('body');
                } else {
                    $table->enum('type', ['text', 'image', 'file', 'system'])->default('text');
                }
                $hasChanges = true;
            }
            
            if (!$schema->hasColumn('messages', 'is_edited')) {
                $table->boolean('is_edited')->default(false);
                $hasChanges = true;
            }
            
            if (!$schema->hasColumn('messages', 'edited_at')) {
                $table->timestamp('edited_at')->nullable();
                $hasChanges = true;
            }
            
            if (!$schema->hasColumn('messages', 'delivered_at')) {
                $table->timestamp('delivered_at')->nullable();
                $hasChanges = true;
            }
            
            if (!$schema->hasColumn('messages', 'deleted_at')) {
                $table->softDeletes();
                $hasChanges = true;
            }
        });
        
        // Modify recipient_id to be nullable if it exists
        if ($schema->hasColumn('messages', 'recipient_id')) {
            Capsule::statement('ALTER TABLE messages MODIFY recipient_id CHAR(36) NULL');
        }
        
        // Add indexes if they don't exist
        $existingIndexes = Capsule::select("SHOW INDEX FROM messages WHERE Key_name = 'messages_chat_id_index'");
        if (empty($existingIndexes) && $schema->hasColumn('messages', 'chat_id')) {
            $schema->table('messages', function (Blueprint $table) {
                $table->index('chat_id');
                $table->index(['chat_id', 'created_at']);
            });
        }
        
        // Add foreign keys if they don't exist and columns exist
        if ($schema->hasColumn('messages', 'chat_id')) {
            $foreignKeys = Capsule::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'messages' AND CONSTRAINT_NAME LIKE '%chat_id%' AND TABLE_SCHEMA = DATABASE()");
            if (empty($foreignKeys)) {
                $schema->table('messages', function (Blueprint $table) {
                    $table->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');
                });
            }
        }
        
        if ($schema->hasColumn('messages', 'reply_to_id')) {
            $foreignKeys = Capsule::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_NAME = 'messages' AND CONSTRAINT_NAME LIKE '%reply_to_id%' AND TABLE_SCHEMA = DATABASE()");
            if (empty($foreignKeys)) {
                $schema->table('messages', function (Blueprint $table) {
                    $table->foreign('reply_to_id')->references('id')->on('messages')->onDelete('set null');
                });
            }
        }
        
        echo $hasChanges ? "✅ messages table updated\n\n" : "⏭️  messages table already updated\n\n";
    } else {
        echo "❌ messages table not found - creating it...\n";
        $schema->create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('chat_id')->nullable();
            $table->uuid('sender_id');
            $table->uuid('recipient_id')->nullable();
            $table->uuid('reply_to_id')->nullable();
            $table->text('body');
            $table->enum('type', ['text', 'image', 'file', 'system'])->default('text');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_edited')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->boolean('is_archived')->default(false);
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('recipient_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');
            $table->foreign('reply_to_id')->references('id')->on('messages')->onDelete('set null');
            
            $table->index(['sender_id', 'created_at']);
            $table->index(['recipient_id', 'is_read']);
            $table->index('chat_id');
            $table->index(['chat_id', 'created_at']);
        });
        echo "✅ messages table created\n\n";
    }

    // 4. Create message_reactions table
    if (!$schema->hasTable('message_reactions')) {
        echo "Creating message_reactions table...\n";
        $schema->create('message_reactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('message_id');
            $table->uuid('user_id');
            $table->string('emoji', 10);
            $table->timestamps();
            
            $table->foreign('message_id')->references('id')->on('messages')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->unique(['message_id', 'user_id', 'emoji']);
            $table->index('message_id');
        });
        echo "✅ message_reactions table created\n\n";
    } else {
        echo "⏭️  message_reactions table already exists\n\n";
    }

    // 5. Create message_attachments table
    if (!$schema->hasTable('message_attachments')) {
        echo "Creating message_attachments table...\n";
        $schema->create('message_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('message_id');
            $table->enum('type', ['image', 'file', 'video', 'audio'])->default('file');
            $table->string('name');
            $table->string('url');
            $table->bigInteger('size')->unsigned();
            $table->string('mime_type')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->foreign('message_id')->references('id')->on('messages')->onDelete('cascade');
            $table->index('message_id');
        });
        echo "✅ message_attachments table created\n\n";
    } else {
        echo "⏭️  message_attachments table already exists\n\n";
    }

    // 6. Create typing_indicators table
    if (!$schema->hasTable('typing_indicators')) {
        echo "Creating typing_indicators table...\n";
        $schema->create('typing_indicators', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('chat_id');
            $table->uuid('user_id');
            $table->timestamp('started_at')->default(Capsule::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('expires_at');
            
            $table->foreign('chat_id')->references('id')->on('chats')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            $table->unique(['chat_id', 'user_id']);
            $table->index(['chat_id', 'expires_at']);
        });
        echo "✅ typing_indicators table created\n\n";
    } else {
        echo "⏭️  typing_indicators table already exists\n\n";
    }

    // 7. Add deleted_at to notifications table
    if ($schema->hasTable('notifications') && !$schema->hasColumn('notifications', 'deleted_at')) {
        echo "Adding deleted_at to notifications table...\n";
        $schema->table('notifications', function (Blueprint $table) {
            $table->softDeletes();
        });
        echo "✅ deleted_at added to notifications table\n\n";
    } else {
        echo "⏭️  notifications table already has deleted_at\n\n";
    }

    echo "✅ All chat system migrations completed successfully!\n\n";

    // Create some sample data
    echo "Would you like to create some sample chat data? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);
    
    if (trim($line) == 'y') {
        echo "\nCreating sample data...\n";
        
        // Get a few users
        $users = Capsule::table('users')->limit(3)->get();
        
        if (count($users) >= 2) {
            // Create a direct chat between first two users
            $chatId = (string) \Ramsey\Uuid\Uuid::uuid4();
            Capsule::table('chats')->insert([
                'id' => $chatId,
                'type' => 'direct',
                'created_by' => $users[0]->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Add participants
            foreach ([$users[0]->id, $users[1]->id] as $userId) {
                Capsule::table('chat_participants')->insert([
                    'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                    'chat_id' => $chatId,
                    'user_id' => $userId,
                    'role' => 'member',
                    'joined_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Add a sample message
            $messageId = (string) \Ramsey\Uuid\Uuid::uuid4();
            Capsule::table('messages')->insert([
                'id' => $messageId,
                'chat_id' => $chatId,
                'sender_id' => $users[1]->id,
                'body' => 'Hey! Welcome to the new chat system! 🎉',
                'type' => 'text',
                'delivered_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            // Update chat's last message
            Capsule::table('chats')->where('id', $chatId)->update([
                'last_message_id' => $messageId,
                'last_message_at' => now(),
            ]);
            
            // Update unread count for first user
            Capsule::table('chat_participants')
                ->where('chat_id', $chatId)
                ->where('user_id', $users[0]->id)
                ->update(['unread_count' => 1]);
            
            echo "✅ Created sample direct chat with welcome message\n";
            
            // Create a group chat if we have 3 users
            if (count($users) >= 3) {
                $groupChatId = (string) \Ramsey\Uuid\Uuid::uuid4();
                Capsule::table('chats')->insert([
                    'id' => $groupChatId,
                    'type' => 'group',
                    'name' => 'Team Chat',
                    'description' => 'General team discussion',
                    'created_by' => $users[0]->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                // Add all users to group
                foreach ($users as $index => $user) {
                    Capsule::table('chat_participants')->insert([
                        'id' => (string) \Ramsey\Uuid\Uuid::uuid4(),
                        'chat_id' => $groupChatId,
                        'user_id' => $user->id,
                        'role' => $index === 0 ? 'admin' : 'member',
                        'joined_at' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
                
                echo "✅ Created sample group chat 'Team Chat'\n";
            }
        } else {
            echo "⚠️  Not enough users to create sample chats\n";
        }
    }
    
    fclose($handle);

} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n🎉 Done!\n";