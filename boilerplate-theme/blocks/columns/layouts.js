/**
 * Fixed column width layouts. Keep in sync with ColumnsBlock::LAYOUTS
 * (theme/src/Blocks/Blocks/ColumnsBlock.php) and the `columns--layout-*`
 * modifiers in tailwind/custom/components/columns.css.
 *
 * The layout name lists the column widths in percent. The first layout per
 * column count is the default with equal widths and needs no modifier class.
 */
const LAYOUT_NAMES = {
	1: ['100'],
	2: ['50-50', '30-70', '70-30'],
	3: ['33-33-33', '25-50-25'],
	4: ['25-25-25-25'],
};

/**
 * Icon width and height in SVG units.
 */
const ICON_WIDTH = 48;
const ICON_HEIGHT = 32;
const ICON_INSET = 2;
const ICON_LINE = 1.5;

/**
 * Returns an SVG path for a rectangle.
 *
 * @param {number} left Left edge.
 * @param {number} top Top edge.
 * @param {number} right Right edge.
 * @param {number} bottom Bottom edge.
 * @returns {string} Path data.
 */
function rectPath(left, top, right, bottom) {
	return `M${left} ${top}H${right}V${bottom}H${left}Z`;
}

/**
 * Renders a layout preview icon: a frame with one divider between each column.
 *
 * Drawn as one filled path with the columns cut out (`evenodd`), because the
 * editor button styles force `fill: currentColor` on icons, which would fill
 * a stroked outline.
 *
 * @param {Object} props Component props.
 * @param {number[]} props.widths Column widths in percent.
 * @returns {JSX.Element} Icon element.
 */
function LayoutIcon({ widths }) {
	const left = ICON_INSET;
	const right = ICON_WIDTH - ICON_INSET;
	const top = ICON_INSET;
	const bottom = ICON_HEIGHT - ICON_INSET;
	const total = widths.reduce((sum, width) => sum + width, 0);
	let start = left;

	const columns = widths.map((width, index) => {
		const end = start + ((right - left) * width) / total;
		const isFirst = 0 === index;
		const isLast = widths.length - 1 === index;
		const path = rectPath(
			start + (isFirst ? ICON_LINE : ICON_LINE / 2),
			top + ICON_LINE,
			end - (isLast ? ICON_LINE : ICON_LINE / 2),
			bottom - ICON_LINE,
		);

		start = end;

		return path;
	});

	return (
		<svg
			xmlns="http://www.w3.org/2000/svg"
			viewBox={`0 0 ${ICON_WIDTH} ${ICON_HEIGHT}`}
			width={ICON_WIDTH}
			height={ICON_HEIGHT}
			fill="currentColor"
			aria-hidden="true"
			focusable="false"
		>
			<path
				fillRule="evenodd"
				d={[rectPath(left, top, right, bottom), ...columns].join('')}
			/>
		</svg>
	);
}

/**
 * Builds the layout descriptor for a layout name.
 *
 * @param {number} columns Column count.
 * @param {string} name Layout name, e.g. `30-70`.
 * @param {boolean} isEqual Whether all columns have the same width.
 * @returns {{columns: number, name: string, label: string, isEqual: boolean, icon: JSX.Element}} Layout.
 */
function createLayout(columns, name, isEqual) {
	const widths = name.split('-').map((width) => parseInt(width, 10));

	return {
		columns,
		name,
		label: widths.join(' / '),
		isEqual,
		icon: <LayoutIcon widths={widths} />,
	};
}

/**
 * Layouts per column count.
 *
 * @type {Object<number, Array<{columns: number, name: string, label: string, isEqual: boolean, icon: JSX.Element}>>}
 */
export const LAYOUTS = Object.fromEntries(
	Object.entries(LAYOUT_NAMES).map(([columns, names]) => [
		columns,
		names.map((name, index) => createLayout(Number(columns), name, 0 === index)),
	]),
);

/**
 * All layouts in picker order (by column count, equal widths first).
 */
export const ALL_LAYOUTS = Object.values(LAYOUTS).flat();

/**
 * Layout used when the picker is skipped.
 */
export const DEFAULT_LAYOUT = LAYOUTS[2][0];

/**
 * Returns the layout for a column count, falling back to equal widths.
 * Mirrors ColumnsBlock::resolve_layout().
 *
 * @param {number} columns Column count (1-4).
 * @param {string} name Stored layout name.
 * @returns {{columns: number, name: string, label: string, isEqual: boolean, icon: JSX.Element}} Layout.
 */
export function resolveLayout(columns, name) {
	const layouts = LAYOUTS[columns] ?? LAYOUTS[2];

	return layouts.find((layout) => layout.name === name) ?? layouts[0];
}
