<?php
session_start();
include 'register.php'; // Include your database connection file

// Function to check if the account is blocked
function isAccountBlocked($email) {
    global $conn;
    $query = "SELECT blocked_until FROM blocked_accounts WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (strtotime($row['blocked_until']) > time()) {
            return true; // Account is blocked
        } else {
            // Account is not blocked anymore, remove from blocked accounts
            $query = "DELETE FROM blocked_accounts WHERE email = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            return false;
        }
    }
    return false;
}

// Function to update failed attempts
function updateFailedAttempts($email) {
    global $conn;
    $query = "INSERT INTO login_attempts (email, attempts, last_attempt) VALUES (?, 1, CURRENT_TIMESTAMP)
              ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt = CURRENT_TIMESTAMP";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $email);
    $stmt->execute();
}

// Function to check if the account should be blocked
function checkAndBlockAccount($email) {
    global $conn;
    $query = "SELECT attempts, last_attempt FROM login_attempts WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['attempts'] >= 3 && (strtotime($row['last_attempt']) + 900) > time()) {
            // Block account for 15 minutes
            $blocked_until = date('Y-m-d H:i:s', strtotime('+15 minutes'));
            $query = "INSERT INTO blocked_accounts (email, blocked_until) VALUES (?, ?)
                      ON DUPLICATE KEY UPDATE blocked_until = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param('sss', $email, $blocked_until, $blocked_until);
            $stmt->execute();
            return true; // Account blocked
        }
    }
    return false;
}

$email = $_POST['email'];
$password = $_POST['password'];

// Check if the account is blocked
if (isAccountBlocked($email)) {
    echo "Your account is blocked. Please try again later.";
    exit();
}

// Verify credentials
$query = "SELECT admin_password FROM admins WHERE admin_email = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('s', $email);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($hashed_password);
$stmt->fetch();

if (password_verify($password, $hashed_password)) {
    // Successful login
    $_SESSION['email'] = $email;
    echo "Login successful!";
    // Reset failed attempts on successful login
    $query = "DELETE FROM login_attempts WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $email);
    $stmt->execute();
} else {
    // Failed login
    updateFailedAttempts($email);
    if (checkAndBlockAccount($email)) {
        echo "Your account has been blocked due to multiple failed login attempts. Please try again later.";
    } else {
        echo "Invalid credentials. Please try again.";
    }
}
?>
