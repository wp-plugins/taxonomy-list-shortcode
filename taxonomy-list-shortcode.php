<?php
/*
Plugin Name: Taxonomy List Shortcode
Plugin URI: http://mfields.org/wordpress/plugins/taxonomy-list-shortcode/
Description: Defines a shortcode which prints an unordered list for taxonomies.
Version: 0.7
Author: Michael Fields
Author URI: http://mfields.org/
Copyright 2009-2010  Michael Fields  michael@mfields.org
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License version 2 as published by
the Free Software Foundation.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if( !function_exists( 'mf_taxonomy_list_activate' ) ) {
	/*
	* Called when user activates this plugin.
	* Adds a custom setting to the options table.
	* @uses add_option
	* @return void
	*/
	function mf_taxonomy_list_activate() {
		add_option( 'mf_taxonomy_list_enable_css', 1 );
	}
}
if( !function_exists( 'mf_taxonomy_list_deactivate' ) ) {
	/*
	* Called when user deactivates this plugin.
	* Deletes custom settings from the options table.
	* @uses delete_option
	* @return void
	*/
	function mf_taxonomy_list_deactivate() {
		delete_option( 'mf_taxonomy_list_enable_css' );
	}
}
if( !function_exists( 'mf_taxonomy_list_admin_menu' ) ) {
	/*
	* Add a checkbox to the WordPress "Miscellaneous" Administration Panel
	* Which allows the user to enable/disable css printing in the head of 
	* each frontend html document.
	* @uses add_settings_field
	* @return void
	*/
	function mf_taxonomy_list_admin_menu() {
		add_settings_field( 'mf_taxonomy_list_enable_css', 'Enable CSS for Taxonomy List Shortcode Plugin', 'mf_taxonomy_list_enable_css_field', 'misc' );
	}
}
if( !function_exists( 'mf_taxonomy_list_init' ) ) {
	/*
	* This function is executed during the "admin_init" action.
	* Registers custom setting for "Miscellaneous" Administration Panel.
	* @uses register_setting
	* @return void
	*/
	function mf_taxonomy_list_init() {
		register_setting( 'misc', 'mf_taxonomy_list_enable_css', 'mf_taxonomy_list_bool' );
	}
}
if( !function_exists( 'mf_taxonomy_list_bool' ) ) {
	/*
	* Always return a Boolean value.
	* @param $bool bool
	* @return bool
	*/
	function mf_taxonomy_list_bool( $bool ) {
		return ( $bool == 1 ) ? 1 : 0;
	}
}
if( !function_exists( 'mf_taxonomy_list_enable_css_field' ) ) {
	/*
	* Prints html input for custom setting on "Miscellaneous" Administation Panel.
	* @uses checked
	* @uses get_option
	* @return void
	*/
	function mf_taxonomy_list_enable_css_field() {
		?>
		<input name="mf_taxonomy_list_enable_css" type="checkbox" id="mf_taxonomy_list_enable_css" value="1"<?php checked( '1', get_option( 'mf_taxonomy_list_enable_css' ) ); ?> />
		<?php
	}
}
if( !function_exists( 'mf_taxonomy_list_shortcode' ) ) {
	/**
	* Process the Shortcode.
	* @uses shortcode_atts
	* @uses get_terms
	* @uses mf_taxonomy_list_sanitize_cols
	* @uses is_taxonomy
	* @uses get_terms
	* @uses esc_url
	* @uses get_term_link
	* @param array $atts
	* @return string: unordered list(s) on sucess - empty string on failure.
	*/
	function mf_taxonomy_list_shortcode( $atts = array() ) {
		$o = ''; /* "Output" */
		$term_args = array();
		
		$defaults = array(
			'tax' => 'post_tag',
			'cols' => 3,
			'args' => '',
			'background' => 'fff',
			'color' => '000',
			'show_counts' => 1
			);
		
		extract( shortcode_atts( $defaults, $atts ) );
		$cols = mf_taxonomy_list_sanitize_cols( $cols );
		
		/* Convert the $args string into an array. */
		parse_str( html_entity_decode( $args ), $term_args );
		
		/* Set value for "pad_counts" to true if user did not specify. */
		if( !array_key_exists( 'pad_counts', $term_args ) )
			$term_args['pad_counts'] = true;
		
		/* The user-defined taxonomy does not exists - return an empty string. */
		if( !is_taxonomy( $tax ) )
			return $o;
		
		/* Get the terms for the given taxonomy. */
		$terms = get_terms( $tax, $term_args );
		
		/* Split the array into smaller pieces + generate html to display lists. */
		if( is_array( $terms ) && count( $terms ) > 0 ) {
			$chunked = array_chunk( $terms, ceil( count( $terms ) / $cols ) );
			$o.= "\n\t" . '<div class="mf_taxonomy_list">';
			foreach( $chunked as $k => $column ) {
				$o.= "\n\t" . '<ul class="mf_taxonomy_column mf_cols_' . $cols . '">';
				foreach( $column as $term ) {
					$url = esc_url( get_term_link( $term, $tax ) );
					$count = intval( $term->count );
					$style = '';
					$style.= ( $background != 'fff' ) ? ' background:#' . $background . ';' : '';
					$style.= ( $color != '000' ) ? ' color:#' . $color . ';' : '';
					$style = ( !empty( $style ) ) ? ' style="' . trim( $style ) . '"' : '';
					
					$li_class = ( $show_counts ) ? ' class="has-quantity"' : '';
					$quantity = ( $show_counts ) ? ' <span' . $style . ' class="quantity">' . $count . '</span>' : '';
					
					$o.= "\n\t\t" . '<li' . $li_class . $style . '><a' . $style . ' href="' . $url . '">' . $term->name . '</a>' . $quantity . '</li>';
				}
				$o.=  "\n\t" . '</ul>';
			}
			$o.=  "\n\t" . '<div class="clear"></div>';
			$o.=  "\n\t" . '</div>';
		}
		$o = "\n\t" . '<!-- START mf-taxonomy-list-plugin -->' . $o . "\n\t" . '<!-- END mf-taxonomy-list-plugin -->' . "\n" ;
		return $o;
	}
}
if( !function_exists( 'mf_taxonomy_list_css' ) ) {
	/*
	* Print html style tag with pre-defined styles.
	* @uses mf_taxonomy_list_bool
	* @uses get_option
	* @return void
	*/
	function mf_taxonomy_list_css() {
		$print_css = mf_taxonomy_list_bool( get_option( 'mf_taxonomy_list_enable_css' ) );
		if( $print_css === 1 ) {
			$o = <<<EOF
		<style type="text/css">
		html>body .entry ul.mf_taxonomy_column { /* Reset for the Default Theme. */
			margin: 0px;
			padding: 0px;
			list-style-type: none;
			padding-left: 0px;
			text-indent: 0px;
			}
		ul.mf_taxonomy_column,
		.entry ul.mf_taxonomy_column {
			float: left;
			margin: 0;
			padding: 0 0 1em;
			list-style-type: none;
			list-style-position: outside;
			}
			.mf_cols_1{ width:99%; }
			.mf_cols_2{ width:49.5%; }
			.mf_cols_3{ width:33%; }
			.mf_cols_4{ width:24.75%; }
			.mf_cols_5{ width:19.77%; }
			
		.entry ul.mf_taxonomy_column li:before {
			content: "";
			}
		.mf_taxonomy_column li,
		.entry ul.mf_taxonomy_column li {
			list-style: none, outside;
			position: relative;
			height: 1.5em;
			z-index: 0;
			background: #fff;
			margin: 0 1em .4em 0;
			}
		.mf_taxonomy_column li.has-quantity,
		.entry ul.mf_taxonomy_column li.has-quantity {
			border-bottom: 1px dotted #888;
			}
		.mf_taxonomy_column a,
		.mf_taxonomy_column .quantity {
			position:absolute;
			bottom: -0.2em;
			line-height: 1em;
			background: #fff;
			z-index:10;
			}
		.mf_taxonomy_column a {
			display: block;
			left:0;
			padding-right: 0.3em;
			text-decoration: none;
			}
		.mf_taxonomy_column .quantity {
			display: block;
			right:0;
			padding-left: 0.3em;
			}
		.mf_taxonomy_list .clear {
			clear:both;
			}
		</style>
EOF;
		print '<!-- mf-taxonomy-list -->' . "\n" . preg_replace( '/\s+/', ' ', $o );
		}
	}
}
if( !function_exists( 'mf_taxonomy_list_sanitize_cols' ) ) {
	/**
	* Returns an integer between 1 and 5.
	* @param $n int.
	* @returns int.
	*/
	function mf_taxonomy_list_sanitize_cols( $n = 1 ) {
		$min = 1;
		$max = 5;
		$n = intval( $n );
		if( $n === $min || $n === $max )
			return $n;
		if( $n > $min && $n < $max )
			return $n;
		return $min;
	}
}
if( !function_exists( 'pr' ) ) {
	/*
	* Recursively print stuff wrapped in a pre tag.
	* @param $var mixed - just about anything ;)
	* @return void
	*/
	function pr( $var ) {
		print '<pre>' . print_r( $var, true ) . '</pre>';
	}
}
/* Hook into WordPress */
add_shortcode( 'taxonomy-list', 'mf_taxonomy_list_shortcode' );
add_action( 'wp_head', 'mf_taxonomy_list_css' );
add_action( 'admin_init', 'mf_taxonomy_list_init' );
add_action( 'admin_menu', 'mf_taxonomy_list_admin_menu' );
register_activation_hook( __FILE__, 'mf_taxonomy_list_activate' );
register_deactivation_hook( __FILE__, 'mf_taxonomy_list_deactivate' );
?>