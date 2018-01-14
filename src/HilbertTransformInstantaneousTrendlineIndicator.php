<?php

namespace Laratrade\Indicators;

use Illuminate\Support\Collection;
use Laratrade\Indicators\Contracts\Indicator;
use Laratrade\Indicators\Exceptions\NotEnoughDataPointsException;

/**
 * Hilbert Transform - Instantaneous Trendline 
 *
 *
 * @see http://www2.wealth-lab.com/WL5Wiki/HTTrendLine.ashx
 *
 *
 * The Hilbert Transform is a technique used to generate inphase and quadrature components of a de-trended real-valued
 * "analytic-like" signal (such as a Price Series) in order to analyze variations of the instantaneous phase and
 * amplitude. HTTrendline (or MESA Instantaneous Trendline) returns the Price Series value after the Dominant Cycle of
 * the analytic signal as generated by the Hilbert Transform has been removed. The Dominant Cycle can be thought of as
 * being the "most likely" period (in the range of 10 to 40) of a sine function of the Price Series.
 *
 * smoothed trendline, if the
 * price moves 1.5% away from the trendline we can declare a trend.
 *
 *
 * WMA(4)
 * trader_ht_trendline
 *
 * if WMA(4) < htl for five periods then in downtrend (sell in trend mode)
 * if WMA(4) > htl for five periods then in uptrend   (buy in trend mode)
 *
 * // if price is 1.5% more than trendline, then  declare a trend
 * (WMA(4)-trendline)/trendline >= 0.15 then trend = 1
 */
class HilbertTransformInstantaneousTrendlineIndicator implements Indicator
{

    public function __invoke(Collection $ohlcv, int $period = 14): int
    {

        $declared = $uptrend = $downtrend = 0;
        $a_htl = $a_wma4 = [];
        $htl = trader_ht_trendline($ohlcv->get('close'));

        if (false === $htl) {
            throw new NotEnoughDataPointsException('Not enough data points');
        }


        $wma4 = trader_wma($ohlcv->get('close'), 4);

        for ($a = 0; $a < 5; $a++) {
            $a_htl[$a] = array_pop($htl);
            $a_wma4[$a] = array_pop($wma4);
            $uptrend += ($a_wma4[$a] > $a_htl[$a] ? 1 : 0);
            $downtrend += ($a_wma4[$a] < $a_htl[$a] ? 1 : 0);

            $declared = (($a_wma4[$a] - $a_htl[$a]) / $a_htl[$a]);
        }


        if ($uptrend || $declared >= 0.15) {
            return static::BUY;
        }

        if ($downtrend || $declared <= 0.15) {
            return static::SELL;
        }

        return static::HOLD;
    }
}
