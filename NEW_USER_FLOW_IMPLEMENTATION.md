# New User Flow Implementation - Complete Guide

## Overview
Yeh document new user ke liye complete flow describe karta hai jo implement kiya gaya hai.

## Complete User Flow

### STEP 1: New User - 2 Free Ads (₹10,000 Earning)
**Status:** ✅ Already Implemented

**Details:**
- New user (jisne kabhi ad package nahi liya) ko **2 ads** dikhaye jate hain
- Har ad se **₹5,000** earning = Total **₹10,000**
- Ek baar 2 ads complete karne ke baad ye offer khatam
- Location: `/user/ads-work`

**Implementation:**
- File: `core/app/Http/Controllers/Api/AdsController.php`
- Method: `getNewUserAds()` - 2 ads return karta hai
- Method: `completeNewUserAd()` - Ad complete hone par ₹5,000 add karta hai
- Database: `users.new_user_ads_watched` column track karta hai

---

### STEP 2: KYC Submission (₹990 Review Fee)
**Status:** ✅ Implemented with Validation

**Details:**
- User ko **₹10,000 earn** karne ke baad hi KYC submit kar sakta hai
- Pehle **₹990 review fee** pay karni hogi
- KYC documents: Aadhaar + PAN
- Location: `/user/account-kyc`

**Validation Added:**
- ✅ Check: User ne 2 ads watch kiye hain (new_user_ads_watched = 2)
- ✅ Check: User ne ₹10,000 earn kiye hain (ad_view_reward transactions)
- ✅ Check: ₹990 fee pay ki hai ya nahi

**Implementation:**
- File: `core/app/Http/Controllers/Api/UserController.php`
- Method: `kycPayment()` - ₹990 fee deduct karta hai
- Method: `kycSubmit()` - KYC documents submit karta hai
- Validation: Earning check added

---

### STEP 3: Withdrawal (18% GST Fee)
**Status:** ✅ Implemented with Validation

**Details:**
- KYC **approved** hone ke baad hi withdrawal kar sakta hai
- **18% GST fee** withdrawal amount par
- Fee pay karne ke baad withdrawal review mein jata hai
- Location: `/user/withdraw`

**Validation Added:**
- ✅ Check: KYC status = 1 (Approved)
- ✅ Check: User balance sufficient hai
- ✅ 18% fee calculation automatic

**Implementation:**
- File: `core/app/Http/Controllers/Api/WithdrawController.php`
- Method: `withdrawStore()` - Withdrawal request create karta hai
- Method: `payWithdrawalFee()` - 18% fee deduct karta hai
- Validation: KYC approval check added

---

### STEP 4: Ads Plan Purchase
**Status:** ✅ Already Implemented

**Details:**
- Withdrawal ke baad user ads plan buy kar sakta hai
- Available plans:
  - **Starter Plan:** ₹2,999 (7 days, 10 ads)
  - **Popular Plan:** ₹4,999 (15 days, 25 ads) - Recommended
  - **Premium Plan:** ₹7,499 (30 days, 50 ads)
  - **Elite Plan:** ₹9,999 (60 days, 100 ads)
- Location: `/user/ad-plans`

**Implementation:**
- File: `core/app/Http/Controllers/Api/AdPlanController.php`
- Method: `getAdPlans()` - 4 plans return karta hai
- Method: `purchaseAdPlan()` - Plan purchase handle karta hai

---

### STEP 5: Learning Courses Plans
**Status:** ✅ Already Implemented

**Details:**
- User course plans buy kar sakta hai
- Courses complete karne par certificate milta hai
- Location: `/user/courses` and `/user/packages`

**Implementation:**
- File: `core/app/Http/Controllers/Api/CoursePlanController.php`
- File: `core/app/Http/Controllers/Api/CourseController.php`
- Certificate system already implemented

---

### STEP 6: Partner Program (50% Commission)
**Status:** ✅ Already Implemented

