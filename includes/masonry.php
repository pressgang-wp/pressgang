<?php

class Masonry {
	public function __construct() {
		// enqueue imagesloaded.pkgd.min.js
		Scripts::$scripts['images-loaded'] = [
			'src'       => 'https://unpkg.com/imagesloaded@4.1/imagesloaded.pkgd.min.js',
			'in_footer' => true,
			'ver'       => '4.1',
		];

		// enqueue masonry.pkgd.js
		Scripts::$scripts['masonry-4.1.1'] = [
			'src'       => get_template_directory_uri() . '/js/src/vendor/masonry/masonry.pkgd.js',
			'deps'      => [ 'jquery', 'images-loaded' ],
			'ver'       => '4.1.1',
			'in_footer' => true
		];

		// enque pressgang masonry init
		Scripts::$scripts['pressgang-masonry'] = [
			'src'       => get_template_directory_uri() . '/js/src/custom/masonry.js',
			'deps'      => [ 'masonry-4.1.1' ],
			'in_footer' => true,
			'ver'       => '1.0'
		];

	}
}

new Masonry();
