<?php
include_once "header.php";
require_once "database.php";

if (!isset($_SESSION['logged_in'])) { header("Location: login.php"); exit(); }

$thread_id = $_GET['thread_id'] ?? 0;
$my_id = $_SESSION['user_id'];

// check this thread belongs to the current user
$check = $connection->prepare("SELECT user1_id, user2_id FROM message_threads WHERE thread_id = ?");
$check->bind_param("i", $thread_id);
$check->execute();
$check->bind_result($u1, $u2);
if (!$check->fetch() || ($u1 != $my_id && $u2 != $my_id)) {
    die("Access denied or thread not found.");
}
$check->close();

// get other user ID
$other_user_id = ($u1 == $my_id) ? $u2 : $u1;

// get all essages
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
    <a href="my_profile.php?section=messages" class="btn btn-secondary mb-3">&laquo; Back to Inbox</a>

    <div class="card" style="height: 400px; overflow-y: scroll; background: #f9f9f9;">
        <div class="card-body">
            <?php while($msg = $messages->fetch_assoc()): 
                $is_me = ($msg['sender_id'] == $my_id);
                $align = $is_me ? 'text-right' : 'text-left';
                $bg = $is_me ? 'bg-primary text-white' : 'bg-light border';
            ?>
                <div class="<?php echo $align; ?> mb-2">
                    <div class="d-inline-block p-2 rounded <?php echo $bg; ?>" style="max-width: 70%;">
                        <small class="d-block font-weight-bold"><?php echo htmlspecialchars($msg['username']); ?></small>
                        <?php echo nl2br(htmlspecialchars($msg['message_body'])); ?>
                    </div>
                    <small class="d-block text-muted"><?php echo date('H:i', strtotime($msg['created_at'])); ?></small>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <form action="process_send_message.php" method="POST" class="mt-3">
        <input type="hidden" name="recipient_id" value="<?php echo $other_user_id; ?>">
        <input type="hidden" name="return_url" value="view_conversation.php?thread_id=<?php echo $thread_id; ?>">
        <div class="input-group">
            <textarea name="message_body" class="form-control" placeholder="Type a reply..." required></textarea>
            <div class="input-group-append">
                <button class="btn btn-success" type="submit">Send</button>
            </div>
        </div>
    </form>
</div>

<?php include_once "footer.php"; ?>
