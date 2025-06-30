// --- File Sharing Logic for Send Button ---

let selectedFile = null;

// When a file is selected, store it and show its name in the input (optional)
fileInput.onchange = async (e) => {
  if (e.target.files[0]) {
    selectedFile = e.target.files[0];
    // Optionally, show the file name in the chat input
    chatInput.value = selectedFile.name;
  }
};

// When Send button is clicked
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