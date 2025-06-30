<?php
session_start();
if (!isset($_SESSION['patient_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Patient Chat - eMedConnect</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="Message.css">
  <script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-app-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-database-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/9.6.10/firebase-storage-compat.js"></script>
  <script src="https://cdn.socket.io/4.5.4/socket.io.min.js"></script>
  <style>
    /* Notification styles */
    .notification-badge {
      position: absolute;
      top: -5px;
      right: -5px;
      background-color: #ff4444;
      color: white;
      border-radius: 50%;
      width: 18px;
      height: 18px;
      font-size: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .notification-container {
      position: relative;
      display: inline-block;
    }
    .notification-dropdown {
      position: absolute;
      right: 0;
      top: 100%;
      width: 350px;
      max-height: 400px;
      overflow-y: auto;
      background: white;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      z-index: 2000;
      display: none;
      padding: 10px;
    }
    .notification-item {
      padding: 10px;
      border-bottom: 1px solid #eee;
      cursor: pointer;
    }
    .notification-item:hover {
      background-color: #f8f9fa;
    }
    .notification-item.unread {
      background-color: #f1f8ff;
    }
    .notification-time {
      font-size: 0.8rem;
      color: #6c757d;
    }
    .notification-sender {
      font-weight: 600;
      color: #2eb872;
    }
    .notification-message {
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .toast-notification {
      position: fixed;
      bottom: 20px;
      right: 20px;
      padding: 12px 16px;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      z-index: 2000;
      display: flex;
      align-items: center;
      gap: 10px;
      transform: translateX(150%);
      transition: transform 0.3s ease;
    }
    .toast-notification.show {
      transform: translateX(0);
    }
    .toast-icon {
      font-size: 1.2rem;
      color: #2eb872;
    }
    
    /* Enhanced Video Call Styles */
    #videoCallArea {
      display: none;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 800px;
      max-width: 90vw;
      height: 600px;
      background: #f0f4f8;
      border-radius: 16px;
      padding: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
      z-index: 2000;
      flex-direction: column;
    }
    
    .video-container {
      display: flex;
      height: 80%;
      position: relative;
    }
    
    #remoteVideo {
      width: 100%;
      height: 100%;
      border-radius: 12px;
      background: #000;
      object-fit: cover;
    }
    
    #localVideo {
      width: 200px;
      height: 150px;
      border-radius: 12px;
      border: 3px solid #2eb872;
      position: absolute;
      bottom: 20px;
      right: 20px;
      z-index: 10;
      background: #000;
    }
    
    .call-controls {
      display: flex;
      justify-content: center;
      gap: 15px;
      margin-top: 20px;
    }
    
    .call-controls button {
      width: 60px;
      height: 60px;
      border-radius: 50%;
      font-size: 1.5rem;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    #endCallBtn {
      background: #e74c3c !important;
    }
    
    /* Incoming call modal */
    #incomingCallModal {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.8);
      z-index: 3000;
      display: none;
      justify-content: center;
      align-items: center;
    }
    
    #incomingCallModal > div {
      background: white;
      padding: 40px;
      border-radius: 20px;
      text-align: center;
      max-width: 500px;
      width: 90%;
      box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    }
    
    #incomingCallTitle {
      color: #2eb872;
      font-size: 2rem;
      margin-bottom: 10px;
    }
    
    #incomingCallerName {
      font-size: 1.5rem;
      margin: 30px 0;
      color: #333;
    }
    
    /* Chat popup styles */
    #chatPopup {
      display: none;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 400px;
      height: 600px;
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 0 24px #aaa;
      z-index: 1000;
      flex-direction: column;
      cursor: move;
    }
    
    #chatHeader {
      background: #2eb872;
      color: #fff;
      padding: 18px 24px;
      border-radius: 12px 12px 0 0;
      font-size: 1.15rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      user-select: none;
    }
    
    #chatMessages {
      flex: 1;
      overflow-y: auto;
      padding: 16px;
      height: 420px;
      background: #f7fafc;
    }
    
    /* Contact card styles */
    .contact-card {
      cursor: pointer;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .contact-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(0,0,0,0.1);
    }
    
    /* File and camera features */
    .chat-form-container {
      display: flex;
      align-items: center;
      padding: 18px;
      background: #f7fafc;
      border-radius: 0 0 12px 12px;
      gap: 10px;
    }
    
    .attachment-btn {
      background: none;
      border: none;
      font-size: 1.3rem;
      color: #666;
      cursor: pointer;
      padding: 8px;
      transition: color 0.2s;
    }
    
    .attachment-btn:hover {
      color: #2eb872;
    }
    
    .message-image {
      max-width: 250px;
      max-height: 250px;
      border-radius: 12px;
      margin: 4px 0;
      cursor: pointer;
    }
    
    .message-file {
      max-width: 200px;
      word-break: break-all;
      padding: 8px;
      background: #f0f0f0;
      border-radius: 8px;
      display: flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
      color: #333;
    }
    
    .message-file:hover {
      background: #e0e0e0;
    }
    
    #camera-modal {
      display: none;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: #fff;
      padding: 16px;
      border-radius: 12px;
      box-shadow: 0 0 24px #aaa;
      z-index: 2000;
      flex-direction: column;
      align-items: center;
      gap: 10px;
    }
    
    #camera-video {
      width: 320px;
      height: 240px;
      background: black;
      border-radius: 8px;
    }
    
    .camera-controls {
      display: flex;
      gap: 10px;
      justify-content: center;
    }
  </style>
