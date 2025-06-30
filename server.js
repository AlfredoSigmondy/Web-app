const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const cors = require('cors');

const app = express();
app.use(cors());
const server = http.createServer(app);
const io = socketIo(server, {
  cors: {
    origin: "*",
    methods: ["GET", "POST"]
  }
});

// Store active users and calls
const users = {};
const activeCalls = {};

io.on('connection', (socket) => {
  console.log('New connection:', socket.id);

  // Register user with their ID
  socket.on('register', (userId) => {
    users[userId] = socket.id;
    socket.userId = userId;
    console.log(`User ${userId} registered`);
    
    // Notify if they have pending calls
    if (activeCalls[userId]) {
      const call = activeCalls[userId];
      io.to(socket.id).emit('incoming-call', {
        callerId: call.callerId,
        callerName: call.callerName
      });
    }
  });

  // Initiate a call
  socket.on('initiate-call', ({ callerId, callerName, receiverId }) => {
    const receiverSocketId = users[receiverId];
    
    if (receiverSocketId) {
      // Store call information
      activeCalls[receiverId] = { 
        callerId, 
        callerName,
        receiverId,
        status: 'ringing'
      };
      
      // Notify receiver
      io.to(receiverSocketId).emit('incoming-call', { 
        callerId, 
        callerName 
      });
      
      console.log(`Call initiated from ${callerId} to ${receiverId}`);
    } else {
      // Receiver not connected
      io.to(socket.id).emit('call-error', {
        message: 'User is not available'
      });
    }
  });

  // Handle call response (accept/reject)
  socket.on('call-response', ({ callerId, accepted }) => {
    const callerSocketId = users[callerId];
    
    if (callerSocketId) {
      if (accepted) {
        // Update call status
        if (activeCalls[socket.userId]) {
          activeCalls[socket.userId].status = 'ongoing';
        }
        
        io.to(callerSocketId).emit('call-accepted');
        console.log(`Call accepted by ${socket.userId}`);
      } else {
        // Call rejected
        io.to(callerSocketId).emit('call-rejected');
        delete activeCalls[socket.userId];
        console.log(`Call rejected by ${socket.userId}`);
      }
    }
  });

  // Handle call end
  socket.on('end-call', ({ targetUserId }) => {
    const targetSocketId = users[targetUserId];
    if (targetSocketId) {
      io.to(targetSocketId).emit('call-ended');
    }
    
    // Clean up call records
    if (activeCalls[targetUserId]) delete activeCalls[targetUserId];
    if (activeCalls[socket.userId]) delete activeCalls[socket.userId];
    
    console.log(`Call ended by ${socket.userId}`);
  });

  // WebRTC signaling
  socket.on('signal', (data) => {
    const targetSocketId = users[data.to];
    if (targetSocketId) {
      io.to(targetSocketId).emit('signal', {
        ...data,
        from: socket.userId
      });
    }
  });

  // Handle disconnection
  socket.on('disconnect', () => {
    if (socket.userId) {
      // End any ongoing calls
      if (activeCalls[socket.userId]) {
        const call = activeCalls[socket.userId];
        const otherUserId = call.callerId === socket.userId ? call.receiverId : call.callerId;
        const otherSocketId = users[otherUserId];
        
        if (otherSocketId) {
          io.to(otherSocketId).emit('call-ended');
        }
        
        delete activeCalls[socket.userId];
      }
      
      delete users[socket.userId];
      console.log(`User ${socket.userId} disconnected`);
    }
  });
});

const PORT = process.env.PORT || 3000;
server.listen(PORT, () => {
  console.log(`Signaling server running on port ${PORT}`);
});