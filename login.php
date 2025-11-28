<?php 
include_once "includes/header.php"; 
?>

<div class="container">
<h2 class="my-3">Login</h2>

<?php if(isset($_SESSION['login_error'])): ?>
    <div class="alert alert-danger">
        <?php 
            echo $_SESSION['login_error']; 
            unset($_SESSION['login_error']); 
        ?>
    </div>
<?php endif; ?>

<form method="POST" action="<?php echo BASE_URL; ?>/actions/login_result.php">
  <div class="form-group">
    <label for="email">Email</label>
    <input name="email" type="text" class="form-control" id="email" placeholder="Email" required>
  </div>
  <div class="form-group">
    <label for="password">Password</label>
    <input name="password" type="password" class="form-control" id="password" placeholder="Password" required>
  </div>
  <button type="submit" class="btn btn-primary form-control">Sign in</button>
</form>

<div class="text-center mt-3">
    or <a href="<?php echo BASE_URL; ?>/register.php">create an account</a>
</div>

</div>

<?php include_once "includes/footer.php"; ?>
