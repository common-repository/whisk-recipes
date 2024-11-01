import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';

export default class Instruction extends Component {

	componentDidMount() {
		const tinymceBind = window.tinymce.DOM.bind;
		window.tinymce.DOM.bind = (target, name, func, scope) => {
			// TODO This is only necessary until https://github.com/tinymce/tinymce/issues/4355 is fixed
			if (name === 'mouseup' && func.toString().includes('throttle()')) {
				return func;
			} else {
				return tinymceBind(target, name, func, scope);
			}
		};
		if (!this.editor) {
			this.initialize();
		}
	}

	componentDidUpdate() {
		if (!this.editor) {
			this.initialize();
		}
	}

	componentWillUnmount() {
		const { clientId } = this.props;
		if ( this.editor ) {
			window.tinymce.execCommand('mceRemoveEditor', true, `editor-${ clientId }`);
		}

	}

	focus = () => {
		if ( this.editor ) {
			this.editor.focus();
		}
	}

	initialize = () => {
		const { baseURL, suffix } = window.wpEditorL10n.tinymce;
		window.tinymce.EditorManager.overrideDefaults( {
			base_url: baseURL,
			suffix,
		} );
		const { settings } = window.wpEditorL10n.tinymce;
		const { clientId } = this.props;
		const editorOptions = {
			...settings,
			selector: `#editor-${ clientId }`,
			setup: this.onSetup,
			inline: true,
			content_css: false,
			fixed_toolbar_container: `#toolbar-${ clientId }`,
			plugins: 'lists',
			external_plugins: '',
			menubar: false,
			indent: false,
			toolbar1: 'bold,italic,strikethrough,bullist,numlist,alignleft,aligncenter,alignright,undo,redo',
			toolbar2: '',
			toolbar3: '',
			toolbar4: ''
		};

		window.tinymce.init( editorOptions );
	};

	onSetup = (editor) => {
		const { attributes, setAttributes } = this.props;
		const { instruction } = attributes;
		const { whisk_step_instruction } = instruction;

		this.editor = editor;

		if ( whisk_step_instruction ) {
			editor.on( 'loadContent', () => editor.setContent( whisk_step_instruction ) );
		}

		editor.on( 'blur', () => {
			const { instruction } = attributes;
			const { whisk_step_instruction } = instruction;
			const value = editor.getContent();

			if (value !== whisk_step_instruction) {
				setAttributes({
					instruction: { ...instruction, whisk_step_instruction: value }
				})
			}

		} );

		editor.on( 'init', () => {
			const rootNode = this.editor.getBody();

			// Create the toolbar by refocussing the editor.
			if ( document.activeElement === rootNode ) {
				rootNode.blur();
				this.editor.focus();
			}
		} );
	}

	onImageSelect = value => {
		const { setAttributes, attributes: { instruction } } = this.props;
		setAttributes({
			instruction: { ...instruction, whisk_step_image: value.id, whisk_step_image_url: value.url }
		})
	}

	onImageRemove = e => {
		e.stopPropagation();
		const { setAttributes, attributes: { instruction } } = this.props;
		setAttributes({
			instruction: { ...instruction, whisk_step_image: undefined, whisk_step_image_url: '' }
		})
	}

	render() {
		const { attributes, clientId } = this.props;
		const { instruction } = attributes;

		return (
			<div className='whisk-instruction-container'>
				<div className="whisk-row">
					<div className="whisk-column whisk-column-34">
						<div className="whisk-cover">
							<MediaUploadCheck>
								<MediaUpload
									allowedTypes={ ['image'] }
									render={ ({ open }) => (
										<Button
											className={ instruction.whisk_step_image ? 'whisk-recipe-image__toggle' : 'whisk-recipe-image__preview'}
											onClick={ open }
										>
											{ instruction.whisk_step_image &&
												<div className="whisk-recipe-image__wrapper">
													<img className='whisk-image' src={ instruction.whisk_step_image_url } alt='' />
													<button
														type="button"
														className="whisk-recipe-image__remove dashicons-before dashicons-no-alt"
														onClick={ this.onImageRemove }
													>
													</button>
												</div>
											 }
											{ ! instruction.whisk_step_image && __('Choose an image', 'whisk-recipes')}
										</Button>
									)
									}
									onSelect={ this.onImageSelect }
								/>
							</MediaUploadCheck>
						</div>
					</div>
					<div className="whisk-column whisk-column-66">
						<div className="whisk-instruction__editor">
							<div
								key="toolbar"
								id={ `toolbar-${ clientId }` }
								className="freeform-toolbar"
								onClick={ this.focus }
								data-placeholder={ __( 'Classic' ) }
							/>
							<div
								key="editor"
								id={ `editor-${ clientId }` }
								className="wp-block-freeform core-blocks-rich-text__tinymce"
							/>
						</div>
					</div>
				</div>
			</div>
		);
	}
}
