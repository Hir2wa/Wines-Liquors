// Sample user data (in real app, this would be in database)
const sampleUsers = {
  "Alain@example.com": {
    name: "Alain Fabrice",
    email: "Alain@example.com",
    phone: "+250 788 123 456",
    address: "KG 15 Ave, Kigali",
    city: "Kigali",
    district: "kigali",
  },
};

function switchTab(tabName) {
  // Update tab buttons
  document.querySelectorAll(".auth-tab").forEach((tab) => {
    tab.classList.remove("active");
  });
  event.target.classList.add("active");

  // Update forms
  document.querySelectorAll(".auth-form").forEach((form) => {
    form.classList.remove("active");
  });
  document.getElementById(tabName + "Form").classList.add("active");
}

function togglePassword(inputId) {
  const input = document.getElementById(inputId);
  const icon = input.parentElement.querySelector(".password-toggle");

  if (input.type === "password") {
    input.type = "text";
    icon.classList.replace("fa-eye", "fa-eye-slash");
  } else {
    input.type = "password";
    icon.classList.replace("fa-eye-slash", "fa-eye");
  }
}

function handleLogin(event) {
  event.preventDefault();

  const email = document.getElementById("email").value;
  const password = document.getElementById("password").value;

  // Reset error messages
  document
    .querySelectorAll(".error-message")
    .forEach((el) => (el.style.display = "none"));

  // Basic validation
  if (!email || !password) {
    if (!email) {
      showError("emailError", "Email is required");
    }
    if (!password) {
      showError("passwordError", "Password is required");
    }
    return;
  }

  // Simulate login (in real app, make API call)
  const button = event.target;
  button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
  button.disabled = true;

  setTimeout(() => {
    if (sampleUsers[email]) {
      // Store user data for profile and checkout auto-fill
      const userData = sampleUsers[email];
      const profileData = {
        firstName: userData.name.split(" ")[0] || "",
        lastName: userData.name.split(" ").slice(1).join(" ") || "",
        email: userData.email,
        phone: userData.phone,
        address: userData.address,
        city: userData.city,
        state: userData.district,
        zipCode: "",
        dateOfBirth: "",
      };

      localStorage.setItem("userData", JSON.stringify(profileData));
      localStorage.setItem("user_logged_in", "true");

      showFlashMessage(
        "Login successful! Welcome back to Total Wine & More.",
        "success"
      );

      // Redirect after a short delay
      setTimeout(() => {
        window.location.href = "index.html";
      }, 1500);
    } else {
      showError("emailError", "Invalid email or password");
      button.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In';
      button.disabled = false;
    }
  }, 1500);
}

function handleRegister(event) {
  event.preventDefault();

  const name = document.getElementById("registerName").value;
  const email = document.getElementById("registerEmail").value;
  const phone = document.getElementById("registerPhone").value;
  const password = document.getElementById("registerPassword").value;
  const confirmPassword = document.getElementById("confirmPassword").value;

  // Reset error messages
  document
    .querySelectorAll(".error-message")
    .forEach((el) => (el.style.display = "none"));

  // Validation
  let isValid = true;

  if (!name) {
    showError("registerNameError", "Name is required");
    isValid = false;
  }

  if (!email) {
    showError("registerEmailError", "Email is required");
    isValid = false;
  } else if (sampleUsers[email]) {
    showError("registerEmailError", "Email already exists");
    isValid = false;
  }

  if (!phone) {
    showError("registerPhoneError", "Phone number is required");
    isValid = false;
  }

  if (!password) {
    showError("registerPasswordError", "Password is required");
    isValid = false;
  } else if (password.length < 6) {
    showError(
      "registerPasswordError",
      "Password must be at least 6 characters"
    );
    isValid = false;
  }

  if (password !== confirmPassword) {
    showError("confirmPasswordError", "Passwords do not match");
    isValid = false;
  }

  if (!isValid) return;

  // Simulate registration
  const button = event.target;
  button.innerHTML =
    '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
  button.disabled = true;

  setTimeout(() => {
    // Store new user data in profile format
    const profileData = {
      firstName: name.split(" ")[0] || "",
      lastName: name.split(" ").slice(1).join(" ") || "",
      email: email,
      phone: phone,
      address: "",
      city: "",
      state: "",
      zipCode: "",
      dateOfBirth: "",
    };

    localStorage.setItem("userData", JSON.stringify(profileData));
    localStorage.setItem("user_logged_in", "true");

    showFlashMessage(
      "Account created successfully! Welcome to Total Wine & More.",
      "success"
    );

    // Redirect after a short delay
    setTimeout(() => {
      window.location.href = "index.html";
    }, 1500);
  }, 2000);
}

function showError(elementId, message) {
  const element = document.getElementById(elementId);
  if (element) {
    element.textContent = message;
    element.style.display = "block";
  }
}

function showFlashMessage(message, type = "success") {
  // Create flash message container
  const flashDiv = document.createElement("div");
  flashDiv.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    background: ${type === "success" ? "#28a745" : "#dc3545"};
    color: white;
    padding: 16px 24px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    z-index: 10000;
    font-weight: 500;
    font-size: 14px;
    max-width: 350px;
    transform: translateX(100%);
    transition: transform 0.3s ease;
    display: flex;
    align-items: center;
    gap: 12px;
  `;

  // Add icon
  const icon = document.createElement("i");
  icon.className =
    type === "success" ? "fas fa-check-circle" : "fas fa-exclamation-circle";
  icon.style.fontSize = "18px";

  // Add message text
  const messageText = document.createElement("span");
  messageText.textContent = message;

  flashDiv.appendChild(icon);
  flashDiv.appendChild(messageText);

  // Add to page
  document.body.appendChild(flashDiv);

  // Animate in
  setTimeout(() => {
    flashDiv.style.transform = "translateX(0)";
  }, 100);

  // Auto remove after 3 seconds
  setTimeout(() => {
    flashDiv.style.transform = "translateX(100%)";
    setTimeout(() => {
      if (flashDiv.parentNode) {
        flashDiv.parentNode.removeChild(flashDiv);
      }
    }, 300);
  }, 3000);
}

function showForgotPassword() {
  alert(
    "Password reset functionality would be implemented here. For demo, use: Alain@example.com"
  );
}

function socialLogin(provider) {
  alert(`${provider} login would be integrated here`);
}

// Auto-format phone number (only if element exists)
document.addEventListener("DOMContentLoaded", function () {
  const phoneInput = document.getElementById("registerPhone");
  if (phoneInput) {
    phoneInput.addEventListener("input", function (e) {
      let value = e.target.value.replace(/\D/g, "");
      if (value.startsWith("250")) {
        value = "+" + value;
      } else if (!value.startsWith("+250") && value.length >= 9) {
        value = "+250 " + value;
      }
      e.target.value = value;
    });
  }
});
