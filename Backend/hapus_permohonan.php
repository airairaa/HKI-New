<?php
define('API_REQUEST', true);
define('DEBUG_MODE', true);

error_reporting(E_ALL);
ini_set('display_errors', DEBUG_MODE ? 1 : 0);
session_start();

include __DIR__ . '/../koneksi.php';
header('Content-Type: application/json; charset=utf-8');

function sendJson($payload, $code = 200)
{
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

$started_transaction = false;

try {
    // DEBUG: log incoming request + session
    @file_put_contents(__DIR__ . '/debug_hapus_permohonan.log',
        date('c') . " POST=" . json_encode($_POST, JSON_UNESCAPED_UNICODE)
        . " RAW=" . @file_get_contents('php://input')
        . " COOKIE=" . json_encode($_COOKIE, JSON_UNESCAPED_UNICODE)
        . " SESSION=" . json_encode(['user_id'=>$_SESSION['user_id'] ?? null,'role'=>$_SESSION['role'] ?? null], JSON_UNESCAPED_UNICODE)
        . PHP_EOL, FILE_APPEND);

    if (!isset($_SESSION['user_id'])) sendJson(['success' => false, 'message' => 'Unauthorized'], 401);
    if (!($conn instanceof mysqli)) throw new Exception('DB connection not available');

    $user_id   = (int) $_SESSION['user_id'];
    $role      = $_SESSION['role'] ?? '';
    $isAdmin   = ($role === 'admin');

    $detail_id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_POST['detail_id']) ? (int)$_POST['detail_id'] : 0);
    $dataid    = isset($_POST['dataid']) ? (int)$_POST['dataid'] : (isset($_POST['data_id']) ? (int)$_POST['data_id'] : 0);
    $upload_id = isset($_POST['upload_id']) ? (int)$_POST['upload_id'] : 0;

    $uploadDir = realpath(__DIR__ . '/../uploads') ?: (__DIR__ . '/../uploads');

    $conn->begin_transaction();
    $started_transaction = true;

    // small helper untuk bind array ints
    $bindIntArray = function (mysqli_stmt $stmt, array $values) {
        if (empty($values)) return;
        $types = str_repeat('i', count($values));
        $refs = [];
        foreach ($values as $k => $v) $refs[] = &$values[$k];
        array_unshift($refs, $types);
        call_user_func_array([$stmt, 'bind_param'], $refs);
    };

    $fetchUploadRows = function (array $ids) use ($conn, $bindIntArray) {
        if (empty($ids)) return [];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "SELECT id, file_ktp, file_contoh_karya, file_sp, file_sph, file_bukti_pembayaran, user_id, dataid FROM uploads WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) throw new Exception('Prepare failed (select uploads): ' . $conn->error);
        $bindVals = $ids;
        $bindIntArray($stmt, $bindVals);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = $res->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    };

    $deleteUploadsByIds = function (array $ids) use ($conn, $uploadDir, $fetchUploadRows, $bindIntArray) {
        if (empty($ids)) return 0;
        $rows = $fetchUploadRows($ids);
        foreach ($rows as $r) {
            foreach (['file_ktp','file_contoh_karya','file_sp','file_sph','file_bukti_pembayaran'] as $col) {
                if (!empty($r[$col])) {
                    $p = $uploadDir . DIRECTORY_SEPARATOR . $r[$col];
                    if (is_file($p)) @unlink($p);
                }
            }
        }
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $sql = "DELETE FROM uploads WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) throw new Exception('Prepare failed (delete uploads): ' . $conn->error);
        $bindIntArray($stmt, $ids);
        if (!$stmt->execute()) {
            $err = $stmt->error;
            $stmt->close();
            throw new Exception('Failed to delete uploads: ' . $err);
        }
        $affected = $stmt->affected_rows;
        $stmt->close();
        return $affected;
    };

    $getUploadIdsByDataId = function (int $dataid, bool $onlyOwned, int $user_id) use ($conn) {
        if ($onlyOwned) {
            $stmt = $conn->prepare('SELECT id FROM uploads WHERE dataid = ? AND user_id = ?');
            if ($stmt === false) throw new Exception('Prepare failed: ' . $conn->error);
            $stmt->bind_param('ii', $dataid, $user_id);
        } else {
            $stmt = $conn->prepare('SELECT id FROM uploads WHERE dataid = ?');
            if ($stmt === false) throw new Exception('Prepare failed: ' . $conn->error);
            $stmt->bind_param('i', $dataid);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $ids = [];
        while ($rw = $res->fetch_assoc()) $ids[] = (int)$rw['id'];
        $stmt->close();
        return $ids;
    };

    // 1) upload_id -> delete single upload (owner or admin)
    if ($upload_id > 0) {
        $stmt = $conn->prepare('SELECT user_id FROM uploads WHERE id = ? LIMIT 1');
        if ($stmt === false) throw new Exception('Prepare failed: ' . $conn->error);
        $stmt->bind_param('i', $upload_id);
        $stmt->execute();
        $r = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$r) {
            $conn->rollback();
            $started_transaction = false;
            sendJson(['success'=>false,'message'=>'Upload not found','received'=>['upload_id'=>$upload_id,'detail_id'=>$detail_id,'dataid'=>$dataid]]);
        }
        if ((int)$r['user_id'] !== $user_id && !$isAdmin) {
            $conn->rollback();
            $started_transaction = false;
            sendJson(['success'=>false,'message'=>'Forbidden','reason'=>'not_owner_of_upload','received'=>['upload_id'=>$upload_id,'detail_id'=>$detail_id,'dataid'=>$dataid]]);
        }
        $deleted = $deleteUploadsByIds([$upload_id]);
        $conn->commit();
        $started_transaction = false;
        sendJson(['success'=>true,'message'=>'Upload deleted','uploads_deleted'=>$deleted]);
    }

    $target_dataid = $detail_id > 0 ? $detail_id : ($dataid > 0 ? $dataid : 0);

    if ($target_dataid > 0) {
        // check detail owner (if exists)
        $stmt = $conn->prepare('SELECT user_id FROM detail_permohonan WHERE id = ? LIMIT 1');
        if ($stmt === false) throw new Exception('Prepare failed: ' . $conn->error);
        $stmt->bind_param('i', $target_dataid);
        $stmt->execute();
        $detailRow = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $ownerOfDetail = $detailRow ? (int)$detailRow['user_id'] : null;

        // treat unassigned detail (user_id == 0) as deletable by authenticated user (log for audit)
        $isUnassigned = ($ownerOfDetail !== null && $ownerOfDetail === 0);
        if ($isUnassigned) {
            @file_put_contents(__DIR__ . '/debug_hapus_permohonan.log', date('c') . " NOTE: target_dataid={$target_dataid} is unassigned (owner=0) - allowing user_id={$user_id} to delete\n", FILE_APPEND);
        }

        // gather ids for diagnostics
        $ownedIds = $getUploadIdsByDataId($target_dataid, true, $user_id); // uploads by current user
        $allIds   = $getUploadIdsByDataId($target_dataid, false, $user_id); // all uploads for dataid

        // write diagnostics to log
        @file_put_contents(__DIR__ . '/debug_hapus_permohonan.log',
            date('c') . " DIAG target_dataid={$target_dataid} ownerOfDetail=" . json_encode($ownerOfDetail)
            . " ownedIds=" . json_encode($ownedIds) . " allIds=" . json_encode($allIds) . PHP_EOL, FILE_APPEND);

        // if admin, owner, or unassigned -> delete all and detail row
        if ($isAdmin || $isUnassigned || ($ownerOfDetail !== null && $ownerOfDetail === $user_id)) {
            $deletedCount = 0;
            if (!empty($allIds)) $deletedCount = $deleteUploadsByIds($allIds);

            if ($ownerOfDetail !== null || $isUnassigned) {
                if ($isAdmin || $isUnassigned) {
                    $stmtDel = $conn->prepare('DELETE FROM detail_permohonan WHERE id = ?');
                    if ($stmtDel === false) throw new Exception('Prepare failed: ' . $conn->error);
                    $stmtDel->bind_param('i', $target_dataid);
                } else {
                    $stmtDel = $conn->prepare('DELETE FROM detail_permohonan WHERE id = ? AND user_id = ?');
                    if ($stmtDel === false) throw new Exception('Prepare failed: ' . $conn->error);
                    $stmtDel->bind_param('ii', $target_dataid, $user_id);
                }
                if (!$stmtDel->execute()) { $err = $stmtDel->error; $stmtDel->close(); throw new Exception('Failed deleting detail: ' . $err); }
                $detailDeleted = $stmtDel->affected_rows > 0;
                $stmtDel->close();
            } else {
                $detailDeleted = false;
            }

            $conn->commit();
            $started_transaction = false;
            sendJson(['success'=>true,'message'=>'Detail and related uploads deleted','uploads_deleted'=>$deletedCount,'detail_deleted'=>$detailDeleted,'diagnostics'=>['ownerOfDetail'=>$ownerOfDetail,'ownedIds'=>$ownedIds,'allIds'=>$allIds]]);
        }

        // not owner/admin: delete only user's uploads (if any)
        if (!empty($ownedIds)) {
            $deletedCount = $deleteUploadsByIds($ownedIds);
            $conn->commit();
            $started_transaction = false;
            sendJson(['success'=>true,'message'=>'Your uploads for this dataid were deleted (detail preserved)','uploads_deleted'=>$deletedCount,'diagnostics'=>['ownerOfDetail'=>$ownerOfDetail,'ownedIds'=>$ownedIds,'allIds'=>$allIds]]);
        }

        // nothing to delete -> return diagnostics to help frontend fix
        $conn->rollback();
        $started_transaction = false;
        sendJson(['success'=>false,'message'=>'Forbidden or no deletable items','diagnostics'=>['ownerOfDetail'=>$ownerOfDetail,'ownedIds'=>$ownedIds,'allIds'=>$allIds,'received'=>['detail_id'=>$detail_id,'dataid'=>$dataid,'upload_id'=>$upload_id]]], 403);
    }

    $conn->rollback();
    $started_transaction = false;
    sendJson(['success'=>false,'message'=>'No valid identifier provided','received'=>['detail_id'=>$detail_id,'dataid'=>$dataid,'upload_id'=>$upload_id]], 400);

} catch (Exception $e) {
    if (isset($conn) && $started_transaction) {
        $conn->rollback();
        $started_transaction = false;
    }
    error_log('hapus_permohonan error: ' . $e->getMessage());
    $msg = (defined('DEBUG_MODE') && DEBUG_MODE) ? $e->getMessage() : 'Server error';
    sendJson(['success'=>false,'message'=>$msg,'mysqli_error'=>$conn->error ?? null], 500);
}
?>