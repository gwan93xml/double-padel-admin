import React from 'react';

type CurrencyType = 'IDR' | 'SGD' | 'USD';

interface CurrencyFormatterProps {
    amount: number;
    currency?: CurrencyType;
    prefix?: string;
}

const CurrencyFormatter: React.FC<CurrencyFormatterProps> = ({ amount, currency = 'IDR', prefix }) => {
    const formatCurrency = (value: number, currencyType: CurrencyType): string => {
        let formatter: Intl.NumberFormat;

        switch (currencyType) {
            case 'IDR':
                formatter = new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 2,
                });
                break;
            case 'SGD':
                formatter = new Intl.NumberFormat('en-SG', {
                    style: 'currency',
                    currency: 'SGD',
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
                break;
            case 'USD':
                formatter = new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: 'USD',
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2,
                });
                break;
        }
        return formatter.format(value);
    };

    return <div className='text-right'>{formatCurrency(amount, currency)}</div>;
};

export default CurrencyFormatter;

