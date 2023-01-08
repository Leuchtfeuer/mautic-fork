<?php

namespace Mautic\LeadBundle\Segment\Decorator\Date;

use Mautic\CoreBundle\Helper\CoreParametersHelper;
use Mautic\CoreBundle\Helper\DateTimeHelper;

class TimezoneResolver
{
    /**
     * @var CoreParametersHelper
     */
    private $coreParametersHelper;

    public function __construct(
        CoreParametersHelper $coreParametersHelper
    ) {
        $this->coreParametersHelper = $coreParametersHelper;
    }

    /**
     * @param bool $hasTimePart
     *
     * @return DateTimeHelper
     */
    public function getDefaultDate($hasTimePart)
    {
        /**
         * $hasTimePart tells us if field in a database is date or datetime
         * All datetime fields are stored in UTC
         * Date field, however, is always stored in a default timezone from configuration (there is no time information, so it cannot be converted to UTC).
         *
         * We will generate default date according to this. We need midnight as a default date (for relative intervals like "today" or "-1 day" and now for datetime
         *  1) in UTC for datetime fields
         *  2) in the default timezone from configuration for date fields
         *
         * Later we use toUtcString() method - it gives us midnight in UTC for first condition and midnight in local timezone for second option.
         */
        $time     = $hasTimePart ? 'now' : 'midnight today';

        $date = new \DateTime($time, new \DateTimeZone($this->getDefaultTimezone()));

        return new DateTimeHelper($date, null, $this->getDefaultTimezone());
    }

    public function getDefaultTimezone(): string
    {
        return $this->coreParametersHelper->get('default_timezone', 'UTC');
    }
}
