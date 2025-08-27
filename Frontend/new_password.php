<?php
session_start();
if (!isset($_GET['token'])) {
    header("Location: login.php");
    exit();
}
$token = $_GET['token'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Password Baru</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen p-6">
    <div class="w-full max-w-md">
        <form action="../Backend/update_password.php" method="POST" class="bg-white shadow-md rounded-lg px-8 pt-6 pb-8 mb-4">
            <h2 class="text-2xl font-bold mb-6 text-center text-gray-800">Set Password Baru</h2>
            
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                    Password Baru
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"
                       id="password" 
                       type="password" 
                       name="password"
                       required
                       minlength="8">
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="confirm_password">
                    Konfirmasi Password
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline"
                       id="confirm_password" 
                       type="password" 
                       name="confirm_password"
                       required
                       minlength="8">
            </div>

            <div class="flex items-center justify-between">
                <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" 
                        type="submit">
                    Update Password
                </button>
            </div>
        </form>
    </div>
</body>
</html>