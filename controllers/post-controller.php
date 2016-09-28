<?php

namespace PressGang;

define ('POST_NO_RELATED_POSTS', 5);

require_once 'page-controller.php';

/**
 * Class PostController
 *
 * @package PressGang
 */
class PostController extends PageController {

    protected $post_type;
    protected $author;
    protected $tags;
    protected $categories;
    protected $related_posts;
    protected $custom_taxonomy_terms = array();

    /**
     * __construct
     *
     * PageController constructor
     *
     * @param string $template
     */
    public function __construct($template = null, $post_type = null) {

        $this->post_type = $post_type ? $post_type : get_post_type();

        if(!$template) {
            // try to guess the view for custom post types
            $template = sprintf("single%s.twig", $this->post_type === 'post' ? '' : "-{$this->post_type}") ;
        }

        parent::__construct(array($template, 'single.twig'));
    }

    /**
     * get_tags
     *
     * @param $page
     * @return array
     */
    protected function get_tags() {

        if(empty($this->tags)) {
            $this->tags = wp_get_post_tags($this->get_post()->ID);
            $this->tags = count($this->tags) ? $this->tags[0]->term_id : array();
        }

        return $this->tags;
    }

    /**
     * get_categories
     *
     * @return array
     */
    protected function get_categories() {
        if (empty($this->categories)) {
            $this->categories = get_the_category(', ');
        }

        return $this->categories;
    }

    /**
     * get_custom_taxonomies
     *
     */
    protected function get_custom_taxonomy_terms() {

        if(empty($this->custom_taxonomy_terms)) {

            $taxonomies = get_object_taxonomies($this->post_type, 'objects');

            foreach ($taxonomies as $slug => &$taxonomy) {

                $terms = get_the_terms($this->get_post()->ID, $slug);

                if (is_array($terms) && count($terms)) {

                    foreach ($terms as &$term) {
                        $term = new \TimberTerm($term);
                    }

                    $name = Pluralizer::pluralize($slug);
                    $this->custom_taxonomy_terms[$name] = $terms;
                }
            }

        }

        return $this->custom_taxonomy_terms;
    }

    /**
     * get_related_posts
     *
     * @param $post
     * @param $tags
     * @return array
     */
    protected function get_related_posts() {

        if(empty($this->related_posts)) {
            $this->related_posts = \Timber::get_posts(array(
                'post_type' => $this->post_type,
                'category__in' => wp_get_post_categories($this->get_post()->ID),
                'numberposts' => POST_NO_RELATED_POSTS,
                'post__not_in' => array($this->get_post()->ID),
                'tag__in' => $this->get_tags(),
                'ignore_sticky_posts' => true,
            ));
        }

        return $this->related_posts;
    }

    /**
     * get_author
     *
     * @return TimberUser
     */
    protected function get_author() {

        if (empty($this->author)) {
            $this->author = $this->get_post()->get_author();
            $this->author->thumbnail = new \TimberImage(get_avatar_url($this->author->id));
        }

        return $this->author;
    }

    /**
     * get_context
     *
     * @return mixed
     */
    protected function get_context()
    {
        $this->context[$this->post_type] = $this->get_post();
        $this->context['tags'] = $this->get_tags();
        $this->context['categories'] = $this->get_categories();
        $this->context['related_posts'] = $this->get_related_posts();
        $this->context['author'] = $this->get_author();

        foreach($this->get_custom_taxonomy_terms() as $name => &$terms) {
            $this->context[$name] = $terms;
        }

        return $this->context;
    }

}