</head>
<body>
<div class="d-flex">
  <!-- Sidebar -->
  <?php include_once __DIR__ . '/../SideBar/Sidebar.php'; ?>
  <!-- Main Content -->
  <div class="flex-grow-1 p-4">
    <div class="d-flex align-items-center mb-4">
      <input type="text" class="form-control" placeholder="Search Doctors Here" style="max-width:350px;">
      <div class="notification-container ms-2">
        <button class="btn btn-light" id="notificationBell"><i class="bi bi-bell"></i></button>
        <div class="notification-badge" id="messageBadge" style="display:none;">0</div>
        <div class="notification-dropdown" id="notificationDropdown">
          <div class="d-flex justify-content-between align-items-center p-2 border-bottom">
            <h6 class="m-0">Notifications</h6>
            <button id="clearNotifications" class="btn btn-sm btn-link">Clear All</button>
          </div>
          <div id="notificationList"></div>
        </div>
        <audio id="notificationSound" src="notification.mp3" preload="auto"></audio>
      </div>
    </div>
    <!-- Doctor List -->
    <div id="contactList">
      <?php
      include_once __DIR__ . '../../database/conection_db.php';
      if (!$conn) {
          die("Database connection failed: " . mysqli_connect_error());
      }
      $sql = "SELECT MedicID, Username, COALESCE(Specialization, 'General') AS Specialization FROM doctors WHERE Status = 'Approved'";
      $result = $conn->query($sql);
      $doctors = [];
      if ($result && $result->num_rows > 0) {
          while ($row = $result->fetch_assoc()) {
              $doctors[$row['MedicID']] = $row;
          }
      }
      ?>
      <div id="sortedContactList">
        <?php foreach ($doctors as $doctorId => $doctor): ?>
          <div class="contact-card d-flex align-items-center p-3 mb-2 bg-white shadow-sm"
               data-doctor="<?= htmlspecialchars($doctorId) ?>"
               data-doctor-name="<?= htmlspecialchars($doctor['Username']) ?>">
            <div style="width:48px;height:48px;border-radius:50%;background:#2eb872;display:flex;align-items:center;justify-content:center;margin-right:16px;">
              <i class="bi bi-person" style="font-size:1.5rem;color:#fff;"></i>
            </div>
            <div style="flex-grow:1;">
              <div style="font-weight:600;"><?= htmlspecialchars($doctor['Username']) ?></div>
              <div style="font-size:0.95rem;color:#666;"><?= htmlspecialchars($doctor['Specialization'] ?? 'General') ?></div>
            </div>
            <button class="btn btn-success btn-sm startCallBtn">Start Call</button>
          </div>
        <?php endforeach; ?>
      </div>
      <?php if (empty($doctors)): ?>
        <div class="text-muted">No doctors found.</div>
      <?php endif; ?>
    </div>
  </div>
  <?php include_once __DIR__ . '/../SideBar_Right/SidebarRight.php'; ?>
</div>

<!-- Chat Popup -->
<div id="chatPopup">
  <div id="chatHeader">
    <span id="chatWith" style="font-weight:600;">Chat</span>
    <button id="closeChat" style="background:none;border:none;color:#fff;font-size:1.7rem;">&times;</button>
  </div>
  <div id="chatMessages"></div>
  <div class="chat-form-container">
    <input type="file" id="fileInput" style="display:none;" accept="image/*,video/*,.pdf,.doc,.docx">
    <button class="attachment-btn" id="fileBtn" title="Attach File"><i class="bi bi-paperclip"></i></button>
    <button class="attachment-btn" id="cameraBtn" title="Take Photo"><i class="bi bi-camera"></i></button>
    <input type="text" id="chatInput" class="form-control" placeholder="Type a message..." autocomplete="off">
    <button class="btn btn-primary" id="sendBtn">Send</button>
  </div>
