document.addEventListener("DOMContentLoaded", function () {
    const dataid = new URLSearchParams(window.location.search).get("dataid");

    if (dataid) {
        fetch(`../Backend/proses_dataid.php?dataid=${dataid}`)
            .then((response) => response.json())
            .then((result) => {
                if (result.success && result.data) {
                    const data = result.data;
                    document.getElementById("nama").value = data.Nama || "";
                    document.getElementById("alamat").value = data.Alamat || "";
                    document.getElementById("kode_pos").value = data.Kode_Pos || "";
                    document.getElementById("nomer_telepon").value = data.Nomor_Telepon || "";
                    document.getElementById("fakultas").value = data.Fakultas || "";
                    document.getElementById("role").value = data.role || "";
                    document.getElementById("email").value = data.Email || "";
                }
            })
            .catch((error) => console.error("Error:", error));
    }
});

document.getElementById('tambahPengusulBtn').addEventListener('click', ()=>{
    const id = sessionStorage.getItem('id_permohonan');
    window.location.href = `dataid.php?id_permohonan=${id}`;
  });