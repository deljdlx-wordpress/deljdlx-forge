<?php

namespace Deljdlx\WPForge\Theme;

use WP_Customize_Control;
use WP_Customize_Manager;

class Customizer
{
    public readonly array $sections;
    public readonly array $settings;

    public function __construct()
    {

        add_action(
            'customize_preview_init',
            function (WP_Customize_Manager $themeCustomizerObject) {
                $pluginUri = '/wp-content/plugins/deljdlx-forge';

                wp_enqueue_style(
                    'customizer-style',
                    $pluginUri . 'assets/customizer/customizer-global.css',
                );


                wp_enqueue_script(
                    'customizer-loader',
                    $pluginUri . '/assets/customizer/customizer-loader.js',
                    [], // gestion des dépendances,
                    false,
                    true // nous demandons à wp de mettre le javascript dans le footer
                );

                wp_enqueue_script(
                    'customizers',
                    get_theme_file_uri('assets/admin//customizer/customizers.js'),
                    [],
                    false,
                    true
                );
            }
        );
    }

    public function getSetting($name, $default = null)
    {
        return get_theme_mod($name, $default);
    }

    public function addPreview($name, $selector)
    {
        add_action(
            'customize_register',
            function (WP_Customize_Manager $themeCustomizerObject) use ($name, $selector)
            {
                $themeCustomizerObject->selective_refresh->add_partial(
                    $name,
                    [
                        'selector' => $selector,
                        'fallback_refresh' => false,
                    ]
                );
            }
        );

        /*
        add_action( 'wp_footer', function() use ($name) {
            echo '<script>
                document.addEventListener(
                    "DOMContentLoaded",
                    () => {
                        if(typeof(registerCustomizer) !== "undefined") {
                            registerBackgroundColorCustomizer("'. $name .'", "body");
                            console.log("CUSTOMIZER INLINE");
                        }
                    }
                );
            </script>';
        }, 1000);
        */
    }

    public function addPanel($name, $label, $order = 300)
    {
        add_action(
            'customize_register',
            function (WP_Customize_Manager $themeCustomizerObject) use ($name, $label, $order)
            {
                $themeCustomizerObject->add_panel(
                    $name,
                    [
                        'title' => __($label),
                        'priority' => $order
                    ]
                );
            }
        );
    }

    public function addSection($name, $label, $panel = null, $order = 300)
    {

        $options = [
            'title' => __($label),
            'priority' => $order
        ];

        if($panel !== null) {
            $options['panel'] = $panel;
        }


        add_action(
            'customize_register',
            function (WP_Customize_Manager $themeCustomizerObject) use ($name, $options)
            {
                $themeCustomizerObject->add_section(
                    $name,
                    $options
                );
            }
        );
    }

    public function addSetting($section, $name, $label, $control = WP_Customize_Control::class, $default = null)
    {
        add_action(
            'customize_register',
            function (WP_Customize_Manager $themeCustomizerObject) use ($section, $name, $label, $control, $default)
            {
                $themeCustomizerObject->add_setting(
                    $name,
                    [
                        'default' => $default,
                        'transport' => 'postMessage'
                    ]
                );

                $themeCustomizerObject->add_control(
                    new $control(
                        $themeCustomizerObject,
                        $name,
                        [
                            'label' => __($label),
                            'section' => $section,
                            'settings' => $name,
                        ]
                    )
                );
            }
        );
    }

    public function addTypographySetting(string $section, string $setting, string $label, array $googleFonts, array $defaultValues = [])
    {

        $defaultValues = array_merge([
            'font-family'     => 'Roboto',
            'font-size'       => '',
            'color'           => '',
            'font-style'      => 'normal',
            'variant'         => 'regular',
            'line-height'     => '1.5',
            'letter-spacing'  => '0',
            'text-transform'  => 'none',
            'text-decoration' => 'none',
            'text-align'      => 'left',
        ], $defaultValues);

        new \Kirki\Field\Typography(
            [
                'settings'    => $setting,
                'label'       => esc_html__($label, 'jdlx_taverne' ),
                'section'     => $section,
                'priority'    => 10,
                'transport'   => 'auto',
                'default'     => $defaultValues,
                'output'      => [
                    [
                        'element' => 'body',
                    ],
                ],
                'choices' => [
                    'fonts' => [
                        // 'google' => [ 'popularity', 30 ],
                        'google' => $googleFonts,
                    ],
                    'font-family'    => true,   // Affiche l'option font-family
                    'font-size'      => true,   // Affiche l'option de font-size
                    'color'          => true,   // Affiche l'option de couleur
                    'variant'        => true,  // Masque l'option de variant
                    'line-height'    => true,   // Affiche l'option de line-height
                    'letter-spacing' => true,  // Masque l'option de letter-spacing
                    'text-transform' => true,  // Masque l'option de text-transform
                    'text-align'     => true,  // Masque l'option d'alignement du texte
                ],
                'transport'   => 'postMessage',
            ]
        );
    }

    public function addSliderSetting(
        string $section,
        string $setting,
        string $label,
        int $min = 0,
        int $max = 64,
        int $step = 1,
        int $default = 0
    ) {
        new \Kirki\Field\Slider(
            [
                'settings'    => $setting,
                'label'       => esc_html__( $label, 'taverne' ),
                'section'     => $section,
                'default'     => $default,
                'choices'     => [
                    'min'  => $min,
                    'max'  => $max,
                    'step' => $step,
                ],
                'transport'   => 'postMessage',
            ]
        );
    }

    public function addColorSetting(
        string $section,
        string $setting,
        string $label,
        bool $alpha = true
    ) {
        new \Kirki\Field\Color(
            [
                'settings'    => $setting,
                'label'       => esc_html__( $label, 'taverne' ),
                'section'     => $section,
                'default'     => 'transparent',
                'transport'   => 'postMessage',
                'choices'     => [
                    'alpha' => $alpha,
                ],
            ]
        );
    }

    public function addSwitchSetting(
        string $section,
        string $setting,
        string $label,
        string $labelOn = 'On',
        string $labelOff = 'Off',
    ) {
        new \Kirki\Field\Checkbox_Switch(
            [
                'settings'    => $setting,
                'label'       => esc_html__( $label, 'taverne' ),
                'section'     => $section,
                'default'     => 'on',
                'choices'     => [
                    'on'  => esc_html__($labelOn, 'taverne' ),
                    'off' => esc_html__($labelOff, 'taverne' ),
                ],'transport'   => 'postMessage',
            ]
        );
    }

    public function addHtml($section, $html)
    {
        static $i;
        if($i === null) {
            $i = 0;
        }
        $i++;



        \Kirki::add_field(
            'custom-' . $section .'-' . $i, [
            'type'     => 'custom',
            'settings' => 'html-' .$section . '-' .$i,
            'section'  => $section,
            'default'  => $html, // insère une ligne horizontale
        ] );
    }

    public function addHr(string $section)
    {
        $this->addHtml($section, '<hr/>');
    }

    public function h1(string $section, string $label)
    {
        $this->addHtml($section, '<h1>' . $label .'</h1>');
    }



}

