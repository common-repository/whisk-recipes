import { Component, Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import TaxonomySelect from '../taxonomy-select/TaxonomySelect';

export default class Tags extends Component {

	render() {
		const { clientId, attributes, setAttributes } = this.props;

		return (
			<Fragment>
				<div className="mb-25">
					<h3 className="whisk-h3">{__('Meal types', 'whisk-recipes')}</h3>
					<TaxonomySelect
						attributes={attributes}
						setAttributes={setAttributes}
						taxonomy='whisk_meal_type'
						taxInRest='meal-types'
						meta='whisk_meal_types'
						clientId={clientId}
						isMulti
					/>
				</div>
				<div className="mb-25">
					<h3 className="whisk-h3">{__('Tags', 'whisk-recipes')}</h3>
					<TaxonomySelect
						attributes={attributes}
						setAttributes={setAttributes}
						taxonomy='whisk_tag'
						taxInRest='tags'
						meta='whisk_tags'
						clientId={clientId}
						isMulti
					/>
				</div>
				<div className="mb-25">
					<h3 className="whisk-h3">{__('Diets', 'whisk-recipes')}</h3>
					<TaxonomySelect
						attributes={attributes}
						setAttributes={setAttributes}
						taxonomy='whisk_diet'
						taxInRest='diets'
						meta='whisk_diets'
						clientId={clientId}
						isMulti
					/>
				</div>
				<div className="mb-25">
					<h3 className="whisk-h3">{__('Cuisines', 'whisk-recipes')}</h3>
					<TaxonomySelect
						attributes={attributes}
						setAttributes={setAttributes}
						taxonomy='whisk_cuisine'
						taxInRest='cuisines'
						meta='whisk_cuisines'
						clientId={clientId}
						isMulti
					/>
				</div>
				<div className="mb-25">
					<h3 className="whisk-h3">{__('Cooking Techniques', 'whisk-recipes')}</h3>
					<TaxonomySelect
						attributes={attributes}
						setAttributes={setAttributes}
						taxonomy='whisk_cooking_technique'
						taxInRest='cooking_techniques'
						meta='whisk_cooking_techniques'
						clientId={clientId}
						isMulti
					/>
				</div>
				<div className="mb-25">
					<h3 className="whisk-h3">{__('Nutrition', 'whisk-recipes')}</h3>
					<TaxonomySelect
						attributes={attributes}
						setAttributes={setAttributes}
						taxonomy='whisk_nutrition'
						taxInRest='nutrition'
						meta='whisk_nutrition'
						clientId={clientId}
						isMulti
					/>
				</div>
			</Fragment>
		);
	}
}
