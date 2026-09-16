<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Goals\Recommendations;

use Piwik\Piwik;

/**
 * Rule-based goal recommender. Turns crawl evidence (shop platform, forms, hosts,
 * structured data, paths) into the best trackable goal per conversion category,
 * ranked by business value. Event goals are flagged as needing setup.
 */
class DeterministicRecommender
{
    /**
     * Category value boosts for the site being analysed, see siteTypeBoosts().
     *
     * @var array<string, int>
     */
    private $boosts = [];

    /** Goals returned; the UI shows the first ones and offers the rest on demand. */
    public const MAX_RECOMMENDATIONS = 10;

    /** A segment with more dash separated parts than this is an article slug, not a page name. */
    private const MAX_SLUG_PARTS = 3;

    /** Distinct product pages needed before a "viewed a product" goal is offered. */
    private const MIN_PRODUCT_PAGES = 5;

    /** Distinct files of one extension needed before an aggregated download goal is offered. */
    private const MIN_FILES_FOR_AGGREGATE = 2;

    /** A form seen on this share of crawled pages (min 3) is site wide, not a page's own form. */
    private const SITE_WIDE_FORM_SHARE = 0.3;

    /**
     * Conversion categories by business value (10 = revenue outcome, 2 = weak intent).
     * Tokens match whole path segments or their dash separated parts, "=" restricts to
     * whole segments; tokens and verbs are supporting evidence only.
     *
     * @var array<string, array{value: int, tokens: string[], verbs: string[], nameKey: string, pageNameKey?: string, eventNameKey?: string, eventName?: string, reasonKey: string}>
     */
    private const CATEGORIES = [
        'purchase' => [
            'value' => 10,
            'tokens' => ['thank-you', 'thankyou', 'thank_you', 'order-received', 'order-confirmation', 'order-complete', 'order-success', 'purchase-confirmation', 'bestellbestaetigung', '=danke', '=merci', '=gracias', '=grazie', '=bedankt', '=vielen-dank'],
            'verbs' => [],
            'nameKey' => 'Goals_RecommendationPurchaseName',
            'reasonKey' => 'Goals_RecommendationPurchaseReason',
        ],
        'checkout' => [
            'value' => 9,
            'tokens' => ['checkout', 'checkouts', '=kasse', '=caisse', '=bestellen', '=order', '=pagar', '=afrekenen', '=commande'],
            'verbs' => [],
            'nameKey' => 'Goals_RecommendationCheckoutName',
            'reasonKey' => 'Goals_RecommendationCheckoutReason',
        ],
        'cart' => [
            'value' => 7,
            'tokens' => ['cart', 'basket', 'bag', 'warenkorb', 'panier', 'carrito', 'carrello', 'winkelwagen', 'winkelmand'],
            'verbs' => [],
            'nameKey' => 'Goals_RecommendationCartName',
            'eventNameKey' => 'Goals_RecommendationAddToCartName',
            'eventName' => 'add-to-cart',
            'reasonKey' => 'Goals_RecommendationCartReason',
        ],
        'signup' => [
            'value' => 9,
            'tokens' => ['signup', 'sign-up', 'register', 'registration', 'registrieren', 'registrierung', 'create-account', 'start-free', 'trial', 'free-trial', 'try', 'inscription', 'inscripcion', 'registro', 'registrati', 'iscrizione', 'iscriviti', 'registreren', 'aanmelden', 'essai'],
            'verbs' => ['signup', 'register', 'registrieren', 'inscrire', 'inscription', 'registrarse', 'registrati', 'registreren', 'trial'],
            'nameKey' => 'Goals_RecommendationSignupName',
            'eventNameKey' => 'Goals_RecommendationSignupEventName',
            'eventName' => 'signup',
            'reasonKey' => 'Goals_RecommendationSignupReason',
        ],
        'booking' => [
            'value' => 9,
            'tokens' => [
                '=book', 'booking', 'bookings', 'reserve', 'reservation', 'reservations', 'buchen', 'buchung', 'reservieren',
                'reservierung', 'appointment', 'appointments', 'terminbuchung', 'termin-buchen', 'termin-vereinbaren',
                'online-termin', 'reserver', 'reservar', 'reservas', 'prenota', 'prenotazioni', 'prenotazione', 'reserveren',
                'tickets', '=ticket', 'biglietti', 'billets', 'entradas', 'planen-buchen', 'book-now', 'book-online',
                'book-a-table', 'book-your-ticket', 'rendez-vous',
            ],
            'verbs' => ['book', 'reserve', 'buchen', 'reservieren', 'reserver', 'reservar', 'prenota', 'reserveren', 'tickets', 'biglietti', 'billets', 'entradas'],
            'nameKey' => 'Goals_RecommendationBookingName',
            'eventNameKey' => 'Goals_RecommendationBookingEventName',
            'eventName' => 'booking',
            'reasonKey' => 'Goals_RecommendationBookingReason',
        ],
        'donate' => [
            'value' => 9,
            'tokens' => ['donate', 'donation', 'donations', 'donate-now', 'give-now', 'spenden', 'spende', 'online-spenden', 'jetzt-spenden', 'faire-un-don', 'dons', 'donar', 'donaciones', 'dona', 'donazioni', 'doneren', 'fundraise', 'fundraising', 'soutenir', 'colabora', 'hazte-socio'],
            'verbs' => ['donate', 'spenden', 'don', 'donner', 'donar', 'dona', 'doneren', 'colabora'],
            'nameKey' => 'Goals_RecommendationDonateName',
            'eventNameKey' => 'Goals_RecommendationDonateEventName',
            'eventName' => 'donation',
            'reasonKey' => 'Goals_RecommendationDonateReason',
        ],
        'membership' => [
            'value' => 8,
            'tokens' => ['membership', 'member', 'become-a-member', 'mitglied', 'mitgliedschaft', 'mitglied-werden', 'foerdermitglied', 'fördermitglied', 'adhesion', 'adherer', 'socio', 'socios', 'lid-worden', 'abonnement'],
            'verbs' => ['member', 'mitglied', 'adherer', 'socio'],
            'nameKey' => 'Goals_RecommendationMembershipName',
            'reasonKey' => 'Goals_RecommendationMembershipReason',
        ],
        'apply' => [
            'value' => 8,
            'tokens' => ['apply', 'bewerbung', 'bewerben', 'zulassung', 'candidature', 'postuler', 'solliciteren'],
            'verbs' => ['apply', 'bewerben', 'postuler', 'solliciteren'],
            'nameKey' => 'Goals_RecommendationApplyName',
            'eventNameKey' => 'Goals_RecommendationApplyEventName',
            'eventName' => 'application',
            'reasonKey' => 'Goals_RecommendationApplyReason',
        ],
        'demo' => [
            'value' => 8,
            'tokens' => ['demo', 'request-demo', 'book-a-demo', 'quote', 'get-a-quote', 'estimate', 'request-estimate', '=angebot', 'angebot-anfordern', '=anfrage', '=anfragen', 'devis', 'presupuesto', 'preventivo', 'consultation', 'callback', 'rueckruf', 'contact-sales', 'talk-to-sales', 'offerte-aanvragen'],
            'verbs' => ['demo', 'quote', 'estimate', 'angebot', 'devis', 'presupuesto', 'preventivo'],
            'nameKey' => 'Goals_RecommendationDemoName',
            'eventNameKey' => 'Goals_RecommendationDemoEventName',
            'eventName' => 'quote-request',
            'reasonKey' => 'Goals_RecommendationDemoReason',
        ],
        'contact' => [
            'value' => 6,
            'tokens' => ['contact', 'contact-us', 'kontakt', 'kontaktformular', 'contacto', 'contactar', 'contatti', 'contatto', 'contactez-nous', 'get-in-touch', 'enquiry', 'enquiries', 'inquiry', 'pre-sale-question'],
            'verbs' => ['contact', 'kontakt', 'contacto', 'contatti', 'contactez'],
            'nameKey' => 'Goals_RecommendationContactName',
            'eventNameKey' => 'Goals_RecommendationContactEventName',
            'eventName' => 'contact-form',
            'reasonKey' => 'Goals_RecommendationContactReason',
        ],
        'newsletter' => [
            'value' => 6,
            'tokens' => ['newsletter', 'newsletters', 'subscribe', 'abonnieren', 'abonnez-vous', 'suscribirse', 'e-mail-subscription', 'mailing-list', 'nieuwsbrief', 'boletin', 'iscriviti-alla-newsletter'],
            'verbs' => ['newsletter', 'subscribe', 'abonnieren', 'abonner', 'suscribir', 'iscriviti', 'inschrijven'],
            'nameKey' => 'Goals_RecommendationNewsletterName',
            'eventNameKey' => 'Goals_RecommendationNewsletterEventName',
            'eventName' => 'newsletter-signup',
            'reasonKey' => 'Goals_RecommendationNewsletterReason',
        ],
        'activation' => [
            'value' => 5,
            'tokens' => ['getting-started', 'get-started', '=installation', '=install', 'quickstart', 'quick-start', '=setup', 'erste-schritte', 'premiers-pas', 'primeros-pasos'],
            'verbs' => [],
            'nameKey' => 'Goals_RecommendationActivationName',
            'reasonKey' => 'Goals_RecommendationActivationReason',
        ],
        'sponsor' => [
            'value' => 5,
            'tokens' => ['=sponsor', '=sponsors', '=sponsoring', 'support-us', '=backers'],
            'verbs' => ['sponsor', 'sponsern', 'sponsoriser', 'patrocinar', 'sponsorizza'],
            'nameKey' => 'Goals_RecommendationSponsorName',
            'pageNameKey' => 'Goals_RecommendationSponsorPageName',
            'reasonKey' => 'Goals_RecommendationSponsorReason',
        ],
        'community' => [
            'value' => 4,
            'tokens' => ['=community', '=discord', '=slack', '=forum'],
            'verbs' => ['join', 'discord', 'slack', 'beitreten', 'rejoindre', 'unirse', 'unisciti'],
            'nameKey' => 'Goals_RecommendationCommunityName',
            'pageNameKey' => 'Goals_RecommendationCommunityPageName',
            'reasonKey' => 'Goals_RecommendationCommunityReason',
        ],
        'download' => [
            'value' => 4,
            'tokens' => ['=download', '=downloads', '=herunterladen', '=telecharger', '=telechargement', '=descargar', '=descargas', '=scaricare', '=downloaden'],
            'verbs' => [],
            'nameKey' => 'Goals_RecommendationDownloadPageName',
            'reasonKey' => 'Goals_RecommendationDownloadReason',
        ],
        'partner' => [
            'value' => 4,
            'tokens' => ['partner-program', 'agency-partner-program', '=partners', '=partner', '=affiliates', '=affiliate', 'affiliate-area', '=partenaires', 'partner-werden', '=reseller', '=resellers'],
            'verbs' => [],
            'nameKey' => 'Goals_RecommendationOutlinkName',
            'pageNameKey' => 'Goals_RecommendationPartnerPageName',
            'reasonKey' => 'Goals_RecommendationOutlinkPlatformReason',
        ],
        'campaign' => [
            'value' => 6,
            'tokens' => ['petition', 'petitions', 'take-action', '=aktionen', '=mitmachen', '=engagieren', '=participer', '=agir', '=participa', '=actua', '=partecipa', 'doe-mee', '=volunteer', '=volunteering', '=benevolat', '=voluntariado', '=volontariato', 'gemeinsam-aktiv'],
            'verbs' => ['petition', 'sign', 'unterschreiben', 'signer', 'firma', 'mitmachen'],
            'nameKey' => 'Goals_RecommendationCampaignName',
            'reasonKey' => 'Goals_RecommendationCampaignReason',
        ],
        'events' => [
            'value' => 4,
            'tokens' => ['=events', '=event', '=veranstaltungen', '=evenements', '=eventi', '=eventos', '=evenementen', '=workshops', '=webinars', '=conference', '=conferences'],
            'verbs' => [],
            'nameKey' => 'Goals_RecommendationEventsName',
            'reasonKey' => 'Goals_RecommendationEventsReason',
        ],
        'offers' => [
            'value' => 3,
            'tokens' => ['special-offers', '=offers', '=angebote', '=promotions', '=deals', '=aanbiedingen', '=ofertas', '=offerte', '=promociones', '=promozioni', '=sale', '=coupons', 'gutscheine', 'vouchers'],
            'verbs' => [],
            'nameKey' => 'Goals_RecommendationOffersName',
            'reasonKey' => 'Goals_RecommendationOffersReason',
        ],
        'menu' => [
            'value' => 3,
            'tokens' => ['=menu', '=menus', '=speisekarte', '=carte', '=la-carte', '=carta', '=menukaart', '=wine-list', '=drinks-list', '=weinkarte'],
            'verbs' => [],
            'nameKey' => 'Goals_RecommendationMenuName',
            'reasonKey' => 'Goals_RecommendationMenuReason',
        ],
        'pricing' => [
            'value' => 3,
            'tokens' => ['pricing', 'prices', 'price', 'preise', 'tarife', 'plans', 'tarifs', 'prix', 'precios', 'tarifas', 'prezzi', 'prijzen', 'tarieven'],
            'verbs' => [],
            'nameKey' => 'Goals_RecommendationPricingName',
            'reasonKey' => 'Goals_RecommendationPricingReason',
        ],
        'product' => [
            'value' => 3,
            'tokens' => [],
            'verbs' => [],
            'nameKey' => 'Goals_RecommendationProductName',
            'reasonKey' => 'Goals_RecommendationProductReason',
        ],
        'enterprise' => [
            'value' => 3,
            'tokens' => ['enterprise', 'enterprises', 'for-business'],
            'verbs' => [],
            'nameKey' => 'Goals_RecommendationEnterpriseName',
            'reasonKey' => 'Goals_RecommendationEnterpriseReason',
        ],
        'locations' => [
            'value' => 4,
            'tokens' => ['locations', 'store-locator', 'find-a-store', 'standorte', 'filialen', 'haendlersuche', 'magasins', 'tiendas', 'negozi', 'winkels', 'ladenlokal'],
            'verbs' => [],
            'nameKey' => 'Goals_RecommendationLocationsName',
            'reasonKey' => 'Goals_RecommendationLocationsReason',
        ],
    ];

