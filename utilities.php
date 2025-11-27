<?php

// display_time_remaining:
// Helper function to help figure out what time to display
function display_time_remaining($interval) {

    if ($interval->days == 0 && $interval->h == 0) {
      // Less than one hour remaining: print mins + seconds:
      $time_remaining = $interval->format('%im %Ss');
    }
    else if ($interval->days == 0) {
      // Less than one day remaining: print hrs + mins:
      $time_remaining = $interval->format('%hh %im');
    }
    else {
      // At least one day remaining: print days + hrs:
      $time_remaining = $interval->format('%ad %hh');
    }

  return $time_remaining;

}

// print_listing_li:
// This function prints an HTML <li> element containing an auction listing
function print_listing_li($item_id, $title, $desc, $price, $num_bids,$start_time, $end_time, $buy_now_price, $is_admin=False, $auction_id)
{
  // Truncate long descriptions
  if (strlen($desc) > 250) {
    $desc_shortened = substr($desc, 0, 250) . '...';
  }
  else {
    $desc_shortened = $desc;
  }
  
  // Fix language of bid vs. bids
  if ($num_bids == 1) {
    $bid = ' bid';
  }
  else {
    $bid = ' bids';
  }
  
  // Calculate time to auction end
  $now = new DateTime();
  if ($now > $end_time) {
    $time_remaining = 'This auction has ended';
  }
  elseif ($now < $start_time) {
    // Get interval:
    $time_to_start = date_diff($now, $start_time);
    $time_remaining = display_time_remaining($time_to_start) . ' until auction starts';
  }
    else {
    // Get interval:
    $time_to_end = date_diff($now, $end_time);
    $time_remaining = display_time_remaining($time_to_end) . ' remaining';
  }
  
  $buy_now_str ="";
  if (!is_null($buy_now_price)){
    $buy_now_str ='Buy Now: £' . $buy_now_price; 
  }

  if ($is_admin){
    $admin_button = '<div class="mt-2">
      <form method="POST" action="delete_listing.php" onsubmit="return confirm(\'Are you sure you want to disable this listing?\');"> 
        <input type="hidden" name="auction_id" value="'. $auction_id . '">
        <button type="submit" class="btn btn-danger btn-sm">
          <i class="fa fa-trash"></i> Disable Listing
        </button>
      </form>
    </div>';

    echo('
    <li class="list-group-item d-flex justify-content-between">
    <div class="p-2 mr-5"><h5><a href="listing.php?item_id=' . $item_id . '">' . $title . '</a></h5>' . $desc_shortened . '</div>
    <div class="text-center text-nowrap"><span style="font-size: 1.5em">£' . number_format($price, 2) . '</span><br/>' . $num_bids . $bid . '<br/>' . $time_remaining . '<br/>' . $buy_now_str . '<br/>' . $admin_button . '</div>
  </li>');
  }
  else{
    $insert_admin_button ="";
    echo('
    <li class="list-group-item d-flex justify-content-between">
    <div class="p-2 mr-5"><h5><a href="listing.php?item_id=' . $item_id . '">' . $title . '</a></h5>' . $desc_shortened . '</div>
    <div class="text-center text-nowrap"><span style="font-size: 1.5em">£' . number_format($price, 2) . '</span><br/>' . $num_bids . $bid . '<br/>' . $time_remaining . '<br/>' . $buy_now_str . '</div>
  </li>'
  );
  }
  // Print HTML
  
}

function list_table_items($table, $is_admin=false) { ?>
  <?php
 while ($row = mysqli_fetch_assoc($table)): ?>
      <div class="list-group"> 
        <?php 
        $item_id = $row['item_id'];
        $title = $row['title'];
        $description = $row['description'];
        $end_date = new DateTime($row['end_date_time']); 
        $current_price = $row['current_price']; 
        $num_bids =$row['num_bids'];
        $buy_now_price =$row['buy_now_price'];
        $start_date= new DateTime($row['start_date_time']);
        $auction_id= $row['auction_id'];

        //using the print listing function from utilities.php
        //need to update so if starting price > current price = print starting price not current price
        print_listing_li($item_id, $title, $description, $current_price, $num_bids, $start_date, $end_date, $buy_now_price,$is_admin, $auction_id) ?>
      
      </div>
      
  <?php endwhile; ?>
<?php } ?>

<?php 


function filter_by_keyword($connection, $keyword, $final_query) {
  if (!empty($keyword)) {
    $safe_keyword = mysqli_real_escape_string($connection, $keyword);
    $final_query.= " AND (i.title LIKE '%$safe_keyword%' OR i.description LIKE '%$safe_keyword%')";
  }
  return $final_query;
}



function filter_by_category($connection, $filter_cat, $final_query) {

    if ($filter_cat === 'all') {
        return $final_query;
    }

    $cat_id = (int)$filter_cat;

    // Is this a parent category?
    $check_if_parent = mysqli_query($connection, 
        "SELECT parent_category FROM category WHERE category_id = $cat_id");
    $parent = mysqli_fetch_assoc($check_if_parent)['parent_category'];
    

    // Parent category
    if ($parent === NULL) {
        // Parent category → fetch children
        $child_query = mysqli_query($connection, 
            "SELECT category_id FROM category WHERE parent_category = $cat_id");

        $child_ids = [];
        while ($child_row = mysqli_fetch_assoc($child_query)) {
            $child_ids[] = (int)$child_row['category_id'];
        }
        // If no children → treat parent as a normal category
        if (empty($child_ids)) {
            return $final_query . " AND c.category_id = $cat_id";
        }
        $ids = implode(',', $child_ids);
        return $final_query . " AND c.category_id IN ($ids)";
    }
    // Child category
    return $final_query . " AND c.category_id = $cat_id";
}





function sort_by($sort_by, $final_query) {
    $core_order = "(a.end_date_time > NOW()) DESC, (a.start_date_time <= NOW()) DESC";

    if ($sort_by === 'pricelow') {
        return $final_query . " ORDER BY $core_order, current_price ASC";
    }
    else if ($sort_by === 'pricehigh') {
        return $final_query . " ORDER BY $core_order, current_price DESC";
    } 
    else if ($sort_by === 'date_asc') {
        return $final_query . " ORDER BY $core_order, a.end_date_time ASC";
    } 
    else if ($sort_by === 'date_dsc') {
        return $final_query . " ORDER BY $core_order, a.end_date_time DESC";
    } 
    else if ($sort_by === 'buy_now_asc') {
        return $final_query . " AND a.buy_now_price IS NOT NULL ORDER BY $core_order, a.buy_now_price ASC";
    } 
    else if ($sort_by === 'buy_now_dsc') {
        return $final_query . " AND a.buy_now_price IS NOT NULL ORDER BY $core_order, a.buy_now_price DESC";
    } else {
        // default 'hot'
        return $final_query . " ORDER BY $core_order, num_bids DESC";
    }
}

function filter_by_keyword_admin($connection, $keyword, $final_query) {
  if (!empty($keyword)) {
    $safe_keyword = mysqli_real_escape_string($connection, $keyword);
    $final_query.= " AND CONCAT_WS(' ', u.first_name, u.family_name, u.username, u.email) LIKE '%$safe_keyword%'";
  }
  return $final_query;
  
}

function filter_by_account($connection, $filter_cat,  $final_query) {
  if ($filter_cat == 'all')
    return $final_query;
  else if ($filter_cat == 'buyer'){
    return $final_query .= " AND b.buyer_id IS NOT NULL"; 
  }
  else if ($filter_cat == 'seller') {
    return $final_query .=" AND s.seller_id IS NOT NULL";
  }
}

function sort_by_admin($sort_by, $final_query) {
  switch ($sort_by) {
    case 'alpha':
      $final_query .= " ORDER BY u.first_name ASC, u.family_name ASC";
      break;
    case 'r-alpha':
      $final_query .= " ORDER BY u.first_name DESC, u.family_name DESC";
      break;
    case'active':
      $final_query .= " ORDER BY u.acc_active DESC,u.user_id DESC";
      break;
    case'inactive':
      $final_query .= " ORDER BY u.acc_active ASC, u.user_id DESC";
      break;
    default:
      case'active':
      $final_query .= " ORDER BY u.user_id DESC"; //will result in most recent up the top
      break;
  }
    return $final_query;

  }





function count_rows_in_result($result) {
  $num_rows = mysqli_num_rows($result);
  return $num_rows;
}


function list_account_details($table) { ?>
  <table class="table table-striped table-hover mt-3">
    <thead class="thead-light">
      <tr>
        <th>Full Name</th>
        <th>User ID</th>
        <th>Username</th>
        <th>Email</th>
        <th class="text-center">Active</th>
        <th class="text-center">Seller ID</th>
        <th class="text-center">Seller Rating</th>
        <th class="text-center">Buyer ID</th>
        <th class="text-center">Buyer Rating</th>
        <th class="text-center">Disable/Enable</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($row = mysqli_fetch_assoc($table)): ?>
      <tr>
        <td><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['family_name']); ?></td>
        <td><?php echo htmlspecialchars($row['user_id']); ?></td>
        <td><?php echo htmlspecialchars($row['username']); ?></td>
        <td><?php echo htmlspecialchars($row['email']); ?></td>
        <td class="text-center">
          <?php if($row['acc_active']){
              echo ('Yes');
              $action = "disable";
              $button_class ="danger";
           } else {
              echo ('No');
              $action = "enable";
              $button_class="success";
            } ?> 
            
        </td>
        <td class="text-center"><?php echo htmlspecialchars($row['seller_id']); ?></td>
        <td class="text-center"><?php echo htmlspecialchars($row['avg_seller_rating']); ?></td>
        <td class="text-center"><?php echo htmlspecialchars($row['buyer_id']); ?></td>
        <td class="text-center"><?php echo htmlspecialchars($row['avg_buyer_rating']); ?></td>
        <td>
          <form method="POST" action="enable_acc.php" onsubmit="return confirm('Are you sure you want to <?php echo $action; ?> this account?');"> 
            <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
            <input type="hidden" name="action" value="<?php echo $action; ?>">
            <button type="submit" class="btn btn-<?php echo $button_class; ?> btn-sm">
              <i class="fa fa-power-off"></i> <?php echo ($action); ?>
            </button>
          </form>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
<?php } ?>


