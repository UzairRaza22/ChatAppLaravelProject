importScripts(
    "https://www.gstatic.com/firebasejs/10.9.0/firebase-app-compat.js",
);
importScripts(
    "https://www.gstatic.com/firebasejs/10.9.0/firebase-messaging-compat.js",
);

// TODO: Replace with your actual Firebase config
const firebaseConfig = {
    apiKey: "AIzaSyBG91G7CeEw-x1wzt6QfNwjGyH2Ps2XcXI",
    authDomain: "chat-5810e.firebaseapp.com",
    projectId: "chat-5810e",
    storageBucket: "chat-5810e.firebasestorage.app",
    messagingSenderId: "431177868521",
    appId: "1:431177868521:web:c287ecf6228b146cf33c78"
};

firebase.initializeApp(firebaseConfig);

const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
    console.log(
        "[firebase-messaging-sw.js] Received background message ",
        payload,
    );
    const notificationTitle = payload.notification?.title || "New Message";
    const notificationOptions = {
        body:
            payload.notification?.body ||
            "You have received a new background message.",
    };

    self.registration.showNotification(notificationTitle, notificationOptions);
});
