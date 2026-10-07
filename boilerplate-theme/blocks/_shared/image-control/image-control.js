import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';
import { BaseControl, Button, Flex } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import './image-control-style.js';

/**
 * Returns a small preview URL for a media entity, falling back to the original file.
 *
 * @param {Object} media Media entity from the core data store.
 * @returns {string} Preview image URL.
 */
function getMediaPreviewUrl(media) {
	const sizes = media?.media_details?.sizes || {};

	return sizes.medium?.source_url || sizes.large?.source_url || media?.source_url || '';
}

/**
 * Image picker with preview, replace and remove actions.
 *
 * @param {Object} props Component props.
 * @param {string} props.label Control label. Pass an already translated string.
 * @param {number} props.imageId Selected attachment ID, or 0.
 * @param {Object|undefined} props.media Media entity for the selected attachment.
 * @param {Function} props.onChange Called with the new attachment ID, or 0 to remove.
 * @returns {JSX.Element} Image control.
 */
export function ImageControl({ label, imageId, media, onChange }) {
	return (
		<BaseControl __nextHasNoMarginBottom label={label}>
			<MediaUploadCheck>
				<MediaUpload
					allowedTypes={['image']}
					value={imageId}
					onSelect={(selected) => onChange(selected?.id || 0)}
					render={({ open }) => (
						<>
							{getMediaPreviewUrl(media) && (
								<Button
									className="image-control__preview"
									onClick={open}
									label={__('Bild ersetzen', 'boilerplate-theme')}
								>
									<img src={getMediaPreviewUrl(media)} alt="" />
								</Button>
							)}
							<Flex justify="flex-start">
								<Button variant="secondary" onClick={open}>
									{imageId
										? __('Bild ersetzen', 'boilerplate-theme')
										: __('Bild auswählen', 'boilerplate-theme')}
								</Button>
								{imageId > 0 && (
									<Button
										variant="tertiary"
										isDestructive
										onClick={() => onChange(0)}
									>
										{__('Entfernen', 'boilerplate-theme')}
									</Button>
								)}
							</Flex>
						</>
					)}
				/>
			</MediaUploadCheck>
		</BaseControl>
	);
}
