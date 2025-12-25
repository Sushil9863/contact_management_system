<style>
    /* Enhanced Glass Navbar with Better Text Visibility */
    .navbar {
        background: rgba(0, 0, 0, 0.25);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding: 12px 0;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .navbar.scrolled {
        background: rgba(0, 0, 0, 0.3);
        padding: 8px 0;
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.25);
    }

    /* Improved Brand with Better Contrast */
    .navbar-brand {
        font-size: 1.9rem;
        font-weight: 800;
        background: linear-gradient(135deg, #ffffff 0%, #e0e0e0 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        position: relative;
        padding-left: 50px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        letter-spacing: 0.5px;
    }

    .navbar-brand::before {
        content: '\f2b9';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        font-size: 2rem;
        background: linear-gradient(135deg, #ffffff, #e0e0e0);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }

    /* Alternative Brand Styling Options */

    /* Option 1: White with glow */
    .navbar-brand.glow {
        background: linear-gradient(135deg, #ffffff 0%, #d4d4d4 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-shadow: 0 0 10px rgba(255, 255, 255, 0.5),
                     0 0 20px rgba(255, 255, 255, 0.3),
                     0 0 30px rgba(255, 255, 255, 0.2);
    }

    /* Option 2: Strong contrast gradient */
    .navbar-brand.contrast {
        background: linear-gradient(135deg, #00d4ff 0%, #0088ff 50%, #a855f7 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-shadow: 0 2px 8px rgba(0, 132, 255, 0.3);
    }

    /* Option 3: Gold/Yellow for maximum visibility */
    .navbar-brand.gold {
        background: linear-gradient(135deg, #FFD700 0%, #FFA500 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        text-shadow: 0 2px 8px rgba(255, 215, 0, 0.4);
    }

    .nav-link {
        color: rgba(255, 255, 255, 0.9) !important;
        font-weight: 500;
        font-size: 1.05rem;
        padding: 10px 22px !important;
        border-radius: 12px;
        margin: 0 5px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .nav-link::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 0;
        height: 3px;
        background: linear-gradient(90deg, #ffffff, #e0e0e0);
        border-radius: 3px;
        transition: width 0.3s ease;
    }

    .nav-link:hover::after,
    .nav-link.active::after {
        width: 70%;
    }

    .nav-link:hover {
        background: rgba(255, 255, 255, 0.15);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }

    .nav-link-add {
        background: rgba(40, 167, 69, 0.25);
        border: 1px solid rgba(40, 167, 69, 0.4);
        color: #ffffff !important;
        font-weight: 600;
    }

    .nav-link-add:hover {
        background: rgba(40, 167, 69, 0.35);
        border-color: rgba(40, 167, 69, 0.6);
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 8px 25px rgba(40, 167, 69, 0.3);
    }

    .nav-link-logout {
        background: rgba(220, 53, 69, 0.25);
        border: 1px solid rgba(220, 53, 69, 0.4);
        color: #ffffff !important;
        font-weight: 600;
    }

    .nav-link-logout:hover {
        background: rgba(220, 53, 69, 0.35);
        border-color: rgba(220, 53, 69, 0.6);
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 8px 25px rgba(220, 53, 69, 0.3);
    }

    .user-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #ffffff, #e0e0e0);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #333;
        font-weight: bold;
        margin-right: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
    }

    .user-welcome {
        color: rgba(255, 255, 255, 0.95);
        font-size: 0.95rem;
        font-weight: 500;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .user-email {
        color: rgba(255, 255, 255, 0.7);
        font-size: 0.8rem;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .navbar-toggler {
        border: 1px solid rgba(255, 255, 255, 0.3);
        border-radius: 8px;
        padding: 6px 10px;
        transition: all 0.3s ease;
        background: rgba(255, 255, 255, 0.1);
    }

    .navbar-toggler:hover {
        border-color: rgba(255, 255, 255, 0.6);
        background: rgba(255, 255, 255, 0.2);
    }

    /* Mobile menu */
    @media (max-width: 992px) {
        .navbar-collapse {
            background: rgba(0, 0, 0, 0.3);
            backdrop-filter: blur(20px);
            border-radius: 15px;
            margin-top: 15px;
            padding: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .nav-item {
            margin: 8px 0;
        }
        
        .nav-link {
            text-align: center;
            justify-content: center;
        }
        
        .user-info-mobile {
            text-align: center;
            padding: 15px;
            margin-bottom: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
    }

    /* Text Visibility Test - Use this to check contrast */
    .visibility-test {
        position: fixed;
        top: 10px;
        right: 10px;
        background: rgba(0, 0, 0, 0.7);
        color: white;
        padding: 10px;
        border-radius: 5px;
        font-size: 12px;
        z-index: 10000;
        display: none;
    }
</style>

<!-- Choose one of these options for the brand class -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container">
        <!-- Brand with Icon - Choose one of these classes for best visibility:
             - No class: White gradient (default)
             - class="glow": White with glow effect
             - class="contrast": Blue/purple gradient
             - class="gold": Gold/yellow gradient
        -->
        <a class="navbar-brand gold" href="index.php">
            ContactFlow
        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Content -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- Left Side - Add Contact -->
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link nav-link-add" href="create_contact.php">
                        <i class="fas fa-plus-circle mr-2"></i>
                        <span>New Contact</span>
                    </a>
                </li>
            </ul>

            <!-- User Info -->
            <?php if (isset($_SESSION['username'])): ?>
            <div class="d-none d-lg-flex align-items-center mr-4">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                </div>
                <div>
                    <div class="user-welcome">Hi, <?php echo htmlspecialchars($_SESSION['username']); ?>!</div>
                    <?php if (isset($_SESSION['user_email'])): ?>
                    <div class="user-email"><?php echo htmlspecialchars($_SESSION['user_email']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Right Side - Logout -->
            <ul class="navbar-nav">
                <li class="nav-item position-relative">
                    <a class="nav-link nav-link-logout" href="logout.php">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        <span>Sign Out</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Add scroll effect and visibility test -->
<script>
    $(document).ready(function() {
        // Scroll effect
        $(window).scroll(function() {
            if ($(window).scrollTop() > 50) {
                $('.navbar').addClass('scrolled');
            } else {
                $('.navbar').removeClass('scrolled');
            }
        });

        // Mobile user info
        <?php if (isset($_SESSION['username'])): ?>
        if ($(window).width() < 992) {
            $('.navbar-collapse').prepend(`
                <div class="user-info-mobile">
                    <div class="user-avatar mx-auto mb-2">
                        <?php echo strtoupper(substr($_SESSION['username'], 0, 1)); ?>
                    </div>
                    <div class="user-welcome">Hi, <?php echo htmlspecialchars($_SESSION['username']); ?>!</div>
                    <?php if (isset($_SESSION['user_email'])): ?>
                    <div class="user-email"><?php echo htmlspecialchars($_SESSION['user_email']); ?></div>
                    <?php endif; ?>
                </div>
            `);
        <?php endif ?>
    });
</script>