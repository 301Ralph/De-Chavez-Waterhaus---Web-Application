<?php
session_start();
include 'includes/connection.php';

// Fetch available products
$productsQuery = "SELECT * FROM product";
$productsResult = $conn->query($productsQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>De Chavez Waterhaus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/index-style.css">
    <link rel="icon" href="assets/images/logo.png" type="image/png">

</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.html">
                <img src="assets/images/logo.png" alt="Logo" style="height: 40px; width: auto;">
                <span>De Chavez Waterhaus</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.html#home">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="index.html#products">Products</a></li>
                <li class="nav-item"><a class="nav-link" href="index.html#about">About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="index.html#faq">FAQ</a></li>
                <li class="nav-item"><a class="nav-link" href="index.html#contact">Contact</a></li>
                <li class="nav-item"><a class="nav-link" href="signin.php">Sign In</a></li>
                </ul>
            </div>
            </div>
        </nav>
    </header>


    <section id="home" class="hero-section d-flex align-items-center text-white text-center">
        <video autoplay muted loop playsinline class="bg-video">
            <source src="assets/videos/BG.mp4" type="video/mp4">
            Your browser does not support HTML5 video.
        </video>
        <div class="overlay"></div>
        <div class="container content">
            <h2 class="display-4">Start Your Day with Pure Water</h2>
            <p class="lead">Discover the difference of pure, clean water with De Chavez Waterhaus. Hydrate smarter, live better.</p>
            <button class="btn btn-primary" onclick="window.location.href='<?php echo isset($_SESSION['userID']) ? ($_SESSION['role'] === 'admin' ? 'Admin/admin_dashboard.php' : 'Customer/order.php') : 'signin.php'; ?>'">Order Now</button>
        </div>
    </section>

    <section id="products" class="py-5 fade-in-up">
        <div class="container">
            <h2 class="text-center mb-4">Products</h2>
            <div class="row">
                <?php
                $defaultImages = [
                    '1' => 'images/waterdispenser.jpg',
                    '2' => 'images/default-upload.jpg'
                ];
                $productIndex = 1;
                while ($product = $productsResult->fetch_assoc()) {
                    $imageURL = $product['ImageURL'];
                    $productID = $product['ProductID'];
                    // Check if the image exists
                    if (!file_exists($imageURL) || empty($imageURL)) {
                        $imageURL = $defaultImages[$productIndex];
                    }
                ?>
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 text-center">
                            <img src="<?php echo $imageURL; ?>" class="card-img-top" alt="<?php echo $product['ProductName']; ?>" onerror="this.src='<?php echo $defaultImages[$productIndex]; ?>';">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo $product['ProductName']; ?></h5>
                                <p class="card-text"><?php echo $product['Description']; ?></p>
                                <p class="card-text"><strong>Price: </strong>₱<?php echo number_format($product['Price'], 2); ?></p>
                            </div>
                        </div>
                    </div>
                <?php
                    $productIndex = ($productIndex % 2) + 1; // Alternate between 1 and 2
                } ?>
            </div>
        </div>
    </section>

    <section id="about" class="py-5 fade-in-up">
        <div class="container">
            <h2 class="text-center mb-4">About Us</h2>
            <div class="row">
                <div class="col-md-8 mx-auto">
                    <p>De Chavez Waterhaus is dedicated to providing the highest quality water to keep you hydrated and healthy. Located at 072 Nawasa, Sta.rosa 1 Noveleta, Cavite, we are committed to serving our community with the purest water available.</p>
                    <p>Tel. No. 438-6311 Tramo Road</p>
                </div>
            </div>
        </div>
    </section>

    <section id="faq" class="py-5 fade-in-up">
        <div class="container">
            <h2 class="text-center mb-4">FAQ</h2>
            <div class="accordion" id="faqAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faqHeadingOne">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseOne" aria-expanded="true" aria-controls="faqCollapseOne">
                            What types of water do you offer?
                        </button>
                    </h2>
                    <div id="faqCollapseOne" class="accordion-collapse collapse show" aria-labelledby="faqHeadingOne" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            We offer Purified Water, Distilled Water, Mineral Water, and Alkaline Water.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faqHeadingTwo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseTwo" aria-expanded="false" aria-controls="faqCollapseTwo">
                            How can I place an order?
                        </button>
                    </h2>
                    <div id="faqCollapseTwo" class="accordion-collapse collapse" aria-labelledby="faqHeadingTwo" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            You can place an order through our website by clicking the "Order Now" button.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faqHeadingThree">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseThree" aria-expanded="false" aria-controls="faqCollapseThree">
                            What are your operating hours?
                        </button>
                    </h2>
                    <div id="faqCollapseThree" class="accordion-collapse collapse" aria-labelledby="faqHeadingThree" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            We operate from 8 AM to 6 PM, Monday to Saturday.
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="faqHeadingFour">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapseFour" aria-expanded="false" aria-controls="faqCollapseFour">
                            How do I contact customer service?
                        </button>
                    </h2>
                    <div id="faqCollapseFour" class="accordion-collapse collapse" aria-labelledby="faqHeadingFour" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            You can contact us at Tel. No. 438-6311 Tramo Road or visit us at our station address.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <section id="contact" class="py-5 fade-in-up">
        <div class="container">
            <h2 class="text-center mb-4">Contact Us</h2>
            <div class="row">
                <!-- Contact Info -->
                <div class="col-md-5 mb-4">
                    <div class="mb-4 d-flex">
                        <i class="bi bi-geo-alt-fill fs-3 text-primary me-3"></i>
                        <div>
                            <h6 class="mb-1">Visit Us</h6>
                            <p class="mb-0">072 Nawasa, Sta. Rosa 1, Noveleta, Cavite</p>
                        </div>
                    </div>

                    <div class="mb-4 d-flex">
                        <i class="bi bi-telephone-fill fs-3 text-primary me-3"></i>
                        <div>
                            <h6 class="mb-1">Call Us</h6>
                            <p class="mb-0">Tel. No. 438-6311 Tramo Road</p>
                        </div>
                    </div>

                    <div class="mb-4 d-flex">
                        <i class="bi bi-envelope-fill fs-3 text-primary me-3"></i>
                        <div>
                            <h6 class="mb-1">Email</h6>
                            <p class="mb-0"><a href="mailto:support@dechavezwaterhaus.com">support@dechavezwaterhaus.com</a></p>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="col-md-7">
                    <form method="post" action="contact_form_handler.php" class="bg-white p-4 shadow-sm rounded">
                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Send Message</button>
                    </form>
                </div>
            </div>

            <!-- Google Maps Embed -->
            <div class="row mt-5">
                <div class="col-12">
                    <div class="ratio ratio-16x9 shadow rounded">
                        <iframe
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3859.840073671182!2d120.8761236!3d14.4345289!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397cdcd63e90f5d%3A0xb9c39c184f00591!2s072%20Nawasa%2C%20Sta.%20Rosa%201%2C%20Noveleta%2C%20Cavite!5e0!3m2!1sen!2sph!4v1715154535123"
                            style="border:0;" allowfullscreen="" loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
        <?php if (isset($_GET['message']) && $_GET['message'] === 'sent') : ?>
        <div class="toast-container position-fixed bottom-0 end-0 p-3">
            <div class="toast align-items-center text-white bg-success border-0 show" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        Your message has been sent successfully!
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const sections = document.querySelectorAll(".fade-in-up");

            const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                entry.target.classList.add("visible");
                }
            });
            }, { threshold: 0.2 });

            sections.forEach(section => observer.observe(section));
        });
    </script>
    <script>
