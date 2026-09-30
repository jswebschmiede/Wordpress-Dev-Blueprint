/**
 * Inline editor icons.
 *
 * WordPress 7 does not register a `wp-icons` script, so these paths are copied
 * from the Gutenberg icon set instead of importing `@wordpress/icons`.
 */

/**
 * Renders a 24px icon from a single SVG path.
 *
 * @param {Object} props Component props.
 * @param {string} props.path SVG path data.
 * @returns {JSX.Element} Icon element.
 */
function Icon({ path }) {
    return (
        <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            width="24"
            height="24"
            fill="currentColor"
            aria-hidden="true"
            focusable="false"
        >
            <path d={path} />
        </svg>
    );
}

/**
 * Desktop device icon.
 *
 * @returns {JSX.Element} Icon element.
 */
export function DesktopIcon() {
    return (
        <Icon path="M20.5 16h-.7V8c0-1.1-.9-2-2-2H6.2c-1.1 0-2 .9-2 2v8h-.7c-.8 0-1.5.7-1.5 1.5h20c0-.8-.7-1.5-1.5-1.5zM5.7 8c0-.3.2-.5.5-.5h11.6c.3 0 .5.2.5.5v7.6H5.7V8z" />
    );
}

/**
 * Tablet device icon.
 *
 * @returns {JSX.Element} Icon element.
 */
export function TabletIcon() {
    return (
        <Icon path="M17 4H7c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm.5 14c0 .3-.2.5-.5.5H7c-.3 0-.5-.2-.5-.5V6c0-.3.2-.5.5-.5h10c.3 0 .5.2.5.5v12zm-7.5-.5h4V16h-4v1.5z" />
    );
}

/**
 * Mobile device icon.
 *
 * @returns {JSX.Element} Icon element.
 */
export function MobileIcon() {
    return (
        <Icon path="M15 4H9c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h6c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm.5 14c0 .3-.2.5-.5.5H9c-.3 0-.5-.2-.5-.5V6c0-.3.2-.5.5-.5h6c.3 0 .5.2.5.5v12zm-4.5-.5h2V16h-2v1.5z" />
    );
}

/**
 * Linked-sides icon.
 *
 * @returns {JSX.Element} Icon element.
 */
export function LinkIcon() {
    return (
        <Icon path="M10 17.389H8.444A5.194 5.194 0 1 1 8.444 7H10v1.5H8.444a3.694 3.694 0 0 0 0 7.389H10v1.5ZM14 7h1.556a5.194 5.194 0 0 1 0 10.39H14v-1.5h1.556a3.694 3.694 0 0 0 0-7.39H14V7Zm-4.5 6h5v-1.5h-5V13Z" />
    );
}

/**
 * Unlinked-sides icon.
 *
 * @returns {JSX.Element} Icon element.
 */
export function LinkOffIcon() {
    return (
        <Icon path="M17.031 4.703 15.576 4l-1.56 3H14v.03l-2.324 4.47H9.5V13h1.396l-1.502 2.889h-.95a3.694 3.694 0 0 1 0-7.389H10V7H8.444a5.194 5.194 0 1 0 0 10.389h.17L7.5 19.53l1.416.719L15.049 8.5h.507a3.694 3.694 0 0 1 0 7.39H14v1.5h1.556a5.194 5.194 0 0 0 .273-10.383l1.202-2.304Z" />
    );
}

/**
 * Reset icon.
 *
 * @returns {JSX.Element} Icon element.
 */
export function ResetIcon() {
    return (
        <Icon path="M18.3 11.7c-.6-.6-1.4-.9-2.3-.9H6.7l2.9-3.3-1.1-1-4.5 5L8.5 16l1-1-2.7-2.7H16c.5 0 .9.2 1.3.5 1 1 1 3.4 1 4.5v.3h1.5v-.2c0-1.5 0-4.3-1.5-5.7z" />
    );
}
