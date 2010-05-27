<?php
/*
Plugin Name: Taxonomy List Shortcode
Plugin URI: http://mfields.org/wordpress/plugins/taxonomy-list-shortcode/
Description: Defines a shortcode which prints an unordered list for taxonomies.
Version: 0.8
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

include_once( 'taxonomy-administration-panel.php' );

add_action( 'mfields_taxonomy_administration_panel', 'test' );
function test() {
	$alert = '';
	
	/* Process the Form */
	if( isset( $_POST ) ) {
		$css = ( isset( $_POST['mfields_taxonomy_list_shortcode_enable_css'] ) ) ? 1 : 0;
		$css_human = ( $css ) ? 'true' : 'false';
		$updated = update_option( 'mfields_taxonomy_list_shortcode_enable_css', $css );
	}
	
	$checked = checked( '1', get_option( 'mfields_taxonomy_list_shortcode_enable_css' ), false );
	
	print <<<EOF
		<div class="mfields-taxonomy-plugin">
		<h3>Taxonomy List Shortcode</h3>
		{$alert}
		<form action="" method="post">
			<p><label for="mfields_taxonomy_list_shortcode_enable_css"><input name="mfields_taxonomy_list_shortcode_enable_css" type="checkbox" id="mfields_taxonomy_list_shortcode_enable_css" value="1"{$checked} /> Enable CSS</label></p>
			<input class="button" type="submit" name="mfields_taxonomy_list_shortcode_submit" value="Update Settings">
		</form>
		</div>
EOF;
}


