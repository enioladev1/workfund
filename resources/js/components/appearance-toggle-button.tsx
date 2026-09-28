import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { CheckIcon, MonitorIcon, MoonIcon, SunIcon } from '@/components/ui/icons';
import { useAppearance } from '@/hooks/use-appearance';
import type { Appearance } from '@/hooks/use-appearance';

const OPTIONS: { value: Appearance; label: string; icon: typeof SunIcon }[] = [
    { value: 'light', label: 'Light', icon: SunIcon },
    { value: 'dark', label: 'Dark', icon: MoonIcon },
    { value: 'system', label: 'System', icon: MonitorIcon },
];

export function AppearanceToggleButton() {
    const { appearance, resolvedAppearance, updateAppearance } = useAppearance();

    const CurrentIcon = resolvedAppearance === 'dark' ? MoonIcon : SunIcon;

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button variant="ghost" size="icon" aria-label="Toggle appearance">
                    <CurrentIcon className="size-4" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                {OPTIONS.map(({ value, label, icon: Icon }) => (
                    <DropdownMenuItem key={value} onClick={() => updateAppearance(value)}>
                        <Icon className="size-4" />
                        {label}
                        {appearance === value && <CheckIcon className="ml-auto size-4" />}
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