    /**
     * Platform URLs a crawl never reaches.
     *
     * @var array<string, array<string, string>>
     */
    private const PLATFORM_PATTERNS = [
        'shopify' => ['purchase' => '/thank_you', 'checkout' => '/checkouts/', 'cart' => '/cart'],
        'woocommerce' => ['purchase' => '/checkout/order-received/', 'checkout' => '/checkout', 'cart' => '/cart'],
        'edd' => ['purchase' => '/checkout/purchase-confirmation', 'checkout' => '/checkout'],
        'shopware' => ['purchase' => '/checkout/finish', 'checkout' => '/checkout/confirm', 'cart' => '/checkout/cart'],
        'magento' => ['purchase' => '/checkout/onepage/success', 'checkout' => '/checkout', 'cart' => '/checkout/cart'],
        'prestashop' => ['purchase' => '/order-confirmation', 'checkout' => '/order', 'cart' => '/cart'],
        'sfcc' => ['purchase' => 'Order-Confirm', 'checkout' => 'Checkout-Begin', 'cart' => 'Cart-Show'],
    ];

    /**
     * schema.org types that reveal what kind of organisation runs the site, and the
     * categories that matter more for it.
     *
     * @var array<string, array<string, int>>
     */
    private const SITE_TYPE_BOOSTS = [
        'NGO' => ['donate' => 2, 'membership' => 2, 'campaign' => 2],
        'NonProfit' => ['donate' => 2, 'membership' => 2, 'campaign' => 2],
        'DonateAction' => ['donate' => 2, 'membership' => 1],
        'Restaurant' => ['booking' => 1, 'menu' => 2],
        'FoodEstablishment' => ['booking' => 1, 'menu' => 2],
        'Cafe' => ['booking' => 1, 'menu' => 2],
        'Bakery' => ['menu' => 2],
        'Hotel' => ['booking' => 2],
        'LodgingBusiness' => ['booking' => 2],
        'TouristDestination' => ['booking' => 2],
        'Museum' => ['booking' => 2, 'membership' => 1],
        'EducationalOrganization' => ['apply' => 2, 'events' => 1],
        'CollegeOrUniversity' => ['apply' => 2, 'events' => 1],
        'Course' => ['apply' => 1, 'signup' => 1],
        'SoftwareApplication' => ['signup' => 1, 'activation' => 1],
        'LocalBusiness' => ['contact' => 1, 'locations' => 1, 'booking' => 1],
        'Event' => ['booking' => 1, 'events' => 1],
    ];

