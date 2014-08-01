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
        
        // set up focus
        $focus = new DateTime( $date );
        // adjust focus by $offset
        $focus->add(DateInterval::createFromDateString($offset));
        
        
        switch ( $view ) {
            case 'month':
                $output = DefectiveCalendarExtension::generate_month( $focus );
                break;

            default:
                $output = DefectiveCalendarExtension::generate_month( $focus );
                break;
        }
        
        // parse wikitext, and return
        return $parser->recursiveTagParse( $output, $frame );
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
        
        
        // the HTML to return
        $output = '';
        
        // begin table
        $output .= '<table class="dcalendar">';
        
        // month name header row
        $output .= '<tr><td colspan="7">' . $focusMonthName . " [[$focusYear (year)|$focusYear]]" . '</td></tr>';
        
        // day of week name row
        $output .= '<tr><td>S</td><td>M</td><td>T</td><td>W</td><td>T</td><td>F</td><td>S</td></tr>';
        
        // start weeks
        $output .= '<tr>';

        // fill in previous month's days to pad the calendar
        $previousDaysCount = $firstOfMonth->format('N') % 7;
        // find first Sunday in calendar
        $currentDate = new DateTime($firstOfMonth->format('Y-m-d'));
        $currentDate->sub(new DateInterval('P' . $previousDaysCount . 'D'));
        for ($i = 0; $i < $previousDaysCount; $i++) {
            $output .= '<td>[[' . $currentDate->format(self::$linkFormat) . '|' . $currentDate->format(self::$displayFormat) . ']]</td>';
            $currentDate->add($intervalOneDay);
        }
        
        // fill in the focused month
        $currentDate->setDate($firstOfMonth->format('Y'), $firstOfMonth->format('m'), $firstOfMonth->format('d'));
        for ($i = 0; $i < $daysInMonth; $i++) {
            $output .= '<td>[[' . $currentDate->format(self::$linkFormat) . '|' . $currentDate->format(self::$displayFormat) . ']]</td>';
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
            $output .= '<td>[[' . $currentDate->format(self::$linkFormat) . '|' . $currentDate->format(self::$displayFormat) . ']]</td>';
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