<?php include_once("header.php")?>

<div class="container">
<h2 class="my-3">Register new account</h2>

<!-- Create auction form -->
<form method="POST" action="process_registration.php">
  <div class="form-group row">
    <label for="accountType" class="col-sm-2 col-form-label text-right">Registering as a:</label>
	<div class="col-sm-10">
	  <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="accountType" id="accountBuyer" value="buyer" checked>
        <label class="form-check-label" for="accountBuyer">Buyer</label>
      </div>
      <div class="form-check form-check-inline">
        <input class="form-check-input" type="radio" name="accountType" id="accountSeller" value="seller">
        <label class="form-check-label" for="accountSeller">Seller</label>
      </div>
      <small id="accountTypeHelp" class="form-text-inline text-muted"><span class="text-danger">* Required.</span></small>
  	</div>
  </div>
  <div class="form-group row">
    <label for="First Name" class="col-sm-2 col-form-label text-right">First Name</label>
	<div class="col-sm-10">
      <input type="text" class="form-control" id="firstname" placeholder="First name">
      <small id="firstname" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
	</div>
  </div>
  <div class="form-group row">
  <label for="lastname" class="col-sm-2 col-form-label text-right">Last Name</label>
  <div class="col-sm-10">
    <input type="text" class="form-control" id="lastname" placeholder="Last name">
    <small id="lastnameHelp" class="form-text text-muted">
      <span class="text-danger">* Required.</span>
    </small>
  </div>
</div>
    <div class="form-group row">
  <label for="username" class="col-sm-2 col-form-label text-right">Username</label>
  <div class="col-sm-10">
    <input type="text" class="form-control" id="username" placeholder="Username">
    <small id="usernameHelp" class="form-text text-muted">
      <span class="text-danger">* Required.</span>
    </small>
  </div>
</div>
  <div class="form-group row">
    <label for="email" class="col-sm-2 col-form-label text-right">Email</label>
	<div class="col-sm-10">
      <input type="text" class="form-control" id="email" placeholder="Email">
      <small id="emailHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
	</div>
  </div>
  <div class="form-group row">
    <label for="password" class="col-sm-2 col-form-label text-right">Password</label>
    <div class="col-sm-10">
      <input type="password" class="form-control" id="password" placeholder="Password">
      <small id="passwordHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
    </div>
  </div>
  <div class="form-group row">
    <label for="passwordConfirmation" class="col-sm-2 col-form-label text-right">Repeat password</label>
    <div class="col-sm-10">
      <input type="password" class="form-control" id="passwordConfirmation" placeholder="Enter password again">
      <small id="passwordConfirmationHelp" class="form-text text-muted"><span class="text-danger">* Required.</span></small>
    </div>
  </div>
  <div class="form-group row">
  <label for="phone" class="col-sm-2 col-form-label text-right">Phone Number</label>
  <div class="col-sm-10">
    <input type="tel" class="form-control" id="phone" placeholder="e.g. +1 555 123 4567">
    <small id="phoneHelp" class="form-text text-muted">
      <span class="text-danger">* Required.</span>
    </small>
  </div>
</div>
<div class="form-group row">
  <label for="address" class="col-sm-2 col-form-label text-right">Delivery Address</label>
  <div class="col-sm-10">
    <textarea class="form-control" id="address" placeholder="Enter your delivery address"></textarea>
    <small id="addressHelp" class="form-text text-muted">
      <span class="text-danger">* Required.</span>
    </small>
  </div>
</div>

  <div class="form-group row">
    <button type="submit" class="btn btn-primary form-control">Register</button>
  </div>
</form>

<div class="text-center">Already have an account? <a href="" data-toggle="modal" data-target="#loginModal">Login</a>

</div>

<?php include_once("footer.php")?>