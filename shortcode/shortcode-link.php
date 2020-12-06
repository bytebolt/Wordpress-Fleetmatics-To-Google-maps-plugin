<?php
/**
 * Link shortcode
 *
 * Write [link] in your post editor to render this shortcode.
 *
 * @package	 ABS
 * @since    1.0.0
 */

if ( ! function_exists( 'aa_link_shortcode' ) ) {
	// Add the action.
	add_action( 'plugins_loaded', function() {
		// Add the shortcode.
		add_shortcode( 'Fmap', 'aa_link_shortcode' );
	});

	/**
	 * Shortcode Function
	 *
	 * @param  Attributes $atts l|t URL TEXT.
	 * @return string
	 * @since  1.0.0
	 */
	function aa_link_shortcode( $atts ) {
		// Text Default.
		ob_start();
		$text_default = __( 'About Me', 'ABS' );

		

		$_atts = shortcode_atts( array(
		  'u'  => '/',           // URL.
		  't'  => $text_default, // Text.
		), $atts );

		$vehiclesNames = array();
		foreach (json_decode(file_get_contents(plugin_dir_path( __FILE__ )."vehicles.json"), true) as $key => $value) {
			$vehiclesNames[$value['RegistrationNumber']] = $value['Name'];
		}

?>

<script type="text/javascript">
var names = <?php echo json_encode($vehiclesNames); ?>;
console.log(names);
</script>

<script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
<script
src="https://maps.googleapis.com/maps/api/js?key=<?php echo($atts['key'])?>&callback=initMap&libraries=&v=weekly"
defer
></script>

<div style="display: block !important;">
	<div id="map"></div>
	<div id="legend"></div>
</div>
<script>

	var timeSince = function(date) {
		if (typeof date !== 'object') {
			date = new Date(date);
		}

		var seconds = Math.floor((new Date() - date) / 1000);
		var intervalType;

		var interval = Math.floor(seconds / 31536000);
		if (interval >= 1) {
			intervalType = 'year';
		} else {
			interval = Math.floor(seconds / 2592000);
			if (interval >= 1) {
				intervalType = 'month';
			} else {
				interval = Math.floor(seconds / 86400);
				if (interval >= 1) {
					intervalType = 'day';
				} else {
					interval = Math.floor(seconds / 3600);
					if (interval >= 1) {
						intervalType = "hour";
					} else {
						interval = Math.floor(seconds / 60);
						if (interval >= 1) {
							intervalType = "minute";
						} else {
							interval = seconds;
							intervalType = "second";
						}
					}
				}
			}
		}

		if (interval > 1 || interval === 0) {
			intervalType += 's';
		}

		return interval + ' ' + intervalType;
	};

	function reloadMap()
	{
		jQuery.ajax
		({
			type: "POST",
			url: '<?php echo admin_url('admin-ajax.php'); ?>',
			data: {action: "refreshMap"},
			success: function(data) 
			{
				var infoWindow = null;
				var info_Window = null;
				data=JSON.parse(data);
				trucks_lon_lat=[];
				var bounds = new google.maps.LatLngBounds();
				const legend = document.getElementById("legend");

				const moving = "PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiB2aWV3Qm94PSIwIDAgMjggMjgiIGZpdD0iIiBwcmVzZXJ2ZUFzcGVjdFJhdGlvPSJ4TWlkWU1pZCBtZWV0IiBmb2N1c2FibGU9ImZhbHNlIj48ZGVmcz48cmVjdCBpZD0iaWNfdmVoaWNsZV9zdG9wLWIiIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgcng9IjEyIi8+PGZpbHRlciBpZD0iaWNfdmVoaWNsZV9zdG9wLWEiIHdpZHRoPSIxMjkuMiUiIGhlaWdodD0iMTI5LjIlIiB4PSItMTQuNiUiIHk9Ii0xMC40JSIgZmlsdGVyVW5pdHM9Im9iamVjdEJvdW5kaW5nQm94Ij48ZmVPZmZzZXQgZHk9IjEiIGluPSJTb3VyY2VBbHBoYSIgcmVzdWx0PSJzaGFkb3dPZmZzZXRPdXRlcjEiLz48ZmVHYXVzc2lhbkJsdXIgaW49InNoYWRvd09mZnNldE91dGVyMSIgcmVzdWx0PSJzaGFkb3dCbHVyT3V0ZXIxIiBzdGREZXZpYXRpb249IjEiLz48ZmVDb21wb3NpdGUgaW49InNoYWRvd0JsdXJPdXRlcjEiIGluMj0iU291cmNlQWxwaGEiIG9wZXJhdG9yPSJvdXQiIHJlc3VsdD0ic2hhZG93Qmx1ck91dGVyMSIvPjxmZUNvbG9yTWF0cml4IGluPSJzaGFkb3dCbHVyT3V0ZXIxIiB2YWx1ZXM9IjAgMCAwIDAgMCAgIDAgMCAwIDAgMCAgIDAgMCAwIDAgMCAgMCAwIDAgMC4yNCAwIi8+PC9maWx0ZXI+PC9kZWZzPjxnIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMiAxKSI+PHVzZSBmaWxsPSIjMDAwIiBmaWx0ZXI9InVybCgjaWNfdmVoaWNsZV9zdG9wLWEpIiB4bGluazpocmVmPSIjaWNfdmVoaWNsZV9zdG9wLWIiLz48cmVjdCB3aWR0aD0iMjMiIGhlaWdodD0iMjMiIHg9Ii41IiB5PSIuNSIgZmlsbD0iIzAwQUEwMCIgc3Ryb2tlPSIjRkZGIiBzdHJva2UtbGluZWpvaW49InNxdWFyZSIgcng9IjExLjUiLz48L2c+PC9zdmc+";
				const stopped = "PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiB2aWV3Qm94PSIwIDAgMjggMjgiIGZpdD0iIiBwcmVzZXJ2ZUFzcGVjdFJhdGlvPSJ4TWlkWU1pZCBtZWV0IiBmb2N1c2FibGU9ImZhbHNlIj4KICA8ZGVmcz4KICAgIDxyZWN0IGlkPSJpY192ZWhpY2xlX3N0b3AtYiIgd2lkdGg9IjI0IiBoZWlnaHQ9IjI0IiByeD0iMTIiLz4KICAgIDxmaWx0ZXIgaWQ9ImljX3ZlaGljbGVfc3RvcC1hIiB3aWR0aD0iMTI5LjIlIiBoZWlnaHQ9IjEyOS4yJSIgeD0iLTE0LjYlIiB5PSItMTAuNCUiIGZpbHRlclVuaXRzPSJvYmplY3RCb3VuZGluZ0JveCI+CiAgICAgIDxmZU9mZnNldCBkeT0iMSIgaW49IlNvdXJjZUFscGhhIiByZXN1bHQ9InNoYWRvd09mZnNldE91dGVyMSIvPgogICAgICA8ZmVHYXVzc2lhbkJsdXIgaW49InNoYWRvd09mZnNldE91dGVyMSIgcmVzdWx0PSJzaGFkb3dCbHVyT3V0ZXIxIiBzdGREZXZpYXRpb249IjEiLz4KICAgICAgPGZlQ29tcG9zaXRlIGluPSJzaGFkb3dCbHVyT3V0ZXIxIiBpbjI9IlNvdXJjZUFscGhhIiBvcGVyYXRvcj0ib3V0IiByZXN1bHQ9InNoYWRvd0JsdXJPdXRlcjEiLz4KICAgICAgPGZlQ29sb3JNYXRyaXggaW49InNoYWRvd0JsdXJPdXRlcjEiIHZhbHVlcz0iMCAwIDAgMCAwICAgMCAwIDAgMCAwICAgMCAwIDAgMCAwICAwIDAgMCAwLjI0IDAiLz4KICAgIDwvZmlsdGVyPgogIDwvZGVmcz4KICA8ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDIgMSkiPgogICAgPHVzZSBmaWxsPSIjMDAwIiBmaWx0ZXI9InVybCgjaWNfdmVoaWNsZV9zdG9wLWEpIiB4bGluazpocmVmPSIjaWNfdmVoaWNsZV9zdG9wLWIiLz4KICAgIDxyZWN0IHdpZHRoPSIyMyIgaGVpZ2h0PSIyMyIgeD0iLjUiIHk9Ii41IiBmaWxsPSIjRTQyRTIwIiBzdHJva2U9IiNGRkYiIHN0cm9rZS1saW5lam9pbj0ic3F1YXJlIiByeD0iMTEuNSIvPgogICAgPHJlY3Qgd2lkdGg9IjgiIGhlaWdodD0iOCIgeD0iOCIgeT0iOCIgZmlsbD0iI0ZGRiIvPgogIDwvZz4KPC9zdmc+";
				document.getElementById('legend').innerHTML="<b><span id='timerText'>Updated Just Now</span></b><br><br>";	

				for (i in data) 
				{
					if(data[i]['Latitude'])
					{
						// markers
						bounds.extend(new google.maps.LatLng(data[i]['Latitude'] ,data[i]['Longitude']));
						marker = new google.maps.Marker({
							position: new google.maps.LatLng(data[i]['Latitude'] ,data[i]['Longitude']),
							map: map,
							icon: { url: 'data:image/svg+xml;charset=UTF-8;base64,' + (data[i]['DisplayState']=='Moving'?moving:stopped), scaledSize: new google.maps.Size(30, 30) },

						});

						//legend
						const div = document.createElement("div");
						div.innerHTML = "<b>" + names[i] + "</b> - " + (data[i]['DisplayState']=="Moving"?"Moving":"Stopped") + "<br>" + data[i]['Address']['AddressLine1'] + ", " + (data[i]['Address']['AddressLine2']?(data[i]['Address']['AddressLine2'] + ", "):"") + data[i]['Address']['Locality'] + ", "+data[i]['Address']['AdministrativeArea'] +"<br><br>" ;
						legend.appendChild(div);

						//info window
						google.maps.event.addListener(marker, 'click', (function(marker,i){
							return function(){
								map.setCenter(marker.getPosition());
								map.setZoom(15);
								if (info_Window) {
									info_Window.close();
								}
								info_Window = new google.maps.InfoWindow({
									content: "<b>" + names[i] + " - " + i + "</b> - " + (data[i]['DisplayState']=="Moving"?"Moving":"Stopped") + "<br>" + data[i]['Address']['AddressLine1'] + ", " + (data[i]['Address']['AddressLine2']?(data[i]['Address']['AddressLine2'] + ", "):"") + data[i]['Address']['Locality'] +", "+data[i]['Address']['AdministrativeArea'] +"<br><br>",
								});
								info_Window.open(map, marker);
							}
						})(marker,i));

						google.maps.event.addListener(marker, 'mouseover', (function(marker,i){
							return function(){
								if (infoWindow) {
									infoWindow.close();
								}
								infoWindow = new google.maps.InfoWindow({
									content: "<b>" + names[i] + " - " + i + "</b> - " + (data[i]['DisplayState']=="Moving"?"Moving":"Stopped") + "<br>" + data[i]['Address']['AddressLine1'] + ", " + (data[i]['Address']['AddressLine2']?(data[i]['Address']['AddressLine2'] + ", "):"") + data[i]['Address']['Locality'] +", "+data[i]['Address']['AdministrativeArea'] +"<br><br>",
								});
								infoWindow.open(map, marker);
							}
						})(marker,i));

						google.maps.event.addListener(marker, 'mouseout', (function(marker,i){
							return function(){
								if (infoWindow) {
									infoWindow.close();
								}
							}
						})(marker,i));
					}
				}
				map.fitBounds(bounds);

				setInterval(function() {
					timer = timer + 1;
				    document.getElementById('timerText').innerHTML=timeSince(new Date-(timer*60*1000))+" ago";
				}, 60 * 1000);
				
				// map.controls[google.maps.ControlPosition.LEFT].push(legend);
				// console.log(data);
			}
		});

	}


	let map;
	var timer=0;
	function initMap() {
		jQuery.ajax
		({
			type: "POST",
			url: '<?php echo admin_url('admin-ajax.php'); ?>',
			data: {action: "lastSeen"},
			success: function(data) 
			{
				map = new google.maps.Map(document.getElementById("map"));
				var infoWindow = null;
				var info_Window = null;
				data=JSON.parse(data);
				trucks_lon_lat=[];
				var bounds = new google.maps.LatLngBounds();
				const legend = document.getElementById("legend");

				const moving = "PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiB2aWV3Qm94PSIwIDAgMjggMjgiIGZpdD0iIiBwcmVzZXJ2ZUFzcGVjdFJhdGlvPSJ4TWlkWU1pZCBtZWV0IiBmb2N1c2FibGU9ImZhbHNlIj48ZGVmcz48cmVjdCBpZD0iaWNfdmVoaWNsZV9zdG9wLWIiIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgcng9IjEyIi8+PGZpbHRlciBpZD0iaWNfdmVoaWNsZV9zdG9wLWEiIHdpZHRoPSIxMjkuMiUiIGhlaWdodD0iMTI5LjIlIiB4PSItMTQuNiUiIHk9Ii0xMC40JSIgZmlsdGVyVW5pdHM9Im9iamVjdEJvdW5kaW5nQm94Ij48ZmVPZmZzZXQgZHk9IjEiIGluPSJTb3VyY2VBbHBoYSIgcmVzdWx0PSJzaGFkb3dPZmZzZXRPdXRlcjEiLz48ZmVHYXVzc2lhbkJsdXIgaW49InNoYWRvd09mZnNldE91dGVyMSIgcmVzdWx0PSJzaGFkb3dCbHVyT3V0ZXIxIiBzdGREZXZpYXRpb249IjEiLz48ZmVDb21wb3NpdGUgaW49InNoYWRvd0JsdXJPdXRlcjEiIGluMj0iU291cmNlQWxwaGEiIG9wZXJhdG9yPSJvdXQiIHJlc3VsdD0ic2hhZG93Qmx1ck91dGVyMSIvPjxmZUNvbG9yTWF0cml4IGluPSJzaGFkb3dCbHVyT3V0ZXIxIiB2YWx1ZXM9IjAgMCAwIDAgMCAgIDAgMCAwIDAgMCAgIDAgMCAwIDAgMCAgMCAwIDAgMC4yNCAwIi8+PC9maWx0ZXI+PC9kZWZzPjxnIGZpbGw9Im5vbmUiIGZpbGwtcnVsZT0iZXZlbm9kZCIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMiAxKSI+PHVzZSBmaWxsPSIjMDAwIiBmaWx0ZXI9InVybCgjaWNfdmVoaWNsZV9zdG9wLWEpIiB4bGluazpocmVmPSIjaWNfdmVoaWNsZV9zdG9wLWIiLz48cmVjdCB3aWR0aD0iMjMiIGhlaWdodD0iMjMiIHg9Ii41IiB5PSIuNSIgZmlsbD0iIzAwQUEwMCIgc3Ryb2tlPSIjRkZGIiBzdHJva2UtbGluZWpvaW49InNxdWFyZSIgcng9IjExLjUiLz48L2c+PC9zdmc+";
				const stopped = "PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiB2aWV3Qm94PSIwIDAgMjggMjgiIGZpdD0iIiBwcmVzZXJ2ZUFzcGVjdFJhdGlvPSJ4TWlkWU1pZCBtZWV0IiBmb2N1c2FibGU9ImZhbHNlIj4KICA8ZGVmcz4KICAgIDxyZWN0IGlkPSJpY192ZWhpY2xlX3N0b3AtYiIgd2lkdGg9IjI0IiBoZWlnaHQ9IjI0IiByeD0iMTIiLz4KICAgIDxmaWx0ZXIgaWQ9ImljX3ZlaGljbGVfc3RvcC1hIiB3aWR0aD0iMTI5LjIlIiBoZWlnaHQ9IjEyOS4yJSIgeD0iLTE0LjYlIiB5PSItMTAuNCUiIGZpbHRlclVuaXRzPSJvYmplY3RCb3VuZGluZ0JveCI+CiAgICAgIDxmZU9mZnNldCBkeT0iMSIgaW49IlNvdXJjZUFscGhhIiByZXN1bHQ9InNoYWRvd09mZnNldE91dGVyMSIvPgogICAgICA8ZmVHYXVzc2lhbkJsdXIgaW49InNoYWRvd09mZnNldE91dGVyMSIgcmVzdWx0PSJzaGFkb3dCbHVyT3V0ZXIxIiBzdGREZXZpYXRpb249IjEiLz4KICAgICAgPGZlQ29tcG9zaXRlIGluPSJzaGFkb3dCbHVyT3V0ZXIxIiBpbjI9IlNvdXJjZUFscGhhIiBvcGVyYXRvcj0ib3V0IiByZXN1bHQ9InNoYWRvd0JsdXJPdXRlcjEiLz4KICAgICAgPGZlQ29sb3JNYXRyaXggaW49InNoYWRvd0JsdXJPdXRlcjEiIHZhbHVlcz0iMCAwIDAgMCAwICAgMCAwIDAgMCAwICAgMCAwIDAgMCAwICAwIDAgMCAwLjI0IDAiLz4KICAgIDwvZmlsdGVyPgogIDwvZGVmcz4KICA8ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDIgMSkiPgogICAgPHVzZSBmaWxsPSIjMDAwIiBmaWx0ZXI9InVybCgjaWNfdmVoaWNsZV9zdG9wLWEpIiB4bGluazpocmVmPSIjaWNfdmVoaWNsZV9zdG9wLWIiLz4KICAgIDxyZWN0IHdpZHRoPSIyMyIgaGVpZ2h0PSIyMyIgeD0iLjUiIHk9Ii41IiBmaWxsPSIjRTQyRTIwIiBzdHJva2U9IiNGRkYiIHN0cm9rZS1saW5lam9pbj0ic3F1YXJlIiByeD0iMTEuNSIvPgogICAgPHJlY3Qgd2lkdGg9IjgiIGhlaWdodD0iOCIgeD0iOCIgeT0iOCIgZmlsbD0iI0ZGRiIvPgogIDwvZz4KPC9zdmc+";
				document.getElementById('legend').innerHTML+="<b>"+(timeSince((data['lastSeen'])))+" ago</b>&nbsp;&nbsp;Refreshing&nbsp;<span class='loading'></span><br><br>";	

				for (i in data) 
				{
					if(data[i]['Latitude'])
					{
						// markers
						bounds.extend(new google.maps.LatLng(data[i]['Latitude'] ,data[i]['Longitude']));
						marker = new google.maps.Marker({
							position: new google.maps.LatLng(data[i]['Latitude'] ,data[i]['Longitude']),
							map: map,
							icon: { url: 'data:image/svg+xml;charset=UTF-8;base64,' + (data[i]['DisplayState']=='Moving'?moving:stopped), scaledSize: new google.maps.Size(30, 30) },

						});

						//legend
						const div = document.createElement("div");
						div.innerHTML = "<b>" + names[i] + "</b> - " + (data[i]['DisplayState']=="Moving"?"Moving":"Stopped") + "<br>" + data[i]['Address']['AddressLine1'] + ", " + (data[i]['Address']['AddressLine2']?(data[i]['Address']['AddressLine2'] + ", "):"") + data[i]['Address']['Locality'] + ", "+data[i]['Address']['AdministrativeArea'] +"<br><br>" ;
						legend.appendChild(div);

						//info window
						google.maps.event.addListener(marker, 'click', (function(marker,i){
							return function(){
								map.setCenter(marker.getPosition());
								map.setZoom(15);
								if (info_Window) {
									info_Window.close();
								}
								info_Window = new google.maps.InfoWindow({
									content: "<b>" + names[i] + " - " + i + "</b> - " + (data[i]['DisplayState']=="Moving"?"Moving":"Stopped") + "<br>" + data[i]['Address']['AddressLine1'] + ", " + (data[i]['Address']['AddressLine2']?(data[i]['Address']['AddressLine2'] + ", "):"") + data[i]['Address']['Locality'] +", "+data[i]['Address']['AdministrativeArea'] +"<br><br>",
								});
								info_Window.open(map, marker);
							}
						})(marker,i));

						google.maps.event.addListener(marker, 'mouseover', (function(marker,i){
							return function(){
								if (infoWindow) {
									infoWindow.close();
								}
								infoWindow = new google.maps.InfoWindow({
									content: "<b>" + names[i] + " - " + i + "</b> - " + (data[i]['DisplayState']=="Moving"?"Moving":"Stopped") + "<br>" + data[i]['Address']['AddressLine1'] + ", " + (data[i]['Address']['AddressLine2']?(data[i]['Address']['AddressLine2'] + ", "):"") + data[i]['Address']['Locality'] +", "+data[i]['Address']['AdministrativeArea'] +"<br><br>",
								});
								infoWindow.open(map, marker);
							}
						})(marker,i));

						google.maps.event.addListener(marker, 'mouseout', (function(marker,i){
							return function(){
								if (infoWindow) {
									infoWindow.close();
								}
							}
						})(marker,i));
					}
				}
				map.fitBounds(bounds);
				map.controls[google.maps.ControlPosition.LEFT].push(legend);
			}

		});

		reloadMap();

	}

</script>
<style type="text/css">
	#map
	{
/*		position: absolute !important;  */
		height: <?php echo($atts['h'])?> !important;
		width: <?php echo($atts['w'])?> !important;
		overflow: auto;
	}
	#legend {
		font-family: Arial, sans-serif;
		background: #fff;
		padding: 10px;
		margin: 10px;
		border: 3px solid #000;
	}

	#legend h3 {
		margin-top: 0;
	}

	#legend img {
		vertical-align: middle;
	}
	.loading{
	  display:inline-block;
	  position:relative;
	  vertical-align:middle;
	  width:  10px;
	  height: 10px;
	  border: 2px solid transparent;
	  border-top-color:#000000;
	  border-bottom-color:#000000;
	  border-radius:50%;
	  animation: rotate 3s linear infinite;
	}
	.loading:after,
	.loading:before{
	  position:absolute;
	  width:0; height:0;
	  border:4px solid transparent;
	  border-bottom-color:#000000;
	}
	.loading:after{
	  top:1px;
	  right:-7px;
	  transform: rotate(135deg);
	}
	.loading:before{
	  top:3px;
	  left:-5px;
	  transform: rotate(-45deg);
	}
	@keyframes rotate{
	   to { transform: rotate(360deg); }
	}
</style>


<?php


return ob_get_clean();
}
}
