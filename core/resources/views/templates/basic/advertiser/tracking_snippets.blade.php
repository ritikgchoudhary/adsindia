@extends($activeTemplate . 'layouts.master')
@section('content')
    <h6 class="flex-align gap-3"><i class="fa-solid fa-bullhorn"></i> @lang('Affiliate Conversion Tracking - Payment Success Integration')</h6>
    <div class="track-section">
        <p>@lang('This documentation explains how advertisers can integrate affiliate conversion tracking on their own website after a successful purchase.')</p>
        <div class="note text-white">
            <i class="fa-solid fa-square-check"></i>
            <p class="fs-14 text-white">
                <strong>@lang('Purpose'):</strong> @lang('Track affiliate conversions sales and notify the affiliate platform when a user makes a purchase after being referred by an affiliate.')
            </p>
        </div>
    </div>

    <div class="track-section">
        <h6 class="flex-align gap-3"><i class="fa-solid fa-thumbtack"></i> @lang('Step 1: Store Referral Tokens in Session')</h6>
        <p class="mb-3">@lang('When a user visits your site with a referral link like') <code>?ref=TOKEN_USERNAME</code>, store it in PHP session:</p>
        <div class="code-wrapper">
            <button class="copy-code-btn" data-target="code-1">
                <i class="fas fa-copy"></i>
                Copy
            </button>
            <pre><code id="code-1">&lt;?php
            if (isset($_GET["ref"])) {
                session_start();
                $ref = $_GET["ref"];
                $baseToken = explode("_", $ref)[0];
                $tokens = $_SESSION["tracking_tokens"] ?? [];
                $found = false;

                foreach ($tokens as $index =&gt; $t) {
                    if (strpos($t, $baseToken . "_") === 0) {
                        $tokens[$index] = $ref;
                        $found = true;
                        break;
                    }
                }

                if (!$found) {
                    $tokens[] = $ref;
                }

                $_SESSION["tracking_tokens"] = $tokens;
            }
            ?&gt;</code></pre>
        </div>

        <p>@lang('This ensures all referred visitors are tracked by their base token.')</p>
    </div>

    <div class="track-section">
        <h6 class="flex-align gap-3"><i class="fa-solid fa-thumbtack"></i> @lang('Step 2: Send Tracking Hit on Payment Success')</h6>
        <p class="mb-3">@lang('Once the payment is successful, call the affiliate tracking server from your backend using the session-stored referral:')</p>

        <div class="code-wrapper">
            <button class="copy-code-btn" data-target="code-2">
                <i class="fas fa-copy"></i>
                Copy
            </button>
            <pre><code id="code-2">&lt;?php
session_start();
$tokens = $_SESSION["tracking_tokens"] ?? [];
$baseToken = "ebc800dc-6cdb-41ee-bf1f-1996dccfee0a"; // Product-specific or campaign token
$matchedToken = null;

foreach ($tokens as $token) {
    if (strpos($token, $baseToken . "_") === 0) {
        $matchedToken = $token;
        break;
    }
}

