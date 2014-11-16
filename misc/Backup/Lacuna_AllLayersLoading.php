<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Lacuna - 3D Web GIS using HTML5</title>
		<meta charset="utf-8">
		
		<link rel="stylesheet" href="//code.jquery.com/ui/1.10.4/themes/smoothness/jquery-ui.css">
		<script src="http://threejs.org/build/three.js"></script>
		<script src="http://threejs.org/examples/js/controls/TrackballControls.js"></script>
		<script src="http://threejs.org/examples/js/libs/stats.min.js"></script>
		<script src="jquery-1.11.0.min.js"></script> <!-- jQuery must be defined first! -->
		<script src="//code.jquery.com/ui/1.10.4/jquery-ui.js"></script>
		<script src="perfect-scrollbar-0.4.10.with-mousewheel.min.js"></script>
		<script src='spectrum.js'></script>
		<script src='getrandomcolor.js'></script>
		<script src='pnltri.min.js'></script>
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
		
	</head>

	<body>
		<script>
			 // console.log("Using PNLTRI");
 			// THREE.Shape.Utils.triangulateShape = ( function () {
 				 // var pnlTriangulator = new PNLTRI.Triangulator();
 				 // return function ( contour, holes ) {
 					  // console.log("new Triangulation: PnlTri.js " + PNLTRI.REVISION );
 				 // return pnlTriangulator.triangulate_polygon( [ contour ].concat(holes) );
 				 // };
 			 // } )();
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
										$layerList = array();
										while ($layer = pg_fetch_row($result)) {
											echo "<br>";
											echo "<input type='checkbox' id='$layer[0]' style='vertical-align: middle; float:left; width:45px' />";
											echo "$layer[0]";
											array_push($layerList, $layer[0]);
											echo "<br>";
										}
										echo "<br><br><br>";
									}
							?>
							<script> var jsLayerList = <?php echo json_encode($layerList); ?>; </script>
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
				
				
				
				// Create Renderer, set antialiasing (smoother graphically, worse performance)
				renderer = new THREE.WebGLRenderer({ antialias: true });
				renderer.setSize( canvaswidth, canvasheight );
				renderer.setClearColor( '#262626', 1 ) // SETS BACKGROUND COLOR
				container.appendChild( renderer.domElement );
	
				//Scene
				scene = new THREE.Scene();
				
				//Centroid
				var centroid 
				// Ugly but functional; get the centroid from Postgresql into PHP, strip quote marks, parse into JS float
				var maxxextent = parseFloat("<?php echo str_replace('"', "", json_encode($centroid[0])); ?>")
				var maxyextent = parseFloat("<?php echo str_replace('"', "", json_encode($centroid[1])); ?>")
				var minxextent = parseFloat("<?php echo str_replace('"', "", json_encode($centroid[2])); ?>")
				var minyextent = parseFloat("<?php echo str_replace('"', "", json_encode($centroid[3])); ?>")
				console.log("Max X: ", maxxextent, "Max Y: ", maxyextent, "Min X: ", minxextent, "Min Y: ", minyextent);
				
				// X , Y
				X = ((maxxextent - minxextent) / 2) + minxextent
				Y = ((maxyextent - minyextent) / 2) + minyextent
				centroid = [X, Y]
				console.log("Centroid: ", centroid[0], centroid[1]);
				var baseGeometry = new THREE.PlaneGeometry( maxxextent - minxextent, maxyextent - minyextent );
				baseGeometry.applyMatrix( new THREE.Matrix4().makeTranslation(centroid[0], centroid[1], 0) );
				var baseMesh = new THREE.Mesh(baseGeometry, new THREE.MeshBasicMaterial({ color: 0x262626, reflectivity: 0, wireframe: false }));
				baseMesh.name = "0 Height"
				
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

				// Add base geometry
				//scene.add(baseMesh);
				
				// Detect Mouse Movement
				container.addEventListener( 'mousemove', onMouseMove, false );
				container.addEventListener( 'click', onMouseMove, false );

				// Resize detector	
				window.addEventListener( 'resize', onWindowResize, false );
				
				// Scene Objects (3D Data)
				
				//We need to use an Epsilon to stop the triangulator from believeing there are duplicate points
				var EPSILON = 0.0005 // 1 meter - 0.1 decimeter 0.01 - centimeter 0.001 milimeter
				
				var modelVertices = [];

				function getLayer (layername) {
					var response =
						$.ajax({
							  url: 'getdataajax.php',
							  type: 'get',
							  dataType: "json",
							  data: {'layer': layername},
							  async: false,
							  success: function(data) {
								//alert( "Layer loaded into Lacuna" )
								}
							 }).responseJSON;
					return response;
				}
				
				function loadLayer(layername) {
				
					aLayer = getLayer(layername);
					//console.log("Linestring: ", aLayer[3][1]);
					//console.log(aLayer);
					if (aLayer[1][0] == "POLYHEDRALSURFACE Z" && aLayer[1][1] != null) {
						aLayerFormatted = aLayer[1][1].slice(0, - 5); //  Remove final %%%
						var aLayerArray = aLayerFormatted.split(" %%% ");
						//console.log(aLayerArray);
						id = 0
						aLayerArray.forEach( function(aFeature) {
							id += 1
							polyhedralzGroup = new THREE.Object3D();
							aFeature.slice(0, - 5); // Remove final ::: 
							ranCol = getRandomColor();
							material = new THREE.MeshLambertMaterial({color: ranCol, ambient: ranCol})
							var aObjectArray = aFeature.split(" ::: ");
							//console.log(aLayerArray);
							
							aObjectArray.forEach( function(arrayModel) {
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
								modelVertices.reverse();
								
								modelVertices.forEach(function(part, index, theVertices) {
									//console.log(part);
									if (part.length < 3 && part[0] != "" ) { console.log("Vertex list length is ", part.length, "needs to be three to convertto Three.js vertex", part) }
									else {
										// The triangulator doesn't appear to take into account the fact that some points may share X Y coordinates, to overcome this we add an epsilon
										// so Three.js thinks they're different coordinates
										part.forEach(function(coord, i) {
											if ($.inArray(parseFloat(coord), epsilonCheck) != -1) {
												part[i] = parseFloat(coord) + EPSILON 
												};
										})
										
										// Change the vertices from str/float to Three.js vertexs 
										theVertices[index] = new THREE.Vector3( parseFloat(part[0]), parseFloat(part[1]) , parseFloat(part[2]) );
										
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
								
									modelGeometry.computeFaceNormals();
									var modelMesh = new THREE.Mesh(modelGeometry, material);
									modelMesh.material.side = THREE.DoubleSide;
									//modelMesh.geometry.normalsNeedUpdate = true;
									//console.log(modelMesh.geometry.normalsNeedUpdate);
									modelMesh.geometry.computeFaceNormals();
									
									polyhedralzGroup.add(modelMesh)
									
								}
								modelVertices = [];
								modelCoords = [];
								
							});
							
							// Set mesh name to pg id
							idname = layername.replace('"', '')
							idname = idname.replace('"', '')
							idname = idname.concat(" ")
							polyhedralzGroup.name =  idname.concat(id.toString());
							// Add group to scene
							scene.add(polyhedralzGroup);
						});
						
					}
					
					if (aLayer[0][0] == "POLYGON ZM" && aLayer[0][1] != null) {
						aLayerFormatted = aLayer[0][1].slice(0, - 5); //  Remove final %%%
						var aLayerArray = aLayerFormatted.split(" %%% ");
						//console.log(aLayerArray);
						id = 0
						aLayerArray.forEach( function(aFeature) {
							id += 1
							polyhedralzGroup = new THREE.Object3D();
							aFeature = aFeature.slice(0, - 5); // Remove final ::: 
							ranCol = getRandomColor();
							material = new THREE.MeshLambertMaterial({color: ranCol, ambient: ranCol})
							var aObjectArray = aFeature.split(" ::: ");
							console.log(id);
							
							aObjectArray.forEach( function(arrayModel) {
								var uniqueCoords = [];
								polygon = [];
								holes = [];
								//console.log(arrayModel);
								holesBoolSpoof = (arrayModel.indexOf(" &&& ") > -1)
								holesBool = false //(arrayModel.indexOf(" &&& ") > -1)
								//console.log(arrayModel);
								//var count = arrayModel.match(/ &&& /g);  

								//alert(count.length);
								
								
								if (holesBool === true) {
									//console.log("Holes found");
									arrayModelSplit = arrayModel.split(" &&& ");
									//console.log("Object Array", arrayModelSplit);
									polygon = arrayModelSplit[0] // Polygon is the first part
									polygon = polygon.split(",") // Now a series of x y z strings 
									//console.log("Polygon with hole", polygon)
									arrayModelSplit.shift() // Holes is the rest
									holes = arrayModelSplit
									//console.log("Holes in polygon", holes.length, holes);
									//console.log("polygon   ", polygon)
									//console.log("holes   ", holes);
								}
								
								if (holesBool === false) { 
									polygon = arrayModel.split(",");
								}
								
								if(holesBoolSpoof === true) {
									console.log(holesBoolSpoof) }
								
								//console.log(polygon);
								
								polygon.forEach( function(modelUnformattedCoords) {
									if ($.inArray(modelUnformattedCoords, uniqueCoords) === -1) {
										modelCoords = modelUnformattedCoords.split(" ");
										modelVertices.push(modelCoords);
										uniqueCoords.push(modelUnformattedCoords);
										
										//console.log(modelCoords);
										}
									else { //console.log("Duplicate found: ", modelUnformattedCoords)
									}
								})
								
								//List
								//console.log(holes)
								if (holesBool === true) {
									holes.forEach( function(polygonHole, polygonHoleIndex, polygonHoles) {
										splitVertexHole = polygonHole.split(",")
										splitVertexHole.pop();
										//console.log(splitVertexHole);
										splitVertexHole.forEach( function(vertex, vindex){ 
											vertexArray = splitVertexHole[vindex].split(" ") 
											//console.log(vertexArray)
											splitVertexHole[vindex] = new THREE.Vector2( parseFloat(vertexArray[0]), parseFloat(vertexArray[1]) )
											//console.log(splitVertexHole[vertex]);
										})
									polygonHoles[polygonHoleIndex] = splitVertexHole ;
									});
								}
								
								var triangles;
								var modelGeometry = new THREE.Geometry();
								var uniqueVertices = [];
								var epsilonCheck = [];
								//console.log(modelVertices.length)
								//modelVertices.reverse();
								//console.log(holes)
								modelVertices.forEach(function(part, index, theVertices) {
								//console.log(part);
									if (part.length < 3 && part[0] != "" ) { console.log("Vertex list length is ", part.length, "needs to be three to convertto Three.js vertex", part) }
									else {
										// The triangulator doesn't appear to take into account the fact that some points may share X Y coordinates, to overcome this we add an epsilon
										// so Three.js thinks they're different coordinates
										part.forEach(function(coord, i) {
											if ($.inArray(parseFloat(coord), epsilonCheck) != -1) {
												part[i] = parseFloat(coord) + EPSILON 
												};
										})
										
										// Change the vertices from str/float to Three.js vertexs 
										theVertices[index] = new THREE.Vector3( parseFloat(part[0]), parseFloat(part[1]) , parseFloat(part[2]) );
										
										//Push all the parts into the checklist to make sure there is no recur
										epsilonCheck.push(parseFloat(part[0]), parseFloat(part[1]), parseFloat(part[2]))
									}
								});
								
								modelGeometry.vertices = modelVertices
								//console.log("vertices", modelVertices)
								//console.log("holes", holes);
								//console.log(holes);
								
								// If the list isn't null or less than 3 triangulate it!
								if (modelVertices != null && modelVertices.length >= 3) {
									console.log(holes);
									triangles = THREE.Shape.Utils.triangulateShape ( modelVertices, holes );
									for( var i = 0; i < triangles.length; i++ ){
										//console.log(triangles[i][0], triangles[i][1], triangles[i][2]);
										modelGeometry.faces.push( new THREE.Face3( triangles[i][0], triangles[i][1], triangles[i][2] ));
										
									}
									
									modelGeometry.computeFaceNormals();
									var modelMesh = new THREE.Mesh(modelGeometry, material);
									modelMesh.material.side = THREE.DoubleSide;
									//modelMesh.geometry.normalsNeedUpdate = true;
									//console.log(modelMesh.geometry.normalsNeedUpdate);
									//modelMesh.geometry.computeFaceNormals();
									//console.log(modelMesh);
									polyhedralzGroup.add(modelMesh)
									
								}
								modelVertices = [];
								modelCoords = [];
								
							});
							
							// Set mesh name to pg id
							idname = layername.replace('"', '')
							idname = idname.replace('"', '')
							idname = idname.concat(" ")
							polyhedralzGroup.name =  idname.concat(id.toString());
							// Add group to scene
					
							scene.add(polyhedralzGroup);
						});
						
					}
					
					if (aLayer[2][0] == "TIN Z" && aLayer[2][1] != null) {
					
						var TINMaterial = new THREE.MeshNormalMaterial( {color: 0x27B030, ambient: 0x27B030} );
						aLayerFormatted = aLayer[2][1].slice(0, - 5); // Remove final %%%
						var aLayerArray = aLayerFormatted.split(" %%% ");
						id = 0
						
						aLayerArray.forEach( function(aFeature) {
							id += 1
							tinzGroup = new THREE.Object3D();
							//console.log((aFeature == ""));
							var aFeatureArray = aFeature.split(" |||  ||| ");
							
							aFeatureArray.forEach( function(arrayModel) {
								if (arrayModel.length > 0) {
								
									arrayModel.slice(0, - 1); //Remove final comma
									arrayModelSplit = arrayModel.split(",");
									arrayModelSplit.pop(); //Remove full circle point (not necessary)
									arrayModelSplit.forEach( function(modelUnformattedCoords) {
										
											modelCoords = modelUnformattedCoords.split(" ");
											modelVertices.push(modelCoords);
										
									})
								
									var modelGeometry = new THREE.Geometry();
									
									modelVertices.forEach(function(part, index, theVertices) {
									
										if ( part.length != 3) { 
											console.log("Vertex list length not eqaul to 3: can't convert to Three.js vertex") 
										}
										else {
											theVertices[index] = new THREE.Vector3( parseFloat(part[0]), parseFloat(part[1]) , parseFloat(part[2]));
										}
									});
									
									modelGeometry.vertices = modelVertices;
									
									// If the list isn't null or less than 3 triangulate it!
									if (modelVertices != null && modelVertices.length >= 3) {
										modelGeometry.faces.push( new THREE.Face3( 0, 1, 2 ));
										modelGeometry.computeFaceNormals();
										var modelMesh = new THREE.Mesh(modelGeometry, TINMaterial);
										modelMesh.material.side = THREE.DoubleSide;  //SET DOUBLE SIDED
										tinzGroup.add(modelMesh) //Add mesh to group
									}
							
									modelVertices = [];
									modelCoords = [];
								}
							})
							
							idname = layername.replace('"', '')
							idname = idname.replace('"', '')
							idname = idname.concat(" ")
							tinzGroup.name = idname.concat(id.toString());
							//console.log("Adding group", tinzGroup);
							scene.add(tinzGroup);
						});
						
					}
					
					//console.log("HELLO?");
					//console.log(aLayer[3][1]);
					if (aLayer[3][0] == "LINESTRING Z" && aLayer[3][1] != null) {
							aLayerFormatted = aLayer[3][1].slice(0, - 5); //  Remove final %%%
							var aLayerArray = aLayerFormatted.split(" %%% ");
							//console.log(aLayerArray);
							id = 0
							
							ranCol = getRandomColor();
							var lineMaterial = new THREE.LineBasicMaterial({color: ranCol,  linewidth: 10});
							
							aLayerArray.forEach( function(aFeature) {
								id = 1;
								var lineGeometry = new THREE.Geometry();
								aLine = aFeature.split(",");
								aLine.forEach( function(aLineVertex) {
									v = aLineVertex.split(" ")
									if (v[0] == "") { v.shift() } // Sometimes the first value is "" which causes the lines to fire off into space!
									lineGeometry.vertices.push( new THREE.Vector3(parseFloat(v[0]), parseFloat(v[1]), parseFloat(v[2])))
								});
								
								aLineMesh = new THREE.Line(lineGeometry, lineMaterial);
								idname = layername.replace('"', '')
								idname = idname.replace('"', '')
								idname = idname.concat(" ")
								aLineMesh.name =  idname.concat(id.toString());
								scene.add(aLineMesh);
							});
						
					}
					
					if (aLayer[4][0] == "POINT Z" && aLayer[4][1] != null) {
						console.log(aLayer[4][1]);
						aLayerFormatted = aLayer[4][1].slice(0, - 5); //  Remove final %%%
						var aLayerArray = aLayerFormatted.split(" %%% ");
						console.log(aLayerArray);
						id = 0
						
						ranCol = getRandomColor();
						var pointMaterial = new THREE.MeshBasicMaterial({color: ranCol, ambient: ranCol});
						var pointGeometry = new THREE.SphereGeometry( 4, 32, 32 );
						

						aLayerArray.forEach( function(aFeature) {
							id += 1
							aPoint = aFeature.split(" ");
							if (aPoint[0] == "") { aPoint.shift() }
							console.log(aPoint)
							point = new THREE.Mesh( pointGeometry, pointMaterial  );
							point.applyMatrix( new THREE.Matrix4().makeTranslation(aPoint[0], aPoint[1], aPoint[2]) );
							idname = layername.replace('"', '')
							idname = idname.replace('"', '')
							idname = idname.concat(" ")
							point.name =  idname.concat(id.toString());
							scene.add(point);
						});
						
					}
							
				}	
				addedToScene = []
				visibleBools = []

				jsLayerList.forEach( function(jsLayer) {
					//console.log(jsLayer);
					
					visibleBools.push(false);
					
					$("#".concat(jsLayer)).on('click', function() { 
						 
						if (visibleBools[jsLayerList.indexOf(jsLayer)] == false) {
							layerName = '"'.concat(jsLayer).concat('"');
							if ($.inArray(jsLayer, addedToScene) === -1) { 
								console.log("Loading from PG")
								loadLayer(layerName); 
								addedToScene.push(jsLayer);
								
							}
							
							else { 
								scene.children.forEach( function(childLayer) { 
									if (childLayer.name != undefined || childLayer.name === "") {
										if (childLayer.name.lastIndexOf(jsLayer, 0) === 0) {
											console.log("Loading from geometry")
											childLayer.traverse( function ( object ) { object.visible = true; } );
										}
									}
								});
							}
							visibleBools[jsLayerList.indexOf(jsLayer)] = true; 
						}
						
						else if (visibleBools[jsLayerList.indexOf(jsLayer)] == true) {
							scene.children.forEach( function(childLayer) { 
								if (childLayer.name != undefined || childLayer.name === "") {
									if (childLayer.name.lastIndexOf(jsLayer, 0) === 0) {
										console.log("Disabling geometry")
										childLayer.traverse( function ( object ) { object.visible = false; } );
									}
								}
							});
							visibleBools[jsLayerList.indexOf(jsLayer)] = false;
						}
					})
				});
				
				//loadLayer('"BuildingRoofs"');
				//loadLayer('"BuildingWalls"');
				//loadLayer('"Vegetation"')
				
				//console.log(numOfTri);
				// http://threejs.org/examples/webgl_geometry_terrain_raycast.html
				// Raycast Helper
				var helpergeometry = new THREE.CylinderGeometry( 0, 1, 2, 3 ); // radius at top, radius at bottom, height, segments
				//geometry.applyMatrix( new THREE.Matrix4().makeTranslation( 10, 50, 0 ) );
				helpergeometry.applyMatrix( new THREE.Matrix4().makeRotationX( Math.PI / 2 ) );
				helper = new THREE.Mesh( helpergeometry, new THREE.MeshBasicMaterial({ color: 0xEB1515, reflectivity: 0, wireframe: false }) );
				scene.add( helper );
				helpertoggle = true;

				// Axis Helper -- red = X - green = Y  - blue = Z
				axes = new THREE.AxisHelper(300);
				axes.position = new THREE.Vector3(centroid[0], centroid[1], 0);
				
				// Lighting
				var ambientLight = new THREE.AmbientLight(0xFFFFFF, 0.1);
				var directionalLight1 = new THREE.DirectionalLight(0xFFFFFF, 0.1);
				var directionalLight2 = new THREE.DirectionalLight(0xFFFFFF, 0.1);
				var directionalLight3 = new THREE.DirectionalLight(0xFFFFFF, 0.1);
				var directionalLight4 = new THREE.DirectionalLight(0xFFFFFF, 0.1);
				
				//Create Scene
				scene.add( axes );
				scene.add(directionalLight1);
				scene.add(directionalLight2);
				scene.add(directionalLight3);
				scene.add(directionalLight4);
				scene.add(ambientLight);
				
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
			
			function onMouseMove( event ) {
				
				if ( helpertoggle == true ) {
					//scene.updateMatrixWorld();
					cX = event.clientX - $( "#info" ).width();
					cY = event.clientY - $( "#topbar").height() ;
					
					var mouseX = ( cX / canvaswidth  ) * 2 - 1;
					var mouseY = -( cY / canvasheight ) * 2 + 1;

					var vector = new THREE.Vector3( mouseX, mouseY, camera.near );
		
					// Convert the [-1, 1] screen coordinate into a world coordinate on the near plane
					var projector = new THREE.Projector();
					projector.unprojectVector( vector, camera );
					
					var raycaster = new THREE.Raycaster( camera.position, vector.sub( camera.position ).normalize() );
					
					var intersects = raycaster.intersectObjects( scene.children, true );
					//console.log(intersects);
					helper.position.set( 0, 0, 0 );
					//console.log(intersects.length);
					if ( intersects.length > 0 ) {
						console.log("Intersection ", intersects.length);
						if (intersects[ 0 ].face != null) {
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

		</script>
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
								if (helper != null) {
									$("#helpercolour").css("background-color", color.toHexString())
								
									helper.material.color.setHex( color.toHexString().replace("#", "0x" ));
									console.log(color.toHexString().replace("#", "0x" ))
								}
							}
						});
						$("#canvascolour").spectrum({
							color: "#262626",
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
								if (helper != null) {
									console.log(helper.visible);
	
									if (helper.visible == true) {
										console.log("Disabling helper");
										helper.visible = false
									}
								}
							}
							else {
								helperswitch = true;
								$('#helpertoggle').text("ON");
								if (helper != null) {
									if (helper.visible == false) {
											helper.visible = true
											console.log("Enabling helper");
									}
								}
							}
						});
				});
			</script>
		<script src="layersattributes.js"></script> <!-- Page needs to have loaded first to run this script successfully! -->
	</body>
</html>