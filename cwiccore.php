<?php
/**
 * CwicCore
 *
 * @package       CWICCORE
 * @author        Sebastian Piskaty
 * @version       1.0.0
 *
 * @wordpress-plugin
 * Plugin Name:   CwicCore
 * Plugin URI:    #
 * Description:   Add CoreFramework Classes to Cwicly
 * Version:       1.0.0
 * Author:        Sebastian Piskaty
 * Author URI:    #
 * Text Domain:   cwiccore
 */

// Exit if accessed directly.
if (!defined('ABSPATH'))
    exit;

use CoreFramework\Helper;

add_action('plugins_loaded', 'cwiccore');

function cwiccore()
{
    if (is_plugin_active('core-framework/core-framework.php') && is_plugin_active('cwicly/cwicly.php')) {
        if (class_exists('CoreFramework\Helper')) {
            global $coreClasses;
            global $coreColors;
            $coreClasses = [];
            $coreColors = [];
            $helper = new Helper();
            $coreClasses = $helper->getClassNames();
            $coreColors = $helper->getVariables();
        }
        function formatStyleCategoryName($styleCategory)
        {
            switch ($styleCategory) {
                case 'colorStyles':
                    return 'CF-Color Styles';
                case 'typographyStyles':
                    return 'CF-Typography Styles';
                case 'spacingStyles':
                    return 'CF-Spacing Styles';
                case 'layoutsStyles':
                    return 'CF-Layout Styles';
                case 'designStyles':
                    return 'CF-Design Styles';
                case 'componentsStyles':
                    return 'CF-Component Styles';
                case 'otherStyles':
                    return 'CF-Other Styles';
                default:
                    return 'CoreFramework' . ucfirst($styleCategory);
            }
        }

        function cwicly_plugin_classes_example($plugin_classes)
        {
            global $coreClasses;

            foreach ($coreClasses as $styleCategory => $styles) {
                $formattedName = formatStyleCategoryName($styleCategory);

                $plugin_classes["coreFramework-" . $styleCategory] = [
                    "name" => $formattedName,
                    "colors" => [
                        "list" => [
                            "light" => [
                                "background" => "linear-gradient(90deg,#636ff666,#4854ea66)",
                                "color" => "hsl(236, 98%, 20%)",
                            ],
                            "dark" => [
                                "background" => "linear-gradient(90deg,#636ff666,#4854ea66)",
                                "color" => "hsl(236, 98%, 87%)",
                            ],
                        ],
                    ],
                    "classes" => $styles,
                ];
            }

            return $plugin_classes;
        }

        add_filter("cwicly_plugin_classes", "cwicly_plugin_classes_example", 10, 1);

        function cwicly_global_colors_custom($global_colors)
        {
            global $coreColors; // Assuming this is your global variable containing color styles

            $base_colors = [];
            $palettes = [];

            // Loop through each color style
            foreach ($coreColors["colorStyles"] as $color) {
                // Extract the base color name (prefix before the '-')
                $parts = explode('-', $color);
                $base_color = $parts[0];

                // Add base colors to array (unique)
                if (!in_array($base_color, $base_colors)) {
                    $base_colors[] = $base_color;
                }

                // Add color to the appropriate palette
                $palettes[$base_color][] = 'var(--' . $color . ')';
            }

            // Create the colors array for base colors
            $colors_array = array_map(function ($color) {
                return array(
                    'name' => ucfirst($color),
                    'color' => 'var(--' . $color . ')',
                );
            }, $base_colors);

            // Create palettes array
            $palettes_array = [];
            foreach ($palettes as $key => $colors) {
                if (count($colors) > 1) { // Ensure there are variants to create a palette
                    $palettes_array[] = array(
                        'name' => ucfirst($key),
                        'colors' => $colors,
                    );
                }
            }

            // Add colors and palettes under a single global color key
            $global_colors['coreFrameworkColors'] = array(
                'name' => 'Core Colors',
                'colors' => $colors_array,
                'palettes' => $palettes_array,
            );
            return $global_colors;
        }
        add_filter('cwicly_global_colors', 'cwicly_global_colors_custom', 10, 1);
    }
}
