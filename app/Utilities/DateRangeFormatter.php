<?php

namespace App\Utilities;

use InvalidArgumentException;

class DateRangeFormatter
{
    public static function format(array $input): array
    {
        $dateType = $input['dateType'] ?? null;

        switch ($dateType) {
            case 'date':
                $date = $input['date'] ?? null;
                return self::parseSingleDate($date);

            case 'month':
                $from = $input['monthFrom'] ?? null;
                $to = $input['monthTo'] ?? null;
                return self::parseMonthRange($from, $to);

            case 'year':
                $year = $input['year'] ?? null;
                return self::parseYear($year);

            case 'range':
                $from = $input['dateFrom'] ?? null;
                $to = $input['dateTo'] ?? null;
                return self::parseDateRange($from, $to);

            case 'until':
                $from = "1900-01-01";
                $to = $input['untilDate'] ?? null;
                return self::parseDateRange($from, $to);

            default:
                throw new InvalidArgumentException("Unsupported dateType: {$dateType}");
        }
    }

    private static function parseSingleDate(?string $date): array
    {
        if (!$date) {
            throw new InvalidArgumentException("Missing 'date' input for 'date' type");
        }

        return [
            'start_date' => $date,
            'end_date' => $date,
        ];
    }

    private static function parseMonthRange(?string $from, ?string $to): array
    {
        // If monthFrom is not provided, use current month as default
        if (!$from) {
            $from = date('Y-m'); // Current year-month (e.g., "2025-09")
        }

        // If monthTo is not provided, use monthFrom as default
        if (!$to) {
            $to = $from;
        }

        $fromStart = date('Y-m-01', strtotime($from));
        $toEnd = date('Y-m-t', strtotime($to));

        return [
            'start_date' => $fromStart,
            'end_date' => $toEnd,
        ];
    }

    private static function parseYear(?string $year): array
    {
        if (!$year || !preg_match('/^\d{4}$/', $year)) {
            throw new InvalidArgumentException("Invalid or missing 'year' input");
        }

        return [
            'start_date' => "{$year}-01-01",
            'end_date' => "{$year}-12-31",
        ];
    }

    private static function parseDateRange(?string $from, ?string $to): array
    {
        if (!$from || !$to) {
            throw new InvalidArgumentException("Both 'dateFrom' and 'dateTo' must be provided for 'range' type");
        }

        return [
            'start_date' => $from,
            'end_date' => $to,
        ];
    }
}