    /**
     * Third-party hosts where the click itself is a conversion step, by category.
     * Matched on the host or any subdomain of it.
     *
     * @var array<string, string>
     */
    private const CONVERSION_HOSTS = [
        'opencollective.com' => 'sponsor', 'patreon.com' => 'sponsor', 'ko-fi.com' => 'sponsor', 'buymeacoffee.com' => 'sponsor',
        'liberapay.com' => 'sponsor', 'github.com/sponsors' => 'sponsor',
        'paypal.com/donate' => 'donate', 'paypal.me' => 'donate', 'betterplace.org' => 'donate', 'justgiving.com' => 'donate',
        'gofundme.com' => 'donate', 'donorbox.org' => 'donate', 'givebutter.com' => 'donate', 'helloasso.com' => 'donate',
        'calendly.com' => 'booking', 'sevenrooms.com' => 'booking', 'opentable.com' => 'booking', 'opentable.de' => 'booking',
        'opentable.co.uk' => 'booking', 'resy.com' => 'booking', 'thefork.com' => 'booking', 'thefork.de' => 'booking',
        'thefork.fr' => 'booking', 'thefork.it' => 'booking', 'quandoo.com' => 'booking', 'quandoo.de' => 'booking',
        'booking.com' => 'booking', 'doctolib.fr' => 'booking', 'doctolib.de' => 'booking', 'acuityscheduling.com' => 'booking',
        'setmore.com' => 'booking', 'simplybook.me' => 'booking', 'treatwell.com' => 'booking', 'zenchef.com' => 'booking',
        'eventbrite.com' => 'booking', 'eventbrite.de' => 'booking', 'eventbrite.co.uk' => 'booking', 'eventbrite.fr' => 'booking',
        'ticketmaster.com' => 'booking', 'ticketmaster.de' => 'booking', 'eventim.de' => 'booking', 'tito.io' => 'booking',
        'universe.com' => 'booking', 'ticketone.it' => 'booking', 'vivaticket.com' => 'booking',
        'discord.gg' => 'community', 'discord.com' => 'community', 'slack.com' => 'community', 'meetup.com' => 'community',
        'apps.apple.com' => 'partner', 'play.google.com' => 'partner', 'apps.shopify.com' => 'partner',
        'wordpress.org/plugins' => 'partner', 'chrome.google.com/webstore' => 'partner', 'chromewebstore.google.com' => 'partner',
        'checkout.stripe.com' => 'checkout', 'buy.stripe.com' => 'checkout', 'gumroad.com' => 'checkout',
        'lemonsqueezy.com' => 'checkout', 'paddle.com' => 'checkout',
        'amazon.com' => 'partner', 'amazon.de' => 'partner', 'amazon.co.uk' => 'partner', 'amazon.fr' => 'partner',
        'amazon.it' => 'partner', 'amazon.es' => 'partner', 'amazon.nl' => 'partner', 'etsy.com' => 'partner',
    ];

    /** Sibling subdomain tokens that indicate a conversion destination, by category. */
    private const SIBLING_SUBDOMAINS = [
        'shop' => 'partner', 'store' => 'partner', 'order' => 'checkout', 'signup' => 'signup', 'register' => 'signup',
        'app' => 'signup', 'checkout' => 'checkout', 'booking' => 'booking', 'book' => 'booking', 'tickets' => 'booking',
        'reservation' => 'booking', 'donate' => 'donate', 'spenden' => 'donate', 'act' => 'campaign', 'certification' => 'partner',
        'events' => 'booking', 'community' => 'community',
        'forum' => 'community', 'my' => 'signup', 'account' => 'signup',
    ];

    /** Redirector hosts (go.example.com/discord): the first path segment names the destination. */
    private const REDIRECTOR_SUBDOMAINS = ['go', 'link', 'links', 'l', 'to'];

    /** Redirector path segments and social hosts that never count as conversions. */
    private const IGNORED_DESTINATIONS = [
        'x', 'twitter', 'facebook', 'instagram', 'linkedin', 'youtube', 'tiktok', 'pinterest', 'bluesky', 'bsky', 'mastodon',
        'github', 'license', 'rss', 'feed', 'threads',
    ];

    /** Path segments that mark content, account or utility areas, never conversions. */
    private const EXCLUDED_SEGMENTS = [
        'blog', 'news', 'aktuelles', 'actualites', 'noticias', 'notizie', 'nieuws', 'press', 'presse', 'prensa', 'docs', 'doc',
        'documentation', 'help', 'hilfe', 'aide', 'ayuda', 'aiuto', 'faq', 'faqs', 'legal', 'privacy', 'privacy-policy',
        'datenschutz', 'terms', 'agb', 'cgv', 'imprint', 'impressum', 'mentions-legales', 'login', 'logout', 'signin',
        'sign-in', 'anmelden', 'connexion', 'accedi', 'inloggen', 'wp-login.php', 'password', 'search', 'suche', 'recherche',
        'busqueda', 'ricerca', 'zoeken', 'tag', 'tags', 'category', 'categories', 'author', 'sitemap', 'feed', 'rss', 'page',
        'wp-content', 'wp-admin', 'media', 'img', 'images', 'assets', 'static', 'cdn', 'files', 'wishlist', 'compare', 'cookies',
        'cookie-policy', 'accessibility', 'barrierefreiheit', 'careers', 'jobs', 'karriere', 'kundenservice',
        'customer-service', 'klantenservice', 'service-client', 'servizio-clienti', 'atencion-al-cliente',
    ];

    /** Sub-tokens that mark legal or account pages wherever they appear in a segment. */
    private const EXCLUDED_TOKENS = [
        'privacy', 'terms', 'conditions', 'policy', 'policies', 'legal', 'impressum', 'imprint', 'datenschutz', 'agb', 'cookie',
        'cookies', 'login', 'logout', 'signin', 'disclaimer', 'widerruf',
    ];

    /** Under these hubs a page only counts when its last segment is the category itself. */
    private const MARKETING_HUB_SEGMENTS = [
        'about', 'about-us', 'why-us', 'ueber-uns', 'uber-uns', 'company', 'features', 'feature', 'resources', 'learn',
        'services', 'solutions', 'leistungen', 'a-propos', 'chi-siamo', 'sobre-nosotros', 'over-ons', 'support', 'help',
    ];

    /** Segments under which many distinct paths mean a product catalogue. */
    private const PRODUCT_SEGMENTS = ['products', 'product', 'produkt', 'produkte', 'produit', 'produits', 'producto', 'productos', 'prodotto', 'prodotti', 'p', 'item', 'items', 'artikel', 'detail'];

    /** schema.org types that mark a product page. */
    private const PRODUCT_TYPES = ['Product', 'ProductGroup', 'Offer'];

