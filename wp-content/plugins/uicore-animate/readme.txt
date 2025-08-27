=== UiCore Animate - Free Animations, Transitions, and Interactions Addon for Elementor & Gutenberg blocks ===
Contributors: uicore
Tags: elementor, gutenberg, blocks, animation, scroll animations
Requires at least: 4.6
Requires PHP: 7.4
Tested up to: 6.8.1
Stable tag: 2.1.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

UiCore Animate adds page transitions, smooth scroll, and engaging animations to Elementor and Gutenberg blocks, for smoother, engaging experiences.

== Description ==

=== Unleash the Power of Animations in Elementor and Gutenberg ===

UiCore Animate is the ultimate tool for creating stunning animations in Elementor and Gutenberg, giving you the ability to easily add eye-catching effects, smooth transitions, and dynamic interactions to your WordPress site.

== Key Features of UiCore Animate ==

Quick & Easy Animations: Say goodbye to time-consuming manual animations. With UiCore Animate, you can effortlessly add animations to any widget in Elementor or Gutenberg in just a few clicks, saving you time and improving design quality.

Animate Multiple Widgets at Once: Apply animations to multiple widgets simultaneously, ensuring consistency and cohesion across your entire site.

Scroll-Based Animations & Entrance Effects: Create captivating animations that trigger as elements come into view or during scrolling. Control the timing, style, and delay for a fully immersive user experience.

Wide Range of Animation Presets: Choose from a diverse set of animation presets to replace default animations in Elementor and Gutenberg. Whether you prefer playful or sleek effects, UiCore Animate has the perfect option for you.

Smooth Scrolling Navigation: Enhance your site's user journey with smooth scroll capabilities, paired seamlessly with animations for a captivating browsing experience.

Page Transitions & Preloaders: Craft engaging user journeys with polished page transitions and customizable preloaders, maintaining a consistent and professional look.

Creative Effects: Extend your widgets with unique effects like fluid gradients, floating animations, sticky reveals on scroll, and animated borders, making your design truly stand out.

== Boost Engagement & Improve User Experience ==

UiCore Animate is designed to enhance the visual appeal of your website while improving user interaction. Whether you're building a portfolio, a business website, or a blog, this plugin is perfect for creating memorable experiences that keep visitors engaged.

== Installation ==

Go to the WordPress plugin directory and search for "UiCore Animate."

Click "Install Now" and then activate the plugin.

Alternatively, upload the plugin files to your server at wp-content/plugins and activate from the WordPress dashboard.

