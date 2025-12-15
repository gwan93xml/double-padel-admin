
import React from 'react'
import CurrencyInput, { CurrencyInputProps } from 'react-currency-input-field'
import { cn } from "@/lib/utils"

interface MoneyInputProps extends Omit<CurrencyInputProps, 'className'> {
  className?: string
}

const MoneyInput = React.forwardRef<HTMLInputElement, MoneyInputProps>(
  ({ className, ...props }, ref) => {
    return (
      <CurrencyInput
        onFocus={e => {
          e.target.select()
        }}
        ref={ref}
        className={cn(
          "flex w-full py-2 text-end rounded-md border border-input bg-background px-3  text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50",
          className
        )}
        // groupSeparator='.'
        // decimalSeparator=','
        decimalsLimit={2}
        allowDecimals
        // intlConfig={{
        //   locale: 'id-ID',
        //    currency: 'IDR',
           

        // }}
        {...props}
      />
    )
  }
)

MoneyInput.displayName = 'MoneyInput'

export default MoneyInput

