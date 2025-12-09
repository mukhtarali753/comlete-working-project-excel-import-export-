<!DOCTYPE html>
<html>
<head>
    <title>Debug Users</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Debug Users Loading</h1>
    <button id="testUsers">Test Load Users</button>
    <div id="result"></div>

    <script>
        $(document).ready(function() {
            $('#testUsers').click(function() {
                console.log('Testing users route...');
                
                $.ajax({
                    url: "/test-users-no-auth",
                    method: 'GET',
                    success: function (response) {
                        console.log('Success:', response);
                        $('#result').html('<pre>' + JSON.stringify(response, null, 2) + '</pre>');
                    },
                    error: function (xhr, status, error) {
                        console.error('Error:', xhr.responseText);
                        $('#result').html('<div style="color: red;">Error: ' + xhr.responseText + '</div>');
                    }
                });
            });
        });
    </script>
</body>
</html>
