import { Button, Dropdown, MenuGroup, MenuItemsChoice } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import { DesktopIcon, MobileIcon, TabletIcon } from './icons.js';
import { useEditorDevice } from './use-editor-device.js';

/**
 * Device choices shown in the switcher menu.
 *
 * @returns {Array<{ value: string, label: string }>} Menu choices.
 */
function getDeviceChoices() {
    return [
        {
            value: 'Desktop',
            label: __('Desktop', 'boilerplate-theme'),
        },
        {
            value: 'Tablet',
            label: __('Tablet', 'boilerplate-theme'),
        },
        {
            value: 'Mobile',
            label: __('Mobile', 'boilerplate-theme'),
        },
    ];
}

/**
 * Icon for the active editor device.
 *
 * @param {Object} props Component props.
 * @param {'Desktop' | 'Tablet' | 'Mobile'} props.deviceType Active device type.
 * @returns {JSX.Element} Device icon.
 */
function DeviceIcon({ deviceType }) {
    if (deviceType === 'Tablet') {
        return <TabletIcon />;
    }

    if (deviceType === 'Mobile') {
        return <MobileIcon />;
    }

    return <DesktopIcon />;
}

/**
 * Switches the global editor preview between desktop, tablet, and mobile.
 *
 * Does not store block attributes. Blocks that need the active viewport read
 * `deviceKey` from `useEditorDevice`.
 *
 * @returns {JSX.Element} Device menu button.
 */
export function EditorDeviceSwitcher() {
    const { deviceType, setDeviceType } = useEditorDevice();
    const choices = getDeviceChoices();
    const current = choices.find((choice) => choice.value === deviceType) ?? choices[0];

    return (
        <Dropdown
            popoverProps={{ placement: 'bottom-start' }}
            renderToggle={({ isOpen, onToggle }) => (
                <Button
                    className="boilerplate-device-switcher"
                    icon={<DeviceIcon deviceType={deviceType} />}
                    label={current.label}
                    showTooltip
                    size="small"
                    aria-expanded={isOpen}
                    onClick={onToggle}
                />
            )}
            renderContent={({ onClose }) => (
                <MenuGroup>
                    <MenuItemsChoice
                        choices={choices}
                        value={deviceType}
                        onSelect={(nextDeviceType) => {
                            setDeviceType(nextDeviceType);
                            onClose();
                        }}
                    />
                </MenuGroup>
            )}
        />
    );
}
