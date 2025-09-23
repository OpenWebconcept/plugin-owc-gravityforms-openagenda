<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\GravityForms\Feed\Services;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use Exception;

class PeriodFieldService
{
    private const PERIOD_START_DATE_FIELD_ID = 1001;
    private const PERIOD_END_DATE_FIELD_ID = 1002;

    private const DAYS = [
        'mon' => 1100,
        'tue' => 1200,
        'wed' => 1300,
        'thu' => 1400,
        'fri' => 1500,
        'sat' => 1600,
        'sun' => 1700,
    ];

    private const DAY_MAP = [
        'mon' => 1,
        'tue' => 2,
        'wed' => 3,
        'thu' => 4,
        'fri' => 5,
        'sat' => 6,
        'sun' => 7,
    ];

    public function handlePeriod(array $period): array
    {
        return $this->handleDays($this->normalizeRow($period));
    }

    /**
     * @throws Exception
     */
    private function handleDays(array $period): array
    {
        $allDates = [];
        $startDate = new DateTimeImmutable($period['start_date']);
        $endDate = new DateTimeImmutable($period['end_date']);

        foreach ($period['days'] as $dayKey => $dayConfig) {
            if (! $this->dayConfigIsValid($dayConfig)) {
                continue; // Remove invalid days
            }

            $targetWeekday = self::DAY_MAP[$dayKey] ?? null;

            if (! is_int($targetWeekday)) {
                continue;
            }

            $datesForWeekday = $this->getDatesForWeekdayInPeriod($startDate, $endDate);

            foreach ($datesForWeekday as $date) {
                if ((int) $date->format('N') !== $targetWeekday) {
                    continue;
                }

                $allDates[] = $this->buildDateConfig($date, $dayConfig);
            }
        }

        return $allDates;
    }

    /**
     * Normalize the row data from Gravity Forms to a more manageable structure.
     * This is needed because of the way GF requires unique field IDs for each input.
     */
    private function normalizeRow(array $row): array
    {
        $out = [
            'start_date' => $row[self::PERIOD_START_DATE_FIELD_ID] ?? '',
            'end_date' => $row[self::PERIOD_END_DATE_FIELD_ID] ?? '',
            'days' => [],
        ];

        foreach (self::DAYS as $dayKey => $baseId) {
            $checkboxId = $baseId + 1;
            $startId = $baseId + 2;
            $endId = $baseId + 3;

            // Retrieve checkbox value: GF stores it as "{$checkboxId}.1"
            $checked = isset($row["{$checkboxId}.1"]) && '1' === $row["{$checkboxId}.1"];

            $out['days'][$dayKey] = [
                'checked' => $checked ? 1 : 0,
                'start_time' => $row[$startId] ?? '',
                'end_time' => $row[$endId] ?? '',
            ];
        }

        return $out;
    }

    private function dayConfigIsValid(array $dayConfig): bool
    {
        return ! empty($dayConfig['checked'])
            && is_string($dayConfig['start_time']) && trim($dayConfig['start_time']) !== ''
            && is_string($dayConfig['end_time']) && trim($dayConfig['end_time']) !== '';
    }

    private function getDatesForWeekdayInPeriod(DateTimeImmutable $startDate, DateTimeImmutable $endDate): DatePeriod
    {
        return new DatePeriod(
            $startDate,
            new DateInterval('P1D'),
            $endDate->modify('+1 day') // Include end date.
        );
    }

    private function buildDateConfig(DateTimeImmutable $date, array $dayConfig): array
    {
        return [
            'start_date' => $date->format('d-m-Y'),
            'end_date' => $date->format('d-m-Y'),
            'start_time' => $dayConfig['start_time'],
            'end_time' => $dayConfig['end_time'],
        ];
    }
}
