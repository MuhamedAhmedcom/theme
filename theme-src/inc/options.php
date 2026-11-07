<?php
/**
 * Panel options for the v1.8 design.
 *
 * Every visible string, image and switch on the homepage comes from here.
 * Values are stored as theme mods (Appearance > Customize > "Muhamed Ahmed")
 * so the theme works on its own, and the panel plugin can take ownership of
 * any key through the `mah_theme_option` filter without a theme change.
 *
 * @package MuhamedAhmed_Astran
 */

defined( 'ABSPATH' ) || exit;

/**
 * Palettes. Hex values live in assets/v18/tokens.css only.
 *
 * @return array<string,array{label:string,cta:string}>
 */
function mahx_palettes() {
	return array(
		'navy'     => array(
			'label' => __( 'Teal on navy (default: trust, calm, senior)', 'muhamed-ahmed-astran' ),
			'cta'   => __( 'Hire me', 'muhamed-ahmed-astran' ),
		),
		'violet'   => array(
			'label' => __( 'Black + violet (modern, premium, AI)', 'muhamed-ahmed-astran' ),
			'cta'   => __( 'Book a call', 'muhamed-ahmed-astran' ),
		),
		'blue'     => array(
			'label' => __( 'White + blue (corporate, highest trust)', 'muhamed-ahmed-astran' ),
			'cta'   => __( 'Get a quote', 'muhamed-ahmed-astran' ),
		),
		'cream'    => array(
			'label' => __( 'Cream + orange (warm, human, freelance)', 'muhamed-ahmed-astran' ),
			'cta'   => __( 'Start a project', 'muhamed-ahmed-astran' ),
		),
		'graphite' => array(
			'label' => __( 'Graphite + lime (hacker, terminal, bold)', 'muhamed-ahmed-astran' ),
			'cta'   => __( "Let's talk", 'muhamed-ahmed-astran' ),
		),
		'indigo'   => array(
			'label' => __( 'Indigo (focused, product, SaaS)', 'muhamed-ahmed-astran' ),
			'cta'   => __( 'Hire me', 'muhamed-ahmed-astran' ),
		),
		'emerald'  => array(
			'label' => __( 'Emerald (growth, fresh, eco)', 'muhamed-ahmed-astran' ),
			'cta'   => __( 'Hire me', 'muhamed-ahmed-astran' ),
		),
		'amber'    => array(
			'label' => __( 'Amber (energetic, warm, bold)', 'muhamed-ahmed-astran' ),
			'cta'   => __( 'Hire me', 'muhamed-ahmed-astran' ),
		),
		'rose'     => array(
			'label' => __( 'Rose (creative, friendly, lifestyle)', 'muhamed-ahmed-astran' ),
			'cta'   => __( 'Hire me', 'muhamed-ahmed-astran' ),
		),
		'slate'    => array(
			'label' => __( 'Slate mono (minimal, neutral, editorial)', 'muhamed-ahmed-astran' ),
			'cta'   => __( 'Hire me', 'muhamed-ahmed-astran' ),
		),
	);
}

/**
 * Option schema: key => [ section, type, default, label ].
 *
 * Copy defaults are suggestions only: translatable, editable, and an empty
 * field hides its element. No default makes a numeric or factual claim.
 *
 * @return array
 */
