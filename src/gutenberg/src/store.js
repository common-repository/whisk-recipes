import { registerStore, dispatch, select, subscribe } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';

const DEFAULT_STATE = {};

const actions = {
	* getFilteredTerms(search, taxInRest) {
		const response = yield {
			type: 'FETCH_FILTERED_TERMS',
			search,
			taxInRest
		}

		return response.map(item => {
			return {
				value: item.id,
				label: item.name
			};
		});

	},
	* getFilteredRecipes(search) {
		const response = yield {
			type: 'FETCH_FILTERED_RECIPES',
			search
		}

		return response.map(item => {
			const label = item.id + ' ' + item.title.rendered;
			return {
				value: item.id,
				label
			};
		});
	},
	* handleTermCreate(newValue, oldTerms, meta, taxInRest, clientId) {
		yield {
			type: 'SET_LOADING'
		}

		const createResponse = yield {
			type: 'CREATE_TERMS',
			name: newValue,
			taxInRest
		}

		const newTerm = [{
			label: createResponse.name,
			value: createResponse.id
		}]
		const currentTerms = oldTerms ? oldTerms.concat(newTerm) : newTerm;
		dispatch('core/block-editor').updateBlockAttributes(clientId, { [meta]: currentTerms });

		return {
			type: 'SET_LOADING'
		}
	},
	* handleRecipeCreate(newValue, clientId) {
		yield {
			type: 'SET_LOADING'
		}

		const createResponse = yield {
			type: 'CREATE_RECIPE',
			title: newValue,
		}
		dispatch('core/block-editor').updateBlockAttributes(clientId, { id: createResponse.id });

		return {
			type: 'SET_LOADING'
		}
	},
	* getRecipe(id, rootClientId) {
		const recipe = yield {
			type: 'FETCH_RECIPE',
			id,
		}

		const templateBlocks = select('core/editor').getBlocks(rootClientId);
		const repeaters = [
			{
				field: 'whisk_instructions',
				block: 'whisk/instructions',
				createInnerBlocks: (item, idx, innerBlocks) => {
					switch (item._type) {
						case 'separator':
							innerBlocks.push(wp.blocks.createBlock('whisk/instruction-title', {
								whisk_step_separator_name: item.whisk_step_separator_name,
								idx
							}));
							break;
						case 'step':
							innerBlocks.push(wp.blocks.createBlock('whisk/instruction',
								{
									instruction: {
										whisk_step_image: item.whisk_step_image,
										whisk_step_image_url: item.whisk_step_image_url,
										whisk_step_instruction: item.whisk_step_instruction,
										whisk_step_summary: item.whisk_step_summary,
										whisk_step_video: item.whisk_step_video,
										whisk_step_video_url: item.whisk_step_video_url,
									},
									idx
								}
								)
							);
							break;
					}
				},
				createUpdateArray: (clientId, data) => {
					const attributes = select('core/editor').getBlockAttributes(clientId);
					if (attributes.hasOwnProperty('instruction')) {
						data.push({ _type: 'step', ...attributes.instruction });
					} else {
						data.push({
							_type: 'separator',
							whisk_step_separator_name: attributes.whisk_step_separator_name
						})
					}
				}
			},
			{
				field: 'whisk_ingredients',
				block: 'whisk/ingredients',
				createInnerBlocks: (item, idx, innerBlocks) => {
					innerBlocks.push(wp.blocks.createBlock('whisk/ingredient', {
						whisk_ingredient_amount: item.whisk_ingredient_amount,
						whisk_ingredient_id: item.whisk_ingredient_id,
						whisk_ingredient_note: item.whisk_ingredient_note,
						_type: '_',
						whisk_ingredient_unit: item.whisk_ingredient_unit
					}));
				},
				createUpdateArray: (clientId, data) => {
					const attributes = select('core/editor').getBlockAttributes(clientId);
					data.push({
						whisk_ingredient_amount: attributes.whisk_ingredient_amount,
						whisk_ingredient_id: attributes.whisk_ingredient_id,
						whisk_ingredient_note: attributes.whisk_ingredient_note,
						_type: '_',
						whisk_ingredient_unit: attributes.whisk_ingredient_unit
					})
				}
			},
			{
				field: 'whisk_notes',
				block: 'whisk/notes',
				createInnerBlocks: (item, idx, innerBlocks) => {
					innerBlocks.push(wp.blocks.createBlock('whisk/note', {
						whisk_note: item.whisk_note,
						_type: '_',
					}));
				},
				createUpdateArray: (clientId, data) => {
					const attributes = select('core/editor').getBlockAttributes(clientId);
					data.push({
						whisk_note: attributes.whisk_note,
						_type: '_',
					})
				}
			},
		];
		const blocks = [
			{
				blockName: 'whisk/tags',
				metas: [
					{
						meta: 'whisk_meal_types',
						taxInRest: 'meal-types',
					},
					{
						meta: 'whisk_tags',
						taxInRest: 'tags',
					},
					{
						meta: 'whisk_diets',
						taxInRest: 'diets',
					},
					{
						meta: 'whisk_cuisines',
						taxInRest: 'cuisines',
					},
					{
						meta: 'whisk_cooking_techniques',
						taxInRest: 'cooking_techniques',
					},
					{
						meta: 'whisk_nutrition',
						taxInRest: 'nutrition',
					},
				],
			},
		];
		const generalFields = [
			{
				blockName: 'whisk/general',
				mapping: [
					{
						attribute: 'image_id',
						recipeField: 'featured_media',
					},
					{
						attribute: 'image_url',
						recipeField: 'featured_image_url',
					},
					{
						attribute: 'video_id',
						recipeField: '_whisk_video',
					},
					{
						attribute: 'video_url',
						recipeField: 'whisk_video_url',
					},
					{
						attribute: 'video_external_url',
						recipeField: '_whisk_video_url',
					},
					{
						attribute: 'title',
						recipeField: 'title',
					},
					{
						attribute: 'description',
						recipeField: 'content',
					},
					{
						attribute: 'whisk_servings',
						recipeField: '_whisk_servings',
					},
					{
						attribute: 'whisk_servings_unit',
						recipeField: '_whisk_servings_unit',
					},
				]
			},
			{
				blockName: 'whisk/times',
				mapping: [
					{
						attribute: 'whisk_prep_time_days',
						recipeField: '_whisk_prep_time_days',
					},
					{
						attribute: 'whisk_prep_time_hours',
						recipeField: '_whisk_prep_time_hours',
					},
					{
						attribute: 'whisk_prep_time_minutes',
						recipeField: '_whisk_prep_time_minutes',
					},
					{
						attribute: 'whisk_cook_time_days',
						recipeField: '_whisk_cook_time_days',
					},
					{
						attribute: 'whisk_cook_time_hours',
						recipeField: '_whisk_cook_time_hours',
					},
					{
						attribute: 'whisk_cook_time_minutes',
						recipeField: '_whisk_cook_time_minutes',
					},
					{
						attribute: 'whisk_resting_time_days',
						recipeField: '_whisk_resting_time_days',
					},
					{
						attribute: 'whisk_resting_time_hours',
						recipeField: '_whisk_resting_time_hours',
					},
					{
						attribute: 'whisk_resting_time_minutes',
						recipeField: '_whisk_resting_time_minutes',
					},
				]
			},
		];

		generalFields.forEach(blockData => {
			let currentBlock = templateBlocks.find(templateBlock => templateBlock.name === blockData.blockName);
			if (!currentBlock) {
				return;
			}
			blockData.mapping.forEach(data => {
				if (recipe[data.recipeField]) {
					const value = data.recipeField === 'title' || data.recipeField === 'content' ? recipe[data.recipeField].rendered : recipe[data.recipeField];
					dispatch('core/block-editor').updateBlockAttributes(currentBlock.clientId, { [data.attribute]: value });
				}
			})
		});

		blocks.forEach(block => {
			block.metas.forEach(fieldObj => {
				let currentBlock = templateBlocks.find(templateBlock => templateBlock.name === block.blockName);
				if (!currentBlock) {
					return;
				}
				if (recipe[fieldObj.meta].length) {
					dispatch('core/block-editor').updateBlockAttributes(currentBlock.clientId, { [fieldObj.meta]: recipe[fieldObj.meta] });
				}
			})
		});

		repeaters.forEach((repeater, idx) => {
			if (recipe[repeater.field].length) {
				let rootBlock = templateBlocks.find(block => block.name === repeater.block);
				if (!rootBlock) {
					return;
				}
				let innerBlocks = [];

				recipe[repeater.field].forEach((item, idx) => repeater.createInnerBlocks(item, idx, innerBlocks));
				dispatch('core/block-editor').replaceInnerBlocks(rootBlock.clientId, innerBlocks, false);
			}
		});

		// subscribe for post save for meta saving
		const { isSavingPost, isAutosavingPost } = select('core/editor');
		var updated = true;
		const unsubscribe = subscribe( () => {
			if (isSavingPost() && !isAutosavingPost()) {
				updated = false;
			} else {
				if (!updated) {
					const blocksOrder = select('core/editor').getBlockOrder(rootClientId);
					const id = select('core/editor').getBlockAttributes(rootClientId).id;
					let data = {};
					let innerBlocks = [];
					let innerData = [];
					blocksOrder.forEach(blockId => {
						innerData = [];
						let attributes = select('core/editor').getBlockAttributes(blockId);
						switch (select('core/editor').getBlockName(blockId)) {
							case 'whisk/general':
								generalFields.filter(field => field.blockName === 'whisk/general')[0].mapping.forEach(item => {
									data[item.recipeField] = attributes[item.attribute];
								});
								break;
							case 'whisk/times':
								generalFields.filter(field => field.blockName === 'whisk/times')[0].mapping.forEach(item => {
									data[item.recipeField] = attributes[item.attribute];
								});
								break;
							case 'whisk/instructions':
								innerBlocks = select('core/editor').getBlockOrder(blockId);
								innerBlocks.forEach(innerBlockId => {
									attributes = select('core/editor').getBlockAttributes(innerBlockId);
									switch (select('core/editor').getBlockName(innerBlockId)) {
										case 'whisk/instruction':
											innerData.push({ _type: 'step', ...attributes.instruction });
											break;
										case 'whisk/instruction-title':
											innerData.push({
												_type: 'separator',
												whisk_step_separator_name: attributes.whisk_step_separator_name
											});
											break;
									}
								});
								data.whisk_instructions = innerData;
								break;
							case 'whisk/ingredients':
								innerBlocks = select('core/editor').getBlockOrder(blockId);
								let catsData = [];
								innerBlocks.forEach(innerBlockId => {
									attributes = select('core/editor').getBlockAttributes(innerBlockId);
									const ids_array = attributes.whisk_ingredient_id ? attributes.whisk_ingredient_id.map(item => {
										return item.value;
									}) : [];
									innerData.push({
										whisk_ingredient_amount: attributes.whisk_ingredient_amount,
										whisk_ingredient_id: ids_array,
										whisk_ingredient_note: attributes.whisk_ingredient_note,
										_type: '_',
										whisk_ingredient_unit: attributes.whisk_ingredient_unit
									});
									catsData = catsData.concat(ids_array);
								});
								data.whisk_ingredients = innerData;
								data.ingredients = catsData;
								break;
							case 'whisk/notes':
								innerBlocks = select('core/editor').getBlockOrder(blockId);
								innerBlocks.forEach(innerBlockId => {
									attributes = select('core/editor').getBlockAttributes(innerBlockId);
									innerData.push({
										whisk_note: attributes.whisk_note,
										_type: '_',
									})
								});
								data.whisk_notes = innerData;
								break;
							case 'whisk/tags':
								blocks[0].metas.forEach(item => {
									const ids_array = attributes[item.meta] ? attributes[item.meta].map(item => {
										return item.value;
									}) : [];
									data[item.meta] = ids_array;
									data[item.taxInRest] = ids_array;
								});
								break;
						}
					});
					dispatch('whisk/recipe').updateBlock(id, data);
					updated = true;
				}
			}
		});

		dispatch('core/block-editor').updateBlockAttributes(rootClientId, { updated: + new Date() });
		dispatch('core/edit-post').hideBlockTypes(['whisk/recipe']);

		return unsubscribe;

	},
	* updateBlock(id, data) {
		yield {
			type: 'UPDATE_BLOCK',
			id,
			data,
		}
	},
	setOpenModal(modalType) {
		return {
			type: 'SET_OPEN',
			modalType
		}
	},
	setClosedModal() {
		return {
			type: 'SET_CLOSED',
		}
	},
	setTitle(title) {
		return {
			type: 'SET_TEMP_TITLE',
			title
		}
	}
}

