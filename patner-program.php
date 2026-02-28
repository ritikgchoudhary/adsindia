<?php
// Standalone public page (served directly by Apache because it exists as a file).
// Submissions are stored via Laravel API: POST /api/influencer/apply

// APK link is controlled by Master Admin (see /master_admin/influencer-program).
// Keep this as an empty fallback so the button stays disabled until admin uploads.
$APK_DOWNLOAD_URL = '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Influencer Application Form - ADS SKILL INDIA</title>
    <meta name="description" content="Influencer application form for ADS SKILL INDIA." />
    <style>
        :root{
            --bg:#0b1220;
            --card:#0f1b33;
            --card2:#0c1730;
            --text:#eaf1ff;
            --muted:rgba(234,241,255,.72);
            --border:rgba(255,255,255,.10);
            --primary:#5b8cff;
            --primary2:#2f6bff;
            --danger:#ff4d6d;
            --success:#2fd28a;
            --warn:#ffb020;
        }
        *{box-sizing:border-box}
        body{
            margin:0;
            font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, Noto Sans, Arial, "Apple Color Emoji", "Segoe UI Emoji";
            background: radial-gradient(1200px 700px at 20% 0%, rgba(91,140,255,.22), transparent 55%),
                        radial-gradient(900px 600px at 90% 15%, rgba(47,210,138,.15), transparent 55%),
                        var(--bg);
            color:var(--text);
            min-height:100vh;
        }
        a{color:inherit}
        .wrap{max-width:980px;margin:0 auto;padding:22px 14px 70px}
        .top{
            display:flex;align-items:center;justify-content:space-between;gap:12px;
            padding:14px 16px;border:1px solid var(--border);background:rgba(15,27,51,.55);
            border-radius:16px;backdrop-filter: blur(10px);
            flex-wrap:wrap;
        }
        .brand{display:flex;flex-direction:column;gap:2px}
        .brand strong{font-size:16px;letter-spacing:.2px}
        .brand span{font-size:12px;color:var(--muted)}
        .pill{
            font-size:12px;padding:8px 10px;border-radius:999px;
            border:1px solid var(--border);color:var(--muted);
            background:rgba(12,23,48,.65);
            white-space:nowrap;
        }
        .linkBtn{
            display:inline-flex;align-items:center;justify-content:center;
            padding:10px 12px;border-radius:12px;
            border:1px solid rgba(255,255,255,.14);
            background:rgba(10,18,35,.65);
            color:#fff;text-decoration:none;
            width:100%;
            font-weight:800;
        }
        .linkBtn.is-disabled{
            opacity:.55;
            cursor:not-allowed;
            pointer-events:none;
        }
        .hero{margin-top:18px;padding:22px 18px;border:1px solid var(--border);border-radius:18px;background:rgba(15,27,51,.38)}
        .hero h1{margin:0 0 8px;font-size:22px}
        .hero p{margin:0;color:var(--muted);line-height:1.55}
        .grid{margin-top:18px;display:grid;grid-template-columns:1.2fr .8fr;gap:16px}
        @media (max-width: 900px){.grid{grid-template-columns:1fr}}
        .card{border:1px solid var(--border);border-radius:18px;background:rgba(15,27,51,.55);overflow:hidden}
        .card .hd{padding:14px 16px;border-bottom:1px solid var(--border);background:rgba(12,23,48,.6)}
        .card .hd h2{margin:0;font-size:14px;letter-spacing:.2px}
        .card .bd{padding:16px}
        .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
        @media (max-width: 640px){.row{grid-template-columns:1fr}}
        label{display:block;font-size:12px;color:var(--muted);margin:0 0 6px}
        input, select, textarea{
            width:100%;
            padding:11px 12px;
            border-radius:12px;
            border:1px solid rgba(255,255,255,.14);
            background:rgba(10,18,35,.75);
            color:var(--text);
            outline:none;
            font-size:15px;
        }
        input:focus, select:focus, textarea:focus{border-color:rgba(91,140,255,.65);box-shadow:0 0 0 3px rgba(91,140,255,.18)}
        textarea{min-height:100px;resize:vertical}
        .stepper{display:flex;gap:10px;align-items:stretch;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap}
        .step{
            flex:1;min-width:0;
            padding:10px 10px;border-radius:14px;border:1px solid rgba(255,255,255,.10);
            background:rgba(10,18,35,.55);
        }
        .step .t{display:flex;align-items:center;gap:10px}
        .dot{
            width:26px;height:26px;border-radius:999px;
            display:flex;align-items:center;justify-content:center;
            font-weight:900;font-size:12px;
            border:1px solid rgba(255,255,255,.14);
            background:rgba(12,23,48,.8);
            color:var(--muted);
            flex:0 0 auto;
        }
        .step strong{font-size:12px;display:block;line-height:1.2}
        .step span{font-size:11px;color:var(--muted);display:block;margin-top:2px}
        .step.active{border-color:rgba(91,140,255,.45);background:rgba(91,140,255,.10)}
        .step.active .dot{border-color:rgba(91,140,255,.55);color:#fff}
        .step.done{border-color:rgba(47,210,138,.35);background:rgba(47,210,138,.08)}
        .step.done .dot{border-color:rgba(47,210,138,.55);background:rgba(47,210,138,.18);color:#eafff4}

        .choices{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:10px;
            padding:12px;
            border:1px solid rgba(255,255,255,.10);
            border-radius:14px;
            background:rgba(10,18,35,.55);
        }
        @media (max-width: 520px){.choices{grid-template-columns:1fr}}
        .choice{
            display:flex;align-items:center;justify-content:space-between;gap:10px;
            padding:12px 12px;border-radius:14px;border:1px solid rgba(255,255,255,.12);
            background:rgba(12,23,48,.65);cursor:pointer;user-select:none;
        }
        .choice .l{display:flex;align-items:center;gap:10px;min-width:0}
        .badge{
            width:34px;height:34px;border-radius:12px;display:flex;align-items:center;justify-content:center;
            border:1px solid rgba(255,255,255,.12);
            background:rgba(10,18,35,.75);
            flex:0 0 auto;
            font-weight:900;
        }
        .choice strong{display:block;font-size:13px}
        .choice span{display:block;font-size:12px;color:var(--muted)}
        .choice input{width:18px;height:18px;flex:0 0 auto}
        .choice.selected{border-color:rgba(91,140,255,.55);box-shadow:0 0 0 3px rgba(91,140,255,.12)}

        .note{font-size:12px;color:var(--muted);line-height:1.5;margin-top:10px}
        .btn{
            display:inline-flex;align-items:center;justify-content:center;gap:10px;
            border:1px solid rgba(91,140,255,.45);
            background:linear-gradient(180deg, rgba(91,140,255,.95), rgba(47,107,255,.95));
            color:white;
            padding:12px 14px;border-radius:14px;
            font-weight:700;cursor:pointer;
            width:100%;
        }
        .btn:disabled{opacity:.6;cursor:not-allowed}
        .btn.secondary{
            background:rgba(12,23,48,.75);
            border-color:rgba(255,255,255,.14);
            color:var(--text);
        }
        .btnRow{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:12px}
        @media (max-width: 520px){.btnRow{grid-template-columns:1fr}}
        .side-list{margin:0;padding:0;list-style:none;display:flex;flex-direction:column;gap:10px}
        .side-item{
            padding:12px 12px;border-radius:14px;border:1px solid var(--border);
            background:rgba(10,18,35,.55)
        }
        .side-item strong{display:block;font-size:13px;margin-bottom:4px}
        .side-item span{display:block;font-size:12px;color:var(--muted);line-height:1.45}
        .alert{display:none;margin-top:12px;padding:12px 12px;border-radius:14px;border:1px solid var(--border);background:rgba(12,23,48,.65)}
        .alert.show{display:block}
        .alert.success{border-color:rgba(47,210,138,.35);background:rgba(47,210,138,.10)}
        .alert.error{border-color:rgba(255,77,109,.35);background:rgba(255,77,109,.10)}
        .alert .title{font-weight:800;margin-bottom:4px}
        .small{font-size:12px;color:var(--muted)}
        .hp{position:absolute;left:-9999px;top:-9999px;opacity:0}
        .foot{margin-top:16px;color:var(--muted);font-size:12px;text-align:center}
        .req{color:rgba(255,176,32,.95)}
        .hide{display:none !important}

        /* Mobile polish */
        @media (max-width: 640px){
            .wrap{padding:18px 12px 64px}
            .top{padding:12px 12px}
            .pill{width:100%;text-align:center}
            .hero{padding:16px 14px}
            .hero h1{font-size:18px}
            .card .hd{padding:12px 12px}
            .card .bd{padding:12px}
            input, select, textarea{font-size:16px}
            .step{flex:1 1 calc(50% - 10px);padding:10px 10px}
            .dot{width:24px;height:24px;font-size:12px}
            .badge{width:32px;height:32px}
        }
        @media (max-width: 380px){
            .step{flex:1 1 100%}
        }
    </style>
</head>
<body>
<div class="wrap">
    <div class="top">
        <div class="brand">
            <strong>ADS SKILL INDIA</strong>
            <span>Influencer Application Form</span>
        </div>
        <div class="pill">4-step application (mobile-friendly)</div>
    </div>

    <div class="hero">
        <h1>Influencer / Creator Application</h1>
        <p>Apply in 4 steps: basic details, select one platform, add payment method, then download the APK and upload the installation screenshot.</p>
    </div>

    <div class="grid">
        <div class="card">
            <div class="hd"><h2>Complete your application</h2></div>
            <div class="bd">
                <form id="appForm">
                    <div class="stepper" aria-label="Application steps">
                        <div class="step active" data-step-ind="1">
                            <div class="t">
                                <div class="dot">1</div>
                                <div style="min-width:0">
                                    <strong>Basic details</strong>
                                    <span>Contact + niche</span>
                                </div>
                            </div>
                        </div>
                        <div class="step" data-step-ind="2">
                            <div class="t">
                                <div class="dot">2</div>
                                <div style="min-width:0">
                                    <strong>Platform</strong>
                                    <span>Select one</span>
                                </div>
                            </div>
                        </div>
                        <div class="step" data-step-ind="3">
                            <div class="t">
                                <div class="dot">3</div>
                                <div style="min-width:0">
                                    <strong>Payment method</strong>
                                    <span>UPI / USDT / PayPal / Wire</span>
                                </div>
                            </div>
                        </div>
                        <div class="step" data-step-ind="4">
                            <div class="t">
                                <div class="dot">4</div>
                                <div style="min-width:0">
                                    <strong>APK + Screenshot</strong>
                                    <span>Download & upload</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="alertOk" class="alert success">
                        <div class="title">Submitted successfully</div>
                        <div class="small">Your details have been received. Our team will contact you soon.</div>
                    </div>
                    <div id="alertErr" class="alert error">
                        <div class="title">Action required</div>
                        <div id="alertErrText" class="small">Please check your details and try again.</div>
                    </div>

                    <!-- STEP 1 -->
                    <section data-step="1">
                        <div class="row">
                            <div>
                                <label for="name">Full Name <span class="req">*</span></label>
                                <input id="name" name="name" placeholder="Your full name" required />
                            </div>
                            <div>
                                <label for="phone">Mobile Number <span class="req">*</span></label>
                                <input id="phone" name="phone" inputmode="numeric" autocomplete="tel" placeholder="10-digit mobile number" required />
                            </div>
                        </div>

                        <div class="row" style="margin-top:12px;">
                            <div>
                                <label for="email">Email (optional)</label>
                                <input id="email" name="email" type="email" autocomplete="email" placeholder="you@example.com" />
                            </div>
                            <div>
                                <label for="city">City / State</label>
                                <input id="city" name="city" autocomplete="address-level2" placeholder="City, State" />
                            </div>
                        </div>

                        <div class="row" style="margin-top:12px;">
                            <div>
                                <label for="cat">Content Category / Niche</label>
                                <input id="cat" name="category" placeholder="Education, Finance, Lifestyle, etc." />
                            </div>
                            <div>
                                <label for="lang">Primary Audience Language</label>
                                <select id="lang" name="audience_language">
                                    <option value="">Select</option>
                                    <option value="hindi">Hindi</option>
                                    <option value="english">English</option>
                                    <option value="hinglish">Hinglish</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="btnRow">
                            <button id="next1" class="btn" type="button">Next</button>
                        </div>
                    </section>

                    <!-- STEP 2 -->
                    <section data-step="2" class="hide">
                        <div>
                            <label>Select one platform <span class="req">*</span></label>
                            <div class="choices" id="platformChoices">
                                <label class="choice">
                                    <div class="l">
                                        <div class="badge">IG</div>
                                        <div>
                                            <strong>Instagram</strong>
                                            <span>@username or profile link</span>
                                        </div>
                                    </div>
                                    <input type="radio" name="platform" value="instagram" />
                                </label>
                                <label class="choice">
                                    <div class="l">
                                        <div class="badge">FB</div>
                                        <div>
                                            <strong>Facebook</strong>
                                            <span>Page / profile link</span>
                                        </div>
                                    </div>
                                    <input type="radio" name="platform" value="facebook" />
                                </label>
                                <label class="choice">
                                    <div class="l">
                                        <div class="badge">YT</div>
                                        <div>
                                            <strong>YouTube</strong>
                                            <span>Channel link</span>
                                        </div>
                                    </div>
                                    <input type="radio" name="platform" value="youtube" />
                                </label>
                                <label class="choice">
                                    <div class="l">
                                        <div class="badge">OT</div>
                                        <div>
                                            <strong>Other</strong>
                                            <span>Any platform</span>
                                        </div>
                                    </div>
                                    <input type="radio" name="platform" value="other" />
                                </label>
                            </div>
                            <div class="note">You can submit only one platform per application.</div>
                        </div>

                        <div class="card" style="margin-top:14px;background:rgba(10,18,35,.45)">
                            <div class="hd"><h2>Platform details</h2></div>
                            <div class="bd">
                                <div id="platInstagram" class="hide">
                                    <div class="row">
                                        <div>
                                            <label for="ig">Instagram Username / Link <span class="req">*</span></label>
                                            <input id="ig" name="instagram_handle" placeholder="@yourhandle or profile link" />
                                        </div>
                                        <div>
                                            <label for="igf">Followers (optional)</label>
                                            <input id="igf" name="instagram_followers" inputmode="numeric" placeholder="e.g. 25000" />
                                        </div>
                                    </div>
                                </div>

                                <div id="platFacebook" class="hide">
                                    <div class="row">
                                        <div>
                                            <label for="fb">Facebook Page / Profile Link <span class="req">*</span></label>
                                            <input id="fb" name="facebook_link" placeholder="facebook link" />
                                        </div>
                                        <div>
                                            <label for="fbf">Followers (optional)</label>
                                            <input id="fbf" name="facebook_followers" inputmode="numeric" placeholder="e.g. 12000" />
                                        </div>
                                    </div>
                                </div>

                                <div id="platYouTube" class="hide">
                                    <div class="row">
                                        <div>
                                            <label for="yt">YouTube Channel Link <span class="req">*</span></label>
                                            <input id="yt" name="youtube_link" placeholder="youtube channel link" />
                                        </div>
                                        <div>
                                            <label for="yts">Subscribers (optional)</label>
                                            <input id="yts" name="youtube_subscribers" inputmode="numeric" placeholder="e.g. 5000" />
                                        </div>
                                    </div>
                                </div>

                                <div id="platOther" class="hide">
                                    <div class="row">
                                        <div>
                                            <label for="opn">Platform Name <span class="req">*</span></label>
                                            <input id="opn" name="other_platform_name" placeholder="e.g. Moj, Josh, ShareChat" />
                                        </div>
                                        <div>
                                            <label for="opl">Profile Link <span class="req">*</span></label>
                                            <input id="opl" name="other_platform_link" placeholder="profile link" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="btnRow">
                            <button id="back2" class="btn secondary" type="button">Back</button>
                            <button id="next2" class="btn" type="button">Next</button>
                        </div>
                    </section>

                    <!-- STEP 3 -->
                    <section data-step="3" class="hide">
                        <div>
                            <label>Preferred payment method <span class="req">*</span></label>
                            <div class="choices" id="paymentChoices">
                                <label class="choice">
                                    <div class="l">
                                        <div class="badge">UPI</div>
                                        <div>
                                            <strong>UPI</strong>
                                            <span>UPI ID</span>
                                        </div>
                                    </div>
                                    <input type="radio" name="payment_method" value="upi" />
                                </label>
                                <label class="choice">
                                    <div class="l">
                                        <div class="badge">USDT</div>
                                        <div>
                                            <strong>USDT (TRC20)</strong>
                                            <span>Wallet address</span>
                                        </div>
                                    </div>
                                    <input type="radio" name="payment_method" value="usdt_trc20" />
                                </label>
                                <label class="choice">
                                    <div class="l">
                                        <div class="badge">PP</div>
                                        <div>
                                            <strong>PayPal</strong>
                                            <span>PayPal email</span>
                                        </div>
                                    </div>
                                    <input type="radio" name="payment_method" value="paypal" />
                                </label>
                                <label class="choice">
                                    <div class="l">
                                        <div class="badge">WIRE</div>
                                        <div>
                                            <strong>Wire Transfer</strong>
                                            <span>Bank details</span>
                                        </div>
                                    </div>
                                    <input type="radio" name="payment_method" value="wire_transfer" />
                                </label>
                            </div>
                            <div class="note">Please provide the required payment information for your selected method.</div>
                        </div>

                        <div class="card" style="margin-top:14px;background:rgba(10,18,35,.45)">
                            <div class="hd"><h2>Payment details</h2></div>
                            <div class="bd">
                                <div id="payUpi" class="hide">
                                    <div class="row">
                                        <div>
                                            <label for="upi_id">UPI ID <span class="req">*</span></label>
                                            <input id="upi_id" name="upi_id" placeholder="example@upi" />
                                        </div>
                                        <div>
                                            <label for="upi_name">Account holder name</label>
                                            <input id="upi_name" name="upi_name" placeholder="Name on UPI" />
                                        </div>
                                    </div>
                                </div>

                                <div id="payUsdt" class="hide">
                                    <div class="row">
                                        <div>
                                            <label for="usdt_address">USDT TRC20 Wallet Address <span class="req">*</span></label>
                                            <input id="usdt_address" name="usdt_address" placeholder="TRC20 address" />
                                        </div>
                                        <div>
                                            <label for="usdt_network">Network</label>
                                            <input id="usdt_network" name="usdt_network" value="TRC20" readonly />
                                        </div>
                                    </div>
                                </div>

                                <div id="payPaypal" class="hide">
                                    <div class="row">
                                        <div>
                                            <label for="paypal_email">PayPal Email <span class="req">*</span></label>
                                            <input id="paypal_email" name="paypal_email" type="email" placeholder="paypal@example.com" />
                                        </div>
                                        <div>
                                            <label for="paypal_name">Account name (optional)</label>
                                            <input id="paypal_name" name="paypal_name" placeholder="Name on PayPal" />
                                        </div>
                                    </div>
                                </div>

                                <div id="payWire" class="hide">
                                    <div class="row">
                                        <div>
                                            <label for="bank_name">Bank name <span class="req">*</span></label>
                                            <input id="bank_name" name="bank_name" placeholder="Bank name" />
                                        </div>
                                        <div>
                                            <label for="account_holder">Account holder name <span class="req">*</span></label>
                                            <input id="account_holder" name="account_holder" placeholder="Account holder name" />
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top:12px;">
                                        <div>
                                            <label for="account_number">Account number <span class="req">*</span></label>
                                            <input id="account_number" name="account_number" inputmode="numeric" placeholder="Account number" />
                                        </div>
                                        <div>
                                            <label for="ifsc_swift">IFSC / SWIFT <span class="req">*</span></label>
                                            <input id="ifsc_swift" name="ifsc_swift" placeholder="IFSC (India) or SWIFT" />
                                        </div>
                                    </div>
                                    <div class="row" style="margin-top:12px;">
                                        <div>
                                            <label for="bank_country">Bank country</label>
                                            <input id="bank_country" name="bank_country" placeholder="e.g. India" />
                                        </div>
                                        <div>
                                            <label for="bank_city">Bank city</label>
                                            <input id="bank_city" name="bank_city" placeholder="City" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div style="margin-top:12px;">
                            <label for="msg">Message (optional)</label>
                            <textarea id="msg" name="message" placeholder="Any extra info (avg views, engagement, etc.)"></textarea>
                        </div>

                        <div class="btnRow">
                            <button id="back3" class="btn secondary" type="button">Back</button>
                            <button id="next3" class="btn" type="button">Next</button>
                        </div>
                    </section>

                    <!-- STEP 4 -->
                    <section data-step="4" class="hide">
                        <div class="card" style="background:rgba(10,18,35,.45)">
                            <div class="hd"><h2>Download APK</h2></div>
                            <div class="bd">
                                <a id="apkDownloadBtn" class="linkBtn is-disabled" href="#" target="_blank" rel="noopener noreferrer">Download APK</a>
                                <div id="apkMeta" class="note" style="margin-top:8px;"></div>
                                <div class="note" style="margin-top:10px;">
                                    Install the APK on your phone. After installation, take a screenshot (App icon / installed screen) and upload it below.
                                </div>
                            </div>
                        </div>

                        <div style="margin-top:12px;">
                            <label for="apk_version">APK Version (optional)</label>
                            <input id="apk_version" name="apk_version" placeholder="e.g. 1.0.0" />
                        </div>

                        <div style="margin-top:12px;">
                            <label for="install_screenshot">Installation Screenshot <span class="req">*</span></label>
                            <input id="install_screenshot" name="install_screenshot" type="file" accept="image/*" />
                            <div class="note">If upload is not possible, you can paste a screenshot link below (Google Drive, etc.).</div>
                        </div>

                        <div style="margin-top:12px;">
                            <label for="install_screenshot_url">Screenshot Link (optional)</label>
                            <input id="install_screenshot_url" name="install_screenshot_url" placeholder="https://..." />
                        </div>

                        <!-- Honeypot -->
                        <input class="hp" name="website" tabindex="-1" autocomplete="off" />

                        <div class="btnRow">
                            <button id="back4" class="btn secondary" type="button">Back</button>
                            <button id="submitBtn" class="btn" type="submit">Submit Application</button>
                        </div>
                    </section>

                </form>
            </div>
        </div>

        <div class="card">
            <div class="hd"><h2>What we need</h2></div>
            <div class="bd">
                <ul class="side-list">
                    <li class="side-item">
                        <strong>Correct profile link</strong>
                        <span>Please share the correct platform link or username.</span>
                    </li>
                    <li class="side-item">
                        <strong>Followers/Subscribers</strong>
                        <span>Share approximate followers/subscribers (exact is not required).</span>
                    </li>
                    <li class="side-item">
                        <strong>Content niche</strong>
                        <span>Education, finance, news, entertainment, etc.</span>
                    </li>
                    <li class="side-item">
                        <strong>Fast response</strong>
                        <span>Keep your mobile number active — our team may call/WhatsApp you.</span>
                    </li>
                </ul>
                <div class="note" style="margin-top:12px;">
                    This form submits securely and stores your details for admin review.
                </div>
            </div>
        </div>
    </div>

    <div class="foot">© <?php echo date('Y'); ?> ADS SKILL INDIA</div>
</div>

<script>
    (function(){
        const form = document.getElementById('appForm');
        const btn = document.getElementById('submitBtn');
        const ok = document.getElementById('alertOk');
        const err = document.getElementById('alertErr');
        const errText = document.getElementById('alertErrText');
        const stepEls = Array.from(form.querySelectorAll('[data-step]'));
        const stepIndEls = Array.from(form.querySelectorAll('[data-step-ind]'));
        const apkBtn = document.getElementById('apkDownloadBtn');
        const apkMeta = document.getElementById('apkMeta');

        let step = 1;
        let leadKey = '';
        let apkDownloadUrl = '';
        let apkVersionLive = '';

        function show(el){ el.classList.add('show'); }
        function hide(el){ el.classList.remove('show'); }
        function unhide(el){ el.classList.remove('hide'); }
        function doHide(el){ el.classList.add('hide'); }

        function radioVal(name){
            const el = form.querySelector(`input[name="${name}"]:checked`);
            return el ? el.value : '';
        }

        function val(name){
            const el = form.querySelector(`[name="${name}"]`);
            return el ? (el.value || '').trim() : '';
        }

        function setStep(n){
            step = n;
            stepEls.forEach(s => {
                const sn = parseInt(s.getAttribute('data-step'), 10);
                if (sn === step) unhide(s); else doHide(s);
            });
            stepIndEls.forEach(s => {
                const sn = parseInt(s.getAttribute('data-step-ind'), 10);
                s.classList.remove('active','done');
                if (sn < step) s.classList.add('done');
                if (sn === step) s.classList.add('active');
            });
            window.scrollTo({top: 0, behavior: 'smooth'});
        }

        function toggleChoiceSelected(containerId, name){
            const wrap = document.getElementById(containerId);
            if (!wrap) return;
            const choices = Array.from(wrap.querySelectorAll('.choice'));
            choices.forEach(c => {
                const r = c.querySelector(`input[name="${name}"]`);
                if (!r) return;
                c.classList.toggle('selected', !!r.checked);
            });
        }

        function showPlatformBlock(platform){
            ['platInstagram','platFacebook','platYouTube','platOther'].forEach(id => doHide(document.getElementById(id)));
            if (platform === 'instagram') unhide(document.getElementById('platInstagram'));
            if (platform === 'facebook') unhide(document.getElementById('platFacebook'));
            if (platform === 'youtube') unhide(document.getElementById('platYouTube'));
            if (platform === 'other') unhide(document.getElementById('platOther'));
        }

        function showPaymentBlock(method){
            ['payUpi','payUsdt','payPaypal','payWire'].forEach(id => doHide(document.getElementById(id)));
            if (method === 'upi') unhide(document.getElementById('payUpi'));
            if (method === 'usdt_trc20') unhide(document.getElementById('payUsdt'));
            if (method === 'paypal') unhide(document.getElementById('payPaypal'));
            if (method === 'wire_transfer') unhide(document.getElementById('payWire'));
        }

        function validateStep1(){
            const nameV = val('name');
            const phoneV = val('phone');
            if (!nameV) return 'Please enter your full name.';
            if (!phoneV) return 'Please enter your mobile number.';
            if (phoneV.length < 8) return 'Please enter a valid mobile number.';
            return '';
        }

        function validateStep2(){
            const platform = radioVal('platform');
            if (!platform) return 'Please select one platform.';
            if (platform === 'instagram' && !val('instagram_handle')) return 'Please enter your Instagram username/link.';
            if (platform === 'facebook' && !val('facebook_link')) return 'Please enter your Facebook link.';
            if (platform === 'youtube' && !val('youtube_link')) return 'Please enter your YouTube channel link.';
            if (platform === 'other' && (!val('other_platform_name') || !val('other_platform_link'))) return 'Please enter other platform name and link.';
            return '';
        }

        function validateStep3(){
            const method = radioVal('payment_method');
            if (!method) return 'Please select a payment method.';
            if (method === 'upi' && !val('upi_id')) return 'Please enter your UPI ID.';
            if (method === 'usdt_trc20' && !val('usdt_address')) return 'Please enter your USDT TRC20 wallet address.';
            if (method === 'paypal' && !val('paypal_email')) return 'Please enter your PayPal email.';
            if (method === 'wire_transfer') {
                if (!val('bank_name') || !val('account_holder') || !val('account_number') || !val('ifsc_swift')) {
                    return 'Please fill all required wire transfer fields.';
                }
            }
            return '';
        }

        function validateStep4(){
            const file = form.querySelector('#install_screenshot');
            const hasFile = file && file.files && file.files.length > 0;
            const hasUrl = !!val('install_screenshot_url');
            if (!hasFile && !hasUrl) return 'Please upload the installation screenshot (or provide a screenshot link).';
            return '';
        }

        function genKey(){
            const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            let out = '';
            for (let i = 0; i < 32; i++) out += chars[Math.floor(Math.random() * chars.length)];
            return out;
        }

        async function saveDraft(stepNo){
            try{
                const platform = radioVal('platform');
                const paymentMethod = radioVal('payment_method');
                const payload = {
                    lead_key: leadKey,
                    last_step: stepNo,
                    name: val('name'),
                    phone: val('phone'),
                    email: val('email'),
                    city: val('city'),
                    platform: platform,
                    payment_method: paymentMethod,
                    payment_details: {
                        upi_id: val('upi_id'),
                        upi_name: val('upi_name'),
                        usdt_address: val('usdt_address'),
                        usdt_network: val('usdt_network'),
                        paypal_email: val('paypal_email'),
                        paypal_name: val('paypal_name'),
                        bank_name: val('bank_name'),
                        account_holder: val('account_holder'),
                        account_number: val('account_number'),
                        ifsc_swift: val('ifsc_swift'),
                        bank_country: val('bank_country'),
                        bank_city: val('bank_city'),
                    },
                    apk_download_url: apkDownloadUrl,
                    apk_version: val('apk_version') || apkVersionLive,
                    install_screenshot_url: val('install_screenshot_url'),
                    data: {
                        instagram_handle: val('instagram_handle'),
                        instagram_followers: val('instagram_followers'),
                        facebook_link: val('facebook_link'),
                        facebook_followers: val('facebook_followers'),
                        youtube_link: val('youtube_link'),
                        youtube_subscribers: val('youtube_subscribers'),
                        other_platform_name: val('other_platform_name'),
                        other_platform_link: val('other_platform_link'),
                        category: val('category'),
                        audience_language: val('audience_language'),
                        message: val('message'),
                    },
                    source_url: window.location.href,
                };

                await fetch('/api/influencer/draft', {
                    method: 'POST',
                    headers: {'Content-Type':'application/json','Accept':'application/json'},
                    body: JSON.stringify(payload),
                }).catch(() => null);
            }catch(_){}
        }

        // Step buttons
        const next1 = document.getElementById('next1');
        const back2 = document.getElementById('back2');
        const next2 = document.getElementById('next2');
        const back3 = document.getElementById('back3');
        const next3 = document.getElementById('next3');
        const back4 = document.getElementById('back4');

        next1.addEventListener('click', async function(){
            hide(ok); hide(err);
            const m = validateStep1();
            if (m) { errText.textContent = m; show(err); return; }
            await saveDraft(1);
            setStep(2);
        });
        back2.addEventListener('click', function(){ hide(ok); hide(err); setStep(1); });
        next2.addEventListener('click', async function(){
            hide(ok); hide(err);
            const m = validateStep2();
            if (m) { errText.textContent = m; show(err); return; }
            await saveDraft(2);
            setStep(3);
        });
        back3.addEventListener('click', function(){ hide(ok); hide(err); setStep(2); });
        next3.addEventListener('click', async function(){
            hide(ok); hide(err);
            const m = validateStep3();
            if (m) { errText.textContent = m; show(err); return; }
            await saveDraft(3);
            setStep(4);
        });
        back4.addEventListener('click', function(){ hide(ok); hide(err); setStep(3); });

        // Platform choice highlighting + conditional blocks
        form.addEventListener('change', function(e){
            if (e.target && e.target.name === 'platform') {
                toggleChoiceSelected('platformChoices', 'platform');
                showPlatformBlock(radioVal('platform'));
            }
            if (e.target && e.target.name === 'payment_method') {
                toggleChoiceSelected('paymentChoices', 'payment_method');
                showPaymentBlock(radioVal('payment_method'));
            }
        });

        form.addEventListener('submit', async function(e){
            e.preventDefault();
            hide(ok); hide(err);

            const m1 = validateStep1();
            if (m1) { errText.textContent = m1; show(err); setStep(1); return; }
            const m2 = validateStep2();
            if (m2) { errText.textContent = m2; show(err); setStep(2); return; }
            const m3 = validateStep3();
            if (m3) { errText.textContent = m3; show(err); setStep(3); return; }
            const m4 = validateStep4();
            if (m4) { errText.textContent = m4; show(err); setStep(4); return; }

            const platform = radioVal('platform');
            const paymentMethod = radioVal('payment_method');

            const payload = {
                name: val('name'),
                phone: val('phone'),
                email: val('email'),
                city: val('city'),
                platform,
                payment_method: paymentMethod,
                payment_details: {
                    upi_id: val('upi_id'),
                    upi_name: val('upi_name'),
                    usdt_address: val('usdt_address'),
                    usdt_network: val('usdt_network'),
                    paypal_email: val('paypal_email'),
                    paypal_name: val('paypal_name'),
                    bank_name: val('bank_name'),
                    account_holder: val('account_holder'),
                    account_number: val('account_number'),
                    ifsc_swift: val('ifsc_swift'),
                    bank_country: val('bank_country'),
                    bank_city: val('bank_city'),
                },
                apk_download_url: apkDownloadUrl || <?php echo json_encode($APK_DOWNLOAD_URL, JSON_UNESCAPED_SLASHES); ?>,
                apk_version: val('apk_version'),
                install_screenshot_url: val('install_screenshot_url'),
                lead_key: leadKey,
                data: {
                    platform: platform,
                    instagram_handle: val('instagram_handle'),
                    instagram_followers: val('instagram_followers'),
                    facebook_link: val('facebook_link'),
                    facebook_followers: val('facebook_followers'),
                    youtube_link: val('youtube_link'),
                    youtube_subscribers: val('youtube_subscribers'),
                    other_platform_name: val('other_platform_name'),
                    other_platform_link: val('other_platform_link'),
                    category: val('category'),
                    audience_language: val('audience_language'),
                    message: val('message'),
                    payment_method: paymentMethod,
                    payment_details: {
                        upi_id: val('upi_id'),
                        upi_name: val('upi_name'),
                        usdt_address: val('usdt_address'),
                        usdt_network: val('usdt_network'),
                        paypal_email: val('paypal_email'),
                        paypal_name: val('paypal_name'),
                        bank_name: val('bank_name'),
                        account_holder: val('account_holder'),
                        account_number: val('account_number'),
                        ifsc_swift: val('ifsc_swift'),
                        bank_country: val('bank_country'),
                        bank_city: val('bank_city'),
                    },
                    apk_download_url: apkDownloadUrl || <?php echo json_encode($APK_DOWNLOAD_URL, JSON_UNESCAPED_SLASHES); ?>,
                    apk_version: val('apk_version'),
                    install_screenshot_url: val('install_screenshot_url'),
                },
                source_url: window.location.href,
                website: val('website') // honeypot
            };

            btn.disabled = true;
            const oldText = btn.textContent;
            btn.textContent = 'Submitting...';
            try{
                const fd = new FormData();
                // basic scalars
                Object.keys(payload).forEach(k => {
                    const v = payload[k];
                    if (v === null || v === undefined) return;
                    if (typeof v === 'object') return;
                    fd.append(k, String(v));
                });
                // objects as JSON strings
                fd.append('data', JSON.stringify(payload.data || {}));
                fd.append('payment_details', JSON.stringify(payload.payment_details || {}));
                fd.append('platforms[]', payload.platform || '');
                fd.append('lead_key', leadKey);

                const fileEl = form.querySelector('#install_screenshot');
                if (fileEl && fileEl.files && fileEl.files[0]) {
                    fd.append('install_screenshot', fileEl.files[0]);
                }

                const res = await fetch('/api/influencer/apply', {
                    method: 'POST',
                    headers: {'Accept':'application/json'},
                    body: fd,
                });
                const json = await res.json().catch(() => null);
                if (!res.ok) {
                    const msg = (json && json.message && json.message.error) ? json.message.error : null;
                    errText.textContent = Array.isArray(msg) ? msg.join(', ') : (msg || 'Please check your details and try again.');
                    show(err);
                    return;
                }

                show(ok);
                form.reset();
                // reset UI
                toggleChoiceSelected('platformChoices', 'platform');
                toggleChoiceSelected('paymentChoices', 'payment_method');
                showPlatformBlock('');
                showPaymentBlock('');
                setStep(1);
            }catch(ex){
                errText.textContent = 'Network error. Please try again.';
                show(err);
            }finally{
                btn.disabled = false;
                btn.textContent = oldText;
            }
        });

        // init
        (function initPrefillAndConfig(){
            try{
                const params = new URLSearchParams(window.location.search || '');
                const lk = (params.get('lead_key') || '').trim();
                leadKey = lk || (localStorage.getItem('influencer_lead_key') || '');
                if (!leadKey) leadKey = genKey();
                localStorage.setItem('influencer_lead_key', leadKey);

                // Prefill fields from URL
                const prefill = (k, fieldName) => {
                    const v = (params.get(k) || '').trim();
                    if (!v) return;
                    const el = form.querySelector(`[name="${fieldName}"]`);
                    if (el) el.value = v;
                };
                prefill('name', 'name');
                prefill('phone', 'phone');
                prefill('email', 'email');
                prefill('city', 'city');
                prefill('category', 'category');
                prefill('audience_language', 'audience_language');
                prefill('instagram_handle', 'instagram_handle');
                prefill('facebook_link', 'facebook_link');
                prefill('youtube_link', 'youtube_link');
                prefill('other_platform_name', 'other_platform_name');
                prefill('other_platform_link', 'other_platform_link');

                const platform = (params.get('platform') || '').trim();
                if (platform) {
                    const r = form.querySelector(`input[name="platform"][value="${platform}"]`);
                    if (r) { r.checked = true; toggleChoiceSelected('platformChoices', 'platform'); showPlatformBlock(platform); }
                }

                // Load APK config from backend (so admin upload is reflected instantly)
                fetch('/api/influencer/config', { headers: {'Accept':'application/json'} })
                    .then(r => r.json())
                    .then(json => {
                        const cfg = json && json.data ? json.data : null;
                        const url = cfg && cfg.apk_url ? String(cfg.apk_url) : '';
                        const ver = cfg && cfg.apk_version ? String(cfg.apk_version) : '';
                        if (url && apkBtn) {
                            apkBtn.setAttribute('href', url);
                            apkBtn.classList.remove('is-disabled');
                            apkDownloadUrl = url;
                        } else if (apkBtn) {
                            apkBtn.setAttribute('href', '#');
                            apkBtn.classList.add('is-disabled');
                            apkDownloadUrl = '';
                        }
                        if (ver) apkVersionLive = ver;
                        if (apkMeta) {
                            const parts = [];
                            if (ver) parts.push('Version: ' + ver);
                            if (url) parts.push('APK available');
                            if (!url) parts.push('APK not uploaded yet (admin will upload)');
                            apkMeta.textContent = parts.join(' • ');
                        }
                    })
                    .catch(() => null);
            }catch(_){}
        })();

        setStep(1);
    })();
</script>
</body>
</html>

