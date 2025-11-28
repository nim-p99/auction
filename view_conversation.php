<?php
// 1. Include Header & Config
include_once "includes/header.php";
require_once "config/database.php";

// 2. Access Control
if (!isset($_SESSION['logged_in'])) { 
    header("Location: " . BASE_URL . "/login.php"); 
    exit(); 
}

$thread_id = $_GET['thread_id'] ?? 0;
$my_id = $_SESSION['user_id'];

// 3. Verify Thread Ownership
$check = $connection->prepare("SELECT user1_id, user2_id FROM message_threads WHERE thread_id = ?");
$check->bind_param("i", $thread_id);
$check->execute();
$check->bind_result($u1, $u2);

if (!$check->fetch() || ($u1 != $my_id && $u2 != $my_id)) {
    // Stop execution if not authorized
    echo "<div class='container mt-5'><div class='alert alert-danger'>Access denied or thread not found.</div></div>";
    include_once "includes/footer.php";
    exit();
}
$check->close();

// Identify the other user ID
$other_user_id = ($u1 == $my_id) ? $u2 : $u1;

// 4. Fetch Messages
$msgQuery = $connection->prepare("
    SELECT m.message_body, m.created_at, m.sender_id, u.username 
    FROM messages m
    JOIN users u ON m.sender_id = u.user_id
    WHERE m.thread_id = ? 
    ORDER BY m.created_at ASC
");
$msgQuery->bind_param("i", $thread_id);
$msgQuery->execute();
$messages = $msgQuery->get_result();
?>

<div class="container mt-4">
    <h3>Conversation</h3>
    
    <a href="<?php echo BASE_URL; ?>/my_profile.php?section=messages" class="btn btn-secondary mb-3">&laquo; Back to Inbox</a>

    <div class="card shadow-sm" style="height: 500px; display: flex; flex-direction: column;">
        
        <div class="card-body" style="flex: 1; overflow-y: auto; background: #f8f9fa;" id="chat-box">
            <?php if ($messages->num_rows > 0): ?>
                <?php while($msg = $messages->fetch_assoc()): 
                    $is_me = ($msg['sender_id'] == $my_id);
                    $align = $is_me ? 'text-right' : 'text-left';
                    $bg = $is_me ? 'bg-primary text-white' : 'bg-white border';
                ?>
                    <div class="<?php echo $align; ?> mb-3">
                        <div class="d-inline-block p-3 rounded <?php echo $bg; ?>" style="max-width: 75%; text-align: left;">
                            <small class="d-block font-weight-bold mb-1" style="opacity: 0.8;">
                                <?php echo htmlspecialchars($msg['username']); ?>
                            </small>
                            <?php echo nl2br(htmlspecialchars($msg['message_body'])); ?>
                        </div>
                        <small class="d-block text-muted mt-1">
                            <?php echo date('M d, H:i', strtotime($msg['created_at'])); ?>
                        </small>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center text-muted mt-5">No messages yet. Say hello!</p>
            <?php endif; ?>
        </div>

        <div class="card-footer bg-white">
            <form action="<?php echo BASE_URL; ?>/actions/process_send_message.php" method="POST">
                
                <input type="hidden" name="recipient_id" value="<?php echo $other_user_id; ?>">
                <input type="hidden" name="return_url" value="view_conversation.php?thread_id=<?php echo $thread_id; ?>">
                
                <div class="input-group">
                    <textarea name="message_body" class="form-control" rows="2" placeholder="Type a reply..." required></textarea>
                    <div class="input-group-append">
                        <button class="btn btn-success px-4" type="submit">
                            <i class="fa fa-paper-plane"></i> Send
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    var chatBox = document.getElementById("chat-box");
    chatBox.scrollTop = chatBox.scrollHeight;
</script>

<?php include_once "includes/footer.php"; ?>
