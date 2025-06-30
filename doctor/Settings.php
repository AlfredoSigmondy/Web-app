
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
      body.dark-mode {
        background: #181a1b !important;
        color: #e0e0e0 !important;
      }
      .main-content.dark-mode, .records-tabs.dark-mode, .record-item.dark-mode {
        background: #23272b !important;
        color: #e0e0e0 !important;
      }
      .record-item.dark-mode {
        border-color: #333 !important;
      }
      .form-control.dark-mode, .form-select.dark-mode {
        background: #23272b !important;
        color: #e0e0e0 !important;
        border-color: #444 !important;
      }
    </style>
</head>
<body>
  <div class="d-flex" style="min-height: 100vh;">
    <?php include_once __DIR__ . '/../SideBar/DSidebar.php'; ?>
    <div class="flex-grow-1 main-content p-4">
      <h4 class="fw-bold mb-4">Settings</h4>
      <div class="mb-4">
        <label class="form-check-label fw-semibold" for="darkModeSwitch">
          <i class="bi bi-moon-stars"></i> Night Mode
        </label>
        <input class="form-check-input ms-2" type="checkbox" id="darkModeSwitch" />
      </div>
      <div class="mb-4">
        <h5 class="fw-bold mb-3">Change Password</h5>
        <form id="changePasswordForm" method="post" action="change_password.php" style="max-width:400px;">
          <div class="mb-3">
            <label for="currentPassword" class="form-label">Current Password</label>
            <input type="password" class="form-control" id="currentPassword" name="current_password" required>
          </div>
          <div class="mb-3">
            <label for="newPassword" class="form-label">New Password</label>
            <input type="password" class="form-control" id="newPassword" name="new_password" required>
          </div>
          <div class="mb-3">
            <label for="confirmPassword" class="form-label">Confirm New Password</label>
            <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
          </div>
          <button type="submit" class="btn btn-success">Change Password</button>
        </form>
        <div id="passwordChangeMsg" class="mt-2"></div>
      </div>
    </div>
    <?php include_once __DIR__ . '/../SideBar_Right/SidebarRight.php'; ?>
  </div>
  <script>
    // Dark mode toggle
    const darkSwitch = document.getElementById('darkModeSwitch');
    function setDarkMode(on) {
      document.body.classList.toggle('dark-mode', on);
      document.querySelector('.main-content').classList.toggle('dark-mode', on);
      document.querySelectorAll('.record-item').forEach(e => e.classList.toggle('dark-mode', on));
      document.querySelectorAll('.form-control, .form-select').forEach(e => e.classList.toggle('dark-mode', on));
      localStorage.setItem('emed_darkmode', on ? '1' : '0');
    }
    // Load dark mode preference
    if (localStorage.getItem('emed_darkmode') === '1') {
      darkSwitch.checked = true;
      setDarkMode(true);
    }
    darkSwitch.addEventListener('change', e => setDarkMode(e.target.checked));

    // Optional: AJAX password change (if you want to handle without reload)
    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
      e.preventDefault();
      const form = e.target;
      const msg = document.getElementById('passwordChangeMsg');
      msg.textContent = '';
      fetch(form.action, {
        method: 'POST',
        body: new FormData(form)
      })
      .then(r => r.text())
      .then(t => { msg.textContent = t; form.reset(); })
      .catch(() => { msg.textContent = "Error changing password."; });
    });
  </script>
</body>
</html>