</div>

<!-- Camera Modal -->
<div id="camera-modal">
  <video id="camera-video" autoplay></video>
  <div class="camera-controls">
    <button id="capture-btn" class="btn btn-primary">Capture</button>
    <button id="cancel-camera-btn" class="btn btn-secondary">Cancel</button>
  </div>
</div>

<!-- Video Call Area -->
<div id="videoCallArea">
  <div class="video-container">
    <video id="remoteVideo" autoplay playsinline></video>
    <video id="localVideo" autoplay muted playsinline></video>
  </div>
  <div class="call-controls">
    <button id="startCallBtn" class="btn btn-success"><i class="bi bi-camera-video"></i></button>
    <button id="endCallBtn" class="btn btn-danger" disabled><i class="bi bi-telephone-x"></i></button>
    <button id="muteAudioBtn" class="btn btn-secondary"><i class="bi bi-mic-fill"></i></button>
    <button id="muteVideoBtn" class="btn btn-secondary"><i class="bi bi-camera-video-fill"></i></button>
  </div>
</div>

<!-- Incoming Call Modal -->
<div id="incomingCallModal">
  <div>
    <h3 id="incomingCallTitle">Incoming Call</h3>
    <p id="incomingCallerName"></p>
    <div style="display:flex; justify-content:center; gap:20px;">
      <button id="acceptCallBtn" class="btn btn-success btn-lg" style="padding:10px 30px;">
        <i class="bi bi-telephone"></i> Accept
      </button>
      <button id="rejectCallBtn" class="btn btn-danger btn-lg" style="padding:10px 30px;">
        <i class="bi bi-telephone-x"></i> Reject
      </button>
    </div>
  </div>
</div>

<!-- Toast Notification -->
<div id="toastNotification" class="toast-notification">
  <div class="toast-icon"><i class="bi bi-chat-dots"></i></div>
  <div>
    <div id="toastSender" class="notification-sender"></div>
    <div id="toastMessage" class="notification-message"></div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// --- Firebase Configuration ---
const firebaseConfig = {
  apiKey: "AIzaSyCAPuzjbS-KYjIB12TL4i1Db-uKPEhSHEI",
  authDomain: "emed-6b602.firebaseapp.com",
  projectId: "emed-6b602",
  storageBucket: "emed-6b602.appspot.com",
  messagingSenderId: "459401321363",
  appId: "1:459401321363:web:0748fdea27a15e48ec6666",
  measurementId: "G-1D3LYMXK5T"
};
firebase.initializeApp(firebaseConfig);
const db = firebase.database();
const storage = firebase.storage();

// --- Socket.io Connection ---
const socket = io('http://localhost:3000', {
  autoConnect: false
});

// --- User Information ---
const sender_id = "<?= $_SESSION['patient_id'] ?? '' ?>";
let receiver_id = null;
let chatRoom = null;
let receiver_name = "";
let callInProgress = false;
let incomingCallData = null;
let cameraStream = null;

// --- DOM Elements ---
const chatPopup = document.getElementById('chatPopup');
const chatHeader = document.getElementById('chatHeader');
const chatMessages = document.getElementById('chatMessages');
const chatInput = document.getElementById('chatInput');
const closeChat = document.getElementById('closeChat');
const chatWith = document.getElementById('chatWith');
const videoCallArea = document.getElementById('videoCallArea');
const localVideo = document.getElementById('localVideo');
const remoteVideo = document.getElementById('remoteVideo');
const startCallBtn = document.getElementById('startCallBtn');
const endCallBtn = document.getElementById('endCallBtn');
const muteAudioBtn = document.getElementById('muteAudioBtn');
const muteVideoBtn = document.getElementById('muteVideoBtn');
const incomingCallModal = document.getElementById('incomingCallModal');
const incomingCallerName = document.getElementById('incomingCallerName');
const acceptCallBtn = document.getElementById('acceptCallBtn');
const rejectCallBtn = document.getElementById('rejectCallBtn');
const fileInput = document.getElementById('fileInput');
const fileBtn = document.getElementById('fileBtn');
const cameraBtn = document.getElementById('cameraBtn');
const sendBtn = document.getElementById('sendBtn');
const cameraModal = document.getElementById('camera-modal');
const cameraVideo = document.getElementById('camera-video');
const captureBtn = document.getElementById('capture-btn');
const cancelCameraBtn = document.getElementById('cancel-camera-btn');

