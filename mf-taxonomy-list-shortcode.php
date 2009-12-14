<?php
/*
Plugin Name: Taxonomy List Shortcode
Plugin URI: http://mfields.org/wordpress/plugins/
Description: Defines a shortcode which prints an unordered list for taxonomies.
Version: 0.2
Author: Michael Fields
Author URI: http://mfields.org/

Copyright 2009  Michael Fields  michael@mfields.org

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

$mf_taxonmy_list_shortcode = new mf_taxonmy_list_shortcode();

class mf_taxonmy_list_shortcode {
	
	var $default_cols = 3;
	var $shortcode_name = 'taxonomy-list';
	var $default_taxonomy = 'post_tag';
	var $posts_content = '';
	var $pattern = '<!-- mf-taxonomy-list -->';
	
	/* PHP 4 Constructor */
	function mf_taxonmy_list_shortcode() {
		add_shortcode( $this->shortcode_name, array( &$this, 'shortcode' ) );
		add_action( 'wp_head', array( &$this, 'css' ) );
		add_action( 'wp_head', array( &$this, 'include_css' ) );
	}
	
	/**
	Process the Shortcode
	@param array $atts
	@returns string an onordered list.
	*/
	function shortcode( $atts = array() ) {
		$o = '';
		
		$defaults = array(
			'tax' => $this->default_taxonomy,
			'cols' => $this->default_cols,
			'args' => ''
			);
		
		extract( shortcode_atts( $defaults, $atts ) );
		
		$cols = $this->sanitize_cols( $cols );
		
		if( !is_taxonomy( $tax ) )
			return $o;
		
		$terms = get_terms( $tax, $args );
		
		if( is_wp_error( $terms ) || !is_array( $terms ) )
			return $o;
		
		$chunked = array_chunk( $terms, ceil( count( $terms ) / $cols ) );
		
		$o.= "\n\n\t" . $this->pattern;
		foreach( $chunked as $k => $column ) {
			$o.= "\n\t" . '<ul class="mf_taxonomy_column mf_cols_' . $cols . '">';
			foreach( $column as $term ) {
				$url = esc_url( get_term_link( $term, $tax ) );
				$o.= "\n\t\t" . '<li><a href="' . $url . '">' . $term->name . '</a> <span class="quantity">' . $term->count . '</span></li>';
			}
			$o.=  "\n\t" . '</ul>';
		}
		$o.=  "\n\t" . '<div class="clear"></div>';
		
		return $o;
	}
	
	/**
	Searches the post content to determine if the css block should be included in wp_head.
	@returns bool
	*/
	function include_css() {
		global $posts;
		$o = false;
		if( !empty( $posts ) )
			foreach( $posts as $post )
				$this->posts_content.= apply_filters( 'the_content', $post->post_content );
		
		if( !empty( $this->posts_content ) )
			if( strstr( $this->posts_content, $this->pattern ) )
				$o = true;
		
		return $o;
	}
	
	/* Style the Shortcode's Output */
	function css() {
		$o = <<<EOF
	{$this->pattern}
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
		padding: 0 0 2em;
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
		border-bottom: 1px dotted #888;
		height: 1.5em;
		z-index: 0;
		background: #fff;
		margin: 0 1em .4em 0;
		}
	.mf_taxonomy_column a,
	.quantity {
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
	.quantity {
		display: block;
		right:0;
		padding-left: 0.3em;
		}
	</style>
EOF;
		print ( $this->include_css() ) ? $o : '';
	}
	
	function sanitize_cols( $n = 1 ) {
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
?>