== How to add aniamtions to any gutenberg block ==
[youtube https://www.youtube.com/watch?v=tMsAp68d530]

== How to use Animate Controller in Elementor ==
[youtube https://www.youtube.com/watch?v=L6UddkUrmk8]

== Frequently Asked Questions ==

= Does UiCore Animate affect my website performance? =
No. UiCore Animate enhances existing CSS animations, ensuring minimal impact on your website's performance.

= Can I use UiCore Animate with any theme? =
Yes, UiCore Animate works with any theme that supports Elementor or Gutenberg.

= Is this a standalone plugin? =
No. UiCore Animate requires either Elementor or Gutenberg to function.

= How do I apply animations to widgets? =
Simply select the widget in Elementor or Gutenberg, go to the UiCore Animate settings, and choose your desired animation. You can adjust the timing, delay, and other settings as needed.

= Can I use custom animations with UiCore Animate? =
Yes, you can customize animations using the settings provided in the plugin. Additionally, you can combine built-in presets with your own CSS for even more unique effects.

= Does UiCore Animate support responsive design? =
Absolutely. UiCore Animate ensures that your animations look great on all devices, including desktops, tablets, and mobile phones.

= How can I create scroll-based animations? =
UiCore Animate allows you to trigger animations when elements come into view during scrolling. Simply select the scroll animation option and configure the settings to control when and how the animation is triggered.

= Is UiCore Animate beginner-friendly? =
Yes, UiCore Animate is designed to be easy to use, even for beginners. The interface is user-friendly, and no coding skills are required to create impressive animations.




== Changelog ==
= 2.1.7 =
* FIX - Animation delay not working for gutenberg blocks
* NEW – Added an option to run the preloader animation only on a user's first visit to the site.
* FIX – Resolved issue where the "Circle Spinner" preloader animation was missing.
* UPD – Preloaders can now be used independently of page transitions.
= 2.1.6 =
* FIX - Most animations were not working properly without Uicore Framework
* FIX - Anchor links with Scroll Smooth have a small jump during the animation process
* FIX - Split text adds texts in weird places on Elementor Editor
* FIX - Variations products can't be added to the cart if a split text widget is on the page
= 2.1.5 =
* FIX - Preloader text stuck at editor screen
* FIX - Page anchor hard scroll with Smooth Scroll enabled
* FIX - Split text was not working if Elementor's Optimized Markup experiment was active
* FIX - Fluid gradient border-radius bug
* UPD - Small typos
* UPD - Disabled page transition animations in the editor screen
* TWEAK - Now using scroll-timeline script locally due to GDPR concerns
= 2.1.4 =
* FIX - PHP 8.x warnings
* FIX - Minor small bugs fixes
= 2.1.3 =
* FIX - Gutenberg 3rd party plugins compatibility
* UPD - Include Scroll based animations polyfill only when needed
= 2.1.2 =
* NEW - Gutenberg Scroll based animation preview in editor
= 2.1.1 =
* FIX - Widgets Area blocks loading issue
= 2.1.0 =
* UPD - Added Scroll based animation js fallback for browsers that does not suport css animation timeline
* FIX - Elementor Optimized Control loading bugs
= 2.0.6 =
* FIX - Gutenberg Blocks Animations fix for 3rd party blocks
= 2.0.5 =
* FIX - Elementor Floating animation when used without UiCore Framework
= 2.0.4 =
* UPD - Default animations fallback
= 2.0.3 =
* FIX - Missing scripts when not used with UiCore Framework
= 2.0.2 =
* FIX - Elementor On Scroll Effects script missing
* NEW - Added 5 New Blur Animations
= 2.0.1 =
* FIX - Legacy animations not working (added support for older elementor animations)
= 2.0.0 =
* NEW - Added support for Gutenberg Blocks
* NEW - Added Scroll Based Animations
= 1.0.11 =
* FIX - Fixed Submenu bug where clicking would open the page, instead of the submenu, when some page transition animations were enabled.
* FIX - Fixed Animated Borders bug that wasn't working with Advanced Post Grid and Container widgets.
* UPD - Improved Split animations viewport trigger method.
= 1.0.10 =
* FIX - Split Text Animation display issues
= 1.0.9 =
* FIX - _border_border warning caused by Disable Optimized Control Loading experiment
* NEW - Added Animated Border support for Icon List individual items (from UiCore Elements)
* NEW - Added Animated Border support for Advanced Post Grid individual items (from UiCore Elements)
* UPD - Minor Animated Border improvements
= 1.0.8 =
* NEW - Added Scroll Effects on containers (contains multiple effects like Sticky Scale, Mask Reveal and more)
* NEW - Added Animated Border in all border controlls (contains hover glow,rotate, multicolor rotate and more)
= 1.0.7 =
* FIX - Fade In Page transition fix.
= 1.0.6 =
* FIX - Animations Controller ui issues caused by selecting multiple widgets
= 1.0.5 =
* NEW - Added multiple page transitions
* NEW - Added multiple page transitions preloaders
= 1.0.4 =
* FIX - Elementor Pro Compatibility fix
* FIX - Removed console log from split animation
= 1.0.3 =
* NEW - Added Page Transitions
* NEW - Added multiple split text animations (Cut, Blur and more)
= 1.0.2 =
* NEW - Added Smooth Scroll
* NEW - Added the option to remove the Animation Controller from editor
* NEW - Added the option to animate the columns
* FIX - Fixed the Animation duration
* FIX - Fixed Fluid Background Animations extra color bug
= 1.0.1 =
* NEW - Added Split Animations for headline and text widget
* NEW - Added Fluid Gradient Background for section, columns and containers
* NEW - Added Floating animations on all widgets
* UPD - Improved Animations Controller UI
= 1.0.0 =
* Initial release