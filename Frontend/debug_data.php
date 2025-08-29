<?php
include '../Backend/session_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug Data</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-4">Debug Data Loading</h1>
        
        <div class="mb-4 p-4 bg-blue-50 rounded">
            <h2 class="font-bold mb-2">Session Information:</h2>
            <p><strong>User ID:</strong> <?php echo $_SESSION['user_id'] ?? 'NULL'; ?></p>
            <p><strong>Role:</strong> <?php echo $_SESSION['role'] ?? 'NULL'; ?></p>
        </div>
        
        <div class="mb-4">
            <button onclick="testDataLoad()" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                Test Data Loading
            </button>
            <button onclick="checkDatabase()" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 ml-2">
                Check Database
            </button>
        </div>
        
        <div id="result" class="bg-gray-50 p-4 rounded border">
            <p class="text-gray-500">Click the buttons above to test...</p>
        </div>
        
        <div class="mt-4">
            <a href="daftar_user.php" class="bg-purple-500 text-white px-4 py-2 rounded hover:bg-purple-600">
                Go to Daftar User
            </a>
        </div>
    </div>

    <script>
        function testDataLoad() {
            const resultDiv = document.getElementById("result");
            resultDiv.innerHTML = '<p class="text-blue-500">Loading...</p>';
            
            fetch('../Backend/get_daftar_user.php?page=1')
                .then(response => {
                    console.log('Response status:', response.status);
                    console.log('Response headers:', response.headers);
                    return response.text();
                })
                .then(text => {
                    console.log('Raw response:', text);
                    try {
                        const data = JSON.parse(text);
                        resultDiv.innerHTML = `
                            <h3 class="font-bold mb-2 text-green-600">Data Loaded Successfully!</h3>
                            <p><strong>Total Records:</strong> ${data.totalRows || 0}</p>
                            <p><strong>Current Page:</strong> ${data.currentPage || 1}</p>
                            <p><strong>Total Pages:</strong> ${data.totalPages || 1}</p>
                            <p><strong>Records in this page:</strong> ${data.data ? data.data.length : 0}</p>
                            <div class="mt-4">
                                <h4 class="font-semibold">Sample Data:</h4>
                                <pre class="bg-white p-2 rounded text-xs overflow-auto max-h-40">${JSON.stringify(data.data ? data.data.slice(0, 3) : [], null, 2)}</pre>
                            </div>
                        `;
                    } catch (e) {
                        resultDiv.innerHTML = `
                            <h3 class="font-bold mb-2 text-red-600">JSON Parse Error!</h3>
                            <p>Raw response:</p>
                            <pre class="bg-red-50 p-2 rounded text-xs overflow-auto max-h-40">${text}</pre>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    resultDiv.innerHTML = `
                        <h3 class="font-bold mb-2 text-red-600">Error!</h3>
                        <p class="text-red-500">Error loading data:</p>
                        <pre class="bg-red-50 p-2 rounded text-xs">${error.message}</pre>
                    `;
                });
        }
        
        function checkDatabase() {
            const resultDiv = document.getElementById("result");
            resultDiv.innerHTML = '<p class="text-blue-500">Checking database...</p>';
            
            fetch('../Backend/check_database.php')
                .then(response => response.text())
                .then(text => {
                    resultDiv.innerHTML = `
                        <h3 class="font-bold mb-2">Database Check Result:</h3>
                        <pre class="bg-white p-2 rounded text-xs overflow-auto max-h-40">${text}</pre>
                    `;
                })
                .catch(error => {
                    resultDiv.innerHTML = `
                        <h3 class="font-bold mb-2 text-red-600">Database Check Error!</h3>
                        <pre class="bg-red-50 p-2 rounded text-xs">${error.message}</pre>
                    `;
                });
        }
    </script>
</body>
</html> 