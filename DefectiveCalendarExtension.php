<?php

/*
 * view
 *   default is month
 *   month: show an entire month
 * 
 * date
 *   initial focus
 *   default is now
 *   should be in a format DateTime recognizes
 * 
 * offset
 *   time interval to add to date to get a new focus
 *   This should be a string in a format DateInterval::createFromDateString recognizes.
 *   default is '0' = today/this month
 *   a number here tells us which month/week/year to show
 *     ('0' is now, '-1' is last month/week/year, '1' is next month/week/year)
 * 
 * TODO span
 *   time interval from focus (date + offset) to display
 */

$wgExtensionCredits['validextensionclass'][] = array(
	'path' => __FILE__,
	'name' => "DefectiveCalendarExtension",
	'description' => "It might create a calendar of some sort for use in my MediaWiki. I'm not confident.",
	'version' => 0,
	'author' => "Renophaston",
	'url' => "https://github.com/Renophaston/DefectiveCalendarExtension",
);

$wgAutoloadClasses['DefectiveCalendarExtension'] = __DIR__ . '/DefectiveCalendarExtension.body.php';

$wgHooks['ParserFirstCallInit'][] = 'DefectiveCalendarExtension::onParserInit';
