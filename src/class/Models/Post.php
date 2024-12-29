<?php

namespace Deljdlx\WPForge\Models;

use Corcel\Model\Post as ModelPost;
use Deljdlx\WPForge\Models\Traits\Acf;
use DOMDocument;
use DOMXPath;
use WP_Post;

class Post extends ModelPost
{
    use Acf;

    public static $POST_TYPE = 'post';
    public ?WP_Post $wpPost = null;


    protected $acfFields = [];


    public static function getAll($postType = 'post')
    {
        if(isset(static::$POST_TYPE) && $postType === 'post') {
            $postType = static::$POST_TYPE;
        }

        $posts = [];
        $wpPosts = get_posts([
            'post_type' => $postType,
            'numberposts' => -1,
        ]);

        foreach ($wpPosts as $wpPost) {
            $post = new static();
            $post->loadFromWpPost($wpPost);
            $posts[] = $post;
        }

        return $posts;
    }

    public static function getByOptions($options, $instanceName = null)
    {
        $posts = [];
        $wpPosts = get_posts($options);

        foreach ($wpPosts as $wpPost) {
            if($instanceName) {
                $post = new $instanceName();
            } else {
                $post = new static();
            }

            $post->loadFromWpPost($wpPost);
            $posts[] = $post;
        }

        return $posts;
    }


