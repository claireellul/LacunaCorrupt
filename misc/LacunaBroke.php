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
				<div id="axis" title="Axis Toggle"><img src="imgs/axis.png" ></img> </div><div id="axisoptions">
					<div id="axistoggle"> ON </div> 
				</div>
				<div id="wireframe" title="Wireframe Mode"><img src="imgs/wireframe.png" ></img> </div><div id="wireframeoptions">
					<div id="wireframetoggle"> OFF </div> 
				</div>
				<div id="select" title="Select" ><img src="imgs/select.png" ></img>  </div><div id="selectoptions">
					<div id="singleselect" title="Single Select"><img id="singleselectimage" src="imgs/singleselect.png" ></img> </div>
					<div id="multiselect" title="Multi Select"><img src="imgs/multiselect.png" ></img> </div> 
				</div>
				<div id="measure" title="Measure"><img src="imgs/measure.png" ></img> </div><div id="measureoptions"></div>
				<div id="objectedit" title="Object Edit"><img  src="imgs/objectedit.png" ></img> </div><div id="objecteditoptions"></div>
				<div id="mode" title="GIS Mode"> Visualise </div>
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
						<div id="attributestop">
							<p class="titles"> Attributes </p>  <div id="loadselected"> </div>
					
						</div>
						 <script> $( "#loadselected" ).button( {label: "Get Selected", text: true} ); </script>
						 <div id="attributesholder"> </div>
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
			var SELECT = false;
			var SELECTED = { 
							sceneobject : [ ],
							color: [ ]
						};

			
			var SELECTED_MATERIAL = new THREE.MeshLambertMaterial({ color: 0xCCCCCC, ambient: 0xCCCCCC, reflectivity: 0});
			;
			
			init();
			animate();
			
			function init() {
			
				//Selection variables
				
				
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
				camera = new THREE.PerspectiveCamera(90, canvaswidth / canvasheight , 1, 20000 );
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
							  timeout: 60000,
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
					//console.log(aLayer);
					
					if ((aLayer[1][0] == "POLYHEDRALSURFACE Z") && (aLayer[1][1] != "")) {
						aLayerFormatted = aLayer[1][1].slice(0, - 5); //  Remove final %%%
						var aLayerArray = aLayerFormatted.split(" %%% ");
						var ids = aLayer[1][2]
						//console.log((ids.length === aLayerArray.length) );
						
						//console.log(aLayerArray);
						var id = 0
						console.log(aLayerArray.length, ids.length);
						aLayerArray.forEach( function(aFeature) {
							
							polyhedralzGroup = new THREE.Object3D();
							aFeature = aFeature.slice(0, - 5); // Remove final ::: 
							ranCol = getRandomColor();
							material = new THREE.MeshLambertMaterial({color: ranCol, ambient: ranCol})
							var aObjectArray = aFeature.split(" ::: ");
							//console.log(aObjectArray.length);
							
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
							
							console.log(polyhedralzGroup)
							// Set mesh name to pg id
							idname = layername.replace('"', '')
							idname = idname.replace('"', '')
							idname = idname.concat(" ")
							//console.log(aObjectArray, ids, id);
							//console.log(ids[id]);
							polyhedralzGroup.name =  idname.concat(ids[id].toString());
							
							// Add group to scene
							scene.add(polyhedralzGroup);
							id += 1
						});
						
					}
					
					if ((aLayer[0][0] == "POLYGON ZM") && (aLayer[0][1] != "")) {
						aLayerFormatted = aLayer[0][1].slice(0, - 5); //  Remove final %%%
						var aLayerArray = aLayerFormatted.split(" %%% ");
						console.log(aLayer[0][0], aLayer[0][1]);
						var ids = aLayer[0][2]
						var id = 0
						
						
						aLayerArray.forEach( function(aFeature) {
							
							polygonZ = new THREE.Object3D();
							aFeature = aFeature.slice(0, - 5); // Remove final ::: 
							
							ranCol = getRandomColor();
							material = new THREE.MeshLambertMaterial({color: ranCol, ambient: ranCol})
							var aObjectArray = aFeature.split(" ::: ");
							//console.log(id);
							
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
							polygonZ.name =  idname.concat(ids[id].toString());;
							// Add group to scene
							scene.add(polygonZ);
							id += 1
						});
						
					}
					
					if ((aLayer[2][0] == "TIN Z") && (aLayer[2][1] != "")) {
						//console.log(aLayer[2][1]);
						var TINMaterial = new THREE.MeshLambertMaterial( {color: 0x27B030, ambient: 0x27B030} );
						aLayerFormatted = aLayer[2][1].slice(0, - 5); // Remove final %%%
						var aLayerArray = aLayerFormatted.split(" %%% ");
						var ids = aLayer[2][2];
						id = 0
						
						aLayerArray.forEach( function(aFeature) {
						
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
							tinzGroup.name = idname.concat(ids[id].toString());
							id += 1
							//console.log("Adding", tinzGroup)
							scene.add(tinzGroup);
						});
						
					}
					
					if ((aLayer[3][0] == "LINESTRING Z") && (aLayer[3][1] != "")) {
						aLayerFormatted = aLayer[3][1].slice(0, - 5); //  Remove final %%%
						var aLayerArray = aLayerFormatted.split(" %%% ");
						//console.log(aLayerArray);
						var id = 0
						var ids = aLayer[3][2];
						
						ranCol = getRandomColor();
						var lineMaterial = new THREE.LineBasicMaterial({color: ranCol,  linewidth: 10});
						
						aLayerArray.forEach( function(aFeature) {
							
							var lineGeometry = new THREE.Geometry();
							aLine = aFeature.split(",");
							aLine.forEach( function(aLineVertex) {
								v = aLineVertex.split(" ")
								if (v[0] == "") { v.shift() } // Sometimes the first value is "" which causes the lines to fire off into space!
								lineGeometry.vertices.push( new THREE.Vector3(parseFloat(v[0]), parseFloat(v[1]), parseFloat(v[2])))
							});
							
							aLineMesh = new THREE.Line(lineGeometry, new THREE.LineBasicMaterial({color: ranCol,  linewidth: 10}) );
							idname = layername.replace('"', '')
							idname = idname.replace('"', '')
							idname = idname.concat(" ")
							aLineMesh.name =  
							scene.add(aLineMesh);
							id = 1;
						});
						
					}
					
					
					if ((aLayer[4][0] == "POINT Z") && (aLayer[4][1] != "")) {
					
						aLayerFormatted = aLayer[4][1].slice(0, - 5); //  Remove final %%%
						var aLayerArray = aLayerFormatted.split(" %%% ");
						var ids = aLayer[4][2];
						var id = 0
						ranCol = getRandomColor();
						//var pointMaterial = new THREE.MeshBasicMaterial({color: ranCol, ambient: ranCol});
						//var pointGeometry = new THREE.SphereGeometry( 4, 32, 32 );

						aLayerArray.forEach( function(aFeature) {
							aPoint = aFeature.split(" ");
							if (aPoint[0] == "") { aPoint.shift() }
							console.log(aPoint)
							point = new THREE.Mesh( pointGeometry = new THREE.SphereGeometry( 4, 32, 32 ),  new THREE.MeshBasicMaterial({color: ranCol, ambient: ranCol})  );
							point.applyMatrix( new THREE.Matrix4().makeTranslation(aPoint[0], aPoint[1], aPoint[2]) );
							idname = layername.replace('"', '')
							idname = idname.replace('"', '')
							idname = idname.concat(" ")
							point.name =  idname.concat(ids[id].toString());
							scene.add(point);
							id += 1
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
				
				//$('#container').bind('keypress', function(e) { console.log(e) });
				
				//$("#container").on('keypress', function (e) { console.log(e) })
				
				// http://threejs.org/examples/webgl_geometry_terrain_raycast.html
				// Raycast Helper
				var helpergeometry = new THREE.CylinderGeometry( 0, 2, 4, 3 ); // radius at top, radius at bottom, height, segments
				//geometry.applyMatrix( new THREE.Matrix4().makeTranslation( 10, 50, 0 ) );
				helpergeometry.applyMatrix( new THREE.Matrix4().makeRotationX( Math.PI / 2 ) );
				helper = new THREE.Mesh( helpergeometry, new THREE.MeshBasicMaterial({ color: 0xEB1515, reflectivity: 0, wireframe: false }) );
				helper.name = "Helper"
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
					raycaster.precision = 25
					raycaster.linePrecision = 15
					
					var intersects = raycaster.intersectObjects( scene.children, true );
					//console.log(intersects)
					helper.position.set( 0, 0, 0 );

					if ( intersects.length > 0 ) {
						//console.log(intersects[0].object instanceof THREE.Line)
						if ( (intersects[0].object instanceof THREE.AxisHelper === false) && ((intersects[0].face != null) || (intersects[0].object instanceof THREE.Line)) && (intersects[0].object.name != "Helper")) {
							console.log("Intersection ", intersects.length, intersects[0]);
								intersectedObject = intersects[0].object.parent
								intersectedMesh = intersects[0].object
								if (intersects[ 0 ].face != null) {
									helper.lookAt( intersects[ 0 ].face.normal );
									helper.position.copy( intersects[ 0 ].point );
								}
								//console.log(String(intersects[ 0 ].point.x));
								$('#xcoord').html(String(intersects[ 0 ].point.x));
								$('#ycoord').html(String(intersects[ 0 ].point.y));
								$('#zcoord').html(String(intersects[ 0 ].point.z));
						}
					}
					else {
						intersectedObject = ""
						intersectedMesh = ""
					}
						
					
					 
				}
			}
			
			intersectedObject = ""
			intersectedMesh = ""
			
			$(document).on('keypress', function (e) {
				//console.log(e);
				var code = e.keyCode || e.which;
				//console.log(code);
				keyIsA = ((String.fromCharCode(code) == "a") ||  (String.fromCharCode(code) == "A" )) 
				objectIsIntersected = ((intersectedObject !== "" ) || ( intersectedMesh !== "" ))
				if ( SELECT && keyIsA && objectIsIntersected ){
					
					aObject3D = ((intersectedObject instanceof THREE.Object3D === true ) && (intersectedObject instanceof THREE.Scene === false ));
					aMeshObject = ((intersectedMesh instanceof THREE.Mesh === true ) && (intersectedObject instanceof THREE.Scene === true ));
					aLineObject = (intersectedMesh instanceof THREE.Line === true );
					//console.log(aObject3D, aMeshObject, aLineObject);
					if ( aObject3D || aMeshObject || aLineObject ) {
						//console.log($.inArray(intersectedObject, SELECTED), SELECTED)
						//console.log(aObject3D);
						//console.log();
						if (aObject3D === true) { inSelected = SELECTED.sceneobject.indexOf(intersectedObject) }
						else if (aLineObject === true || aMeshObject === true ) { inSelected = SELECTED.sceneobject.indexOf(intersectedMesh) } 
						//console.log(inSelected)
						
						// If object hasn't been selected
						if (inSelected  === -1) {	
							
							//console.log(intersectedObject)
							 if (aObject3D === true) {
								//console.log(intersectedObject)
								
								colorArray = [];
							
								intersectedObject.children.forEach( function(child, childIndex) {
									if (childIndex === 0) {
										objectColour = child.material.color.clone();
									}
									child.material.color.setHex( 0xCCCCCC )
									child.material.ambient.setHex( 0xCCCCCC )
								});
								console.log("adding ", intersectedObject, " to SELECTED");
								SELECTED.sceneobject.push(intersectedObject)
								SELECTED.color.push(objectColour)
								console.log(SELECTED);
								
							}
							
							else if ((aLineObject === true || aMeshObject === true) && (intersectedMesh.name != "Helper")) {
								
								SELECTED.sceneobject.push(intersectedMesh)
								SELECTED.color.push(intersectedMesh.material.color.clone())
								intersectedMesh.material.color.setHex( 0xCCCCCC )
								
							}
						}
						
						// If object has been selected before
						if (inSelected  != -1) {
						
							
							if (aObject3D === true) {
								intersectedObject.children.forEach( function(child, colIndex) {
									console.log(SELECTED.color[inSelected][0])
									child.material.color.set( SELECTED.color[inSelected] )
									child.material.ambient.set( SELECTED.color[inSelected] )

								});
								//console.log("removing ", intersectedObject, " to SELECTED");
								SELECTED.sceneobject.splice(inSelected, 1);
								//console.log(SELECTED.sceneobject);
								SELECTED.color.splice(inSelected, 1);
							}
							
							else if ((aLineObject === true || aMeshObject === true) && (intersectedMesh.name != "Helper")) {
								
								//console.log("remove color", 
								intersectedMesh.material.color.set( SELECTED.color[inSelected] )
								SELECTED.sceneobject.splice(inSelected, 1);
								SELECTED.color.splice(inSelected, 1);
								
							}
						}
					}
				}
				
				
			});	
			
			function getattributes () {
				tables = [] ;
				objectsToGet = [] ;
				ajaxAttributes = false
				SELECTED.sceneobject.forEach( function(so, selectIndex) {
					if ((so.hasOwnProperty('name')) && ((so.name != "") || (so.name != "Helper"))) {
						objectName = so.name
						objectParts = objectName.split(" "); // Split name into TABLE and ID NUMBER [ 'Bridges', '2' ]
						if (tables.indexOf(objectParts[0]) === -1) {
							tables.push(objectParts[0])
							objectsToGet.push([])
						}
							//console.log(tables.indexOf(objectParts[0]));
						tableNum = tables.indexOf(objectParts[0])
						objectsToGet[tableNum].push(objectParts[1])
					}
				});
					
				//console.log(tablesAndObjectsToGet)
				var attributeresponse =
					$.ajax({
						  url: 'getattributes.php',
						  type: 'get',
						  dataType: "json",
						  timeout: 60000,
						  data: {'tables': tables, 'attributesToGet': objectsToGet},
						  async: false,
						  success: function(data) {
							//alert( "Layer loaded into Lacuna" )
							}
						 }).responseJSON;
				
					
				console.log(attributeresponse);
				
				attributerepsonse.forEach( function(tableToDisplay, tableIndex) {
					
					// For each ROW
					attributerespone[tableIndex][2].forEach( function(attributeObject) {
						
						// Get object keys
						attributeObjectKeys = Object.keys(attributeObject)
						
						attributeArray = [];
						attributeObjectKeys.forEach( function(attributeRow, attributeIndex) {
							if ( typeof attributeArray[attributeIndex] == 'undefined' ) {
								attributeArray.push( [ attributeRow ] ) 
							}
							else {
								// Else push attributeObjects property at key attributeRow
								attributeArray[attributeIndex].push(attributeObject[attributeRow])
							}
						});
					});
					
					//attributeArray is an array with all row values at the same position as the attributeObjectKey array
					tableString = "<tr>" 
					
					attributeArray.forEach ( function(attributeCol, colID) {

						tableString = [tableString, "<td>", attributeCol[colID], "</td>"].join();
						
					});
					
					tableString = tableString.concat("</tr>")
					//$("#attributesholder").append(["<div>", attributerespone[tableIndex], "<br>", tableString, "</div>"]);
					
				});
			}
			
			$('#loadselected').click( function() {
				if ((SELECT === true) && (SELECTED.sceneobject.length != 0)) {
					getattributes()
				}
			});
			
			
		
		
		</script>
		<script src="toolbar.js"></script> 
		<script src="layersattributes.js"></script> <!-- Page needs to have loaded first to run this script successfully! -->
	</body>
</html>