const reducer = (state = DEFAULT_STATE, action) => {
	switch(action.type) {
		case 'SET_LOADING':
			const loading = state.loading;

			return {
				...state,
				loading: !loading,
			}
		case 'SET_OPEN':
			return {
				...state,
				modalType: action.modalType,
				isOpen: true,
			}
		case 'SET_CLOSED':
			return {
				...state,
				isOpen: false,
			}
		case 'SET_TEMP_TITLE':
			return {
				...state,
				title: action.title,
			}
		default:
			return state;
	}
}

const selectors = {
	isLoading(state) {
		return state.loading;
	},
	isOpenModal(state) {
		return state.isOpen;
	},
	getModalType(state) {
		return state.modalType;
	},
	getTitle(state) {
		return state.title;
	}
}

registerStore('whisk/recipe', {
	reducer,
	selectors,
	actions,
	controls: {
		UPDATE_BLOCK({ id, data }) {
			return apiFetch(
				{
					path: `/wp/v2/recipes/${id}`,
					method: 'POST',
					data
				}
			)
		},
		FETCH_FILTERED_TERMS({ search, taxInRest }) {
			return apiFetch(
				{
					path: `/wp/v2/${taxInRest}?search=${search}&per_page=100`
				}
			)
		},
		FETCH_FILTERED_RECIPES({ search }) {
			return apiFetch(
				{
					path: `/wp/v2/recipes?search=${search}&per_page=100`
				}
			)
		},
		CREATE_TERMS({ name, taxInRest }) {
			 return apiFetch(
				{
					path: `/wp/v2/${taxInRest}`,
					method: 'POST',
					data: {
						name: name,
					},
				}
			)
		},
		CREATE_RECIPE({ title }) {
			return apiFetch(
				{
					path: `/wp/v2/recipes`,
					method: 'POST',
					data: {
						title,
						status: 'publish',
					},
				}
			)
		},
		FETCH_RECIPE({ id }) {
			return apiFetch(
				{
					path: `/wp/v2/recipes/${id}`
				}
			)
		},
	},
});


