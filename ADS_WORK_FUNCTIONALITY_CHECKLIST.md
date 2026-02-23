# Ads Work Page - Functionality Checklist

## Pre-requisites
1. User must be logged in
2. User must have purchased an Ad Plan

## Test Checklist

### 1. Page Load & Display
- [ ] Page loads without errors
- [ ] No "Watch Ads & Earn Money" header banner visible (should be removed)
- [ ] No page title visible (should be empty)
- [ ] Active Plan Info card displays correctly
- [ ] Shows: Plan name, Daily Limit, Today Watched, Remaining ads
- [ ] "Upgrade Plan" button displays with modern gradient design
- [ ] Progress Summary card shows: Ads Grid title, ads count, completed count
- [ ] Progress bar displays correctly

### 2. Ads Grid Display
- [ ] Ads display in grid layout (5 per row on large screens)
- [ ] First ad is unlocked (clickable)
- [ ] Other ads are locked (grayed out with lock overlay)
- [ ] Ad thumbnails load correctly (no broken images)
- [ ] Play button overlay shows on unlocked ads
- [ ] Completed ads show green "Completed" badge

### 3. Video Watch Functionality
- [ ] Click on unlocked ad opens video modal
- [ ] Video player loads correctly
- [ ] Video plays when clicked
- [ ] Timer starts at 60 seconds and counts down
- [ ] Play/Pause button works
- [ ] Video cannot be skipped/forwarded (skip prevention works)
- [ ] Video must be watched for full 60 seconds
- [ ] If video is shorter, it loops to ensure 1 minute watch

### 4. Ad Completion & Rewards
- [ ] After watching 60 seconds (54 seconds minimum), ad completes
- [ ] Success message shows: "Ad Watched Successfully!"
- [ ] Earning amount displays correctly (₹5,000 - ₹6,000)
- [ ] Reward is added to user balance (check balance update)
- [ ] Transaction is created in history
- [ ] Ad is marked as "Completed" in grid
- [ ] Next ad unlocks automatically

### 5. Next Ad Unlock
- [ ] After completing first ad, second ad unlocks
- [ ] Completed ad shows green badge
- [ ] Next ad becomes clickable
- [ ] Process continues for all ads
- [ ] All ads can be watched sequentially

### 6. History & Tracking
- [ ] Completed ads appear in transaction history
- [ ] AdView records created in database
- [ ] History shows: Date, Amount, Status (Completed)
- [ ] Today's watched count updates correctly
- [ ] Remaining ads count decreases

### 7. Daily Limit
- [ ] After watching daily limit ads, no more ads available
- [ ] Message shows: "All ads have been watched today. Come back tomorrow!"
- [ ] Cannot watch more than daily limit

### 8. Error Handling
- [ ] Video loading errors are handled gracefully
- [ ] Error messages show if video fails to load
- [ ] No console errors (check browser console)
- [ ] No "a.replace is not a function" errors
- [ ] No "NotSupportedError" video errors

### 9. UI/UX
- [ ] Upgrade Plan button has hover effects
- [ ] Video modal closes properly
- [ ] Page scrolls correctly
- [ ] Responsive design works on mobile
- [ ] All buttons and links work correctly

### 10. Edge Cases
- [ ] Refresh page - watched ads status preserved
- [ ] Refresh page - current unlocked index preserved
- [ ] Cannot watch same ad twice in one day
- [ ] Duplicate watching prevented
- [ ] Empty state shows if no active package
- [ ] Empty state shows if all ads watched

## Common Issues to Check

### Console Errors
- Check browser console for:
  - `TypeError: Cannot read properties of null (reading 'id')`
  - `TypeError: a.replace is not a function`
  - `NotSupportedError: The element has no supported sources`

### Network Requests
- Check Network tab for:
  - `/api/ads/work` - Should return 200 with ads data
  - `/api/ads/complete` - Should return 200 after watching
  - No 404 or 500 errors

### Database
- Check database for:
  - `ad_views` table - Records created after watching
  - `transactions` table - Transaction records created
  - `users` table - Balance updated correctly

## Test Steps

1. **Login** to the system
2. **Navigate** to `/user/ads-work`
3. **Check** page loads without errors
4. **Click** on first unlocked ad
5. **Watch** video for 60 seconds
6. **Verify** reward added and next ad unlocked
7. **Check** transaction history
8. **Refresh** page and verify status preserved
9. **Complete** all ads up to daily limit
10. **Verify** daily limit enforced

## Expected Behavior

- All ads should be exactly 1 minute (60 seconds)
- Timer should countdown from 60 seconds
- Reward should be ₹5,000 - ₹6,000 per ad
- Next ad should unlock after completion
- Previous ads should show as completed
- History should track all watched ads
- Daily limit should be enforced

## Notes

- Video URLs are from Google Cloud Storage (sample videos)
- Videos may loop if shorter than 60 seconds
- Minimum watch time is 54 seconds (90% of 60)
- All functionality should work without errors
