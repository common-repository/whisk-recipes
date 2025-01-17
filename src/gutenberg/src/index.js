import { registerBlockType } from '@wordpress/blocks';
import { dispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import Edit from './edit';
import save from './save';
import Instruction from './instruction/Instruction';
import InstructionTitle from './instruction-title/InstructionTitle';
import Instructions from './instructions/Instructions';
import Ingredients from './ingredients/Ingredients';
import Ingredient from './ingredient/Ingredient';
import Notes from './notes/Notes';
import Note from './note/Note';
import Tags from './tags/Tags';
import General from './general/General';
import Times from './times/Times';

dispatch('core/edit-post').showBlockTypes(['whisk/recipe']);

registerBlockType( 'whisk/recipe', {
	title: __( 'Whisk Recipe', 'whisk-recipes' ),
	description: __(
		'Insert ready Whisk recipe or create a new one.',
		'whisk-recipes'
	),
	category: 'whisk',
	icon: <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 256 257" fill="none"><mask id="mask0" mask-type="alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="256" height="257"><path d="M0 46.5C0 21.1 20.6 0.5 46 0.5H210C235.4 0.5 256 21.1 256 46.5V210.5C256 235.9 235.4 256.5 210 256.5H46C20.6 256.5 0 235.9 0 210.5V46.5Z" fill="#3DC795"/></mask><g mask="url(#mask0)"><rect y="0.5" width="256" height="256" fill="#3DC795"/><path d="M334.8 181.5C334.8 178.2 332.7 175.6 330.9 171.6 326.2 176.5 324.3 179.8 324.3 183.8 324.3 185.8 326.1 187.4 328.2 187.4 331.8 187.4 334.8 185.8 334.8 182.6V181.5ZM434.5 199.5C421.1 199.5 398 179.4 391.3 163.3L392 181C391.9 187.7 388 191.9 383.3 191.9 374.4 191.9 369.7 185.6 369.9 175.5L369.5 135.1C363.5 143.6 356.3 150.8 349.6 157 354 164.8 357.1 172 357.1 177.7 357.1 190.1 344.2 200.2 328.4 200.2 319.5 200.2 312.3 192.8 312.3 183.8 312.3 175.2 317.7 167.9 325.2 160.5 323.4 157.5 321.3 154.3 319.3 151 309.2 173 293.8 191.9 278.5 191.9 269.8 191.9 262.2 184.3 261.6 174.9L259.5 145.3C259.4 144.2 258.6 144.1 258.3 145.1L242.8 187.5C240.2 194.5 234.2 200.2 229.4 200.2 220.2 200.2 212.3 192.1 211.7 182.3L208.2 129.3C208.1 128.3 207.3 128.3 207 129.2L192.6 178.9C190.3 186.8 183.5 191.9 177.3 191.9 168.2 191.9 162.8 185.6 162.7 175.5L162.1 84.6C162.1 83.8 161.2 83.6 161 84.4 150 135.1 143.1 167.5 140 181.6 135.5 202.8 133.4 212.7 119.6 213.8 113 213.8 110.5 211.9 107.7 207.5 105 203.3 103.4 198.5 102.7 193.5L90 111.8C89.8 110.8 89 110.8 88.8 111.7L69 189.5C67.2 196.2 63.7 200.2 56.1 200.2 48.7 199.9 44.6 195.4 42.1 184.9L28.6 123.3C27.2 117.9 24.5 112.2 20.9 112.2 17.6 112.2 16.6 115 14.6 115 13.3 115 12.8 114.2 12.8 112.2 12.8 104.3 22.9 94.3 31.8 94.3 38.4 94.3 43.9 99.7 46 108.1L56.7 159.2C56.9 160.2 57.7 160.2 57.9 159.2L76.2 87C78.8 77.4 85.6 71.4 93.5 71.4 101.6 71.4 105.1 77.7 106.5 88.1L121.7 169.3C121.9 170.3 122.7 170.4 123 169.4L147.4 62.3C150.6 50.4 157.5 43.2 165.5 43.2 173.2 43.2 182.3 51.1 182.5 64L184.1 151.8C184.1 152.8 185 152.9 185.3 152L196.5 116.5C199 108.4 205.1 103.3 212.8 103.3 222.2 103.3 228.4 109 229.1 118.4L233.1 170.6C233.1 171.6 233.9 171.7 234.3 170.7L255 113.9C257.4 107.3 261.7 103.3 266.3 103.3 273.6 103.3 278.5 109.2 279.3 118.7L283.2 166.3C283.4 169 284.7 170.8 286.5 170.8 293.1 170.8 303.3 153.4 310.1 134.9 306.1 126.6 303.1 117.9 303.1 109 303.1 96.3 310.4 85.9 319.3 85.9 326 85.9 331 93.4 331 105.3 331 111.2 330.1 118.1 328.4 125.2 332.6 131.5 337.5 138.3 342.1 145.1 355.7 132.4 368.1 117.3 369.1 94.3L369.7 58.7C369.9 48.5 372.5 43.8 378.4 43.8 385.8 43.8 392 51.9 391.9 61.7L391.5 141.1C398.9 126.5 423.8 108.1 429.2 108.1 434.7 108.1 438.7 116.4 438.7 124 438.7 128.4 434.3 132.5 429.3 135.7 415 144.7 402.2 148.6 402.2 153 402.2 160.5 435.5 179.1 447.2 179.1 453.3 179.1 456.7 174 459 174 460 174 460.8 175 460.8 176.3 460.8 184.9 445.9 199.5 434.5 199.5ZM278.8 75.7C278.8 83 273.7 88.9 267.4 88.9 261.1 88.9 256.1 83 256.1 75.7 256.1 68.5 261.1 62.5 267.4 62.5 273.7 62.5 278.8 68.5 278.8 75.7Z" fill="white"/></g><defs><clipPath id="clip0"><rect width="256" height="256" fill="white" transform="translate(0 0.5)"/></clipPath></defs></svg>,

	/**
	 * Optional block extended support features.
	 */
	supports: {
		// Removes support for an HTML mode.
		html: false,
	},

	/**
	 * @see ./edit.js
	 */
	edit: Edit,

	/**
	 * @see ./save.js
	 */
	save,
} );

registerBlockType( 'whisk/instruction', {
	title: 'Instruction',
	category: 'whisk',
	supports: {
		// Removes support for an HTML mode.
		html: false,
	},
	attributes: {
		instruction: {
			type: 'object',
			default: {
				whisk_step_image: '',
				whisk_step_image_url: '',
				whisk_step_instruction: '',
				_type: 'step',
				whisk_step_summary: '',
				whisk_step_video: '',
				whisk_step_video_url: '',
			}
		}
	},
	parent: [ 'whisk/instructions' ],
	edit: Instruction,
	save: () => {
		return null;
	},
});

registerBlockType( 'whisk/ingredient', {
	title: 'Ingredient',
	category: 'whisk',
	supports: {
		// Removes support for an HTML mode.
		html: false,
	},
	attributes: {
		whisk_ingredient_amount: {
			type: 'string',
			default: ''
		},
		whisk_ingredient_id: {
			type: 'array',
			default: '[]'
		},
		whisk_ingredient_note: {
			type: 'string',
			default: ''
		},
		_type: {
			type: 'string',
			default: '_'
		},
		whisk_ingredient_unit: {
			type: 'string',
			default: ''
		}
	},
	parent: [ 'whisk/ingredients' ],
	edit: Ingredient,
	save: () => {
		return null;
	},
});

registerBlockType( 'whisk/instruction-title', {
	title: 'Instructions Group Title',
	category: 'whisk',
	supports: {
		// Removes support for an HTML mode.
		html: false,
	},
	attributes: {
		whisk_step_separator_name: {
			type: 'string',
			default: ''
		}
	},
	parent: [ 'whisk/instructions' ],
	edit: InstructionTitle,
	save: () => {
		return null;
	},
});

registerBlockType( 'whisk/instructions', {
	title: 'Instructions',
	category: 'whisk',
	supports: {
		// Removes support for an HTML mode.
		html: false,
	},
	parent: [ 'whisk/recipe' ],
	edit: Instructions,
	save: () => {
		return null;
	},
});

registerBlockType( 'whisk/ingredients', {
	title: 'Ingredients',
	category: 'whisk',
	supports: {
		// Removes support for an HTML mode.
		html: false,
	},
	parent: [ 'whisk/recipe' ],
	edit: Ingredients,
	save: () => {
		return null;
	},
});

registerBlockType( 'whisk/notes', {
	title: 'Notes',
	category: 'whisk',
	supports: {
		// Removes support for an HTML mode.
		html: false,
	},
	parent: [ 'whisk/recipe' ],
	edit: Notes,
	save: () => {
		return null;
	},
});

registerBlockType( 'whisk/note', {
	title: 'Note',
	category: 'whisk',
	supports: {
		// Removes support for an HTML mode.
		html: false,
	},
	attributes: {
		whisk_note: {
			type: 'string',
			default: ''
		},
		_type: {
			type: 'string',
			default: '_'
		},
	},
	parent: [ 'whisk/notes' ],
	edit: Note,
	save: () => {
		return null;
	},
});

registerBlockType( 'whisk/tags', {
	title: 'Tags',
	category: 'whisk',
	supports: {
		// Removes support for an HTML mode.
		html: false,
	},
	attributes: {
		whisk_meal_types: {
			type: 'array',
			default: []
		},
		whisk_tags: {
			type: 'array',
			default: []
		},
		whisk_diets: {
			type: 'array',
			default: []
		},
		whisk_cuisines: {
			type: 'array',
			default: []
		},
		whisk_cooking_techniques: {
			type: 'array',
			default: []
		},
		whisk_nutrition: {
			type: 'array',
			default: []
		},
	},
	parent: [ 'whisk/recipe' ],
	edit: Tags,
	save: () => {
		return null;
	},
});

registerBlockType( 'whisk/general', {
	title: 'General',
	category: 'whisk',
	supports: {
		// Removes support for an HTML mode.
		html: false,
	},
	attributes: {
		image_id: {
			type: 'number',
			default: null
		},
		image_url: {
			type: 'string',
			default: ''
		},
		video_url: {
			type: 'string',
			default: ''
		},
		video_id: {
			type: 'number',
			default: null
		},
		video_external_url: {
			type: 'string',
			default: ''
		},
		title: {
			type: 'string',
			default: ''
		},
		description: {
			type: 'string',
			default: '',
		},
		whisk_servings: {
			type: 'number',
			default: null
		},
		whisk_servings_unit: {
			type: 'string',
			default: '',
		}
	},
	parent: [ 'whisk/recipe' ],
	edit: General,
	save: () => {
		return null;
	},
});

registerBlockType( 'whisk/times', {
	title: 'Times',
	category: 'whisk',
	supports: {
		// Removes support for an HTML mode.
		html: false,
	},
	attributes: {
		whisk_prep_time_days: {
			type: 'number',
			default: null
		},
		whisk_prep_time_hours: {
			type: 'number',
			default: null
		},
		whisk_prep_time_minutes: {
			type: 'number',
			default: null
		},
		whisk_cook_time_days: {
			type: 'number',
			default: null
		},
		whisk_cook_time_hours: {
			type: 'number',
			default: null
		},
		whisk_cook_time_minutes: {
			type: 'number',
			default: null
		},
		whisk_resting_time_days: {
			type: 'number',
			default: null
		},
		whisk_resting_time_hours: {
			type: 'number',
			default: null
		},
		whisk_resting_time_minutes: {
			type: 'number',
			default: null
		},
	},
	parent: [ 'whisk/recipe' ],
	edit: Times,
	save: () => {
		return null;
	},
});

