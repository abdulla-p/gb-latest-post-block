import { registerBlockType } from '@wordpress/blocks';
import {
	ToggleControl,
	PanelBody,
	TextControl,
	SelectControl,
	RangeControl,
} from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';
import './style.scss';
import { __ } from '@wordpress/i18n';
import ServerSideRender from '@wordpress/server-side-render';

const allAttr = {
	numberColumns: {
		type: 'number',
		default: 3,
	},
	numberPosts: {
		type: 'string',
	},
	categories: {
		type: 'object',
	},
	selectedCategory: {
		type: 'string',
	},
	showExcerpt: {
		type: 'boolean',
		default: true,
	},
	showPostThumbnail: {
		type: 'boolean',
		default: true,
	},
	showFeaturedPosts: {
		type: 'boolean',
	},
};

function latestPosts(props) {
	const numOfColumns = (value) => {
		props.setAttributes({ numberColumns: value });
	};

	const onChangeCategory = (value) => {
		props.setAttributes({ selectedCategory: value });
	};

	const onChangeNumberPosts = (value) => {
		props.setAttributes({ numberPosts: value });
	};

	const onChangeShowExcerpt = (value) => {
		props.setAttributes({ showExcerpt: value });
	};

	const onChangeShowPostThumbnail = (value) => {
		props.setAttributes({ showPostThumbnail: value });
	};

	const onChangeShowFeaturedPosts = (value) => {
		props.setAttributes({ showFeaturedPosts: value });
	};

	const getCategoryNames = () => {
		const options = [];
		if (!props.attributes.categories) {
			wp.apiFetch({
				url: '/wp-json/wp/v2/categories',
			}).then((categories) => {
				props.setAttributes({ categories });
			});
		}
		if (props.attributes.categories) {
			props.attributes.categories.map((category) => {
				return options.push({
					label: category.name,
					value: category.id,
				});
			});
		}

		return options;
	};

	return (
		<div>
			<InspectorControls>
				<PanelBody title={__('Block Settings')} initialOpen={true}>
					<SelectControl
						label={__('Select Category')}
						value={props.attributes.selectedCategory}
						onChange={onChangeCategory}
						options={getCategoryNames()}
					/>
					<TextControl
						label={__('Number of posts to show')}
						onChange={onChangeNumberPosts}
						value={props.attributes.numberPosts}
					/>
					<ToggleControl
						label={__('Show Excerpt ?')}
						onChange={onChangeShowExcerpt}
						checked={props.attributes.showExcerpt}
					/>
					<ToggleControl
						label={__('Show Featured Images ?')}
						onChange={onChangeShowPostThumbnail}
						checked={props.attributes.showPostThumbnail}
					/>
					<ToggleControl
						label={__('Show Featured Posts Only ?')}
						onChange={onChangeShowFeaturedPosts}
						checked={props.attributes.showFeaturedPosts}
					/>
				</PanelBody>
				<PanelBody title={__('Block Style')}>
					<RangeControl
						label={__('Columns')}
						min={1}
						max={6}
						onChange={numOfColumns}
						value={props.attributes.numberColumns}
					/>
				</PanelBody>
			</InspectorControls>
			<ServerSideRender
				block="my-first-dynamic-gutenberg-block/latest-post"
				attributes={{
					selectedCategory: props.attributes.selectedCategory,
					categories: props.attributes.categories,
					numberPosts: props.attributes.numberPosts,
					numberColumns: props.attributes.numberColumns,
					showExcerpt: props.attributes.showExcerpt,
					showPostThumbnail: props.attributes.showPostThumbnail,
					showFeaturedPosts: props.attributes.showFeaturedPosts,
				}}
				httpMethod="POST"
			/>
		</div>
	);
}

registerBlockType('my-first-dynamic-gutenberg-block/latest-post', {
	title: 'Latest Post Custom Block',
	icon: 'megaphone',
	category: 'text',
	attributes: allAttr,
	supports: {
		html: false,
	},
	edit: latestPosts,
	save() {
		return null;
	},
});
