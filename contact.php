<?php 
    $pageTitle = 'Contact Us';
    $currentPage = 'contact';
    include 'header.php'; 
?>

    <main class="section">
        <div class="container">
            <h2 class="section-title">Get In Touch</h2>
            <div class="contact-grid">
                <div class="feature-card" style="text-align: left;">
                    <h3>Contact Information</h3>
                    <div class="contact-info-item">
                        <i class="fa-solid fa-location-dot"></i> <span>Kochi, Kerala, India</span>
                    </div>
                    <div class="contact-info-item">
                        <i class="fa-solid fa-phone"></i> <span>+91 98765 43210</span>
                    </div>
                    <div class="contact-info-item">
                        <i class="fa-solid fa-envelope"></i> <span>vault@thevault.in</span>
                    </div>
                    <div class="social-links" style="margin-top: 30px;">
                        <a href="#"><i class="fa-brands fa-facebook"></i></a>
                        <a href="#"><i class="fa-brands fa-instagram"></i></a>
                        <a href="#"><i class="fa-brands fa-youtube"></i></a>
                    </div>
                </div>
                <div>
                    <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
                        <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #10b981; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
                            <i class="fa-solid fa-check-circle"></i> Message sent successfully! We'll get back to you soon.
                        </div>
                    <?php endif; ?>
                    <form action="contact.php" method="POST" class="contact-form">
                        <input type="text" name="name" required placeholder="Your Name" class="form-input">
                        <input type="email" name="email" required placeholder="Your Email" class="form-input">
                        <textarea name="message" required placeholder="Your Message" rows="5" class="form-input"></textarea>
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Send Message</button>
                    </form>
                </div>
            </div>
        </div>
    </main>

<?php include 'footer.php'; ?>
