window.TrackingGenerator = {
    generate: function(uuid, domain, type) {

        let trackingSnippet = '';
        let landingSnippet = '';

        if (type == 2) {
            // JavaScript Tracking Snippet
            trackingSnippet = `<script>
                const tokens = JSON.parse(localStorage.getItem('tracking_tokens') || '[]');
                const baseToken = "${uuid}";
                const ua = encodeURIComponent(navigator.userAgent);

                const matched = tokens.find(t => t.startsWith(baseToken + "_"));
                if (matched) {
                    const username = matched.split("_")[1] || '';
                    const endpoint = (username === "testingConnectionToServer") ? "connect" : "store";
                    fetch("${domain}/track/" + endpoint + "/" + baseToken + "?user_agent=" + ua + "&username=" + username)
                        .then(response => response.json())
                        .then(data => console.log('Tracking success:', data))
                        .catch(error => console.error('Tracking error:', error));
                }
            <\/script>`;

            landingSnippet = `<script>
                const params = new URLSearchParams(window.location.search);
                const token = params.get('ref');
                if (token) {
                    let tokens = JSON.parse(localStorage.getItem('tracking_tokens') || '[]');
                    const baseToken = token.split('_')[0];

                    const existingIndex = tokens.findIndex(t => t.startsWith(baseToken + "_"));
                    if (existingIndex !== -1) {
                        tokens[existingIndex] = token;
                    } else {
                        tokens.push(token);
                    }

                    localStorage.setItem('tracking_tokens', JSON.stringify(tokens));
                }
            <\/script>`;
        } 
        else if (type == 3) {
            // PHP Tracking Snippet
            trackingSnippet = 
                '<?php\n' +
                'session_start();\n' +
                '$tokens = isset($_SESSION["tracking_tokens"]) ? $_SESSION["tracking_tokens"] : [];\n' +
                '$baseToken = "' + uuid + '";\n' +
                '$matchedToken = null;\n\n' +
                'foreach ($tokens as $token) {\n' +
                '    if (strpos($token, $baseToken . "_") === 0) {\n' +
                '        $matchedToken = $token;\n' +
                '        break;\n' +
                '    }\n' +
                '}\n\n' +
                'if ($matchedToken) {\n' +
                '    $username = explode("_", $matchedToken)[1] ?? "";\n' +
                '    $ua = urlencode($_SERVER["HTTP_USER_AGENT"] ?? "");\n' +
                '    $endpoint = ($username === "testingConnectionToServer") ? "connect" : "store";\n' +
                '    $url = "' + domain + '/track/" . $endpoint . "/' + uuid + '?user_agent=" . $ua . "&username=" . $username;\n' +
                '    file_get_contents($url);\n' +
                '}\n' +
                '?>';

            landingSnippet = 
                '<?php\n' +
                'if (isset($_GET["ref"])) {\n' +
                '    session_start();\n' +
                '    $ref = $_GET["ref"];\n' +
                '    $baseToken = explode("_", $ref)[0];\n' +
                '    $tokens = isset($_SESSION["tracking_tokens"]) ? $_SESSION["tracking_tokens"] : [];\n' +
                '    $found = false;\n' +
                '    foreach ($tokens as $index => $t) {\n' +
                '        if (strpos($t, $baseToken . "_") === 0) {\n' +
                '            $tokens[$index] = $ref;\n' +
                '            $found = true;\n' +
                '            break;\n' +
                '        }\n' +
                '    }\n' +
                '    if (!$found) {\n' +
                '        $tokens[] = $ref;\n' +
                '    }\n' +
                '    $_SESSION["tracking_tokens"] = $tokens;\n' +
                '}\n' +
                '?>';
        }

        return {
            tracking: trackingSnippet,
            landing: landingSnippet
        };
    }
};