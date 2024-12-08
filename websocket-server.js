import { Server } from 'socket.io';
const io = new Server(3000, {
    cors: {
        origin: ["http://chatting.test", "http://localhost:8000"],
        methods: ["GET", "POST"],
        credentials: true
    }
});

// Store active users
const activeUsers = new Map();

io.on('connection', (socket) => {
    console.log('A user connected');

    // Handle user joining
    socket.on('user_connected', (userData) => {
        activeUsers.set(socket.id, userData);
        io.emit('user_list', Array.from(activeUsers.values()));
    });

    // Handle chat message
    socket.on('chat_message', (message) => {
        io.emit('chat_message', {
            ...message,
            timestamp: Date.now()
        });
    });

    // Handle typing status
    socket.on('typing', (data) => {
        socket.broadcast.emit('typing', data);
    });

    // Handle disconnection
    socket.on('disconnect', () => {
        activeUsers.delete(socket.id);
        io.emit('user_list', Array.from(activeUsers.values()));
        console.log('A user disconnected');
    });
});

console.log('WebSocket server running on port 3000');
