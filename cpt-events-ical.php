<?php
function tf_events_ical() {

// - start collecting output -
ob_start();

// - file header -
header('Content-type: text/calendar');
header('Content-Disposition: attachment; filename="ical.ics"');

// - content header -
?>
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//<?php the_title(); ?>//NONSGML Events //EN
X-WR-CALNAME:<?php the_title(); _e(' - Events'); ?>
X-ORIGINAL-URL:<?php echo the_permalink(); ?>
X-WR-CALDESC:<?php the_title(); _e(' - Events'); ?>
CALSCALE:GREGORIAN

<?php

// - grab date barrier -
$today6am = strtotime('today 6:00') + ( get_option( 'gmt_offset' ) * 3600 );
$limit = get_option('pubforce_rss_limit');

// - query -
global $wpdb;
$querystr = "
    SELECT *
    FROM $wpdb->posts wposts, $wpdb->postmeta metastart, $wpdb->postmeta metaend
    WHERE (wposts.ID = metastart.post_id AND wposts.ID = metaend.post_id)
    AND (metaend.meta_key = 'tf_events_enddate' AND metaend.meta_value > $today6am )
    AND metastart.meta_key = 'tf_events_enddate'
    AND wposts.post_type = 'tf_events'
    AND wposts.post_status = 'publish'
    ORDER BY metastart.meta_value ASC LIMIT $limit
 ";

$events = $wpdb->get_results($querystr, OBJECT);

// - loop -
if ($events):
global $post;
foreach ($events as $post):
setup_postdata($post);

// - custom variables -
$custom = get_post_custom(get_the_ID());
$sd = $custom["tf_events_startdate"][0];
$ed = $custom["tf_events_enddate"][0];

// - grab gmt for start -
$gmts = date('Y-m-d H:i:s', $sd);
$gmts = get_gmt_from_date($gmts); // this function requires Y-m-d H:i:s, hence the back & forth.
$gmts = strtotime($gmts);

// - grab gmt for end -
$gmte = date('Y-m-d H:i:s', $ed);
$gmte = get_gmt_from_date($gmte); // this function requires Y-m-d H:i:s, hence the back & forth.
$gmte = strtotime($gmte);

// - Set to UTC ICAL FORMAT -
$stime = date('Ymd\THis\Z', $gmts);
$etime = date('Ymd\THis\Z', $gmte);

// - item output -
?>
BEGIN:VEVENT
DTSTART:<?php echo $stime; ?>
DTEND:<?php echo $etime; ?>
SUMMARY:<?php echo the_title(); ?>
DESCRIPTION:<?php the_excerpt_rss('', TRUE, '', 50); ?>
END:VEVENT
<?php
endforeach;
else :
endif;
?>
END:VCALENDAR
<?php
// - full output -
$tfeventsical = ob_get_contents();
ob_end_clean();
echo $tfeventsical;
}

function add_tf_events_ical_feed () {
    // - add it to WP RSS feeds -
    add_feed('tf-events-ical', 'tf_events_ical');
}

add_action('init','add_tf_events_ical_feed');
?>