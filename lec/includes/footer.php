<?php
// includes/footer.php
?>
    </div> <!-- Tutup div konten utama -->
    <style>
        html, body {
            height: 100%;
            margin: 0; /* add this to remove default margin */
        }

        .main-content {
            min-height: 100vh; /* set main content area to 100vh */
            display: flex;
            flex-direction: column;
        }
 
        footer {
            flex-shrink: 0; /* prevent footer from shrinking */
        }
    </style>
    <div class="main-content"> <!-- wrap main content in a div -->
        <!-- your main content here -->
    </div>
    <footer class="bg-dark text-white mt-5 py-3">
        <div class="container text-center">
            <p>&copy; <?php echo date('Y'); ?> Event Registration System. All rights reserved.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/assets/js/main.js"></script>
</body>
</html>