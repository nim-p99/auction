<?php include_once("header.php")?>


<style>
  /* Thumbnails for photo upload (scoped to this page) */
  #photoPreview {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }

  .photo-thumb {
    position: relative;
    width: 100px !important;
    height: 100px !important;
    flex: 0 0 100px;
  }

  .photo-thumb img {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
    border: 1px solid #ccc;
    border-radius: 6px;
    display: block;
  }

  .remove-photo {
    position: absolute;
    top: -6px;
    right: -6px;
    background: red;
    color: white;
    border: none;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 14px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
  }
</style>

<?php
/* (Uncomment this block to redirect people without selling privileges away from this page)
  // If user is not logged in or not a seller, they should not be able to
  // use this page.
  if (!isset($_SESSION['account_type']) || $_SESSION['account_type'] != 'seller') {
    header('Location: browse.php');
  }
 */

//$_SESSION['seller_id'] will be null, if user has not 
//listed any items before. If user clicks submit, we should
//validate data first and then add to seller table 
//and then add auction to auction table  
?>

<div class="container">

<!-- Create auction form -->
<div style="max-width: 800px; margin: 10px auto">
  <h2 class="my-3">Create new auction</h2>
  <div class="card">
    <div class="card-body">
      <!-- Note: This form does not do any dynamic / client-side / 
      JavaScript-based validation of data. It only performs checking after 
      the form has been submitted, and only allows users to try once. You 
      can make this fancier using JavaScript to alert users of invalid data
      before they try to send it, but that kind of functionality should be
      extremely low-priority / only done after all database functions are
      complete. -->
      <form method="post" action="create_auction_result.php" enctype="multipart/form-data">
        <div class="form-group row">
          <label for="auctionTitle" class="col-sm-2 col-form-label text-right">Item to auction</label>
          <div class="col-sm-10">
            <input type="text" class="form-control" id="auctionTitle" name="auctionTitle" placeholder="e.g. Black mountain bike">
            <small id="titleHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> What are you listing?</small>
          </div>
        </div>

        <div class="form-group row">
          <label for="auctionCategory" class="col-sm-2 col-form-label text-right">Item category</label>
          <div class="col-sm-10">
            <select class="form-control" id="auctionCategory" name="auctionCategory">
              <option value="">Choose...</option>
              <?php
                // Load categories from the database
                /* $sql = "SELECT category_id, category_name, parent_category  */
                /*         FROM category */
                /*         ORDER BY parent_category IS NULL DESC, category_name"; */ 
                $sql = "
                  SELECT c.category_id, c.category_name, c.parent_category
                  FROM category c
                  LEFT JOIN category p ON c.parent_category = p.category_id
                  ORDER BY
                    CASE WHEN c.parent_category IS NULL THEN c.category_name ELSE p.category_name END,
                    CASE WHEN c.parent_category IS NULL THEN '' ELSE c.category_name END;
                  ";
                $result = mysqli_query($connection, $sql);

                if ($result) {
                  while ($row = mysqli_fetch_assoc($result)) {
                    $label = $row['category_name'];

                    # indent chld categories
                    if (!is_null($row['parent_category'])) {
                      $label = '— ' . $label;
                    }

                    echo '<option value="' . htmlspecialchars($row['category_id']) . '">'
                       . htmlspecialchars($label) .
                       '</option>';
                  }
                } else {
                  echo '<option disabled>Unable to load categories</option>';
                }
              ?>
            </select>
            <small id="categoryHelp" class="form-text text-muted">
              <span class="text-danger">* Required.</span> Select a category for this item.
            </small>
          </div>
        </div>
      
                <!-- Item condition (hardcoded options) -->
        <div class="form-group row">
          <label for="auctionCondition" class="col-sm-2 col-form-label text-right">Condition</label>
          <div class="col-sm-10">
            <select class="form-control" id="auctionCondition" name="auctionCondition">
              <option value="">Choose condition...</option>
              <option value="new">New</option>
              <option value="like_new">Like new</option>
              <option value="very_good">Very good</option>
              <option value="good">Good</option>
              <option value="fair">Fair</option>
              <option value="poor">Poor</option>
            </select>
            <small id="conditionHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> Describe the overall condition of the item.</small>
          </div>
        </div>

        <div class="form-group row">
          <label for="auctionDetails" class="col-sm-2 col-form-label text-right">Item details</label>
          <div class="col-sm-10">
            <textarea class="form-control" id="auctionDetails" name="auctionDetails" rows="4"></textarea>
            <small id="detailsHelp" class="form-text text-muted"><span class="text-danger">* Required.</span> A short description of the item you're selling, which will display on your listing.</small>
          </div>
        </div>
        
        <!-- Upload photos -->
        <div class="form-group row">
          <label class="col-sm-2 col-form-label text-right">Upload photos</label>
        <div class="col-sm-10">

        <!-- Drop zone -->
        <div id="photoDropZone" style="border: 2px dashed #ccc; border-radius: 8px; padding: 20px; text-align: center; cursor: pointer;">
          <p style="margin:0;">Drag & drop images here<br>or click to choose</p>
          <input type="file" id="photoFileInput" name="auctionPhotos[]" accept="image/*" multiple style="display:none;">
        </div>
        
        <!-- Previews -->
        <div id="photoPreview" class="mt-3"></div>

        <!-- Explanation -->
        <small class="form-text text-muted">Maximum 5 upload of 5 photos. Accepted image types: JPG, JPEG, PNG, GIF, WEBP.</small>
        
        <!-- Inline error for invalid photo types -->
        <small id="photoError" class="text-danger" style="display:none;"></small>
          </div>
        </div>


        
        <div class="form-group row">
          <label for="auctionStartPrice" class="col-sm-2 col-form-label text-right">Starting price</label>
          <div class="col-sm-10">
	        <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text">£</span>
              </div>
              <input type="number" class="form-control" id="auctionStartPrice" name="auctionStartPrice" step="0.01" min="0.01" required>
            </div>
            <small id="startBidHelp" class="form-text text-muted">
              <span class="text-danger">* Required.</span> Initial bid amount.
            </small>
          </div>
        </div>
        
        <div class="form-group row">
          <label for="auctionReservePrice" class="col-sm-2 col-form-label text-right">Reserve price</label>
          <div class="col-sm-10">
          <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text">£</span>
              </div>
              <input type="number" class="form-control" id="auctionReservePrice" name="auctionReservePrice" step="0.01" min="0.01">
            </div>
            <small id="reservePriceHelp" class="form-text text-muted">
              Optional. Auctions that end below this price will not go through. This value is not displayed in the auction listing.
            </small>
          </div>
        </div>

        <div class="form-group row">
          <label for="auctionBuyNowPrice" class="col-sm-2 col-form-label text-right">Buy now price</label>
          <div class="col-sm-10">
          <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text">£</span>
              </div>
              <input type="number" class="form-control" id="auctionBuyNowPrice" name="auctionBuyNowPrice" step="0.01" min="0">
            </div>
            <small id="buyNowHelp" class="form-text text-muted">
            Optional. While the auction is still active, a buyer can immeditaly purchase the item at the buy now price, causing the auction to subsequently end.
            </small>
            <!-- Inline error message for Buy Now rule -->
             <small id="buyNowError" class="text-danger" style="display:none;"></small>
          </div>
        </div>
        
         <div class="form-group row">
          <label for="auctionStartDate" class="col-sm-2 col-form-label text-right">Start date</label>
          <div class="col-sm-10">
            <input type="datetime-local" class="form-control" id="auctionStartDate" name="auctionStartDate">
            <small id="startDateHelp" class="form-text text-muted">
              <span class="text-danger">* Required.</span> Choose the auction start date.</small>
          </div>
        </div>

        <div class="form-group row">
          <label for="auctionEndDate" class="col-sm-2 col-form-label text-right">End date</label>
          <div class="col-sm-10">
            <input type="datetime-local" class="form-control" id="auctionEndDate" name="auctionEndDate">
            <small id="endDateHelp" class="form-text text-muted">
              <span class="text-danger">* Required.</span> Choose the auction end date. Auctions must be live for at least one hour.
            </small>
            <small id="endDateError" class="text-danger" style="display:none;"></small>
          </div>
        </div>

        <button type="submit" id="createAuctionButton" class="btn btn-primary form-control">Create Auction</button>
      </form>
    </div>
  </div>
