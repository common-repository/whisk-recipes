import { Component, Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import ImageUpload from "../image-upload/ImageUpload";
import TextInsert from "../text-insert/TextInsert";
import VideoUpload from "../video-upload/VideoUpload";
import { TextControl } from '@wordpress/components';

export default class General extends Component {

	onChangeValue = (value, attribute) => {
		const { setAttributes } = this.props;
		setAttributes({
			[attribute]: value,
		})
	}

	render() {
		const { attributes, setAttributes } = this.props;
		const { image_id, image_url, video_id, video_url, video_external_url, title, description, whisk_servings, whisk_servings_unit } = attributes;

		return (
			<Fragment>
				<div className="whisk-row">
					<div className="whisk-column whisk-column-100">
						<h2 className="whisk-h2">
							<TextInsert
								setAttributes={ setAttributes }
								placeholder={__(
									'Recipe Title',
									'whisk-recipes'
								)}
								value={ title }
								attribute="title"
							/>
						</h2>
					</div>
				</div>
				<div className="whisk-row">
					<div className="whisk-column whisk-column-100 mb-25">
						<ImageUpload
							imageId={ image_id }
							imageUrl={ image_url }
							setAttributes={ setAttributes }
						/>
					</div>
				</div>
				<div className="whisk-row">
					<div className="whisk-column whisk-column-100 mb-25">
						<h4 className="whisk-h4">{ __('External video url', 'whisk-recipes') }</h4>
						<TextInsert
							setAttributes={ setAttributes }
							placeholder={ __(
								'Recipe Video Url',
								'whisk-recipes'
							) }
							value={ video_external_url }
							attribute="video_external_url"
						/>
					</div>
				</div>
				<div className="whisk-row">
					<div className="whisk-column whisk-column-100 mb-25">
						<h4 className="whisk-h4">{ __('Video upload', 'whisk-recipes') }</h4>
						<VideoUpload
							videoId={ video_id }
							videoUrl={ video_url }
							setAttributes={ setAttributes }
						/>
					</div>
				</div>
				<div className="whisk-row">
					<div className="whisk-column whisk-column-100 mb-25">
						<h4 className="whisk-h4">{ __('Recipe description', 'whisk-recipes') }</h4>
						<div className="whisk-content">
							<TextInsert
								setAttributes={ setAttributes }
								placeholder={__(
									'Please enter description',
									'whisk-recipes'
								)}
								value={ description }
								attribute="description"
							/>
						</div>
					</div>
				</div>
				<div className="whisk-row">
					<div className="whisk-column whisk-column-50">
						<TextControl
							label={ __('Servings', 'whisk-recipes') }
							value={ whisk_servings }
							type="number"
							onChange={ (value) => this.onChangeValue(value, 'whisk_servings') }
						/>
					</div>
					<div className="whisk-column whisk-column-50">
						<TextControl
							label={ __('Servings unit', 'whisk-recipes') }
							value={ whisk_servings_unit }
							onChange={ (value) => this.onChangeValue(value, 'whisk_servings_unit') }
						/>
					</div>
				</div>
			</Fragment>
		);
	}
}
