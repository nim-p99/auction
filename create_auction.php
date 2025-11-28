<?php 

include_once "includes/header.php";


// If user is not logged in, redirect to login page
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}
?>

<div class="container">

<div style="max-width: 800px; margin: 10px auto">
  <h2 class="my-3">Create new auction</h2>
  <div class="card">
    <div class="card-body">
      
      <form method="POST" action="<?php echo BASE_URL; ?>/actions/create_auction_result.php">
        
        <div class="form-group row">
          <label for="auctionTitle" class="col-sm-2 col-form-label text-right">Title of auction</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" id="auctionTitle" name="auctionTitle" placeholder="e.g. Black mountain bike" required>
            <small id="titleHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> A short description of the item you're selling, which will display in listings.</small>
          </div>
        </div>

        <div class="form-group row">
          <label for="auctionDetails" class="col-sm-2 col-form-label text-right">Details</label>
          <div class="col-sm-10">
            <textarea class="form-control" id="auctionDetails" name="auctionDetails" rows="4"></textarea>
            <small id="detailsHelp" class="form-text text-muted">Full details of the listing to help bidders decide if it's what they're looking for.</small>
          </div>
        </div>

        <div class="form-group row">
          <label for="auctionCategory" class="col-sm-2 col-form-label text-right">Category</label>
          <div class="col-sm-10">
            <select class="form-control" id="auctionCategory" name="auctionCategory" required>
              <option value="">Choose...</option>
              <?php
                // Load categories from the database
                $sql = "SELECT c.category_id, c.category_name, c.parent_category 
                        FROM category c
                        LEFT JOIN category p ON c.parent_category = p.category_id
                        ORDER BY 
                          CASE WHEN c.parent_category IS NULL THEN c.category_name ELSE p.category_name END,
                          CASE WHEN c.parent_category IS NULL THEN '' ELSE c.category_name END";
                
                $result = mysqli_query($connection, $sql);

                if ($result) {
                  while ($row = mysqli_fetch_assoc($result)) {
                    $label = $row['category_name'];

                    // Indent child categories
                    if (!is_null($row['parent_category'])) {
                      $label = '— ' . $label;
                    }

                    echo '<option value="' . htmlspecialchars($row['category_id']) . '">'
                       . htmlspecialchars($label) . '</option>';
                  }
                }
              ?>
            </select>
            <small id="categoryHelp" class="form-text text-muted">
              <span class="text-danger">* Required.</span> Select a category for this item.
            </small>
          </div>
        </div>
