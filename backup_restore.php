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

    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
}

$conn->close();
?>