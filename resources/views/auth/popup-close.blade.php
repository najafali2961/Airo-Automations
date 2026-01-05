<!DOCTYPE html>
<html>

<head>
    <title>Authentication Successful</title>
</head>

<body>
    <script>
        // Notify the parent window
        if (window.opener) {
            window.opener.postMessage('google_auth_success', '*');
        }
        // Close the popup
        window.close();
    </script>
    <p>Authentication successful. You can close this window.</p>
</body>

</html>
