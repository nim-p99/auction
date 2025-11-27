<?php
session_start();
require_once "database.php";

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: browse.php");
    exit();
}

$sender_id = $_SESSION['user_id'];
$recipient_id = intval($_POST['recipient_id']);
$body = trim($_POST['message_body']);
$return_url = $_POST['return_url'] ?? 'my_profile.php?section=messages';

if (empty($body) || empty($recipient_id)) {
    $_SESSION['error'] = "Message cannot be empty.";
    header("Location: $return_url");
    exit();
}

// determine user1 (low) and user2 (high) for the thread
$user1 = min($sender_id, $recipient_id);
$user2 = max($sender_id, $recipient_id);

// check if thread exists
$query = $connection->prepare("SELECT thread_id FROM message_threads WHERE user1_id = ? AND user2_id = ?");
$query->bind_param("ii", $user1, $user2);
$query->execute();
$query->bind_result($thread_id);
$query->fetch();
$query->close();

// if no thread, create one
if (!$thread_id) {
    $insertThread = $connection->prepare("INSERT INTO message_threads (user1_id, user2_id) VALUES (?, ?)");
    $insertThread->bind_param("ii", $user1, $user2);
    $insertThread->execute();
    $thread_id = $connection->insert_id;
    $insertThread->close();
} else {
    // update timestamp on existing thread
    $updateThread = $connection->prepare("UPDATE message_threads SET last_updated = NOW() WHERE thread_id = ?");
    $updateThread->bind_param("i", $thread_id);
    $updateThread->execute();
    $updateThread->close();
}

// insert the message
$insertMsg = $connection->prepare("INSERT INTO messages (thread_id, sender_id, message_body) VALUES (?, ?, ?)");
$insertMsg->bind_param("iis", $thread_id, $sender_id, $body);

if ($insertMsg->execute()) {
    // If sent from seller profile, add success message to session
    // (You'll need to add logic to your pages to display $_SESSION['flash_success'])
    // $_SESSION['flash_success'] = "Message sent!";
}

$insertMsg->close();

header("Location: $return_url");
exit();
?>
