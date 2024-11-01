import { Component } from '@wordpress/element';
import { RichText } from '@wordpress/block-editor';

export default class TextInsert extends Component {

	onChangeValue = value => {
		const { setAttributes, attribute } = this.props;
		setAttributes({
			[attribute]: value,
		})
	}

	render() {
		const { placeholder, value } = this.props;

		return (
			<RichText
				placeholder={ placeholder }
				value={ value }
				onChange={ this.onChangeValue }
				formattingControls={ [] }
			/>
		);
	}
}
