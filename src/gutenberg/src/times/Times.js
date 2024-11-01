import { Component, Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { TextControl } from '@wordpress/components';

export default class Times extends Component {

	onChangeValue = (value, attribute) => {
		const { setAttributes } = this.props;
		setAttributes({
			[attribute]: value,
		})
	}

	render() {
		const { attributes } = this.props;
		const {
			whisk_prep_time_days,
			whisk_prep_time_hours,
			whisk_prep_time_minutes,
			whisk_cook_time_days,
			whisk_cook_time_hours,
			whisk_cook_time_minutes,
			whisk_resting_time_days,
			whisk_resting_time_hours,
			whisk_resting_time_minutes,
		} = attributes;

		return (
			<Fragment>
				<h3 className="whisk-h3">
					{ __('Times', 'whisk-recipes') }
				</h3>
				<h4 className="whisk-h4">
					{ __('Prep Time', 'whisk-recipes') }
				</h4>
				<div className="whisk-row">
					<div className="whisk-column whisk-column-33">
						<TextControl
							label={ __('Days', 'whisk-recipes') }
							value={ whisk_prep_time_days }
							type="number"
							onChange={ (value) => this.onChangeValue(value, 'whisk_prep_time_days') }
						/>
					</div>
					<div className="whisk-column whisk-column-33">
						<TextControl
							label={ __('Hours', 'whisk-recipes') }
							value={ whisk_prep_time_hours }
							type="number"
							onChange={ (value) => this.onChangeValue(value, 'whisk_prep_time_hours') }
						/>
					</div>
					<div className="whisk-column whisk-column-33">
						<TextControl
							label={ __('Minutes', 'whisk-recipes') }
							value={ whisk_prep_time_minutes }
							type="number"
							onChange={ (value) => this.onChangeValue(value, 'whisk_prep_time_minutes') }
						/>
					</div>
				</div>
				<h4 className="whisk-h4">
					{ __('Cook Time', 'whisk-recipes') }
				</h4>
				<div className="whisk-row">
					<div className="whisk-column whisk-column-33">
						<TextControl
							label={ __('Days', 'whisk-recipes') }
							value={ whisk_cook_time_days }
							type="number"
							onChange={ (value) => this.onChangeValue(value, 'whisk_cook_time_days') }
						/>
					</div>
					<div className="whisk-column whisk-column-33">
						<TextControl
							label={ __('Hours', 'whisk-recipes') }
							value={ whisk_cook_time_hours }
							type="number"
							onChange={ (value) => this.onChangeValue(value, 'whisk_cook_time_hours') }
						/>
					</div>
					<div className="whisk-column whisk-column-33">
						<TextControl
							label={ __('Minutes', 'whisk-recipes') }
							value={ whisk_cook_time_minutes }
							type="number"
							onChange={ (value) => this.onChangeValue(value, 'whisk_cook_time_minutes') }
						/>
					</div>
				</div>
				<h4 className="whisk-h4">
					{ __('Resting Time', 'whisk-recipes') }
				</h4>
				<div className="whisk-row">
					<div className="whisk-column whisk-column-33">
						<TextControl
							label={ __('Days', 'whisk-recipes') }
							value={ whisk_resting_time_days }
							type="number"
							onChange={ (value) => this.onChangeValue(value, 'whisk_resting_time_days') }
						/>
					</div>
					<div className="whisk-column whisk-column-33">
						<TextControl
							label={ __('Hours', 'whisk-recipes') }
							value={ whisk_resting_time_hours }
							type="number"
							onChange={ (value) => this.onChangeValue(value, 'whisk_resting_time_hours') }
						/>
					</div>
					<div className="whisk-column whisk-column-33">
						<TextControl
							label={ __('Minutes', 'whisk-recipes') }
							value={ whisk_resting_time_minutes }
							type="number"
							onChange={ (value) => this.onChangeValue(value, 'whisk_resting_time_minutes') }
						/>
					</div>
				</div>
			</Fragment>
		)
	}
}
