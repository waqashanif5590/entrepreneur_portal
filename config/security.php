<?php

function requireAccountRole(mysqli $conn, array $allowedRoles): string
{
    if (empty($_SESSION['loggedin']) || empty($_SESSION['id'])) {
        http_response_code(403);
        exit('Access denied.');
    }

    $accountId = (int)$_SESSION['id'];
    $statement = $conn->prepare('SELECT entity_type, status FROM accounts WHERE id = ? LIMIT 1');
    $statement->bind_param('i', $accountId);
    $statement->execute();
    $account = $statement->get_result()->fetch_assoc();
    $statement->close();

    $role = $account['entity_type'] ?? '';
    if (!in_array($role, $allowedRoles, true) || ($account['status'] ?? '') !== 'Active') {
        http_response_code(403);
        exit('Access denied.');
    }

    return $role;
}

function csrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function requirePostCsrfToken(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        header('Allow: POST');
        exit('Method not allowed.');
    }

    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!is_string($submittedToken) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
        http_response_code(403);
        exit('Invalid request token.');
    }
}

function requestPositiveId($value): ?int
{
    if (!is_string($value) || !preg_match('/\A[0-9]+\z/', $value)) {
        return null;
    }

    $id = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    return $id === false ? null : $id;
}

function canAccessConversation(mysqli $conn, int $accountId, string $role, int $otherAccountId): bool
{
    if ($accountId === $otherAccountId) {
        return false;
    }

    if ($role === 'user') {
        $statement = $conn->prepare("SELECT 1 FROM accounts JOIN profiles ON profiles.agent_account_id = accounts.id WHERE accounts.id = ? AND accounts.entity_type = 'agent' AND accounts.status <> 'Blocked' AND profiles.status = 'Approved' LIMIT 1");
        $statement->bind_param('i', $otherAccountId);
    } elseif ($role === 'agent') {
        $profileStatement = $conn->prepare("SELECT 1 FROM profiles WHERE agent_account_id = ? AND status = 'Approved' LIMIT 1");
        $profileStatement->bind_param('i', $accountId);
        $profileStatement->execute();
        $agentIsApproved = $profileStatement->get_result()->num_rows > 0;
        $profileStatement->close();
        if (!$agentIsApproved) {
            return false;
        }

        $statement = $conn->prepare("SELECT 1 FROM accounts WHERE id = ? AND entity_type = 'user' AND status <> 'Blocked' AND EXISTS (SELECT 1 FROM messages WHERE (sender_id = accounts.id AND receiver_id = ?) OR (sender_id = ? AND receiver_id = accounts.id)) LIMIT 1");
        $statement->bind_param('iii', $otherAccountId, $accountId, $accountId);
    } else {
        return false;
    }

    $statement->execute();
    $allowed = $statement->get_result()->num_rows > 0;
    $statement->close();
    return $allowed;
}
