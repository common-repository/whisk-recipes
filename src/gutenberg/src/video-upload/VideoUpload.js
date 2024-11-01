import { Component } from '@wordpress/element';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

export default class VideoUpload extends Component {

	selectVideo = value => {
		const { setAttributes } = this.props;
		setAttributes({
			video_id: value.id,
			video_url: value.url,
		})
	}

	render() {
		const { videoId, videoUrl } = this.props;

		return (
			<MediaUploadCheck>
				<MediaUpload
					allowedTypes={ ['video'] }
					render={ ({ open }) => (
						<Button
							className={ videoId ? 'whisk-recipe-image__toggle' : 'whisk-recipe-image__preview'}
							onClick={ open }
						>
							{ videoId && <video controls src={ videoUrl }></video> }
							{ ! videoId && __('Choose video', 'whisk-recipes')}
						</Button>
					)
					}
					onSelect={ this.selectVideo }
				/>
			</MediaUploadCheck>
		)
	}
}
