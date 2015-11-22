<?php

namespace PressGang;

class Title {

    /**
     * __construct
     *
     */
    public function __construct() {
        add_filter('wp_title', array($this, 'filter_wp_title'), 10, 3);
    }

    /**
     * Makes some changes to the <title> tag, by filtering the output of wp_title().
     *
     * If we have a site description and we're viewing the home page or a blog posts
     * page (when using a static front page), then we will add the site description.
     *
     * If we're viewing a search result, then we're going to recreate the title entirely.
     * We're going to add page numbers to all titles as well, to the middle of a search
     * result title and the end of all other titles.
     *
     * The site title also gets added to all titles.
     *
     * @param string $title Title generated by wp_title()
     * @param string $separator The separator passed to wp_title().
     * @return string The new title, ready for the <title> tag.
     */
    public function filter_wp_title($title, $separator, $location)
    {
        // TODO title should not be longer than 70 chars!

        // $title = trim($title);
        $separator = trim($separator);
        $separator = $separator ? " {$separator} " : ' ';

        // not on feed
        if (is_feed()) {
            return $title;
        }

        global $paged, $page;

        if (is_search()) {
            // recreate title on search pages
            $title = sprintf("%s '%s'", __("Search", THEMENAME), get_search_query());
            // add page no.
            if ($paged >= 2) {
                $title .= $separator . $paged;
            }
            // add the site name to the end
            $title .= $separator . get_bloginfo('name', 'display');

            return $title;
        }

        // other pages

        // setup according to location of site name
        switch (strtolower($location)) {
            case 'left' :
                $title = get_bloginfo('name', 'display') . $title;
                break;
            case 'right' :
                $title .= get_bloginfo('name', 'display');
                break;
            default:
                $title .= $separator . get_bloginfo('name', 'display');
        }

        // on the front page, add the description
        $site_description = get_bloginfo('description', 'display');
        if ($site_description && (is_home() || is_front_page())) {
            $title .=  $separator . sprintf('%s', $site_description);
        }

        // add a page number if necessary
        if ($paged >= 2 || $page >= 2) {
            $title .= $separator . sprintf('%s', max($paged, $page));
        }

        return $title;
    }
}

new Title();

