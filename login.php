<?php include_once("header.php")?>

<div class="container">
<h2 class="my-3">Login</h2>


<form method="POST" action="login_result.php">
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
<div class="text-center">or <a href="register.php">create an account</a></div>
