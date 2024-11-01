import { withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';
import { Component, Fragment } from '@wordpress/element';
import { InnerBlocks } from '@wordpress/block-editor';
import '../store';

class PostEdit extends Component {

	constructor(props) {
		super(props);
		this.subscription = null;
	}

	componentDidMount() {
		const { getRecipe } = this.props;
		const subscriptionsPromise = getRecipe();

		subscriptionsPromise.then(subscription => {
			this.subscription = subscription;
		})
	}

	componentWillUnmount() {
		const { showBlock } = this.props;

		this.subscription();
		showBlock();
	}

	/**
	 * Render the component.
	 *
	 * @return {Object}
	 */
	render() {
		const TEMPLATE = [
			[ 'whisk/general', {} ],
			[ 'whisk/instructions', {} ],
			[ 'whisk/ingredients', {} ],
			[ 'whisk/notes', {} ],
			[ 'whisk/times', {} ],
			[ 'whisk/tags', {} ],
		];

		return (
			<Fragment>
				<main className="whisk-container wp-block-whisk-recipe">
					<article className="whisk-single">
							<InnerBlocks
								allowedBlocks={ ['whisk/instructions', 'whisk/ingredients', 'whisk/notes', 'whisk/tags', 'whisk/general', 'whisk/times'] }
								template={ TEMPLATE }
								templateLock="all"
							/>
					</article>
				</main>
			</Fragment>
		);
	}
}

export default compose([
	withDispatch(
		(dispatch, props) => {
			const { attributes, clientId } = props;
			return {
				getRecipe: () => dispatch('whisk/recipe').getRecipe(attributes.id, clientId),
				showBlock: () => dispatch('core/edit-post').showBlockTypes(['whisk/recipe']),
			}
		}
	)
])(PostEdit);
