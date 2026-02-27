<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::namespace('Api')->name('api.')->group(function(){

    // No-auth: check if live site is this server (see DEPLOY_SEE_CHANGES.md)
    Route::get('deploy-check', function () {
        // Laravel base_path() points to /core. version.txt is written to project root.
        $vFileCore = base_path('version.txt');
        $vFileRoot = base_path('../version.txt');
        $vFile = file_exists($vFileRoot) ? $vFileRoot : $vFileCore;
        $build = file_exists($vFile) ? trim(file_get_contents($vFile)) : 'version.txt not found';
        return response()->json([
            'ok' => true,
            'build' => $build,
            'message' => 'If you see this, API is from this server. Frontend: hard refresh (Ctrl+Shift+R) on /user/courses.',
        ]);
    });

    Route::controller('AppController')->group(function () {
        Route::get('general-setting','generalSetting');
        Route::get('get-countries','getCountries');
        Route::get('language/{key?}','getLanguage');
        Route::get('policies', 'policies');
        Route::get('policy/{slug}', 'policyContent');
        Route::get('faq', 'faq');
        Route::get('seo', 'seo');
        Route::get('get-extension/{act}','getExtension');
        Route::post('contact', 'submitContact');
        Route::get('cookie', 'cookie');
        Route::post('cookie/accept', 'cookieAccept');
        Route::get('custom-pages', 'customPages');
        Route::get('custom-page/{slug}', 'customPageData');
        Route::get('sections/{key?}', 'allSections');
        Route::get('categories', 'getCategories');
        Route::get('traffic-types', 'getTrafficTypes');
        Route::get('campaigns', 'getCampaigns');
        Route::get('campaign/details/{slug}', 'getCampaignDetails');
        Route::get('gateway-status', 'paymentMethodsStatus');
        Route::get('ticket/{ticket}', 'viewTicket');
        Route::post('ticket/ticket-reply/{id}', 'replyTicket');
    });

    // Public Package Routes (for Registration)
    Route::get('packages', 'PackageController@getPackages');

    // Public Courses & Course Packages (Homepage)
    Route::get('public/courses', 'CourseController@publicCourses');
    Route::get('public/course-plans', 'CoursePlanController@getPlans');

	Route::namespace('Auth')->group(function(){
        Route::controller('LoginController')->group(function(){
            Route::post('login', 'login');
            Route::post('check-token', 'checkToken');
            Route::post('social-login', 'socialLogin');
        });
		Route::get('register/referrer-info', 'RegisterController@getReferrerInfo');
		Route::post('register/validate', 'RegisterController@validateRegistration');
		Route::post('register/payment/initiate', 'RegisterController@initiateRegistrationPayment');
		Route::get('register/payment/dummy', 'RegisterController@dummyPaymentHandler');
		Route::post('register', 'RegisterController@register');

        Route::controller('ForgotPasswordController')->group(function(){
            Route::post('password/email', 'sendResetCodeEmail');
            Route::post('password/verify-code', 'verifyCode');
            Route::post('password/reset', 'reset');
        });
	});

    Route::middleware('auth:sanctum')->group(function () {

        Route::post('user-data-submit', 'UserController@userDataSubmit');

        //authorization
        Route::controller('\App\Http\Controllers\User\AuthorizationController')->middleware('registration.complete')->group(function(){
            // authorization, kyc, verify
            Route::get('authorization', 'authorization');
            Route::get('resend-verify/{type}', 'sendVerifyCode');
            Route::post('verify-email', 'emailVerification');
            Route::post('verify-mobile', 'mobileVerification');
            Route::post('verify-g2fa', 'g2faVerification');
        });

        Route::middleware(['check.status'])->group(function () {

            Route::middleware('registration.complete')->group(function(){
                Route::get('dashboard', 'UserController@dashboard');

                // Premium/Beta User Actions
                Route::post('verified/purchase', 'VerifiedController@purchase');
                Route::post('booster/purchase', 'AdBoosterController@purchase');
                Route::controller('VipController')->prefix('vip')->group(function () {
                    Route::get('info', 'info');
                    Route::post('subscribe', 'subscribe');
                });

                Route::controller('UserController')->group(function(){
                    Route::get('beta-settings', 'betaSettings');
                    Route::get('download-attachments/{file_hash}', 'downloadAttachment')->name('download.attachment');
                    Route::post('profile-setting', 'submitProfile');
                    Route::post('change-password', 'submitPassword');

                    Route::get('user-info','userInfo');
                    //KYC
                    Route::get('kyc-form','kycForm');
                    Route::get('kyc-data','kycData');
                    Route::post('kyc-submit','kycSubmit');

                    //Report
                    Route::any('deposit/history', 'depositHistory');
                    Route::get('transactions','transactions');

                    Route::post('add-device-token', 'addDeviceToken');
                    Route::get('push-notifications', 'pushNotifications');
                    Route::post('push-notifications/read/{id}', 'pushNotificationsRead');

                    //2FA
                    Route::get('twofactor', 'show2faForm');
                    Route::post('twofactor/enable', 'create2fa');
                    Route::post('twofactor/disable', 'disable2fa');

                    Route::post('delete-account', 'deleteAccount');
                    Route::post('special-agent/payment/initiate', 'initiateSpecialPayment');
                    Route::post('special-agent/payment/confirm', 'confirmSpecialPayment');
                    Route::get('special-agent/payment/history', 'specialPaymentHistory');
                });

                // Withdraw
                Route::controller('WithdrawController')->group(function(){
                    // Allow fetching methods without KYC (so user can see the page)
                    Route::get('withdraw-method', 'withdrawMethod');
                    Route::get('withdraw/history', 'withdrawLog');
                    
                    // These routes require KYC verification
                    Route::middleware('kyc')->group(function(){
                        Route::post('withdraw-request', 'withdrawStore');
                        Route::post('withdraw-request/pay-fee', 'payWithdrawalFee');
                        Route::post('withdraw-request/confirm', 'withdrawSubmit');
                        // Main wallet GST (18%) payment via gateway before withdraw
                        Route::post('withdraw-request/gst/initiate', 'initiateWithdrawGstPayment');
                        Route::post('withdraw-request/gst/confirm', 'confirmWithdrawGstPayment');
                    });

                    // Affiliate wallet withdraw (separate wallet)
                    Route::get('affiliate/withdraw-method', 'affiliateWithdrawMethod');
                    Route::get('affiliate/withdraw/history', 'affiliateWithdrawLog');
                    Route::middleware('kyc')->group(function(){
                        Route::post('affiliate/withdraw-request', 'affiliateWithdrawStore');
                        Route::post('affiliate/withdraw-request/pay-fee', 'affiliatePayWithdrawalFee');
                    });
                });

                // Payment
                Route::controller('\App\Http\Controllers\Gateway\PaymentController')->group(function(){
                    Route::get('deposit/methods', 'methods');
                    Route::post('deposit/insert', 'depositInsert');
                    Route::post('app/payment/confirm', 'appPaymentConfirm');
                    Route::post('manual/confirm', 'manualDepositConfirm');
                });
                
                Route::controller('ManualPaymentController')->group(function(){
                    Route::post('manual-payment/submit', 'submitManualPayment');
                });

                Route::controller('\App\Http\Controllers\TicketController')->prefix('ticket')->group(function () {
                    Route::get('/', 'supportTicket');
                    Route::post('create', 'storeSupportTicket');
                    Route::get('view/{ticket}', 'viewTicket');
                    Route::post('reply/{id}', 'replyTicket');
                    Route::post('close/{id}', 'closeTicket');
                    Route::get('download/{attachment_id}', 'ticketDownload');
                });

                // Ads Work
                Route::controller('AdsController')->prefix('ads')->group(function () {
                    Route::get('work', 'getAds');
                    Route::post('complete', 'completeAd');
                });

                // Account & KYC
                Route::get('account-kyc', 'UserController@accountKYC');
                Route::post('bank-details', 'UserController@updateBankDetails');
                Route::post('kyc-payment', 'UserController@kycPayment');
                Route::post('kyc-payment/confirm', 'UserController@confirmKycPayment');
                Route::post('account-kyc/reset', 'UserController@resetAccountKyc');

                // Packages
                Route::controller('PackageController')->prefix('packages')->group(function () {
                    // Removed getPackages as it needs to be public for registration
                    Route::get('current', 'getCurrentPackage');
                    Route::post('purchase', 'purchasePackage');
                    Route::post('payment/dummy', 'dummyGatewayPayment'); // Dummy gateway payment
                });

                // Ad Plans
                Route::controller('AdPlanController')->prefix('ad-plans')->group(function () {
                    Route::get('/', 'getAdPlans');
                    Route::post('purchase', 'purchaseAdPlan');
                    Route::post('payment/dummy', 'dummyGatewayPayment'); // Dummy gateway payment
                });

                // Leaderboard
                Route::get('leaderboard', 'LeaderboardController@getLeaderboard');

                // Course plans (learning) – buy plan to access courses, complete course to get certificate
                Route::controller(\App\Http\Controllers\Api\CoursePlanController::class)->prefix('course-plans')->group(function () {
                    Route::get('/', 'getPlans');
                    Route::get('current', 'getCurrent');
                    Route::post('purchase', 'purchase');
                    Route::post('payment/confirm', 'confirmPayment');
                });

                // Ad Certificate (₹1250) – mandatory unlock for courses/certificates
                Route::controller(\App\Http\Controllers\Api\AdCertificateController::class)->prefix('ad-certificate')->group(function () {
                    Route::get('status', 'status');
                    Route::post('purchase', 'purchase');
                    Route::post('payment/confirm', 'confirmPayment');
                });
                // Courses
                Route::get('courses', 'CourseController@getCourses');
                Route::post('courses/complete', 'CourseController@markComplete');

                // Referral
                Route::get('referral', 'ReferralController@getReferralData');

                // Affiliate Income
                Route::get('affiliate-income', 'AffiliateController@getAffiliateIncome');

                // Partner Program
                Route::controller('PartnerController')->prefix('partner-program')->group(function () {
                    Route::get('plans', 'getPartnerPlans');
                    Route::get('current', 'getCurrentPlan');
                    Route::post('join', 'joinPartnerProgram');
                    Route::post('payment/confirm', 'confirmPayment');
                });

                // Certificates
                Route::controller('CertificateController')->prefix('certificates')->group(function () {
                    Route::get('/', 'getCertificates');
                    Route::get('{id}', 'getCertificate');
                });

                // Customer Support
                Route::get('customer-support/links', 'SupportController@getSupportLinks');

                // Conversion Log
                Route::get('conversion/log', 'UserController@conversionLog');
            });
        });

        Route::get('logout', 'Auth\LoginController@logout');
    });
});

