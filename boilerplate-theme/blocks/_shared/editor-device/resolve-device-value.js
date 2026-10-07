/**
 * Lookup order per device: the own value first, then larger devices only.
 * A value set on desktop applies to every device without its own value; a
 * value set on tablet or mobile never applies upward.
 *
 * Keep in sync with Spacing::resolve_device_value() (theme/src/Blocks/Spacing.php).
 *
 * @type {Record<'mobile'|'tablet'|'desktop', Array<'mobile'|'tablet'|'desktop'>>}
 */
const LOOKUP_ORDER = {
	desktop: ['desktop'],
	tablet: ['tablet', 'desktop'],
	mobile: ['mobile', 'tablet', 'desktop'],
};

/**
 * Returns the effective value of a device from per-device values.
 *
 * @param {Object} values Values keyed by `mobile`, `tablet` and `desktop`.
 * @param {'mobile'|'tablet'|'desktop'} deviceKey Device to resolve.
 * @param {Function} [isSet] Returns whether a value counts as set. Defaults to not null/undefined.
 * @returns {*} First set value in lookup order, or undefined.
 */
export function resolveDeviceValue(
	values,
	deviceKey,
	isSet = (value) => value !== null && value !== undefined,
) {
	const order = LOOKUP_ORDER[deviceKey] ?? [];
	const match = order.find((device) => isSet(values?.[device]));

	return match ? values[match] : undefined;
}
