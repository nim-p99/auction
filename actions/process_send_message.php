<?php
// 1. Start Session & Connect DB
session_start();
require_once "../config/database.php";

// 2. Access Control
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "/browse.php");
    exit();
}

// 3. Extract Data
$sender_id = $_SESSION['user_id'];
$recipient_id = intval($_POST['recipient_id']);
$body = trim($_POST['message_body']);

// 4. Handle Return URL logic
$default_return = "my_profile.php?section=messages&tab=inbox";
$raw_return_url = $_POST['return_url'] ?? $default_return;
$final_redirect = BASE_URL . "/" . ltrim($raw_return_url, '/');

// 5. Validation
if (empty($body) || empty($recipient_id)) {
    $_SESSION['error_message'] = "Message cannot be empty.";
    header("Location: " . $final_redirect);
    exit();
}

// 6. Determine Thread Users (Low ID first, High ID second)
$user1 = min($sender_id, $recipient_id);
$user2 = max($sender_id, $recipient_id);

// 7. Check if thread exists
$query = $connection->prepare("SELECT thread_id FROM message_threads WHERE user1_id = ? AND user2_id = ?");
$query->bind_param("ii", $user1, $user2);
$query->execute();
$query->bind_result($thread_id);
$query->fetch();
$query->close();

// 8. Create Thread if missing
if (!$thread_id) {
    $insertThread = $connection->prepare("INSERT INTO message_threads (user1_id, user2_id) VALUES (?, ?)");
    $insertThread->bind_param("ii", $user1, $user2);
    $insertThread->execute();
    $thread_id = $connection->insert_id;
    $insertThread->close();
} else {
    // Update timestamp on existing thread
    $updateThread = $connection->prepare("UPDATE message_threads SET last_updated = NOW() WHERE thread_id = ?");
    $updateThread->bind_param("i", $thread_id);
    $updateThread->execute();
    $updateThread->close();
}

// 9. Insert Message
$insertMsg = $connection->prepare("INSERT INTO messages (thread_id, sender_id, message_body, created_at) VALUES (?, ?, ?, NOW())");
$insertMsg->bind_param("iis", $thread_id, $sender_id, $body);

if ($insertMsg->execute()) {
    $_SESSION['success_message'] = "Message sent successfully.";
} else {
    $_SESSION['error_message'] = "Failed to send message.";
}

$insertMsg->close();

// 10. Redirect
header("Location: " . $final_redirect);
exit();
?>