// --- Notification System ---
let notifications = JSON.parse(localStorage.getItem('notifications')) || [];
let shownNotificationIds = new Set(notifications.map(n => n.id));
let unreadCount = notifications.filter(n => !n.read).length;
const notificationDropdown = document.getElementById('notificationDropdown');
const notificationList = document.getElementById('notificationList');
const clearNotifications = document.getElementById('clearNotifications');
const toastNotification = document.getElementById('toastNotification');
const toastSender = document.getElementById('toastSender');
const toastMessage = document.getElementById('toastMessage');
const notificationSound = document.getElementById('notificationSound');
const messageBadge = document.getElementById('messageBadge');

// --- Video Call Variables ---
let localStream = null;
let peerConnection = null;
let isAudioMuted = false;
let isVideoMuted = false;

// ICE Configuration
const iceConfig = {
  iceServers: [
    { urls: "stun:stun.l.google.com:19302" }
  ]
};

// --- Utility Functions ---
function generateNotificationId(senderId, message, timestamp) {
  return `${senderId}_${message.substring(0, 20)}_${timestamp}`;
}

function updateBadge() {
  messageBadge.textContent = unreadCount > 9 ? '9+' : unreadCount;
  messageBadge.style.display = unreadCount > 0 ? 'flex' : 'none';
}

function formatTime(timestamp) {
  const date = new Date(timestamp);
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
}

function playNotificationSound() {
  try {
    notificationSound.currentTime = 0;
    notificationSound.play().catch(e => console.log("Notification Formats: [markdown, html, text]"));
  } catch (e) {
    console.log("Notification sound error:", e);
    // Fallback for browsers that don't support the play method
    notificationSound.load();
    notificationSound.play().catch(e => console.log("Notification sound fallback error:", e));
  }
}

function showDesktopNotification(senderName, message) {
  if (Notification.permission === "granted") {
    const notification = new Notification(`New message from ${senderName}`, {
      body: message,
      icon: 'https://example.com/notification-icon.png'
    });
    setTimeout(() => notification.close(), 5000);
  }
}

function getFileIcon(fileName) {
  const ext = fileName.split('.').pop().toLowerCase();
  if (['jpg', 'jpeg', 'png', 'gif'].includes(ext)) return 'bi bi-image';
  if (['pdf'].includes(ext)) return 'bi bi-file-pdf';
  if (['doc', 'docx'].includes(ext)) return 'bi bi-file-word';
  return 'bi bi-file';
}

// --- File Handling Functions ---
async function handleFileUpload(file) {
  if (!file || !chatRoom) return;
  
  const storageRef = storage.ref(`chat_files/${chatRoom}/${Date.now()}_${file.name}`);
  try {
    const snapshot = await storageRef.put(file);
    const downloadURL = await snapshot.ref.getDownloadURL();
    
    db.ref('chats/' + chatRoom).push({
      sender_id: sender_id,
      receiver_id: receiver_id,
      type: 'file',
      file_url: downloadURL,
      file_name: file.name,
      sent_at: Date.now()
    });
  } catch (error) {
    console.error('File upload error:', error);
    alert('Failed to upload file');
  }
}

async function startCamera() {
  try {
    cameraStream = await navigator.mediaDevices.getUserMedia({ video: true });
    cameraVideo.srcObject = cameraStream;
    cameraModal.style.display = 'flex';
  } catch (error) {
    console.error('Camera access error:', error);
    alert('Failed to access camera');
  }
}

function stopCamera() {
  if (cameraStream) {
    cameraStream.getTracks().forEach(track => track.stop());
    cameraStream = null;
    cameraVideo.srcObject = null;
  }
  cameraModal.style.display = 'none';
}

async function capturePhoto() {
  const canvas = document.createElement('canvas');
  canvas.width = cameraVideo.videoWidth;
  canvas.height = cameraVideo.videoHeight;
  canvas.getContext('2d').drawImage(cameraVideo, 0, 0);
  
  canvas.toBlob(async (blob) => {
    const file = new File([blob], `photo_${Date.now()}.jpg`, { type: 'image/jpeg' });
    await handleFileUpload(file);
    stopCamera();
  }, 'image/jpeg');
}

