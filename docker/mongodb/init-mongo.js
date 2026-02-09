// MongoDB initialization script (No Authentication)

// Switch to application database
db = db.getSiblingDB('whistle_it');

// Create collections and indexes if needed
db.createCollection('users');
db.createCollection('workspaces');
db.createCollection('teams');
db.createCollection('channels');
db.createCollection('messages');
db.createCollection('files');
db.createCollection('otps');
db.createCollection('api_tokens');

print('MongoDB initialized successfully for Whistle-It application (No Authentication)');