function mahx_option_schema() {
	$s = array(
		'palette'            => array( 'design', 'palette', 'navy', __( 'Colour palette', 'muhamed-ahmed-astran' ) ),
		'phone_frame'        => array( 'design', 'phoneframe', 'titanium', __( 'Phone mockup frame colour', 'muhamed-ahmed-astran' ) ),
		'phone_island'       => array( 'design', 'checkbox', true, __( 'Show the Dynamic Island on the phone mockup', 'muhamed-ahmed-astran' ) ),
		'header_scroll'      => array( 'design', 'hdrscroll', 'fixed', __( 'Header behaviour on scroll', 'muhamed-ahmed-astran' ) ),
		'mode'               => array( 'design', 'mode', 'system', __( 'Default colour mode for first-time visitors (returning visitors keep their last moon/sun choice)', 'muhamed-ahmed-astran' ) ),
		'mode_switch'        => array( 'design', 'switch', 'toggle', __( 'Who picks the mode', 'muhamed-ahmed-astran' ) ),
		'look'               => array( 'design', 'look', 'editorial', __( 'Look preset (one switch for hero style, card radius, spacing and motion)', 'muhamed-ahmed-astran' ) ),
		'motion_fade_min'    => array( 'design', 'number', 100, __( 'Hero text opacity at the end of its scroll-out effect, in % (85-100; 100 = never fades)', 'muhamed-ahmed-astran' ) ),
		'motion_reveal_from' => array( 'design', 'number', 85, __( 'Starting opacity of sections before they reveal, in % (0-100; content is never left below this)', 'muhamed-ahmed-astran' ) ),
		'motion_level'       => array( 'design', 'motion', 'preset', __( 'Motion (reduced-motion visitors always get none)', 'muhamed-ahmed-astran' ) ),
		'cta_label'          => array( 'design', 'text', '', __( 'CTA label override (empty = palette default)', 'muhamed-ahmed-astran' ) ),
		'cta_url'            => array( 'design', 'text', '', __( 'CTA link (empty = the Contact page with the form; #section = a homepage section; or any full URL)', 'muhamed-ahmed-astran' ) ),
		'home_engine'        => array( 'design', 'engine', 'theme', __( 'Homepage renderer', 'muhamed-ahmed-astran' ) ),
		'webp'               => array( 'design', 'checkbox', true, __( 'Save new JPEG/PNG uploads as WebP', 'muhamed-ahmed-astran' ) ),
		'hero_status'        => array( 'hero', 'text', '', __( 'Availability badge (empty hides it)', 'muhamed-ahmed-astran' ) ),
		'hero_title'         => array( 'hero', 'textarea', __( 'Online stores and websites that *sell*, load *fast* and save you time.', 'muhamed-ahmed-astran' ), __( 'Headline (wrap words in *asterisks* to highlight)', 'muhamed-ahmed-astran' ) ),
		'hero_lead'          => array( 'hero', 'textarea', __( 'I build WooCommerce stores and business websites, including for brands that run more than one shop or site, and add what pays back: automation, speed optimisation, AI tooling and conversion fixes.', 'muhamed-ahmed-astran' ), __( 'Intro paragraph', 'muhamed-ahmed-astran' ) ),
		'hero_alt_label'     => array( 'hero', 'text', __( 'See the work', 'muhamed-ahmed-astran' ), __( 'Secondary button label', 'muhamed-ahmed-astran' ) ),
		'hero_alt_url'       => array( 'hero', 'url', '#work', __( 'Secondary button link', 'muhamed-ahmed-astran' ) ),
		'hero_chips'         => array( 'hero', 'textarea', __( "E-commerce stores\nWooCommerce\nSpeed optimisation\nAutomation\nAI tooling\nConversion", 'muhamed-ahmed-astran' ), __( 'Skill chips (one per line)', 'muhamed-ahmed-astran' ) ),
		'portrait'           => array( 'hero', 'image', 0, __( 'Portrait photo: upload at least 1200 × 1500 px (portrait, 4:5); smaller photos are shown smaller so they stay sharp', 'muhamed-ahmed-astran' ) ),
		'portrait_focus'     => array( 'hero', 'text', '50% 30%', __( 'Portrait focal point (CSS object-position)', 'muhamed-ahmed-astran' ) ),
		'portrait_name'      => array( 'hero', 'text', '', __( 'Portrait card: name (fallback: panel identity)', 'muhamed-ahmed-astran' ) ),
		'portrait_role'      => array( 'hero', 'text', '', __( 'Portrait card: role (fallback: panel identity)', 'muhamed-ahmed-astran' ) ),
		'work_eyebrow'       => array( 'work', 'text', __( 'Stores and sites I built', 'muhamed-ahmed-astran' ), __( 'Eyebrow', 'muhamed-ahmed-astran' ) ),
		'work_count'         => array( 'work', 'number', 6, __( 'Projects shown on the devices', 'muhamed-ahmed-astran' ) ),
		'work_link'          => array( 'work', 'text', __( 'View project', 'muhamed-ahmed-astran' ), __( 'Project link label', 'muhamed-ahmed-astran' ) ),
		'svc_eyebrow'        => array( 'services', 'text', __( 'Solutions', 'muhamed-ahmed-astran' ), __( 'Eyebrow', 'muhamed-ahmed-astran' ) ),
		'svc_title'          => array( 'services', 'text', __( 'Solutions that *add value*, not just pages', 'muhamed-ahmed-astran' ), __( 'Title', 'muhamed-ahmed-astran' ) ),
		'svc_lead'           => array( 'services', 'textarea', __( 'From a first online store to a group of sites: development, speed, automation and AI tools chosen for what they save or earn.', 'muhamed-ahmed-astran' ), __( 'Intro', 'muhamed-ahmed-astran' ) ),
		'svc_for'            => array( 'services', 'text', __( 'For', 'muhamed-ahmed-astran' ), __( 'Audience prefix', 'muhamed-ahmed-astran' ) ),
		'rev_eyebrow'        => array( 'reviews', 'text', __( 'Client words', 'muhamed-ahmed-astran' ), __( 'Eyebrow', 'muhamed-ahmed-astran' ) ),
		'rev_title'          => array( 'reviews', 'text', __( 'What clients say', 'muhamed-ahmed-astran' ), __( 'Title', 'muhamed-ahmed-astran' ) ),
		'rev_source'         => array( 'reviews', 'text', __( 'Verified on {source}', 'muhamed-ahmed-astran' ), __( 'Source link label under each review ({source} = site name taken from the review Source URL, e.g. Trustpilot; shown when the review has a source and link)', 'muhamed-ahmed-astran' ) ),
		'rev_agg_show'       => array( 'reviews', 'checkbox', true, __( 'Show the rating summary under the title', 'muhamed-ahmed-astran' ) ),
		'rev_agg1'           => array( 'reviews', 'text', __( '4.6 on Trustpilot · 22 reviews', 'muhamed-ahmed-astran' ), __( 'Rating summary 1 (empty hides it). Use only figures shown on the public profile.', 'muhamed-ahmed-astran' ) ),
		'rev_agg1_url'       => array( 'reviews', 'url', 'https://www.trustpilot.com/review/prowpsite.com', __( 'Rating summary 1 link', 'muhamed-ahmed-astran' ) ),
		'rev_agg2'           => array( 'reviews', 'text', __( '5.0 on Upwork · 38 reviews · 38 jobs · 202 hours', 'muhamed-ahmed-astran' ), __( 'Rating summary 2 (empty hides it)', 'muhamed-ahmed-astran' ) ),
		'rev_agg2_url'       => array( 'reviews', 'url', 'https://www.upwork.com/freelancers/muhamedahmed', __( 'Rating summary 2 link', 'muhamed-ahmed-astran' ) ),
		'rev_order'          => array( 'reviews', 'order', 'priority', __( 'Order', 'muhamed-ahmed-astran' ) ),
		'rev_clamp'          => array( 'reviews', 'number', 6, __( 'Review text: max lines before "Read more" (0 = show full text)', 'muhamed-ahmed-astran' ) ),
		'rev_more'           => array( 'reviews', 'text', __( 'Read more', 'muhamed-ahmed-astran' ), __( '"Read more" label', 'muhamed-ahmed-astran' ) ),
		'rev_count'          => array( 'reviews', 'number', 8, __( 'How many', 'muhamed-ahmed-astran' ) ),
		'blog_eyebrow'       => array( 'blog', 'text', __( 'Journal', 'muhamed-ahmed-astran' ), __( 'Eyebrow', 'muhamed-ahmed-astran' ) ),
		'blog_title'         => array( 'blog', 'text', __( 'Notes on online stores, speed, automation and AI', 'muhamed-ahmed-astran' ), __( 'Title', 'muhamed-ahmed-astran' ) ),
		'blog_count'         => array( 'blog', 'number', 3, __( 'Posts on the homepage (0 hides)', 'muhamed-ahmed-astran' ) ),
		'blog_toc'           => array( 'blog', 'checkbox', true, __( 'Table of contents on posts', 'muhamed-ahmed-astran' ) ),
		'blog_related_show'  => array( 'blog', 'checkbox', true, __( 'Related posts carousel under posts', 'muhamed-ahmed-astran' ) ),
		'blog_related'       => array( 'blog', 'number', 6, __( 'Related posts: how many (3-12)', 'muhamed-ahmed-astran' ) ),
		'blog_rel_title_en'  => array( 'blog', 'text', 'Related articles', __( 'Related posts heading (English posts)', 'muhamed-ahmed-astran' ) ),
		'blog_rel_title_ar'  => array( 'blog', 'text', 'مقالات ذات صلة', __( 'Related posts heading (Arabic posts)', 'muhamed-ahmed-astran' ) ),
		'blog_rel_excerpt'   => array( 'blog', 'checkbox', true, __( 'Related posts: show a short excerpt', 'muhamed-ahmed-astran' ) ),
		'blog_rel_time'      => array( 'blog', 'checkbox', true, __( 'Related posts: show reading time', 'muhamed-ahmed-astran' ) ),
		'blog_share'         => array( 'blog', 'checkbox', true, __( 'Share buttons on posts (X, LinkedIn, Facebook, WhatsApp, copy link)', 'muhamed-ahmed-astran' ) ),
		'blog_crumbs'        => array( 'blog', 'checkbox', true, __( 'Breadcrumbs on posts', 'muhamed-ahmed-astran' ) ),
		'blog_meta'          => array( 'blog', 'checkbox', true, __( 'Post meta row (date, categories, reading time)', 'muhamed-ahmed-astran' ) ),
		'blog_updated'       => array( 'blog', 'checkbox', true, __( 'Show "Updated" date when a post was edited after publishing', 'muhamed-ahmed-astran' ) ),
		'blog_author_box'    => array( 'blog', 'checkbox', true, __( 'Author box with profile links after posts', 'muhamed-ahmed-astran' ) ),
		'author_avatar'      => array( 'blog', 'image', 0, __( 'Author box photo (empty = hero portrait, then the site icon)', 'muhamed-ahmed-astran' ) ),
		'author_role_en'     => array( 'blog', 'text', 'Ecommerce & Web Solutions Developer', __( 'Author box role (English posts)', 'muhamed-ahmed-astran' ) ),
		'author_role_ar'     => array( 'blog', 'text', 'مطوّر متاجر إلكترونية وحلول ويب', __( 'Author box role (Arabic posts)', 'muhamed-ahmed-astran' ) ),
		'author_bio_en'      => array( 'blog', 'textarea', "I'm Muhamed Ahmed, an ecommerce and web solutions developer with %years% years of experience building fast, secure online stores and websites on WooCommerce, WordPress and custom Laravel platforms. I write about website speed, SEO, automation and conversion.", __( 'Author box bio (English posts; %years% = years of experience)', 'muhamed-ahmed-astran' ) ),
		'author_bio_ar'      => array( 'blog', 'textarea', 'أنا محمد أحمد، مطوّر متاجر إلكترونية وحلول ويب بخبرة %years% عامًا في بناء متاجر ومواقع سريعة وآمنة على WooCommerce وWordPress ومنصات مخصّصة مثل Laravel. أكتب هنا عن سرعة المواقع وتحسين محركات البحث (SEO) والأتمتة وزيادة المبيعات.', __( 'Author box bio (Arabic posts; %years% = years of experience)', 'muhamed-ahmed-astran' ) ),
		'author_url'         => array( 'blog', 'text', '', __( 'Author name link (empty = the About page, then the blog)', 'muhamed-ahmed-astran' ) ),
		'author_posts_en'    => array( 'blog', 'text', 'All articles', __( 'Author box: articles link label (English)', 'muhamed-ahmed-astran' ) ),
		'author_posts_ar'    => array( 'blog', 'text', 'كل المقالات', __( 'Author box: articles link label (Arabic)', 'muhamed-ahmed-astran' ) ),
		'author_hire_en'     => array( 'blog', 'text', 'Hire me', __( 'Author box: contact link label (English)', 'muhamed-ahmed-astran' ) ),
		'author_hire_ar'     => array( 'blog', 'text', 'تواصل معي', __( 'Author box: contact link label (Arabic)', 'muhamed-ahmed-astran' ) ),
		'blog_tldr_title'    => array( 'blog', 'text', __( 'Key takeaways', 'muhamed-ahmed-astran' ), __( 'Takeaways box heading', 'muhamed-ahmed-astran' ) ),
		'blog_faq_title'     => array( 'blog', 'text', __( 'Frequently asked questions', 'muhamed-ahmed-astran' ), __( 'Post FAQ heading', 'muhamed-ahmed-astran' ) ),
		'blog_cta_show'      => array( 'blog', 'checkbox', true, __( 'Call-to-action / newsletter block on the blog and under posts', 'muhamed-ahmed-astran' ) ),
		'blog_cta_title'     => array( 'blog', 'text', __( 'Want a faster store that *sells* more?', 'muhamed-ahmed-astran' ), __( 'Block title (*asterisks* highlight)', 'muhamed-ahmed-astran' ) ),
		'blog_cta_text'      => array( 'blog', 'textarea', __( 'Get practical notes on online stores, speed and automation, or tell me about your project.', 'muhamed-ahmed-astran' ), __( 'Block text', 'muhamed-ahmed-astran' ) ),
		'blog_cta_form'      => array( 'blog', 'text', '', __( 'Newsletter form shortcode (optional, e.g. from your email plugin; empty shows the button)', 'muhamed-ahmed-astran' ) ),
		'blog_cta_label'     => array( 'blog', 'text', __( 'Start a project', 'muhamed-ahmed-astran' ), __( 'Button label (links to the CTA link in Design)', 'muhamed-ahmed-astran' ) ),
		'llms_full'          => array( 'entity', 'checkbox', true, __( 'Also publish /llms-full.txt (llms.txt plus the full text of recent posts)', 'muhamed-ahmed-astran' ) ),
		'llms_full_count'    => array( 'entity', 'number', 10, __( 'Posts included in /llms-full.txt', 'muhamed-ahmed-astran' ) ),
		'blog_wpm'           => array( 'blog', 'number', 200, __( 'Reading speed (words per minute)', 'muhamed-ahmed-astran' ) ),
		'blog_single_preset'   => array( 'blog', 'blogsingle', 'mixed', __( 'Single post layout preset', 'muhamed-ahmed-astran' ) ),
		'blog_archive_preset'  => array( 'blog', 'blogarchive', 'cards', __( 'Blog archive layout preset', 'muhamed-ahmed-astran' ) ),
		'blog_pagination'      => array( 'blog', 'blogpager', 'numbers', __( 'Archive pagination', 'muhamed-ahmed-astran' ) ),
		'blog_progress'        => array( 'blog', 'checkbox', true, __( 'Reading progress bar on posts', 'muhamed-ahmed-astran' ) ),
		'blog_cover_below'     => array( 'blog', 'checkbox', true, __( 'Featured image below hero (Mixed preset)', 'muhamed-ahmed-astran' ) ),
		'blog_filters'         => array( 'blog', 'checkbox', true, __( 'Category pills on blog archive', 'muhamed-ahmed-astran' ) ),
		'blog_per_page'        => array( 'blog', 'number', 0, __( 'Posts per archive page (0 = WordPress Reading setting)', 'muhamed-ahmed-astran' ) ),
		'blog_empty_en'        => array( 'blog', 'text', 'No articles yet.', __( 'Empty archive message (English)', 'muhamed-ahmed-astran' ) ),
		'blog_empty_ar'        => array( 'blog', 'text', 'لا توجد مقالات بعد.', __( 'Empty archive message (Arabic)', 'muhamed-ahmed-astran' ) ),
		'blog_comments'        => array( 'blog', 'checkbox', true, __( 'Show comments template under posts when open', 'muhamed-ahmed-astran' ) ),
		'cta_eyebrow'        => array( 'contact', 'text', __( 'Start a project', 'muhamed-ahmed-astran' ), __( 'Eyebrow', 'muhamed-ahmed-astran' ) ),
		'cta_title'          => array( 'contact', 'textarea', __( 'Need a store or site that *works harder*?', 'muhamed-ahmed-astran' ), __( 'Title', 'muhamed-ahmed-astran' ) ),
		'cta_text'           => array( 'contact', 'textarea', __( 'Share what you sell and where things slow down, and we can map out a practical plan.', 'muhamed-ahmed-astran' ), __( 'Text', 'muhamed-ahmed-astran' ) ),
		'cta_email'          => array( 'contact', 'email', 'hello@muhamedahmed.com', __( 'Public email (fallback: panel identity)', 'muhamed-ahmed-astran' ) ),
		'email_show'         => array( 'contact', 'checkbox', false, __( 'Show email on site (footer, contact, schema, llms.txt)', 'muhamed-ahmed-astran' ) ),
		'cta_form'           => array( 'contact', 'text', '', __( 'Form shortcode (e.g. Fluent Forms)', 'muhamed-ahmed-astran' ) ),
		'footer_note'        => array( 'footer', 'text', '', __( 'Footer note', 'muhamed-ahmed-astran' ) ),
		'stats_verified'     => array( 'stats', 'checkbox', false, __( 'I verified the panel stats; show them publicly (until then only editors see them, flagged)', 'muhamed-ahmed-astran' ) ),
		'seo_title'          => array( 'seo', 'text', __( '%name% | WooCommerce & WordPress Developer in Egypt', 'muhamed-ahmed-astran' ), __( 'Homepage title; %name% = your name (used when no SEO plugin is active)', 'muhamed-ahmed-astran' ) ),
		'seo_desc'           => array( 'seo', 'textarea', __( 'WooCommerce developer in Egypt building professional online stores and business websites: e-commerce development, website speed optimisation, automation and AI tooling.', 'muhamed-ahmed-astran' ), __( 'Homepage / default meta description', 'muhamed-ahmed-astran' ) ),
		'seo_blog_title'     => array( 'seo', 'text', __( 'Journal: online stores, WooCommerce and website speed', 'muhamed-ahmed-astran' ), __( 'Blog index title', 'muhamed-ahmed-astran' ) ),
		'twitter_site'       => array( 'seo', 'text', '', __( 'X / Twitter handle (e.g. @name)', 'muhamed-ahmed-astran' ) ),
		'person_name'        => array( 'entity', 'text', '', __( 'Your name as search engines should show it (fallback: panel identity)', 'muhamed-ahmed-astran' ) ),
		'person_alt'         => array( 'entity', 'textarea', '', __( 'Other spellings of your name, one per line (e.g. the Arabic spelling)', 'muhamed-ahmed-astran' ) ),
		'person_knows'       => array( 'entity', 'textarea', '', __( 'Skills for knowsAbout, one per line (fallback: hero chips)', 'muhamed-ahmed-astran' ) ),
		'business_name'      => array( 'entity', 'text', '', __( 'Business / studio name for ProfessionalService (fallback: site title)', 'muhamed-ahmed-astran' ) ),
		'business_area'      => array( 'entity', 'text', '', __( 'Area served (e.g. Egypt, Worldwide); empty omits it', 'muhamed-ahmed-astran' ) ),
		'about_show'         => array( 'entity', 'checkbox', true, __( 'Show the About section on the homepage', 'muhamed-ahmed-astran' ) ),
		'about_title'        => array( 'entity', 'text', __( 'About %name%', 'muhamed-ahmed-astran' ), __( 'About section title; %name% = your name', 'muhamed-ahmed-astran' ) ),
		'years_experience'   => array( 'entity', 'number', 15, __( 'Years of experience (fills %years% in the author bio and the Person schema description)', 'muhamed-ahmed-astran' ) ),
		'about_text'         => array( 'entity', 'textarea', '', __( 'About text (fallback: panel identity bio)', 'muhamed-ahmed-astran' ) ),
		'llms_intro'         => array( 'entity', 'textarea', '', __( 'Summary at the top of /llms.txt (fallback: homepage description)', 'muhamed-ahmed-astran' ) ),
		'ai_crawlers'        => array( 'entity', 'crawl', 'allow', __( 'AI crawlers (GPTBot, ClaudeBot, Google-Extended, PerplexityBot…) in robots.txt', 'muhamed-ahmed-astran' ) ),
		'person_job'         => array( 'entity', 'text', __( 'WordPress & WooCommerce developer', 'muhamed-ahmed-astran' ), __( 'Person job title (schema)', 'muhamed-ahmed-astran' ) ),
		'person_same'        => array( 'entity', 'textarea', '', __( 'Profile URLs (GitHub, LinkedIn, X, Behance…), one per line: used for sameAs and rel=me links', 'muhamed-ahmed-astran' ) ),
		'logo_light'         => array( 'design', 'image', 0, __( 'Logo for light mode (SVG or transparent PNG at 2x, e.g. 600 × 192 px; empty = Site Identity logo, then the site title as a wordmark)', 'muhamed-ahmed-astran' ) ),
		'logo_dark'          => array( 'design', 'image', 0, __( 'Logo for dark mode (light-coloured version; empty = light-mode logo)', 'muhamed-ahmed-astran' ) ),
		'logo_h'             => array( 'design', 'range', 46, __( 'Logo height on desktop (px)', 'muhamed-ahmed-astran' ) ),
		'logo_h_m'           => array( 'design', 'range', 36, __( 'Logo height on phones (px)', 'muhamed-ahmed-astran' ) ),
		'wa_enable'          => array( 'whatsapp', 'checkbox', true, __( 'Show WhatsApp buttons (only when a number is set here or in the panel identity)', 'muhamed-ahmed-astran' ) ),
		'wa_number'          => array( 'whatsapp', 'text', '', __( 'WhatsApp number in international format, digits only, e.g. 201001234567 (empty = panel identity)', 'muhamed-ahmed-astran' ) ),
		'wa_text'            => array( 'whatsapp', 'textarea', __( 'Hi, I would like to talk about a project.', 'muhamed-ahmed-astran' ), __( 'Prefilled message', 'muhamed-ahmed-astran' ) ),
		'wa_label'           => array( 'whatsapp', 'text', __( 'Chat on WhatsApp', 'muhamed-ahmed-astran' ), __( 'Button label', 'muhamed-ahmed-astran' ) ),
		'wa_side'            => array( 'whatsapp', 'side', 'end', __( 'Floating button position', 'muhamed-ahmed-astran' ) ),
		'wa_desktop'         => array( 'whatsapp', 'checkbox', true, __( 'Floating button on desktop', 'muhamed-ahmed-astran' ) ),
		'wa_mobile'          => array( 'whatsapp', 'checkbox', true, __( 'WhatsApp on phones (floating button and in the menu)', 'muhamed-ahmed-astran' ) ),
		'wa_bar'             => array( 'whatsapp', 'checkbox', false, __( 'Phones: use a sticky bottom bar (WhatsApp + main CTA) instead of the floating button', 'muhamed-ahmed-astran' ) ),
		'wa_vanity'          => array( 'whatsapp', 'checkbox', true, __( 'Hide the number behind a site link (recommended against scrapers)', 'muhamed-ahmed-astran' ) ),
		'wa_slug'            => array( 'whatsapp', 'text', 'whatsapp', __( 'Public path for that link, e.g. whatsapp → muhamedahmed.com/whatsapp', 'muhamed-ahmed-astran' ) ),
		'clients_enable'     => array( 'clients', 'checkbox', true, __( 'Show the client logos strip (Clients menu in the admin; hidden while no client has a logo)', 'muhamed-ahmed-astran' ) ),
		'clients_eyebrow'    => array( 'clients', 'text', __( 'Trusted by', 'muhamed-ahmed-astran' ), __( 'Eyebrow', 'muhamed-ahmed-astran' ) ),
		'clients_title'      => array( 'clients', 'text', __( 'Brands that trusted me with their stores and sites', 'muhamed-ahmed-astran' ), __( 'Title (empty hides it)', 'muhamed-ahmed-astran' ) ),
		'clients_lead'       => array( 'clients', 'textarea', '', __( 'Intro text (empty hides it)', 'muhamed-ahmed-astran' ) ),
		'clients_speed'      => array( 'clients', 'number', 40, __( 'Seconds for one full loop (higher = slower)', 'muhamed-ahmed-astran' ) ),
		'clients_dir'        => array( 'clients', 'dir', 'auto', __( 'Scroll direction', 'muhamed-ahmed-astran' ) ),
		'clients_pause'      => array( 'clients', 'checkbox', true, __( 'Pause on hover and keyboard focus', 'muhamed-ahmed-astran' ) ),
		'clients_grey'       => array( 'clients', 'checkbox', false, __( 'Greyscale logos until hover', 'muhamed-ahmed-astran' ) ),
		'clients_h'          => array( 'clients', 'range', 52, __( 'Logo height (px)', 'muhamed-ahmed-astran' ) ),
		'clients_opacity'    => array( 'clients', 'number', 100, __( 'Logo opacity in % (60-100)', 'muhamed-ahmed-astran' ) ),
		'clients_plate'      => array( 'clients', 'checkbox', true, __( 'White logo plates in dark mode too (best legibility for mixed logos; off = plates follow the palette)', 'muhamed-ahmed-astran' ) ),
		'clients_h_mobile'   => array( 'clients', 'number', 40, __( 'Logo height on phones (px)', 'muhamed-ahmed-astran' ) ),
		'clients_link'       => array( 'clients', 'checkbox', false, __( 'Link logos to the client websites', 'muhamed-ahmed-astran' ) ),
		'clients_rel'        => array( 'clients', 'text', 'noopener nofollow', __( 'Link rel attribute', 'muhamed-ahmed-astran' ) ),
		'logo_dark_fix'      => array( 'design', 'logo_fix', 'auto', __( 'Dark mode with only one logo image (no dark-mode logo set)', 'muhamed-ahmed-astran' ) ),
		'cta_primary_label'  => array( 'contact', 'text', '', __( 'Main button label (links to the CTA link in Design; empty = palette CTA label)', 'muhamed-ahmed-astran' ) ),
		'cta_mail_label'     => array( 'contact', 'text', __( 'Email me', 'muhamed-ahmed-astran' ), __( 'Secondary email button label (empty = show the address)', 'muhamed-ahmed-astran' ) ),
		'foot_cta_home'      => array( 'footer', 'checkbox', false, __( 'Also show the CTA band on the home page (it repeats the contact section there)', 'muhamed-ahmed-astran' ) ),
		'fonts_local'        => array( 'design', 'checkbox', true, __( 'Speed: serve the web fonts from this site (no Google Fonts request)', 'muhamed-ahmed-astran' ) ),
		'foot_show_brand'    => array( 'footer', 'checkbox', true, __( 'Show the brand and tagline', 'muhamed-ahmed-astran' ) ),
		'foot_tagline'       => array( 'footer', 'textarea', __( 'Online stores, websites and automation that save time and grow sales, on WordPress, Shopify, Salla and custom stacks.', 'muhamed-ahmed-astran' ), __( 'Footer tagline (empty = hero intro)', 'muhamed-ahmed-astran' ) ),
		'foot_show_menus'    => array( 'footer', 'checkbox', true, __( 'Show the link columns (Appearance > Menus: "Footer column 1/2/3"; empty columns fall back to pages, services and latest posts)', 'muhamed-ahmed-astran' ) ),
		'foot_col1'          => array( 'footer', 'text', __( 'Explore', 'muhamed-ahmed-astran' ), __( 'Column 1 heading', 'muhamed-ahmed-astran' ) ),
		'foot_col2'          => array( 'footer', 'text', __( 'Solutions', 'muhamed-ahmed-astran' ), __( 'Column 2 heading', 'muhamed-ahmed-astran' ) ),
		'foot_col3'          => array( 'footer', 'text', __( 'Latest notes', 'muhamed-ahmed-astran' ), __( 'Column 3 heading', 'muhamed-ahmed-astran' ) ),
		'foot_show_contact'  => array( 'footer', 'checkbox', true, __( 'Show the contact block (email, phone, WhatsApp, location from the panel identity)', 'muhamed-ahmed-astran' ) ),
		'foot_contact'       => array( 'footer', 'text', __( 'Get in touch', 'muhamed-ahmed-astran' ), __( 'Contact block heading', 'muhamed-ahmed-astran' ) ),
		'foot_show_social'   => array( 'footer', 'checkbox', true, __( 'Show social profiles (Name & profiles > Profile URLs)', 'muhamed-ahmed-astran' ) ),
		'foot_show_cta'      => array( 'footer', 'checkbox', true, __( 'Show the call-to-action band', 'muhamed-ahmed-astran' ) ),
		'foot_cta_title'     => array( 'footer', 'text', __( 'Have a store or site in mind?', 'muhamed-ahmed-astran' ), __( 'CTA band title', 'muhamed-ahmed-astran' ) ),
		'foot_cta_label'     => array( 'footer', 'text', __( 'Start a project', 'muhamed-ahmed-astran' ), __( 'CTA button label (links to the CTA link in Design)', 'muhamed-ahmed-astran' ) ),
		'foot_show_top'      => array( 'footer', 'checkbox', true, __( 'Show the back-to-top button', 'muhamed-ahmed-astran' ) ),
		'foot_copyright'     => array( 'footer', 'text', '', __( 'Copyright line (empty = automatic: (c) year and your name). {year} is replaced with the current year', 'muhamed-ahmed-astran' ) ),
		'foot_show_legal'    => array( 'footer', 'checkbox', true, __( 'Show the legal links (Appearance > Menus: "Footer bottom line")', 'muhamed-ahmed-astran' ) ),
		'scrollbars'         => array( 'design', 'checkbox', true, __( 'Style the scrollbars in the site colours (both modes, every preset)', 'muhamed-ahmed-astran' ) ),
		'theme_color_dark'   => array( 'design', 'text', '', __( 'Browser bar colour in dark mode, hex like #0B1220 (empty = automatic from the preset background)', 'muhamed-ahmed-astran' ) ),
		'theme_color_light'  => array( 'design', 'text', '', __( 'Browser bar colour in light mode, hex (empty = automatic from the preset background)', 'muhamed-ahmed-astran' ) ),
		'svcp_enable'        => array( 'svcpage', 'checkbox', true, __( 'Use the designed Services page (off = the page content as written in the editor)', 'muhamed-ahmed-astran' ) ),
		'svcp_slug'          => array( 'svcpage', 'text', 'services', __( 'Slug of the page that gets this design (or pick the "MX Services" template on any page)', 'muhamed-ahmed-astran' ) ),
		'svcp_eyebrow'       => array( 'svcpage', 'text', __( 'Services', 'muhamed-ahmed-astran' ), __( 'Eyebrow', 'muhamed-ahmed-astran' ) ),
		'svcp_title'         => array( 'svcpage', 'textarea', __( 'Effective solutions for *productivity* and online stores that *sell*', 'muhamed-ahmed-astran' ), __( 'Title (*asterisks* highlight)', 'muhamed-ahmed-astran' ) ),
		'svcp_lead'          => array( 'svcpage', 'textarea', __( 'Stores and websites on WordPress, WooCommerce, Shopify, Salla and custom stacks, plus the automation and AI tooling that saves your team hours every week.', 'muhamed-ahmed-astran' ), __( 'Intro', 'muhamed-ahmed-astran' ) ),
		'svcp_jump'          => array( 'svcpage', 'text', __( 'Jump to services', 'muhamed-ahmed-astran' ), __( 'Hero soft link label (empty hides)', 'muhamed-ahmed-astran' ) ),
		'svcp_paths_title'   => array( 'svcpage', 'text', __( 'Where should we start?', 'muhamed-ahmed-astran' ), __( 'Decision paths heading (empty hides paths)', 'muhamed-ahmed-astran' ) ),
		'svcp_paths'         => array( 'svcpage', 'textarea', __( "New website or redesign | A clear site that matches how you sell | svc-ui-ux-design,svc-wordpress-development\nOnline store that sells | WooCommerce plus order confirmations that cut no-shows | svc-woocommerce-builds,svc-whatsapp-sms-order-confirmation\nSpeed, SEO and growth | Faster pages and search visibility that compounds | svc-performance-and-seo\nKeep my site healthy | Updates, backups and calm support after launch | svc-care-plans", 'muhamed-ahmed-astran' ), __( 'Paths, one per line: Title | Outcome | comma-separated svc-… anchors (first is primary href)', 'muhamed-ahmed-astran' ) ),
		'svcp_catalog_title' => array( 'svcpage', 'text', __( 'Services', 'muhamed-ahmed-astran' ), __( 'Catalog section heading', 'muhamed-ahmed-astran' ) ),
		'svcp_proof_title'   => array( 'svcpage', 'text', __( 'Proof', 'muhamed-ahmed-astran' ), __( 'Proof heading (empty hides proof)', 'muhamed-ahmed-astran' ) ),
		'svcp_proof_text'    => array( 'svcpage', 'textarea', __( 'Projects delivered across Egypt, the Gulf, the USA and Europe — WordPress, WooCommerce and store messaging that teams actually use.', 'muhamed-ahmed-astran' ), __( 'Proof sentence', 'muhamed-ahmed-astran' ) ),
		'svcp_proof_logos'   => array( 'svcpage', 'checkbox', true, __( 'Show client logos strip on Services proof (uses existing clients list)', 'muhamed-ahmed-astran' ) ),
		'svcp_cta_title'     => array( 'svcpage', 'text', __( 'Ready for a site that sells?', 'muhamed-ahmed-astran' ), __( 'Closing CTA heading (empty hides CTA band)', 'muhamed-ahmed-astran' ) ),
		'svcp_cta_text'      => array( 'svcpage', 'textarea', __( 'Tell me about the WordPress, WooCommerce or WhatsApp workflow you need — I will reply with a clear next step.', 'muhamed-ahmed-astran' ), __( 'Closing CTA supporting line', 'muhamed-ahmed-astran' ) ),
		'svcp_cta_label'     => array( 'svcpage', 'text', __( 'Book a services consult', 'muhamed-ahmed-astran' ), __( 'Closing CTA button label', 'muhamed-ahmed-astran' ) ),
		'svcp_price'         => array( 'svcpage', 'text', __( 'From', 'muhamed-ahmed-astran' ), __( 'Price prefix (service edit screen > Price from)', 'muhamed-ahmed-astran' ) ),
		'svcp_more'          => array( 'svcpage', 'text', __( 'Ask about this', 'muhamed-ahmed-astran' ), __( 'Service link label', 'muhamed-ahmed-astran' ) ),
		'svcp_steps_title'   => array( 'svcpage', 'text', __( 'How we work', 'muhamed-ahmed-astran' ), __( 'Process heading (empty hides the process)', 'muhamed-ahmed-astran' ) ),
		'svcp_steps'         => array( 'svcpage', 'textarea', __( "Discovery | We map what you sell, where time is lost and what success looks like.\nPlan | A clear scope, platform choice, timeline and fixed price.\nBuild | Weekly previews on a staging site you can click through.\nLaunch and grow | Go live, measure, then automate and optimise.", 'muhamed-ahmed-astran' ), __( 'Process steps, one per line: Title | text', 'muhamed-ahmed-astran' ) ),
		'svcp_faq_title'     => array( 'svcpage', 'text', __( 'Questions', 'muhamed-ahmed-astran' ), __( 'FAQ heading (empty hides the FAQ)', 'muhamed-ahmed-astran' ) ),
		'svcp_faq'           => array( 'svcpage', 'textarea', __( "Do you only work with WordPress? | No. I build on WordPress and WooCommerce, Shopify, Salla and custom stacks, and pick what fits your business.\nCan you improve an existing store? | Yes: speed, conversion, automation and AI tooling on the store you already run.\nHow long does a project take? | Most stores and sites launch in two to six weeks, depending on scope.", 'muhamed-ahmed-astran' ), __( 'FAQ, one per line: Question | Answer (also sent to search engines as FAQ data)', 'muhamed-ahmed-astran' ) ),
		'abp_enable'         => array( 'abpage', 'checkbox', true, __( 'Use the designed About page (off = the page content as written in the editor)', 'muhamed-ahmed-astran' ) ),
		'abp_slug'           => array( 'abpage', 'text', 'about', __( 'Slug of the page that gets this design (or pick the "MX About" template)', 'muhamed-ahmed-astran' ) ),
		'abp_eyebrow'        => array( 'abpage', 'text', __( 'About', 'muhamed-ahmed-astran' ), __( 'Eyebrow', 'muhamed-ahmed-astran' ) ),
		'abp_title'          => array( 'abpage', 'textarea', __( 'I build stores and sites that *sell* and tools that *save time*', 'muhamed-ahmed-astran' ), __( 'Title (*asterisks* highlight)', 'muhamed-ahmed-astran' ) ),
		'abp_lead'           => array( 'abpage', 'textarea', '', __( 'Intro (empty = the About text from the home page / panel bio)', 'muhamed-ahmed-astran' ) ),
		'abp_content'        => array( 'abpage', 'checkbox', false, __( 'Also show the page editor content (turn on once the old demo text is replaced)', 'muhamed-ahmed-astran' ) ),
		'abp_portrait'       => array( 'abpage', 'image', 0, __( 'Developer portrait (empty hides the photo). Upload at least 1200 × 1500 px (portrait, 4:5).', 'muhamed-ahmed-astran' ) ),
		'faqp_enable'        => array( 'faqpage', 'checkbox', true, __( 'Use the designed FAQ page', 'muhamed-ahmed-astran' ) ),
		'faqp_slug'          => array( 'faqpage', 'text', 'faq', __( 'Slug of the page that gets this design (or pick the "MX FAQ" template)', 'muhamed-ahmed-astran' ) ),
		'faqp_eyebrow'       => array( 'faqpage', 'text', __( 'FAQ', 'muhamed-ahmed-astran' ), __( 'Eyebrow', 'muhamed-ahmed-astran' ) ),
		'faqp_title'         => array( 'faqpage', 'textarea', __( 'Questions clients *ask* before we start', 'muhamed-ahmed-astran' ), __( 'Title (*asterisks* highlight)', 'muhamed-ahmed-astran' ) ),
		'faqp_lead'          => array( 'faqpage', 'textarea', __( 'Straight answers about platforms, timelines, pricing and support. Anything else, just ask.', 'muhamed-ahmed-astran' ), __( 'Intro', 'muhamed-ahmed-astran' ) ),
		'faqp_faq'           => array( 'faqpage', 'textarea', __( "Do you only work with WordPress? | No. I build on WordPress and WooCommerce, Shopify, Salla and custom stacks, and pick what fits your business.\nCan you improve an existing store? | Yes: speed, conversion, automation and AI tooling on the store you already run.\nHow long does a project take? | Most stores and sites launch in two to six weeks, depending on scope.\nHow is pricing set? | A fixed price per agreed scope, split into milestones, so there are no surprises.\nDo you offer support after launch? | Yes: monthly care plans for updates, backups, speed and small changes, or help on demand.\nCan you work with my existing team or agency? | Yes. I can take a clear part of the project and work inside your tools and process.", 'muhamed-ahmed-astran' ), __( 'Questions, one per line: Question | Answer (also sent to search engines as FAQ data)', 'muhamed-ahmed-astran' ) ),
		'pages_mx'           => array( 'pages', 'checkbox', true, __( 'Show every other page (privacy, legal, etc.) in the site design instead of the old Astra layout', 'muhamed-ahmed-astran' ) ),
		'pages_empty'        => array( 'pages', 'textarea', __( 'This page is being updated. Meanwhile, tell me what you need and I will reply within one working day.', 'muhamed-ahmed-astran' ), __( 'Text shown when a page has no usable content (e.g. a shop page while no shop plugin is active)', 'muhamed-ahmed-astran' ) ),
		'ctp_enable'         => array( 'ctpage', 'checkbox', true, __( 'Use the designed Contact page (off = the page content as written in the editor)', 'muhamed-ahmed-astran' ) ),
		'ctp_slug'           => array( 'ctpage', 'text', 'contact', __( 'Slug of the page that gets this design (or pick the "MX Contact" template on any page)', 'muhamed-ahmed-astran' ) ),
		'ctp_eyebrow'        => array( 'ctpage', 'text', __( 'Contact', 'muhamed-ahmed-astran' ), __( 'Eyebrow', 'muhamed-ahmed-astran' ) ),
		'ctp_title'          => array( 'ctpage', 'textarea', __( "Let's build something that *works harder*", 'muhamed-ahmed-astran' ), __( 'Title (*asterisks* highlight)', 'muhamed-ahmed-astran' ) ),
		'ctp_lead'           => array( 'ctpage', 'textarea', __( 'Tell me about your store, site or the task that eats your time. I reply with practical next steps.', 'muhamed-ahmed-astran' ), __( 'Intro', 'muhamed-ahmed-astran' ) ),
		'ctp_hours'          => array( 'ctpage', 'text', '', __( 'Availability / hours (empty = panel response time)', 'muhamed-ahmed-astran' ) ),
		'ctp_booking'        => array( 'ctpage', 'url', '', __( 'Booking link, e.g. Calendly (empty hides it)', 'muhamed-ahmed-astran' ) ),
		'ctp_booking_label'  => array( 'ctpage', 'text', __( 'Book a call', 'muhamed-ahmed-astran' ), __( 'Booking button label', 'muhamed-ahmed-astran' ) ),
		'ctp_map'            => array( 'ctpage', 'url', '', __( 'Map embed URL (https, e.g. a Google Maps embed link; empty = no map)', 'muhamed-ahmed-astran' ) ),
		'cf_enable'          => array( 'form', 'checkbox', true, __( 'Show the built-in contact form', 'muhamed-ahmed-astran' ) ),
		'cf_to'              => array( 'form', 'email', '', __( 'Send enquiries to (empty = site admin email)', 'muhamed-ahmed-astran' ) ),
		'cf_subject'         => array( 'form', 'text', __( 'New enquiry', 'muhamed-ahmed-astran' ), __( 'Email subject prefix', 'muhamed-ahmed-astran' ) ),
		'cf_l_name'          => array( 'form', 'text', __( 'Your name', 'muhamed-ahmed-astran' ), __( 'Label: name', 'muhamed-ahmed-astran' ) ),
		'cf_l_email'         => array( 'form', 'text', __( 'Email', 'muhamed-ahmed-astran' ), __( 'Label: email', 'muhamed-ahmed-astran' ) ),
		'cf_phone'           => array( 'form', 'checkbox', true, __( 'Ask for a phone / WhatsApp number (optional field)', 'muhamed-ahmed-astran' ) ),
		'cf_l_phone'         => array( 'form', 'text', __( 'Phone or WhatsApp (optional)', 'muhamed-ahmed-astran' ), __( 'Label: phone', 'muhamed-ahmed-astran' ) ),
		'cf_phone_geo'       => array( 'form', 'checkbox', true, __( 'Detect the phone country from the visitor IP when available (no country is forced otherwise)', 'muhamed-ahmed-astran' ) ),
		'cf_service'         => array( 'form', 'checkbox', true, __( 'Ask which service (list = your Services)', 'muhamed-ahmed-astran' ) ),
		'cf_l_service'       => array( 'form', 'text', __( 'What do you need?', 'muhamed-ahmed-astran' ), __( 'Label: service', 'muhamed-ahmed-astran' ) ),
		'cf_budget'          => array( 'form', 'checkbox', true, __( 'Ask for a budget range', 'muhamed-ahmed-astran' ) ),
		'cf_l_budget'        => array( 'form', 'text', __( 'Budget', 'muhamed-ahmed-astran' ), __( 'Label: budget', 'muhamed-ahmed-astran' ) ),
		'cf_budgets'         => array( 'form', 'textarea', __( "Under $1,000\n$1,000 - $3,000\n$3,000 - $8,000\nOver $8,000\nNot sure yet", 'muhamed-ahmed-astran' ), __( 'Budget choices, one per line', 'muhamed-ahmed-astran' ) ),
		'cf_l_message'       => array( 'form', 'text', __( 'Message', 'muhamed-ahmed-astran' ), __( 'Label: message', 'muhamed-ahmed-astran' ) ),
		'cf_consent'         => array( 'form', 'checkbox', true, __( 'Require a privacy consent tick', 'muhamed-ahmed-astran' ) ),
		'cf_l_consent'       => array( 'form', 'text', __( 'I agree that my details are used to answer this enquiry.', 'muhamed-ahmed-astran' ), __( 'Label: consent', 'muhamed-ahmed-astran' ) ),
		'cf_privacy_link'    => array( 'form', 'checkbox', true, __( 'Link the Privacy Policy page (Settings > Privacy) next to the consent', 'muhamed-ahmed-astran' ) ),
		'cf_l_submit'        => array( 'form', 'text', __( 'Send message', 'muhamed-ahmed-astran' ), __( 'Label: submit button', 'muhamed-ahmed-astran' ) ),
		'cf_success'         => array( 'form', 'textarea', __( 'Thank you, your message is on its way. I usually reply within one business day.', 'muhamed-ahmed-astran' ), __( 'Success message', 'muhamed-ahmed-astran' ) ),
		'cf_save'            => array( 'form', 'checkbox', false, __( 'Also save enquiries in the admin (Enquiries menu, private)', 'muhamed-ahmed-astran' ) ),
		'cf_min_secs'        => array( 'form', 'number', 3, __( 'Spam check: minimum seconds before a form can be sent', 'muhamed-ahmed-astran' ) ),
		'cf_rate'            => array( 'form', 'number', 5, __( 'Spam check: max messages per visitor per hour', 'muhamed-ahmed-astran' ) ),
		'cf_ts_site'         => array( 'form', 'text', '', __( 'Cloudflare Turnstile site key (optional; both keys turn it on)', 'muhamed-ahmed-astran' ) ),
		'cf_ts_secret'       => array( 'form', 'text', '', __( 'Cloudflare Turnstile secret key', 'muhamed-ahmed-astran' ) ),
		'og_image'           => array( 'seo', 'image', 0, __( 'Default share image (fallback: portrait)', 'muhamed-ahmed-astran' ) ),
		'wm_enable'          => array( 'ops', 'checkbox', true, __( 'Watermark REST (wm-probe / wm-apply / wm-restore) for admins', 'muhamed-ahmed-astran' ) ),
		'wm_text'            => array( 'ops', 'text', 'muhamedahmed.com', __( 'Watermark text stamped on images', 'muhamed-ahmed-astran' ) ),
		'seo_hreflang'       => array( 'seo', 'checkbox', true, __( 'Keep WPML hreflang tags in the document head', 'muhamed-ahmed-astran' ) ),
		'hide_x_powered_by'  => array( 'seo', 'checkbox', true, __( 'Hide the X-Powered-By header', 'muhamed-ahmed-astran' ) ),
		'schema_alt_names'   => array( 'seo', 'checkbox', true, __( 'Add name variants (alternateName) to Person and WebSite schema', 'muhamed-ahmed-astran' ) ),
		'ar_chrome'          => array( 'ops', 'checkbox', true, __( 'Translate chrome strings and menus on the Arabic site', 'muhamed-ahmed-astran' ) ),
		'ar_chrome_map'      => array( 'ops', 'textarea', '', __( 'Arabic chrome map, one per line: English | Arabic (empty keeps the stored map)', 'muhamed-ahmed-astran' ) ),
		'work_public'        => array( 'work', 'checkbox', false, __( 'Public project pages (off = cards on the home stage only, no /work URLs)', 'muhamed-ahmed-astran' ) ),
		'work_show_link'     => array( 'work', 'checkbox', false, __( 'Show the “View project” link on home cards', 'muhamed-ahmed-astran' ) ),
		'rev_hide_upwork'    => array( 'reviews', 'checkbox', true, __( 'Hide Upwork profile links (keep the review text)', 'muhamed-ahmed-astran' ) ),
		'rev_interleave'     => array( 'reviews', 'checkbox', true, __( 'Interleave reviews so the same source is not clustered', 'muhamed-ahmed-astran' ) ),
	);

	return apply_filters( 'muhamed_ahmed_astran_option_schema', $s );
}

