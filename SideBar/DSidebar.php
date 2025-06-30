<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sidebar</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .sidebar-custom {
      background: #fff;
      border-radius: 2rem;
      box-shadow: 0 8px 32px 0 rgba(67, 201, 126, 0.15);
      min-height: 100vh;
      margin: 24px 0 24px 24px;
      font-family: 'Poppins', sans-serif;
      width: 240px;
    }

    .sidebar-custom .fs-4.fw-bold {
      color: #14532d;
      letter-spacing: 0.5px;
    }

    .sidebar-custom .nav-link {
      color: #222 !important;
      font-weight: 500;
      border-radius: 1.5rem;
      margin-bottom: 4px;
      transition: background 0.2s, color 0.2s;
      padding-left: 18px;
      padding-top: 10px;
      padding-bottom: 10px;
      font-size: 1rem;
      display: flex;
      align-items: center;
      background-color: transparent;
    }

    .sidebar-custom .nav-link.active,
    .sidebar-custom .nav-link:hover {
      background: #43c97e !important;
      color: #fff !important;
    }

    .nav-pills .nav-link.active {
      background-color: #43c97e !important;
      color: #fff !important;
    }

    .sidebar-custom .nav-link i {
      margin-right: 10px;
      font-size: 1.2rem;
    }

    .sidebar-custom hr {
      border-top: 2px solid #e0ffe0;
      margin: 16px 0;
    }

    .sidebar-custom .mt-auto .nav-link {
      color: #43c97e !important;
      font-weight: 500;
    }

    .sidebar-custom .nav-link:focus {
      outline: 2px solid #43c97e;
    }
  </style>
</head>
<body>
  <div class="d-flex flex-column flex-shrink-0 p-3 sidebar-custom">
    <a href="../doctor/Dashboard.php" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-decoration-none">
      <span class="fs-4 fw-bold">eMedConnect</span>
    </a>
    <hr>
    <ul class="nav nav-pills flex-column mb-auto">
      <li class="nav-item">
        <a href="Dashboard.php" class="nav-link <?php echo ($currentPage == 'Dashboard.php') ? 'active' : 'link-dark'; ?>">Dashboard</a>
      </li>
      <li>
        <a href="Message.php" class="nav-link <?php echo ($currentPage == 'Message.php') ? 'active' : 'link-dark'; ?>">Message</a>
      </li>
      <li>
        <a href="Records.php" class="nav-link <?php echo ($currentPage == 'Records.php') ? 'active' : 'link-dark'; ?>">Records</a>
      </li>
      <li>
        <a href="Settings.php" class="nav-link <?php echo ($currentPage == 'Settings.php') ? 'active' : 'link-dark'; ?>">Settings</a>
      </li>
    </ul>
    <hr>
    <ul class="nav flex-column mb-2">
      <li>
        <a href="My_Profile.php" class="nav-link <?php echo ($currentPage == 'My_Profile.php') ? 'active' : 'link-dark'; ?>">My Profile</a>
      </li>
      <li>
        <a href="../Homepage.php" class="nav-link link-dark">Sign Out</a>
      </li>
    </ul>
    <div class="mt-auto">
      <a href="#" class="nav-link link-dark"><span class="me-2">?</span>Help</a>
    </div>
  </div>
</body>
</html>
