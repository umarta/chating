const app = require('express')();
const server = require('http').createServer(app);
const io = require('socket.io')(server, {
    cors: {
        origin: ["http://chatting.test", "http://localhost:8000"],
        methods: ["GET", "POST"],
        credentials: true
    }
});

let onlineUsers = 0;

io.on('connection', (socket) => {
    onlineUsers++;
    io.emit('user-count', onlineUsers);

    socket.on('typing', (data) => {
        socket.broadcast.emit('typing', data);
    });

    socket.on('stop-typing', () => {
        socket.broadcast.emit('stop-typing');
    });

    socket.on('chat-message', (data) => {
        io.emit('chat-message', data);
    });

    socket.on('disconnect', () => {
        onlineUsers--;
        io.emit('user-count', onlineUsers);
    });
});

const PORT = process.env.SOCKET_PORT || 3000;
server.listen(PORT, () => {
    console.log(`Socket.IO server running on port ${PORT}`);
});
