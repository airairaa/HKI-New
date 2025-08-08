document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('m');
    const alertSection = document.getElementById('alert-section');

    if (message) {
        let displayMessage = '';
        let color = 'red';

        switch (message) {
            case 'success':
                displayMessage = 'Registrasi Berhasil';
                color = 'blue';
                break;
            case 'wrong':
                displayMessage = 'Username atau Password salah!';
                break;
            case 'nfound':
                displayMessage = 'Akun tidak ditemukan!';
                break;
            default:
                displayMessage = 'Pesan tidak dikenali.';
        }

        alertSection.innerHTML = `
            <div class="flex relative z-10 items-center p-4 mb-4 text-${color}-800 border-t-4 border-${color}-300 bg-${color}-50 dark:text-${color}-400 dark:bg-gray-800 dark:border-${color}-800"
                 role="alert">
                <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                     viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
                <div class="ms-3 text-sm font-medium">
                    ${displayMessage}
                </div>
            </div>
        `;
    }
});