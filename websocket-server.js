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
        console.log('User connected:', userData);
    });

    // Handle chat message
    socket.on('chat_message', (message) => {
        console.log('Received message:', message);
        // Broadcast the message to all connected clients
        io.emit('chat_message', message);
    });

    // Handle typing status
    socket.on('typing', (data) => {
        console.log('User typing:', data);
        socket.broadcast.emit('typing', data);
    });

    // Handle stop typing
    socket.on('stop_typing', (data) => {
        console.log('User stopped typing:', data);
        socket.broadcast.emit('stop_typing', data);
    });

    // Handle disconnection
    socket.on('disconnect', () => {
        const userData = activeUsers.get(socket.id);
        activeUsers.delete(socket.id);
        io.emit('user_list', Array.from(activeUsers.values()));
        console.log('User disconnected:', userData);
    });
});

console.log('WebSocket server running on port 3000');