function toggleChatbot() {
  const chatbotBody = document.getElementById("chatbotBody");
  chatbotBody.style.display = chatbotBody.style.display === "block" ? "none" : "block";
}

function handleFAQ(question) {
  const chatWindow = document.getElementById("chatWindow");

  // Show user's selected question
  const userMsg = document.createElement("div");
  userMsg.className = "user-msg";
  userMsg.innerText = question;
  chatWindow.appendChild(userMsg);

  // Show bot response
  const botMsg = document.createElement("div");
  botMsg.className = "bot-msg";

  let answer = "Sorry, I don't understand.";
  switch (question) {
    case 'What types of water do you offer?':
      answer = "We offer Purified, Distilled, Mineral, and Alkaline water.";
      break;
    case 'How can I place an order?':
      answer = "You can place an order by clicking 'Order Now' on the homepage.";
      break;
    case 'What are your operating hours?':
      answer = "We are open from 8 AM to 6 PM, Monday to Saturday.";
      break;
    case 'Talk to customer service':
      answer = "Connecting you to a customer service representative. Please wait...";
      break;
  }

  setTimeout(() => {
    botMsg.innerText = answer;
    chatWindow.appendChild(botMsg);
    chatWindow.scrollTop = chatWindow.scrollHeight;
  }, 500); // Delay for more realistic effect
}
</script>