if( !function_exists( 'mf_taxonomy_list_activate' ) ) {
	/*
	* Called when user activates this plugin.
	* Adds a custom setting to the options table.
	* @uses add_option
	* @return void
	*/
	function mf_taxonomy_list_activate() {
		add_option( 'mfields_taxonomy_list_shortcode_enable_css', 1 );
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
		delete_option( 'mfields_taxonomy_list_shortcode_enable_css' );
	}
}
if( !function_exists( 'mfields_sanitize_bool' ) ) {
	/*
	* Always return a Boolean value.
	* @param $bool bool
	* @return bool
	*/
	function mfields_sanitize_bool( $bool ) {
		return ( $bool == 1 ) ? 1 : 0;
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
		global $mfields_taxonomy_shortcode_templates;
		$o = ''; /* "Output" */
		$nav = '';
		$term_args = array();
		$defaults = array(
			'tax' => 'post_tag',
			'cols' => 3,
			'args' => '',
			'background' => 'fff',
			'color' => '000',
			'show_counts' => 1,
			'per_page' => false
			);
		
		extract( shortcode_atts( $defaults, $atts ) );
		$cols = mf_taxonomy_list_sanitize_cols( $cols );
		
		/* Convert the $args string into an array. */
		parse_str( html_entity_decode( $args ), $term_args );
		
		/* Set value for "pad_counts" to true if user did not specify. */
		if( !array_key_exists( 'pad_counts', $term_args ) )
			$term_args['get'] = 'all';
		
		/* Paging arguments for get_terms(). */
		$per_page = absint( $per_page );
		if( $per_page ) {
			$term_args['number'] = $per_page;
			
			$offset = 0;
			
			if( is_paged() )
				$offset = $per_page;
			
			$current_page = (int) get_query_var( 'paged' );
			
			if( !$current_page )
				$current_page = 1;
			
			$offset = $per_page * ( $current_page - 1 );
			
			$term_args['offset'] = $offset;
			
			/* Need to get count for all terms of this taxonomy. */
			$term_count_args = $term_args;
			unset( $term_count_args['number'] );
			unset( $term_count_args['offset'] );
			$total_terms = wp_count_terms( $tax, $term_count_args );
			
			/* HTML for paged navigation */
			if( $offset === 0 ) {
				$prev = null;
			}
			else {
				$href = mfields_paged_taxonomy_link( $current_page - 1 );
				$prev = '<div class="alignleft"><a href="' . $href . '">' . apply_filters( 'mf_taxonomy_list_shortcode_link_prev', 'Previous' ) .' </a></div>';
			}
			if ( ( $offset + $per_page ) >= $total_terms ) {
				$next = null;
			}
			else {
				$href = mfields_paged_taxonomy_link( $current_page + 1 );
				$next = '<div class="alignright"><a href="' . $href . '">' . apply_filters( 'mf_taxonomy_list_shortcode_link_next', 'Next' ) . '</a></div>';
			}
			if( $prev || $next ) {
				$nav = <<<EOF
				<div class="navigation">
					$prev
					$next
				</div>
EOF;
			}
		}
		
		/* The user-defined taxonomy does not exist - return an empty string. */
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
		$o.= $nav;
		$o = "\n\t" . '<!-- START mf-taxonomy-list-plugin -->' . $o . "\n\t" . '<!-- END mf-taxonomy-list-plugin -->' . "\n" ;
		return $o;
	}
}
if( !function_exists( 'mfields_paged_taxonomy_link' ) ) {
	function mfields_paged_taxonomy_link( $pagenum ) {
		global $wp_rewrite;
			
		$request = remove_query_arg( 'paged' );
		$home_root = parse_url( get_home_url() );
		$home_root = ( isset( $home_root['path'] ) ) ? $home_root['path'] : '';
		$home_root = preg_quote( trailingslashit( $home_root ), '|' );
		$request = preg_replace( '|^'. $home_root . '|', '', $request );
		$request = preg_replace( '|^/+|', '', $request );
		
		if ( !$wp_rewrite->using_permalinks() ) {
			$base = trailingslashit( get_bloginfo( 'url' ) );
			if ( $pagenum > 1 ) {
				$request = add_query_arg( 'paged', $pagenum, $base . $request );
			} else {
				$request = $base . $request;
			}
		}
		else {
			$qs_regex = '|\?.*?$|';
			preg_match( $qs_regex, $request, $qs_match );

			if ( !empty( $qs_match[0] ) ) {
				$query_string = $qs_match[0];
				$request = preg_replace( $qs_regex, '', $request );
			} else {
				$query_string = '';
			}

			$request = preg_replace( '|page/\d+/?$|', '', $request);
			$request = preg_replace( '|^index\.php|', '', $request);
			$request = ltrim($request, '/');

			$base = trailingslashit( get_bloginfo( 'url' ) );

			if ( $wp_rewrite->using_index_permalinks() && ( $pagenum > 1 || '' != $request ) )
				$base .= 'index.php/';

			if ( $pagenum > 1 ) {
				$request = ( ( !empty( $request ) ) ? trailingslashit( $request ) : $request ) . user_trailingslashit( 'page/' . $pagenum, 'paged' );
			}

			$request = $base . $request . $query_string;
		}
		
		return $request;
	}
}
if( !function_exists( 'mf_taxonomy_list_css' ) ) {
	/*
	* Print html style tag with pre-defined styles.
	* @uses mfields_sanitize_bool
	* @uses get_option
	* @return void
	*/
	function mf_taxonomy_list_css() {
		$print_css = mfields_sanitize_bool( get_option( 'mfields_taxonomy_list_shortcode_enable_css' ) );
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

if( !function_exists( 'get_home_url' ) ) {
	function get_home_url( $blog_id = null, $path = '', $scheme = null ) {
		$orig_scheme = $scheme;

		if ( !in_array( $scheme, array( 'http', 'https' ) ) )
			$scheme = is_ssl() && !is_admin() ? 'https' : 'http';

		if ( empty( $blog_id ) || !is_multisite() )
			$home = get_option( 'home' );
		else
			$home = get_blog_option( $blog_id, 'home' );

		$url = str_replace( 'http://', "$scheme://", $home );

		if ( !empty( $path ) && is_string( $path ) && strpos( $path, '..' ) === false )
			$url .= '/' . ltrim( $path, '/' );

		return apply_filters( 'home_url', $url, $path, $orig_scheme, $blog_id );
	}
}

/* Hook into WordPress */
add_shortcode( 'taxonomy-list', 'mf_taxonomy_list_shortcode' );
add_action( 'wp_head', 'mf_taxonomy_list_css' );
register_activation_hook( __FILE__, 'mf_taxonomy_list_activate' );
register_deactivation_hook( __FILE__, 'mf_taxonomy_list_deactivate' );
?>