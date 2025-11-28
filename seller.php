<?php 
include_once "includes/header.php";
require_once "includes/utilities.php";

// 1. Ensure user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: " . BASE_URL . "/login.php");
    exit();
}

// 2. Define Tabs
// Keys are the filename (without .php), Values are the Heading
$tabs = [
  'mylistings' => 'My Listings',
  'mybids' => 'My Bids'
];

// Get current tab or default
$current_tab = $_GET['tab'] ?? 'mylistings';

if (!array_key_exists($current_tab, $tabs)) {
    $current_tab = 'mylistings';
}

$tab_heading = $tabs[$current_tab];
$tab_path = 'partials/' . $current_tab . '.php';
?>

<div class="container mt-4 mb-4">

  <?php if (isset($_SESSION['account_type']) && $_SESSION['account_type'] == 'buyer'): ?>
      
      <div class="alert alert-warning shadow-sm">
          <h4 class="alert-heading">
