<?php
declare(strict_types=1);

namespace MauticPlugin\MauticFocusBundle\EventListener;

use Mautic\LeadBundle\Model\CompanyReportData;
use Mautic\ReportBundle\Event\ReportBuilderEvent;
use Mautic\ReportBundle\Event\ReportDataEvent;
use Mautic\ReportBundle\Event\ReportGeneratorEvent;
use Mautic\ReportBundle\ReportEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ReportSubscriber implements EventSubscriberInterface
{
    public const CONTEXT_FOCUS_STATS = 'focus_stats';
    public const CONTEXT_FOCUS = 'focus';

    private CompanyReportData $companyReportData;

    public function __construct(CompanyReportData $companyReportData)
    {
        $this->companyReportData = $companyReportData;
    }

    /**
     * @return array<string, array<int, int|string>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ReportEvents::REPORT_ON_BUILD => ['onReportBuilder', 0],
            ReportEvents::REPORT_ON_GENERATE => ['onReportGenerate', 0],
            ReportEvents::REPORT_ON_DISPLAY => ['onReportDisplay', 0],
        ];
    }

    /**
     * Add available tables and columns to the report builder lookup.
     */
    public function onReportBuilder(ReportBuilderEvent $event): void
    {
        if (!$event->checkContext([self::CONTEXT_FOCUS, self::CONTEXT_FOCUS_STATS])) {
            return;
        }

        $prefix = 'f.';
        $prefixStats = 'fs.';
        $columns = [
            $prefix . 'name' => [
                'label' => 'mautic.core.name',
                'type' => 'html',
            ],
            $prefix . 'description' => [
                'label' => 'mautic.core.description',
                'type' => 'html',
            ],
            $prefix . 'focus_type' => [
                'label' => 'mautic.focus.thead.type',
                'type' => 'html',
            ],
            $prefix . 'style' => [
                'label' => 'mautic.focus.tab.focus_style',
                'type' => 'html',
            ],
            $prefixStats . 'type' => [
                'label' => 'mautic.focus.interaction',
                'type' => 'html',
            ],
        ];

        $event->addTable(
            self::CONTEXT_FOCUS,
            [
                'display_name' => 'mautic.focus',
                'columns' => $columns,
            ]
        );

        if ($event->checkContext(self::CONTEXT_FOCUS_STATS)) {
            $columns = array_merge(
                $columns,
                $event->getLeadColumns()
            );

            $data = [
                'display_name' => 'mautic.focus.graph.stats',
                'columns' => $columns,
            ];
            $context = self::CONTEXT_FOCUS_STATS;

            // Register table
            $event->addTable($context, $data, self::CONTEXT_FOCUS);
        }
    }

    /**
     * Initialize the QueryBuilder object to generate reports from.
     */
    public function onReportGenerate(ReportGeneratorEvent $event): void
    {
        if ($event->checkContext([self::CONTEXT_FOCUS_STATS])) {
            $queryBuilder = $event->getQueryBuilder();
            $queryBuilder->from(MAUTIC_TABLE_PREFIX . 'focus_stats', 'fs')
                ->leftJoin('fs', MAUTIC_TABLE_PREFIX . 'leads', 'l', 'l.id = fs.lead_id')
                ->leftJoin('fs', MAUTIC_TABLE_PREFIX . 'focus', 'f', 'f.id = fs.focus_id');

            if ($this->companyReportData->eventHasCompanyColumns($event)) {
                $event->addCompanyLeftJoin($queryBuilder);
            }

            $event->setQueryBuilder($queryBuilder);
        } elseif ($event->checkContext([self::CONTEXT_FOCUS])) {
            $queryBuilder = $event->getQueryBuilder();
            $queryBuilder->from(MAUTIC_TABLE_PREFIX . 'focus', 'f')
                ->leftJoin('f', MAUTIC_TABLE_PREFIX . 'focus_stats', 'fs', 'f.id = fs.focus_id');

            if ($this->companyReportData->eventHasCompanyColumns($event)) {
                $event->addCompanyLeftJoin($queryBuilder);
            }

            $event->setQueryBuilder($queryBuilder);
        }
    }

    public function onReportDisplay(ReportDataEvent $event): void
    {
        $data = $event->getData();
        if ($event->checkContext([self::CONTEXT_FOCUS_STATS]) || $event->checkContext([self::CONTEXT_FOCUS])) {
            if (isset($data[0]['channel']) && isset($data[0]['channel_id'])) {
                foreach ($data as &$row) {
                    $href = 'bla';
                    if (isset($row['channel'])) {
                        $row['channel'] = sprintf('<a href="%s">%s</a>', $href, $row['channel']);
                    }
                    unset($row);
                }
            }
        }

        $event->setData($data);
        unset($data);
    }
}