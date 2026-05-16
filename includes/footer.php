<?php
// includes/footer.php
?>
</main>

<footer style="background: var(--navy-light); padding: 64px 0 24px; margin-top: auto; border-top: 1px solid var(--glass-border);">
    <div class="container">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px; margin-bottom: 48px;">
            <div class="footer-brand">
                <h3 style="font-size: 1.5rem; color: var(--white); margin-bottom: 16px;">Arts<span style="color: var(--gold);">Haven</span></h3>
                <p style="color: var(--gray-muted); margin-bottom: 24px;">Experience the ultimate in luxury and comfort. Book your perfect stay with our premium selection of hotels worldwide.</p>
                <div style="display: flex; gap: 16px;">
                    <a href="#" style="color: var(--gray-muted); font-size: 1.2rem;"><i class="fab fa-facebook"></i></a>
                    <a href="#" style="color: var(--gray-muted); font-size: 1.2rem;"><i class="fab fa-twitter"></i></a>
                    <a href="#" style="color: var(--gray-muted); font-size: 1.2rem;"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            
            <div class="footer-links">
                <h4 style="color: var(--white); margin-bottom: 16px;">Explore</h4>
                <ul style="list-style: none;">
                    <li style="margin-bottom: 8px;"><a href="index.php" style="color: var(--gray-muted);">Home</a></li>
                    <li style="margin-bottom: 8px;"><a href="hotels.php" style="color: var(--gray-muted);">Hotels & Resorts</a></li>
                    <li style="margin-bottom: 8px;"><a href="#" style="color: var(--gray-muted);">Special Offers</a></li>
                    <li style="margin-bottom: 8px;"><a href="#" style="color: var(--gray-muted);">Destinations</a></li>
                </ul>
            </div>
            
            <div class="footer-contact">
                <h4 style="color: var(--white); margin-bottom: 16px;">Contact Us</h4>
                <ul style="list-style: none;">
                    <li style="margin-bottom: 12px; color: var(--gray-muted);"><i class="fas fa-envelope" style="color: var(--gold); width: 24px;"></i> info@artshaven.com</li>
                    <li style="margin-bottom: 12px; color: var(--gray-muted);"><i class="fas fa-phone" style="color: var(--gold); width: 24px;"></i> 048 196 780</li>
                    <li style="margin-bottom: 12px; color: var(--gray-muted);"><i class="fas fa-map-marker-alt" style="color: var(--gold); width: 24px;"></i> Pejton Pristina</li>
                </ul>
            </div>
        </div>
        
        <div style="text-align: center; padding-top: 24px; border-top: 1px solid rgba(255,255,255,0.05); color: var(--gray-muted); font-size: 0.9rem;">
            <p>&copy; <?php echo date('Y'); ?> ArtsHaven. All rights reserved.</p>
        </div>
    </div>
</footer>

<!-- Main JS -->
<script src="assets/js/main.js"></script>
</body>
</html>