if ($matchedToken) {
    $username = explode("_", $matchedToken)[1] ?? "";
    $ua = urlencode($_SERVER["HTTP_USER_AGENT"] ?? "");
    $endpoint = ($username === "testingConnectionToServer") ? "connect" : "store";
    
    // Replace localhost with your actual API domain
    $url = "https://yourplatform.com/track/" . $endpoint . "/" . $baseToken . "?user_agent=" . $ua . "&username=" . $username;
    
    @file_get_contents($url); // Send tracking data
}
?&gt;</code></pre>
        </div>

        <div class="note">
            <span>
                <i class="fa-solid fa-square-check"></i> @lang('This code should be added on your') <strong>@lang('payment success or order confirmation')</strong> @lang('page.')
            </span>
        </div>
    </div>

    <div class="track-section">
        <h6 class="flex-align gap-3"><i class="fa-solid fa-shield-halved"></i> @lang('Tips')</h6>
        <ul class="track-section-list">
            <li>@lang('Make sure session is started on both the landing page and payment success page.')</li>
            <li>@lang('Use HTTPS on production URLs for security.')</li>
            <li>@lang('Store the referrer data in database for reporting if needed.')</li>
        </ul>
    </div>

    <div class="track-section">
        <h6 class="flex-align gap-3"><i class="fa-solid fa-wrench"></i> @lang('API Endpoint Example')</h6>
        <p class="mb-3">@lang('The following request is sent from the advertiser\'s site to your affiliate system:')</p>
        <pre><code>@lang('GET https://yourplatform.com/track/store/ebc800dc-6cdb-41ee-bf1f-1996dccfee0a?user_agent=encoded-user-agent&amp;username=eyJpdiI6Im5JNnhBMTdNcERoRmFuMzZ0cmdmOWc9PSIsInZhbHVlIjoiY1gvUDRlSTY2RFgramZHWVhzdlRwZz09IiwibWFjIjoiOWQ3ODg1ZjRlZTIxYTkzYjI0YTBjY2UyNmY4NzY5NWMzNTAzZTI3MzY3Y2FkYjgzNWI4YmZlMDMxMGI2YjQ0MCIsInRhZyI6IiJ9')</code></pre>
    </div>
    @php
        $contact = getContent('contact_us.content', true);
    @endphp

    <div class="track-section">
        <h6 class="flex-align gap-3"><i class="fa-solid fa-phone"></i> @lang('Support')</h6>
        <p class="mb-3">@lang('If you need assistance implementing this, contact our developer support team:')</p>
        <ul class="track-social-list">
            <li><strong class="text-white">@lang('Email'):</strong> <a href="mailto:{{ $contact?->data_values?->email_address }}">{{ $contact?->data_values?->email_address }}</a></li>
            <li><strong class="text-white">@lang('Phone'):</strong> <a href="tel:{{ $contact?->data_values?->contact_number }}">{{ $contact?->data_values?->contact_number }}</a></li>
        </ul>
    </div>
@endsection


@push('style')
    <style>
        p {
            font-weight: 300;
            color: #fff0f0b3;
        }

        code {
            padding: 2px 6px;
            border-radius: 4px;
            font-family: hsl(var(--body-font));
            line-break: anywhere;
            white-space: pre-wrap;
        }


        .code-wrapper {
            position: relative;
            margin-bottom: 30px;
            max-width: 1200px;
        }


        pre {
            background-color: #0b0c0e;
            border: 1px solid hsl(var(--white) / .1);
            padding: 15px;
            overflow-x: auto;
            border-radius: 8px;
            max-width: 1200px;
        }

        .note {
            background-color: hsl(var(--section-bg));
            padding: 10px 15px;
            margin: 20px 0;
            border: 1px solid hsl(var(--white) / .1);
            border-radius: 8px;
            max-width: 1200px;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .note i {
            line-height: 1.6;
        }

        .success-box {
            background-color: hsl(var(--section-bg));
            border-left: 4px solid hsl(var(--base));
            padding: 10px 15px;
            margin: 20px 0;
        }

        .track-section {
            margin-bottom: 40px;
        }

        .copy-code-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: hsl(var(--white) / .1);
            color: #fff;
            border: none;
            padding: 6px 10px;
            font-size: 12px;
            border-radius: 4px;
            cursor: pointer;
            border: 1px solid hsl(var(--white) / .1);
        }

        .copy-code-btn:hover {
            background: hsl(var(--white) / .2);
        }

        .track-section-list {
            padding-left: 20px;
        }

        .track-section-list li {
            list-style: circle;
            font-size: 0.875rem;
        }

        .track-section-list li:not(:last-child),
        .track-social-list li:not(:last-child) {
            margin-bottom: 12px;
        }
    </style>
@endpush


@push('script')
    <script>
        (function($) {
            "use strict";
            $('.copy-code-btn').on('click', function() {
                const targetId = $(this).data('target');
                const code = document.getElementById(targetId).innerText;

                const tempInput = $('<textarea>');
                $('body').append(tempInput);
                tempInput.val(code).select();

                try {
                    document.execCommand('copy');
                    notify('success', 'Code copied to clipboard!');
                } catch (e) {
                    notify('error', 'Failed to copy code.');
                }

                tempInput.remove();
            });
        })(jQuery)
    </script>
@endpush
