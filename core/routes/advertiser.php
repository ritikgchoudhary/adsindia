<?php

use Illuminate\Support\Facades\Route;

Route::namespace('Advertiser\Auth')->name('advertiser.')->middleware('advertiser.guest')->group(function () {
    Route::controller('LoginController')->group(function () {
        Route::get('/login', 'showLoginForm')->name('login');
        Route::post('/login', 'login');
        Route::get('logout', 'logout')->middleware('advertiser')->withoutMiddleware('advertiser.guest')->name('logout');
    });

    Route::controller('RegisterController')->group(function () {
        Route::get('register', 'showRegistrationForm')->name('register');
        Route::post('register', 'register');
        Route::post('check-advertiser', 'checkAdvertiser')->name('checkAdvertiser')->withoutMiddleware('advertiser.guest');
    });

    Route::controller('ForgotPasswordController')->prefix('password')->name('password.')->group(function () {
        Route::get('reset', 'showLinkRequestForm')->name('request');
        Route::post('email', 'sendResetCodeEmail')->name('email');
        Route::get('code-verify', 'codeVerify')->name('code.verify');
        Route::post('verify-code', 'verifyCode')->name('verify.code');
    });

    Route::controller('ResetPasswordController')->group(function () {
        Route::post('password/reset', 'reset')->name('password.update');
        Route::get('password/reset/{token}', 'showResetForm')->name('password.reset');
    });

    Route::controller('SocialiteController')->group(function () {
        Route::get('social-login/{provider}', 'socialLogin')->name('social.login');
        Route::get('social-login/callback/{provider}', 'callback')->name('social.login.callback');
    });
});

Route::middleware('advertiser')->name('advertiser.')->group(function () {

    Route::get('advertiser-data', 'Advertiser\AdvertiserController@advertiserData')->name('data');
    Route::post('advertiser-data-submit', 'Advertiser\AdvertiserController@advertiserDataSubmit')->name('data.submit');

    //authorization
    Route::middleware('advertiser.registration.complete')->namespace('Advertiser')->controller('AuthorizationController')->group(function () {
        Route::get('authorization', 'authorizeForm')->name('authorization');
        Route::get('resend-verify/{type}', 'sendVerifyCode')->name('send.verify.code');
        Route::post('verify-email', 'emailVerification')->name('verify.email');
        Route::post('verify-mobile', 'mobileVerification')->name('verify.mobile');
        Route::post('verify-g2fa', 'g2faVerification')->name('2fa.verify');
    });

    Route::middleware(['advertiser.check', 'advertiser.registration.complete'])->group(function () {

        Route::namespace('Advertiser')->group(function () {

            Route::controller('AdvertiserController')->group(function () {
                Route::get('dashboard', 'home')->name('home');
                Route::get('download-attachments/{file_hash}', 'downloadAttachment')->name('download.attachment');

                Route::get('chart/payout', 'payoutReport')->name('chart.payout');

                //2FA
                Route::get('twofactor', 'show2faForm')->name('twofactor');
                Route::post('twofactor/enable', 'create2fa')->name('twofactor.enable');
                Route::post('twofactor/disable', 'disable2fa')->name('twofactor.disable');

                //KYC
                Route::get('kyc-form', 'kycForm')->name('kyc.form');
                Route::get('kyc-data', 'kycData')->name('kyc.data');
                Route::post('kyc-submit', 'kycSubmit')->name('kyc.submit');

                //Report
                Route::any('deposit/history', 'depositHistory')->name('deposit.history');
                Route::get('transactions', 'transactions')->name('transactions');

                Route::get('tracking-snippets', 'trackingSnippets')->name('tracking.snippets');

                Route::post('add-device-token', 'addDeviceToken')->name('add.device.token');
            });

            //Profile setting
            Route::controller('ProfileController')->group(function () {
                Route::get('profile-setting', 'profile')->name('profile.setting');
                Route::post('profile-setting', 'submitProfile');
                Route::get('change-password', 'changePassword')->name('change.password');
                Route::post('change-password', 'submitPassword');
            });

            // Advertiser Campaign Routes
            Route::controller('CampaignController')->prefix('campaigns')->name('campaign.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('create', 'create')->name('create');
                Route::get('edit/{id}', 'edit')->name('edit');
                Route::post('store/{id?}', 'store')->name('store');
                Route::post('pause/{id}', 'togglePause')->name('toggle.pause');
                Route::get('conversions/{id}', 'conversions')->name('conversions');
            });

            // Withdraw
            Route::controller('WithdrawController')->prefix('withdraw')->name('withdraw')->group(function () {
                Route::middleware('advertiser.kyc')->group(function () {
                    Route::get('/', 'withdrawMoney');
                    Route::post('/', 'withdrawStore')->name('.money');
                    Route::get('preview', 'withdrawPreview')->name('.preview');
                    Route::post('preview', 'withdrawSubmit')->name('.submit');
                });
                Route::get('history', 'withdrawLog')->name('.history');
            });

            Route::controller('TicketController')->prefix('ticket')->name('ticket.')->group(function () {
                Route::get('/', 'supportTicket')->name('index');
                Route::get('new', 'openSupportTicket')->name('open');
                Route::post('create', 'storeSupportTicket')->name('store');
                Route::get('view/{ticket}', 'viewTicket')->name('view');
                Route::post('reply/{id}', 'replyTicket')->name('reply');
                Route::post('close/{id}', 'closeTicket')->name('close');
                Route::get('download/{attachment_id}', 'ticketDownload')->name('download');
            });
        });

        // Payment
        Route::prefix('deposit')->name('deposit.')->controller('Gateway\PaymentController')->group(function () {
            Route::post('insert', 'depositInsert')->name('insert');
            Route::get('confirm', 'depositConfirm')->name('confirm');
            Route::get('manual', 'manualDepositConfirm')->name('manual.confirm');
            Route::post('manual', 'manualDepositUpdate')->name('manual.update');
            Route::any('/{campaign_id?}', 'deposit')->name('index');
        });
    });
});
