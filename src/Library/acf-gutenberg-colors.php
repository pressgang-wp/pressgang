<?php

namespace PressGang\Library;

use function PressGang\add_action;

class AcfGutenbergColors {

	/**
	 * @see https://whiteleydesigns.com/synchronizing-your-acf-color-picker-with-gutenberg-color-classes/
	 */
	public function __construct() {
		add_action( 'acf/input/admin_footer', [ $this, 'acf_color_palette' ] );
	}

	/**
	 * @return void
	 */
	public function acf_color_palette() { ?>
		<script type="text/javascript">
			(function () {
				acf.add_filter('color_picker_args', function (args) {
					args.palettes = wp.data.select( 'core/block-editor' ).getSettings().colors.map(color => color.color);
					return args;
				});
			})();
		</script>
	<?php }
}

new AcfGutenbergColors();
