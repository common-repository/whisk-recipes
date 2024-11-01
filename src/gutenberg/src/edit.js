
import { __ } from '@wordpress/i18n';
import './editor.scss';
import PostSelect from "./post-select/PostSelect";
import PostEdit from "./post-edit/PostEdit";
import { Component, Fragment } from '@wordpress/element';
import { Button, Modal, TextControl } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import { withSelect, withDispatch } from '@wordpress/data';

class Edit extends Component {

	createButtonClick = () => {
		const { getTitle, onCreate } = this.props;
		onCreate(getTitle);
	}

	render() {
		const { attributes, setAttributes, setOpenModal, setClosedModal, isOpenModal, modalType, setTitle } = this.props;

		if ( attributes.id ) {
			return (
				<PostEdit { ...this.props } />
			)
		}

		return (
			<Fragment>
				<h2 className='whisk-h2'>{__('Whisk Recipe', 'whisk-recipes')}</h2>
				<Button
					onClick={ () => setOpenModal('existing') }
					className='wsk-button'
				>
					{__('Insert existing recipe', 'whisk-recipes')}
				</Button>
				<Button
					onClick={ () => setOpenModal('new') }
					className='wsk-button'
				>
					{__('Create New Recipe', 'whisk-recipes')}
				</Button>

				{ isOpenModal && modalType === 'existing' && (
					<Modal
						title={ __('Insert existing recipe', 'whisk-recipes') }
						onRequestClose={ setClosedModal }
						shouldCloseOnClickOutside={ false }
					>
						<PostSelect
							setAttributes={ setAttributes }
						/>
					</Modal>
				) }
				{ isOpenModal && modalType === 'new' && (
					<Modal
						title={ __('Create new recipe', 'whisk-recipes') }
						onRequestClose={ setClosedModal }
						shouldCloseOnClickOutside={ false }
					>
						<TextControl
							label={ __('Enter Recipe Title', 'whisk-recipes') }
							onChange={ setTitle }
						/>
						<Button
							onClick={ this.createButtonClick }
							className='wsk-button'
						>
							{__('Create Recipe', 'whisk-recipes')}
						</Button>
					</Modal>
				)}
			</Fragment>
		)
	}
}

export default compose([
	withSelect(
		(select) => {
			return {
				isOpenModal: select('whisk/recipe').isOpenModal(),
				modalType: select('whisk/recipe').getModalType(),
				getTitle: select('whisk/recipe').getTitle(),
			}
		}
	),
	withDispatch(
		(dispatch, props) => {
			const { clientId } = props;

			return {
				setOpenModal: type => dispatch('whisk/recipe').setOpenModal(type),
				setClosedModal: () => dispatch('whisk/recipe').setClosedModal(),
				onCreate: value => dispatch('whisk/recipe').handleRecipeCreate(value, clientId),
				setTitle: value => dispatch('whisk/recipe').setTitle(value),
			}
		}
	)
])(Edit);
