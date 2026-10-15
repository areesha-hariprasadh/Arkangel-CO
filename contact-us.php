<?php
include 'includes/db.php';
    $success = "";
    $error = "";

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $name = trim($_POST["name"]);
        $email = trim($_POST["email"]);
        $subject = trim($_POST["subject"]);
        $message = trim($_POST["message"]);

        $missingFields = [];

        if (empty($name)) {
            $missingFields[] = "name";
        }

        if (empty($email)) {
            $missingFields[] = "email";
        }

        if (empty($subject)) {
            $missingFields[] = "subject";
        }

        if (empty($message)) {
            $missingFields[] = "message";
        }

        if (!empty($missingFields)) {
            if (count($missingFields) == 1) {

                $error = "Please fill in your " . $missingFields[0] . ".";
            } else {

                $lastField = array_pop($missingFields);

                $error = "Please fill in your " .
                        implode(", ", $missingFields) .
                        " and " .
                        $lastField . ".";
            }
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Please enter a valid email address.";
        } else {
            $stmt = $conn->prepare("
                INSERT INTO contact_messages (name, email, subject, message)
                VALUES (?, ?, ?, ?)
            ");

            $stmt->bind_param("ssss", $name, $email, $subject, $message);

            if ($stmt->execute()) {
                $success = "Message sent successfully!";
            } else {
                $error = "Something went wrong. Please try again.";
            }
            $stmt->close();
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us</title>
    <link rel="stylesheet" href="style-global.css">
    <link rel="stylesheet" href="includes/style.css">

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
</head>
<body class="contact-us">
    <!-- ===================== HEADER ===================== -->
    <?php include 'includes/header.php'; ?>

    <!-- ===================== CONTACT HERO ===================== -->
    <section class="contact-page-hero">
        <img src="images/hero/contact-hero.png" alt="Contact Hero">
    </section> 


    <!-- ===================== CONTACT SECTION ===================== -->
    <section class="contact-page-wrapper">
        <div class="contact-page-left">
            <h2 class="contact-page-section-title">GET IN TOUCH</h2>

            <p class="contact-page-description">
                Have a question or need assistance?<br>
                Reach out to us using any of the details below.
            </p>

            <div class="contact-page-info-box">
                <i class="fa-solid fa-location-dot"></i>
                <div class="contact-page-info-content">
                    <h4>ADDRESS</h4>

                    <p>
                        123 Blossom Avenue<br>
                        RoseWood, KingsTable, SA
                    </p>
                </div>
            </div>

            <div class="contact-page-info-box">
                <i class="fa-regular fa-envelope"></i>

                <div class="contact-page-info-content">
                    <h4>EMAIL</h4>

                    <p>arkangel&co@gmail.com</p>
                </div>
            </div>

            <div class="contact-page-info-box">
                <i class="fa-solid fa-phone"></i>
                <div class="contact-page-info-content">
                    <h4>PHONE NUMBER</h4>

                    <p>012-345-6789</p>
                </div>
            </div>

            <div class="contact-page-info-box">
                <i class="fa-regular fa-clock"></i>
                <div class="contact-page-info-content">

                    <h4>HOURS</h4>

                    <p>
                        Monday - Friday: 10:00AM - 6:00PM<br>
                        Saturday: 11:00AM - 5:00PM<br>
                        Sunday: Closed<br>
                        Public Holidays: Closed
                    </p>
                </div>
            </div>

            <div class="contact-page-social-icons">
                <i class="fa-brands fa-instagram"></i>
                <i class="fa-brands fa-facebook"></i>
                <i class="fa-brands fa-tiktok"></i>
            </div>
        </div>

        <div class="contact-page-right" id="contact-page-form-section">
            <h2 class="contact-page-section-title">SEND US A MESSAGE</h2>

            <p class="contact-page-description">
                Fill out the form and we'll get back to you as soon as possible
            </p>

            <?php if($success): ?>
            <p style="color:green; margin-bottom:15px;">
                <?php echo $success; ?>
            </p>
            <?php endif; ?>

            <?php if($error): ?>
            <p style="color:red; margin-bottom:15px;">
                <?php echo $error; ?>
            </p>
            <?php endif; ?>

            <form method="POST" action="#contact-page-form-section">
                <div class="contact-page-input-row">
                    <input type="text" 
                        name="name" 
                        placeholder="Full Name"
                        style="margin-bottom:20px;">

                    <input type="email" 
                        name="email" 
                        placeholder="Email Address"
                        style="margin-bottom:20px;">
                </div>


                <input type="text" 
                    name="subject" 
                    placeholder="Subject"
                    style="margin-bottom:20px;">
                <textarea name="message" 
                        placeholder="Message"></textarea>


                <button type="submit" class="contact-page-btn">
                    SEND MESSAGE
                </button>
            </form>
        </div>
    </section>

    <!-- ===================== FOOTER ===================== -->
    <?php include 'includes/footer.php'; ?>
</body>
</html>