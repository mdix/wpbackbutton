=== Plugin Name ===
Contributors: Marc Dix
Donate link: http://no-donate-link
Tags: navigation
Requires at least: 3.3.1
Tested up to: 3.3.1
Stable tag: 0.2

Enables a back button that reverse engineers the users navigation to enable a simple "back" button on every page.

== Description ==
wpbackbutton adds a "Back" button to your Wordpress blog which behaves like the javascript history.back() method. In contrast to history.back() it, of course, doesn't need Javascript & makes it possible to have a working 'Back' button even if the user comes to your blog via direct link (the "Back" button then redirects to the frontpage).

== Installation ==
Install & activate plugin via wordpress backend.
Add `<?php echo do_shortcode('[renderBackButton]'); ?>` to your template (where you want to see the "Back" button).

== Frequently Asked Questions ==

= I can't see the Back button on my Frontpage? =

The "Back" button is only displayed if there's a page to navigate back. Because there's no page to navigate back to on the frontpage, you can't see the button there, even if you included it.

= How exactly does wpbackbutton work? =

When a user enters a blog, wpbackbutton sets a cookie with the current URL and (if the current URL is not the frontpage of your blog) the frontpage URL. If the user followed a direct link he'll get redirected to the frontpage when pressing 'Back', because there's no history for that user. When starting with the frontpage and navigating trough the blog, wpbackbutton saves the navigation history. When the "Back" button is clicked, the element, that has been viewed just before the current element, is loaded. This happens until the user is on the frontpage. Note: It's NOT neccessary to use the "Back" button in order to have a clean history. If the user navigates to page A, then to page B and after that back to page A (with a link, instead of pressing "Back") the history acts as if the user pressed the "Back" button.

Some navigation cases (different chars are different pages, where A is frontpage):

A -> B -> C -> D would reverse navigate C -> B -> A

A -> B -> C -> D -> C would reverse navigate B -> A

A -> B -> C -> D -> E -> C would reverse navigate E -> D -> C -> B -> A

A -> B -> C -> D -> A would clear the history (mainpage has no back button)

= Where should I add the Back button? =

I suppose that it's no good idea to add the button into a loop, because you'd have it below every post. Instead you should add it directly after / before the loop, so it's displayed once or twice (above and below the content). This typically takes place in the single.php / page.php / category.php / ... files of your template (wherever a loop gets executed).

== Screenshots ==

== Changelog ==

= 0.2 =
first working, but untested version

= 0.1 =
Initial creation.
