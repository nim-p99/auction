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
function print_listing_li($item_id, $title, $desc, $price, $num_bids,$start_time, $end_time, $buy_now_price)
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
  
  // Print HTML
  echo('
    <li class="list-group-item d-flex justify-content-between">
    <div class="p-2 mr-5"><h5><a href="listing.php?item_id=' . $item_id . '">' . $title . '</a></h5>' . $desc_shortened . '</div>
    <div class="text-center text-nowrap"><span style="font-size: 1.5em">£' . number_format($price, 2) . '</span><br/>' . $num_bids . $bid . '<br/>' . $time_remaining . '<br/>' . $buy_now_str . '</div>
  </li>'
  );
}

function list_table_items($table) { ?>
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

        //using the print listing function from utilities.php
        //need to update so if starting price > current price = print starting price not current price
        print_listing_li($item_id, $title, $description, $current_price, $num_bids, $start_date, $end_date, $buy_now_price) ?>
      
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





function count_rows_in_result($result) {
  $num_rows = mysqli_num_rows($result);
  return $num_rows;
}
?>
