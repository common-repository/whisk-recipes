import { Component } from '@wordpress/element';
import AsyncCreatableSelect from 'react-select/async-creatable';
import { withSelect, withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';

class TaxonomySelect extends Component {

	onChange = newValue => {
		const { setAttributes, meta, subField } = this.props;
		const attribute = subField ? subField : meta;
		if (!Array.isArray(newValue)) {
			newValue = [newValue];
		}

		setAttributes({
			[attribute]: newValue,
		})
	}

	onCreate = newValue => {
		const { onCreate } = this.props;
		onCreate(newValue);
		this.onChange(newValue);
	}

	render() {
		let { defaultTerms, filteredTerms, isLoading, meta, isMulti, clientId, attributes, value } = this.props;
		if (defaultTerms) {
			defaultTerms = defaultTerms.map(item => {
				return {
					value: item.id,
					label: item.name
				};
			});
		}
		if (isMulti === undefined) {
			isMulti = true;
		}

		const loadingMessage = () => {
			return __('Loading...', 'whisk-recipes');
		};
		const createLabel = (inputValue) => {
			return __('Create ', 'whisk-recipes') + ' ' + inputValue;
		};

		return (
			<AsyncCreatableSelect
				defaultOptions={ defaultTerms }
				loadOptions={ inputValue => filteredTerms(inputValue) }
				onCreateOption={ this.onCreate }
				onChange={ this.onChange }
				isLoading={ isLoading }
				isDisabled={ isLoading }
				value={ value ? value : attributes[meta] }
				placeholder={ __('Select...', 'whisk-recipes') }
				loadingMessage={ loadingMessage }
				formatCreateLabel={ createLabel }
				isMulti={ isMulti }
				isClearable
				id={ meta + clientId }
				name={ meta + clientId }
				instanceId={ meta + clientId }
				inputId={ meta + clientId }
			/>
		);
	}
}

export default compose([
	withSelect(
		(select, props) => {
			const { taxonomy } = props;
			return {
				defaultTerms: select('core').getEntityRecords('taxonomy', taxonomy, { per_page: 20, orderby: 'count' }),
				isLoading: select('whisk/recipe').isLoading(),
			}
		}
	),
	withDispatch(
		(dispatch, props) => {
			const { attributes, meta, taxInRest, clientId, isMulti, subField } = props;
			return {
				filteredTerms: search => dispatch('whisk/recipe').getFilteredTerms(search, taxInRest),
				onCreate: value => {
					const allValues = isMulti ? attributes[meta] : [];
					const attribute = subField ? subField : meta;

					dispatch('whisk/recipe').handleTermCreate(value, allValues, attribute, taxInRest, clientId)
				},
			}
		}
	)
])(TaxonomySelect);
