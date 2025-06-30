<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>EMed Consultation - Home</title>
  <link rel="stylesheet" href="../NavBAr/Global.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(to bottom right, #d4f6e4, #e0fff3);
      color: #333;
    }

    html {
      scroll-behavior: smooth;
      scroll-padding-top: 100px;
    }

    section {
      padding: 80px 0;
    }

    .option-card {
      border-radius: 20px;
      box-shadow: 0 8px 24px rgba(0,0,0,0.1);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      background-color: #ffffff;
    }

    .option-card:hover {
      transform: translateY(-6px);
      box-shadow: 0 10px 28px rgba(0,0,0,0.15);
    }

    .btn-green {
      background-color: #43c97e;
      color: white;
      font-weight: 500;
    }

    .btn-green:hover {
      background-color: #38b66c;
      color: white;
    }

    .section-title {
      font-weight: 700;
      color: #333;
    }

    .lead {
      color: #6c757d;
    }

    .bg-section {
      background-color: #ffffff;
      border-radius: 16px;
      padding: 40px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }

    #services, #about, #contact {
      background-color: rgba(255, 255, 255, 0.95);
    }
  </style>
</head>
<body>
  <?php include 'NavBar/Navbar.html'; ?>
  
  <!-- Home Section -->
  <section id="homepage">
    <div class="container py-5 text-center">
      <div class="row justify-content-center">
        <div class="col-lg-10">
          <h1 class="fw-bold text-success">Welcome to EMed Connect</h1>
          <p class="lead mb-5">Empowering healthcare through digital solutions. Choose your role to get started.</p>
        </div>
      </div>

      <div class="row justify-content-center g-4">
        <div class="col-md-5">
          <a href="authentication/patient_auth/LogForm.php" style="text-decoration:none;">
            <div class="card option-card text-center p-4">
              <img src="Images/patient.png" alt="Patient" style="width:70px;" class="mx-auto mb-3">
              <h3 class="mb-2">I'm a Patient</h3>
              <p class="text-muted">Book appointments, consult with doctors, and manage your health online.</p>
              <span class="btn btn-green w-100 mt-3">Continue as Patient</span>
            </div>
          </a>
        </div>

        <div class="col-md-5">
          <a href="authentication/doctor_auth/LogForm.php" style="text-decoration:none;">
            <div class="card option-card text-center p-4">
              <img src="Images/doctor.png" alt="Doctor" style="width:70px;" class="mx-auto mb-3">
              <h3 class="mb-2">I'm a Doctor</h3>
              <p class="text-muted">Join our network to offer virtual care and manage your practice.</p>
              <span class="btn btn-green w-100 mt-3">Continue as Doctor</span>
            </div>
          </a>
        </div>
      </div>
    </div>
  </section>

  <!-- Services Section -->
  <section id="services">
    <div class="container text-center">
      <h2 class="section-title mb-4">Our Services</h2>
      <div class="row justify-content-center">
        <div class="col-md-5 mb-4">
          <div class="bg-section">
            <img src="Images/consultation.png" alt="Online Consultation" style="width:50px;" class="mb-3">
            <h5 class="mb-2">Online Consultation</h5>
            <p class="lead">Consult licensed doctors from home. Book appointments, chat, and get expert advice.</p>
          </div>
        </div>
        <div class="col-md-5 mb-4">
          <div class="bg-section">
            <img src="Images/pharmacys.png" alt="Online Pharmacy" style="width:50px;" class="mb-3">
            <h5 class="mb-2">Online Pharmacy</h5>
            <p class="lead">Order medicines easily and securely. Get prescriptions delivered to your door.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- About Section -->
  <section id="about">
    <div class="container text-center">
      <h2 class="section-title mb-4">About Us</h2>
      <p class="lead">We are second-year BSIT students dedicated to improving access to healthcare through digital innovation. EMed Connect aims to simplify online consultations and medicine delivery for everyone.</p>
    </div>
  </section>

  <!-- Contact Section -->
  <section id="contact">
    <div class="container">
      <h2 class="text-center section-title mb-5">Contact Us</h2>
      <div class="row justify-content-center">
        <!-- Contact Form -->
        <div class="col-md-6 mb-4">
          <div class="bg-section">
            <form action="#" method="POST">
              <div class="mb-3">
                <label for="name" class="form-label">Your Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Your Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
              </div>
              <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject">
              </div>
              <div class="mb-3">
                <label for="message" class="form-label">Message</label>
                <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
              </div>
              <button type="submit" class="btn btn-green w-100">Send Message</button>
            </form>
          </div>
        </div>

        <!-- Contact Info -->
        <div class="col-md-5 mb-4">
          <div class="bg-section h-100">
            <h5 class="mb-3">Reach Us At</h5>
            <p><strong>Email:</strong> emedconsultation@gmail.com</p>
            <p><strong>Phone:</strong> +63 907 673 3710</p>
            <p><strong>Address:</strong> Pamantasan ng Lungsod ng San Pablo, Philippines</p>

            <h6 class="mt-4">Business Hours</h6>
            <p>Mon – Fri: 8:00 AM – 5:00 PM</p>
            <p>Sat – Sun: Closed</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Smooth Scroll for Internal Links -->
  <script>
    document.addEventListener("DOMContentLoaded", function () {
      const links = document.querySelectorAll("a[href^='#']");
      links.forEach(link => {
        link.addEventListener("click", function (e) {
          e.preventDefault();
          const target = document.querySelector(this.getAttribute("href"));
          if (target) target.scrollIntoView({ behavior: "smooth" });
        });
      });
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