</div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

  // PHOTO UPLOAD CODE (no addPhotoButton)
  const photoDropZone   = document.getElementById("photoDropZone");
  const photoFileInput  = document.getElementById("photoFileInput");
  const photoPreview    = document.getElementById("photoPreview");

  let selectedPhotos = [];
  const MAX_PHOTOS = 5;
  const allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
  const photoError = document.getElementById('photoError');


  function refreshPreviews() {
    if (!photoPreview) return;

    photoPreview.innerHTML = "";

    selectedPhotos.forEach((file, index) => {
      const thumb = document.createElement("div");
      thumb.classList.add("photo-thumb");

      const img = document.createElement("img");
      img.src = URL.createObjectURL(file);

      const removeBtn = document.createElement("button");
      removeBtn.classList.add("remove-photo");
      removeBtn.textContent = "×";
      removeBtn.addEventListener("click", () => {
        selectedPhotos.splice(index, 1);
        refreshPreviews();
      });

      thumb.appendChild(img);
      thumb.appendChild(removeBtn);
      photoPreview.appendChild(thumb);
    });
  }

    function handleFiles(files) {
    if (!files || !files.length) return;

    // Clear previous error when user tries again
    if (photoError) {
      photoError.style.display = 'none';
      photoError.textContent = '';
    }

    for (let file of files) {
      const ext = file.name.split('.').pop().toLowerCase();

      // Check file extension against allowed list
      if (!allowedExtensions.includes(ext)) {
        if (photoError) {
          photoError.textContent =
            `File "${file.name}" is not a supported image type. Allowed: JPG, JPEG, PNG, GIF, WEBP.`;
          photoError.style.display = 'block';
        }
        continue; // skip this file
      }

      // Check max photo limit
      if (selectedPhotos.length >= MAX_PHOTOS) {
        if (photoError) {
          photoError.textContent = `You can only upload up to ${MAX_PHOTOS} photos.`;
          photoError.style.display = 'block';
        }
        break; // stop processing more files
      }

      // Valid file – add to list
      selectedPhotos.push(file);
    }

    refreshPreviews();
  }

  if (photoDropZone && photoFileInput) {
    photoDropZone.addEventListener("click", () => photoFileInput.click());

    photoFileInput.addEventListener("change", function () {
      handleFiles(this.files);
      this.value = "";
    });

    photoDropZone.addEventListener("dragover", e => {
      e.preventDefault();
    });

    photoDropZone.addEventListener("drop", e => {
      e.preventDefault();
      handleFiles(e.dataTransfer.files);
    });
  }

  // SYNC selectedPhotos TO THE REAL FILE INPUT ON SUBMIT
  const auctionForm = document.querySelector('form[action="create_auction_result.php"]');
  if (auctionForm && photoFileInput) {
    auctionForm.addEventListener('submit', function (e) {
      // If you want to enforce at least one photo, uncomment this block:
      // if (selectedPhotos.length === 0) {
      //   e.preventDefault();
      //   alert("Please upload at least one photo.");
      //   return;
      // }

      // Limit to MAX_PHOTOS just in case
      const filesToSend = selectedPhotos.slice(0, MAX_PHOTOS);

      // Build a DataTransfer object so the files appear in photoFileInput.files
      const dt = new DataTransfer();
      filesToSend.forEach(file => dt.items.add(file));
      photoFileInput.files = dt.files;
    });
  }


  // DATE MINIMUM CODE
  function formatForInput(date) {
    const pad = n => n.toString().padStart(2, '0');
    return (
      date.getFullYear() + '-' +
      pad(date.getMonth() + 1) + '-' +
      pad(date.getDate()) + 'T' +
      pad(date.getHours()) + ':' +
      pad(date.getMinutes())
    );
  }

  function setMinDateTime() {
    const now = new Date();

    const startDateEl = document.getElementById('auctionStartDate');
    const endDateEl   = document.getElementById('auctionEndDate');

    if (startDateEl) {
      const startFormatted = formatForInput(now);
      startDateEl.min = startFormatted;
      if (!startDateEl.value) {
        startDateEl.value = startFormatted;   // default start = now
      }
    }

    if (endDateEl) {
      const minEnd = new Date(now.getTime() + 60 * 60 * 1000); // +1 hour
      const endFormatted = formatForInput(minEnd);
      endDateEl.min = endFormatted;
      if (!endDateEl.value) {
        endDateEl.value = endFormatted;      // default end = start + 1 hour
      }
    }
  }

  setMinDateTime();

  function adjustEndDateBasedOnStart() {
    const startDateInput = document.getElementById('auctionStartDate');
    const endDateInput   = document.getElementById('auctionEndDate');
    if (!startDateInput || !endDateInput || !startDateInput.value) return;

    const startDate = new Date(startDateInput.value);
    if (isNaN(startDate.getTime())) return;

    const minEnd = new Date(startDate.getTime() + 60 * 60 * 1000); // +1 hour
    const endFormatted = formatForInput(minEnd);

    // Update the minimum end date and adjust value if it's too early
    endDateInput.min = endFormatted;

    const currentEnd = endDateInput.value ? new Date(endDateInput.value) : null;
    if (!currentEnd || currentEnd < minEnd || isNaN(currentEnd.getTime())) {
      endDateInput.value = endFormatted;
    }
  }

  
  // REQUIRED FIELD VALIDATION
  const titleInput        = document.getElementById('auctionTitle');
  const categoryInput     = document.getElementById('auctionCategory');
  const conditionInput    = document.getElementById('auctionCondition');
  const detailsInput      = document.getElementById('auctionDetails');

  const startPriceInput   = document.getElementById('auctionStartPrice');
  const buyNowInput       = document.getElementById('auctionBuyNowPrice');

  const startDateInput    = document.getElementById('auctionStartDate');
  const endDateInput      = document.getElementById('auctionEndDate');

  const buyNowError       = document.getElementById('buyNowError');
  const endDateError      = document.getElementById('endDateError'); // may be null

  const submitButton      = document.getElementById('createAuctionButton');

  if (submitButton) {
    submitButton.disabled = true;
  }

  function validateForm() {
    let formIsValid = true;

    // ----- Required text/select fields -----
    if (!titleInput || titleInput.value.trim() === "") formIsValid = false;
    if (!categoryInput || categoryInput.value === "") formIsValid = false;
    if (!conditionInput || conditionInput.value === "") formIsValid = false;
    if (!detailsInput || detailsInput.value.trim() === "") formIsValid = false;

    // ----- Start price -----
    let startVal = NaN;
    if (!startPriceInput) {
      formIsValid = false;
    } else {
      startVal = parseFloat(startPriceInput.value);
      if (isNaN(startVal) || startVal < 0.01) formIsValid = false;
    }

    // ----- Start date -----
    if (!startDateInput || !startDateInput.value) {
      formIsValid = false;
    }

    // ----- End date: required + >= start date + 1 hour -----
    if (endDateInput) {
      endDateInput.classList.remove("is-invalid");
    }
    if (endDateError) {
      endDateError.style.display = "none";
      endDateError.textContent   = "";
    }

    if (!endDateInput || !endDateInput.value || !startDateInput || !startDateInput.value) {
      formIsValid = false;
    } else {
      const startDate = new Date(startDateInput.value);
      const endDate   = new Date(endDateInput.value);

      if (isNaN(startDate.getTime()) || isNaN(endDate.getTime())) {
        formIsValid = false;
      } else {
        const minEnd = new Date(startDate.getTime() + 60 * 60 * 1000); // +1 hour
        if (endDate < minEnd) {
          formIsValid = false;
          if (endDateError) {
            endDateError.textContent = "End date must be at least 1 hour after the start date.";
            endDateError.style.display = "block";
          }
          endDateInput.classList.add("is-invalid");
        }
      }
    }

    // ----- Buy Now validation -----
    if (buyNowInput) {
      buyNowInput.classList.remove("is-invalid");
    }
    if (buyNowError) {
      buyNowError.style.display = "none";
      buyNowError.textContent   = "";
    }

    if (buyNowInput) {
      const buyVal = parseFloat(buyNowInput.value);
      if (!isNaN(buyVal)) {
        if (buyVal < 0) {
          if (buyNowError) {
            buyNowError.textContent = "Buy now price cannot be negative.";
            buyNowError.style.display = "block";
          }
          buyNowInput.classList.add("is-invalid");
          formIsValid = false;
        } else if (!isNaN(startVal) && buyVal < startVal) {
          if (buyNowError) {
            buyNowError.textContent = "Buy now price must be at least the starting price.";
            buyNowError.style.display = "block";
          }
          buyNowInput.classList.add("is-invalid");
          formIsValid = false;
        }
      }
    }

    // ----- Finally, toggle the submit button -----
    if (submitButton) {
      submitButton.disabled = !formIsValid;
    }
  }

  // EVENT LISTENERS TO TRIGGER VALIDATION
  if (titleInput)     titleInput.addEventListener('input',  validateForm);
  if (categoryInput)  categoryInput.addEventListener('change', validateForm);
  if (conditionInput) conditionInput.addEventListener('change', validateForm);
  if (detailsInput)   detailsInput.addEventListener('input', validateForm);

  if (startPriceInput) startPriceInput.addEventListener('input', validateForm);
  if (startDateInput) {
    startDateInput.addEventListener('input', function () {
      adjustEndDateBasedOnStart(); // keep end at least +1h
      validateForm();
    });
  }
  if (endDateInput)    endDateInput.addEventListener('input',   validateForm);
  if (buyNowInput)     buyNowInput.addEventListener('input',    validateForm);

  // Run once on load to set initial disabled/enabled state
  validateForm();

});
</script>



<?php include_once("footer.php")?>