// --- Notification Functions ---
function initNotifications() {
  updateBadge();
  renderNotifications();
  
  if (Notification.permission !== "granted") {
    Notification.requestPermission().then(permission => {
      if (permission === "granted") console.log("Notification permission granted");
    });
  }
  
  db.ref('chats').on('child_added', function(roomSnapshot) {
    const roomName = roomSnapshot.key;
    if (roomName.includes(sender_id)) {
      roomSnapshot.ref.on('child_added', function(messageSnapshot) {
        const data = messageSnapshot.val();
        if (data.receiver_id === sender_id && data.sender_id !== sender_id) {
          const isFromCurrentChat = chatRoom && chatRoom.includes(data.sender_id);
          if (!isFromCurrentChat) {
            const doctorCard = document.querySelector(`.contact-card[data-doctor="${data.sender_id}"]`);
            let doctorName = 'Unknown Doctor';
            if (doctorCard) {
              doctorName = doctorCard.getAttribute('data-doctor-name');
            } else if (doctors && doctors[data.sender_id] && doctors[data.sender_id].Username) {
              doctorName = doctors[data.sender_id].Username;
            }
            storeNotification(data.sender_id, doctorName, data.message || 'Sent a file');
          }
        }
      });
    }
  });
}

function renderNotifications() {
  notificationList.innerHTML = '';
  if (notifications.length === 0) {
    notificationList.innerHTML = '<div class="text-muted p-2 text-center">No notifications</div>';
    return;
  }
  
  const sortedNotifications = [...notifications].reverse();
  sortedNotifications.forEach((notification, index) => {
    const notificationItem = document.createElement('div');
    notificationItem.className = `notification-item ${notification.read ? '' : 'unread'}`;
    notificationItem.innerHTML = `
      <div class="d-flex justify-content-between">
        <span class="notification-sender">${notification.sender}</span>
        <span class="notification-time">${formatTime(notification.time)}</span>
      </div>
      <div class="notification-message">${notification.message}</div>
    `;
    
    notificationItem.addEventListener('click', () => {
      if (!notification) {
        notification.read = true;
        notifications[notifications.length - 1 - index].read = true;
        localStorage.setItem('notifications', JSON.stringify(notifications));
        unreadCount--;
        updateBadge();
      }
      openChatForNotification(notification.senderId);
    });
    
    notificationList.appendChild(notificationItem);
  });
}

function storeNotification(senderId, senderName, message) {
  const timestamp = Date.now();
  const notificationId = generateNotificationId(senderId, message, timestamp);
  
  if (shownNotificationIds.has(notificationId)) return;
  
  shownNotificationIds.add(notificationId);
  const newNotification = {
    senderId,
    sender: senderName,
    message,
    time: timestamp,
    read: false,
    id: notificationId
  };
  
  notifications.push(newNotification);
  localStorage.setItem('notifications', JSON.stringify(notifications));
  unreadCount++;
  updateBadge();
  
  playNotificationSound();
  showDesktopNotification(senderName, message);
  showToastNotification(senderName, message);
}

function showToastNotification(sender, message) {
  toastSender.textContent = sender;
  toastMessage.textContent = message;
  toastNotification.classList.add('show');
  setTimeout(() => toastNotification.classList.remove('show'), 5000);
}

function openChatForNotification(senderId) {
  const contactCard = document.querySelector(`.contact-card[data-doctor="${senderId}"]`);
  if (contactCard) contactCard.click();
  hideNotificationDropdown();
}

function showNotificationDropdown() {
  notificationDropdown.style.display = 'block';
  if (unreadCount > 0) {
    notifications.forEach(n => n.read = true);
    localStorage.setItem('notifications', JSON.stringify(notifications));
    unreadCount = 0;
    updateBadge();
  }
}

function hideNotificationDropdown() {
  notificationDropdown.style.display = 'none';
}

function clearAllNotifications() {
  notifications = [];
  shownNotificationIds.clear();
  unreadCount = 0;
  localStorage.setItem('notifications', JSON.stringify(notifications));
  updateBadge();
  renderNotifications();
}

// --- Call Handling Functions ---
function showIncomingCallModal(callerName) {
  incomingCallerName.textContent = callerName;
  incomingCallModal.style.display = 'flex';
  
  acceptCallBtn.onclick = () => {
    callInProgress = true;
    receiver_id = incomingCallData.callerId;
    socket.emit('call-response', {
      callerId: incomingCallData.callerId,
      accepted: true
    });
    hideIncomingCallModal();
    startVideoCall();
  };
  
  rejectCallBtn.onclick = () => {
    socket.emit('call-response', {
      callerId: incomingCallData.callerId,
      accepted: false
    });
    hideIncomingCallModal();
    incomingCallData = null;
  };
}

