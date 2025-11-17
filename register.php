<?php include_once("header.php")?>

<div class="container">
<h2 class="my-3">Register new account</h2>

<form method="POST" action="process_registration.php">
  </div>
  <div class="form-group row">
    <label for="email" class="col-sm-2 col-form-label text-right">Email</label>
	<div class="col-sm-10">
      <input name="email" type="text" class="form-control" id="email" placeholder="Email">
      <small id="emailHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
	</div>
  </div>
  <div class="form-group row">
    <label for="username" class="col-sm-2 col-form-label text-right">Username</label>
	<div class="col-sm-10">
      <input name="username" type="text" class="form-control" id="username" placeholder="Username">
      <small id="usernameHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
	</div>
  </div>
  <div class="form-group row">
    <label for="password" class="col-sm-2 col-form-label text-right">Password</label>
    <div class="col-sm-10">
      <input name="password" type="password" class="form-control" id="password" placeholder="Password">
      <small id="passwordHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
    </div>
  </div>
  <div class="form-group row">
    <label for="passwordConfirmation" class="col-sm-2 col-form-label text-right">Repeat password</label>
    <div class="col-sm-10">
      <input name="passwordConfirmation" type="password" class="form-control" id="passwordConfirmation" placeholder="Enter password again">
      <small id="passwordConfirmationHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
    </div>
  </div>
  <div class="form-group row">
    <label for="firstName" class="col-sm-2 col-form-label text-right">First Name</label>
	<div class="col-sm-10">
      <input name="firstName" type="text" class="form-control" id="firstName" placeholder="First Name">
      <small id="firstNameHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
	</div>
  </div>
  <div class="form-group row">
    <label for="lastName" class="col-sm-2 col-form-label text-right">Last Name</label>
	<div class="col-sm-10">
      <input name="lastName" type="text" class="form-control" id="lastName" placeholder="Last Name">
      <small id="lastNameHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
	</div>
  </div>
  <div class="form-group row">
    <label for="phoneNumber" class="col-sm-2 col-form-label text-right">Phone Number</label>
	<div class="col-sm-10">
      <input name="phoneNumber" type="text" class="form-control" id="phoneNumber" placeholder="Phone Number">
  </div>
  <div class="form-group row">
    <button type="submit" class="btn btn-primary form-control">Register</button>
  </div>
</form>

<div class="text-center">Already have an account? <a href="" data-toggle="modal" data-target="#loginModal">Login</a>

</div>

<?php include_once("footer.php")?>
