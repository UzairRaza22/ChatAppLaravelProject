# Firebase Cloud Messaging (FCM) Integration

This project uses FCM HTTP v1 API to send push notifications for new messages.

## Prerequisites

1.  A Firebase account and project.
2.  A Service Account JSON key from Firebase Console (Project Settings > Service Accounts > Generate New Private Key).

## Setup & Configuration

1.  Rename your downloaded Firebase Service Account key array to `service-account.json`.
2.  Place it within your project server at: `storage/app/firebase/service-account.json`. _(This directory is ignored by git)._
3.  Update your `.env` file with the connection details to enable queue processing and specify the FCM Project ID (optional if project_id is within the json config).
    ```env
    FCM_PROJECT_ID=your-firebase-project-id
    FCM_SERVICE_ACCOUNT_PATH=storage/app/firebase/service-account.json
    QUEUE_CONNECTION=database
    ```

## Queue Setup

Since FCM network requests are dispatched as background jobs to prevent blocking the users chat response, a queue daemon is required.

Run the queue worker locally in another terminal:

```bash
php artisan queue:work -vvv
```

In production, ensure Supervisor manages `php artisan queue:work --tries=3 --backoff=5` processes.

## Testing Web Push Notifications

A minimal web testing utility is available. This runs entirely in the browser using the Firebase JS SDK via CDN, without a web framework.

### Setup Test UI

1. Open `public/fcm-test.html` and `public/firebase-messaging-sw.js` in a text editor.
2. Replace the placeholder configurations `YOUR_API_KEY`, etc. with your Firebase Web App credentials.
3. Replace the `YOUR_VAPID_PUBLIC_KEY` in `fcm-test.html` with your web push certificate key (Generate this under Cloud Messaging > Web configuration).

### Try it out

1. Open `http://your-local-url/fcm-test.html` in your browser.
2. Enter a valid Bearer Token for an authenticated user.
3. Click "1. Request Permission & Get Token". Allow notifications when prompted by your browser.
4. Click "2. Register Token to Server". The token is now saved in the database for the user.
5. In Postman, simulate sending a new direct message or channel message to that user `POST /api/messages/create`.
6. Watch the `php artisan queue:work` terminal queue output picking up and successfully processing `SendMessagePushNotificationJob`.
7. You should receive a web push notification if Chrome/Firefox is minimized or you're on a different tab.

> **Note**: Web notification sounds are controlled primarily by the browser settings and OS notifications settings.
