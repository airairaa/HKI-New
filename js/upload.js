document.addEventListener('DOMContentLoaded', function () {
    const requiredFields = [
        'file_sp',
        'file_sph',
        'file_contoh_karya',
        'file_ktp',
        'file_bukti_pembayaran'
    ];

    function validateUpload() {
        const allFilled = requiredFields.every(fieldName => {
            const input = document.querySelector(`input[name="${fieldName}"]`);
            return input && input.files.length > 0;
        });
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = !allFilled;
    }

    validateUpload();

    requiredFields.forEach(name => {
        const input = document.querySelector(`input[name="${name}"]`);
        if (input) {
            input.addEventListener('change', validateUpload);
        }
    });

    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const msg = urlParams.get('msg');

    if (status === 'success') {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: 'Upload dan penyimpanan data berhasil!',
            confirmButtonText: 'OK'
        }).then(() => {
            const form = document.querySelector('form');
            form.reset();
            window.location.href = 'daftar_user.php';
        });
    } else if (status === 'error') {
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: msg || 'Terjadi kesalahan saat mengupload!',
            confirmButtonText: 'OK'
        }).then(() => {
            window.location.href = 'upload.php';
        });
    }
});