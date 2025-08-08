console.log('input.js loaded, hapus-btn count:', document.querySelectorAll('.hapus-btn').length);

document.addEventListener('DOMContentLoaded', function () {
    const flash = document.getElementById('flash-message');
    const success = flash?.dataset.success;
    const error = flash?.dataset.error;

    if (success) {
        Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: success,
            timer: 3000,
            showConfirmButton: false
        });
    } else if (error) {
        Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: error,
            timer: 3000,
            showConfirmButton: false
        });
    }

    // Hapus dengan AJAX
    document.querySelectorAll('.hapus-btn').forEach(button => {
        button.addEventListener('click', function () {
            const id = this.dataset.id;
            const role = this.dataset.role;
            const dataid = new URLSearchParams(window.location.search).get('dataid');

            Swal.fire({
                title: 'Yakin ingin menghapus?',
                text: "Data tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('../Backend/proses_hapus_pengusul.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `delete_id=${encodeURIComponent(id)}&role=${encodeURIComponent(role)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil',
                                text: data.message,
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = `input.php?dataid=${encodeURIComponent(dataid)}&success=${encodeURIComponent(data.message)}`;
                            });
                        } else {
                            Swal.fire('Gagal', data.message, 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire('Error', 'Terjadi kesalahan saat menghapus.', 'error');
                    });
                }
            });
        });
    });
});
