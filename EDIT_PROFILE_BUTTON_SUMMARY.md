# Edit Profile Button Added

## Overview
Added an "Edit Profile" button to the user dashboard (`/user/dashboard`) to allow users to easily update their profile photo and name.

## Changes Made

### 1. Dashboard View (`frontend/src/views/Dashboard.vue`)
- Added a new `router-link` button in the Profile Card section.
- Positioned below the "Ads Skill India" badge.
- Styled with semi-transparent white background (`bg-white/20`) to match the yellow gradient card design.
- Added `fa-user-edit` icon for better visual cue.

### 2. Button Behavior
- Clicks to `/user/profile-setting` route.
- Redirects user to the Profile Settings page where they can:
  - Upload a new profile photo
  - Change First Name and Last Name
  - Update Address details

## Verification
- Checked `ProfileSetting.vue` to confirm it supports image and name updates.
- Verified route configuration in `router/index.js`.
- Rebuilt the frontend application to deploy changes.

## User Flow
1. User logs in and lands on Dashboard.
2. User sees their Profile Card with Name and Photo.
3. User clicks "Edit Profile" button.
4. User is taken to Profile Settings page.
5. User updates details and saves.
6. User can return to Dashboard to see updated information.