</body>
<div class="chatbot-widget">
  <div class="chatbot-header" onclick="toggleChatbot()">
    <span>💬 Need Help?</span>
  </div>
  <div class="chatbot-body" id="chatbotBody">
    <div class="chat-window" id="chatWindow">
      <div class="bot-msg">Hi there! How can I help you today?</div>
    </div>
    <div class="faq-options">
      <button onclick="handleFAQ('What types of water do you offer?')">What types of water do you offer?</button>
      <button onclick="handleFAQ('How can I place an order?')">How can I place an order?</button>
      <button onclick="handleFAQ('What are your operating hours?')">What are your operating hours?</button>
      <button onclick="handleFAQ('Talk to customer service')">Talk to customer service</button>
    </div>
  </div>
</div>
<style>
    .chatbot-widget {
  position: fixed;
  bottom: 20px;
  right: 20px;
  width: 300px;
  font-family: 'Poppins', sans-serif;
  z-index: 9999;
}

.chatbot-header {
  background-color: #007bff;
  color: #fff;
  padding: 10px;
  cursor: pointer;
  border-top-left-radius: 10px;
  border-top-right-radius: 10px;
}

.chatbot-body {
  display: none;
  background: #fff;
  border: 1px solid #ccc;
  border-top: none;
  border-bottom-left-radius: 10px;
  border-bottom-right-radius: 10px;
  max-height: 450px;
  width: 300px;
  box-shadow: 0 4px 10px rgba(0,0,0,0.1);
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.chat-window {
  flex: 1;
  overflow-y: auto;
  padding: 10px;
  display: flex;
  flex-direction: column;
}

.bot-msg, .user-msg {
  background: #f1f1f1;
  padding: 8px 12px;
  border-radius: 15px;
  margin: 5px 0;
  max-width: 80%;
}

.bot-msg {
  background: #e0f0ff;
  align-self: flex-start;
}

.user-msg {
  background: #dcf8c6;
  align-self: flex-end;
  text-align: right;
}

.faq-options {
  padding: 10px;
  background: #fafafa;
  border-top: 1px solid #eee;
  position: sticky;
  bottom: 0;
  z-index: 10;
}

.faq-options button {
  background: #f5f5f5;
  border: 1px solid #ddd;
  padding: 6px 10px;
  text-align: left;
  cursor: pointer;
  border-radius: 5px;
  transition: background 0.2s;
}

.faq-options button:hover {
  background: #e7f1ff;
}

/* Responsive Icon-only button */
#chatbot-button {
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 20px;
  padding: 10px 12px;
  background-color: #007bff;
  color: white;
  border: none;
  border-radius: 50%;
  cursor: pointer;
  position: fixed;
  bottom: 20px;
  right: 20px;
  box-shadow: 0 4px 8px rgba(0,0,0,0.3);
}

/* Chatbot body responsive sizing */
@media (max-width: 600px) {
  .chatbot-body {
    width: 90vw;
    max-height: 70vh;
    right: 5vw;
    bottom: 80px;
  }
}

</style>
<footer class="bg-dark text-white py-4 fade-in-up">
    <div class="container text-center">
        <img src="assets/images/logo.png" alt="Logo" style="height: 40px;" class="mb-2">
        <p class="mb-1">De Chavez Waterhaus – Pure Water, Trusted Service</p>
        <div class="mb-2">
            <a href="#home" class="text-white text-decoration-none mx-2">Home</a>
            <a href="#products" class="text-white text-decoration-none mx-2">Products</a>
            <a href="#about" class="text-white text-decoration-none mx-2">About Us</a>
            <a href="#faq" class="text-white text-decoration-none mx-2">FAQ</a>
            <a href="#contact" class="text-white text-decoration-none mx-2">Contact</a>
        </div>
        <small>&copy; <?php echo date("Y"); ?> De Chavez Waterhaus. All rights reserved.</small>
    </div>
</footer>
</html>
