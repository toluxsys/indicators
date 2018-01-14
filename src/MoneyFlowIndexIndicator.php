<?php

namespace Laratrade\Indicators;

use Illuminate\Support\Collection;
use Laratrade\Indicators\Contracts\Indicator;
use Laratrade\Indicators\Exceptions\NotEnoughDataException;
use Throwable;

/**
 * Money Flow Index
 *
 * @see https://www.investopedia.com/terms/m/mfi.asp
 *
 * What is the 'Money Flow Index - MFI'
 *
 * The money flow index (MFI) is a momentum indicator that measures the inflow and
 * outflow of money into a security over a specific period of time. The MFI uses a
 * stock's price and volume to measure trading pressure. Because the MFI adds trading
 * volume to the relative strength index (RSI), it's sometimes referred to as volume-weighted RSI.
 *
 */
class MoneyFlowIndexIndicator implements Indicator
{
    /**
     * Invoke the indicator.
     *
     * @param Collection $ohlcv
     * @param int        $timePeriod
     *
     * @return int
     *
     * @throws Throwable
     */
    public function __invoke(Collection $ohlcv, int $timePeriod = 14): int
    {
        $mfi = trader_mfi(
            $ohlcv->get('high'),
            $ohlcv->get('low'),
            $ohlcv->get('close'),
            $ohlcv->get('volume'),
            $timePeriod
        );

        throw_unless($mfi, NotEnoughDataException::class);

        $mfiValue = array_pop($mfi);

        if ($mfiValue < -10) {
            return static::BUY;
        } elseif ($mfiValue > 80) {
            return static::SELL;
        }

        return static::HOLD;
    }
}
