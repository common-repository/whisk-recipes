import { Component, Fragment } from '@wordpress/element';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { withDispatch } from '@wordpress/data';

class ImageUpload extends Component {

	selectImage = value => {
		const { setAttributes } = this.props;
		setAttributes({
			image_id: value.id,
			image_url: value.url,
		})
	}

	onImageRemove = e => {
		e.stopPropagation();
		const { setAttributes } = this.props;
		setAttributes({
			image_id: undefined,
			image_url: '',
		})
	}

	render() {
		const { imageId, imageUrl } = this.props;

		return (
			<MediaUploadCheck>
				<MediaUpload
					allowedTypes={ ['image'] }
					render={ ({ open }) => (
						<Button
							className={ imageId ? 'whisk-recipe-image__toggle' : 'whisk-recipe-image__preview'}
							onClick={ open }
						>
							{ imageId &&
								<Fragment>
									<div className="whisk-recipe-image__wrapper">
										<img className='whisk-image' src={ imageUrl } alt="" />
										<button
											type="button"
											className="whisk-recipe-image__remove dashicons-before dashicons-no-alt"
											onClick={ this.onImageRemove }
										>
										</button>
									</div>
								</Fragment>
							}
							{ ! imageId && __('Choose an image', 'whisk-recipes')}
						</Button>
					)
					}
					onSelect={ this.selectImage }
				/>
			</MediaUploadCheck>
		)
	}
}

export default withDispatch(
	(dispatch, props) => {
		const { attributes } = props;
		return {
			selectImage: media => dispatch('whisk/recipe').selectField('IMAGE', media, attributes.id),
		}
	}
)(ImageUpload);
