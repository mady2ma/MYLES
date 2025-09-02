<?php
require_once 'config.php';

// Require authentication
requireAuth();

// Verify CSRF token for non-GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $csrf_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!verifyCsrfToken($csrf_token)) {
        ob_end_clean();
        header('HTTP/1.1 403 Forbidden');
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }
}

ob_start();
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

$conn = getDBConnection();
if (!$conn) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$user_id = $_SESSION['user_id'];

switch ($method) {
    case 'GET':
        switch ($_GET['action'] ?? '') {
            case 'collections':
                $result = $conn->query("SELECT * FROM collections");
                if ($result === false) {
                    error_log("GET collections error: " . $conn->error);
                    ob_end_clean();
                    echo json_encode(['success' => false, 'error' => $conn->error]);
                } else {
                    $data = $result->fetch_all(MYSQLI_ASSOC);
                    foreach ($data as &$row) {
                        foreach (['S', 'M', 'L', 'XL', 'XXL', 'XXXL', 'Mix'] as $size) {
                            $row[$size] = (int)$row[$size];
                        }
                    }
                    ob_end_clean();
                    echo json_encode(['success' => true, 'data' => $data]);
                }
                break;
            case 'transactions':
                $result = $conn->query("SELECT * FROM transactions ORDER BY timestamp DESC");
                if ($result === false) {
                    error_log("GET transactions error: " . $conn->error);
                    ob_end_clean();
                    echo json_encode(['success' => false, 'error' => $conn->error]);
                } else {
                    $data = $result->fetch_all(MYSQLI_ASSOC);
                    foreach ($data as &$row) {
                        foreach (['S', 'M', 'L', 'XL', 'XXL', 'XXXL', 'Mix'] as $size) {
                            $row[$size] = (int)$row[$size];
                        }
                    }
                    ob_end_clean();
                    echo json_encode(['success' => true, 'data' => $data]);
                }
                break;
            case 'last_entry':
                $result = $conn->query("SELECT * FROM transactions WHERE type = 'entry' ORDER BY timestamp DESC LIMIT 1");
                if ($result === false) {
                    error_log("GET last_entry error: " . $conn->error);
                    ob_end_clean();
                    echo json_encode(['success' => false, 'error' => $conn->error]);
                } else {
                    $data = $result->fetch_assoc() ?: null;
                    if ($data) {
                        foreach (['S', 'M', 'L', 'XL', 'XXL', 'XXXL', 'Mix'] as $size) {
                            $data[$size] = (int)$data[$size];
                        }
                    }
                    ob_end_clean();
                    echo json_encode(['success' => true, 'data' => $data]);
                }
                break;
            case 'last_issue':
                $result = $conn->query("SELECT * FROM transactions WHERE type = 'issue' ORDER BY timestamp DESC LIMIT 1");
                if ($result === false) {
                    error_log("GET last_issue error: " . $conn->error);
                    ob_end_clean();
                    echo json_encode(['success' => false, 'error' => $conn->error]);
                } else {
                    $data = $result->fetch_assoc() ?: null;
                    if ($data) {
                        foreach (['S', 'M', 'L', 'XL', 'XXL', 'XXXL', 'Mix'] as $size) {
                            $data[$size] = (int)$data[$size];
                        }
                    }
                    ob_end_clean();
                    echo json_encode(['success' => true, 'data' => $data]);
                }
                break;
            default:
                ob_end_clean();
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            ob_end_clean();
            echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
            exit;
        }
        switch ($data['action'] ?? '') {
            case 'add_collection':
                $stmt = $conn->prepare("INSERT INTO collections (name, created, last_updated, notes, S, M, L, XL, XXL, XXXL, Mix) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt === false) {
                    error_log("POST add_collection prepare error: " . $conn->error);
                    ob_end_clean();
                    echo json_encode(['success' => false, 'error' => $conn->error]);
                    exit;
                }
                $stmt->bind_param("ssssiiiiiii", $data['name'], $data['created'], $data['last_updated'], $data['notes'],
                    $data['quantities']['S'], $data['quantities']['M'], $data['quantities']['L'],
                    $data['quantities']['XL'], $data['quantities']['XXL'], $data['quantities']['XXXL'], $data['quantities']['Mix']);
                $success = $stmt->execute();
                $error = $stmt->error;
                $id = $conn->insert_id;
                $stmt->close();
                if (!$success) error_log("POST add_collection error: " . $error);
                ob_end_clean();
                echo json_encode(['success' => $success, 'id' => $id, 'error' => $error ?: null]);
                break;

            case 'add_transaction':
                $stmt = $conn->prepare("INSERT INTO transactions (type, collection_name, date, issued_to, notes, S, M, L, XL, XXL, XXXL, Mix, timestamp, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt === false) {
                    error_log("POST add_transaction prepare error: " . $conn->error);
                    ob_end_clean();
                    echo json_encode(['success' => false, 'error' => $conn->error]);
                    exit;
                }
                $issued_to = $data['issued_to'] ?? null;
                $stmt->bind_param("sssssiiiiiiisi", $data['type'], $data['collection_name'], $data['date'], $issued_to, $data['notes'],
                    $data['quantities']['S'], $data['quantities']['M'], $data['quantities']['L'],
                    $data['quantities']['XL'], $data['quantities']['XXL'], $data['quantities']['XXXL'], $data['quantities']['Mix'],
                    $data['timestamp'], $user_id);
                $success = $stmt->execute();
                $error = $stmt->error;
                $id = $conn->insert_id;
                $stmt->close();
                if (!$success) error_log("POST add_transaction error: " . $error);
                ob_end_clean();
                echo json_encode(['success' => $success, 'id' => $id, 'error' => $error ?: null]);
                break;

            default:
                ob_end_clean();
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            error_log("PUT: Invalid JSON input");
            ob_end_clean();
            echo json_encode(['success' => false, 'error' => 'Invalid JSON input']);
            exit;
        }

        if (isset($_GET['name'])) {
            $name = $_GET['name'];
            $stmt = $conn->prepare("UPDATE collections SET name=?, last_updated=?, notes=?, S=?, M=?, L=?, XL=?, XXL=?, XXXL=?, Mix=? WHERE name=?");
            if ($stmt === false) {
                error_log("PUT collections prepare error: " . $conn->error);
                ob_end_clean();
                echo json_encode(['success' => false, 'error' => $conn->error]);
                exit;
            }
            $new_name = $data['newName'] ?? $name;
            $last_updated = $data['last_updated'];
            $notes = $data['notes'];
            $s = $data['quantities']['S'];
            $m = $data['quantities']['M'];
            $l = $data['quantities']['L'];
            $xl = $data['quantities']['XL'];
            $xxl = $data['quantities']['XXL'];
            $xxxl = $data['quantities']['XXXL'];
            $mix = $data['quantities']['Mix'];
            $stmt->bind_param("sssiiiiiiis", $new_name, $last_updated, $notes, $s, $m, $l, $xl, $xxl, $xxxl, $mix, $name);
            $success = $stmt->execute();
            $error = $stmt->error;
            $stmt->close();
            if (!$success) error_log("PUT collections error: " . $error);
            ob_end_clean();
            echo json_encode(['success' => $success, 'error' => $error ?: null]);
        } elseif (isset($_GET['transaction_id'])) {
            $id = $_GET['transaction_id'];
            error_log("PUT transaction_id=$id: Starting update with data: " . json_encode($data));
            
            $stmt = $conn->prepare("UPDATE transactions SET type=?, collection_name=?, date=?, issued_to=?, notes=?, S=?, M=?, L=?, XL=?, XXL=?, XXXL=?, Mix=?, timestamp=?, user_id=? WHERE id=?");
            if ($stmt === false) {
                error_log("PUT transaction prepare error: " . $conn->error);
                ob_end_clean();
                echo json_encode(['success' => false, 'error' => 'Prepare failed: ' . $conn->error]);
                exit;
            }

            $type = $data['type'];
            $collection_name = $data['collection_name'];
            $date = $data['date'];
            $issued_to = isset($data['issued_to']) && $data['issued_to'] !== '' ? $data['issued_to'] : null;
            $notes = $data['notes'] ?? '';
            $s = $data['quantities']['S'];
            $m = $data['quantities']['M'];
            $l = $data['quantities']['L'];
            $xl = $data['quantities']['XL'];
            $xxl = $data['quantities']['XXL'];
            $xxxl = $data['quantities']['XXXL'];
            $mix = $data['quantities']['Mix'];
            $timestamp = $data['timestamp'];
            $id_param = $id;

            error_log("PUT transaction_id=$id: Binding params - type=$type, issued_to=" . ($issued_to ?? 'NULL'));
            $stmt->bind_param("sssssiiiiiiisii", 
                $type, $collection_name, $date, $issued_to, $notes, 
                $s, $m, $l, $xl, $xxl, $xxxl, $mix, 
                $timestamp, $user_id, $id_param
            );

            if ($stmt->error) {
                error_log("PUT transaction bind_param error: " . $stmt->error);
                ob_end_clean();
                echo json_encode(['success' => false, 'error' => 'Bind failed: ' . $stmt->error]);
                $stmt->close();
                exit;
            }

            $success = $stmt->execute();
            $error = $stmt->error;
            $affected = $conn->affected_rows;
            error_log("PUT transaction_id=$id: Execute result - success=$success, affected=$affected, error=" . ($error ?: 'none'));

            $stmt->close();
            ob_end_clean();
            if ($success && $affected > 0) {
                echo json_encode(['success' => true, 'error' => null]);
            } else {
                echo json_encode(['success' => false, 'error' => $error ?: 'No rows updated']);
            }
        } else {
            error_log("PUT: Missing name or transaction_id");
            ob_end_clean();
            echo json_encode(['success' => false, 'error' => 'Missing name or transaction_id']);
        }
        break;

    case 'DELETE':
        if (isset($_GET['name'])) {
            $stmt = $conn->prepare("DELETE FROM collections WHERE name=?");
            if ($stmt === false) {
                error_log("DELETE collections prepare error: " . $conn->error);
                ob_end_clean();
                echo json_encode(['success' => false, 'error' => $conn->error]);
                exit;
            }
            $stmt->bind_param("s", $_GET['name']);
            $success = $stmt->execute();
            $error = $stmt->error;
            $stmt->close();
            if (!$success) error_log("DELETE collections error: " . $error);
            ob_end_clean();
            echo json_encode(['success' => $success, 'error' => $error ?: null]);
        } elseif (isset($_GET['transaction_id'])) {
            $id = $_GET['transaction_id'];
            $stmt = $conn->prepare("DELETE FROM transactions WHERE id=?");
            if ($stmt === false) {
                error_log("DELETE transaction prepare error: " . $conn->error);
                ob_end_clean();
                echo json_encode(['success' => false, 'error' => $conn->error]);
                exit;
            }
            $stmt->bind_param("i", $id);
            $success = $stmt->execute();
            $error = $stmt->error;
            $affected = $conn->affected_rows;
            $stmt->close();
            if (!$success) error_log("DELETE transaction error: " . $error);
            ob_end_clean();
            echo json_encode(['success' => $success && $affected > 0, 'error' => $error ?: ($affected === 0 ? 'Transaction not found' : null)]);
        } else {
            ob_end_clean();
            echo json_encode(['success' => false, 'error' => 'Missing name or transaction_id']);
        }
        break;

    default:
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'Unsupported HTTP method']);
}

$conn->close();
ob_end_flush();
?>