    public static function registerFields($customPostType, $name, $caption, $fields = [])
    {
        $defaultFieldOptions = [
            'key' => 'must-be-set',
            'label' => 'Must be set at createFieldGroupToType call ',
            'name' => 'must-be-set',
            'type' => 'text',
            'prefix' => '',
            'instructions' => '',
            'required' => 0,
            'conditional_logic' => 0,
            'wrapper' => array (
                'width' => '',
                'class' => '',
                'id' => '',
            ),
            'default_value' => '',
            'placeholder' => '',
            'prepend' => '',
            'append' => '',
            'maxlength' => '',
            'readonly' => 0,
            'disabled' => 0,
        ];

        $builtFields = [];
        foreach($fields as $key => $field) {
            $field['key'] = $key;
            $field['name'] = $key;
            $field = array_merge($defaultFieldOptions, $field);

            if($field['type'] === 'repeater') {
                $subFields = [];
                foreach($field['sub_fields'] as $subFieldKey => $subField) {
                    $subField['key'] = $subFieldKey;
                    $subField['name'] = $subFieldKey;
                    $subField = array_merge($defaultFieldOptions, $subField);
                    $subFields[] = $subField;
                }
                $field['sub_fields'] = $subFields;
            }


            $builtFields[] = $field;
        }


        acf_add_local_field_group(array (
            'key' => $name,              //
            'title' => $caption, //
            'fields' => $builtFields,
            'location' => array (
                array (
                    array (
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => $customPostType,
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'hide_on_screen' => '',
        ));
    }

    public static function registerPostType($name, $caption, $support = null, $showInMenu = true, array $extraOptions = [])
    {
        if(!$support) {
            $support = [
                'title',
                'thumbnail',
                'editor',
                'author',
                'excerpt',
                'comments',
            ];
        }

        $options = [
            'label' => $caption,
            'public' => true,
            'hierarchical' => false,
            'supports' => $support,
            'map_meta_cap' => true,
            'show_in_rest' => true,
            'show_in_menu' => $showInMenu,
            'menu_position' => 0,
            // 'capability_type' => $name,
            // 'menu_icon' => 'dashicons-food',
            // archives
            'has_archive' => true,
        ];

        $options['labels'] = [
            'name' => $caption,
            'singular_name' => $caption,
            'menu_name' => $caption,
            'name_admin_bar' => $caption,
            'add_new' => 'Add new ' .$caption,
            'add_new_item' => 'Add new',
            'new_item' => 'New',
            'edit_item' => 'Edit',
            'view_item' => 'View',
            'all_items' => 'All',
            'search_items' => 'Search',
            'parent_item_colon' => 'Parent',
            'not_found' => 'None found',
            'not_found_in_trash' => 'None found in trash',
        ];


        $options = array_merge($options, $extraOptions);

        register_post_type(
            $name,
            $options,
        );

        // give all capabilities to admin
        $admin = get_role('administrator');

        $admin->add_cap('edit_'.$name);
        $admin->add_cap('edit_'.$name.'s');
        $admin->add_cap('edit_others_'.$name.'s');
        $admin->add_cap('publish_'.$name.'s');
        $admin->add_cap('read_'.$name);
        $admin->add_cap('read_private_'.$name.'s');
        $admin->add_cap('delete_'.$name);
        $admin->add_cap('delete_'.$name.'s');
        $admin->add_cap('delete_private_'.$name.'s');
        $admin->add_cap('delete_published_'.$name.'s');
        $admin->add_cap('delete_others_'.$name.'s');
        $admin->add_cap('edit_private_'.$name.'s');
        $admin->add_cap('edit_published_'.$name.'s');
        $admin->add_cap('edit_others_'.$name.'s');
    }


    public function save(array $data = [])
    {
        $this->post_title = $data['title'] ?? $this->post_title ?? '';
        $this->post_content = $data['content'] ?? $this->post_content ?? '';
        $this->post_excerpt = $data['excerpt'] ?? $this->post_excerpt ?? '';
        $this->to_ping = $data['to_ping'] ?? $this->to_ping ?? '';
        $this->pinged = $data['pinged'] ?? $this->pinged ?? '';
        $this->post_content_filtered = $data['post_content_filtered'] ?? $this->post_content_filtered ?? '';

        // set post type
        $this->post_type = $data['post_type'] ?? $this->post_type ?? static::$POST_TYPE;

        $slug = sanitize_title($this->post_title);
        $this->guid = $data['permalink'] ?? $this->guid ?? $slug;

        return parent::save();
    }

    public function savePostAttachment($name)
    {
        if($_FILES[$name] && !empty($_FILES[$name]['name'])) {
            $attachedFile = $_FILES[$name];
            $upload = wp_upload_bits($attachedFile['name'], null, file_get_contents($attachedFile['tmp_name']));

            $attachment = [
                'post_title' => $attachedFile['name'],
                'post_content' => '',
                'post_status' => 'inherit',
                'post_mime_type' => $attachedFile['type'],
                'guid' => $upload['url'],
            ];

            $attach_id = wp_insert_attachment($attachment, $upload['file'], $this->getId());
            update_field('illustration', $attach_id, $this->getId());
        }
    }


    public function setField(string $fieldName, $value)
    {
        $this->acfFields[$fieldName] = $value;
        return update_field($fieldName, $value, $this->ID);
    }


    public function setPermalink(string $permalink)
    {
        $this->guid = $permalink;
        return $this;
    }

    // ===========================================================
    public function getId()
    {
        return $this->ID;
    }

    public function getStatus()
    {
        return $this->post_status;
    }


    public function setAuthor(int $authorId)
    {
        $this->post_author = $authorId;
        return $this;
    }

    public function setTitle(string $title)
    {
        $this->post_title = $title;
        $permalink = sanitize_title($title);
        $this->post_name = $permalink;
        return $this;
    }

    public function setContent(string $content)
    {
        $this->post_content = $content;
        return $this;
    }

    public function setExcerpt(string $excerpt)
    {
        $this->post_excerpt = $excerpt;
        return $this;
    }

    // ===========================================================

    public function loadFromWpPost(WP_Post $post)
    {
        $this->wpPost = $post;
        foreach ($post as $attribute => $value) {
            $this->$attribute = $value;
        }
    }

    public function loadById($id) {
        $wpPost = get_post($id);
        $this->loadFromWpPost($wpPost);

        return $this;
    }


    public function getPost()
    {
        if(!$this->wpPost) {
            $this->wpPost = get_post($this->ID);
        }
        return $this->wpPost;
    }

    public function getType(): string
    {
        return $this->post_type;
    }

    public function getField($fieldName) {
        return $this->getAcfField($fieldName);
    }

    public function getFields() {
        return $this->getAcfFields();
    }

    public function getAcfField($fieldName)
    {
        if(array_key_exists($fieldName, $this->acfFields)) {
            return $this->acfFields[$fieldName];
        }
        $this->acfFields[$fieldName] = get_field($fieldName, $this->ID);

        return $this->acfFields[$fieldName];
    }

    public function getAcfFields()
    {
        return get_fields($this->ID);
    }

    public function getSlug()
    {
        return $this->post_name;
    }

    public function getThumbnail($size = 'large', $default = 'https://picsum.photos/400/400')
    {
        $url = get_the_post_thumbnail_url($this->wpPost, $size);
        if(!$url && $default) {
            return $default;
        }

        return $url;
    }

    public function getPermalink()
    {
        return get_permalink($this->ID);
    }

    public function getTitle()
    {
        return $this->post_title;
    }

    public function getExcerpt($ending = '...'): string
    {

        return get_the_excerpt();

        // return get_the_content();
        $content = get_the_content();
        $length = 1000;

        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?>' . $content);
        $xpath = new DOMXPath($doc);
        $nodes = $xpath->query("//text()[not(ancestor::script)][not(ancestor::style)]");
        $excerpt = '';
        $count = 0;
        foreach ($nodes as $node) {
            $text = $node->nodeValue;
            $words = preg_split("/[\n\r\t ]+/", $text, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($words as $word) {
                if ($count >= $length) {
                    $excerpt .= $ending;
                    return $excerpt;
                }
                $excerpt .= $word . ' ';
                $count++;
            }
        }
        // $excerpt = trim(strip_tags($excerpt));
        $excerpt .= $ending;
        return $excerpt;


        // return get_the_excerpt();
    }

    public function getAuthor()
    {
        $author = User::find($this->post_author);
        return $author;
    }

    public function getAuthorId()
    {
        return $this->post_author;
    }

    public function getContent($applyFilter = true)
    {
        if(!$this->wpPost) {
            return '';
        }


        if($applyFilter && $this->wpPost->post_content) {
            return apply_filters('the_content', $this->wpPost->post_content);
        }

        return $this->wpPost->post_content;
    }

    public function getTags()
    {
        return $this->getTerms('post_tag');
    }

    public function getTerms(string $taxonomy)
    {
        return get_the_terms($this->wpPost, $taxonomy);
    }

    public function getCategories()
    {
        return get_the_category();
    }

    public function getDate(string $format = 'Y-m-d H:i:s')
    {
        return get_the_date($format);
    }
}

