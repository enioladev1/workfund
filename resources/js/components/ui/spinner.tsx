import type { IconComponent } from "@/components/ui/icons"
import { SpinnerIcon } from "@/components/ui/icons"

import { cn } from "@/lib/utils"

function Spinner({
  className,
  ...props
}: Parameters<IconComponent>[0]) {
  return (
    <SpinnerIcon
      role="status"
      aria-label="Loading"
      className={cn("size-4 animate-spin", className)}
      {...props}
    />
  )
}

export { Spinner }