/**
 * Read one option. The panel plugin may override via `mah_theme_option`.
 *
 * @param string $key Option key.
 * @return mixed
 */
function mahx_opt( $key ) {
	static $schema = null;
	if ( null === $schema ) {
		$schema = mahx_option_schema();
	}
	$default = isset( $schema[ $key ] ) ? $schema[ $key ][2] : '';
	$value   = get_theme_mod( 'mahx_' . $key, $default );

	return apply_filters( 'muhamed_ahmed_astran_option', $value, $key, $default );
}

/**
 * REST permission: site admin only.
 *
 * @return bool
 */
function mahx_rest_can_manage() {
	return current_user_can( 'manage_options' );
}

/**
 * Boolean option without going through translating filters (safe inside those filters).
 *
 * @param string $key     Option key.
 * @param bool   $default Default.
 * @return bool
 */
function mahx_flag( $key, $default = true ) {
	static $cache = array();
	if ( array_key_exists( $key, $cache ) ) {
		return $cache[ $key ];
	}
	// Do not call mahx_option_schema() here: its __() labels re-enter gettext
	// (WPML + i18n-chrome) and blow the stack. Callers pass the schema default.
	$value = get_theme_mod( 'mahx_' . $key, $default );
	$panel = get_option( 'muhamed_ahmed_panel' );
	if ( is_array( $panel ) && array_key_exists( $key, $panel ) && '' !== $panel[ $key ] && null !== $panel[ $key ] ) {
		$value = $panel[ $key ];
	}
	if ( is_bool( $value ) ) {
		$cache[ $key ] = $value;
		return $value;
	}
	if ( is_numeric( $value ) ) {
		$cache[ $key ] = 0 !== (int) $value;
		return $cache[ $key ];
	}
	$s             = strtolower( trim( (string) $value ) );
	$cache[ $key ] = ! in_array( $s, array( '0', 'false', 'off', 'no' ), true );
	return $cache[ $key ];
}