function hideIncomingCallModal() {
  incomingCallModal.style.display = 'none';
}

// --- Chat Functions ---
let isDragging = false, dragOffsetX = 0, dragOffsetY = 0;
chatHeader.onmousedown = function(e) {
  isDragging = true;
  const rect = chatPopup.getBoundingClientRect();
  dragOffsetX = e.clientX - rect.left;
  dragOffsetY = e.clientY - rect.top;
  document.body.style.userSelect = "none";
};

document.onmousemove = function(e) {
  if (isDragging) {
    chatPopup.style.left = (e.clientX - dragOffsetX) + "px";
    chatPopup.style.top = (e.clientY - dragOffsetY) + "px";
    chatPopup.style.transform = "none";
  }
};

document.onmouseup = function() {
  isDragging = false;
  document.body.style.userSelect = "";
};

document.addEventListener('DOMContentLoaded', function() {
  // Attach click event to each contact card
  document.querySelectorAll('.contact-card').forEach(function(contactCard) {
    contactCard.onclick = function(e) {
      if(e.target.classList.contains('startCallBtn')) return;

      receiver_id = this.getAttribute('data-doctor');
      receiver_name = this.getAttribute('data-doctor-name');
      chatRoom = [sender_id, receiver_id].sort().join("_");
      chatWith.textContent = receiver_name;
      chatPopup.style.display = 'flex';
      chatPopup.style.left = "50%";
      chatPopup.style.top = "50%";
      chatPopup.style.transform = "translate(-50%, -50%)";
      chatMessages.innerHTML = '';

      socket.connect();
      socket.emit('register', sender_id);

      db.ref('chats/' + chatRoom).off();
      db.ref('chats/' + chatRoom).on('child_added', function(snapshot) {
        const data = snapshot.val();
        const msg = document.createElement('div');
        const time = new Date(data.sent_at || Date.now());
        const timeStr = time.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});

        if (data.type === 'file') {
          const isImage = ['jpg', 'jpeg', 'png', 'gif'].includes(data.file_name.split('.').pop().toLowerCase());
          msg.innerHTML = `
            <span style="display:block;font-size:0.97rem;${data.sender_id === sender_id ? 'color:#14532d;font-weight:500;text-align:right;' : 'color:#2eb872;font-weight:500;text-align:left;'}">
              ${data.sender_id === sender_id ? "Me" : receiver_name}
            </span>
            ${isImage ? 
              `<a href="${data.file_url}" target="_blank"><img src="${data.file_url}" class="message-image" alt="Shared image"></a>` :
              `<a href="${data.file_url}" target="_blank" class="message-file"><i class="${getFileIcon(data.file_name)}"></i>${data.file_name}</a>`
            }
            <span style="display:block;font-size:0.85rem;color:#888;${data.sender_id === sender_id ? 'text-align:right;' : 'text-align:left;'}">${timeStr}</span>
            <div style="clear:both"></div>`;
        } else {
          msg.innerHTML = `
            <span style="display:block;font-size:0.97rem;${data.sender_id === sender_id ? 'color:#14532d;font-weight:500;text-align:right;' : 'color:#2eb872;font-weight:500;text-align:left;'}">
              ${data.sender_id === sender_id ? "Me" : receiver_name}
            </span>
            <span style="display:inline-block;padding:10px 16px;border-radius:18px;max-width:70%;margin:2px 0 2px 0;background:${data.sender_id === sender_id ? '#e0f7ef' : '#fff'};color:#222;font-size:1.08rem;${data.sender_id === sender_id ? 'float:right;' : 'float:left;'}">
              ${data.message}
            </span>
            <span style="display:block;font-size:0.85rem;color:#888;${data.sender_id === sender_id ? 'text-align:right;' : 'text-align:left;'}">${timeStr}</span>
            <div style="clear:both"></div>`;
        }
        
        chatMessages.appendChild(msg);
        chatMessages.scrollTop = chatMessages.scrollHeight;
      });
      chatInput.focus();
      
      // Add call button if not present
      if (!document.getElementById('startCallHeaderBtn')) {
        const callBtn = document.createElement('button');
        callBtn.id = 'startCallHeaderBtn';
        callBtn.className = 'btn btn-success ms-2';
        callBtn.innerHTML = '<i class="bi bi-camera-video"></i> Start Call';
        callBtn.onclick = (e) => {
          e.stopPropagation();
          if (!receiver_id) return;
          
          socket.emit('initiate-call', {
            callerId: sender_id,
            callerName: "Patient",
            receiverId: receiver_id
          });
          
          callInProgress = true;
          startVideoCall();
        };
        chatHeader.appendChild(callBtn);
      }
    };
    
    // Start Call button event
    contactCard.querySelector('.startCallBtn').onclick = (e) => {
      e.stopPropagation();
      startCall(contactCard.getAttribute('data-doctor'), contactCard.getAttribute('data-doctor-name'));
    };
  });
  
  db.ref('chats').once('value').then(snapshot => {
    snapshot.forEach(roomSnapshot => {
      const roomName = roomSnapshot.key;
      if (roomName.includes(sender_id)) {
        roomSnapshot.forEach(messageSnapshot => {
          const data = messageSnapshot.val();
          const otherUserId = data.sender_id === sender_id ? data.receiver_id : data.sender_id;
          lastMessageTimestamps[otherUserId] = data.sent_at || Date.now();
        });
      }
    });
  });
  
  initNotifications();
});

