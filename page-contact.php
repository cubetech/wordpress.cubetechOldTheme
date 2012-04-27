<?php
/*
Template Name: Contact
*/
get_header();
	the_post(); ?>
	<?php cc_breadcrumbs(); ?>
	<!-- End Breadcrumbs -->
	<!--  Sidebar -->
	<?php get_sidebar(); ?>
	<!-- End Sidebar -->
	<!-- Content -->
	<div id="content">
		<div class="contact">
			<?php the_content(); ?>
		</div>
		<div class="cl">&nbsp;</div>
	</div>
	<!-- End Content -->
	<div class="cl">&nbsp;</div>
	<div class="map">
			<?php 
			$googlemap = get_meta('_googlemap');
			$googleaddress = get_meta('_googleaddress');
			$image = get_meta('_googlemap_icon');
			if($googlemap && $googleaddress && $image) {
				?>
				<script src="http://maps.google.com/maps/api/js?sensor=true" type="text/javascript"></script>
				<script type="text/javascript">
					jQuery(document).ready(function($) {
						//------- Google Maps ---------//
							  
						// Creating a LatLng object containing the coordinate for the center of the map
						var latlng = new google.maps.LatLng(<?php echo $googlemap; ?>);
						  
						// Creating an object literal containing the properties we want to pass to the map  
						var options = {  
							zoom: 15, // This number can be set to define the initial zoom level of the map
							center: latlng,
							mapTypeId: google.maps.MapTypeId.ROADMAP // This value can be set to define the map type ROADMAP/SATELLITE/HYBRID/TERRAIN
						};  
						// Calling the constructor, thereby initializing the map  
						var map = new google.maps.Map(document.getElementById('map_div'), options);  

						// Define Marker properties
						var image = new google.maps.MarkerImage('<?php echo ecf_get_image_url($image); ?>',
							// This marker is 129 pixels wide by 42 pixels tall.
							new google.maps.Size(129, 42),
							// The origin for this image is 0,0.
							new google.maps.Point(0,0),
							// The anchor for this image is the base of the flagpole at 18,42.
							new google.maps.Point(100, 60)
						);

						// Add Marker
						var marker1 = new google.maps.Marker({
							position: new google.maps.LatLng(<?php echo $googlemap; ?>), 
							map: map,		
							icon: image // This path is the custom pin to be shown. Remove this line and the proceeding comma to use default pin
						});	
					});
				</script>
			<div id="map_div" style="width: 928px; height: 181px;" class="section"></div>
				<?php 
			}
			?>

	</div>
<?php get_footer(); ?>