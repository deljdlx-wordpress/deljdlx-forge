<?php
namespace Deljdlx\WPForge;

use Deljdlx\WPForge\Theme\Theme;
use Illuminate\Config\Repository;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use WP_Error;
use WP_Query;

class Plugin
{
    private static ?Plugin $instance = null;

    protected string $bootstrapFile;
    protected string $filepath;


    protected $namespace;

    protected $api;
    protected $customPostTypes = [];
    protected $customTaxonomies = [];
    protected Container $container;
    protected View $view;
    protected Router $router;
    protected Theme $theme;

    public static function getInstance(Container $container = null)
    {
        if(!static::$instance) {
            static::$instance = new static($container);
        }
        return static::$instance;
    }

    public static function run()
    {
        $instance = static::getInstance();

        try {
            $result = $instance->router->route();

            if($result) {
                http_response_code(200);
                echo $result;
                return true;
            }
        }
        catch(\Exception $e) {
            dump($e);
        }
        return false;
    }

    public function __construct(Container $container,$bootstrapFile = null)
    {
        if(!static::$instance) {
            static::$instance = $this;
        }

        if(!$bootstrapFile) {
            $bootstrapFile = debug_backtrace()[0]['file'];
        }
        $this->bootstrapFile = $bootstrapFile;

        $this->filepath = dirname($this->bootstrapFile);

        $this->container =  $container;


        $this->view = $container->get(View::class);

        // $this->theme = new Theme($this->container);
        $this->theme = $container->get(Theme::class);

        $this->router = $container->get(Router::class);

        // $this->router = Router::getInstance();

        $this->setup();


        // check if session is started
        if(session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        register_activation_hook(
            $this->bootstrapFile,
            [$this, 'activate']
         );

        register_deactivation_hook(
            $this->bootstrapFile,
            [$this, 'deactivate']
        );

        add_action(
            'init',
            [$this, 'initialize']
        );

        add_action(
            'admin_menu',
            [$this, 'disableAdminItem']
        );

        add_action(
            'acf/init',
            [$this, 'createCustomFields']
        );
    }

    public function mustBeLogged() {
        // if not logger, redirect to sign-in page
        if(!is_user_logged_in()) {
            wp_safe_redirect('/sign-in');
        }
    }

    public function getView(): View
    {
        return $this->view;
    }

    public function getTheme(): Theme
    {
        return $this->theme;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function renderTemplate(string $template)
    {

        if($this->view->templateExists($template)) {
            return $this->view->render($template);
        }
        else {
            throw new \Exception(
                'Template not found: ' . $template .'neither in plugin or theme. '.
                'Plugin templates pathes : '. $this->view->getTemplatePathes() .
                'Theme templates pathes : '. wp_forge()->view->getTemplatePathes()
            );
        }
    }


    public function apiAutoload()
    {
        $reflector = new \ReflectionClass($this);
        $this->namespace = $reflector->getNamespaceName();
        $apiClassName = $this->namespace. '\Api';

        if(class_exists($apiClassName)) {
            $this->api = new $apiClassName($this);
        }
    }

    private function setup()
    {
        global $table_prefix;
        $params = [
            'host'      => DB_HOST,
            'database'  => DB_NAME,
            'username'  => DB_USER,
            'password'  => DB_PASSWORD,
            'prefix'    => $table_prefix,
        ];
        $corcel = \Corcel\Database::connect($params);
    }

    public function dieIfNot($roles)
    {
        $roles = (array) $roles;
        $current_user = wp_get_current_user();
        if(!array_intersect($roles, $current_user->roles)) {
            wp_die('You are not allowed to access this page');
        }
    }


    public function loadComponentsFromFolder(string $componentFolder, string $namespace = 'WpPecule\\Components')
    {
        $this->view->loadComponentsFromFolder($componentFolder, $namespace);
    }



    public function getPluginFilepath()
    {
        return plugin_dir_path($this->bootstrapFile);
    }

    public function initialize()
    {

    }

    public function activate()
    {

    }

    public function deactivate()
    {

    }

    public function createCustomFields()
    {

    }

    public function disableAdminPageForRole(string $role, string $page)
    {
        $current_user = wp_get_current_user();
        if(in_array($role, $current_user->roles)) {
            remove_menu_page($page);
        }
    }

    public function disableAdminPageForNotRoles(array $roles, string $page)
    {
        $current_user = wp_get_current_user();
        if(!array_intersect($roles, $current_user->roles)) {
            remove_menu_page($page);
        }
    }




    public function disableAdminItem()
    {

    }

    //===========================================================

    public function setError($errorName, $error)
    {
        $_SESSION[$errorName] = $error;
    }

    public function getError($errorName)
    {
        if(isset($_SESSION[$errorName])) {
            $error = $_SESSION[$errorName];
            unset($_SESSION[$errorName]);
            return $error;
        }
        return null;
    }

    public function getOption($optionName, $default = null)
    {
        return get_option($optionName, $default);
    }

    //===========================================================

    public function addColumnToUsersAdminPage($columnName, $columnTitle, $callback, $position = null)
    {
        add_filter('manage_users_columns', function($columns) use ($columnName, $columnTitle, $position) {
            if($position === null) {
                $columns[$columnName] = $columnTitle;
            }
            else {
                $columns = array_slice($columns, 0, $position, true) +
                    [$columnName => $columnTitle] +
                    array_slice($columns, $position, null, true);
            }


            return $columns;
        });

        add_filter('manage_users_custom_column', function($value, $currentColumnName, $userId) use ($columnName, $callback) {
            if($currentColumnName === $columnName) {
                return $callback($value, $columnName, $userId);
            }
            return $value;
        }, 10, 3);
    }

    public function addColumnToCustomPostTypeAdminPage($postType, $columnName, $columnTitle, $callback, $position = null)
    {
        add_filter('manage_'.$postType.'_posts_columns', function($columns) use ($columnName, $columnTitle, $position) {
            if($position === null) {
                $columns[$columnName] = $columnTitle;
            }
            else {
                $columns = array_slice($columns, 0, $position, true) +
                    [$columnName => $columnTitle] +
                    array_slice($columns, $position, null, true);
            }


            return $columns;
        });

        add_action('manage_'.$postType.'_posts_custom_column', $callback, 10, 2);
    }

    public function hideAdminBarForGuest()
    {
        add_filter('show_admin_bar', function($value) {
            if(!is_user_logged_in()) {
                return false;
            }

            return $value;
        });
    }

    public function hideAdminBarForRole($role = 'subscriber')
    {
        add_filter('show_admin_bar', function($value) use ($role) {
            if(current_user_can($role)) {
                return false;
            }

            return $value;
        });
    }

    public function createPrivatePage(string $slug, string $pageTitle, string $content ='', string $postType = 'page')
    {
        $page = $this->createPage($slug, $pageTitle, $content, $postType);
        $page->post_status = 'private';
        wp_update_post($page);
        return $page;
    }

    public function createPage(string $slug, string $pageTitle, string $content ='', string $postType = 'page')
    {

        // get page by slug
        // $page = get_page_by_path($slug);

        $query = new WP_Query([
            'post_type' => $postType,
            'name' => $slug,
        ]);

        $page = null;
        if($query->have_posts()) {
            $page = $query->posts[0];
        }

        if(!$page) {
            $page = [
                'post_name' => $slug,
                'post_title' => $pageTitle,
                'post_content' => $content,
                'post_status' => 'publish',
                'post_author' => 1,
                'post_type' => $postType,
            ];
            wp_insert_post($page);
        }

        return $page;
    }

    public function disableApi()
    {
        add_filter('rest_authentication_errors', function() {
            return new WP_Error( 'rest_disabled', __( 'The WordPress REST API has been disabled.' ), array( 'status' => rest_authorization_required_code() ) );
        });
    }

    public function addAdminHiddenPage($name, $menuEntry, $callback, $authorizations = 'manage_options')
    {
        add_action('admin_menu', function() use ($name, $menuEntry, $callback, $authorizations) {
            add_submenu_page(
                '__does-not-exists',
                $menuEntry,     // Titre de la page
                '',     // Texte du menu
                $authorizations,
                $name,
                $callback
            );
        });
    }

    public function addAdminPage( $name, $menuEntry, $callback, $priority = 100, $authorizations = 'manage_options', $icon = 'dashicons-admin-tools')
    {
        add_action('admin_menu', function() use($menuEntry, $name, $callback, $authorizations, $priority, $icon, ) {
            add_menu_page(
                $menuEntry,
                $menuEntry,
                $authorizations,
                $name,
                $callback,
                $icon,
                $priority
            );
        });

        return $this;
    }

    public function setAdminHomePage($callback, $authorizations = 'pecule-manager')
    {
        $this->addAdminPage(
            'admin-home',
            'Home',
            function() use ($callback) {
                $callback();
            },
            1,
            $authorizations
        );

        add_action('load-index.php', function() use ($callback) {
            wp_redirect(admin_url('admin.php?page=admin-home'));
            exit();
        });
    }



    public function addAdminCss($css)
    {
        add_action('admin_enqueue_scripts', function() use ($css) {
            if(strpos($css, 'http') === false && strpos($css, '//') !== 0)
            {
                wp_enqueue_style($css, plugin_dir_url($this->bootstrapFile).$css);
            }
            else {
                wp_enqueue_style($css, $css);
            }
        });
    }

    public function addAdminJs($js, $args = [])
    {
        add_action('admin_enqueue_scripts', function() use ($js, $args) {
            if(strpos($js, 'http') === false && strpos($js, '//') !== 0) {
                wp_enqueue_script(
                    $js,
                    plugin_dir_url($this->bootstrapFile).$js,
                    [],
                    false,
                    $args,

                );
            }
            else {
                wp_enqueue_script(
                    $js,
                    $js,
                    [],
                    false,
                    $args,
                );
            }

        });
    }

    public function addMetabox(
        string $id,
        string $title,
        $callback,
        string $postType,
        $context = 'advanced',
        $priority='default',
    )
    {
        add_action('add_meta_boxes', function() use($id, $title, $callback, $postType, $context, $priority) {
            \add_meta_box(
                $id,
                $title,
                $callback,
                $postType,
                $context,
                $priority,
            );
        });
    }

    public function createFieldGroupToUser($name, $caption, $fields = [])
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
            $builtFields[] = $field;
        }


        acf_add_local_field_group(array (
            'key' => $name,              //
            'title' => $caption, //
            'fields' => $builtFields,
            'location' => array (
                array (
                    array (
                        'param' => 'user_form',
                        'operator' => '==',
                        'value' => 'all',
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

    public function createFieldGroupToType($customPostType, $name, $caption, $fields = [])
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

    public function createRole($name, $caption)
    {
        return add_role(
            $name,
            $caption,
            [
                'read' => true,
            ]
        );
    }

    public function removeRole($name)
    {
        remove_role($name);
    }


    public function addCapabilityToRole($role, $capability)
    {
        add_action('admin_init', function() use ($role, $capability) {
            $role = get_role($role);
            $role->add_cap($capability);
        });
    }

    public function removeCapabilityFromRole($role, $capability)
    {
        add_action('admin_init', function() use ($role, $capability) {
            $role = get_role($role);
            $role->remove_cap($capability);
        });
    }

    public function createTerm($name, $taxonomy)
    {
        return wp_insert_term($name, $taxonomy);
    }


    public function createPostType($name, $caption, $support = null, $showInMenu = true, array $extraOptions = [])
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

    public function createTaxonomy($postType, $name, $caption, $hierachical = false)
    {
        register_taxonomy(
            $name,
            (array) $postType,
            [
                'label' => $caption,
                'hierarchical' => $hierachical,
                'public' => true,
                'show_in_rest' => true,
            ]
        );
    }

    public function render(string $template, array $data = []): string
    {
        return $this->view->render($template, $data);
    }
}

