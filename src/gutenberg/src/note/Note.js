import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';

export default class Note extends Component {

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
		const { whisk_note } = attributes;

		this.editor = editor;

		if ( whisk_note ) {
			editor.on( 'loadContent', () => editor.setContent( whisk_note ) );
		}

		editor.on( 'blur', () => {
			const { whisk_note } = attributes;
			const value = editor.getContent();

			if (value !== whisk_note) {
				setAttributes({
					whisk_note: value,
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

	render() {
		const { clientId } = this.props;

		return (
			<div className='whisk-note-container'>
				<div className="whisk-note__editor">
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
		);
	}
}
