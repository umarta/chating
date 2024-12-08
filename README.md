# Secure Real-time Chat Application

A secure real-time chat application built with Laravel, Firebase, and Socket.IO. Features include user authentication, real-time messaging, typing indicators, and online user tracking.

## Features

- User Authentication (Laravel Breeze)
- Real-time Messaging
- Typing Indicators
- Online User Tracking
- Message History
- Secure Firebase Integration
- Cross-Origin Resource Sharing (CORS) Support

## Prerequisites

- PHP >= 8.1
- Node.js >= 14
- Composer
- npm or yarn
- Firebase Account

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd chatting
```

2. Install PHP dependencies:
```bash
composer install
```

3. Install Node.js dependencies:
```bash
npm install
```

4. Copy the environment file:
```bash
cp .env.example .env
```

5. Generate application key:
```bash
php artisan key:generate
```

6. Configure Firebase:
   - Go to [Firebase Console](https://console.firebase.google.com)
   - Create a new project or select existing one
   - Go to Project Settings > Service Accounts
   - Click "Generate New Private Key"
   - Add the following variables to your `.env` file:
     ```
     FIREBASE_DATABASE_URL=https://your-project.firebaseio.com
     FIREBASE_PROJECT_ID=your-project-id
     FIREBASE_PRIVATE_KEY_ID=your-private-key-id
     FIREBASE_PRIVATE_KEY="your-private-key"
     FIREBASE_CLIENT_EMAIL=your-client-email
     FIREBASE_CLIENT_ID=your-client-id
     FIREBASE_CLIENT_CERT_URL=your-client-cert-url
     ```

7. Generate Firebase credentials file:
```bash
php artisan firebase:credentials
```

8. Set up Firebase Realtime Database rules:
```json
{
  "rules": {
    "messages": {
      ".read": "auth != null",
      ".write": "auth != null",
      ".indexOn": ["timestamp"],
      "$message": {
        ".validate": "newData.hasChildren(['userId', 'userName', 'text', 'timestamp'])"
      }
    }
  }
}
```

## Running the Application

1. Start the Laravel development server:
```bash
php artisan serve
```

2. Start the WebSocket server:
```bash
npm run socket
```

3. In a separate terminal, start Vite for frontend assets:
```bash
npm run dev
```

4. Visit http://localhost:8000 in your browser

## Usage

1. Register a new account or login with existing credentials
2. You'll be redirected to the chat page
3. Start sending messages
4. You'll see:
   - Real-time messages from other users
   - Typing indicators when someone is typing
   - Number of online users
   - Message history

## Development

### File Structure

- `app/Http/Controllers/ChatController.php`: Handles chat functionality
- `resources/views/chat.blade.php`: Chat interface
- `websocket-server.js`: WebSocket server configuration
- `routes/web.php`: Application routes
- `database.rules.json`: Firebase database rules

### Key Components

1. **Laravel Backend**
   - User authentication via Laravel Breeze
   - Firebase integration for message storage
   - API endpoints for message handling

2. **WebSocket Server**
   - Real-time message broadcasting
   - User presence tracking
   - Typing indicators

3. **Frontend**
   - Real-time updates via Socket.IO
   - Responsive chat interface
   - Message history loading

## Security

- All Firebase credentials are stored server-side
- CSRF protection enabled
- Authentication required for all chat features
- XSS prevention implemented
- Secure WebSocket connection

## Troubleshooting

1. **Messages not appearing in real-time**
   - Check WebSocket server is running
   - Verify Firebase credentials
   - Check browser console for errors

2. **Authentication issues**
   - Clear browser cache
   - Verify Laravel session configuration
   - Check `.env` configuration

3. **Firebase connection issues**
   - Verify credentials in `.env`
   - Check Firebase rules
   - Run `php artisan firebase:credentials`

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License.
