<?php

class DefectiveCalendarExtension
{
    static $linkFormat = "Y-m-d";
    static $displayFormat = "d";

    static function onParserInit( Parser $parser ) {
        // when the parser sees 'dcalendar', sub the output of render
	$parser->setHook( 'dcalendar', 'DefectiveCalendarExtension::render' );
	
	// always return true
	return true;
    }
    
    static function render ( $input, array $args, Parser $parser, PPFrame $frame ) {

        // set up vars
        $view = isset($args['view']) ? $args['view'] : 'month';
        $date = isset($args['date']) ? $args['date'] : 'now';
        $offset = isset($args['offset']) ? $args['offset'] : '0';
        $specialMonthOffset = isset($args['specialmonthoffset']) ? $args['specialmonthoffset'] : 0;
        
        // disable cache, because otherwise "today" won't be today.
        $parser->disableCache();
        
        // set up focus
        $focus = new DateTime( $date );
        // adjust focus by months (special)
        if ($specialMonthOffset != 0) {
            self::specialMonthAdjust( $focus, $specialMonthOffset );
        }
        // adjust focus by $offset
        $focus->add(DateInterval::createFromDateString($offset));
        
        
        switch ( $view ) {
            case 'month':
                $output = self::generate_month( $focus );
                break;

            default:
                $output = self::generate_month( $focus );
                break;
        }
        
        // parse wikitext, and return
        return $parser->recursiveTagParse( $output, $frame );
    }
    
    /*
     * if you add or subtract months in PHP, you can get weirdness, e.g.:
     * 2014-01-31 + 1 month = 2014-03-03
     * 2014-07-31 - 1 month = 2014-07-01
     * So this function does the month math from the 15th, and then reassigns or clips the day
     */
    static function specialMonthAdjust( $focus, $offset ) {
        $day = $focus->format('d');
        // set focus to the middle of the month
        $focus->setDate( $focus->format('Y'), $focus->format('m'), 15 );
        $focus->add(DateInterval::createFromDateString($offset . ' months'));
        // put old day back
        if ($day > $focus->format('t')) {
            $focus->setDate( $focus->format('Y'), $focus->format('m'), $focus->format('t') );
        } else {
            $focus->setDate( $focus->format('Y'), $focus->format('m'), $day );
        }
    }
    
    static function generate_month( $focus ) {
        
        // figure out some stuff
        $focusDay = $focus->format('d');
        $focusMonth = $focus->format('m');
        $focusYear = $focus->format('Y');
        $focusMonthName = $focus->format('F');
        $firstOfMonth = new DateTime();
        $firstOfMonth->setDate($focusYear, $focusMonth, 1);
        $intervalOneDay = new DateInterval('P1D');
        $daysInMonth = $focus->format('t');
        $lastOfMonth = new DateTime();
        $lastOfMonth->setDate($focusYear, $focusMonth, $daysInMonth);
        
        $today = new DateTime();
        $todayString = $today->format('Y-m-d');
        
        
        // the HTML to return
        $output = '';
        
        // begin table
        $output .= '<table class="dcalendar-table">';
        
        // month name header row
        $output .= '<tr class="dcalendar-header-row"><td colspan="7">' . $focusMonthName . " [[$focusYear (year)|$focusYear]]" . '</td></tr>';
        
        // day of week name row
        $output .= '<tr class="dcalendar-dotw-row"><td>S</td><td>M</td><td>T</td><td>W</td><td>T</td><td>F</td><td>S</td></tr>';
        
        // start weeks
        $output .= '<tr>';

        // fill in previous month's days to pad the calendar
        $previousDaysCount = $firstOfMonth->format('N') % 7;
        // find first Sunday in calendar
        $currentDate = new DateTime($firstOfMonth->format('Y-m-d'));
        $currentDate->sub(new DateInterval('P' . $previousDaysCount . 'D'));
        for ($i = 0; $i < $previousDaysCount; $i++) {
            $output .= '<td class="dcalendar-prevmonth-cell">[[' . $currentDate->format(self::$linkFormat) . '|' . $currentDate->format(self::$displayFormat) . ']]</td>';
            $currentDate->add($intervalOneDay);
        }
        
        // fill in the focused month
        $currentDate->setDate($firstOfMonth->format('Y'), $firstOfMonth->format('m'), $firstOfMonth->format('d'));
        for ($i = 0; $i < $daysInMonth; $i++) {
            $output .= '<td class="dcalendar-focusmonth-cell';
            if ($currentDate->format('Y-m-d') == $todayString) {
                $output .= ' dcalendar-today-cell';
            }
            $output .= '">[[' . $currentDate->format(self::$linkFormat) . '|' . $currentDate->format(self::$displayFormat) . ']]</td>';
            if ($currentDate->format('N') == 6) {
                $output .= '</tr><tr>';
            }
            $currentDate->add($intervalOneDay);
        }
        
        // fill in the following month's days
        $currentDate->setDate($focusYear, $focusMonth, $daysInMonth);
        $currentDate->add($intervalOneDay);
        $followingDaysCount = 7 - $currentDate->format('N');
        for ($i = 0; $i < $followingDaysCount; $i++) {
            $output .= '<td class="dcalendar-nextmonth-cell">[[' . $currentDate->format(self::$linkFormat) . '|' . $currentDate->format(self::$displayFormat) . ']]</td>';
            $currentDate->add($intervalOneDay);
        }


        // end weeks
        $output .= "</tr>";
        
        // end table
        $output .= '</table>';
        
        return $output;
        
//        return $focus->format(self::$dateLinkFormat);
    }
}