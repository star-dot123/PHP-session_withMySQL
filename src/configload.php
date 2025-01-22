<?php
// データベース接続情報
$dsn = 'mysql:host=localhost;dbname=your_database;charset=utf8';
$username = 'your_username';
$password = 'your_password';

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

// クッキー名
define('SESSION_COOKIE', 'SUID');
define('AUTH_COOKIE', 'AUTHID');
// クッキーの有効期限（デフォルトは1時間）
define('COOKIE_TIME',3600);
// セッション用のSQLテーブル名
$session_sqltable = 'sessions';

// セッション管理関数
function sql_session($action, $name = null, $value = null) {
    global $pdo;

    switch ($action) {
        case 'start':
            // セッション開始
            if (!isset($_COOKIE[SESSION_COOKIE]) || !isset($_COOKIE[AUTH_COOKIE])) {
                $sessionId = bin2hex(random_bytes(128));
                $authId = bin2hex(random_bytes(128));
                setcookie(SESSION_COOKIE, $sessionId, time() + COOKIE_TIME, '/', '', true, true);
                setcookie(AUTH_COOKIE, $authId, time() + COOKIE_TIME, '/', '', true, true);
                $stmt = $pdo->prepare("INSERT INTO $session_sqltable (session_id, auth_id, data) VALUES (:session_id, :auth_id, :data)");
                $stmt->execute(['session_id' => $sessionId, 'auth_id' => $authId, 'data' => json_encode([])]);
            }
            break;

        case 'inset':
            // セッションデータの挿入・更新
            if (isset($_COOKIE[SESSION_COOKIE], $_COOKIE[AUTH_COOKIE])) {
                $stmt = $pdo->prepare("SELECT data FROM $session_sqltable WHERE session_id = :session_id AND auth_id = :auth_id");
                $stmt->execute(['session_id' => $_COOKIE[SESSION_COOKIE], 'auth_id' => $_COOKIE[AUTH_COOKIE]]);
                $session = $stmt->fetch();
                if ($session) {
                    $data = json_decode($session['data'], true);
                    $data[$name] = $value;
                    $stmt = $pdo->prepare("UPDATE $session_sqltable SET data = :data WHERE session_id = :session_id AND auth_id = :auth_id");
                    $stmt->execute(['data' => json_encode($data), 'session_id' => $_COOKIE[SESSION_COOKIE], 'auth_id' => $_COOKIE[AUTH_COOKIE]]);
                }
            }
            break;

        case 'delete':
            // セッションデータの削除
            if (isset($_COOKIE[SESSION_COOKIE], $_COOKIE[AUTH_COOKIE])) {
                $stmt = $pdo->prepare("SELECT data FROM $session_sqltable WHERE session_id = :session_id AND auth_id = :auth_id");
                $stmt->execute(['session_id' => $_COOKIE[SESSION_COOKIE], 'auth_id' => $_COOKIE[AUTH_COOKIE]]);
                $session = $stmt->fetch();
                if ($session) {
                    $data = json_decode($session['data'], true);
                    unset($data[$name]);
                    $stmt = $pdo->prepare("UPDATE $session_sqltable SET data = :data WHERE session_id = :session_id AND auth_id = :auth_id");
                    $stmt->execute(['data' => json_encode($data), 'session_id' => $_COOKIE[SESSION_COOKIE], 'auth_id' => $_COOKIE[AUTH_COOKIE]]);
                }
            }
            break;

        case 'take':
            // セッションデータの取得
            if (isset($_COOKIE[SESSION_COOKIE], $_COOKIE[AUTH_COOKIE])) {
                $stmt = $pdo->prepare("SELECT data FROM $session_sqltable WHERE session_id = :session_id AND auth_id = :auth_id");
                $stmt->execute(['session_id' => $_COOKIE[SESSION_COOKIE], 'auth_id' => $_COOKIE[AUTH_COOKIE]]);
                $session = $stmt->fetch();
                if ($session) {
                    $data = json_decode($session['data'], true);
                    return $data[$name] ?? null;
                }
            }
            return null;

        case 'take_all':
            // セッションデータ全体の取得
            if (isset($_COOKIE[SESSION_COOKIE], $_COOKIE[AUTH_COOKIE])) {
                $stmt = $pdo->prepare("SELECT data FROM $session_sqltable WHERE session_id = :session_id AND auth_id = :auth_id");
                $stmt->execute(['session_id' => $_COOKIE[SESSION_COOKIE], 'auth_id' => $_COOKIE[AUTH_COOKIE]]);
                $session = $stmt->fetch();
                if ($session) {
                    return json_decode($session['data'], true); // 全データを配列で返す
                }
            }
            return null;

        default:
            throw new InvalidArgumentException('Invalid action specified');
    }
}
