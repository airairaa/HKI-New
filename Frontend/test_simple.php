<?php
include '../Backend/session_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simple Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-4">Simple Test</h1>
        
        <div class="mb-4 p-4 bg-blue-50 rounded">
            <h2 class="font-bold mb-2">Session Info:</h2>
            <p><strong>User ID:</strong> <?php echo $_SESSION['user_id'] ?? 'NULL'; ?></p>
            <p><strong>Role:</strong> <?php echo $_SESSION['role'] ?? 'NULL'; ?></p>
        </div>
        
        <div class="mb-4">
            <button onclick="testAPI()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Test API Call
            </button>
        </div>
        
        <div id="result" class="bg-gray-50 p-4 rounded border">
            <p class="text-gray-500">Click the button to test...</p>
        </div>
        
        <div class="mt-4 space-y-2">
            <a href="daftar_user.php" class="block bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 text-center">
                Go to Daftar User
            </a>
            <a href="debug_data.php" class="block bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600 text-center">
                Debug Data
            </a>
        </div>
    </div>

    <script>
        function testAPI() {
            const resultDiv = document.getElementById("result");
            resultDiv.innerHTML = '<p class="text-blue-500">Testing API...</p>';
            
            fetch('../Backend/get_daftar_user.php?page=1')
                .then(response => response.json())
                .then(data => {
                    resultDiv.innerHTML = `
                        <h3 class="font-bold mb-2 text-green-600">✓ API Working!</h3>
                        <p><strong>Total Records:</strong> ${data.totalRows || 0}</p>
                        <p><strong>Records in this page:</strong> ${data.data ? data.data.length : 0}</p>
                        <p><strong>Status:</strong> ${data.data && data.data.length > 0 ? 'Data Found' : 'No Data'}</p>
                    `;
                })
                .catch(error => {
                    resultDiv.innerHTML = `
                        <h3 class="font-bold mb-2 text-red-600">✗ API Error!</h3>
                        <p class="text-red-500">${error.message}</p>
                    `;
                });
        }
    </script>
</body>
</html> 