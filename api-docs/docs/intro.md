---
title: Chat Application API Documentation
description: Complete API documentation for the Laravel Chat Application
sidebar_label: API Endpoints
---

# Chat Application API Documentation

Welcome to the comprehensive API documentation for the Laravel Chat Application. This documentation provides detailed information about all available endpoints, request/response formats, authentication methods, and data structures.

## Overview

This API powers a real-time chat application built with Laravel and MongoDB, featuring workspaces, teams, channels, and messaging capabilities with Firebase Cloud Messaging (FCM) support for push notifications.

## Table of Contents

- [Authentication](./authentication) - User registration, login, password reset, and token management
- [Workspaces](./workspaces) - Create and manage workspaces and their members
- [Teams](./teams) - Team creation, member management within workspaces
- [Channels](./channels) - Public, private, and direct messaging channels
- [Messages](./messages) - Send, read, update, delete messages with file support
- [FCM Tokens](./fcm-tokens) - Device token registration for push notifications
- [Admin API](./admin-api) - Admin-specific endpoints
- [Data Structures](./data-structures) - MongoDB collection schemas and field definitions
- [Error Responses](./error-responses) - Common error formats and HTTP status codes
- [Notes](./notes) - Additional implementation details and specifications

## Base URL

All API endpoints are relative to:
```
http://localhost:8000/api
```

## Authentication

Most endpoints require authentication via JWT tokens. Include the token in the request headers:

```
token: your_jwt_token_here
```

## Getting Started

1. [Register a new user](./authentication#1-user-signup)
2. [Verify your email](./authentication#2-verify-signup)
3. [Login to get your access token](./authentication#3-user-login)
4. Start using the API with your token

## Support

For questions or issues with the API, please refer to the detailed documentation in each section or contact the development team.
