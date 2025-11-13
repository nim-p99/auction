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
function print_listing_li($item_id, $title, $desc, $price, $num_bids, $end_time)
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
  else {
    // Get interval:
    $time_to_end = date_diff($now, $end_time);
    $time_remaining = display_time_remaining($time_to_end) . ' remaining';
  }
  
  // Print HTML
  echo('
    <li class="list-group-item d-flex justify-content-between">
    <div class="p-2 mr-5"><h5><a href="listing.php?item_id=' . $item_id . '">' . $title . '</a></h5>' . $desc_shortened . '</div>
    <div class="text-center text-nowrap"><span style="font-size: 1.5em">£' . number_format($price, 2) . '</span><br/>' . $num_bids . $bid . '<br/>' . $time_remaining . '</div>
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
        $end_date = $row['end_date_time']; 
        $current_price = $row['current_price']; 
        $num_bids =$row['num_bids'];

        //using the print listing function from utilities.php
        //need to update so if starting price > current price = print starting price not current price
        print_listing_li($item_id, $title, $description, $current_price, $num_bids, $end_date) ?>
      
      </div>
      
  <?php endwhile; ?>
<?php } ?>

<?php 
function filter_by_category($connection, $filter_cat,  $final_query) {

  if ($filter_cat != 'all') {

      $check_if_parent = "SELECT parent_category FROM category WHERE category_id = $filter_cat";
      $parent_result = mysqli_query($connection, $check_if_parent);
      $parent_row = mysqli_fetch_assoc($parent_result); // got row
        if ($parent_row['parent_category'] === NULL ){ //is a parent category
          $child_query = "SELECT category_id FROM category WHERE parent_category = $filter_cat";// get all child category ids of that parent
          $child_result = mysqli_query($connection, $child_query);
          $child_ids = [];
          while ($child_row = mysqli_fetch_assoc($child_result)) {
            $child_ids[] = $child_row['category_id']; // add each child id to array
          }
          $child_ids_string = implode(',', $child_ids); // need to convert array to string for sql query, its not possible to pass array directly
          $final_query .= " AND c.category_id IN ($child_ids_string)";// add to final query 
        }
      
    
    
        else {
            //is a child category
          $final_query .= " AND c.category_id = $filter_cat";
        }
      }

    return $final_query;
} ?>