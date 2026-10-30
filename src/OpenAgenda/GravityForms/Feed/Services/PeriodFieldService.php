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

    /**
     * @throws Exception
     */
    public function handlePeriod(array $period): array
    {
        $period = $this->normalizeRow($period);

        if ('' === trim($period['start_date'])) {
            return [];
        }

        $startDate = new DateTimeImmutable($period['start_date']);
        $endDate = '' !== trim($period['end_date']) ? new DateTimeImmutable($period['end_date']) : $startDate;
        $days = array_filter($period['days'], fn (array $dayConfig) => $this->dayConfigIsValid($dayConfig));

        // Without selected days the period itself is exported as a single date range.
        if ([] === $days) {
            return [$this->buildPeriodConfig($startDate, $endDate)];
        }

        return $this->handleDays($days, $startDate, $endDate);
    }

    /**
     * @since NEXT
     */
    private function handleDays(array $days, DateTimeImmutable $startDate, DateTimeImmutable $endDate): array
    {
        $allDates = [];

        foreach ($days as $dayKey => $dayConfig) {
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
            'start_date' => (string) ($row[self::PERIOD_START_DATE_FIELD_ID] ?? ''),
            'end_date' => (string) ($row[self::PERIOD_END_DATE_FIELD_ID] ?? ''),
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
                'start_time' => (string) ($row[$startId] ?? ''),
                'end_time' => (string) ($row[$endId] ?? ''),
            ];
        }

        return $out;
    }

    /**
     * A day is valid when checked and both start and end times are valid time strings.
     */
    private function dayConfigIsValid(array $dayConfig): bool
    {
        return ! empty($dayConfig['checked'])
            && $this->timeIsValid($dayConfig['start_time'] ?? '')
            && $this->timeIsValid($dayConfig['end_time'] ?? '');
    }

    /**
     * @since NEXT
     */
    private function timeIsValid(mixed $time): bool
    {
        return is_string($time) && '' !== trim($time) && false !== strtotime($time);
    }

    private function getDatesForWeekdayInPeriod(DateTimeImmutable $startDate, DateTimeImmutable $endDate): DatePeriod
    {
        return new DatePeriod(
            $startDate,
            new DateInterval('P1D'),
            $endDate->modify('+1 day') // Include end date.
        );
    }

    private function buildPeriodConfig(DateTimeImmutable $startDate, DateTimeImmutable $endDate): array
    {
        return [
            'start_date' => $startDate->format('d-m-Y'),
            'end_date' => $endDate->format('d-m-Y'),
        ];
    }

    /**
     * @since NEXT
     */
    private function buildDateConfig(DateTimeImmutable $date, array $dayConfig): array
    {
        return $this->buildPeriodConfig($date, $date) + [
            'start_time' => trim($dayConfig['start_time']),
            'end_time' => trim($dayConfig['end_time']),
        ];
    }
}
