<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Page</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="style.css" rel="stylesheet" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="../js/login.js"></script>
</head>

<body class="bg">

  <!-- Alert Section -->
  <div id="alert-container" class="flex relative z-10 items-center p-4 mb-4 hidden" role="alert">
    <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
      viewBox="0 0 20 20">
      <path
        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 1 1 1 1v4h1a1 1 0 0 1 0 2Z" />
    </svg>
    <div id="alert-message" class="ms-3 text-sm font-medium"></div>
  </div>

  <div class="bg flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-sm">
      <h2 class="text-2xl font-semibold text-center text-gray-700 mb-6">Login</h2>
      <form method="post" action="../Backend/login_verification.php" onsubmit="console.log('Form submitted to: ' + this.action);">
        <div class="mb-4">
          <label class="block text-gray-700 mb-2" for="username">Email</label>
          <input class="w-full px-3 py-2 border rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500"
            type="email" id="username" name="username" required placeholder="example@domain.com">
        </div>
        <div class="mb-6">
          <label class="block text-gray-700 mb-2" for="password">Kata Sandi</label>
          <input class="w-full px-3 py-2 border rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500"
            type="password" id="password" name="password" required placeholder="">
        </div>
        <div class="text-center mt-4">
          <a href="reset_password.php" class="text-sm text-blue-500 hover:text-blue-700">
            Lupa Password?
          </a>
        </div>
        <button
          class="w-full bg-teal-700 text-white py-2 rounded shadow hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500"
          type="submit">Login</button>
      </form>
      <p id="error-message" class="text-red-500 text-center mt-4 hidden"></p>
    </div>
  </div>

  <script>
    // Script untuk mengambil pesan dari URL (sebagai pengganti PHP)
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('m');

    if (message) {
      let displayMessage = "Pesan tidak dikenali.";
      let color = "red";

      switch (message) {
        case "success":
          displayMessage = "Registrasi Berhasil";
          color = "blue";
          break;
        case "wrong":
          displayMessage = "Username atau Password salah!";
          break;
        case "nfound":
          displayMessage = "Akun tidak ditemukan!";
          break;
        case "login_required":
          displayMessage = "Silahkan login terlebih dahulu!";
          break;
        case "unauthorized":
          displayMessage = "Anda tidak memiliki akses ke halaman tersebut!";
          break;
        case "logged_out":
          displayMessage = "Anda telah berhasil logout!";
          color = "blue";
          break;
      }

      const alertContainer = document.getElementById('alert-container');
      const alertMessage = document.getElementById('alert-message');

      alertContainer.classList.remove('hidden');
      alertContainer.classList.add(`text-${color}-800`, `border-t-4`, `border-${color}-300`, `bg-${color}-50`);
      alertMessage.innerText = displayMessage;
    }
  </script>

</body>

</html>