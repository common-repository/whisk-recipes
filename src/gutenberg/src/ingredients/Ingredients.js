import { Component, Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { InnerBlocks } from '@wordpress/block-editor';
import { withSelect } from '@wordpress/data';

class Ingredients extends Component {

	render() {
		const { noInnerBlocks } = this.props;

		return (
			<Fragment>
				<h3 className="whisk-h3">{ __('Ingredients', 'whisk-recipes') }</h3>
				<p>
					{ noInnerBlocks() ? __('Add your ingredients.', 'whisk-recipes') : '' }
				</p>
				<InnerBlocks
					allowedBlocks={ ['whisk/ingredient'] }
					templateLock={ false }
				/>
			</Fragment>
		);
	}
}

export default withSelect(
	(select, props) => {
		const { clientId } = props;
		return {
			noInnerBlocks: () => select('core/block-editor').getBlocks(clientId).length === 0,
		}
})(Ingredients);
