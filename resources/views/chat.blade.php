<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Realtime Chat</title>
    <script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f0f2f5;
        }
        .chat-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .chat-header {
            padding: 20px;
            background: #075e54;
            color: white;
            border-radius: 10px 10px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .online-users {
            font-size: 0.9em;
            color: #dcf8c6;
        }
        .chat-messages {
            height: 400px;
            overflow-y: auto;
            padding: 20px;
        }
        .message {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 10px;
            max-width: 70%;
        }
        .message.sent {
            background: #dcf8c6;
            margin-left: auto;
        }
        .message.received {
            background: #e8e8e8;
        }
        .message .time {
            font-size: 0.8em;
            color: #666;
            margin-top: 5px;
        }
        .chat-input {
            padding: 20px;
            border-top: 1px solid #eee;
        }
        .chat-input form {
            display: flex;
            gap: 10px;
        }
        .chat-input input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 20px;
            outline: none;
        }
        .chat-input button {
            padding: 10px 20px;
            background: #075e54;
            color: white;
            border: none;
            border-radius: 20px;
            cursor: pointer;
        }
        .typing-indicator {
            padding: 10px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="chat-container">
        <div class="chat-header">
            <div>
                <h2>Realtime Chat</h2>
                <div>Welcome, {{ auth()->user()->name }}</div>
            </div>
            <div class="online-users" id="online-users">
                Online: 1
            </div>
        </div>
        <div class="chat-messages" id="messages">
            <!-- Messages will be inserted here -->
        </div>
        <div class="typing-indicator" id="typing-indicator"></div>
        <div class="chat-input">
            <form id="message-form">
                <input type="text" id="message-input" placeholder="Type a message..." autocomplete="off">
                <button type="submit">Send</button>
            </form>
        </div>
    </div>

    <script>
        // Current user info
        const currentUser = {
            id: '{{ auth()->id() }}',
            name: '{{ auth()->user()->name }}'
        };

        // Connect to WebSocket server
        const socket = io('http://localhost:3000');

        // DOM elements
        const messagesDiv = document.getElementById('messages');
        const messageForm = document.getElementById('message-form');
        const messageInput = document.getElementById('message-input');
        const typingIndicator = document.getElementById('typing-indicator');
        const onlineUsersDiv = document.getElementById('online-users');

        // Join chat
        socket.emit('user_connected', currentUser);

        // Listen for user list updates
        socket.on('user_list', (users) => {
            onlineUsersDiv.textContent = `Online: ${users.length}`;
        });

        // Listen for new messages
        socket.on('chat_message', (message) => {
            appendMessage(message);
        });

        // Listen for typing status
        socket.on('typing', (data) => {
            if (data.userId !== currentUser.id) {
                updateTypingIndicator(data);
            }
        });

        // Load existing messages
        async function loadMessages() {
            try {
                const response = await fetch('/api/chat/messages', {
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.error) {
                    throw new Error(data.error);
                }
                
                if (Array.isArray(data)) {
                    Object.values(data).forEach(message => appendMessage(message));
                } else if (data && typeof data === 'object') {
                    Object.values(data).forEach(message => appendMessage(message));
                }
                
                messagesDiv.scrollTop = messagesDiv.scrollHeight;
            } catch (error) {
                console.error('Error loading messages:', error);
                alert('Failed to load messages: ' + error.message);
            }
        }

        // Append a message to the chat
        function appendMessage(message) {
            const messageElement = document.createElement('div');
            messageElement.className = `message ${message.userId === currentUser.id ? 'sent' : 'received'}`;
            messageElement.innerHTML = `
                <div class="content">${escapeHtml(message.text)}</div>
                <div class="time">${message.userName} • ${new Date(message.timestamp).toLocaleTimeString()}</div>
            `;
            messagesDiv.appendChild(messageElement);
            messagesDiv.scrollTop = messagesDiv.scrollHeight;
        }

        // Escape HTML to prevent XSS
        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Handle typing indicator
        let typingTimeout;
        messageInput.addEventListener('input', () => {
            socket.emit('typing', {
                userId: currentUser.id,
                userName: currentUser.name,
                typing: true
            });

            clearTimeout(typingTimeout);
            typingTimeout = setTimeout(() => {
                socket.emit('typing', {
                    userId: currentUser.id,
                    userName: currentUser.name,
                    typing: false
                });
            }, 2000);
        });

        // Update typing indicator
        function updateTypingIndicator(data) {
            if (data.typing) {
                typingIndicator.textContent = `${data.userName} is typing...`;
            } else {
                typingIndicator.textContent = '';
            }
        }

        // Send a message
        async function sendMessage(text) {
            try {
                const response = await fetch('/api/chat/messages', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ text })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();
                
                if (data.error) {
                    throw new Error(data.error);
                }

                // Emit the message through WebSocket
                socket.emit('chat_message', data.message);
            } catch (error) {
                console.error('Error sending message:', error);
                alert('Failed to send message: ' + error.message);
            }
        }

        // Handle form submission
        messageForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const text = messageInput.value.trim();
            if (text) {
                await sendMessage(text);
                messageInput.value = '';
            }
        });

        // Load messages when the page loads
        document.addEventListener('DOMContentLoaded', loadMessages);
    </script>
</body>
</html>
