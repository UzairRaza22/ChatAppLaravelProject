<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <title>FCM Test Web</title>
        <style>
            body {
                font-family: sans-serif;
                padding: 20px;
                max-width: 600px;
                margin: auto;
            }
            .form-group {
                margin-bottom: 15px;
            }
            input[type="text"] {
                width: 100%;
                padding: 8px;
                box-sizing: border-box;
            }
            button {
                padding: 10px 15px;
                cursor: pointer;
            }
            #log {
                white-space: pre-wrap;
                background: #f4f4f4;
                padding: 10px;
                border-radius: 5px;
                margin-top: 20px;
            }
            .note {
                font-size: 0.9em;
                color: #666;
                font-style: italic;
                margin-bottom: 20px;
            }
        </style>
    </head>
    <body>
        <h2>FCM Test Client</h2>
        <p class="note">
            Note: Web notification sound is OS/Browser controlled. We cannot
            guarantee custom sound for web push.
        </p>

        <div class="form-group">
            <label>Your Bearer Token (from login):</label>
            <input
                type="text"
                id="bearerToken"
                placeholder="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."
            />
        </div>

        <button id="reqPermissionBtn">1. Request Permission & Get Token</button>
        <button id="registerTokenBtn" disabled>
            2. Register Token to Server
        </button>

        <div id="log">Logs will appear here...</div>

        <script type="module">
            import { initializeApp } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-app.js";
            import {
                getMessaging,
                getToken,
                onMessage,
            } from "https://www.gstatic.com/firebasejs/10.9.0/firebase-messaging.js";

            // TODO: Replace with your actual Firebase config
            const firebaseConfig = {
                apiKey: "AIzaSyBG91G7CeEw-x1wzt6QfNwjGyH2Ps2XcXI",
                authDomain: "chat-5810e.firebaseapp.com",
                projectId: "chat-5810e",
                storageBucket: "chat-5810e.firebasestorage.app",
                messagingSenderId: "431177868521",
                appId: "1:431177868521:web:c287ecf6228b146cf33c78",
                measurementId: "G-TKCFY15B7F"
            };

            // TODO: Replace with your VAPID key
            const vapidKey = "BFUxGl5lgDKrdGlsCNwKTpW3jjvTsy5I3up_XaBixFb3KB8ZVBTKbKNaBav80gZ-nZLGRyH365sgVFqr-ok4Ab4";

            const app = initializeApp(firebaseConfig);
            const messaging = getMessaging(app);

            let currentFcmToken = null;

            function log(msg) {
                console.log(msg);
                document.getElementById("log").innerText +=
                    "\n" +
                    (typeof msg === "object"
                        ? JSON.stringify(msg, null, 2)
                        : msg);
            }

            document
                .getElementById("reqPermissionBtn")
                .addEventListener("click", async () => {
                    try {
                        log("Requesting notification permission...");
                        const permission =
                            await Notification.requestPermission();
                        if (permission === "granted") {
                            log("Notification permission granted.");
                            log("Registering service worker...");
                            const registration =
                                await navigator.serviceWorker.register(
                                    "/firebase-messaging-sw.js",
                                );

                            // Wait for the service worker to be active
                            log("Waiting for service worker to become active...");
                            const serviceWorker = registration.active || registration.waiting || registration.installing;

                            if (registration.active) {
                                log("Service worker is already active.");
                            } else {
                                await new Promise((resolve) => {
                                    const listener = () => {
                                        if (registration.active) {
                                            log("Service worker activated.");
                                            serviceWorker.removeEventListener('statechange', listener);
                                            resolve();
                                        }
                                    };
                                    serviceWorker.addEventListener('statechange', listener);
                                    // Also check if it's already active by the time we added the listener
                                    if (registration.active) resolve();
                                });
                            }

                            log("Fetching FCM token...");
                            currentFcmToken = await getToken(messaging, {
                                vapidKey: vapidKey,
                                serviceWorkerRegistration: registration,
                            });

                            if (currentFcmToken) {
                                log("FCM Token generated: " + currentFcmToken);
                                document.getElementById(
                                    "registerTokenBtn",
                                ).disabled = false;
                            } else {
                                log(
                                    "No registration token available. Request permission to generate one.",
                                );
                            }
                        } else {
                            log("Notification permission denied.");
                        }
                    } catch (error) {
                        log("Error: " + error.message);
                    }
                });

            document
                .getElementById("registerTokenBtn")
                .addEventListener("click", async () => {
                    const bearerToken =
                        document.getElementById("bearerToken").value;
                    if (!bearerToken) {
                        alert("Please enter your bearer token first");
                        return;
                    }

                    try {
                        log("Sending token to server...");
                        const response = await fetch(
                            "/api/devices/fcm-token",
                            {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                    Authorization: "Bearer " + bearerToken,
                                },
                                body: JSON.stringify({
                                    token: currentFcmToken,
                                    platform: "web",
                                }),
                            },
                        );

                        const result = await response.json();
                        log("Server response: " + JSON.stringify(result));
                    } catch (error) {
                        log("Server error: " + error.message);
                    }
                });

            onMessage(messaging, (payload) => {
                log(
                    "Message received in foreground: " +
                        JSON.stringify(payload),
                );
                const notificationTitle = payload.notification.title;
                const notificationOptions = {
                    body: payload.notification.body,
                    icon: "/favicon.ico",
                };

                new Notification(notificationTitle, notificationOptions);
            });
        </script>
    </body>
</html>
