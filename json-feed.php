<?php
// - standalone json feed -
header('Content-Type:application/json');

// - grab wp load, wherever it's hiding -
if(file_exists('../../../../wp-load.php')) :
	include '../../../../wp-load.php';
else:
	include '../../../../../wp-load.php';
endif;

global $wpdb;

// - grab date barrier -
$today6am = strtotime('today 6:00') + ( get_option( 'gmt_offset' ) * 3600 );

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
    ORDER BY metastart.meta_value ASC LIMIT 500
 ";

$events = $wpdb->get_results($querystr, OBJECT);
$jsonevents = array();

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
$gmts = get_gmt_from_date($gmts); // this function requires Y-m-d H:i:s
$gmts = strtotime($gmts);

// - grab gmt for end -
$gmte = date('Y-m-d H:i:s', $ed);
$gmte = get_gmt_from_date($gmte); // this function requires Y-m-d H:i:s
$gmte = strtotime($gmte);

// - set to ISO 8601 date format -
$stime = date('c', $gmts);
$etime = date('c', $gmte);

// - json items -
$jsonevents[]= array(
    'title' => $post->post_title,
    'allDay' => false, // <- true by default with FullCalendar
    'start' => $stime,
    'end' => $etime,
    'url' => get_permalink($post->ID)
    );

endforeach;
else :
endif;

// - fire away -
echo json_encode($jsonevents);
?>