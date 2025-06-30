// Initialize Firebase
  // Import the functions you need from the SDKs you need
  import { initializeApp } from "https://www.gstatic.com/firebasejs/11.8.1/firebase-app.js";
  import { getAnalytics } from "https://www.gstatic.com/firebasejs/11.8.1/firebase-analytics.js";
  // TODO: Add SDKs for Firebase products that you want to use
  // https://firebase.google.com/docs/web/setup#available-libraries

  // Your web app's Firebase configuration
  // For Firebase JS SDK v7.20.0 and later, measurementId is optional
  const firebaseConfig = {
    apiKey: "AIzaSyCAPuzjbS-KYjIB12TL4i1Db-uKPEhSHEI",
    authDomain: "emed-6b602.firebaseapp.com",
    projectId: "emed-6b602",
    storageBucket: "emed-6b602.firebasestorage.app",
    messagingSenderId: "459401321363",
    appId: "1:459401321363:web:0748fdea27a15e48ec6666",
    measurementId: "G-1D3LYMXK5T"
  };
firebase.auth().onAuthStateChanged(user => {
  if (!user) {
    console.error('User not authenticated');
    alert('Please log in to send files');
    fileInput.disabled = true;
    cameraBtn.disabled = true;
  } else {
    fileInput.disabled = false;
    cameraBtn.disabled = false;
  }
}); 
  // Initialize Firebase
  const app = initializeApp(firebaseConfig);
  const analytics = getAnalytics(app);

 