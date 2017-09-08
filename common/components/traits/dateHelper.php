<?php
namespace common\components\traits;

use DateTime;

trait dateHelper
{
    protected function strToDate($date, $format)
    {
        $d = DateTime::createFromFormat($format, $date);

        if ($d && $d->format($format) === $date) {
            return $d;
        }

        return null;
    }

    public function strToTsAM($date, $format = 'd-m-Y')
    {
        $d = $this->strToDate($date, $format);

        if ($d) {
            $d->setTime(0, 0);
            return $d->getTimestamp();
        }

        return null;
    }

    public function strToTsPM($date, $format = 'd-m-Y')
    {
        $d = $this->strToDate($date, $format);

        if ($d) {
            $d->setTime(23, 59, 59);
            return $d->getTimestamp();
        }

        return null;
    }
}