closeChat.onclick = () => {
  chatPopup.style.display = 'none';
  if (socket.connected) socket.disconnect();
};

fileBtn.onclick = () => fileInput.click();

let selectedFile = null;

fileInput.onchange = async (e) => {
  if (e.target.files[0]) {
    selectedFile = e.target.files[0];
    chatInput.value = selectedFile.name;
  }
};

sendBtn.onclick = async (e) => {
  e.preventDefault();
  if (selectedFile && chatRoom) {
    await handleFileUpload(selectedFile);
    selectedFile = null;
    chatInput.value = '';
    fileInput.value = '';
  } else if (chatInput.value.trim() !== '' && chatRoom) {
    db.ref('chats/' + chatRoom).push({
      sender_id: sender_id,
      receiver_id: receiver_id,
      message: chatInput.value,
      sent_at: Date.now()
    });
    chatInput.value = '';
  }
};

cameraBtn.onclick = startCamera;

captureBtn.onclick = capturePhoto;

cancelCameraBtn.onclick = stopCamera;

sendBtn.onclick = (e) => {
  e.preventDefault();
  if (chatInput.value.trim() !== '' && chatRoom) {
    db.ref('chats/' + chatRoom).push({
      sender_id: sender_id,
      receiver_id: receiver_id,
      message: chatInput.value,
      sent_at: Date.now()
    });
    chatInput.value = '';
  }
};

// --- Video Call Functions ---
async function startVideoCall() {
  try {
    startCallBtn.disabled = true;
    endCallBtn.disabled = false;
    videoCallArea.style.display = 'flex';

    localStream = await navigator.mediaDevices.getUserMedia({ 
      video: true, 
      audio: true 
    });
    localVideo.srcObject = localStream;

    peerConnection = new RTCPeerConnection(iceConfig);
    localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));

    peerConnection.ontrack = (event) => {
      if (remoteVideo.srcObject !== event.streams[0]) remoteVideo.srcObject = event.streams[0];
    };

    peerConnection.onicecandidate = (event) => {
      if (event.candidate) {
        socket.emit('signal', {
          type: 'candidate',
          candidate: event.candidate,
          to: receiver_id
        });
      }
    };

    const offer = await peerConnection.createOffer();
    await peerConnection.setLocalDescription(offer);
    socket.emit('signal', {
      type: 'offer',
      offer: offer,
      to: receiver_id
    });

  } catch (err) {
    console.error('Error starting call:', err);
    alert('Failed to start call: ' + err.message);
    endCall();
  }
}

async function handleOffer(offer) {
  try {
    if (!peerConnection) {
      startCallBtn.disabled = true;
      endCallBtn.disabled = false;
      videoCallArea.style.display = 'flex';

      localStream = await navigator.mediaDevices.getUserMedia({ 
        video: true, 
        audio: true 
      });
      localVideo.srcObject = localStream;

      peerConnection = new RTCPeerConnection(iceConfig);
      localStream.getTracks().forEach(track => peerConnection.addTrack(track, localStream));

      peerConnection.ontrack = (event) => {
        if (remoteVideo.srcObject !== event.streams[0]) remoteVideo.srcObject = event.streams[0];
      };

      peerConnection.onicecandidate = (event) => {
        if (event.candidate) {
          socket.emit('signal', {
            type: 'candidate',
            candidate: event.candidate,
            to: receiver_id
          });
        }
      };
    }

    await peerConnection.setRemoteDescription(new RTCSessionDescription(offer));
    const answer = await peerConnection.createAnswer();
    await peerConnection.setLocalDescription(answer);
    socket.emit('signal', {
      type: 'answer',
      answer: answer,
      to: receiver_id
    });

  } catch (err) {
    console.error('Error handling offer:', err);
    endCall();
  }
}

