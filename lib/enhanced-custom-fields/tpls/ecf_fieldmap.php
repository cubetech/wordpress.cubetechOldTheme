<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>

<?php if(!empty($this->value)) : ?>
	<input type="hidden" name="<?php echo $this->name?>" value="<?php echo $this->value; ?>" id="<?php echo $this->name?>" />
<?php else: ?>
	<input type="hidden" name="<?php echo $this->name?>" value="" id="<?php echo $this->name?>" />
<?php endif ?>
<div class="cl">&nbsp;</div>
<div id="map_<?php echo $this->name?>" style="width: 500px; height: 300px; border: solid 2px #dfdfdf; overflow: hidden;"></div>
<script type="text/javascript" charset="utf-8">
	<?php if(!empty($this->value)) : ?>
        v = '<?php echo $this->value; ?>'.split(',');
        var latlng = new google.maps.LatLng(v[0],v[1]);
        var zoom = <?php echo $this->zoom; ?>;
        if (v.length > 2) {
            zoom = v[2];
        }

	<?php else: ?>
        var zoom = <?php echo $this->zoom; ?>;
        var latlng = new google.maps.LatLng(<?php echo $this->lat?>, <?php echo $this->long?>);
	<?php endif; ?>	
    var myOptions = {
        zoom: parseInt(zoom),
        center: latlng,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        disableDoubleClickZoom: true,
        panControl: true,
        zoomControl: true,
        mapTypeControl: true,
        scaleControl: true,
        streetViewControl: false,
        overviewMapControl: true
    };
    var map_<?php echo $this->name?> = new google.maps.Map(document.getElementById("map_<?php echo $this->name?>"), myOptions);

    <?php if(!empty($this->value)) : ?>
        var marker = new google.maps.Marker({
            position: latlng,
            map: map_<?php echo $this->name?>,
            draggable: true
        });
        google.maps.event.addListener(marker, 'dragend', change_coords);     
    <?php else: ?>
        var marker = null;
    <?php endif; ?>  

	function change_coords(point) {
        latLng = marker.getPosition();
        if (point) {
            latLng = point;
        }
        document.getElementById("<?php echo $this->name?>").value = latLng.lat() + "," + latLng.lng() + "," + map_<?php echo $this->name?>.getZoom();
	}
	function set_coords(point) {
        if (marker != null) {
            marker.setMap(null);
        }
        if (point) {
            if (point.latLng) {
                latLng = point.latLng;
            } else {
                latLng = point;
            }
            
            marker = new google.maps.Marker({
                position: latLng,
                map: map_<?php echo $this->name?>,
                draggable: true
            });
            google.maps.event.addListener(marker, 'dragend', change_coords);
            change_coords(latLng);
        }
        return false;
	}
    function get_map() {
        return map_<?php echo $this->name?>;
    }
    google.maps.event.addListener(map_<?php echo $this->name?>, "dblclick", set_coords);
    google.maps.event.addListener(map_<?php echo $this->name?>, "zoom_changed", change_coords);
</script>