/**
 * Look presets. Each one is a set of CSS variables in assets/v18/tokens.css.
 *
 * @return array<string,array{label:string,motion:string}>
 */
function mahx_looks() {
	return array(
		'editorial' => array(
			'label'  => __( 'Editorial: split hero, soft corners, full motion', 'muhamed-ahmed-astran' ),
			'motion' => 'full',
		),
		'bold'      => array(
			'label'  => __( 'Bold: large centred hero, sharp corners, full motion', 'muhamed-ahmed-astran' ),
			'motion' => 'full',
		),
		'soft'      => array(
			'label'  => __( 'Soft: rounded cards, airy spacing, subtle motion', 'muhamed-ahmed-astran' ),
			'motion' => 'subtle',
		),
		'compact'   => array(
			'label'  => __( 'Compact: dense spacing, small radius, subtle motion', 'muhamed-ahmed-astran' ),
			'motion' => 'subtle',
		),
	);
}

/**
 * Current look key.
 *
 * @return string
 */
function mahx_look() {
	$l = (string) mahx_opt( 'look' );
	return array_key_exists( $l, mahx_looks() ) ? $l : 'editorial';
}

/**
 * Effective motion level: full, subtle or off.
 *
 * @return string
 */
function mahx_motion() {
	$m = (string) mahx_opt( 'motion_level' );
	if ( in_array( $m, array( 'full', 'subtle', 'off' ), true ) ) {
		return $m;
	}
	$looks = mahx_looks();
	return $looks[ mahx_look() ]['motion'];
}