    /** Login pages: password field, no other content. Vocabulary only breaks ties. */
    private const LOGIN_WORDS = ['login', 'log-in', 'signin', 'sign-in', 'anmelden', 'einloggen', 'connexion', 'iniciar', 'accedi', 'inloggen'];

    /** Download extensions that count as documents. */
    private const DOCUMENT_EXTENSIONS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'csv', 'zip'];

    /** Words in a file name or link label that mark a marketing or sales asset worth a goal of its own. */
    private const ASSET_TOKENS = [
        'brochure', 'broschuere', 'broschüre', 'prospekt', 'catalog', 'catalogue', 'katalog', 'catalogo', 'whitepaper', 'guide',
        'ebook', 'e-book', 'menu', 'menü', 'speisekarte', 'carte', 'karte', 'price-list', 'pricelist', 'preisliste', 'tarifs',
        'flyer', 'datasheet', 'datenblatt', 'programme', 'programm', 'report', 'checklist', 'folleto', 'depliant',
    ];

    /**
     * @param array<string, mixed> $analysis
     * @return array<int, array<string, mixed>>
     */
    public function recommend(array $analysis): array
    {
        $links = array_values(array_filter($analysis['links'] ?? [], 'is_array'));
        $pages = array_values(array_filter($analysis['pages'] ?? [], 'is_array'));
        $this->boosts = $this->siteTypeBoosts($pages);
        $pagesCrawled = max(1, (int) ($analysis['pagesCrawled'] ?? count($pages)));
        $siteDomain = $this->registrableDomain((string) parse_url((string) ($analysis['url'] ?? ''), PHP_URL_HOST));

        $candidates = array_merge(
            $this->platformCandidates((string) ($analysis['platform'] ?? '')),
            $this->pathCandidates($links, $pagesCrawled),
            $this->formCandidates($analysis['forms'] ?? [], $pagesCrawled),
            $this->outlinkCandidates($analysis['externalLinks'] ?? [], $siteDomain, $pagesCrawled),
            $this->shopCandidates($links, $pages, (string) ($analysis['platform'] ?? '')),
            $this->downloadCandidates($analysis['downloads'] ?? [], $siteDomain)
        );

        // category winners first, their runners-up (a second booking type) after them
        $best = [];
        $runnersUp = [];
        foreach ($candidates as $candidate) {
            $category = $candidate['category'];
            if (!isset($best[$category])) {
                $best[$category] = $candidate;
            } elseif ($this->score($candidate) > $this->score($best[$category])) {
                $runnersUp[$category] = $best[$category];
                $best[$category] = $candidate;
            } elseif (!isset($runnersUp[$category]) || $this->score($candidate) > $this->score($runnersUp[$category])) {
                $runnersUp[$category] = $candidate;
            }
        }
        $runnersUp = array_filter($runnersUp, function (array $candidate) use ($best): bool {
            // a second booking type is worth showing, the /en/ copy of the same page is not
            $winner = $this->pathFamily($best[$candidate['category']]['pattern']);
            $own = $this->pathFamily($candidate['pattern']);

            return $candidate['matchAttribute'] !== 'event_name'
                && $candidate['confidence'] >= 0.45
                && $own !== $winner
                && strpos($own, rtrim($winner, '/') . '/') !== 0
                && strpos($winner, rtrim($own, '/') . '/') !== 0;
        });
        $byScore = function (array $a, array $b): int {
            return $this->score($b) <=> $this->score($a);
        };
        usort($best, $byScore);
        usort($runnersUp, $byScore);

        $goals = [];
        $seen = [];
        foreach (array_merge($best, $runnersUp) as $candidate) {
            $goal = $this->buildGoal($candidate);
            $key = RecommendationMatcher::buildKey($goal['matchAttribute'], $goal['pattern']);
            if ($key === '' || isset($seen[$key])) {
                continue;
            }
            $seen[$key] = true;
            $goal['priority'] = count($goals) + 1;
            $goals[] = $goal;
            if (count($goals) >= self::MAX_RECOMMENDATIONS) {
                break;
            }
        }

        return $goals;
    }

    /**
     * Business value first (boosted for the organisation type), then evidence
     * confidence, then prominence.
     *
     * @param array<string, mixed> $candidate
     */
    private function score(array $candidate): float
    {
        $value = self::CATEGORIES[$candidate['category']]['value'] + ($this->boosts[$candidate['category']] ?? 0);

        return $value * 3 + (float) $candidate['confidence'] * 10 + (float) $candidate['prominence'] * 5;
    }

    /**
     * Category boosts from the schema.org types of the crawled pages.
     *
     * @param array<int, array<string, mixed>> $pages
     * @return array<string, int>
     */
    private function siteTypeBoosts(array $pages): array
    {
        $boosts = [];
        foreach ($pages as $page) {
            foreach ($page['types'] ?? [] as $type) {
                foreach (self::SITE_TYPE_BOOSTS[$type] ?? [] as $category => $boost) {
                    $boosts[$category] = max($boosts[$category] ?? 0, $boost);
                }
            }
        }

        return $boosts;
    }

    /**
     * Path without a leading language segment, so /en/tickets and /nl/tickets are one family.
     */
    private function pathFamily(string $pattern): string
    {
        return (string) preg_replace('#^(/[a-z]{2}([-_][a-z]{2})?(?=/))+#i', '', strtolower($pattern));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function platformCandidates(string $platform): array
    {
        $candidates = [];
        foreach (self::PLATFORM_PATTERNS[$platform] ?? [] as $category => $pattern) {
            $candidates[] = $this->candidate($category, 'url', $pattern, 0.9, 0.0, 'platform', [
                Piwik::translate('Goals_RecommendationEvidencePlatformUrl', [ucfirst($platform)]),
            ]);
        }

        return $candidates;
    }

    /**
     * Same-origin paths classified by whole path tokens, link labels as supporting evidence.
     *
     * @param array<int, array<string, mixed>> $links
     * @return array<int, array<string, mixed>>
     */
    private function pathCandidates(array $links, int $pagesCrawled): array
    {
        $candidates = [];
        foreach ($links as $link) {
            $path = (string) ($link['linkTarget'] ?? '');
            $segments = $this->pathSegments($path);
            if (empty($segments) || $this->isFilePath($segments)) {
                continue;
            }
            $excluded = $this->isExcludedPath($segments);

            $bestCategory = null;
            $bestStrength = 0;
            foreach (self::CATEGORIES as $category => $definition) {
                // activation pages live inside documentation, everything else must not
                if ($excluded && ($category !== 'activation' || $this->isContentPath($segments))) {
                    continue;
                }
                $strength = $this->matchStrength($segments, $link, $definition['tokens']);
                if ($strength > $bestStrength) {
                    $bestCategory = $category;
                    $bestStrength = $strength;
                }
            }
            if ($bestCategory === null) {
                continue;
            }

            $label = $this->pageLabel($link, $path);
            $exact = $this->isExactCategoryPage($path, self::CATEGORIES[$bestCategory]['tokens']);
            $evidence = [Piwik::translate('Goals_RecommendationEvidenceLinkSightings', [
                (string) ($link['occurrenceCount'] ?? 1),
                (string) ($link['pageCount'] ?? 1),
            ])];
            if ($label !== '') {
                $evidence[] = $label;
            }

            // /pricing is a better pricing page than /support-plans
            $candidates[] = $this->candidate(
                $bestCategory,
                'url',
                $path,
                min(1.0, 0.4 + $bestStrength * 0.15 - (count($segments) - 1) * 0.1 + ($exact ? 0.1 : 0)),
                $this->prominence($link, $pagesCrawled),
                'rule',
                $evidence,
                ['label' => $exact ? '' : $label, 'exampleUrls' => array_slice($link['exampleUrls'] ?? [], 0, 3)]
            );
        }

        return $candidates;
    }

    /**
     * Forms by field type: a form with its own page becomes a URL goal, a site wide
     * form an event goal that needs setup.
     *
     * @param array<int, array<string, mixed>> $forms
     * @return array<int, array<string, mixed>>
     */
    private function formCandidates(array $forms, int $pagesCrawled): array
    {
        // the same form on many pages shows up once per page path, group it by its fields
        $groups = [];
        foreach ($forms as $form) {
            if (!is_array($form) || empty($form['fieldTypes'])) {
                continue;
            }
            $key = implode(',', $form['fieldTypes']);
            if (!isset($groups[$key])) {
                $groups[$key] = ['fieldTypes' => $form['fieldTypes'], 'pages' => [], 'labels' => [], 'actions' => []];
            }
            foreach ($form['sourcePages'] ?? [] as $page) {
                $groups[$key]['pages'][(string) parse_url((string) $page, PHP_URL_PATH) ?: '/'] = true;
            }
            foreach ($form['submitTexts'] ?? [] as $text) {
                $groups[$key]['labels'][] = (string) $text;
            }
            $groups[$key]['actions'][] = (string) ($form['action'] ?? '');
        }

        $candidates = [];
        foreach ($groups as $group) {
            $pagePaths = array_values(array_filter(array_keys($group['pages']), function (string $path): bool {
                return !$this->isExcludedPath($this->pathSegments($path));
            }));
            $category = empty($pagePaths) ? null : $this->classifyForm($group['fieldTypes'], $group['labels'], $pagePaths);
            if ($category === null) {
                continue;
            }
            $definition = self::CATEGORIES[$category];
            $pageCount = count($pagePaths);
            $isSiteWide = $pageCount >= max(3, (int) ceil($pagesCrawled * self::SITE_WIDE_FORM_SHARE));
            $evidence = [Piwik::translate('Goals_RecommendationEvidenceFormFields', [
                implode(', ', array_unique($group['fieldTypes'])),
                (string) $pageCount,
            ])];
            $label = trim((string) ($group['labels'][0] ?? ''));
            if ($label !== '') {
                $evidence[] = $label;
            }
            $prominence = min(1.0, $pageCount / $pagesCrawled) * 0.5 + 0.25;

            $ownPage = !$isSiteWide ? $this->formPage($pagePaths, $definition['tokens']) : null;
            if ($ownPage !== null) {
                $candidates[] = $this->candidate($category, 'url', $ownPage, 0.75, $prominence, 'rule-form', $evidence, ['exampleUrls' => $pagePaths]);
                continue;
            }
            if (empty($definition['eventName'])) {
                continue;
            }
            $candidates[] = $this->candidate($category, 'event_name', $definition['eventName'], 0.6, $prominence, 'rule-form', $evidence, [
                'needsSetup' => true,
                'exampleUrls' => array_slice($pagePaths, 0, 3),
            ]);
        }

        return $candidates;
    }

    /**
     * Classifies a form by input types; labels and paths only break ties.
     *
     * @param string[] $types
     * @param string[] $labels
     * @param string[] $pagePaths
     */
    private function classifyForm(array $types, array $labels, array $pagePaths): ?string
    {
        $counts = array_count_values($types);
        $has = function (string $type) use ($counts): bool {
            return !empty($counts[$type]);
        };
        $textual = ($counts['text'] ?? 0) + ($counts['email'] ?? 0) + ($counts['tel'] ?? 0) + ($counts['textarea'] ?? 0)
            + ($counts['select'] ?? 0) + ($counts['date'] ?? 0) + ($counts['number'] ?? 0) + ($counts['file'] ?? 0)
            + ($counts['url'] ?? 0);
        $words = strtolower(implode(' ', $labels) . ' ' . implode(' ', $pagePaths));

        if ($has('password')) {
            // one password next to one identifier is a login form, a repeated password or more fields a registration
            $isLogin = ($counts['password'] ?? 0) < 2
                && ($textual <= 1 || $this->containsAnyToken($words, self::LOGIN_WORDS));
            return $isLogin && !$this->containsAnyToken($words, self::CATEGORIES['signup']['tokens']) ? null : 'signup';
        }
        // an upload form is an application unless it sits in the help or support area (ticket forms)
        $belowHub = false;
        foreach ($pagePaths as $path) {
            $segments = $this->pathSegments($path);
            $belowHub = $belowHub || $this->isBelowMarketingHub(array_merge($segments, ['']));
        }
        if ($has('file') && ($has('email') || $has('tel')) && !$belowHub) {
            return 'apply';
        }
        if ($has('date') && !$has('textarea')) {
            return 'booking';
        }
        if (($has('number') || $has('range')) && ($counts['radio'] ?? 0) >= 2 && !$has('textarea') && !$has('email')) {
            return 'donate';
        }
        if ($has('textarea') && ($has('email') || $has('tel'))) {
            return $this->containsAnyToken($words, self::CATEGORIES['demo']['tokens']) ? 'demo' : 'contact';
        }
        // email plus at most a name (two text fields) and no message: a subscription form
        $other = $textual - ($counts['email'] ?? 0) - ($counts['select'] ?? 0) - min(2, $counts['text'] ?? 0);
        if ($has('email') && $other === 0 && !$has('textarea') && !$has('tel')) {
            return 'newsletter';
        }

        return null;
    }

    /**
     * The page whose path carries the category token, else the only page seen.
     *
     * @param string[] $pagePaths
     * @param string[] $tokens
     */
    private function formPage(array $pagePaths, array $tokens): ?string
    {
        foreach ($pagePaths as $path) {
            $segments = $this->pathSegments($path);
            if (!empty($segments) && $this->segmentMatches(end($segments), array_fill_keys($tokens, true))) {
                return $path;
            }
        }

        return count($pagePaths) === 1 && $pagePaths[0] !== '/' ? $pagePaths[0] : null;
    }

    /**
     * Outlinks to conversion platforms, sibling hosts, redirectors, branded services
     * and links whose label is a conversion verb.
     *
     * @param array<int, array<string, mixed>> $externalLinks
     * @return array<int, array<string, mixed>>
     */
    private function outlinkCandidates(array $externalLinks, string $siteDomain, int $pagesCrawled): array
    {
        $siteName = explode('.', $siteDomain)[0];
        $siteName = strlen($siteName) >= 4 ? $siteName : '';
        $candidates = [];

        foreach ($externalLinks as $link) {
            if (!is_array($link)) {
                continue;
            }
            $host = strtolower((string) ($link['host'] ?? ''));
            $hrefs = array_values(array_unique(array_filter(array_map('strval', array_merge([$link['href'] ?? ''], $link['examples'] ?? [])))));
            if ($host === '' || $this->isIgnoredHost($host)) {
                continue;
            }
            $platformCategory = null;
            foreach ($hrefs as $href) {
                $platformCategory = $this->categoryForConversionHost($host . rtrim((string) parse_url($href, PHP_URL_PATH), '/'));
                if ($platformCategory !== null) {
                    break;
                }
            }
            if ($platformCategory === null && $this->isGenericHost($host)) {
                continue;
            }
            $labels = array_values(array_filter(array_map('strval', $link['labels'] ?? [])));
            $pattern = $host;
            $category = null;
            $confidence = 0.0;
            $isSibling = $siteDomain !== '' && $this->registrableDomain($host) === $siteDomain;
            $subdomain = explode('.', $host)[0];

            if ($isSibling && in_array($subdomain, self::REDIRECTOR_SUBDOMAINS, true)) {
                // go.example.com/discord: the path names the destination, one link per destination
                foreach ($hrefs as $href) {
                    $destination = strtolower((string) ($this->pathSegments($href)[0] ?? ''));
                    if ($destination === '' || in_array($destination, self::IGNORED_DESTINATIONS, true)) {
                        continue;
                    }
                    $category = $this->categoryForToken($destination) ?? $this->categoryForConversionHost($destination . '.com');
                    if ($category !== null) {
                        $pattern = $host . '/' . $destination;
                        $confidence = 0.7;
                        break;
                    }
                }
            } elseif ($isSibling) {
                $category = self::SIBLING_SUBDOMAINS[$subdomain] ?? null;
                $confidence = 0.7;
            } else {
                $category = $platformCategory;
                $confidence = 0.85;
                if ($category === null && $siteName !== '' && strpos($subdomain, $siteName) !== false && substr_count($host, '.') >= 2) {
                    $category = 'partner';
                    $confidence = 0.6;
                }
            }

            $verbCategory = $this->categoryForLabels($labels);
            if ($category === null && $verbCategory !== null) {
                // label evidence alone: only short, repeated call-to-action labels
                if (count($link['sourcePages'] ?? []) < 2) {
                    continue;
                }
                $category = $verbCategory;
                $confidence = 0.45;
            } elseif ($category !== null && $verbCategory === $category) {
                $confidence = min(1.0, $confidence + 0.1);
            }
            if ($category === null) {
                continue;
            }

            $pageCount = count($link['sourcePages'] ?? []);
            $candidates[] = $this->candidate($category, 'external_website', $pattern, $confidence, min(1.0, $pageCount / $pagesCrawled) * 0.5 + (count($labels) > 0 ? 0.25 : 0), 'rule-external-link', array_filter([
                Piwik::translate('Goals_RecommendationEvidenceExternalLinks', [(string) ($link['count'] ?? 1), (string) $pageCount]),
                $labels[0] ?? '',
            ]), [
                'host' => $host,
                'allowMultiple' => true,
                'reasonKey' => $this->outlinkReasonKey($category, $isSibling),
                'implementationNote' => Piwik::translate('Goals_RecommendationOutlinkSetupNote', [$pattern]),
                'exampleUrls' => array_slice($link['examples'] ?? [], 0, 3),
            ]);
        }

        return $candidates;
    }

    /**
     * Product view goal from many product pages, plus an add-to-cart goal when no
     * shop platform and no cart page were found.
     *
     * @param array<int, array<string, mixed>> $links
     * @param array<int, array<string, mixed>> $pages
     * @return array<int, array<string, mixed>>
     */
    private function shopCandidates(array $links, array $pages, string $platform): array
    {
        $candidates = [];
        $productPages = 0;
        $addToCartPages = 0;
        foreach ($pages as $page) {
            if (array_intersect(self::PRODUCT_TYPES, $page['types'] ?? [])) {
                ++$productPages;
            }
            if (!empty($page['hasAddToCart'])) {
                ++$addToCartPages;
            }
        }

        $prefix = $this->productPathPrefix($links) ?? $this->commonPrefixOfProductPages($pages);
        if ($prefix !== null) {
            $productPages = max($productPages, $prefix['count']);
        }
        if ($productPages < self::MIN_PRODUCT_PAGES) {
            return $candidates;
        }

        if ($prefix !== null) {
            $candidates[] = $this->candidate('product', 'url', $prefix['pattern'], 0.7, 0.5, 'rule', [
                Piwik::translate('Goals_RecommendationEvidenceProductPaths', [(string) $productPages]),
            ], ['exampleUrls' => $prefix['examples']]);
        }

        $hasCartPath = false;
        foreach ($links as $link) {
            $segments = $this->pathSegments((string) ($link['linkTarget'] ?? ''));
            if (!empty($segments) && $this->segmentMatches(end($segments), array_fill_keys(self::CATEGORIES['cart']['tokens'], true))) {
                $hasCartPath = true;
                break;
            }
        }
        // JS rendered shops expose neither a cart link nor add-to-cart markup, a product catalogue is evidence enough
        if ($platform === '' && !$hasCartPath && ($addToCartPages > 0 || $prefix !== null)) {
            $candidates[] = $this->candidate('cart', 'event_name', self::CATEGORIES['cart']['eventName'], 0.6, 0.5, 'rule-form', [
                Piwik::translate('Goals_RecommendationEvidenceProductPaths', [(string) $productPages]),
            ], ['needsSetup' => true]);
        }

        return $candidates;
    }

    /**
     * Shared first segment of the crawled schema.org Product pages.
     *
     * @param array<int, array<string, mixed>> $pages
     * @return array{pattern: string, count: int, examples: string[]}|null
     */
    private function commonPrefixOfProductPages(array $pages): ?array
    {
        $counts = [];
        $examples = [];
        foreach ($pages as $page) {
            if (!array_intersect(self::PRODUCT_TYPES, $page['types'] ?? [])) {
                continue;
            }
            $segments = $this->pathSegments($this->pathFamily((string) ($page['path'] ?? '')));
            if (count($segments) < 2) {
                continue;
            }
            $prefix = '/' . $segments[0] . '/';
            $counts[$prefix] = ($counts[$prefix] ?? 0) + 1;
            $examples[$prefix][] = (string) $page['path'];
        }
        if (empty($counts)) {
            return null;
        }
        arsort($counts);
        $prefix = (string) array_key_first($counts);

        return $counts[$prefix] >= self::MIN_PRODUCT_PAGES
            ? ['pattern' => $prefix, 'count' => $counts[$prefix], 'examples' => array_slice($examples[$prefix], 0, 3)]
            : null;
    }

    /**
     * @param array<int, array<string, mixed>> $links
     * @return array{pattern: string, count: int, examples: string[]}|null
     */
    private function productPathPrefix(array $links): ?array
    {
        $counts = [];
        $examples = [];
        $productSegments = array_fill_keys(self::PRODUCT_SEGMENTS, true);
        foreach ($links as $link) {
            $segments = $this->pathSegments((string) ($link['linkTarget'] ?? ''));
            foreach ($segments as $index => $segment) {
                if (isset($productSegments[$segment]) && isset($segments[$index + 1])) {
                    $prefix = '/' . $segment . '/';
                    $counts[$prefix] = ($counts[$prefix] ?? 0) + 1;
                    $examples[$prefix][] = (string) $link['linkTarget'];
                    break;
                }
            }
        }
        if (empty($counts)) {
            return null;
        }
        arsort($counts);
        $prefix = (string) array_key_first($counts);

        return ['pattern' => $prefix, 'count' => $counts[$prefix], 'examples' => array_slice($examples[$prefix], 0, 3)];
    }

    /**
     * One goal for the most common document extension of the site, or a single file
     * when it looks like a sales asset.
     *
     * @param array<int, array<string, mixed>> $downloads
     * @return array<int, array<string, mixed>>
     */
    private function downloadCandidates(array $downloads, string $siteDomain): array
    {
        $byExtension = [];
        foreach ($downloads as $download) {
            if (!is_array($download)) {
                continue;
            }
            $href = (string) ($download['href'] ?? '');
            $path = (string) parse_url($href, PHP_URL_PATH);
            $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $basename = strtolower(basename($path));
            if (
                !in_array($extension, self::DOCUMENT_EXTENSIONS, true)
                || $this->registrableDomain((string) parse_url($href, PHP_URL_HOST)) !== $siteDomain
                || $this->containsToken($basename, self::EXCLUDED_TOKENS)
            ) {
                continue;
            }
            $byExtension[$extension][] = $download;
        }
        if (empty($byExtension)) {
            return [];
        }
        uasort($byExtension, function (array $a, array $b): int {
            return count($b) <=> count($a);
        });
        $extension = (string) array_key_first($byExtension);
        $files = $byExtension[$extension];
        $sourcePages = array_slice($files[0]['sourcePages'] ?? [], 0, 3);

        if (count($files) >= self::MIN_FILES_FOR_AGGREGATE) {
            return [$this->candidate('download', 'file', '.' . $extension, 0.7, min(1.0, count($files) / 10), 'rule-download', [
                Piwik::translate('Goals_RecommendationEvidenceDistinctFiles', [(string) count($files), strtoupper($extension)]),
            ], [
                'name' => Piwik::translate('Goals_RecommendationDownloadTypeName', [strtoupper($extension)]),
                'allowMultiple' => true,
                'implementationNote' => Piwik::translate('Goals_RecommendationDownloadSetupNote', ['.' . $extension]),
                'exampleUrls' => array_slice(array_column($files, 'href'), 0, 3),
                'sourcePages' => $sourcePages,
            ])];
        }

        $file = $files[0];
        $basename = basename((string) parse_url((string) $file['href'], PHP_URL_PATH));
        $labelText = (string) ($file['labels'][0] ?? '');
        if (!$this->containsToken(strtolower($basename . ' ' . $labelText), self::ASSET_TOKENS)) {
            return [];
        }

        return [$this->candidate('download', 'file', $basename, 0.6, 0.3, 'rule-download', [
            Piwik::translate('Goals_RecommendationEvidenceDownloadSightings', [(string) ($file['count'] ?? 1), (string) count($file['sourcePages'] ?? [])]),
        ], [
            'name' => Piwik::translate('Goals_RecommendationDownloadFileName', [$this->titleFromText($labelText ?: $basename)]),
            'allowMultiple' => true,
            'implementationNote' => Piwik::translate('Goals_RecommendationDownloadSetupNote', [$basename]),
            'exampleUrls' => [$file['href']],
            'sourcePages' => $sourcePages,
        ])];
    }

    /**
     * Evidence strength: 3 for a token in the last segment, 2 or 1 in an earlier one,
     * plus 1 from the link label. Below marketing hubs only whole segments count.
     *
     * @param string[] $segments
     * @param array<string, mixed> $link
     * @param string[] $tokens
     */
    private function matchStrength(array $segments, array $link, array $tokens): int
    {
        if (empty($tokens)) {
            return 0;
        }
        $lookup = array_fill_keys($tokens, true);
        $lastSegment = $segments[count($segments) - 1];
        if ($this->isBelowMarketingHub($segments)) {
            return isset($lookup[$lastSegment]) || isset($lookup['=' . $lastSegment]) ? 3 : 0;
        }

        $strength = 0;
        if ($this->segmentMatches($lastSegment, $lookup)) {
            $strength = 3;
        } else {
            foreach (array_slice($segments, 0, -1) as $segment) {
                if ($this->segmentMatches($segment, $lookup)) {
                    $strength = count($segments) <= 2 ? 2 : 1;
                    break;
                }
            }
        }

        if ($this->textMatches($link, $lookup)) {
            if ($strength > 0) {
                ++$strength;
            } elseif ($this->isCallToAction($link)) {
                // opaque paths (/node/123) qualify through a repeated short CTA label
                $strength = 2;
            }
        }

        return $strength;
    }

    /**
     * @param string[] $segments
     */
    private function isBelowMarketingHub(array $segments): bool
    {
        $hubs = array_fill_keys(self::MARKETING_HUB_SEGMENTS, true);
        foreach (array_slice($segments, 0, -1) as $segment) {
            if (isset($hubs[trim($segment, '-_')])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, true> $lookup
     */
    private function segmentMatches(string $segment, array $lookup): bool
    {
        $segment = strtolower($segment);
        if (isset($lookup[$segment]) || isset($lookup['=' . $segment])) {
            return true;
        }
        $parts = preg_split('/[-_.]+/', $segment) ?: [];
        // long dashed segments are article slugs ("integrating-the-api-with-contact-form-7")
        if (count($parts) > self::MAX_SLUG_PARTS) {
            return false;
        }
        foreach ($parts as $part) {
            if ($part !== '' && isset($lookup[$part])) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $link
     * @param array<string, true> $lookup
     */
    private function textMatches(array $link, array $lookup): bool
    {
        foreach ($this->labels($link) as $label) {
            foreach ($this->words($label) as $word) {
                if (isset($lookup[$word])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * A button styled link repeated across pages with a short label.
     *
     * @param array<string, mixed> $link
     */
    private function isCallToAction(array $link): bool
    {
        if ((int) ($link['buttonLikeCount'] ?? 0) < 1 || (int) ($link['pageCount'] ?? 0) < 2) {
            return false;
        }
        foreach ($this->labels($link) as $label) {
            if (str_word_count($label) <= 4) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<string, mixed> $link
     * @return string[]
     */
    private function labels(array $link): array
    {
        $labels = $link['labelSamples'] ?? [$link['linkText'] ?? ''];

        return array_values(array_filter(array_map('strval', (array) $labels)));
    }

    /**
     * Visibility on the site: share of linking pages, button styling, hero placement.
     *
     * @param array<string, mixed> $link
     */
    private function prominence(array $link, int $pagesCrawled): float
    {
        return min(1.0, (int) ($link['pageCount'] ?? 0) / $pagesCrawled) * 0.4
            + ((int) ($link['buttonLikeCount'] ?? 0) > 0 ? 0.3 : 0)
            + ((int) ($link['heroCount'] ?? 0) > 0 ? 0.15 : 0);
    }

    /**
     * @param string[] $labels
     */
    private function categoryForLabels(array $labels): ?string
    {
        foreach ($labels as $label) {
            if (str_word_count($label) > 5) {
                continue;
            }
            $words = array_fill_keys($this->words($label), true);
            foreach (self::CATEGORIES as $category => $definition) {
                foreach ($definition['verbs'] as $verb) {
                    if (isset($words[$verb])) {
                        return $category;
                    }
                }
            }
        }

        return null;
    }

    private function categoryForToken(string $token): ?string
    {
        foreach (self::CATEGORIES as $category => $definition) {
            foreach (array_merge($definition['tokens'], $definition['verbs']) as $candidate) {
                if (ltrim($candidate, '=') === $token) {
                    return $category;
                }
            }
        }

        return null;
    }

    /**
     * @param string $hostPath host plus path, e.g. "www.paypal.com/donate/campaign"
     */
    private function categoryForConversionHost(string $hostPath): ?string
    {
        $hostPath = (string) preg_replace('/^www\./', '', $hostPath);
        foreach (self::CONVERSION_HOSTS as $conversionHost => $category) {
            if (
                $hostPath === $conversionHost
                || strpos($hostPath, $conversionHost . '/') === 0
                || substr($hostPath, -strlen('.' . $conversionHost)) === '.' . $conversionHost
                || strpos($hostPath, '.' . $conversionHost . '/') !== false
            ) {
                return $category;
            }
        }

        return null;
    }

    /** Sponsor and community reasons describe a click already, others need handoff wording. */
    private function outlinkReasonKey(string $category, bool $isSibling): string
    {
        if (in_array($category, ['sponsor', 'community'], true)) {
            return '';
        }

        return $isSibling ? 'Goals_RecommendationOutlinkSiblingReason' : 'Goals_RecommendationOutlinkPlatformReason';
    }

    /** Social networks and reference sites are never conversions. */
    private function isIgnoredHost(string $host): bool
    {
        if (preg_match('/^(mailto|tel|javascript):/', $host)) {
            return true;
        }
        $labels = explode('.', (string) preg_replace('/^www\./', '', $host));
        $name = $labels[count($labels) >= 2 ? count($labels) - 2 : 0];

        return in_array($name, self::IGNORED_DESTINATIONS, true)
            || in_array($name, ['wikipedia', 'mozilla', 'w3', 'vimeo', 'flickr', 'medium'], true);
    }

    /**
     * Big platforms whose links mean nothing unless the path is a known conversion
     * destination (app stores, plugin directories).
     */
    private function isGenericHost(string $host): bool
    {
        $labels = explode('.', (string) preg_replace('/^www\./', '', $host));
        $name = $labels[count($labels) >= 2 ? count($labels) - 2 : 0];

        return in_array($name, ['google', 'microsoft', 'apple', 'amazon', 'wordpress', 'adobe', 'cloudflare'], true);
    }

    /**
     * "example.co.uk" for "shop.example.co.uk"; good enough to spot sibling hosts.
     */
    private function registrableDomain(string $host): string
    {
        $labels = explode('.', strtolower(trim($host)));
        $count = count($labels);
        if ($count < 2) {
            return '';
        }
        $secondLevel = ['co', 'com', 'org', 'net', 'ac', 'gov', 'edu'];
        $keep = ($count >= 3 && in_array($labels[$count - 2], $secondLevel, true) && strlen($labels[$count - 1]) === 2) ? 3 : 2;

        return implode('.', array_slice($labels, -$keep));
    }

    /**
     * @return string[]
     */
    private function pathSegments(string $path): array
    {
        $path = strtolower((string) parse_url($path, PHP_URL_PATH));

        return array_values(array_filter(explode('/', $path), function (string $segment): bool {
            return $segment !== '';
        }));
    }

    /**
     * @param string[] $segments
     */
    private function isExcludedPath(array $segments): bool
    {
        $excluded = array_fill_keys(self::EXCLUDED_SEGMENTS, true);
        $excludedTokens = array_fill_keys(self::EXCLUDED_TOKENS, true);
        foreach ($segments as $segment) {
            // dated archive paths (/2026/07/post) are content
            if (isset($excluded[$segment]) || preg_match('/^(19|20)\d\d$/', $segment)) {
                return true;
            }
            foreach (preg_split('/[-_.]+/', $segment) ?: [] as $part) {
                if (isset($excludedTokens[$part])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Blog, news and dated paths: never a conversion, not even an activation page.
     *
     * @param string[] $segments
     */
    private function isContentPath(array $segments): bool
    {
        foreach ($segments as $segment) {
            if (in_array($segment, ['blog', 'news', 'aktuelles', 'actualites', 'noticias', 'notizie', 'nieuws'], true) || preg_match('/^(19|20)\d\d$/', $segment)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string[] $segments
     */
    private function isFilePath(array $segments): bool
    {
        return preg_match('/\.(png|jpe?g|gif|svg|webp|mp4|mp3|css|js|xml|json|txt|pdf|docx?|xlsx?|pptx?|zip|csv)$/', end($segments)) === 1;
    }

    /**
     * Whether the last path segment is itself a category token (/contact-sales, /kasse).
     *
     * @param string[] $tokens
     */
    private function isExactCategoryPage(string $pattern, array $tokens): bool
    {
        $segments = $this->pathSegments($pattern);
        if (empty($segments)) {
            return false;
        }
        $last = end($segments);
        foreach ($tokens as $token) {
            if (ltrim($token, '=') === $last) {
                return true;
            }
        }

        return false;
    }

    /**
     * Short link label, or a readable form of the last path segment.
     *
     * @param array<string, mixed> $link
     */
    private function pageLabel(array $link, string $pattern): string
    {
        foreach ($this->labels($link) as $label) {
            $label = trim((string) preg_split('/\s*\/\s*/', $label)[0]);
            if ($label !== '' && str_word_count($label) <= 6) {
                return $this->truncate($label, 40);
            }
        }
        $segments = $this->pathSegments($pattern);

        return $this->truncate($this->titleFromText((string) end($segments)), 40);
    }

    /**
     * @return string[]
     */
    private function words(string $text): array
    {
        return array_values(array_filter(preg_split('/[^\p{L}\p{N}]+/u', strtolower($text)) ?: []));
    }

    /**
     * Whole-word token check on a lowercased string.
     *
     * @param string[] $tokens
     */
    private function containsToken(string $haystack, array $tokens): bool
    {
        $words = array_fill_keys($this->words($haystack), true);
        foreach ($tokens as $token) {
            if (isset($words[ltrim($token, '=')])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Token check that also splits dashed tokens ("sign-up" matches "sign up").
     *
     * @param string[] $tokens
     */
    private function containsAnyToken(string $haystack, array $tokens): bool
    {
        $normalized = ' ' . implode(' ', $this->words($haystack)) . ' ';
        foreach ($tokens as $token) {
            $token = str_replace(['-', '_'], ' ', ltrim($token, '='));
            if (strpos($normalized, ' ' . $token . ' ') !== false) {
                return true;
            }
        }

        return false;
    }

    private function titleFromText(string $value): string
    {
        $cleaned = preg_replace('/[-_]+/', ' ', $value);
        $cleaned = preg_replace('/\.[a-z0-9]+$/i', '', (string) $cleaned);
        $parts = array_slice(array_filter(explode(' ', trim((string) $cleaned))), 0, 6);

        return implode(' ', array_map(function (string $part): string {
            return ucfirst(strtolower($part));
        }, $parts));
    }

    private function truncate(string $value, int $maxLength): string
    {
        return function_exists('mb_substr') ? mb_substr($value, 0, $maxLength) : substr($value, 0, $maxLength);
    }

    /**
     * @param string[] $evidence
     * @param array<string, mixed> $extra
     * @return array<string, mixed>
     */
    private function candidate(string $category, string $matchAttribute, string $pattern, float $confidence, float $prominence, string $source, array $evidence, array $extra = []): array
    {
        return array_merge([
            'category' => $category,
            'matchAttribute' => $matchAttribute,
            'pattern' => $pattern,
            'confidence' => $confidence,
            'prominence' => min(1.0, $prominence),
            'source' => $source,
            'evidence' => array_values(array_filter($evidence)),
            'needsSetup' => false,
            'reasonKey' => '',
            'label' => '',
            'exampleUrls' => [],
            'sourcePages' => [],
        ], $extra);
    }

    /**
     * @param array<string, mixed> $candidate
     * @return array<string, mixed>
     */
    private function buildGoal(array $candidate): array
    {
        $definition = self::CATEGORIES[$candidate['category']];
        $matchAttribute = (string) $candidate['matchAttribute'];
        $isEvent = strpos($matchAttribute, 'event_') === 0;
        $isRepeatable = $isEvent || in_array($matchAttribute, ['file', 'external_website'], true);

        if (!empty($candidate['name'])) {
            $name = (string) $candidate['name'];
        } elseif ($isEvent && !empty($definition['eventNameKey'])) {
            $name = Piwik::translate($definition['eventNameKey']);
        } elseif ($matchAttribute === 'external_website') {
            $name = Piwik::translate($definition['nameKey'], [(string) ($candidate['host'] ?? $candidate['pattern'])]);
        } elseif ($candidate['label'] === '' && !empty($definition['pageNameKey'])) {
            $name = Piwik::translate($definition['pageNameKey']);
        } elseif ($candidate['label'] !== '') {
            // the page only mentions the category, so name the page rather than the category
            $name = Piwik::translate('Goals_RecommendationKeyPageName', [$candidate['label']]);
        } else {
            $name = Piwik::translate($definition['nameKey']);
        }

        $reason = Piwik::translate(
            !empty($candidate['reasonKey']) ? (string) $candidate['reasonKey'] : $definition['reasonKey']
        );

        if (!empty($candidate['implementationNote'])) {
            $implementationNote = (string) $candidate['implementationNote'];
        } elseif ($isEvent) {
            $implementationNote = Piwik::translate('Goals_RecommendationEventSetupNote', [$candidate['pattern']]);
        } else {
            $implementationNote = Piwik::translate('Goals_RecommendationDefaultSetupNote');
        }

        return [
            'name' => $name,
            'category' => $candidate['category'],
            'matchAttribute' => $matchAttribute,
            'pattern' => (string) $candidate['pattern'],
            'patternType' => 'contains',
            'caseSensitive' => false,
            'allowMultipleConversionsPerVisit' => !empty($candidate['allowMultiple']) || $isRepeatable,
            'revenue' => 0,
            'useEventValueAsRevenue' => false,
            'reason' => $reason,
            'description' => $reason,
            'source' => (string) $candidate['source'],
            'needsSetup' => !empty($candidate['needsSetup']),
            'implementationNote' => $implementationNote,
            'evidence' => array_values($candidate['evidence']),
            'sourcePages' => array_values($candidate['sourcePages'] ?: $candidate['exampleUrls']),
            'exampleMatches' => array_values($candidate['exampleUrls'] ?: [$candidate['pattern']]),
        ];
    }
}
