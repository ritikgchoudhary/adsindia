# User Dashboard Pages - Test Checklist

## Login Required
**Note:** All pages require authentication. Please login with valid credentials to test.

## Pages to Test (24 Total)

### 1. Dashboard ✅
- **Route:** `/dashboard` or `/user/dashboard`
- **Component:** `Dashboard.vue`
- **Features:**
  - Welcome banner
  - Earning cards (Today, Last 7 Days, Last 30 Days, Total)
  - Balance widgets (Balance, Affiliate Income, Ads Income, Withdrawn)
  - Latest transactions table
  - Suggested campaigns sidebar
  - KYC alerts

### 2. Ads Work ✅
- **Route:** `/user/ads-work`
- **Component:** `AdsWork.vue`
- **Features:**
  - Display ads with 30-minute duration
  - 5-6k earning per ad
  - Watch ad functionality
  - Timer countdown

### 3. Profile Setting ✅
- **Route:** `/user/profile-setting`
- **Component:** `ProfileSetting.vue`
- **Features:**
  - Update profile information
  - Profile image upload

### 4. Change Password ✅
- **Route:** `/user/change-password`
- **Component:** `ChangePassword.vue`
- **Features:**
  - Change password form

### 5. Two Factor (2FA) ✅
- **Route:** `/user/twofactor`
- **Component:** `TwoFactor.vue`
- **Features:**
  - Enable/Disable 2FA
  - Google Authenticator setup

### 6. Account & KYC ✅
- **Route:** `/user/account-kyc`
- **Component:** `AccountKYC.vue`
- **Features:**
  - Personal details form
  - Bank details form
  - KYC documents (Aadhaar, PAN)
  - KYC status display
  - 990 fee display

### 7. KYC Form ✅
- **Route:** `/user/kyc-form`
- **Component:** `KYCForm.vue`
- **Features:**
  - Dynamic KYC form fields
  - File uploads
  - KYC fee display

### 8. KYC Data ✅
- **Route:** `/user/kyc-data`
- **Component:** `KYCData.vue`
- **Features:**
  - View submitted KYC data

### 9. Withdraw ✅
- **Route:** `/user/withdraw`
- **Component:** `Withdraw.vue`
- **Features:**
  - Withdrawal method selection
  - Amount input
  - 18% fee calculation
  - Confirmation popup
  - Available balance display

### 10. Withdraw History ✅
- **Route:** `/user/withdraw/history`
- **Component:** `WithdrawHistory.vue`
- **Features:**
  - Withdrawal history table
  - Status filters

### 11. Transactions ✅
- **Route:** `/user/transactions`
- **Component:** `Transactions.vue`
- **Features:**
  - Transaction history
  - Search and filters
  - Pagination

### 12. Conversion Log ✅
- **Route:** `/user/conversion-log`
- **Component:** `ConversionLog.vue`
- **Features:**
  - Conversion history
  - Campaign details
  - Search functionality

### 13. Support Tickets ✅
- **Route:** `/user/ticket`
- **Component:** `SupportTickets.vue`
- **Features:**
  - List of support tickets
  - Ticket status

### 14. Open Ticket ✅
- **Route:** `/user/ticket/open`
- **Component:** `OpenTicket.vue`
- **Features:**
  - Create new support ticket
  - Subject and message

### 15. View Ticket ✅
- **Route:** `/user/ticket/:ticket`
- **Component:** `ViewTicket.vue`
- **Features:**
  - View ticket details
  - Reply to ticket

### 16. Packages ✅
- **Route:** `/user/packages`
- **Component:** `Packages.vue`
- **Features:**
  - Current package display
  - Package details

### 17. Upgrade Package ✅
- **Route:** `/user/upgrade-package`
- **Component:** `UpgradePackage.vue`
- **Features:**
  - 5 packages (AdsLite-1499, AdsPro-2999, AdsSupreme-5999, AdsPremium-9999, AdsPremium+-15999)
  - Upgrade functionality
  - Remaining amount calculation

### 18. Ad Plans ✅
- **Route:** `/user/ad-plans`
- **Component:** `AdPlans.vue`
- **Features:**
  - 4 ad plans (2999, 4999, 7499, 9999)
  - Purchase functionality

### 19. Courses ✅
- **Route:** `/user/courses`
- **Component:** `Courses.vue`
- **Features:**
  - Video courses list
  - Course details
  - Enrollment

### 20. Referral ✅
- **Route:** `/user/referral`
- **Component:** `Referral.vue`
- **Features:**
  - General referral link
  - Package-specific referral links with discounts
  - Downline team display
  - Team statistics
  - Referral earnings

### 21. Affiliate Income ✅
- **Route:** `/user/affiliate-income`
- **Component:** `AffiliateIncome.vue`
- **Features:**
  - Today, This Week, This Month, Total income
  - Income history
  - 50% earning display

### 22. Partner Program ✅
- **Route:** `/user/partner-program`
- **Component:** `PartnerProgram.vue`
- **Features:**
  - 3 partner plans (₹2000, ₹4000, ₹6000)
  - Commission structure
  - Join functionality

### 23. Certificates ✅
- **Route:** `/user/certificates`
- **Component:** `Certificates.vue`
- **Features:**
  - Certificate list
  - Apply functionality
  - ₹1250 fee per certificate

### 24. Customer Support ✅
- **Route:** `/user/customer-support`
- **Component:** `CustomerSupport.vue`
- **Features:**
  - Telegram link
  - WhatsApp link
  - Live Chat integration

### 25. Leaderboard ✅
- **Route:** `/user/leaderboard`
- **Component:** `Leaderboard.vue`
- **Features:**
  - Weekly leaderboard
  - Monthly leaderboard
  - All-time leaderboard
  - Trophy badges for top 3

## Testing Steps

1. **Login:**
   - Navigate to `/login`
   - Enter valid credentials
   - Submit form

2. **Dashboard:**
   - Check all widgets load correctly
   - Verify earning calculations
   - Check transactions table
   - Verify campaigns sidebar

3. **Navigate through each page:**
   - Click sidebar menu items
   - Verify page loads without errors
   - Check API calls work
   - Verify data displays correctly
   - Test form submissions
   - Check modals/popups

4. **Test Features:**
   - Withdrawal with 18% fee
   - KYC submission with 990 fee
   - Package upgrades
   - Ad watching
   - Referral link copying
   - Form submissions

## Common Issues to Check

- [ ] API errors in console
- [ ] Null reference errors
- [ ] Missing data displays
- [ ] Form validation
- [ ] Modal/popup functionality
- [ ] Navigation between pages
- [ ] Responsive design
- [ ] Loading states
- [ ] Error messages

## Notes

- All pages use `DashboardLayout` component
- All routes have `requiresAuth: true` meta
- Profile incomplete check is disabled
- All components have null safety checks
