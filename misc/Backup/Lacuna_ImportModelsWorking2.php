<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Lacuna - 3D Web GIS using HTML5</title>
		<meta charset="utf-8">
		
		<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
		<script src="http://threejs.org/build/three.min.js"></script>
		<script src="http://threejs.org/examples/js/controls/TrackballControls.js"></script>
		<script src="http://threejs.org/examples/js/libs/stats.min.js"></script>
		<script src="jquery-1.11.0.min.js"></script> <!-- jQuery must be defined first! -->
		<script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
		<script src="perfect-scrollbar-0.4.10.with-mousewheel.min.js"></script>
		<script src='spectrum.js'></script>

		<link rel="stylesheet" href="perfect-scrollbar-0.4.10.min.css">
		<link rel='stylesheet' href='spectrum.css' />
		<link rel="stylesheet" href="Lacuna.css">

		
		<script> 
			$(document).ready(function() {
					$("div").bind("contextmenu",function(e){ return false; }); <!-- DISABLE CONTEXT MENU -->
					
				}); 
		</script>
		<?php include 'dbconnect.php'; ?>
		<?php include 'centroid.php'; ?>
		<?php include 'getdata.php'; ?>
	</head>

	<body>
			<script> 
				
			
				$(document).ready(function(){
				$('#topbar').tooltip({ position: { my: "center bottom", at: "right+10 top+5" }, hide: 100, show: 500 });
					helperswitch = true;
					buttons = ['#helper', '#canvas', '#select', '#measure', '#objectedit']
					$.each(buttons, function(buttonindex, button) {  
						$(button).click(function(){
							// ON BUTTON CLICK FOR EACH BUTTON SLIDE IT UP
							$.each(buttons, function(optionindex, option){ 
								 if (button != option) { $(option + 'options').slideUp("faster"); } 
								 else  if (button == option) { 
								 $(button + 'options').slideToggle("slow")
								 ;}
							});
						});	
					});
					$("#helpercolour").spectrum({
						color: "#FF0000",
						containerClassName: 'colourpicker',
						change: function(color) {
							$("#helpercolour").css("background-color", color.toHexString())
							
							console.log(color.toHexString())
						}
					});
					$("#canvascolour").spectrum({
						color: "#1F2829",
						containerClassName: 'colourpicker',
						change: function(color) {
							$("#canvascolour").css("background-color", color.toHexString())
							renderer.setClearColor( color.toHexString(), 1 )
							console.log(color.toHexString())
						}
					});
					$('#helpertoggle').click( function() {
						if (helperswitch == true) {
							helperswitch = false;
							$('#helpertoggle').text("OFF");
							}
						else {
							helperswitch = true;
							$('#helpertoggle').text("ON");
							}
						});
				 });
			</script>
		<div id="topbar"> 
			<div id="logoholder"> 
				<img id="logo" src="imgs/LacunaLogo.png"></img>
			</div>
			<div id="buttons">
				<div id="helper" title="Helper" ><img src="imgs/helper.png"></img></div> <div id="helperoptions"> 
					<div id="helpertoggle"> ON </div> 
					<div id="helpercolour"> </div>
				</div>
				<div id="canvas" title="Canvas Colour" ><img src="imgs/canvas.png"></img></div> <div id="canvasoptions"> 
					<div id="canvascolour"> </div> 
				</div>
				<div id="select" title="Select" ><img src="imgs/select.png" ></img>  </div><div id="selectoptions"></div>
				<div id="measure" title="Measure"><img src="imgs/measure.png" ></img> </div><div id="measureoptions"></div>
				<div id="objectedit" title="Object Edit"><img src="imgs/objectedit.png" ></img> </div><div id="objecteditoptions"></div>
				
			</div>
		</div>

		<div id="main">
				<div id="info">
					<div id="layerscontainer">
						<div id="layers"> 
							<p class="titles">Layers</p>
							<?php 
									$result = pg_query($db, "SELECT table_name FROM information_schema.tables  WHERE table_schema = 'public'");
									if (!$result) {
									  echo "An error occurred.\n";
									  exit;
									}
									else {
										while ($layer = pg_fetch_row($result)) {
											echo "<br>";
											echo "<input type='checkbox' id='$layer[0]' style='vertical-align: middle; float:left; width:45px' />";
											echo "$layer[0]";
											echo "<br>";
										}
									}
							?>
							
						</div>
					</div>
					<div id="attributes"> 
						<p class="titles"> Attributes </p>
					
					</div>
				</div>
					<script> 
						$('#layers').perfectScrollbar({suppressScrollX: true, scrollYMarginOffset: 3});
						$('#attributes').perfectScrollbar({scrollXMarginOffset: 10});
					</script>
				<div id="container">
				</div>
		</div>
			<div id="bottombar"> 
				Loading : <progress id="progressbar" value="50" max="100">  </progress>
				<script> 
					var prog = 0
					var progressbar = $('#progressbar')
					value = progressbar.val(); 
			
				</script>
			<div id="coords">
				X <div id="xcoord"> </div> 
				Y <div id="ycoord"> </div> 
				Z <div id="zcoord"> </div>
			</div>
		
		</div>
		<div id="select-marquee"></div>
		<!-- <script src="multiselect.js"></script> -->
		<script>
			
			var camera, scene, renderer;
			var mesh;
			var buildingmesh = [];
			var roofmesh = [];
			var arrayCombined = [];
			var meshlen
			
			init();
			animate();
			
			function init() {
				// Create document container
				container = document.getElementById("container")

				//Create the width and heights using some jQuery magic
				canvasheight = $( "#container" ).height()  
				canvaswidth = $( "#container" ).width()
				console.log("Canvas Width: ", canvaswidth)
				console.log("Canvas Height: ", canvasheight)
				
				// Load Stats
				stats = new Stats();
				stats.setMode(0);
				container.appendChild( stats.domElement ); 
				
				// Detect Mouse Movement
				container.addEventListener( 'mousemove', onMouseMove, false );
				container.addEventListener( 'click', onMouseMove, false );

				// Resize detector	
				window.addEventListener( 'resize', onWindowResize, false );
				
				// Create Renderer, set antialiasing (smoother graphically, worse performance)
				renderer = new THREE.WebGLRenderer({ antialias: false });
				renderer.setSize( canvaswidth, canvasheight );
				renderer.setClearColor( '#707070', 1 ) // SETS BACKGROUND COLOR
				container.appendChild( renderer.domElement );
	
				//Scene
				scene = new THREE.Scene();
				
				var centroid 
				// Ugly but functional; get the centroid from Postgresql into PHP, strip quote marks, parse into JS float
				var maxxextent = parseFloat("<?php echo  str_replace('"', "", json_encode($centroid[0])); ?>")
				var maxyextent = parseFloat("<?php echo str_replace('"', "", json_encode($centroid[1])); ?>")
				var minxextent = parseFloat("<?php echo str_replace('"', "", json_encode($centroid[2])); ?>")
				var minyextent = parseFloat("<?php echo str_replace('"', "", json_encode($centroid[3])); ?>")
				console.log("Max X: ", maxxextent, "Max Y: ", maxyextent, "Min X: ", minxextent, "Min Y: ", minyextent);
				
				// X , Y
				X = ((maxxextent - minxextent) / 2) + minxextent
				Y = ((maxyextent - minyextent) / 2) + minyextent
				centroid = [X, Y]
				console.log("Centroid: ", centroid[0], centroid[1]);
	
				//Camera 
				camera = new THREE.PerspectiveCamera(60, canvaswidth / canvasheight , 1, 10000 );
				camera.position = new THREE.Vector3(X ,Y , 1);
				
				//Controls
				controls = new THREE.TrackballControls( camera, renderer.domElement );
				controls.rotateSpeed = 0.4;
				controls.zoomSpeed = 1.0;
				controls.panSpeed = 1.0;
				controls.noZoom = false;
				controls.noPan = false;
				controls.staticMoving = true;
				controls.dynamicDampingFactor = 0.3;
				controls.minDistance = 50;
				controls.maxDistance = 8000;
				controls.keys = [ 65, 83, 68 ];
				controls.target = new THREE.Vector3(X, Y, 0)
				controls.addEventListener( 'change', render );

				// Scene Objects (3D Data)
				
				//We need to use an Epsilon to stop the triangulator from believeing there are duplicate points
				var EPSILON = 0.000001 // 0 meter 0.0 decimeter 0.00 centimeter 0.000 milimeter
				
				var material = new THREE.MeshLambertMaterial( {color: 0x333333, ambient: 0x333333} );
				var modelVertices = [];
				var aLayer = <?php echo getModel('"Vegetation"'); ?>;
				//console.log(aLayer);
				if (aLayer[1][0] == "POLYHEDRALSURFACE Z" && aLayer[1][1] != null) {
					//console.log("Hello");
					console.log(aLayer[1][1]);
					aLayer[1][1].slice(0, - 5); // Remove final ::: 
					
					var aLayerArray = aLayer[1][1].split(" ::: ");
					//console.log(aLayerArray);
					
					aLayerArray.forEach( function(arrayModel) {
						var uniqueCoords = [];
						arrayModelSplit = arrayModel.split(",");
						
						arrayModelSplit.forEach( function(modelUnformattedCoords) {
							if ($.inArray(modelUnformattedCoords, uniqueCoords) === -1) {
								modelCoords = modelUnformattedCoords.split(" ");
								modelVertices.push(modelCoords);
								uniqueCoords.push(modelUnformattedCoords);
								
								//console.log(modelCoords);
								}
							else { //console.log("Duplicate found: ", modelUnformattedCoords)
							}
						})

						//console.log(uniqueCoords);
						var holes = [];
						var triangles;
						var modelGeometry = new THREE.Geometry();
						var uniqueVertices = [];
						var epsilonCheck = [];
						//console.log(modelVertices.length)
						
						modelVertices.forEach(function(part, index, theVertices) {
							//console.log(part);
							if (part.length != 3) { console.log("Vertex list length not eqaul to 3: can't convert to Three.js vertex") }
							else {
								// The triangulator doesn't appear to take into account the fact that some points may share X Y coordinates, to overcome this we add an epsilon
								// so Three.js thinks they're different coordinates
								part.forEach(function(coord, i) {
									if ($.inArray(parseFloat(coord), epsilonCheck) != -1) {
										part[i] = parseFloat(coord) + EPSILON 
										};
								})
								
								// Change the vertices from str/float to Three.js vertexs 
								theVertices[index] = new THREE.Vector3( part[0], part[1] , part[2] );
								
								//Push all the parts into the checklist to make sure there is no recur
								epsilonCheck.push(parseFloat(part[0]), parseFloat(part[1]), parseFloat(part[2]))
							}
						});
						
						//console.log(modelVertices);
						//modelVertices.reverse();
						modelGeometry.vertices = modelVertices;
						
						// If the list isn't null or less than 3 triangulate it!
						if (modelVertices != null && modelVertices.length >= 3) {
							triangles = THREE.Shape.Utils.triangulateShape ( modelVertices, holes );

							for( var i = 0; i < triangles.length; i++ ){
								//console.log(triangles[i][0], triangles[i][1], triangles[i][2]);
								modelGeometry.faces.push( new THREE.Face3( triangles[i][0], triangles[i][1], triangles[i][2] ));
							}

							var modelMesh = new THREE.Mesh(modelGeometry, material);
							scene.add(modelMesh);
						}
						modelVertices = [];
						modelCoords = [];
						
					})
				}
				
				if (aLayer[2][0] == "TIN Z" && aLayer[2][1] != null) {
					//console.log("Hello");
					aLayer[2][1].slice(0, - 5); // Remove final ::: 
					
					var aLayerArray = aLayer[2][1].split(" ::: ");
					//console.log(aLayerArray);
					
					aLayerArray.forEach( function(arrayModel) {
						var uniqueCoords = [];
						arrayModelSplit = arrayModel.split(",");
						
						arrayModelSplit.forEach( function(modelUnformattedCoords) {
							if ($.inArray(modelUnformattedCoords, uniqueCoords) === -1) {
								modelCoords = modelUnformattedCoords.split(" ");
								modelVertices.push(modelCoords);
								uniqueCoords.push(modelUnformattedCoords);
								
								//console.log(modelCoords);
								}
							else { //console.log("Duplicate found: ", modelUnformattedCoords)
							}
						})

						//console.log(uniqueCoords);
						var holes = [];
						var triangles;
						var modelGeometry = new THREE.Geometry();
						var uniqueVertices = [];
						var epsilonCheck = [];
						//console.log(modelVertices.length)
						
						modelVertices.forEach(function(part, index, theVertices) {
							//console.log(part);
							if (part.length != 3) { console.log("Vertex list length not eqaul to 3: can't convert to Three.js vertex") }
							else {
								// The triangulator doesn't appear to take into account the fact that some points may share X Y coordinates, to overcome this we add an epsilon
								// so Three.js thinks they're different coordinates
								part.forEach(function(coord, i) {
									if ($.inArray(parseFloat(coord), epsilonCheck) != -1) {
										part[i] = parseFloat(coord) + EPSILON 
										};
								})

								// Change the vertices from str/float to Three.js vertexs 
								theVertices[index] = new THREE.Vector3( part[0], part[1] , part[2] );
								
								//Push all the parts into the checklist to make sure there is no recur
							}
						});
						
						//console.log(modelVertices);
						//modelVertices.reverse();
						modelGeometry.vertices = modelVertices;
						
						// If the list isn't null or less than 3 triangulate it!
						if (modelVertices != null && modelVertices.length >= 3) {
							triangles = THREE.Shape.Utils.triangulateShape ( modelVertices, holes );

							for( var i = 0; i < triangles.length; i++ ){
								//console.log(triangles[i][0], triangles[i][1], triangles[i][2]);
								modelGeometry.faces.push( new THREE.Face3( triangles[i][0], triangles[i][1], triangles[i][2] ));
							}

							var modelMesh = new THREE.Mesh(modelGeometry, material);
							scene.add(modelMesh);
						}
						modelVertices = [];
						modelCoords = [];
						
					})
				}

				// http://threejs.org/examples/webgl_geometry_terrain_raycast.html
				// Raycast Helper
				var helpergeometry = new THREE.CylinderGeometry( 0, 5, 15, 3 ); // radius at top, radius at bottom, height, segments
				//geometry.applyMatrix( new THREE.Matrix4().makeTranslation( 10, 50, 0 ) );
				helpergeometry.applyMatrix( new THREE.Matrix4().makeRotationX( Math.PI / 2 ) );
				helper = new THREE.Mesh( helpergeometry, new THREE.MeshLambertMaterial({ color: 0xEB1515, ambient: 0xEB1515, wireframe: false }) );
				scene.add( helper );
				rayz = true;

				// Axis Helper -- red = X - green = Y  - blue = Z
				axes = new THREE.AxisHelper(300);
				axes.position = new THREE.Vector3(centroid[0], centroid[1], 0);
				
				// Lighting
				var ambientLight = new THREE.AmbientLight(0xFFFFFF, 0.1);
				
				//Create Scene
				scene.add( axes );
				scene.add(ambientLight);
				
			}
			
			function onMouseMove( event ) {
				
				if ( rayz == true ) {
					
					cX = event.clientX - $( "#info" ).width();
					cY = event.clientY - $( "#topbar").height() ;
					
					var mouseX = ( cX / canvaswidth  ) * 2 - 1;
					var mouseY = -( cY / canvasheight ) * 2 + 1;

					var vector = new THREE.Vector3( mouseX, mouseY, camera.near );
		
					// Convert the [-1, 1] screen coordinate into a world coordinate on the near plane
					var projector = new THREE.Projector();
					projector.unprojectVector( vector, camera );
					
					var raycaster = new THREE.Raycaster( camera.position, vector.sub( camera.position ).normalize() );

					// See if the ray from the camera into the world hits one of our meshes
					for (var i = 0; i < meshlen; i++) {
			
						var intersects = raycaster.intersectObject( buildingsroofs[i] );
						if ( intersects.length > 0 ) {
							helper.position.set( 0, 0, 0 );
							helper.lookAt( intersects[ 0 ].face.normal );
							helper.position.copy( intersects[ 0 ].point );
							//console.log(String(intersects[ 0 ].point.x));
							$('#xcoord').html(String(intersects[ 0 ].point.x));
							$('#ycoord').html(String(intersects[ 0 ].point.y));
							$('#zcoord').html(String(intersects[ 0 ].point.z));
						}
					}
				
				}
			}
			
			function onWindowResize() {
				
				//Create the width and heights using some jQuery magic
				canvasheight = $( "#container" ).height() // Adjust for the bottom bar
				canvaswidth = $( "#container" ).width() 
				camera.aspect = canvaswidth / canvasheight ;
				camera.updateProjectionMatrix();

				renderer.setSize( canvaswidth , canvasheight   );
				stats.update();
				
				//Fix attributes window on resize
				var infoHeight = $('#info').height()
				var layersHeight = $("#layers").height()
				var newHeight = infoHeight - layersHeight;
				$("#attributes").height( newHeight );

			}

			function animate() {

				requestAnimationFrame( animate );
				controls.update();
				stats.update();
				render();
				
			}
			
			function render() {

				renderer.render( scene, camera );
				stats.update();
				
			}

		</script>
		<script src="layersattributes.js"></script> <!-- Page needs to have loaded first to run this script successfully! -->
	</body>
</html>