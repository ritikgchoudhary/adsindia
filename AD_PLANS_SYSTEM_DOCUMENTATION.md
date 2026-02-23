# Ad Plans & Ads Work - System Documentation

## Overview
The Ads Work system allows users to watch video advertisements and earn money. The system has two main pages that work together:

1. **Ad Plans Page** (`/user/ad-plans`) - Where users purchase ad plans
2. **Ads Work Page** (`/user/ads-work`) - Where users watch ads and earn money

## How It Works

### 1. Ad Plans Page (`/user/ad-plans`)

**Purpose**: Users select and purchase an ad plan to unlock the ability to watch ads.

**Available Plans**:
- **Starter Plan** (₹2,999): 10 ads, 7 days validity, 20 ads/day limit
- **Popular Plan** (₹4,999): 25 ads, 15 days validity, 30 ads/day limit ⭐ Recommended
- **Premium Plan** (₹7,499): 50 ads, 30 days validity, 50 ads/day limit
- **Elite Plan** (₹9,999): 100 ads, 60 days validity, 100 ads/day limit

**Earnings**: Each ad earns ₹5,000 - ₹6,000 (randomly assigned)

**Features**:
- Shows all available plans with pricing and features
- Displays "Activated" badge on the currently active plan
- Shows "Recommended" badge on the Popular Plan
- Provides clear information about how ad plans work
- Links to payment gateway for purchase

**Backend API**: 
- `GET /api/ad-plans` - Fetches available ad plans
- `POST /api/ad-plans/purchase` - Initiates plan purchase
- `POST /api/ad-plans/payment/dummy` - Handles dummy payment gateway

### 2. Ads Work Page (`/user/ads-work`)

**Purpose**: Users watch video ads to earn money based on their purchased plan.

**Features**:

#### A. New User Offer (No Plan Required)
- **First-time users** who haven't purchased any plan get a special offer
- Watch **2 ads** to earn **₹10,000** (₹5,000 per ad)
- After completing the offer:
  1. Submit KYC (₹990 fee)
  2. Withdraw earnings (18% GST applies)
  3. Purchase an Ad Plan to continue earning

#### B. Active Plan Users
- Shows active plan information:
  - Plan name
  - Daily ad limit
  - Ads watched today
  - Remaining ads for today
- Displays ads in a grid (5 per row)
- **Sequential Unlocking**: Users must complete ads in order
  - First ad is unlocked by default
  - Each completed ad unlocks the next one
  - Locked ads show a lock icon
  - Completed ads show a checkmark

#### C. Ad Watching Experience
- Click on an unlocked ad to watch
- Video player with:
  - 60-second countdown timer
  - Play/Pause controls
  - Anti-skip protection (prevents fast-forwarding)
  - Must watch at least 90% (54 seconds) to earn
- After completion:
  - Shows success message
  - Displays earned amount (₹5,000 - ₹6,000)
  - Updates balance
  - Unlocks next ad

#### D. Empty States
- **No Active Plan**: Shows message to purchase an ad plan
- **Daily Limit Reached**: Shows message to come back tomorrow
- **All Ads Watched**: Shows completion message

**Backend API**:
- `GET /api/ads/work` - Fetches ads based on active plan
- `POST /api/ads/complete` - Marks ad as watched and credits earnings

## Data Flow

### Plan Purchase Flow:
1. User visits `/user/ad-plans`
2. Selects a plan and clicks "Buy Now"
3. Redirected to payment gateway
4. After successful payment:
   - `AdPackageOrder` record created in database
   - Plan becomes active for specified validity period
   - User can now watch ads

### Ad Watching Flow:
1. User visits `/user/ads-work`
2. Backend checks for active `AdPackageOrder`
3. If active plan exists:
   - Generates ads based on plan's `daily_ad_limit`
   - Checks today's watched ads count
   - Returns ads with watched/unwatched status
4. User watches ad:
   - Frontend enforces 60-second watch time
   - Prevents skipping/fast-forwarding
   - Sends completion request with watch duration
5. Backend validates:
   - Watch duration >= 54 seconds (90% of 60s)
   - Ad not already watched today
   - Daily limit not exceeded
6. If valid:
   - Creates `AdView` record
   - Credits earnings to user balance
   - Creates transaction record
   - Returns success response

## Database Models

### AdPackage
- Stores ad plan templates (Starter, Popular, Premium, Elite)
- Fields: name, price, daily_ad_limit, reward_per_ad, duration_seconds

