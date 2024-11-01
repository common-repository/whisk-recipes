import { withDispatch, withSelect } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import { RichText } from '@wordpress/block-editor';
import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

export default class InstructionTitle extends Component {

	onChangeValue = value => {
		const { setAttributes } = this.props;
		setAttributes({
			whisk_step_separator_name: value
		})
	}

	render() {
		const { attributes } = this.props;
		const { whisk_step_separator_name } = attributes;

		return (
			<h4 className='whisk-h4'>
				<RichText
					placeholder={ __('Insert title', 'whisk-recipes') }
					value={ whisk_step_separator_name }
					onChange={ this.onChangeValue }
				/>
			</h4>
		);
	}
}
