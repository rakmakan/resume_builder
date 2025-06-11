<?php
require_once __DIR__ . '/database/config.php';

// Get content for different sections
$hero_content = get_section_content('hero');
$services = get_services();
$testimonials = get_testimonials();
$featured_cases = get_case_studies(3);
$clients = get_clients();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portfolio</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- AOS CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
    
    <!-- Inline styles for What Working Together Looks Like Section -->
    <style>
        /* What Working Together Looks Like Section */
        .collaboration-section {
            background-color: var(--bg-color);
        }

        .collaboration-intro,
        .collaboration-scenarios {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .collaboration-intro:hover,
        .collaboration-scenarios:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .outcome-item {
            position: relative;
        }

        .outcome-number {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            color: white;
            font-weight: 700;
            margin-right: 15px;
            flex-shrink: 0;
        }

        .outcome-content h4 {
            margin-bottom: 0;
            font-size: 1.1rem;
            font-weight: 600;
        }

        .scenario-card {
            padding: 15px 0;
            position: relative;
        }

        .scenario-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: rgba(0, 102, 204, 0.1);
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .scenario-icon i {
            font-size: 1.5rem;
        }

        .scenario-divider {
            height: 1px;
            background: linear-gradient(90deg, var(--primary-color) 0%, transparent 100%);
            margin-top: 15px;
        }

        .collaboration-conclusion {
            font-style: italic;
            color: var(--primary-color);
            font-weight: 500;
        }
        
        /* Extra Gap section styles for consistent alignment */
        .canvas-container {
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }
        
        @media (max-width: 767px) {
            .canvas-container {
                justify-content: center;
                margin-top: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 order-lg-1 order-2">
                    <div class="hero-content">
                        <h1 class="hero-heading"><?php echo htmlspecialchars($hero_content['title'] ?? 'Get eyes on your brand and keep them there.'); ?></h1>
                        <p class="hero-description"><?php echo htmlspecialchars($hero_content['description'] ?? 'Brand strategy, Messaging & Copy that turn attention into trust.'); ?></p>
                        
                        <div class="hero-cta-group">
                            <a href="#contact" class="btn btn-primary btn-hero-primary">Start a Project</a>
                            <a href="views/case-studies.php" class="btn btn-outline-dark btn-hero-primary">View Case Studies</a>
                        </div>

                        <div class="hero-stats">
                            <?php 
                            $stats = get_homepage_stats();
                            if (empty($stats)): 
                                $default_stats = [
                                    ['value' => '7+', 'label' => 'Years Experience'],
                                    ['value' => '20+', 'label' => 'Projects Completed'],
                                    ['value' => '8+', 'label' => 'Brands']
                                ];
                                foreach ($default_stats as $stat):
                            ?>
                                <div class="hero-stat">
                                    <div class="hero-stat-number"><?php echo $stat['value']; ?></div>
                                    <div class="hero-stat-label"><?php echo $stat['label']; ?></div>
                                </div>
                            <?php 
                                endforeach;
                            else:
                                foreach ($stats as $stat):
                            ?>
                                <div class="hero-stat">
                                    <div class="hero-stat-number"><?php echo htmlspecialchars($stat['value']); ?></div>
                                    <div class="hero-stat-label"><?php echo htmlspecialchars($stat['label']); ?></div>
                                </div>
                            <?php 
                                endforeach;
                            endif;
                            ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 order-lg-2 order-1">
                    <div class="hero-image-container">
                        <div class="orbital-background"></div>
                        <div class="orbital-container">
                            <div class="metallic-ball"></div>
                            
                            <div class="orbit orbit-1">
                                <div class="orbital-tag" data-label="Brand Strategy">
                                    <i class="fas fa-pen-fancy"></i>
                                </div>
                            </div>
                            
                            <div class="orbit orbit-2">
                                <div class="orbital-tag" data-label="AI Solutions">
                                    <i class="fas fa-robot"></i>
                                </div>
                            </div>
                            
                            <div class="orbit orbit-3">
                                <div class="orbital-tag" data-label="Market Research">
                                    <i class="fas fa-bullseye"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Removed duplicate The Gap Section -->

    <!-- About Section -->
    <section id="about" class="about-section section-padding bg-light">
        <div class="container">
            <h2 class="section-title">About Me</h2>
            <div class="row">
                <div class="col-lg-6">
                    <img src="assets/images/about-image.png" alt="About Image" class="img-fluid rounded shadow hover-color-image">
                </div>
                <div class="col-lg-6">
                    <h3 class="mb-4">Where strategy meets storytelling.</h3>
                    <p class="lead mb-4">
                        I help brands find their voice, define their positioning, and communicate with clarity across every touchpoint — from pitch decks to product pages.
                    </p>
                    <p class="lead mb-4">
                        My work lives at the intersection of strategy and storytelling. I partner with founders, marketing teams, and creative leads to turn scattered ideas into sharp narratives — the kind that actually land with the people you're trying to reach.
                    </p>
                    <p class="lead mb-4">
                        I treat your brand like my own. That means I care where every word lands.
                    </p>
                    <p class="lead mb-4">
                        Through audience research, competitive analysis, and structured messaging systems, I help brands communicate with purpose — not just polish.
                    </p>
                    <p class="lead mb-4">
                        If you're building something worth paying attention to, I'll help you say the thing that makes people stop and listen.
                    </p>
                    <p class="mb-4">With a foundation in English Literature and specialized training in Marketing & Brand Direction, I bring both the art and science of communication to every project.</p>
                    <div class="d-flex align-items-center mb-4">
                        <div class="experience-highlight me-4">
                            <h4 class="mb-0">7+</h4>
                            <p class="mb-0">Years Experience</p>
                        </div>
                        <div class="experience-highlight">
                            <h4 class="mb-0">20+</h4>
                            <p class="mb-0">Projects Completed</p>
                        </div>
                    </div>
                    <a href="views/about.php" class="btn btn-primary">Learn More About Me</a>
                </div>
            </div>
        </div>
    </section>

    <!-- The Gap Section -->
    <section id="the-gap" class="the-gap-section section-padding bg-gray-50">
        <div class="container">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
                <!-- Left: Text Column -->
                <div class="text-left max-w-prose space-y-6">
                    <h2 class="section-title text-left" data-aos="fade-up">The Gap</h2>
                    
                    <div class="intro-paragraph" data-aos="fade-up" data-aos-delay="100">
                        <p class="intro-text">Most brands have something valuable to offer.</p>
                        <p class="intro-text">But they struggle to explain it in a way that sticks.</p>
                    </div>
                    
                    <div class="problem-list space-y-3">
                        <div class="problem-item fade-in">
                            <p class="problem-text">Messaging feels scattered.</p>
                        </div>
                        <div class="problem-item fade-in">
                            <p class="problem-text">Voice shifts from channel to channel.</p>
                        </div>
                        <div class="problem-item fade-in">
                            <p class="problem-text">Their difference gets buried under buzzwords or borrowed lines.</p>
                        </div>
                    </div>
                    
                    <div class="highlight-block mb-5 fade-in">
                        <p class="highlight-text mb-3">I help brands get clear on what makes them matter.</p>
                        <p class="supporting-text">I shape messaging that's focused, consistent, and easy to rally around — inside and out.</p>
                    </div>
                    
                    <div class="emphasis-block fade-in">
                        <p class="emphasis-text">For founders, marketers, and creative leads at early-stage to growth-stage brands who want to sound as sharp as they think.</p>
                    </div>
                </div>
                
                <!-- Right: Canvas Animation -->
                <div class="flex justify-end canvas-container">
                    <div class="canvas-wrapper">
                        <canvas id="scatterCanvas" width="400" height="400" class="w-full max-w-md"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section id="services" class="services-section section-padding">
        <div class="container">
            <h2 class="section-title">My Services</h2>
            <div class="row g-4">
                <?php if (empty($services)): 
                    $default_services = [
                        [
                            'icon' => 'fas fa-bullseye',
                            'title' => 'Market Positioning',
                            'description' => 'Most brands sound the same. I help you say something different — and mean it. Together, we\'ll figure out who you\'re for, what makes you worth caring about, and how to claim a space no one else owns.'
                        ],
                        [
                            'icon' => 'fas fa-pen-fancy',
                            'title' => 'Brand Copy',
                            'description' => 'Words make people feel things. Or scroll past. I write the kind that make them feel, act, and remember. Websites, taglines, decks, bios — if it needs a voice, I give it yours (but sharper).'
                        ],
                        [
                            'icon' => 'fas fa-lightbulb',
                            'title' => 'Consulting',
                            'description' => 'Need a marketer without hiring one full-time? I plug into your team, review what\'s working, scrap what\'s not, and help you market smarter — not just louder.'
                        ]
                    ];
                    foreach ($default_services as $service):
                ?>
                    <div class="col-md-4">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="<?php echo $service['icon']; ?>"></i>
                            </div>
                            <h4><?php echo $service['title']; ?></h4>
                            <p><?php echo $service['description']; ?></p>
                        </div>
                    </div>
                <?php 
                    endforeach;
                else:
                    foreach ($services as $service):
                ?>
                    <div class="col-md-4">
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="<?php echo htmlspecialchars($service['icon']); ?>"></i>
                            </div>
                            <h4><?php echo htmlspecialchars($service['title']); ?></h4>
                            <p><?php echo htmlspecialchars($service['description']); ?></p>
                        </div>
                    </div>
                <?php 
                    endforeach;
                endif;
                ?>
            </div>
        </div>
    </section>

    <!-- How I Work Section -->
    <section id="how-i-work" class="how-i-work-section section-padding">
        <div class="container">
            <h2 class="section-title">How I Work</h2>
            <div class="row g-4">
                <!-- Step 1: LISTEN -->
                <div class="col-md-4">
                    <div class="process-step">
                        <div class="step-number">1</div>
                        <div class="step-icon-circle">
                            <i class="fas fa-comments"></i>
                        </div>
                        <h3 class="step-title">LISTEN</h3>
                        <p class="step-description">We start with interviews, surveys, and a little (friendly) stalking to understand what your audience really thinks and wants.</p>
                        <ul class="step-tags">
                            <li>Voice-of-customer research</li>
                            <li>Stakeholder interviews</li>
                            <li>Message audit (AI-supported)</li>
                        </ul>
                        <div class="step-connector"></div>
                    </div>
                </div>

                <!-- Step 2: FRAME -->
                <div class="col-md-4">
                    <div class="process-step">
                        <div class="step-number">2</div>
                        <div class="step-icon-circle">
                            <i class="fas fa-puzzle-piece"></i>
                        </div>
                        <h3 class="step-title">FRAME</h3>
                        <p class="step-description">We shape your message, tone, and position with a clear POV. Strategy before copy.</p>
                        <ul class="step-tags">
                            <li>Positioning + personality</li>
                            <li>Messaging framework</li>
                            <li>Voice & tone system</li>
                        </ul>
                        <div class="step-connector"></div>
                    </div>
                </div>

                <!-- Step 3: WRITE -->
                <div class="col-md-4">
                    <div class="process-step">
                        <div class="step-number">3</div>
                        <div class="step-icon-circle">
                            <i class="fas fa-pen-nib"></i>
                        </div>
                        <h3 class="step-title">WRITE</h3>
                        <p class="step-description">Words that sound human — and sell. AI helps move faster, not sound robotic.</p>
                        <ul class="step-tags">
                            <li>Website copy</li>
                            <li>Email flows</li>
                            <li>Sales decks & social messaging</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- What Working Together Looks Like Section -->
    <section id="collaboration" class="collaboration-section section-padding">
        <div class="container">
            <h2 class="section-title">What Working Together Looks Like</h2>
            
            <div class="row mb-5">
                <div class="col-md-8 mx-auto text-center">
                    <p class="lead mb-2">Your brand speaks clearly.</p>
                    <p class="lead mb-2">Your team knows what to say — and how to say it.</p>
                    <p class="lead mb-4">Your audience doesn't just understand you — they remember you.</p>
                </div>
            </div>
            
            <div class="row mb-5">
                <div class="col-lg-6">
                    <div class="collaboration-intro p-4 rounded shadow-sm h-100">
                        <h3 class="mb-4">After we work together, you'll walk away with:</h3>
                        <div class="collaboration-outcome-list">
                            <div class="outcome-item d-flex align-items-start mb-4">
                                <div class="outcome-number">1</div>
                                <div class="outcome-content">
                                    <h4>A clear brand position that sets you apart</h4>
                                </div>
                            </div>
                            <div class="outcome-item d-flex align-items-start mb-4">
                                <div class="outcome-number">2</div>
                                <div class="outcome-content">
                                    <h4>A messaging system your team can actually use</h4>
                                </div>
                            </div>
                            <div class="outcome-item d-flex align-items-start mb-4">
                                <div class="outcome-number">3</div>
                                <div class="outcome-content">
                                    <h4>Copy that sounds like you — and converts like it should</h4>
                                </div>
                            </div>
                            <div class="outcome-item d-flex align-items-start">
                                <div class="outcome-number">4</div>
                                <div class="outcome-content">
                                    <h4>Confidence in how you show up, online and off</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="collaboration-scenarios p-4 rounded shadow-sm h-100">
                        <div class="scenario-card mb-4">
                            <div class="scenario-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h4>Pitching Investors</h4>
                            <div class="scenario-divider"></div>
                        </div>
                        <div class="scenario-card mb-4">
                            <div class="scenario-icon">
                                <i class="fas fa-bullhorn"></i>
                            </div>
                            <h4>Launching Campaigns</h4>
                            <div class="scenario-divider"></div>
                        </div>
                        <div class="scenario-card">
                            <div class="scenario-icon">
                                <i class="fas fa-laptop"></i>
                            </div>
                            <h4>Updating Your Site</h4>
                            <div class="scenario-divider"></div>
                        </div>
                        <p class="mt-4 collaboration-conclusion">Whether you're pitching investors, launching a campaign, or updating your site, your message will be sharp, strategic, and unmistakably yours.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Case Studies Section -->
    <section id="case-studies" class="case-studies-section section-padding bg-light">
        <div class="container">
            <h2 class="section-title">Case Studies</h2>
            <div class="case-study-slider">
                <?php
                // Full server path to the config file to avoid path issues on SiteGround
                require_once __DIR__ . '/database/config.php';
                $featured_cases = get_case_studies(3);
                ?>
                <div id="caseStudyCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        <?php foreach ($featured_cases as $index => $case): ?>
                        <button type="button" data-bs-target="#caseStudyCarousel" data-bs-slide-to="<?php echo $index; ?>" 
                                <?php echo $index === 0 ? 'class="active"' : ''; ?>></button>
                        <?php endforeach; ?>
                    </div>
                    <div class="carousel-inner">
                        <?php foreach ($featured_cases as $index => $case): ?>
                        <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                            <div class="case-study-card">
                                <div class="row align-items-center">
                                    <div class="col-lg-6">
                                        <div class="image-text-container">
                                            <img src="<?php echo htmlspecialchars($case['featured_image']); ?>" 
                                                 class="img-fluid hover-color-image" alt="<?php echo htmlspecialchars($case['title']); ?>">
                                            <div class="overlay-text">
                                                <span class="badge bg-light text-dark"><?php echo htmlspecialchars($case['category'] ?? 'Case Study'); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <h3><?php echo htmlspecialchars($case['title']); ?></h3>
                                        <p class="lead"><?php echo htmlspecialchars($case['summary']); ?></p>
                                        <a href="views/case-study.php?slug=<?php echo urlencode($case['slug']); ?>" 
                                           class="btn btn-primary">View Case Study</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#caseStudyCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon"></span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#caseStudyCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon"></span>
                    </button>
                </div>
                <div class="text-center mt-5">
                    <a href="views/case-studies.php" class="btn btn-outline-primary">View All Case Studies</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Client Review Section -->
    <section id="reviews" class="reviews-section section-padding">
        <div class="container">
            <h2 class="section-title">Client Reviews</h2>
            <div class="reviews-container">
                <div class="review-track">
                    <?php if (empty($testimonials)): 
                        $default_testimonials = [
                            [
                                'client_name' => 'Karan Sharma',
                                'client_position' => 'Founder, Hubsell',
                                'company_logo' => 'hubsell-logo.svg',
                                'rating' => 5,
                                'review_text' => 'I highly recommend Riya to anybody needing help in marketing, corporate communications and PR. She is a self-starter with commendable storytelling, analytical and writing skills, all of which make her an excellent marketer.'
                            ],
                            // ... other default testimonials
                        ];
                        
                        // Print testimonials twice for seamless loop
                        for ($i = 0; $i < 2; $i++):
                            foreach ($default_testimonials as $testimonial):
                    ?>
                            <div class="review-card">
                                <div class="review-profile">
                                    <div class="image-text-container">
                                        <img src="assets/images/<?php echo $testimonial['company_logo']; ?>" 
                                             alt="<?php echo htmlspecialchars($testimonial['client_position']); ?>" 
                                             class="company-logo hover-color-image">
                                    </div>
                                    <div class="rating">
                                        <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                                            <i class="fas fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="review-text"><?php echo htmlspecialchars($testimonial['review_text']); ?></p>
                                <h4 class="client-name"><?php echo htmlspecialchars($testimonial['client_name']); ?></h4>
                                <p class="client-position"><?php echo htmlspecialchars($testimonial['client_position']); ?></p>
                            </div>
                    <?php 
                            endforeach;
                        endfor;
                    else:
                        // Print dynamic testimonials twice for seamless loop
                        for ($i = 0; $i < 2; $i++):
                            foreach ($testimonials as $testimonial):
                    ?>
                            <div class="review-card">
                                <div class="review-profile">
                                    <div class="image-text-container">
                                        <img src="<?php echo htmlspecialchars($testimonial['image_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($testimonial['client_name']); ?>" 
                                             class="company-logo hover-color-image">
                                    </div>
                                    <div class="rating">
                                        <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                                            <i class="fas fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <p class="review-text"><?php echo htmlspecialchars($testimonial['review_text']); ?></p>
                                <h4 class="client-name"><?php echo htmlspecialchars($testimonial['client_name']); ?></h4>
                                <p class="client-position"><?php echo htmlspecialchars($testimonial['client_position']); ?></p>
                            </div>
                    <?php 
                            endforeach;
                        endfor;
                    endif;
                    ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Client Logos Section -->
    <section id="clients" class="clients-section section-padding bg-light">
        <div class="container-fluid">
            <div class="container">
                <h2 class="section-title">Trusted By</h2>
            </div>
            <div class="client-logos-container">
                <div class="client-logos-track">
                    <?php if (empty($clients)): 
                        $default_clients = [
                            ['company_name' => 'IDEMIA', 'logo_url' => 'idemia-logo.svg'],
                            ['company_name' => 'Pari Match', 'logo_url' => 'parimatch-logo.svg'],
                            ['company_name' => 'OpenSense Labs', 'logo_url' => 'opensense-logo.svg'],
                            ['company_name' => 'Hubsell', 'logo_url' => 'hubsell-logo.svg'],
                            ['company_name' => 'Cladiator', 'logo_url' => 'cladiator-logo.svg'],
                            ['company_name' => 'Gripphy', 'logo_url' => 'gripphy-logo.svg']
                        ];
                        
                        // Print logos twice for seamless loop
                        for ($i = 0; $i < 2; $i++):
                            foreach ($default_clients as $client):
                    ?>
                            <div class="client-logo-box">
                                <div class="image-text-container">
                                    <img src="assets/images/<?php echo $client['logo_url']; ?>" 
                                         alt="<?php echo htmlspecialchars($client['company_name']); ?>" 
                                         class="img-fluid hover-color-image">
                                </div>
                            </div>
                    <?php 
                            endforeach;
                        endfor;
                    else:
                        // Print dynamic logos twice for seamless loop
                        for ($i = 0; $i < 2; $i++):
                            foreach ($clients as $client):
                    ?>
                            <div class="client-logo-box">
                                <div class="image-text-container">
                                    <img src="<?php echo htmlspecialchars($client['logo_url']); ?>" 
                                         alt="<?php echo htmlspecialchars($client['company_name']); ?>" 
                                         class="img-fluid hover-color-image">
                                </div>
                            </div>
                    <?php 
                            endforeach;
                        endfor;
                    endif;
                    ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Skills Section -->
    <section id="skills" class="skills-section section-padding">
        <div class="container">
            <h2 class="section-title">Skills & Expertise</h2>
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="skill-category">
                        <h3 class="mb-4">Marketing & Communications</h3>
                        <div class="skill-bar mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Brand Strategy & Positioning</span>
                                <span>95%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 95%" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div class="skill-bar mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Content Strategy</span>
                                <span>90%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 90%" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div class="skill-bar mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Digital Marketing</span>
                                <span>88%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 88%" aria-valuenow="88" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="skill-category">
                        <h3 class="mb-4">Digital Tools & Analytics</h3>
                        <div class="skill-bar mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span>SEO & Google Analytics</span>
                                <span>92%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 92%" aria-valuenow="92" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div class="skill-bar mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Email Marketing</span>
                                <span>90%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 90%" aria-valuenow="90" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div class="skill-bar mb-4">
                            <div class="d-flex justify-content-between mb-2">
                                <span>Social Media Marketing</span>
                                <span>85%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: 85%" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="text-center mt-4">
                <a href="views/skills.php" class="btn btn-outline-primary">View Full Skills & Experience</a>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact-section section-padding">
        <div class="container">
            <h2 class="section-title">Contact Me</h2>
            <form id="contactFormHome" action="services/process_contact.php" method="POST">
                <input type="hidden" name="redirect" value="true">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <input type="text" name="name" class="form-control" placeholder="Your Name" required>
                    </div>
                    <div class="col-md-6 mb-4">
                        <input type="email" name="email" class="form-control" placeholder="Your Email" required>
                    </div>
                    <div class="col-md-12 mb-4">
                        <textarea name="message" class="form-control" rows="4" placeholder="Your Message" required></textarea>
                    </div>
                    <div class="col-md-12">
                        <button type="submit" class="btn btn-primary btn-lg">Send Message</button>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    
    <!-- Direct Dark Mode Script (Debugging) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Direct script execution');
            const darkModeToggle = document.getElementById('darkModeToggle');
            console.log('Direct toggle element:', darkModeToggle);
            
            if (darkModeToggle) {
                darkModeToggle.addEventListener('click', function() {
                    console.log('Toggle clicked directly');
                    document.body.classList.toggle('dark-mode');
                    const isDarkMode = document.body.classList.contains('dark-mode');
                    darkModeToggle.innerHTML = isDarkMode ? '☀️' : '🌙';
                    localStorage.setItem('theme', isDarkMode ? 'dark' : 'light');
                });
            }
        });
    </script>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/gap-animation.js"></script>
    <script src="assets/js/reduced-motion.js"></script>
    <!-- Load hover animation script -->
    <script src="assets/js/hover-animation.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize AOS animation
            initAOS();
            
            // Problem items animation with IntersectionObserver
            const problemItems = document.querySelectorAll('.js-animate-problem');
            
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    root: null,
                    threshold: 0.2,
                    rootMargin: '0px 0px -50px 0px'
                });
                
                problemItems.forEach(item => {
                    observer.observe(item);
                });
            } else {
                // Fallback for browsers without IntersectionObserver support
                problemItems.forEach(item => {
                    item.classList.add('visible');
                });
            }
            
            // Contact form submission for homepage
            $('#contactFormHome').on('submit', function(e) {
                if (!$(this).find('input[name="redirect"]').val()) {
                    e.preventDefault();
                    
                    const button = $(this).find('button[type="submit"]');
                    const originalButtonText = button.html();
                    button.html('<i class="fas fa-spinner fa-spin"></i> Sending...').prop('disabled', true);
                    
                    $.ajax({
                        type: 'POST',
                        url: 'services/process_contact.php',
                        data: $(this).serialize(),
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                alert('Thank you for your message. I will get back to you soon!');
                                $('#contactFormHome')[0].reset();
                            } else {
                                alert(response.message || 'An error occurred. Please try again.');
                            }
                        },
                        error: function() {
                            alert('An error occurred. Please try again later.');
                        },
                        complete: function() {
                            button.html(originalButtonText).prop('disabled', false);
                        }
                    });
                }
            });
        });

        // Canvas Animation for The Gap section        // Canvas Animation for The Gap section has been moved to gap-animation.js
        // This ensures proper initialization after DOM is loaded
        
        // Intersection Observer for fade-in effects
        const fadeElements = document.querySelectorAll('.fade-in');
        if (fadeElements.length > 0 && 'IntersectionObserver' in window) {
            // Adjust threshold based on device type for better mobile performance
            const isMobileDevice = window.innerWidth < 768;
            const observerThreshold = isMobileDevice ? 0.2 : 0.1;
            const observerMargin = isMobileDevice ? '10px' : '20px';
            
            const fadeObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        // Add visible class with a slight delay on mobile to stagger animations
                        if (isMobileDevice) {
                            const index = Array.from(fadeElements).indexOf(entry.target);
                            setTimeout(() => {
                                entry.target.classList.add('visible');
                            }, index * 150); // 150ms delay between each element
                        } else {
                            entry.target.classList.add('visible');
                        }
                        fadeObserver.unobserve(entry.target); // Stop observing once animation is triggered
                    }
                });
            }, { 
                threshold: observerThreshold,
                rootMargin: observerMargin 
            });
            
            fadeElements.forEach(element => {
                fadeObserver.observe(element);
            });
            
            // Update observer settings on window resize
            window.addEventListener('resize', () => {
                const wasIsMobile = isMobileDevice;
                const newIsMobile = window.innerWidth < 768;
                
                // Only reinitialize if device type changed
                if (wasIsMobile !== newIsMobile) {
                    fadeElements.forEach(element => {
                        if (!element.classList.contains('visible')) {
                            fadeObserver.unobserve(element);
                            fadeObserver.observe(element);
                        }
                    });
                }
            });
        }
        const fadeElements = document.querySelectorAll('.fade-in');
        
        if (fadeElements.length > 0 && 'IntersectionObserver' in window) {
            const fadeObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, { threshold: 0.1 });
            
            fadeElements.forEach(element => {
                fadeObserver.observe(element);
            });
        } else {
            // Fallback for browsers without IntersectionObserver
            fadeElements.forEach(element => {
                element.classList.add('visible');
            });
        }
    </script>
</body>
</html>