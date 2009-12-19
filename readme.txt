===Plugin Name===
Taxonomy List Shortcode

Contributors: mfields
Donate link: http://mfields.org/donate/
Tags: taxonomy, tag, category, index, list, custom
Requires at least: 2.8.6
Tested up to: 2.9
Stable tag: trunk

The Taxonomy List Shortcode plugin adds a shortcode to your WordPress installation which enables you to display multiple unordered lists containing every term of a given taxonomy.

==Description==

The Taxonomy List Shortcode plugin adds a [shortcode](http://codex.wordpress.org/Shortcode_API) to your [WordPress](http://wordpress.org/) installation which enables you to display multiple unordered lists containing every term of a given [taxonomy](http://codex.wordpress.org/WordPress_Taxonomy ).

==Installation==
1. [Download](http://wordpress.org/extend/plugins/taxonomy-list-shortcode/)
1. Unzip the package and upload to your /wp-content/plugins/ directory.
1. Log into WordPress and navigate to the "Plugins" panel.
1. Activate the plugin.

==Usage==

Adding `[taxonomy-list]` to your post content will render a one column, unordered list of every tag associated to a post. This list is styled with custom css which is printed in the head section of the generated xhtml document. Please note: this css will only be printed on pages/posts where you have used the shortcode. If no shortcode is present in the post-content field, no css will be printed.

==Supported Parameters==

The `[taxonomy-list]` shortcode currently supports two parameters, one that accepts the taxonomy name and another that defines the number of columns to be displayed.

1. = Taxonomy Name = To define the taxonomy that you would like to list terms from, you will need to specifiy the name of the taxonomy in the `tax` parameter: `[taxonomy-list tax="category"]`. In an "out-of-the-box" installation of WordPress, the following taxonomies will be recognized: `post_tag`, `category`, and `link_category`. If you have defined custom taxonomies through use of a plugin or your own hacker-skillz, you can use the name of this taonomy as well: `[taxonomy-list tax="fishy-squiggles"]`. If the taxonomy cannot be located, due to a spelling error or missing code, the `[taxonomy-list]` shortcode will return an empty string.
1. = Columns = To define the number of columns that the `[taxonomy-list]` shortcode generates, you will want to use the `cols` parameter. This parameter will accept an integer from 1 - 5. If this parameter is left empty, or a value outside of it's range is defined, it will default to three columns.

==Examples==

1. = Post Tags (default) = `[taxonomy-list]` - Display a three column, list of Post Tags in alphetbetical order. Although this is the default usage, it is synonymous with `[taxonomy-list tax="post_tag" cols="3"]`.
1. = Post Categories = `[taxonomy-list tax='category']` - Display a three column, unordered list of Post Categories.
1. = Link Categories = `[taxonomy-list tax='link_category']` - Display a three column, unordered list of Link Categories.
1. = Custom Taxonomy = `[taxonomy-list tax="fishy-squiggles"]` - Display a three column, unordered list of the [custom taxonomy](http://justintadlock.com/archives/2009/05/06/custom-taxonomies-in-wordpress-28) "fishy-squiggles".
1. = Two Columns = `[taxonomy-list cols="2"]` - Display a two, horizontally-aligned unordered lists of Post Tags.
1. = Five Columns (maximum) = `[taxonomy-list cols="5"]` - Display a 5, horizontally-aligned unordered lists of Post Tags.
1. = Custom Taxonomy with 3 Columns = `[taxonomy-list tax="fishy-squiggles" cols="5"]` - Display a 5, horizontally-aligned unordered lists of the custom taxonomy "fishy-squiggles".


= The XHTML + CSS was Tested in the Following User Agents =
* Windows XP: Internet Explorer 6
* Windows XP: FireFox 3.5.3
* Windows XP: Opera 9.26
* Windows XP: Safari 4.0.3


= This Plugin has been tested with the Following WordPress Themes =
* WordPress Classic
* [Kubrick](http://binarybonsai.com/wordpress/kubrick/)
* [Platypus Theme](http://platypus-theme.com/)
* [Hybrid](http://themehybrid.com/)

==Changelog==

= 0.4 =
* pad_counts is now set to true by default for get_terms();

= 0.3 =
* PHP Bugfix with empty array being passed to array_chunk().

= 0.2 =
* Added the `sanitize_cols()` method.

= 0.1 =
* Original Release - Works With: wp 2.8.6 + wp 2.9 beta 2.
