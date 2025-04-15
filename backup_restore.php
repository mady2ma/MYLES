<?php
require_once 'config.php';
requireAuth();

// Verify CSRF token for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!verifyCsrfToken($csrf_token)) {
        header('HTTP/1.1 403 Forbidden');
        echo json_encode(['success' => false, 'error' => 'Invalid CSRF token']);
        exit;
    }
}

header('Content-Type: application/json');
$conn = getDBConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$action = $_GET['action'] ?? '';
switch ($action) {
    case 'backup':
        $tables = ['users', 'collections', 'transactions'];
        $sql = '';
        foreach ($tables as $table) {
            $result = $conn->query("SHOW CREATE TABLE $table");
            if ($result) {
                $row = $result->fetch_row();
                $sql .= "DROP TABLE IF EXISTS `$table`;\n" . $row[1] . ";\n\n";
            }

            $result = $conn->query("SELECT * FROM $table");
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $values = array_map([$conn, 'real_escape_string'], array_values($row));
                    $keys = array_keys($row);
                    $sql .= "INSERT INTO `$table` (`" . implode('`,`', $keys) . "`) VALUES ('" . implode("','", $values) . "');\n";
                }
                $sql .= "\n";
            }
        }
        echo json_encode(['success' => true, 'sql' => $sql]);
        break;

    case 'restore':
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data || !isset($data['sql'])) {
            echo json_encode(['success' => false, 'error' => 'Invalid SQL data']);
            exit;
        }

        $sql = $data['sql'];
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        $queries = array_filter(explode(';', $sql));
        foreach ($queries as $query) {
            $query = trim($query);
            if ($query && $conn->query($query) === false) {
                error_log("Restore error: " . $conn->error);
                echo json_encode(['success' => false, 'error' => $conn->error]);
                $conn->query("SET FOREIGN_KEY_CHECKS = 1");
                exit;
            }
        }
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

$conn->close();
?>