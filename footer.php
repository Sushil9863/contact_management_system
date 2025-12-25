<style>
    /* Footer Styles */
        .sticky-footer {
            background: rgba(0, 0, 0, 0.2);
            color: white;
            padding: 20px 0;
            width: 100%;
            flex-shrink: 0;
            text-align: center;
            backdrop-filter: blur(10px);
        }

        .footer-content {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .footer-links {
            display: flex;
            gap: 20px;
            margin-top: 10px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer-links a:hover {
            color: white;
            text-decoration: none;
        }

</style>

<footer class="sticky-footer">
    <div class="container">
        <div class="footer-content">
            <p>&copy; <?= date('Y') ?> Contact Manager. All rights reserved.</p>
            <p> Made With <i class="fas fa-heart"></i>  by Kismat Neupane</p>
        </div>
    </div>
</footer>