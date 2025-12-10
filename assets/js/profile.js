// assets/js/profile.js
document.addEventListener("DOMContentLoaded", () => {
  // ---------- Elements ----------
  const profileForm = document.getElementById("profileForm");
  const profileImageInput = document.getElementById("profileImage");
  const profileImagePreview = document.getElementById("profileImagePreview");
  const responseMessage = document.getElementById("responseMessage");
  const saveButton = profileForm.querySelector(".save-btn");

  // ---------- Utility Functions ----------
  const showMessage = (message, type = "success") => {
    responseMessage.textContent = message;
    responseMessage.className = `response-message ${type}`;
    responseMessage.style.display = "block";
    responseMessage.style.opacity = "1";
    responseMessage.style.transform = "translateY(0)";
    setTimeout(() => {
      responseMessage.style.opacity = "0";
      responseMessage.style.transform = "translateY(-10px)";
      setTimeout(() => responseMessage.style.display = "none", 400);
    }, 4000);
  };

  const debounce = (fn, delay = 300) => {
    let timeout;
    return (...args) => {
      clearTimeout(timeout);
      timeout = setTimeout(() => fn.apply(this, args), delay);
    };
  };

  const isValidEmail = email => /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);

  const validateForm = () => {
    const fullName = profileForm.full_name.value.trim();
    const email = profileForm.email.value.trim();
    if (!fullName || !email) return "Full Name and Email are required.";
    if (!isValidEmail(email)) return "Please enter a valid email address.";
    return null;
  };

  // ---------- Profile Image Preview ----------
  profileImageInput.addEventListener("change", () => {
    const file = profileImageInput.files[0];
    if (!file) return;
    if (!file.type.startsWith("image/")) {
      showMessage("Please select a valid image file.", "error");
      profileImageInput.value = "";
      return;
    }

    if (file.size > 3 * 1024 * 1024) { // 3MB limit
      showMessage("Image must be smaller than 3MB.", "error");
      profileImageInput.value = "";
      return;
    }

    const reader = new FileReader();
    reader.onload = (e) => {
      profileImagePreview.src = e.target.result;
      profileImagePreview.classList.add("image-updated");
      setTimeout(() => profileImagePreview.classList.remove("image-updated"), 800);
    };
    reader.readAsDataURL(file);
  });

  // ---------- Form Submission ----------
  profileForm.addEventListener("submit", async (e) => {
    e.preventDefault();

    // Validation
    const error = validateForm();
    if (error) {
      showMessage(error, "error");
      return;
    }

    // Disable button and show loading
    saveButton.disabled = true;
    saveButton.textContent = "Saving...";

    const formData = new FormData(profileForm);
    if (profileImageInput.files[0]) {
      formData.append("profile_image", profileImageInput.files[0]);
    }

    try {
      const response = await fetch("../api/utils/update_profile.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.success) {
        showMessage(result.message || "Profile updated successfully!", "success");
        // Optional: subtle shake animation for the save button
        saveButton.classList.add("btn-success");
        setTimeout(() => saveButton.classList.remove("btn-success"), 800);
      } else {
        showMessage(result.message || "Something went wrong.", "error");
      }
    } catch (err) {
      console.error(err);
      showMessage("Network error. Please try again.", "error");
    } finally {
      saveButton.disabled = false;
      saveButton.textContent = "Save Changes";
    }
  });

  // ---------- Real-Time Email Validation ----------
  profileForm.email.addEventListener("input", debounce((e) => {
    if (!isValidEmail(e.target.value)) {
      e.target.classList.add("invalid");
    } else {
      e.target.classList.remove("invalid");
    }
  }, 300));

  // ---------- Accessibility Enhancement ----------
  profileImagePreview.addEventListener("click", () => profileImageInput.click());
});

