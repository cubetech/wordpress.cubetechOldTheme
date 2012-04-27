<script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?sensor=false"></script>

<input type="hidden" name="<?php echo $this->name ?>" value="<?php echo $this->value ?>" id="<?php echo $this->name ?>" />
<div id="map_<?php echo $this->name ?>" style="width: 400px; height: 400px;">
	
</div>
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
        if (point != null) {
            latLng = point.latLng;
        }
        document.getElementById("<?php echo $this->name?>").value = latLng.lat() + "," + latLng.lng() + "," + map_<?php echo $this->name?>.getZoom();
    }
    function set_coords(point) {
        if (marker != null) {
            marker.setMap(null);
        }
        if (point) {
            marker = new google.maps.Marker({
                position: point.latLng,
                map: map_<?php echo $this->name?>,
                draggable: true
            });
            google.maps.event.addListener(marker, 'dragend', change_coords);
            change_coords(point);
        }
        return false;
    }
    google.maps.event.addListener(map_<?php echo $this->name?>, "dblclick", set_coords);
    google.maps.event.addListener(map_<?php echo $this->name?>, "zoom_changed", change_coords);

</script>