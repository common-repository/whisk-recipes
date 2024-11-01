=== Whisk Recipes ===
Contributors: whiskstudio, mihdan, katyatina
Tags: whisk, recipes, recipe builder, recipe, ingredients, shoppable recipes, widget, whisk studio, food, cooking
Requires at least: 5.0
Tested up to: 5.7
Requires PHP: 5.6.20
Stable tag: 1.2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Whisk Recipes for WordPress is a first recipe plugin with seamless integration into Whisk ecosystem. It allows you to easily add recipes to your website and have them instantly saved to Whisk platform. Your friends won’t have to print out recipes from your website and worry about buying the right amount of ingredients.

== Description ==

<strong><em>Feel free to [report](mailto:wordpress@whisk.com) any issues you find or share your opinion about how we can improve this plugin. Any feedback is much appreciated!</em></strong>

Whisk Recipes for WordPress is a free fully-featured recipe plugin, developed for creators and food-lovers. Whisk features are naturally integrated into the plugin and allow your visitors to instantly save recipes and create shopping lists right from recipe cards on your website! One click and a recipe is saved to Whisk ecosystem, where it can be viewed, analyzed and added to shopping list in one of our mobile apps, available on [Google Play](https://getwhisk.com/download-android), [App Store](https://getwhisk.com/download-ios), [Galaxy Store](https://galaxy.store/whisk) or [Web](http://my.whisk.com/). Your friends won’t have to print out recipes from your website and worry about buying the right amount of ingredients.

= Features =

Main features of Whisk Recipes for WordPress:

* Easily add recipes into any posts using our **Gutenberg** and **Elementor** blocks
* Seamless integration with **Whisk ecosystem**, including “Save to Whisk” and “Add to Shopping list” features. Allows your visitors to instantly save your recipe to their Whisk account and go shopping with the right amount of ingredients!
* Full support of Schema.org/Recipe JSON-LD microdata, optimised for **Google Recipe search**
* Integrated recipe rating, engineered for speed and durability
* Additional SEO-optimization for you recipes with customizable semantic URLs like site.com/recipes/breakfast/you-awesome-recipe
* Flexible ingredients and instructions, with summary, photos and videos for every step.
* Unlimited amount of **Tips** for each recipe
* Add the right amount of **time** needed to **Prepare and Cook** for your recipes
* Option to add **Groups** for instructions
* **Simple mode** for ingredients, instructions and notes – for those who prefer to keep everything as basic lists.
* Option to show your recipe collection right on the main page
* Upload videos to Media library or just insert links to YouTube or Vimeo
* Include Diets, Avoidance, Cuisines, Meal Types, Cooking Techniques and Tags for each recipe. **Filter them** with any of those taxonomies.
* **Instant Metric/US conversion** for ingredient quantities on front-end
* All recipes are **Print-friendly**
* Responsive design for usage within any theme or device
* Change the look and feel of you recipes with built-in **Customizer** support

Import all your recipes from other plugins like WP Tasty, WP Recipe Maker, WP Ultimate Recipe, EasyRecipe and Zip Recipes without losing precious data.

== Frequently Asked Questions ==

= Is it free? =
Yes, the plugin is completely free and functional without any additional purchases.

= How do I start adding recipes? =
Go to Whisk Recipes section in you admin console and hit Add new. A recipe builder is part of a standard WordPress post editing process, where post content is a recipe description and all the over fields are below. You can select recipe categories, assign tags, add ingredients, instruction steps, etc.

= How can I insert recipes into posts? =
You can add recipes into posts as Gutenberg blocks, Elementor blocks or shortcodes. We encourage everyone to move to Gutenberg as it is the future of WordPress, but understand that sometimes it may be not so easy to do. If you need support for some specific page builder, drop us a line at wordpress@whisk.com or create an Issue on Github.

= What is Semantic URL structure? =
Our plugin allows you to have better URLs for recipes, like *site.com/recipes/breakfast/your-awesome-recipe*, which is good for SEO. You (and bots!) can traverse back and forth and always get what you\'re looking for. For example, *site.com/recipes/* will show all the recipes you have and *site.com/recipes/breakfast/* will show only recipes for breakfast. If enabled, you won\'t have to insert your recipes into posts because recipes would become posts themselves.

= Do you offer any support? =
Yes, we provide limited support via support forum and e-mail. You can always drop us a line at wordpress@whisk.com and share your ideas and suggestions about how we can improve the plugin. No email is left unanswered!

== Changelog ==

= 1.2.0 (03.06.2021) =
* New option: restricted grocers for Whisk Modal
* Fix: minor backend errors

= 1.1.9 (22.04.2021) =
* CSS improvements for better compatibility with themes
* Fix: do not show calories icon on archive page when no calories are available
* Fix: error with 0 ingredient amounts

= 1.1.8 (22.04.2021) =
* Do not add 1 as a default number for ingredients without amounts
* Fix: Remove duplicate Comments title from templates

= 1.1.7 (16.04.2021) =
* Remove Whisk Studio API support (Whisk Studio is discontinued)

= 1.1.6 (16.03.2021) =
* Import videos from Whisk API
* Fixed bug with impressions tracking
* Fixed manual servings input

= 1.1.5 (04.03.2021) =
* Added the ability to specify zero for the cook time fields
* Fixed a bug with adding a product to the shopping list
* Fixed a bug with the output of the Nutrition per serving block

= 1.1.4 (03.03.2021) =
* Added parser for linked products
* Fixed bugs with `glycemic_index` shortcode

= 1.1.3 (01.03.2021) =
* Added ability to update instructions images via API
* Added default unit for ingredient
* Added setting for tracking ID
* Added meta tag with Tracking ID.
* Added Monolog logger for debugging
* Added microdata for Recipe ItemList
* Changed algorithm for calculate Recipe scale
* Fixed bug with API key validation
* Fixed bug with fields mapping


= 1.1.2 (16.02.2021) =
* Added setting to disable sending analytics data
* Fixed bug with API key validation
* Fixed bug with Action Scheduler
* Fixed bug with Ingredient amount converter
* Fixed bug with converting decimal to html entity in Ingredient calculator

= 1.1.1 (11.02.2021) =
* Fixed a fatal error on the plugin onboarding page

= 1.1.0 (11.02.2021) =
* Added whisklabs/amounts js library for ingredients calculator
* Added integration_id support for RCP
* Added Sync Now button for manual recipes suncronization from Whisk Recipe Content Platform
* Fixed bug with Action Scheduler
* Fixed bug with updating recipe statuses from Whisk API
* Fixed bug with updating Meal Type/Technique/Cuisine recipe labels from Whisk API
* Fixed bug with updating cook/total time for recipes from Whisk API
* Fixed bug with updating servings for recipes from Whisk API
* Fixed bug with "Add to shopping list" integration for some cases
* Removed image column from Meal Types CPT

= 1.0.6 (29.01.2021) =
* Added custom table for logging sync process
* Fixed bug with php-scoper
* Remove Rollbar from production

= 1.0.5 (25.01.2021) =
* Added custom prefix for requirements via php-scoper
* Added new shortcode `[whisk_ingredients]`
* Added more fields for mapping
* Added `amounts.js` library
* Updated requirements
* Remove unused files

= 1.0.4 (24.01.2021) =
* Fixed bugs
* Added custom prefix for requirements via php-scoper

= 1.0.3 (13.01.2021) =
* Updated plugin assets
* Updated readme.txt
* Updated requirements
* Introduced custom CSS feature
* Hide additional meta box for ingredient
* Fixed tons of bugs

= 1.0.2 (05.01.2021) =
* Fixed bugs

= 1.0.1 (31.12.2020) =
* Fixed bugs

= 1.0.0 (30.12.2020) =
* First plugin release