<?php
// print_bid_li: prints a single bid in list-group style
function print_bid_li($item_id, $title, $description, $bid_amount, $bid_date) {
    // Truncate long descriptions
    $desc_shortened = strlen($description) > 250 ? substr($description, 0, 250) . '...' : $description;

    echo '
    <li class="list-group-item d-flex justify-content-between">
        <div class="p-2 mr-5">
            <h5><a href="listing.php?item_id=' . $item_id . '">' . htmlspecialchars($title) . '</a></h5>
            ' . htmlspecialchars($desc_shortened) . '
        </div>
        <div class="text-center text-nowrap">
            <span><strong>Your bid:</strong> £' . number_format($bid_amount, 2) . '</span><br>
            <small>Placed on: ' . htmlspecialchars($bid_date) . '</small>
        </div>
    </li>
    ';
}

// list_user_bids: prints all bids using print_bid_li
function list_user_bids($result) {
    echo '<ul class="list-group">';
    while ($row = mysqli_fetch_assoc($result)) {
        $item_id     = $row['item_id'];
        $title       = $row['title'];
        $description = $row['description'] ?? '';
        $bid_amount  = $row['amount'];
        $bid_date    = $row['date'];

        print_bid_li($item_id, $title, $description, $bid_amount, $bid_date);
    }
    echo '</ul>';
}

// list_bid_history: only displays all bids for a specific auction in a table
function list_bid_history($result) {
    $num_bids = mysqli_num_rows($result); //gives number of results to correctly calculate
    
    if ($num_bids == 0) {
        echo '<p class="text-muted">No bids have been placed yet.</p>';
        return;
    }
    
    echo '
    <table class="table table-striped table-sm">
        <thead>
            <tr>
                <th>Bidder</th>
                <th>Bid amount</th>
                <th>Date & Time</th>
            </tr>
        </thead>
        <tbody>';
    
    while ($row = mysqli_fetch_assoc($result)) {
        $username = htmlspecialchars($row['username']);
        $amount = number_format($row['amount'], 2);
        $date = htmlspecialchars($row['date']);
        
        echo "
            <tr>
                <td>{$username}</td>
                <td>£{$amount}</td>
                <td>{$date}</td>
            </tr>";
    }
    
    echo '
        </tbody>
    </table>';

}?>
