# Ads Work & Earning Test Guide

## Prerequisites
1. User must be logged in
2. User must have purchased an Ad Plan (via `/user/ad-plans`)

## Test Steps

### 1. Login & Navigate
- Login to the system
- Navigate to `/user/ads-work`

### 2. Check Ads Display
- **Expected:** Random video ads should be displayed
- **Check:**
  - Ads show with thumbnails
  - Ad title visible
  - Duration shown (30 minutes)
  - Earning amount shown (₹5,000 - ₹6,000)
  - "Watch Ad Now" button visible

### 3. Watch an Ad
1. Click "Watch Ad Now" on any ad
2. **Expected:** Video player modal opens
3. **Check:**
   - Video player displays
   - Video URL loads correctly
   - Progress bar shows
   - Timer countdown works
   - Video controls available

### 4. Complete Video Watch
1. Let video play completely (or watch 90%+)
2. **Expected:** 
   - Video completes
   - Success message appears
   - "Ad Watched Successfully!" message
   - Earning amount displayed

### 5. Verify Earning
1. Check browser console for API response
2. **Expected API Response:**
   ```json
   {
     "status": "success",
     "data": {
       "earning": 5000,
       "new_balance": 15000
     }
   }
   ```
3. Check user balance updated
4. Check transaction created

### 6. Check Daily Limit
- Watch 2 ads (daily limit)
- **Expected:** After 2 ads, no more ads available
- Message: "All ads have been watched today"

## API Endpoints

### Get Ads
**GET** `/api/ads/work`
**Response:**
```json
{
  "status": "success",
  "data": {
    "data": [
      {
        "id": 1,
        "title": "Amazing Product Showcase #1",
        "description": "Watch this video ad completely to earn ₹5,000...",
        "video_url": "https://commondatastorage.googleapis.com/...",
        "duration": 30,
        "duration_seconds": 1800,
        "earning": 5000,
        "is_active": true,
        "is_watched": false
      }
    ],
    "currency_symbol": "₹",
    "active_package": {
      "name": "Starter Plan",
      "daily_limit": 2,
      "today_views": 0,
      "remaining_ads": 2
    }
  }
}
```

### Complete Ad
**POST** `/api/ads/complete`
**Body:**
```json
{
  "ad_id": 1,
  "watch_duration": 1800,
  "ad_url": "https://..."
}
```

**Success Response:**
```json
{
  "status": "success",
  "data": {
    "earning": 5000,
    "new_balance": 15000
  }
}
```

## Console Logs to Check

When page loads:
```
Ads API Response: {...}
Loaded ads: [...]
Currency symbol: ₹
```

When watching ad:
- Video play events
- Timer countdown
- Watch duration tracking

When completing ad:
```
Payment response: {...}
```

## Common Issues

### 1. No Ads Showing
**Possible Causes:**
- No active Ad Plan purchased
- Daily limit reached
- API error

**Check:**
- Browser console for errors
- Network tab for API response
- Verify AdPackageOrder exists in database

### 2. Video Not Playing
**Possible Causes:**
- Video URL invalid
- CORS issue
- Network problem

**Check:**
- Video URL in network tab
- Console for CORS errors
- Try different video URL

### 3. Earning Not Added
**Possible Causes:**
- Video not watched 90%+
- API error
- Database issue

**Check:**
- Watch duration in console
- API response in network tab
- Database transaction record

## Testing Checklist

- [ ] Ads page loads without errors
- [ ] Ads display correctly
- [ ] Video player opens
- [ ] Video plays correctly
- [ ] Progress bar works
- [ ] Timer countdown works
- [ ] Video completion detected
- [ ] Success message shows
- [ ] Earning added to balance
- [ ] Transaction created
- [ ] Daily limit enforced
- [ ] Console logs appear
- [ ] No null reference errors

## Expected Behavior

1. **After Plan Purchase:**
   - User can see ads on `/user/ads-work`
   - Number of ads = daily limit (usually 2)

2. **While Watching:**
   - Video plays in modal
   - Progress bar shows watch progress
   - Timer counts down
   - User cannot close modal easily

3. **After Watching:**
   - Success message appears
   - Earning added to balance
   - Transaction record created
   - Ad marked as watched
   - User can watch next ad (if limit not reached)

4. **Daily Limit:**
   - After watching daily limit ads, no more ads available
   - Message: "All ads watched today, come back tomorrow"

## Video URLs Used (Sample)

Currently using Google Cloud Storage sample videos:
- BigBuckBunny.mp4
- ElephantsDream.mp4
- ForBiggerBlazes.mp4
- etc.

**Note:** In production, replace with actual ad videos.
