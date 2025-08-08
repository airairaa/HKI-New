<?php
function generate_id($conn) {
    $year = date('Y');
    $q = "SELECT id_permohonan FROM detail_permohonan ORDER BY id_permohonan DESC LIMIT 1";
    $res = $conn->query($q);
    if ($res && $r = $res->fetch_assoc()) {
        list($pfx, $yr, $num) = explode('-', $r['id_permohonan']);
        $next = str_pad((int)$num + 1, 3, '0', STR_PAD_LEFT);
    } else {
        $next = '001';
    }
    return 'HKI-' . $year . '-' . $next;
}
?>