// Admin API Routes (controllers are in App\Http\Controllers\Admin, NOT Api\Admin)
Route::prefix('admin')->group(function () {
    // Admin Auth
    Route::post('login', [\App\Http\Controllers\Api\Admin\Auth\LoginController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        // Dashboard & Users
        Route::get('dashboard', [\App\Http\Controllers\Admin\AdminController::class, 'dashboard']);
        Route::get('user', [\App\Http\Controllers\Admin\AdminController::class, 'user']);
        Route::get('users', [\App\Http\Controllers\Admin\AdminController::class, 'allUsers']);
        Route::get('users/counts', [\App\Http\Controllers\Admin\AdminController::class, 'allUsersCounts']);
        Route::post('users/create', [\App\Http\Controllers\Admin\AdminController::class, 'createUser']);
        Route::post('user/{id}/ban', [\App\Http\Controllers\Admin\AdminController::class, 'banUser']);
        Route::post('user/{id}/unban', [\App\Http\Controllers\Admin\AdminController::class, 'unbanUser']);
        Route::get('user/{id}/details', [\App\Http\Controllers\Admin\AdminController::class, 'userDetail']);
        Route::post('user/{id}/update-ad-certificate', [\App\Http\Controllers\Admin\AdminController::class, 'updateAdCertificate']);
        Route::post('user/{id}/basic-update', [\App\Http\Controllers\Admin\AdminController::class, 'updateUserBasic']);
        Route::post('user/{id}/reset-password', [\App\Http\Controllers\Admin\AdminController::class, 'resetUserPassword']);
        Route::post('user/{id}/change-sponsor', [\App\Http\Controllers\Admin\AdminController::class, 'changeUserSponsor']);
        // Agent tag + commission settings (dynamic per agent)
        Route::post('user/{id}/agent', [\App\Http\Controllers\Admin\AdminController::class, 'setUserAgent']);
        Route::get('user/{id}/agent-commissions', [\App\Http\Controllers\Admin\AdminController::class, 'getAgentCommissionSettings']);
        Route::post('user/{id}/agent-commissions', [\App\Http\Controllers\Admin\AdminController::class, 'updateAgentCommissionSettings']);
        Route::post('user/{id}/reset-data', [\App\Http\Controllers\Admin\AdminController::class, 'resetUserData']);
        Route::post('user/{id}/adjust-balance', [\App\Http\Controllers\Admin\AdminController::class, 'adjustBalance']);
        Route::post('user/{id}/delete', [\App\Http\Controllers\Admin\AdminController::class, 'deleteUser']);
        Route::post('user/{id}/delete-bank', [\App\Http\Controllers\Admin\AdminController::class, 'deleteBankDetails']);
        Route::post('user/{id}/update-course-package', [\App\Http\Controllers\Admin\AdminController::class, 'updateCoursePackage']);
        Route::post('user/{id}/update-ads-plan', [\App\Http\Controllers\Admin\AdminController::class, 'updateAdsPlan']);
        Route::post('user/{id}/approve-kyc', [\App\Http\Controllers\Admin\AdminController::class, 'approveKyc']);
        Route::post('user/{id}/reject-kyc', [\App\Http\Controllers\Admin\AdminController::class, 'rejectKyc']);
        Route::post('user/{id}/unapprove-kyc', [\App\Http\Controllers\Admin\AdminController::class, 'unapproveKyc']);
        Route::get('user/{id}/bank-details', [\App\Http\Controllers\Admin\AdminController::class, 'updateUserBankDetails']);

        // Gateway Management (API)
        Route::get('gateways', [\App\Http\Controllers\Admin\AdminController::class, 'allGateways']);
        Route::post('gateway/{id}/toggle', [\App\Http\Controllers\Admin\AdminController::class, 'toggleGateway']);
        Route::post('gateway/upload-qr', [\App\Http\Controllers\Admin\AdminController::class, 'uploadGatewayQr']);
        Route::post('gateway/remove-qr/{index}', [\App\Http\Controllers\Admin\AdminController::class, 'removeGatewayQr']);
        Route::get('transactions', [\App\Http\Controllers\Admin\AdminController::class, 'allTransactions']);
        Route::get('deposits', [\App\Http\Controllers\Admin\AdminController::class, 'allDeposits']);
        Route::get('withdrawals', [\App\Http\Controllers\Admin\AdminController::class, 'allWithdrawals']);
        // Commission Management (Master Admin)
        Route::get('commissions/direct-affiliate', [\App\Http\Controllers\Admin\AdminController::class, 'directAffiliateCommissions']);
        Route::post('commissions/direct-affiliate/{packageId}', [\App\Http\Controllers\Admin\AdminController::class, 'updateDirectAffiliateCommission']);
        Route::get('commissions/agent-defaults', [\App\Http\Controllers\Admin\AdminController::class, 'getAgentCommissionDefaults']);
        Route::post('commissions/agent-defaults', [\App\Http\Controllers\Admin\AdminController::class, 'updateAgentCommissionDefaults']);
        Route::get('commissions/agent-upgrade-rules', [\App\Http\Controllers\Admin\AdminController::class, 'listAgentUpgradeRules']);
        Route::post('commissions/agent-upgrade-rules', [\App\Http\Controllers\Admin\AdminController::class, 'upsertAgentUpgradeRule']);
        Route::post('commissions/reverse', [\App\Http\Controllers\Admin\AdminController::class, 'reverseAffiliateCommission']);
        Route::get('commissions/special-link', [\App\Http\Controllers\Admin\AdminController::class, 'getSpecialLinkCommissions']);
        Route::post('commissions/special-link/{packageId}', [\App\Http\Controllers\Admin\AdminController::class, 'updateSpecialLinkCommission']);
        Route::get('gateway-orders', [\App\Http\Controllers\Admin\AdminController::class, 'gatewayOrders']);
        Route::get('gateway-deposit-orders', [\App\Http\Controllers\Admin\AdminController::class, 'gatewayDepositOrders']);
        Route::get('all-gateway-orders', [\App\Http\Controllers\Admin\AdminController::class, 'allGatewayOrders']);
        Route::post('order/approve/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'approveOrder']);
        Route::post('order/reject/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'rejectOrder']);
        Route::post('order/delete/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'deleteOrder']);
        Route::get('ads-income/settings', [\App\Http\Controllers\Admin\AdsIncomeController::class, 'settings']);
        Route::post('ads-income/settings', [\App\Http\Controllers\Admin\AdsIncomeController::class, 'updateSettings']);
        Route::get('ads-income/liability', [\App\Http\Controllers\Admin\AdsIncomeController::class, 'liability']);
        Route::post('referral/special-link', [\App\Http\Controllers\Admin\ReferralLinksController::class, 'generateSpecialLink']);
        Route::get('referral/special-links', [\App\Http\Controllers\Admin\ReferralLinksController::class, 'listSpecialLinks']);
        Route::put('referral/special-links/{id}', [\App\Http\Controllers\Admin\ReferralLinksController::class, 'updateSpecialLink']);
        Route::delete('referral/special-links/{id}', [\App\Http\Controllers\Admin\ReferralLinksController::class, 'deleteSpecialLink']);
        Route::post('referral/special-links/{id}/commission', [\App\Http\Controllers\Admin\ReferralLinksController::class, 'updateSpecialLinkCommissionAmount']);
        Route::post('deposit/approve/{id}', [\App\Http\Controllers\Admin\DepositController::class, 'approve']);
        Route::post('deposit/reject', [\App\Http\Controllers\Admin\DepositController::class, 'reject']);
        Route::post('withdraw/approve', [\App\Http\Controllers\Admin\AdminController::class, 'approveWithdrawal']);
        Route::post('withdraw/reject', [\App\Http\Controllers\Admin\AdminController::class, 'rejectWithdrawal']);
        Route::get('simplypay/balance', [\App\Http\Controllers\Admin\AdminController::class, 'simplyPayBalance']);
        Route::post('withdraw/auto-payout/simplypay', [\App\Http\Controllers\Admin\AdminController::class, 'simplyPayAutoPayout']);
        // Master Admin Settings & History Clear
        Route::get('agents', [\App\Http\Controllers\Admin\AdminController::class, 'getAgents']);
        Route::post('clear-history/transactions', [\App\Http\Controllers\Admin\AdminController::class, 'clearTransactions']);
        Route::post('clear-history/orders', [\App\Http\Controllers\Admin\AdminController::class, 'clearOrders']);
        Route::post('clear-history/deposits', [\App\Http\Controllers\Admin\AdminController::class, 'clearDeposits']);
        Route::post('clear-history/withdrawals', [\App\Http\Controllers\Admin\AdminController::class, 'clearWithdrawals']);
        Route::post('clear-history/commissions', [\App\Http\Controllers\Admin\AdminController::class, 'clearCommissions']);
        Route::post('clear-history/user-logins', [\App\Http\Controllers\Admin\AdminController::class, 'clearUserLogins']);
        Route::post('clear-history/notifications', [\App\Http\Controllers\Admin\AdminController::class, 'clearNotifications']);
        Route::post('clear-history/ledger', [\App\Http\Controllers\Admin\AdminController::class, 'clearLedger']);

        // Dummy (GET) deposit gateway – creates pending deposit for admin approval
        Route::get('dummy/user-deposit', [\App\Http\Controllers\Admin\DummyGatewayController::class, 'createUserDeposit']);
        
        // Account Ledger (Expenses and Summaries)
        Route::prefix('account-ledger')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Admin\AccountLedgerController::class, 'getSummary']);
            Route::post('add-expense', [\App\Http\Controllers\Api\Admin\AccountLedgerController::class, 'addExpense']);
            Route::delete('expense/{id}', [\App\Http\Controllers\Api\Admin\AccountLedgerController::class, 'deleteExpense']);
            Route::post('hide-date', [\App\Http\Controllers\Api\Admin\AccountLedgerController::class, 'hideDate']);
            Route::post('restore-date', [\App\Http\Controllers\Api\Admin\AccountLedgerController::class, 'restoreDate']);
        });

        // Courses Management
        Route::prefix('course')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\CourseController::class, 'index']);
            Route::get('edit/{id}', [\App\Http\Controllers\Admin\CourseController::class, 'edit']);
            Route::post('store', [\App\Http\Controllers\Admin\CourseController::class, 'store']);
            Route::post('update/{id}', [\App\Http\Controllers\Admin\CourseController::class, 'update']);
            Route::post('delete/{id}', [\App\Http\Controllers\Admin\CourseController::class, 'delete']);
        });

        // Customer Support Links (Master Admin – values shown on /user/customer-support)
        Route::get('support-links', [\App\Http\Controllers\Admin\SupportLinksController::class, 'index']);
        Route::post('support-links', [\App\Http\Controllers\Admin\SupportLinksController::class, 'update']);

        // Homepage Contact Section (Master Admin – values shown on /#contact)
        Route::get('contact-info', [\App\Http\Controllers\Admin\ContactInfoController::class, 'index']);
        Route::post('contact-info', [\App\Http\Controllers\Admin\ContactInfoController::class, 'update']);

        // Policy Pages (Master Admin – Privacy, Terms, Refund, Disclaimer)
        Route::get('policy-pages', [\App\Http\Controllers\Admin\PolicyPagesController::class, 'index']);
        Route::post('policy-pages/{slug}', [\App\Http\Controllers\Admin\PolicyPagesController::class, 'update']);

        // Gateway Test (Master Admin)
        Route::post('gateway-test/initiate', [\App\Http\Controllers\Admin\GatewayTestController::class, 'initiate']);
        Route::get('gateway-test/status', [\App\Http\Controllers\Admin\GatewayTestController::class, 'status']);

        // Packages Management
        Route::prefix('packages')->group(function () {
            Route::get('all', [\App\Http\Controllers\Admin\CoursePlanController::class, 'all']);
            Route::post('create', [\App\Http\Controllers\Admin\CoursePlanController::class, 'store']);
            Route::get('edit/{id}', [\App\Http\Controllers\Admin\CoursePlanController::class, 'edit']);
            Route::post('update/{id}', [\App\Http\Controllers\Admin\CoursePlanController::class, 'update']);
            Route::post('delete/{id}', [\App\Http\Controllers\Admin\CoursePlanController::class, 'delete']);
        });
        
        // Admin Management (Master Admin)
        Route::get('admins', [\App\Http\Controllers\Admin\AdminController::class, 'listAdmins']);
        Route::post('admins/create', [\App\Http\Controllers\Admin\AdminController::class, 'createAdmin']);
        Route::post('admins/{id}/update', [\App\Http\Controllers\Admin\AdminController::class, 'updateAdmin']);
        Route::post('admins/{id}/toggle', [\App\Http\Controllers\Admin\AdminController::class, 'toggleAdmin']);
        Route::post('admins/{id}/reset-password', [\App\Http\Controllers\Admin\AdminController::class, 'resetAdminPassword']);
        Route::post('admins/{id}/delete', [\App\Http\Controllers\Admin\AdminController::class, 'deleteAdmin']);

        // Reports & Analytics (Master Admin)
        Route::get('reports', [\App\Http\Controllers\Admin\AdminController::class, 'reports']);

        // Withdrawal Settings (Master Admin)
        Route::get('withdrawal-settings', [\App\Http\Controllers\Admin\AdminController::class, 'getWithdrawalSettings']);
        Route::post('withdrawal-settings', [\App\Http\Controllers\Admin\AdminController::class, 'updateWithdrawalSettings']);

        // Email Settings
        Route::get('email-settings', [\App\Http\Controllers\Admin\AdminController::class, 'getEmailSettings']);
        Route::post('email-settings', [\App\Http\Controllers\Admin\AdminController::class, 'updateEmailSettings']);

        // Beta Features (Master Admin Only)
        Route::prefix('beta')->group(function () {
            $c = \App\Http\Controllers\Admin\BetaFeaturesController::class;
            Route::get('summary', [$c, 'getSummary']);
            
            Route::get('vip/plans', [$c, 'getVipPlans']);
            Route::post('vip/plans/save', [$c, 'saveVipPlan']);
            Route::post('vip/plans/toggle', [$c, 'toggleVipPlan']);
            
            Route::get('verified/settings', [$c, 'getVerifiedSettings']);
            Route::post('verified/settings', [$c, 'saveVerifiedSettings']);
            
            Route::get('booster/settings', [$c, 'getBoosterSettings']);
            Route::post('booster/settings', [$c, 'saveBoosterSettings']);
            
            Route::get('instant/settings', [$c, 'getInstantSettings']);
            Route::post('instant/settings', [$c, 'saveInstantSettings']);

            // Additional Points Placeholders
            Route::get('extra/settings', [$c, 'getExtraSettings']);
            Route::post('extra/settings', [$c, 'saveExtraSettings']);
        });

        // Admin Logout
        Route::post('logout', function (\Illuminate\Http\Request $request) {
            $request->user()->currentAccessToken()->delete();
            return responseSuccess('logout_success', ['Logged out successfully']);
        });
    });
});