/**
 * Colour mode the server renders: light, dark or system.
 *
 * @return string
 */
function mahx_mode() {
	$m = (string) mahx_opt( 'mode' );
	return in_array( $m, array( 'light', 'dark', 'system' ), true ) ? $m : 'system';
}

/**
 * Whether visitors may switch the colour mode.
 *
 * @return bool
 */
function mahx_mode_toggle() {
	return 'fixed' !== mahx_opt( 'mode_switch' );
}

/**
 * Public name: entity option, panel identity, site title.
 *
 * @return string
 */
function mahx_name() {
	$n = trim( (string) mahx_opt( 'person_name' ) );
	$n = '' !== $n ? $n : mahx_identity( 'full_name' );
	return '' !== $n ? $n : get_bloginfo( 'name' );
}

/**
 * Replace %name% in a panel string.
 *
 * @param string $text Text.
 * @return string
 */
function mahx_with_name( $text ) {
	return str_replace( '%name%', mahx_name(), (string) $text );
}

/**
 * Split a one-per-line panel field.
 *
 * @param string $key Option key.
 * @return string[]
 */
function mahx_lines( $key ) {
	$raw   = (string) mahx_opt( $key );
	$parts = preg_split( '/\R/u', $raw );
	if ( ! is_array( $parts ) ) {
		$parts = preg_split( '/\r\n|\r|\n/', $raw );
	}
	return array_values( array_filter( array_map( 'trim', (array) $parts ) ) );
}

