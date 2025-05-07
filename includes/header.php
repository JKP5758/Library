<?php
    session_start();
    $isLoggedIn = isset($_SESSION['username']); 
?>

<!DOCTYPE html>
<html>
<head>
    <title>Book Shop</title>
    <link rel="stylesheet" type="text/css" href="../../assets/style.css">
    <link rel="icon" type="image/png" href="../../assets/images/favicon.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <a href="../../view/dashboard/" class="logo">
                <h1>Book Shop</h1>
            </a>
            <nav>
                <a href="../../view/dashboard/" class="nav-link"><i class="fas fa-home"></i> Beranda</a>
                <?php if (!$isLoggedIn): ?>
                    <a href="../../view/login/" class="nav-link"><i class="fas fa-sign-in-alt"></i> Login</a>
                <?php else: ?>
                    <a href="../../view/keranjang/" class="nav-link cart-icon" title="Keranjang">
                        <i class="fas fa-shopping-cart"></i>
                    </a>

                    <div class="profile-menu" id="profileMenu">
                        <img src="../../assets/images/user.jpg" alt="Foto Profil" class="profile-img">
                        <ul class="dropdown" id="dropdownMenu">
                            <li><a href="../../view/user"><i class="fas fa-user"></i> Profil</a></li>
                            <li><a href="../../view/member"><i class="fas fa-users"></i> Komunitas</a></li>
                            <li><a href="../../view/login/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </div>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="site-content">

    <script>
        const profileMenu = document.getElementById('profileMenu');
        const dropdown = document.getElementById('dropdownMenu');
        let timeout;

        profileMenu.addEventListener('mouseenter', () => {
            clearTimeout(timeout);
            dropdown.style.display = 'block';
        });

        profileMenu.addEventListener('mouseleave', () => {
            timeout = setTimeout(() => {
                dropdown.style.display = 'none';
            }, 250); // jeda 0.25 detik
        });

        dropdown.addEventListener('mouseenter', () => {
            clearTimeout(timeout);
        });

        dropdown.addEventListener('mouseleave', () => {
            timeout = setTimeout(() => {
                dropdown.style.display = 'none';
            }, 250);
        });
    </script>