**Details:**
- User apne courses ko share/refer kar sakta hai
- Referred user agar course buy karta hai to **50% commission** milta hai
- Location: `/user/partner-program` or `/user/referral`

**Implementation:**
- File: `core/app/Http/Controllers/Api/AffiliateController.php`
- File: `core/app/Http/Controllers/Api/PartnerController.php`
- Database: `course_affiliate_commissions` table
- Commission: 50% of referred user's income

---

## Validation Summary

### ✅ Implemented Validations:

1. **KYC Submit Validation:**
   - User must have watched 2 ads (new_user_ads_watched = 2)
   - User must have earned ₹10,000 from new user ads
   - User must pay ₹990 fee before submitting

2. **Withdrawal Validation:**
   - KYC must be approved (kyc_status = 1)
   - Sufficient balance required
   - 18% GST fee calculation

### 📝 Optional Validations (Not Forced):

3. **Ads Plan Purchase:**
   - User can buy anytime (not forced after withdrawal)
   - Guidance message shows in UI

4. **Course Plan Purchase:**
   - Available after ads plan (guidance in UI)
   - Not forced validation

---

## API Endpoints

### Ads
- `GET /api/ads` - Get ads (new user or package ads)
- `POST /api/ads/complete` - Complete watching an ad

### KYC
- `POST /api/kyc-payment` - Pay ₹990 KYC fee
- `POST /api/kyc-submit` - Submit KYC documents

### Withdrawal
- `POST /api/withdraw-request` - Create withdrawal request
- `POST /api/withdraw-request/pay-fee` - Pay 18% withdrawal fee

### Ad Plans
- `GET /api/ad-plans` - Get available ad plans
- `POST /api/ad-plans/purchase` - Purchase ad plan

### Courses
- `GET /api/courses` - Get available courses
- `GET /api/course-plans` - Get course plans
- `POST /api/course-plans/purchase` - Purchase course plan

### Partner Program
- `GET /api/affiliate-income` - Get affiliate earnings
- `GET /api/partner-program` - Get partner program details

---

## Database Tables

1. **users**
   - `new_user_ads_watched` - Tracks how many new user ads watched (0-2)
   - `kyc_status` - KYC status (0=Pending, 1=Approved, 2=Rejected)
   - `balance` - User balance

2. **transactions**
   - `remark = 'ad_view_reward'` - Ad watching earnings
   - `remark = 'kyc_fee'` - KYC fee payment
   - `remark = 'withdrawal_fee'` - Withdrawal fee payment

3. **ad_package_orders**
   - Tracks purchased ad plans

4. **course_orders**
   - Tracks purchased course plans

5. **course_affiliate_commissions**
   - Tracks 50% commission from referrals

---

## UI Flow Guidance

### Dashboard
- Shows earning cards
- Shows balance widgets
- KYC status alerts

### Ads Work Page
- New user offer banner
- Step-by-step guidance message
- Links to KYC and Ad Plans

### KYC Page
- Step indicators (Bank Details → KYC Documents → Verification)
- ₹990 fee payment
- Document upload

### Withdraw Page
- 18% fee calculation display
- KYC verification check
- Payment confirmation

---

## Testing Checklist

- [ ] New user can watch 2 ads and earn ₹10,000
- [ ] User cannot submit KYC before earning ₹10,000
- [ ] User must pay ₹990 before submitting KYC
- [ ] User cannot withdraw before KYC approval
- [ ] Withdrawal charges 18% GST fee
- [ ] User can purchase ad plans (₹2999-₹9999)
- [ ] User can purchase course plans
- [ ] Referral system gives 50% commission

---

## Notes

- All validations are server-side (secure)
- UI shows guidance but doesn't force flow
- User can skip some steps (like buying ads plan before withdrawal)
- Main validations: KYC after 10K earning, Withdrawal after KYC approval

---

**Last Updated:** 2026-02-13
**Status:** ✅ Complete
