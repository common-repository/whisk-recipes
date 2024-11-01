import AsyncSelect from 'react-select/async';
import { Component } from '@wordpress/element';
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';

class PostSelect extends Component {

	onValueChange = value => {
		const { setAttributes } = this.props;
		setAttributes({
			id: value.value,
		})
	}

	render() {
		let { defaultOptions, filteredRecipes, isLoading } = this.props;

		if (defaultOptions) {
			defaultOptions = defaultOptions.map(item => {
				const label = item.id + ' ' + item.title.rendered;
				return {
					value: item.id,
					label
				};
			});
		}

		return (
			<AsyncSelect
				className="whisk-post-select"
				value={ null }
				onChange={this.onValueChange}
				defaultOptions={ defaultOptions }
				menuShouldScrollIntoView={false}
				loadOptions={ inputValue => filteredRecipes(inputValue) }
				clearable={ false }
				isLoading={ isLoading }
				isDisabled={ isLoading }
			/>
		);
	}
}

export default compose([
	withSelect(
		(select, props) => {
			return {
				defaultOptions: select('core').getEntityRecords('postType', 'whisk_recipe', { per_page: 20 }),
				isLoading: select('whisk/recipe').isLoading(),
			}
		}
	),
	withDispatch(
		(dispatch, props) => {
			return {
				filteredRecipes: search => dispatch('whisk/recipe').getFilteredRecipes(search),
			}
		}
	)
])(PostSelect);
