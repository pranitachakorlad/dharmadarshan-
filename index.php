<?php
require_once __DIR__ . '/includes/functions.php';

$db = getDB();
$blogPosts = getLatestBlogPosts($db, 1);
$prosperityPaths = [
    'darshankundli' => [
        'title' => 'Darshan Kundli',
        'description' => 'An Effective Medium for Understanding Planetary Positions',
    ],
    'ratna' => [
        'title' => 'Ratna',
        'description' => 'Health-Based Suitability of Gemstones According to Planetary Positions in the Darshan Kundli',
    ],
    'yantra' => [
        'title' => 'Yantra',
        'description' => 'A Supportive Guide for Wealth and Vastu Prosperity',
    ],
    'yach' => [
        'title' => 'Yach',
        'description' => 'A Traditional Source of Positive Energy in Human Life',
    ],
];
$pageTitle = 'Home';

require_once __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="hero-bg" aria-hidden="true"></div>
    <div class="container hero-inner">
        <div class="hero-content">
            <h1 class="hero-title"><?= e(SITE_NAME) ?></h1>
            <p class="hero-slogan"><?= e(SITE_SLOGAN) ?></p>
            <p class="hero-intro">
                Faith in one&apos;s own religion is a natural part of every human being. The vast region stretching from the <span class="keep-together">Hindu Kush</span> mountains to the Indian Ocean is our country. It is believed that the people living in this region came to be known as Hindus through the evolution of the words <span class="keep-together">Hindu Kush and Indu</span>.
            </p>
            <p class="hero-intro hero-intro-secondary">
                From these principles of life emerged the concept of Dharma, the righteous way of living. The knowledge, philosophy, and understanding of these cultures came to be known as religious philosophy, <span class="keep-together">Dharma Darshan</span>.
            </p>
            <div class="hero-actions">
                <a href="category.php?cat=shop-collection" class="btn btn-primary">Explore Collection</a>
                <a href="https://wa.me/918108579007?text=Namaste%2C%20I%20want%20Darshan%20Kundli%20guidance%20from%20Dharma%20Darshan." class="btn btn-outline">Darshan Kundli on WhatsApp</a>
            </div>
            <a href="https://wa.me/918108579007?text=<?= rawurlencode('Namaste, I want more information about Dharma Darshan.') ?>" class="home-whatsapp-cta">
                For more information contact us on whatsapp
            </a>
        </div>
    </div>
</section>

<section class="section prosperity-section" id="prosperity">
    <div class="container">
        <header class="section-header">
            <h2>Your Path to Prosperity</h2>
            <p>Choose the guidance that matches your need.</p>
        </header>
        <div class="prosperity-grid">
            <?php foreach ($prosperityPaths as $pathSlug => $path): ?>
            <a href="category.php?cat=<?= e($pathSlug) ?>" class="prosperity-card">
                <span><?= e($path['title']) ?></span>
                <p><?= e($path['description']) ?></p>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="section-cta">
            <a href="category.php?cat=shop-collection" class="btn btn-outline">Shop Collection</a>
        </div>
    </div>
</section>

<section class="section blog-section" id="blog">
    <div class="container">
        <header class="section-header">
            <h2>Blog</h2>
            <p>Insights on astrology, remedies, and spiritual living</p>
        </header>
        <div class="blog-grid">
            <?php if ($blogPosts): ?>
                <?php foreach ($blogPosts as $post): ?>
                <article class="blog-card">
                    <div class="blog-image">
                        <img src="<?= e(productImageUrl($post['image'], 'assets/images/placeholder-blog.svg')) ?>" alt="<?= e($post['title']) ?>">
                    </div>
                    <div class="blog-body">
                        <time datetime="<?= e(date('Y-m-d', strtotime($post['created_at']))) ?>">
                            <?= e(date('F j, Y', strtotime($post['created_at']))) ?>
                        </time>
                        <h3><?= e($post['title']) ?></h3>
                        <p><?= e($post['excerpt']) ?></p>
                        <a href="blog.php?slug=<?= e($post['slug']) ?>" class="read-more">Read more</a>
                    </div>
                </article>
                <?php endforeach; ?>
            <?php else: ?>
                <article class="blog-card">
                    <div class="blog-body">
                        <h3>Understanding Your Kundli</h3>
                        <p>Demo blog post - import database to load full content.</p>
                    </div>
                </article>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="section vlog-section" id="vlog">
    <div class="container">
        <header class="section-header">
            <h2>Vlog</h2>
            <p>Watch our spiritual guidance and product showcases</p>
        </header>
        <div class="vlog-wrap">
            <div class="video-responsive">
                <iframe
                    src="https://www.youtube.com/embed/LXO-jKksQkM"
                    title="DharmaDarshan Demo Vlog"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen>
                </iframe>
            </div>
            <div class="vlog-caption">
                <h3>Welcome to DharmaDarshan</h3>
                <p>Demo video - replace with your YouTube embed URL when ready.</p>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
