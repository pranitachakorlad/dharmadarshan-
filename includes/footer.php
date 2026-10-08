<?php $base = !empty($isAdminPage) ? '../' : ''; ?>
</main>
<footer class="site-footer">
    <div class="container footer-grid">
        <section class="footer-about">
            <h3>About Us</h3>
            <p>
                According to science, many billions of years have passed since the creation of the universe. However, in Hindu philosophy, pure knowledge has been passed down since the beginning of creation. For science, the universe still remains a subject of curiosity and exploration. The universe serves as the foundation for new discoveries and inventions made through science.
            </p>
            <p>
                Certain narrow-minded and destructive forces have created the impression that Hindu philosophy is against science, and this conflict has been used to support activities that are harmful to the nation. Hindutva is a matter of faith, and for thousands of years, other religious groups have tried to weaken or challenge this faith.
            </p>
            <p>
                Hindu philosophy teaches that while emphasizing Hindutva, people should understand the philosophical nature of religion and work towards building a strong nation. It says that contributing to the nation&apos;s development is the moral responsibility of every individual.
            </p>
            <a href="https://wa.me/918108579007?text=<?= rawurlencode('Namaste, I want information about Yajna services.') ?>" class="btn btn-primary">Contact us for Yajna services</a>
        </section>
        <section class="footer-links">
            <h3>Quick Links</h3>
            <ul>
                <?php foreach (navItems() as $item): ?>
                <?php if ($item['slug'] === 'home') { continue; } ?>
                <?php
                $href = !empty($isAdminPage) ? 'products.php?cat=' . $item['slug'] : $base . 'category.php?cat=' . $item['slug'];
                ?>
                <li><a href="<?= e($href) ?>"><?= e($item['label']) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </section>
        <section class="footer-contact">
            <h3>Contact</h3>
            <p>Email: info@dharmadarshan.in</p>
            <p>Phone: +91 81085 79007</p>
        </section>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <p>Powered by <strong>KDR Infotect</strong> &copy; 2026</p>
        </div>
    </div>
</footer>
<script src="<?= $base ?>assets/js/main.js"></script>
</body>
</html>