async function handleAnswer(answer) {
  try {
    await peerConnection.setRemoteDescription(new RTCSessionDescription(answer));
  } catch (err) {
    console.error('Error handling answer:', err);
  }
}

async function handleCandidate(candidate) {
  try {
    if (peerConnection && candidate) await peerConnection.addIceCandidate(new RTCIceCandidate(candidate));
  } catch (err) {
    console.error('Error adding ICE candidate:', err);
  }
}

function endCall() {
  if (peerConnection) {
    peerConnection.close();
    peerConnection = null;
  }

  if (localStream) {
    localStream.getTracks().forEach(track => track.stop());
    localStream = null;
    localVideo.srcObject = null;
  }

  remoteVideo.srcObject = null;
  videoCallArea.style.display = 'none';
  startCallBtn.disabled = false;
  endCallBtn.disabled = true;
  callInProgress = false;

  if (receiver_id) {
    socket.emit('signal', {
      type: 'call-ended',
      to: receiver_id
    });
  }
}

function handleCallEnded() {
  if (peerConnection) {
    peerConnection.close();
    peerConnection = null;
  }

  if (localStream) {
    localStream.getTracks().forEach(track => track.stop());
    localStream = null;
    localVideo.srcObject = null;
  }

  remoteVideo.srcObject = null;
  videoCallArea.style.display = 'none';
  startCallBtn.disabled = false;
  endCallBtn.disabled = true;
  callInProgress = false;
}

// --- Socket.io Event Handlers ---
function initCallHandling() {
  socket.on('incoming-call', (data) => {
    incomingCallData = data;
    showIncomingCallModal(data.callerName);
  });
  
  socket.on('call-accepted', () => {
    if (callInProgress) startVideoCall();
  });
  
  socket.on('call-rejected', () => {
    hideIncomingCallModal();
    alert('Call was rejected');
    endCall();
  });
  
  socket.on('call-ended', () => {
    hideIncomingCallModal();
    endCall();
  });
}

socket.on('connect', () => {
  console.log('Connected to signaling server');
  socket.emit('register', sender_id);
});

socket.on('signal', async (data) => {
  if (data.from !== receiver_id) return;

  switch(data.type) {
    case 'offer':
      await handleOffer(data.offer);
      break;
    case 'answer':
      await handleAnswer(data.answer);
      break;
    case 'candidate':
      await handleCandidate(data.candidate);
      break;
    case 'call-ended':
      handleCallEnded();
      alert('The doctor has ended the call');
      break;
  }
});

// --- UI Event Listeners ---
startCallBtn.onclick = () => {
  if (!receiver_id) {
    alert("Please select a doctor first");
    return;
  }
  
  socket.emit('initiate-call', {
    callerId: sender_id,
    callerName: "Patient",
    receiverId: receiver_id
  });
  
  callInProgress = true;
  startVideoCall();
};

endCallBtn.onclick = endCall;

muteAudioBtn.onclick = () => {
  if (!localStream) return;
  isAudioMuted = !isAudioMuted;
  localStream.getAudioTracks().forEach(track => track.enabled = !isAudioMuted);
  muteAudioBtn.innerHTML = isAudioMuted 
    ? '<i class="bi bi-mic-mute-fill"></i>' 
    : '<i class="bi bi-mic-fill"></i>';
};

muteVideoBtn.onclick = () => {
  if (!localStream) return;
  isVideoMuted = !isVideoMuted;
  localStream.getVideoTracks().forEach(track => track.enabled = !isVideoMuted);
  muteVideoBtn.innerHTML = isVideoMuted 
    ? '<i class="bi bi-camera-video-off-fill"></i>' 
    : '<i class="bi bi-camera-video-fill"></i>';
};

notificationBell.addEventListener('click', (e) => {
  e.stopPropagation();
  notificationDropdown.style.display === 'block' ? hideNotificationDropdown() : showNotificationDropdown();
});

document.addEventListener('click', (e) => {
  if (!notificationBell.contains(e.target) && !notificationDropdown.contains(e.target)) {
    hideNotificationDropdown();
  }
});

clearNotifications.addEventListener('click', (e) => {
  e.stopPropagation();
  clearAllNotifications();
});

document.addEventListener('DOMContentLoaded', () => {
  initNotifications();
  initCallHandling();
});
</script>
</body>
</html>