/** Current palette key. */
function mahx_palette() {
	$p = (string) mahx_opt( 'palette' );
	return array_key_exists( $p, mahx_palettes() ) ? $p : 'navy';
}

/** CTA label: override, else palette default. */
function mahx_cta_label() {
	if ( has_filter( 'mahx_cta_label' ) ) {
		$f = trim( (string) apply_filters( 'mahx_cta_label', '' ) );
		if ( '' !== $f ) {
			return $f;
		}
	}
	$label = trim( (string) mahx_opt( 'cta_label' ) );
	if ( '' !== $label ) {
		return $label;
	}
	$p = mahx_palettes();
	return $p[ mahx_palette() ]['cta'];
}

/**
 * Turn "*word*" into <em>word</em> after escaping.
 *
 * @param string $text Raw text.
 * @return string Safe HTML.
 */
function mahx_highlight( $text ) {
	return preg_replace( '/\*(.+?)\*/u', '<em>$1</em>', esc_html( $text ) );
}

/**
 * Customizer registration.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
add_action(
	'customize_register',
	static function ( $wp_customize ) {
		$wp_customize->add_panel(
			'mahx_panel',
			array(
				'title'    => __( 'Muhamed Ahmed', 'muhamed-ahmed-astran' ),
				'priority' => 5,
			)
		);

		$sections = array(
			'design'   => __( 'Design', 'muhamed-ahmed-astran' ),
			'hero'     => __( 'Hero', 'muhamed-ahmed-astran' ),
			'work'     => __( 'Work on devices', 'muhamed-ahmed-astran' ),
			'services' => __( 'Services', 'muhamed-ahmed-astran' ),
			'stats'    => __( 'Stats (verify before showing)', 'muhamed-ahmed-astran' ),
			'reviews'  => __( 'Reviews', 'muhamed-ahmed-astran' ),
			'blog'     => __( 'Blog', 'muhamed-ahmed-astran' ),
			'contact'  => __( 'Contact (homepage section)', 'muhamed-ahmed-astran' ),
			'footer'   => __( 'Footer', 'muhamed-ahmed-astran' ),
			'svcpage'  => __( 'Services page', 'muhamed-ahmed-astran' ),
			'ctpage'   => __( 'Contact page', 'muhamed-ahmed-astran' ),
			'abpage'   => __( 'About page', 'muhamed-ahmed-astran' ),
			'faqpage'  => __( 'FAQ page', 'muhamed-ahmed-astran' ),
			'pages'    => __( 'Other pages', 'muhamed-ahmed-astran' ),
			'form'     => __( 'Contact form', 'muhamed-ahmed-astran' ),
			'whatsapp' => __( 'WhatsApp', 'muhamed-ahmed-astran' ),
			'clients'  => __( 'Client logos', 'muhamed-ahmed-astran' ),
			'seo'      => __( 'SEO & sharing', 'muhamed-ahmed-astran' ),
			'entity'   => __( 'Name & profiles (search + AI answers)', 'muhamed-ahmed-astran' ),
		);
		foreach ( $sections as $id => $title ) {
			$wp_customize->add_section(
				'mahx_' . $id,
				array(
					'title' => $title,
					'panel' => 'mahx_panel',
				)
			);
		}

		$choices = array(
			'palette'    => wp_list_pluck( mahx_palettes(), 'label' ),
			'mode'       => array(
				'system' => __( 'Auto: visitor device setting on first visit, then their last toggle choice', 'muhamed-ahmed-astran' ),
				'light'  => __( 'Light', 'muhamed-ahmed-astran' ),
				'dark'   => __( 'Dark', 'muhamed-ahmed-astran' ),
			),
			'phoneframe' => array(
				'titanium' => __( 'Natural titanium', 'muhamed-ahmed-astran' ),
				'black'    => __( 'Black titanium', 'muhamed-ahmed-astran' ),
				'white'    => __( 'White titanium', 'muhamed-ahmed-astran' ),
			),
			'hdrscroll'  => array(
				'fixed' => __( 'Fixed: always at the top, no motion', 'muhamed-ahmed-astran' ),
				'hide'  => __( 'Hide on scroll down, show on scroll up', 'muhamed-ahmed-astran' ),
			),
			'switch'     => array(
				'toggle' => __( 'Visitors choose (show the toggle, start from the mode above)', 'muhamed-ahmed-astran' ),
				'fixed'  => __( 'Always use the mode above (hide the toggle)', 'muhamed-ahmed-astran' ),
			),
			'look'       => wp_list_pluck( mahx_looks(), 'label' ),
			'motion'     => array(
				'preset' => __( 'As the look preset', 'muhamed-ahmed-astran' ),
				'full'   => __( 'Full (devices, reveals, marquee)', 'muhamed-ahmed-astran' ),
				'subtle' => __( 'Subtle (fades only)', 'muhamed-ahmed-astran' ),
				'off'    => __( 'Off', 'muhamed-ahmed-astran' ),
			),
			'crawl'      => array(
				'allow' => __( 'Allow (recommended: lets AI answers cite you)', 'muhamed-ahmed-astran' ),
				'block' => __( 'Block AI training/answer crawlers', 'muhamed-ahmed-astran' ),
			),
			'engine'     => array(
				'theme'  => __( 'Theme v1.8 design', 'muhamed-ahmed-astran' ),
				'plugin' => __( 'Panel plugin (1.7 layout)', 'muhamed-ahmed-astran' ),
			),
			'dir'        => array(
				'auto'    => __( 'Reading direction (right-to-left on Arabic pages)', 'muhamed-ahmed-astran' ),
				'reverse' => __( 'Opposite to reading direction', 'muhamed-ahmed-astran' ),
			),
			'logo_fix'   => array(
				'auto'   => __( 'Auto: drop the white background (blend) and lighten dark ink', 'muhamed-ahmed-astran' ),
				'invert' => __( 'Invert colours (for a dark logo on white)', 'muhamed-ahmed-astran' ),
				'plate'  => __( 'Keep as is on a rounded light plate', 'muhamed-ahmed-astran' ),
				'none'   => __( 'No change', 'muhamed-ahmed-astran' ),
			),
			'side'       => array(
				'end'   => __( 'Right (left on Arabic pages)', 'muhamed-ahmed-astran' ),
				'start' => __( 'Left (right on Arabic pages)', 'muhamed-ahmed-astran' ),
			),
			'order'      => array(
				'priority' => __( 'Priority', 'muhamed-ahmed-astran' ),
				'random'   => __( 'Random', 'muhamed-ahmed-astran' ),
				'newest'   => __( 'Newest', 'muhamed-ahmed-astran' ),
			),
			'blogsingle'  => array(
				'mixed'     => __( 'Mixed: dark hero + light body (recommended)', 'muhamed-ahmed-astran' ),
				'light'     => __( 'Light: full light reading page', 'muhamed-ahmed-astran' ),
				'editorial' => __( 'Editorial: full-bleed image hero, narrow column', 'muhamed-ahmed-astran' ),
			),
			'blogarchive' => array(
				'cards'    => __( 'Card grid (default)', 'muhamed-ahmed-astran' ),
				'list'     => __( 'Compact list', 'muhamed-ahmed-astran' ),
				'featured' => __( 'Large featured grid', 'muhamed-ahmed-astran' ),
			),
			'blogpager'   => array(
				'numbers'   => __( 'Page numbers', 'muhamed-ahmed-astran' ),
				'load_more' => __( 'Load more button', 'muhamed-ahmed-astran' ),
				'infinite'  => __( 'Infinite scroll', 'muhamed-ahmed-astran' ),
			),
		);

		foreach ( mahx_option_schema() as $key => $def ) {
			list( $section, $type, $default, $label ) = $def;
			$sanitize                                 = array(
				'text'     => 'sanitize_text_field',
				'textarea' => 'sanitize_textarea_field',
				'url'      => 'esc_url_raw',
				'email'    => 'sanitize_email',
				'number'   => 'absint',
				'range'    => static function ( $v ) {
					return max( 16, min( 96, absint( $v ) ) );
				},
				'image'    => 'absint',
				'checkbox' => 'rest_sanitize_boolean',
			);
			$cb                                       = isset( $sanitize[ $type ] ) ? $sanitize[ $type ] : static function ( $v ) use ( $choices, $type, $default ) {
				return isset( $choices[ $type ][ $v ] ) ? $v : $default;
			};
			$wp_customize->add_setting(
				'mahx_' . $key,
				array(
					'default'           => $default,
					'sanitize_callback' => $cb,
					'transport'         => in_array( $key, array( 'palette', 'mode', 'look' ), true ) ? 'postMessage' : 'refresh',
				)
			);

			$args = array(
				'label'   => $label,
				'section' => 'mahx_' . $section,
			);
			if ( 'image' === $type ) {
				$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'mahx_' . $key, $args + array( 'mime_type' => 'image' ) ) );
				continue;
			}
			if ( 'range' === $type ) {
				$args['type']        = 'range';
				$args['input_attrs'] = array(
					'min'  => 16,
					'max'  => 96,
					'step' => 1,
				);
				$wp_customize->add_control( 'mahx_' . $key, $args );
				continue;
			}
			if ( isset( $choices[ $type ] ) ) {
				$args['type']    = 'select';
				$args['choices'] = $choices[ $type ];
			} else {
				$args['type'] = in_array( $type, array( 'textarea', 'url', 'email', 'number', 'checkbox' ), true ) ? $type : 'text';
			}
			$wp_customize->add_control( 'mahx_' . $key, $args );
		}
	}
);

/**
 * Blog chrome keys the Codestar panel can turn off (same bag as muhamed_ahmed_panel).
 *
 * blog_related is a count, not a switch: the related block uses blog_related_show.
 *
 * @return string[]
 */
