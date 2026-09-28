import { HugeiconsIcon } from '@hugeicons/react';
import type { HugeiconsIconProps } from '@hugeicons/react';
import HugeAlertCircleIcon from '@hugeicons/core-free-icons/AlertCircleIcon';
import HugeAlertDiamondIcon from '@hugeicons/core-free-icons/AlertDiamondIcon';
import HugeArrowUpDownIcon from '@hugeicons/core-free-icons/ArrowUpDownIcon';
import HugeBookOpenIcon from '@hugeicons/core-free-icons/BookOpen01Icon';
import HugeCalendarIcon from '@hugeicons/core-free-icons/Calendar01Icon';
import HugeCancelIcon from '@hugeicons/core-free-icons/Cancel01Icon';
import HugeChartColumnIcon from '@hugeicons/core-free-icons/ChartColumnIcon';
import HugeCheckmarkCircleIcon from '@hugeicons/core-free-icons/CheckmarkCircle02Icon';
import HugeChevronDownIcon from '@hugeicons/core-free-icons/ChevronDownIcon';
import HugeChevronLeftIcon from '@hugeicons/core-free-icons/ChevronLeftIcon';
import HugeChevronRightIcon from '@hugeicons/core-free-icons/ChevronRightIcon';
import HugeChevronUpIcon from '@hugeicons/core-free-icons/ChevronUpIcon';
import HugeCircleIcon from '@hugeicons/core-free-icons/CircleIcon';
import HugeClockIcon from '@hugeicons/core-free-icons/Clock01Icon';
import HugeCopyIcon from '@hugeicons/core-free-icons/Copy01Icon';
import HugeEyeIcon from '@hugeicons/core-free-icons/EyeIcon';
import HugeFilterIcon from '@hugeicons/core-free-icons/FilterIcon';
import HugeFolderIcon from '@hugeicons/core-free-icons/Folder01Icon';
import HugeGitBranchIcon from '@hugeicons/core-free-icons/GitBranchIcon';
import HugeGridViewIcon from '@hugeicons/core-free-icons/GridViewIcon';
import HugeInboxIcon from '@hugeicons/core-free-icons/InboxIcon';
import HugeLoadingIcon from '@hugeicons/core-free-icons/Loading03Icon';
import HugeLogoutIcon from '@hugeicons/core-free-icons/LogoutIcon';
import HugeMailIcon from '@hugeicons/core-free-icons/Mail01Icon';
import HugeMenuIcon from '@hugeicons/core-free-icons/Menu01Icon';
import HugeMoneyIcon from '@hugeicons/core-free-icons/Money01Icon';
import HugeMonitorIcon from '@hugeicons/core-free-icons/MonitorIcon';
import HugeMoonIcon from '@hugeicons/core-free-icons/MoonIcon';
import HugeMoreHorizontalIcon from '@hugeicons/core-free-icons/MoreHorizontalIcon';
import HugePackageIcon from '@hugeicons/core-free-icons/Package01Icon';
import HugePlusSignIcon from '@hugeicons/core-free-icons/PlusSignIcon';
import HugeSearchIcon from '@hugeicons/core-free-icons/Search01Icon';
import HugeSettingsIcon from '@hugeicons/core-free-icons/Settings01Icon';
import HugeShieldIcon from '@hugeicons/core-free-icons/Shield01Icon';
import HugeSidebarLeftIcon from '@hugeicons/core-free-icons/SidebarLeftIcon';
import HugeSparklesIcon from '@hugeicons/core-free-icons/SparklesIcon';
import HugeSunIcon from '@hugeicons/core-free-icons/Sun03Icon';
import HugeTickIcon from '@hugeicons/core-free-icons/Tick02Icon';
import HugeUserIcon from '@hugeicons/core-free-icons/UserIcon';
import HugeUserMultipleIcon from '@hugeicons/core-free-icons/UserMultipleIcon';
import HugeViewOffIcon from '@hugeicons/core-free-icons/ViewOffIcon';

/**
 * Every icon in the app renders through this module so only HugeIcons
 * (free) is used anywhere, never mixed with another icon library. Each icon
 * is imported from its own subpath (not the package's barrel export) so the
 * bundler only includes the handful of icons actually used, not the whole
 * ~15,000-icon set.
 */
type IconComponentProps = Omit<HugeiconsIconProps, 'icon'>;

export type IconComponent = (props: IconComponentProps) => React.JSX.Element;

function makeIcon(icon: HugeiconsIconProps['icon']): IconComponent {
    return function BoundIcon(props: IconComponentProps) {
        return <HugeiconsIcon icon={icon} {...props} />;
    };
}

export const CheckIcon = makeIcon(HugeTickIcon);
export const XIcon = makeIcon(HugeCancelIcon);
export const ChevronRightIcon = makeIcon(HugeChevronRightIcon);
export const ChevronLeftIcon = makeIcon(HugeChevronLeftIcon);
export const ChevronDownIcon = makeIcon(HugeChevronDownIcon);
export const ChevronUpIcon = makeIcon(HugeChevronUpIcon);
export const CircleIcon = makeIcon(HugeCircleIcon);
export const PanelLeftCloseIcon = makeIcon(HugeSidebarLeftIcon);
export const PanelLeftOpenIcon = makeIcon(HugeSidebarLeftIcon);
export const SpinnerIcon = makeIcon(HugeLoadingIcon);
export const MoreHorizontalIcon = makeIcon(HugeMoreHorizontalIcon);
export const MenuIcon = makeIcon(HugeMenuIcon);
export const SearchIcon = makeIcon(HugeSearchIcon);
export const LayoutGridIcon = makeIcon(HugeGridViewIcon);
export const InboxIcon = makeIcon(HugeInboxIcon);
export const BookOpenIcon = makeIcon(HugeBookOpenIcon);
export const FolderIcon = makeIcon(HugeFolderIcon);
export const GitBranchIcon = makeIcon(HugeGitBranchIcon);
export const ArrowUpDownIcon = makeIcon(HugeArrowUpDownIcon);
export const LogOutIcon = makeIcon(HugeLogoutIcon);
export const SettingsIcon = makeIcon(HugeSettingsIcon);
export const AlertCircleIcon = makeIcon(HugeAlertCircleIcon);
export const AlertDiamondIcon = makeIcon(HugeAlertDiamondIcon);
export const EyeIcon = makeIcon(HugeEyeIcon);
export const EyeOffIcon = makeIcon(HugeViewOffIcon);
export const SunIcon = makeIcon(HugeSunIcon);
export const MoonIcon = makeIcon(HugeMoonIcon);
export const MonitorIcon = makeIcon(HugeMonitorIcon);
export const MoneyIcon = makeIcon(HugeMoneyIcon);
export const PackageIcon = makeIcon(HugePackageIcon);
export const ClockIcon = makeIcon(HugeClockIcon);
export const CalendarIcon = makeIcon(HugeCalendarIcon);
export const ShieldIcon = makeIcon(HugeShieldIcon);
export const UserIcon = makeIcon(HugeUserIcon);
export const FilterIcon = makeIcon(HugeFilterIcon);
export const SparklesIcon = makeIcon(HugeSparklesIcon);
export const MailIcon = makeIcon(HugeMailIcon);
export const ChartColumnIcon = makeIcon(HugeChartColumnIcon);
export const CheckmarkCircleIcon = makeIcon(HugeCheckmarkCircleIcon);
export const CopyIcon = makeIcon(HugeCopyIcon);
export const PlusIcon = makeIcon(HugePlusSignIcon);
export const UserMultipleIcon = makeIcon(HugeUserMultipleIcon);

export type { HugeiconsIconProps };
