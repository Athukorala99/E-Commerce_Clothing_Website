/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/packages/packages-block-editor/#useBlockProps
 */
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';

// import ServerSideRender from '@wordpress/server-side-render';
import { Panel, PanelBody, PanelRow, SelectControl, Button } from '@wordpress/components';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';
// import InnerHtml from 'dangerously-set-html-content';

// import logo from './light-logo.svg';

import IframeResizer from 'iframe-resizer-react'


export default function Edit( { attributes, setAttributes } ) {
	const blockProps = useBlockProps();

	function updateID( newID ) {
		setAttributes({ id: Number(newID) });
		setPublishBtnState(newID);
		// fetchSlider(newID);
	}

	// function getSliderTitle( sliderID ) {
	// 	let sliderTitle = null;
	// 	if ( sliderID ) {
	// 		sliderTitle = depicterSliders.list.map( function( item ) {
	// 			if ( item.value == sliderID ) {
	// 				return item.label;
	// 			}
	// 			return null;
	// 		} );
	// 	}
	// 	return sliderTitle ? sliderTitle : 'Select slider from list';
	// }

	function editSlider() {
		let sliderID = document.getElementById('dep-slider-list').value;
		let editorUrl = window.depicterSliders.editor_url.replace('document=1', 'document=' + sliderID);
		window.open(editorUrl);
	}

	function publishSlider() {
		let sliderID = document.getElementById('dep-slider-list').value;
		let publishBtn = document.getElementById('dep-publish-slider-btn');
		publishBtn.setAttribute('disabled', true);
		publishBtn.classList.add('is-busy');

		var data = new FormData();
		data.append('ID', sliderID);
		data.append('status', 'published');

		window.fetch( window.depicterSliders.ajax_url + "?action=depicter/document/store", {
			method: 'post',
			body: data,
			headers: {
				'X-DEPICTER-CSRF': window.depicterSliders.token
			}
		})
		.then((response) => response.json())
		.then((data) => {
			if (data.hits) {
				setPublishBtnState(sliderID);
				var $depicterNoticeWrapper = document.querySelector(".depicter-notice-wrapper");
				if ( $depicterNoticeWrapper ) {
					$depicterNoticeWrapper.remove();
				}
				publishBtn.classList.remove('is-busy');
			}
		}).catch((error) => {
			console.error(error);
		});
	}

	function setPublishBtnState(sliderID) {
		window.fetch( window.depicterSliders.ajax_url + '?action=depicter/document/status&ID=' + sliderID, {
			method: 'GET', // or 'PUT'
			headers: {
				'Content-Type': 'text/html',
				'X-DEPICTER-CSRF': window.depicterSliders.token,
			},
		} )
		.then((response) => response.json())
		.then(function (data) {
			let publishBtn = document.getElementById('dep-publish-slider-btn');
			if (typeof data.status != 'undefined' && data.status == 'publish') {
				publishBtn.setAttribute('disabled', true);
				return;
			}
			publishBtn.removeAttribute('disabled');
			return;
		} )
		.catch( function() {
			// console.log( 'error encountered' );
		} );
	}


	// function fetchSlider( sliderID ) {
	// 	window.fetch( window.depicterSliders.ajax_url + '?action=depicter/document/render&ID=' + sliderID, {
	// 		method: 'GET', // or 'PUT'
	// 		headers: {
	// 			'Content-Type': 'text/html',
	// 			'X-DEPICTER-CSRF': window.depicterSliders.token,
	// 		},
	// 	} )
	// 		.then( function( response ) {
	// 			return response.text();
	// 		} )
	// 		.then( function( html ) {
	// 			setAttributes({ content: html });

	// 			setTimeout(function() {
	// 				window.Depicter.initAll();
	// 			}, 0);
	// 		} )
	// 		.catch( function() {
	// 			// console.log( 'error encountered' );
	// 		} );
	// }

	return (
		<>
			<InspectorControls key="setting">
				<Panel header="Depicter">
					<PanelBody title="Depicter Settings" initialOpen={ true }>
						<PanelRow>
							<SelectControl
								id='dep-slider-list'
								label="Slider"
								value={ attributes.id }
								options={ depicterSliders.list }
								onChange={ updateID }
							/>
						</PanelRow>
						<PanelRow className='sliderBtns'>
							<Button variant='primary' id='dep-publish-slider-btn' onClick={publishSlider}>{ depicterSliders.publish_text }</Button>
							<Button variant='secondary' onClick={editSlider}>{ depicterSliders.edit_text }</Button>
						</PanelRow>
					</PanelBody>
				</Panel>
			</InspectorControls>
			<div { ...blockProps }>
				<IframeResizer
					src={`${window.depicterSliders.ajax_url}?action=depicter/document/preview&depicter-csrf=${window.depicterSliders.token}&ID=${attributes.id}&status=draft|publish&gutenberg=true`}
					style={{ width: '1px', minWidth: '100%'}}
				/>
			</div>
		</>
	);
}