function mahx_blog_chrome_keys() {
	return array(
		'blog_progress',
		'blog_crumbs',
		'blog_meta',
		'blog_updated',
		'blog_toc',
		'blog_share',
		'blog_author_box',
		'blog_related_show',
		'blog_cta_show',
		'blog_comments',
	);
}

/**
 * Codestar switchers that live in muhamed_ahmed_panel (blog chrome + migrated snippets).
 *
 * @return string[]
 */
function mahx_panel_bool_keys() {
	return array_merge(
		mahx_blog_chrome_keys(),
		array(
			'wm_enable',
			'seo_hreflang',
			'hide_x_powered_by',
			'schema_alt_names',
			'ar_chrome',
			'work_public',
			'work_show_link',
			'rev_hide_upwork',
			'rev_interleave',
		)
	);
}

/**
 * Codestar switcher field: EN title plus Arabic so the owner sees both without WPML.
 *
 * @param string $id    Option key (same as mahx_opt).
 * @param string $en    English label.
 * @param string $ar    Arabic label.
 * @param string $en_d  English description.
 * @param string $ar_d  Arabic description.
 * @return array
 */
function mahx_blog_csf_switch( $id, $en, $ar, $en_d = '', $ar_d = '', $default = true, $label = '' ) {
	$field = array(
		'id'      => $id,
		'type'    => 'switcher',
		'title'   => $en . ' / ' . $ar,
		'label'   => '' !== $label ? $label : ( __( 'Show', 'muhamed-ahmed-astran' ) . ' / إظهار' ),
		'default' => (bool) $default,
	);
	if ( '' !== $en_d || '' !== $ar_d ) {
		$field['desc'] = trim( $en_d . ( '' !== $ar_d ? ' / ' . $ar_d : '' ) );
	}
	return $field;
}

/**
 * Codestar media field for the About portrait (same option bag as chrome).
 */
function mahx_abp_csf_section() {
	if ( ! class_exists( 'CSF' ) ) {
		return;
	}
	CSF::createSection(
		'muhamed_ahmed_panel',
		array(
			'id'     => 'mah_about_page',
			'title'  => __( 'About page', 'muhamed-ahmed-astran' ),
			'icon'   => 'fas fa-user',
			'fields' => array(
				array(
					'id'      => 'abp_portrait',
					'type'    => 'media',
					'title'   => __( 'Developer portrait', 'muhamed-ahmed-astran' ),
					'desc'    => __( 'Empty hides the photo on the About page. Prefer 1200 × 1500 px (4:5).', 'muhamed-ahmed-astran' ),
					'library' => 'image',
				),
			),
		)
	);
}

/**
 * Codestar Blog chrome switches for admin.php?page=muhamed-ahmed-panel.
 */
