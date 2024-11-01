<?php
namespace Whisk\Recipes\Import;

use KAGG\WP_Background_Processing\WP_Background_Process;

/**
 * Class Background_Import
 *
 * @package Whisk\Recipes\Import
 */
class Background_Import extends WP_Background_Process {
	/**
	 * Class for import from particular plugin
	 *
	 * @var null
	 */
	private $importer = null;

	/**
	 * Transient name for progress tracking
	 *
	 * @var string
	 */
	private $transient_name = 'whisk_import_progress';

	/**
	 * Ajax action
	 *
	 * @var string
	 */
	protected $action = 'whisk_background_import';

	/**
	 * Task for single post
	 *
	 * @param mixed $item Queue item to iterate over.
	 *
	 * @return mixed
	 * @throws \Exception Error report.
	 */
	protected function task( $item ) {
		// Processing zip recipes item.
		if ( is_array( $item ) ) {
			$importer_name = array_keys( $item )[0];
			$item          = array_values( $item )[0];
		} else {
			// Processing all other items.
			if ( get_post_meta( $item, 'imported', true ) ) {
				return false;
			}
			$importer_name = get_post_type( $item );
			if ( in_array( $importer_name, [ 'post', 'page' ], true ) ) {
				$importer_name = 'easy_recipe';
				$post          = get_post( $item );
				if ( strpos( $post->post_content, 'ERName' ) === false ) {
					return false;
				}
			}
		}

		if ( ! $this->importer ) {
			$this->importer = General_Import::load_importer( $importer_name );
		}
		$this->importer->process_recipe( $item );

		// Manage transient for progress bar.
		$processed = get_transient( $this->transient_name );
		set_transient( $this->transient_name, ++$processed, 12 * HOUR_IN_SECONDS );

		return false;
	}

	/**
	 * End of process
	 */
	protected function complete() {
		delete_transient( $this->transient_name );
		parent::complete();
	}
}
