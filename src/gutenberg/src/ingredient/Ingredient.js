import { RichText } from '@wordpress/block-editor';
import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import TaxonomySelect from '../taxonomy-select/TaxonomySelect';
import { TextControl } from '@wordpress/components';

export default class Ingredient extends Component {

	onChangeValue = (value, field) => {
		const { setAttributes } = this.props;
		setAttributes({
			[field]: value
		})
	}

	render() {
		const { attributes, setAttributes, clientId } = this.props;
		const { whisk_ingredient_amount, whisk_ingredient_id, whisk_ingredient_unit } = attributes;

		return (
			<div className='whisk-instruction-container'>
				<div className="whisk-row">
					<div className="whisk-column whisk-column-25">
						<p className="whisk-text-wrap">
							<TextControl
								label={ __('Amount', 'whisk-recipes') }
								value={ whisk_ingredient_amount }
								onChange={ value => this.onChangeValue(value, 'whisk_ingredient_amount') }
								type="number"
							/>
						</p>
					</div>
					<div className="whisk-column whisk-column-25">
						<p className="whisk-text-wrap">
							<TextControl
								label={ __('Unit', 'whisk-recipes') }
								value={ whisk_ingredient_unit }
								onChange={ value => this.onChangeValue(value, 'whisk_ingredient_unit') }
							/>
						</p>
					</div>
					<div className="whisk-column whisk-column-50">
						<p className="whisk-ingredient__wrap">
							<label
								className="whisk-ingredient__label"
							>
								{ __('Ingredient', 'whisk-recipes') }
							</label>
							<TaxonomySelect
								attributes={attributes}
								setAttributes={setAttributes}
								taxonomy='whisk_ingredient'
								taxInRest='ingredients'
								meta='whisk_ingredients'
								value={whisk_ingredient_id}
								isMulti={false}
								clientId={clientId}
								subField="whisk_ingredient_id"
							/>
						</p>
					</div>
				</div>
			</div>
		)
	}
}
