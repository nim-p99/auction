<?php
$my_id = $_SESSION['user_id'];

// get all threads with my user id in it
// join users to get other persons username
$sql = "
    SELECT 
        t.thread_id, 
        t.last_updated,
        u.username AS other_user,
        u.user_id AS other_user_id
    FROM message_threads t
    JOIN users u ON (
        (t.user1_id = ? AND t.user2_id = u.user_id) 
        OR 
        (t.user2_id = ? AND t.user1_id = u.user_id)
    )
    WHERE t.user1_id = ? OR t.user2_id = ?
    ORDER BY t.last_updated DESC
";

$query = $connection->prepare($sql);
$query->bind_param("iiii", $my_id, $my_id, $my_id, $my_id);
$query->execute();
$result = $query->get_result();
?>

<div class="list-group">
    <?php if ($result->num_rows == 0): ?>
        <p class="p-3">You have no messages yet.</p>
    <?php else: ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <a href="view_conversation.php?thread_id=<?php echo $row['thread_id']; ?>" class="list-group-item list-group-item-action">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1"><?php echo htmlspecialchars($row['other_user']); ?></h5>
                    <small><?php echo date('M d, H:i', strtotime($row['last_updated'])); ?></small>
                </div>
                <p class="mb-1 text-muted">Click to view conversation</p>
            </a>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<?php $query->close(); ?>
