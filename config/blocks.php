<?php

/**
 * Blocks Configuration
 *
 * This configuration file defines settings for custom WordPress Blocks used within the theme.
 * Each entry in the array represents a custom block configuration, pointing to a block.json file
 * that describes the block. The configuration array can also specify a custom category for each block.
 *
 * Each block's configuration includes a path to its block.json file and an optional 'category' array.
 * The 'category' array allows categorization of the block in the Gutenberg editor and can be used
 * to group related blocks together.
 *
 * Structure of the configuration array:
 * - Key: The path to the block.json file for the block.
 * - Value: An associative array with optional 'category' settings.
 *
 * Example configuration array:
 * return [
 *     '/blocks/hero/block.json' => [
 *         'category' => [
 *             'slug'  => 'custom-category',
 *             'title' => 'Custom Category',
 *         ],
 *     ],
 *     ... additional blocks ...
 * ];
 *
 * Note: The 'category' array is optional. If omitted, the block will be placed in a default category.
 *
 * @see https://www.advancedcustomfields.com/resources/create-your-first-acf-block/
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 *
 * @var array
 */

return [];
