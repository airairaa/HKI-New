<?php
include '../Backend/session_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Session</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-md mx-auto bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-4">Session Test</h1>
        
        <div class="mb-4">
            <p><strong>User ID:</strong> <?php echo $_SESSION['user_id'] ?? 'Not set'; ?></p>
            <p><strong>Role:</strong> <?php echo $_SESSION['role'] ?? 'Not set'; ?></p>
            <p><strong>Session Status:</strong> <span class="text-green-600">✓ Active</span></p>
        </div>
        
        <div class="space-y-2">
            <a href="menu_input.php" class="block w-full bg-blue-500 text-white text-center py-2 rounded hover:bg-blue-600">
                Go to Menu Input
            </a>
            <a href="daftar_user.php" class="block w-full bg-green-500 text-white text-center py-2 rounded hover:bg-green-600">
                Go to Daftar User
            </a>
            <a href="logout.php" class="block w-full bg-red-500 text-white text-center py-2 rounded hover:bg-red-600">
                Logout
            </a>
        </div>
    </div>
</body>
</html> 