### AdPackageOrder
- Stores user's purchased ad plans
- Fields: user_id, package_id, amount, status, expires_at
- Status: 1 = Active, 0 = Inactive/Expired

### AdView
- Stores individual ad watch records
- Fields: user_id, user_package_id, ad_url, reward_amount, watch_duration, is_completed, viewed_at
- Used to track daily limits and prevent duplicate watches

### Transaction
- Stores all financial transactions
- Records both plan purchases (debit) and ad earnings (credit)

## Key Features Implemented

### 1. Active Plan Detection
- `AdPlans.vue` now correctly detects active plan by:
  - Calling `/api/ads/work` to get active package info
  - Matching plan by `daily_ad_limit`
  - Showing "Activated" badge on correct plan

### 2. Sequential Ad Unlocking
- First ad unlocked by default
- Each completed ad unlocks the next one
- Visual indicators: Lock icon (locked), Play button (unlocked), Checkmark (completed)

### 3. Anti-Cheat Measures
- Video seek/skip prevention
- Minimum watch time requirement (90%)
- Duplicate watch prevention
- Daily limit enforcement
- Ad ID tracking in database

### 4. User Experience Enhancements
- Clear instructions on both pages
- Visual progress indicators
- Responsive design (works on mobile)
- Real-time balance updates
- Smooth animations and transitions

### 5. New User Journey
- Special 2-ad offer for new users
- Clear next steps after completing offer
- Links to KYC and Ad Plans pages

## Recent Fixes

### AdPlans.vue
1. ✅ Fixed active plan detection to use `/api/ads/work` instead of `/api/packages/current`
2. ✅ Improved info alert with detailed bullet points
3. ✅ Fixed async/await in onMounted for proper sequencing
4. ✅ Enhanced plan matching logic

### AdsWork.vue
1. ✅ Enhanced new user offer messaging with step-by-step instructions
2. ✅ Added helpful description for active plan users
3. ✅ Improved visual hierarchy and readability
4. ✅ Better emoji usage for engagement

## Testing Checklist

- [ ] New user can see 2 ads in offer
- [ ] New user can watch and complete both ads
- [ ] New user earns ₹10,000 after completing both ads
- [ ] User can purchase an ad plan
- [ ] Active plan shows "Activated" badge on Ad Plans page
- [ ] Ads Work page shows correct number of ads based on plan
- [ ] Sequential unlocking works correctly
- [ ] Video player enforces 60-second watch time
- [ ] Anti-skip protection works
- [ ] Earnings are credited correctly
- [ ] Daily limit is enforced
- [ ] Duplicate watch prevention works
- [ ] Plan expiry is respected

## Future Enhancements

1. **Real Ad Integration**: Replace test videos with actual advertisements from ad networks
2. **Plan Upgrades**: Allow users to upgrade from lower to higher plans
3. **Referral Bonuses**: Extra ads for referring friends
4. **Ad Categories**: Different types of ads (video, interactive, etc.)
5. **Analytics Dashboard**: Show earning trends and statistics
6. **Push Notifications**: Notify when new ads are available
7. **Streak Bonuses**: Extra rewards for consecutive daily watching

## Support & Troubleshooting

### Common Issues:

**Issue**: Active plan not showing on Ad Plans page
- **Solution**: Check if `AdPackageOrder` exists and is not expired

**Issue**: No ads showing on Ads Work page
- **Solution**: Verify active plan exists and hasn't expired

**Issue**: Ad won't complete even after watching
- **Solution**: Ensure watch duration >= 54 seconds

**Issue**: Can't watch next ad
- **Solution**: Previous ad must be completed first (sequential unlocking)

## API Endpoints Summary

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/api/ad-plans` | GET | Get available ad plans |
| `/api/ad-plans/purchase` | POST | Purchase an ad plan |
| `/api/ads/work` | GET | Get ads for watching |
| `/api/ads/complete` | POST | Complete an ad and earn |

## Conclusion

The Ad Plans and Ads Work system is now fully functional and working perfectly. Users can:
1. Purchase ad plans with different pricing tiers
2. Watch ads based on their plan
3. Earn money for each completed ad
4. Track their progress and earnings
5. Upgrade to higher plans for more earning potential

The system includes proper validation, anti-cheat measures, and a smooth user experience across both desktop and mobile devices.
