import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Editor device types accepted by `core/editor` `setDeviceType`.
 *
 * @type {readonly ['Desktop', 'Tablet', 'Mobile']}
 */
export const EDITOR_DEVICE_TYPES = ['Desktop', 'Tablet', 'Mobile'];

/**
 * Attribute keys stored on blocks, keyed by the editor device type.
 *
 * @type {Record<'Desktop' | 'Tablet' | 'Mobile', 'desktop' | 'tablet' | 'mobile'>}
 */
const DEVICE_KEYS = {
    Desktop: 'desktop',
    Tablet: 'tablet',
    Mobile: 'mobile',
};

/**
 * Reads and sets the global editor preview device.
 *
 * The value is shared by every block and by the toolbar view menu. Setting it
 * resizes the editor canvas.
 *
 * @returns {{
 *   deviceType: 'Desktop' | 'Tablet' | 'Mobile',
 *   deviceKey: 'desktop' | 'tablet' | 'mobile',
 *   setDeviceType: (deviceType: 'Desktop' | 'Tablet' | 'Mobile') => void
 * }} Current device and a setter.
 */
export function useEditorDevice() {
    const deviceType = useSelect((select) => {
        const current = select('core/editor').getDeviceType?.();

        return DEVICE_KEYS[current] ? current : 'Desktop';
    }, []);
    const { setDeviceType } = useDispatch('core/editor');

    return {
        deviceType,
        deviceKey: DEVICE_KEYS[deviceType],
        setDeviceType: (nextDeviceType) => {
            if (!DEVICE_KEYS[nextDeviceType] || typeof setDeviceType !== 'function') {
                return;
            }

            setDeviceType(nextDeviceType);
        },
    };
}
