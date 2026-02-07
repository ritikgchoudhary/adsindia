# Ad Plan Purchase Test Guide

## Test Steps

### 1. Login
- Navigate to `/login`
- Login with valid credentials
- Ensure you have sufficient balance (at least ₹2,999)

### 2. Navigate to Ad Plans
- Go to `/user/ad-plans`
- You should see 4 plans:
  - **Starter Plan** - ₹2,999 (7 days validity)
  - **Popular Plan** - ₹4,999 (15 days validity) - Recommended
  - **Premium Plan** - ₹7,499 (30 days validity)
  - **Elite Plan** - ₹9,999 (60 days validity)

### 3. Purchase a Plan
1. Click "Buy Now" on any plan
2. Confirm the purchase in the popup
3. Check browser console for logs:
   - "Purchasing plan: ..."
   - "Purchase response: ..."
   - "Purchase successful, redirecting..."

### 4. Expected Results

#### Success Case:
- ✅ Success notification appears
- ✅ Balance is deducted
- ✅ Transaction record created
- ✅ AdPackageOrder created with status=1
- ✅ Redirects to `/user/ads-work` after 1.5 seconds
- ✅ User can now watch ads and earn

#### Error Cases to Test:

**Insufficient Balance:**
- Error: "Insufficient balance. Required: ₹X,XXX"
- Balance should not be deducted
- No order created

**Plan Not Found:**
- Error: "Ad plan not found"
- Check if plan exists in database

**Network Error:**
- Error message displayed
- Check console for error details

### 5. Verify Purchase

#### Check Database:
```sql
-- Check AdPackageOrder
SELECT * FROM ad_package_orders WHERE user_id = YOUR_USER_ID ORDER BY id DESC LIMIT 1;

-- Check Transaction
SELECT * FROM transactions WHERE user_id = YOUR_USER_ID AND remark = 'ad_plan_purchase' ORDER BY id DESC LIMIT 1;

-- Check User Balance
SELECT balance FROM users WHERE id = YOUR_USER_ID;
```

#### Check Frontend:
- Go to `/user/ads-work`
- Should see ads available to watch
- Should show active package details

### 6. Test Ads After Purchase
- Navigate to `/user/ads-work`
- Should see ads based on purchased plan
- Daily limit should be 2 ads
- Each ad earns ₹5,000-₹6,000
- Watch duration: 30 minutes per ad

## Console Logs to Check

When purchasing, check browser console for:
```
Purchasing plan: {id: X, name: "...", price: ...}
Purchase response: {status: "success", data: {...}}
Purchase successful, redirecting to ads-work...
```

## API Endpoint

**POST** `/api/ad-plans/purchase`
**Body:**
```json
{
  "plan_id": 1
}
```

**Success Response:**
```json
{
  "status": "success",
  "data": {
    "order_id": 123,
    "expires_at": "2024-01-15 12:00:00",
    "package_name": "Starter Plan",
    "daily_ad_limit": 2,
    "reward_per_ad": 5000
  }
}
```

**Error Response:**
```json
{
  "status": "error",
  "message": {
    "error": ["Error message here"]
  }
}
```

## Common Issues

1. **Plans not showing:**
   - Check API response in Network tab
   - Check console for errors
   - Verify AdPackage records exist in database

2. **Purchase fails:**
   - Check user balance
   - Check plan exists and is active
   - Check console for error details
   - Verify authentication token

3. **No ads after purchase:**
   - Check AdPackageOrder was created
   - Verify order status = 1
   - Check expires_at is in future
   - Verify package relationship exists

## Testing Checklist

- [ ] Plans display correctly
- [ ] Purchase confirmation works
- [ ] Balance deduction works
- [ ] Transaction created
- [ ] AdPackageOrder created
- [ ] Redirect to ads-work works
- [ ] Ads available after purchase
- [ ] Error handling works (insufficient balance)
- [ ] Error handling works (plan not found)
- [ ] Console logs appear correctly
