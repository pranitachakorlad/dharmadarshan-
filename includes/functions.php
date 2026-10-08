<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/cart-wishlist.php';

initCartWishlist();

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function formatPrice(float $price): string
{
    return 'Rs. ' . number_format($price, 2);
}

function getCategories(PDO $db): array
{
    $stmt = $db->query("SELECT * FROM categories WHERE slug != 'other' ORDER BY id ASC");
    return $stmt->fetchAll();
}

function getCategoryBySlug(PDO $db, string $slug): ?array
{
    $stmt = $db->prepare("SELECT * FROM categories WHERE slug = ? LIMIT 1");
    $stmt->execute([$slug]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function getProductsByCategory(PDO $db, string $slug): array
{
    $stmt = $db->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug FROM products p INNER JOIN categories c ON p.category_id = c.id WHERE c.slug = ? AND p.is_active = 1 ORDER BY p.created_at DESC, p.id DESC");
    $stmt->execute([$slug]);
    return $stmt->fetchAll();
}

function getFeaturedProducts(PDO $db, int $limit = 8): array
{
    $stmt = $db->prepare("
        SELECT p.*, c.name AS category_name, c.slug AS category_slug
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.is_featured = 1 AND p.is_active = 1
        ORDER BY p.created_at DESC
        LIMIT ?
    ");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getLatestBlogPosts(PDO $db, int $limit = 3): array
{
    $stmt = $db->prepare("
        SELECT * FROM blog_posts
        WHERE is_published = 1
        ORDER BY created_at DESC
        LIMIT ?
    ");
    $stmt->bindValue(1, $limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

function categoryPageTitle(string $slug): string
{
    $titles = [
        'darshankundli' => 'Darshan Kundli',
        'ratna' => 'Ratna (Gemstones)',
        'yantra' => 'Yantra',
        'yach' => 'Yach (Yagya)',
        'shop-collection' => 'Shop Collection',
    ];
    return $titles[$slug] ?? 'Products';
}

function categorySubtitle(string $slug): string
{
    $subtitles = [
        'darshankundli' => 'An Effective Medium for Understanding Planetary Positions',
        'ratna' => 'Health-Based Suitability of Gemstones According to Planetary Positions in the Darshan Kundli',
        'yantra' => 'A Supportive Guide for Wealth and Vastu Prosperity',
        'yach' => 'A Traditional Source of Positive Energy in Human Life',
    ];

    return $subtitles[$slug] ?? '';
}

function categoryDescriptionHtml(string $slug): string
{
    $descriptions = [
        'darshankundli' => '
            <p>Darshan Kundli reflects a person&rsquo;s planetary positions based on their date of birth, time, and place of birth. Astrology itself is not a part of the Darshan Kundli; rather, astrology is a branch of knowledge derived from the teachings of Sage Bhrigu.</p>
            <p>The planetary positions shown in the Darshan Kundli indicate the suitability of gemstones that are associated with an individual&rsquo;s health and well-being.</p>
        ',
        'ratna' => '
            <p>Ratna are natural gemstones selected as Vedic remedies for planetary balance, confidence, prosperity, health, and personal clarity. The right gemstone is chosen only after understanding the birth chart, current dasha, and the purpose for wearing it.</p>
            <p>At Dharma Darshan, Ratna guidance focuses on authenticity, suitability, and correct wearing method. Explore certified stones such as Ruby, Emerald, Blue Sapphire, Yellow Sapphire, Pearl, Coral, Hessonite, and Cat Eye, along with guidance on metal, finger, day, mantra, and energizing process.</p>
        ',
        'yantra' => '
            <p>In sacred spiritual practice, Yantra is used as a geometric tool for the upliftment and progress of human life. It is based on the use of divine symbols and sacred geometric patterns that represent spiritual energies. In the combined practice of Yantra and Mantra, the geometric form of the Yantra and the sound vibrations produced through Mantra work together as powerful spiritual instruments.</p>
            <p>When properly energized through disciplined practice and devotion, they are believed to help open the path toward personal growth, inner strength, and spiritual progress. Mantras create a state of purified consciousness and help the mind become focused and steady. Through this concentration, the divine symbols represented in the Yantra become active as spiritual tools, generating positive energy around the individual.</p>
            <p>A Yantra in itself is a geometric form, but its true spiritual power is believed to emerge only after it is properly consecrated and energized through ritual practice. In spiritual tradition, the Yantras used in astrology and Dharma Darshan are often prepared by placing the individual&rsquo;s Ishta Devata at the center, and then energizing the Yantra through the Beej mantras of the deity as well as the planetary deities indicated in the person&rsquo;s horoscope or Darshan Kundali.</p>
        ',
        'yach' => '
            <p>The word Yajna is derived from a Sanskrit root and refers to a sacred ritual performed with a spiritual intention or resolve. In a Yajna, deities are invoked for the fulfillment of a particular purpose, and offerings are made into a properly kindled sacred fire as part of that ritual process. The materials used for these offerings are believed to possess beneficial qualities, and when offered into the purified fire, they help create a spiritually and environmentally pure atmosphere.</p>
            <p>The chanting of mantras and hymns in praise of the invoked deities is believed to awaken divine energies and fill the surroundings with positivity. For this reason, Yajna has traditionally been regarded as a powerful spiritual practice for invoking positive energy and divine blessings. In Dharma Darshan, Yajna is considered especially significant and is often associated with pleasing Goddess Lakshmi and invoking prosperity, harmony, and auspiciousness.</p>
            <p>According to the traditions said to have been carried forward by Sage Bhrigu, different forms of Yajna are performed to please one&rsquo;s chosen deity, fulfill specific intentions, and mark important social or spiritual occasions. When performed according to proper rules and rituals, Yajna, Homa, and Havan are believed to remove negativity and create an environment filled with positive spiritual energy. For this reason, Yajna holds a place of great importance in Dharma Darshan.</p>
        ',
        'shop-collection' => '
            <p>Shop Collection contains only our other spiritual products and accessories, separate from Ratna, Yantra, Yach, and Kundli services. Browse puja essentials, malas, copper items, dhoop, and devotional accessories here.</p>
        ',
    ];

    return $descriptions[$slug] ?? '<p>Explore carefully selected Dharma Darshan offerings for your spiritual practice.</p>';
}

function categoryFeatureImage(string $slug): ?string
{
    $images = [
        'yantra' => 'assets/images/yantra-feature.jpeg',
        'yach' => 'assets/images/yach-feature.jpeg',
    ];

    return $images[$slug] ?? null;
}

function ratnaCards(): array
{
    return [
        [
            'name' => 'Moti',
            'image' => 'assets/images/ratna-moti.jpeg',
            'description' => 'The Moon is associated with the gemstone Pearl. Pearls are found in many varieties, and natural pearls formed in the sea are considered extremely rare and valuable. A natural pearl is usually white in colour. Basra pearls are yellowish and highly lustrous, and they are traditionally found in Iraq. Another type is the cultured pearl, the method of which was first developed in Japan. In this process, special organic particles are inserted into oysters to produce pearls. The third type is the artificial pearl, which is made from glass, plastic, and other materials. Pearl carries the cooling and soothing qualities of the Moon into the body, helping to bring mental peace and reduce emotional disturbances. It is also widely used to strengthen the mind and improve self-confidence. Pearl is best worn in silver. Women should wear it on the little finger of the left hand, while men should wear it on the little finger of the right hand. It should ideally be worn during a favourable Moon period after placing it in a vessel containing milk and Tulsi leaves. The pearl must touch the skin for proper effect. The Moon Beej Mantra is “Om Shreem Shreem Chandraya Namah.”',
        ],
        [
            'name' => 'Povale',
            'image' => 'assets/images/ratna-povale.jpeg',
            'description' => 'Mars is associated with Red Coral. Coral grows on rocks deep within the sea and is formed by tiny living organisms that resemble plants. Coral is available in many colours, but red coral without holes is considered the best quality. Natural coral usually grows in branch-like forms, and for use in jewellery it is polished, cut, and shaped properly. Mars represents fire and energy, and coral is believed to help balance excessive fiery energy and reduce its harmful effects on the body. Coral is best worn in gold or Panchdhatu. Women may wear it on the ring finger of either hand, while men should wear it on the ring finger of the right hand. The coral should touch the skin to be effective. The Mars Beej Mantra is “Om Kraam Kreem Kraum Sah Bhaumaya Namah.”',
        ],
        [
            'name' => 'Vaidarya / Lasanya',
            'image' => 'assets/images/ratna-vaidarya.jpg',
            'description' => 'Ketu is associated with the gemstone Cat’s Eye (Lehsunia). The raw stones obtained from mines are carefully shaped, cut, and polished to bring out their shine and beauty. After polishing, the stone displays a distinct milky white streak within it. This gemstone is found in shades such as grey, yellowish, and green, and it is known for the unique ray of light that resembles the eye of a cat. In astrology, Ketu, like Rahu, is not considered a physical planet but a shadow planet that can create unfavourable effects in a person’s life. Cat’s Eye is worn to reduce and neutralize these negative influences of Ketu. It is best worn in silver. Both men and women are advised to wear it on the middle finger, and the gemstone must touch the skin to be effective. It should be worn during an auspicious time as prescribed. The Ketu Beej Mantra is “Om Sraam Sreem Sraum Sah Ketave Namah.”',
        ],
        [
            'name' => 'Pushkarraj',
            'image' => 'assets/images/ratna-pushkarraj.jpeg',
            'description' => 'Jupiter is associated with Yellow Sapphire, also known as Pukhraj. It is a naturally occurring gemstone, and a bright, lustrous yellow stone is considered the finest quality. According to Hindu spiritual tradition, Jupiter is the Guru of the Gods, and therefore this gemstone is believed to bring auspicious and beneficial results to the wearer. Yellow Sapphire is considered highly favourable for attracting wisdom, prosperity, blessings, and positive outcomes in life. It is best worn in gold, and both men and women should wear it on the index finger of the right hand. The gemstone must touch the skin to be effective. It is considered especially auspicious to wear Yellow Sapphire after sunrise on a Thursday. The Jupiter Beej Mantra is “Om Graam Greem Graum Sah Gurave Namah.”',
        ],
        [
            'name' => 'Gomedh',
            'image' => 'assets/images/ratna-gomedh.jpeg',
            'description' => 'Rahu is associated with the gemstone Hessonite (Gomed). Hessonite belongs to the garnet mineral family and is formed naturally within the earth. A dark brown or honey-coloured Hessonite is considered to be of good quality. In astrology, Rahu, like Ketu, is not a physical planet but a shadow planet, yet it is believed to have a strong influence on human life, often creating confusion, instability, and unexpected challenges. To reduce the negative effects of Rahu, Hessonite is recommended. It is best worn in silver or Panchdhatu, and the gemstone must touch the skin to be effective. Both men and women are advised to wear it on the middle finger. It should preferably be worn after sunrise during an auspicious time. The Rahu Beej Mantra is “Om Bhram Bhreem Bhraum Sah Rahave Namah.”',
        ],
        [
            'name' => 'Manik',
            'image' => 'assets/images/ratna-manik.jpeg',
            'description' => 'The Sun is associated with the gemstone Ruby (Manik). Ruby is a highly precious gemstone found in mines, and among all varieties, the Pigeon Blood Ruby is considered the finest and most valuable. Since the Sun is regarded as the ruling planet of life and energy in the solar system, Ruby is believed to bring a powerful and divine influence to the wearer. It is associated with strength, confidence, vitality, and authority. Ruby is best worn in gold. Women should wear it on the ring finger of the left hand, while men should wear it on the ring finger of the right hand. The gemstone must touch the skin for proper effect. It is considered highly auspicious to wear Ruby at sunrise.',
        ],
        [
            'name' => 'Heera',
            'image' => 'assets/images/ratna-heera.jpeg',
            'description' => 'Venus is associated with the gemstone Diamond. Natural diamonds are formed deep within the Earth’s crust under intense heat and extreme pressure, and high-quality natural diamonds are considered highly valuable. Well-cut diamonds sparkle brilliantly and are admired for their beauty and purity. Although some diamonds may appear similar to natural ones, genuine natural diamonds are rare and therefore expensive. A colourless and clear diamond is considered the most beneficial and auspicious. In astrology, Diamond is associated with luxury, attraction, comfort, beauty, and harmonious relationships. However, because it can produce mixed results depending on a person’s horoscope, it should be worn carefully and only when suitable. Both men and women may wear Diamond on the ring finger or little finger of either hand. The gemstone must touch the skin to be effective. It should preferably be worn after sunrise during a favourable Venus period. The Venus Beej Mantra is “Om Draam Dreem Draum Sah Shukraya Namah.”',
        ],
        [
            'name' => 'Pachu',
            'image' => 'assets/images/ratna-pachu.jpeg',
            'description' => 'Mercury is associated with the gemstone Emerald (Panna). Emerald is also a mineral gemstone found in mines, and its beautiful green colour comes from elements such as chromium and vanadium. Dark green emeralds are considered to be of the highest quality and are highly valued. Emerald is regarded as an excellent gemstone for improving both mental and physical well-being, and it is especially associated with intelligence, communication, clarity of thought, and overall balance. Gold is considered the best metal for wearing Emerald. Women may wear it on the little finger of either hand, while men are advised to wear it on the little finger of the right hand. It should be worn during an auspicious Nakshatra, and the gemstone must touch the skin to provide its proper effect. The Mercury Beej Mantra is “Om Braam Breem Braum Sah Budhaya Namah.”',
        ],
        [
            'name' => 'Neelam',
            'image' => 'assets/images/ratna-neelam.jpeg',
            'description' => 'Saturn is associated with the gemstone Blue Sapphire (Neelam). Blue Sapphire is formed naturally within the Earth through geological processes and is created from the mineral corundum under extremely high temperatures deep inside mines. A deep blue Blue Sapphire with a bright and lustrous appearance is considered to be of the finest quality. In astrology, Blue Sapphire is believed to have a strong influence and is considered highly beneficial for health, discipline, stability, focus, and protection when it suits the wearer. It is best worn in silver, as this helps maintain the gemstone’s positive influence. Both men and women are advised to wear it on the middle finger of the right hand. It is traditionally worn during an auspicious time in the evening, and the gemstone must touch the skin for proper effect. The Saturn Beej Mantra is “Om Praam Preem Praum Sah Shanaye Namah” or “Om Sham Shanicharaya Namah” depending on the tradition followed.',
        ],
        [
            'name' => 'Navratna',
            'image' => 'assets/images/ratna-navratna.jpeg',
            'description' => 'In spiritual and astrological tradition, Navratna represents health, prosperity, and overall well-being. The nine gemstones symbolize the celestial bodies and planetary forces of the solar system, and each gemstone is associated with a specific planet. Navratna gemstones can be worn in jewellery such as rings, pendants, or other ornaments in a way that allows them to remain in physical contact with the body, so their effects are believed to work both internally and externally. Traditionally, Navratna is arranged in a specific manner, with Ruby, the gemstone of the Sun, placed at the center of the design because the Sun is considered the chief among the planets. It is important to use pure and flawless gemstones when wearing Navratna, as these gemstones are believed to carry protective and auspicious energies. Defective or impure gemstones are traditionally considered unsuitable, as they may create adverse effects on health and well-being. According to belief, pure gemstones emit subtle positive vibrations that help resist negative influences. Their electrical and magnetic radiations are thought to interact with the surrounding environment and the human body. Therefore, gemstones should always be chosen carefully according to their quality, authenticity, and proper astrological suitability.',
        ],
    ];
}

function shopCollectionCards(): array
{
    return [
        ['slug' => 'upratna-mani', 'name' => 'Upratna - Mani', 'image' => 'assets/images/shop-upratna-mani.png'],
        ['slug' => 'ring', 'name' => 'Ring', 'image' => 'assets/images/shop-ring.png'],
        ['slug' => 'pendant', 'name' => 'Pendant', 'image' => 'assets/images/shop-pendant.png'],
        ['slug' => 'rudraksh', 'name' => 'Rudraksh', 'image' => 'assets/images/shop-rudraksh.png'],
        ['slug' => 'mala', 'name' => 'Mala', 'image' => 'assets/images/shop-mala.png'],
        ['slug' => 'bracelet', 'name' => 'Bracelet', 'image' => 'assets/images/shop-bracelet.png'],
        ['slug' => 'attar', 'name' => 'Attar', 'image' => 'assets/images/shop-attar.png'],
        ['slug' => 'puja-samagri', 'name' => 'Puja Samagri', 'image' => 'assets/images/shop-puja-samagri.png'],
        ['slug' => 'view-more', 'name' => 'View More', 'image' => 'assets/images/shop-view-more.png'],
    ];
}

function shopCollectionCardBySlug(string $slug): ?array
{
    foreach (shopCollectionCards() as $card) {
        if ($card['slug'] === $slug) {
            return $card;
        }
    }

    return null;
}

function navItems(): array
{
    return [
        ['slug' => 'home', 'label' => 'Home'],
        ['slug' => 'darshankundli', 'label' => 'Darshan Kundli'],
        ['slug' => 'ratna', 'label' => 'Ratna'],
        ['slug' => 'yantra', 'label' => 'Yantra'],
        ['slug' => 'yach', 'label' => 'Yach'],
        ['slug' => 'shop-collection', 'label' => 'Shop Collection'],
    ];
}

function shopNavItems(): array
{
    return array_values(array_filter(navItems(), fn($item) => $item['slug'] !== 'home'));
}

function productImageUrl(string $path, string $fallback = 'assets/images/placeholder-product.svg'): string
{
    if ($path && file_exists(__DIR__ . '/../' . $path)) {
        return $path;
    }
    return $fallback;
}

function currentUser(): ?array
{
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    return [
        'id' => (int) $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? 'User',
        'contact_no' => $_SESSION['user_contact_no'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'birth_date' => $_SESSION['user_birth_date'] ?? '',
        'birth_time' => $_SESSION['user_birth_time'] ?? '',
        'birth_place' => $_SESSION['user_birth_place'] ?? '',
    ];
}

function requireUser(): void
{
    if (!currentUser()) {
        setFlash('error', 'Please login to continue.');
        header('Location: login.php');
        exit;
    }
}