function mahx_blog_csf_section() {
	if ( ! class_exists( 'CSF' ) ) {
		return;
	}
	CSF::createSection(
		'muhamed_ahmed_panel',
		array(
			'id'     => 'mah_blog_chrome',
			'title'  => __( 'Blog post chrome', 'muhamed-ahmed-astran' ) . ' / عناصر المقال',
			'icon'   => 'fas fa-newspaper',
			'fields' => array(
				array(
					'type'    => 'subheading',
					'content' => __( 'Show or hide each piece on single posts. Off removes the markup, not an empty wrapper.', 'muhamed-ahmed-astran' ) . ' / إيقاف الخيار يخفي العنصر بالكامل.',
				),
				mahx_blog_csf_switch(
					'blog_progress',
					__( 'Reading progress bar', 'muhamed-ahmed-astran' ),
					'شريط تقدّم القراءة'
				),
				mahx_blog_csf_switch(
					'blog_crumbs',
					__( 'Breadcrumbs', 'muhamed-ahmed-astran' ),
					'مسار التنقّل'
				),
				mahx_blog_csf_switch(
					'blog_meta',
					__( 'Post meta row', 'muhamed-ahmed-astran' ),
					'صف بيانات المقال',
					__( 'Date, categories, and reading time in the post hero.', 'muhamed-ahmed-astran' ),
					'التاريخ والتصنيفات ووقت القراءة في رأس المقال.'
				),
				mahx_blog_csf_switch(
					'blog_updated',
					__( 'Updated date', 'muhamed-ahmed-astran' ),
					'تاريخ التحديث',
					__( 'Shown only when the post was edited more than a day after publishing.', 'muhamed-ahmed-astran' ),
					'يظهر فقط إذا عُدّل المقال بعد يوم من نشره.'
				),
				mahx_blog_csf_switch(
					'blog_toc',
					__( 'Table of contents', 'muhamed-ahmed-astran' ),
					'جدول المحتويات'
				),
				mahx_blog_csf_switch(
					'blog_share',
					__( 'Share buttons', 'muhamed-ahmed-astran' ),
					'أزرار المشاركة'
				),
				mahx_blog_csf_switch(
					'blog_author_box',
					__( 'Author box', 'muhamed-ahmed-astran' ),
					'صندوق الكاتب'
				),
				mahx_blog_csf_switch(
					'blog_related_show',
					__( 'Related posts', 'muhamed-ahmed-astran' ),
					'مقالات ذات صلة'
				),
				mahx_blog_csf_switch(
					'blog_cta_show',
					__( 'End-of-post call to action', 'muhamed-ahmed-astran' ),
					'دعوة الإجراء بعد المقال'
				),
				mahx_blog_csf_switch(
					'blog_comments',
					__( 'Comments', 'muhamed-ahmed-astran' ),
					'التعليقات',
					__( 'Comments template only when comments are open or already have replies.', 'muhamed-ahmed-astran' ),
					'يظهر قالب التعليقات فقط إن كانت مفتوحة أو وُجدت ردود.'
				),
			),
		)
	);
}

/**
 * Register theme Codestar sections once CSF exists.
 */
function mahx_csf_theme_sections() {
	mahx_abp_csf_section();
	mahx_blog_csf_section();
	mahx_ops_csf_section();
}

/**
 * Codestar section for migrated snippet toggles (admin.php?page=muhamed-ahmed-panel).
 */
function mahx_ops_csf_section() {
	if ( ! class_exists( 'CSF' ) ) {
		return;
	}
	$on = __( 'On', 'muhamed-ahmed-astran' ) . ' / تشغيل';
	CSF::createSection(
		'muhamed_ahmed_panel',
		array(
			'id'     => 'mah_site_ops',
			'title'  => __( 'SEO, Arabic & portfolio', 'muhamed-ahmed-astran' ) . ' / SEO والعربية والمعرض',
			'icon'   => 'fas fa-sliders-h',
			'fields' => array(
				array(
					'type'    => 'subheading',
					'content' => __( 'SEO', 'muhamed-ahmed-astran' ),
				),
				mahx_blog_csf_switch(
					'seo_hreflang',
					__( 'WPML hreflang in the head', 'muhamed-ahmed-astran' ),
					'وسوم hreflang من WPML',
					__( 'Keeps WPML language tags on. Rank Math is not the source of truth.', 'muhamed-ahmed-astran' ),
					'WPML هو مصدر وسوم اللغة.',
					true,
					$on
				),
				mahx_blog_csf_switch(
					'hide_x_powered_by',
					__( 'Hide X-Powered-By', 'muhamed-ahmed-astran' ),
					'إخفاء ترويسة X-Powered-By',
					'',
					'',
					true,
					$on
				),
				mahx_blog_csf_switch(
					'schema_alt_names',
					__( 'Schema name variants', 'muhamed-ahmed-astran' ),
					'صيغ الاسم في البيانات المنظمة',
					__( 'Adds alternateName on Person and WebSite. Fill “Other spellings” in Name & profiles to override the built-in list.', 'muhamed-ahmed-astran' ),
					'يُضاف alternateName. حقل صيغ الاسم في اللوحة يستبدل القائمة الافتراضية.',
					true,
					$on
				),
				array(
					'id'    => 'person_alt',
					'type'  => 'textarea',
					'title' => __( 'Other spellings of your name', 'muhamed-ahmed-astran' ) . ' / صيغ الاسم',
					'desc'  => __( 'One per line. Empty = built-in Arabic/Latin variants for schema only (not shown in About).', 'muhamed-ahmed-astran' ),
				),
				array(
					'type'    => 'subheading',
					'content' => __( 'Arabic chrome', 'muhamed-ahmed-astran' ) . ' / الواجهة العربية',
				),
				mahx_blog_csf_switch(
					'ar_chrome',
					__( 'Translate chrome on /ar/', 'muhamed-ahmed-astran' ),
					'ترجمة عناصر الواجهة في /ar/',
					__( 'Options, gettext and menu titles. Stored map is option mah_ar_chrome_map.', 'muhamed-ahmed-astran' ),
					'الخيارات والقوائم. الخريطة المحفوظة تبقى إن تُرك الحقل فارغاً.',
					true,
					$on
				),
				array(
					'id'    => 'ar_chrome_map',
					'type'  => 'textarea',
					'title' => __( 'Arabic chrome map', 'muhamed-ahmed-astran' ) . ' / خريطة الترجمة',
					'desc'  => __( 'One per line: English | Arabic. Empty keeps the stored map. Saving a filled field replaces it.', 'muhamed-ahmed-astran' ),
				),
				array(
					'type'    => 'subheading',
					'content' => __( 'Portfolio', 'muhamed-ahmed-astran' ) . ' / المعرض',
				),
				mahx_blog_csf_switch(
					'work_public',
					__( 'Public project pages', 'muhamed-ahmed-astran' ),
					'صفحات مشاريع عامة',
					__( 'Off = cards on the home stage only; /work redirects home. Matches the live site.', 'muhamed-ahmed-astran' ),
					'إيقاف = بطاقات في الرئيسية فقط.',
					false,
					$on
				),
				mahx_blog_csf_switch(
					'work_show_link',
					__( '“View project” link on cards', 'muhamed-ahmed-astran' ),
					'رابط «عرض المشروع» على البطاقات',
					'',
					'',
					false,
					$on
				),
				array(
					'type'    => 'subheading',
					'content' => __( 'Reviews', 'muhamed-ahmed-astran' ) . ' / التقييمات',
				),
				mahx_blog_csf_switch(
					'rev_hide_upwork',
					__( 'Hide Upwork profile links', 'muhamed-ahmed-astran' ),
					'إخفاء روابط Upwork',
					__( 'Review text stays. Profile URLs are stripped from options, HTML and JSON-LD.', 'muhamed-ahmed-astran' ),
					'النص يبقى. الروابط تُحذف.',
					true,
					$on
				),
				mahx_blog_csf_switch(
					'rev_interleave',
					__( 'Interleave review sources', 'muhamed-ahmed-astran' ),
					'توزيع مصادر التقييمات',
					__( 'Avoids a run of Trustpilot (or any one source) in the marquee.', 'muhamed-ahmed-astran' ),
					'يمنع تتابع تقييمات من مصدر واحد.',
					true,
					$on
				),
				array(
					'type'    => 'subheading',
					'content' => __( 'Watermark', 'muhamed-ahmed-astran' ) . ' / العلامة المائية',
				),
				mahx_blog_csf_switch(
					'wm_enable',
					__( 'Admin watermark REST', 'muhamed-ahmed-astran' ),
					'REST للعلامة المائية للمدير',
					__( 'mah/v1/wm-probe, wm-apply, wm-restore. Manage-options only.', 'muhamed-ahmed-astran' ),
					'للمدير فقط.',
					true,
					$on
				),
				array(
					'id'      => 'wm_text',
					'type'    => 'text',
					'title'   => __( 'Watermark text', 'muhamed-ahmed-astran' ) . ' / نص العلامة',
					'default' => 'muhamedahmed.com',
				),
			),
		)
	);
}
if ( class_exists( 'CSF' ) ) {
	mahx_csf_theme_sections();
} else {
	add_action( 'after_setup_theme', 'mahx_csf_theme_sections', 0 );
}

/**
 * Prefer Codestar panel values for blog chrome switches.
 *
 * @param mixed  $value Theme-mod / schema value.
 * @param string $key   Option key.
 * @return mixed
 */
function mahx_panel_blog_chrome_override( $value, $key ) {
	static $keys = null;
	if ( null === $keys ) {
		$keys = array_fill_keys( mahx_panel_bool_keys(), true );
	}
	if ( ! isset( $keys[ $key ] ) ) {
		return $value;
	}
	$o = get_option( 'muhamed_ahmed_panel' );
	if ( ! is_array( $o ) || ! array_key_exists( $key, $o ) ) {
		return $value;
	}
	$raw = $o[ $key ];
	if ( '' === $raw || null === $raw ) {
		return $value;
	}
	if ( is_bool( $raw ) ) {
		return $raw;
	}
	if ( is_numeric( $raw ) ) {
		return 0 !== (int) $raw;
	}
	$s = strtolower( trim( (string) $raw ) );
	return ! in_array( $s, array( '0', 'false', 'off', 'no' ), true );
}

add_filter( 'muhamed_ahmed_astran_option', 'mahx_panel_blog_chrome_override', 20, 2 );

add_filter(
	'muhamed_ahmed_astran_option',
	static function ( $value, $key ) {
		if ( 'abp_portrait' !== $key ) {
			return $value;
		}
		$o = get_option( 'muhamed_ahmed_panel' );
		if ( ! is_array( $o ) || empty( $o['abp_portrait'] ) ) {
			return $value;
		}
		$p  = $o['abp_portrait'];
		$id = is_array( $p ) ? (int) ( isset( $p['id'] ) ? $p['id'] : 0 ) : (int) $p;
		return $id ? $id : $value;
	},
	20,
	2
);

add_filter(
	'muhamed_ahmed_astran_option',
	static function ( $value, $key ) {
		if ( ! in_array( $key, array( 'wm_text', 'person_alt', 'ar_chrome_map' ), true ) ) {
			return $value;
		}
		$o = get_option( 'muhamed_ahmed_panel' );
		if ( ! is_array( $o ) || ! array_key_exists( $key, $o ) ) {
			return $value;
		}
		$raw = $o[ $key ];
		if ( '' === $raw || null === $raw ) {
			return $value;
		}
		if ( is_array( $raw ) && isset( $raw['url'] ) ) {
			return $value;
		}
		return is_string( $raw ) ? $raw : $value;
	},
	20,
	2
);
