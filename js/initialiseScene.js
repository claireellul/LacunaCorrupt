var clock;
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
	console.log("Max X: ", LAYEREXTENTS[0], "Max Y: ", LAYEREXTENTS[1], "Min X: ", LAYEREXTENTS[2], "Min Y: ", LAYEREXTENTS[3]) ;
	console.log("Centroid: ", CENTROID[0], CENTROID[1]);

	//var baseGeometry = new THREE.PlaneGeometry( maxxextent - minxextent, maxyextent - minyextent );
	//baseGeometry.applyMatrix( new THREE.Matrix4().makeTranslation(centroid[0], centroid[1], 0) );
	//var baseMesh = new THREE.Mesh(baseGeometry, new THREE.MeshLambertMaterial({ color: 0x262626, reflectivity: 0, wireframe: false }));
	//baseMesh.name = "0 Height"

	//Camera

	camera = new THREE.PerspectiveCamera(70, canvaswidth / canvasheight , 1, 4000 );
	//camera.position = new THREE.Vector3(CENTROID[0], CENTROID[1], 20);
	var yOffset = (LAYEREXTENTS[1] - LAYEREXTENTS[3]);
	camera.position = new THREE.Vector3(CENTROID[0],LAYEREXTENTS[3]-yOffset,20);
	// rotate the camera to take account of the fact that three.js axies have
	// +z out of screen, +y up and +x left to right
	// we need +y back into screen, + z up

	// Axis Helper -- red = X - green = Y  - blue = Z
	axes = new THREE.AxisHelper(400);
	scene.add( axes );
	console.log(CENTROID[0], CENTROID[1])
	axes.position.x = CENTROID[0]
	axes.position.y = CENTROID[1]
	//axes.position = new THREE.Vector3(CENTROID[0], CENTROID[1], 0);
	console.log("Helper Axes Position : " , axes.position);


	//Controls
	controls = new THREE.TrackballControls( camera, renderer.domElement );
	controls.rotateSpeed = 0.6;
	controls.zoomSpeed = 1.0;
	controls.panSpeed = 1.0;
	controls.noZoom = false;
	controls.noPan = false;
	controls.staticMoving = true;
	controls.dynamicDampingFactor = 0.3;
	controls.minDistance = 50;
	controls.maxDistance = 8000;
	controls.keys = [ 65, 83, 68 ];
	controls.target = new THREE.Vector3(CENTROID[0], CENTROID[1], 0)
	controls.addEventListener( 'change', render );


	// pointer lock interaction
//	raycaster = new THREE.Raycaster( new THREE.Vector3(), new THREE.Vector3( CENTROID[0], CENTROID[1], 20 ), 0, 10 );
//	controls2 = new THREE.ArrowControls( camera );
//	scene.add( controls2.getObject() );
//	setupArrowControls();
//	animate2();


clock = new THREE.Clock();

/*   flyControls = new THREE.FlyControls(camera,renderer.domElement );
   flyControls.movementSpeed = 25;
   //flyControls.domElement = renderer.domElement;
   flyControls.rollSpeed = Math.PI/24;
	flyControls.minDistance = 50;
	flyControls.maxDistance = 8000;
   flyControls.autoForward = false;
   flyControls.target = new THREE.Vector3(CENTROID[0], CENTROID[1], 0)
   flyControls.dragToLook = false;
*/
/*	camControls = new THREE.FirstPersonControls(camera);
	camControls.lookSpeed = 0.4;
	camControls.movementSpeed = 20;
	camControls.noFly = false;
	camControls.lookVertical = true;
	camControls.constrainVertical = false;
	camControls.verticalMin = 1.0;
	camControls.verticalMax = 2.0;
	camControls.lon = CENTROID[1];
	camControls.lat = CENTROID[0];
	*/
	// Add base geometry
	//scene.add(baseMesh);

	// Detect Mouse Movement
	//container.addEventListener( 'mousemove', onMouseMove, false );
	//container.addEventListener( 'click', onMouseMove, false );

	// Resize detector
	window.addEventListener( 'resize', onWindowResize, false );

	// Scene Objects (3D Data)

	// SEE EPSILON !

	// http://threejs.org/examples/webgl_geometry_terrain_raycast.html
	// Raycast Helper
	var helpergeometry = new THREE.CylinderGeometry( 0, 2, 4, 3 ); // radius at top, radius at bottom, height, segments
	helpergeometry.applyMatrix( new THREE.Matrix4().makeTranslation( 0, 0, 0 ) );
	//helpergeometry.applyMatrix( new THREE.Matrix4().makeRotationX( Math.PI / 2 ) );
	helper = new THREE.Mesh( helpergeometry, new THREE.MeshBasicMaterial({ color: 0xEB1515, reflectivity: 0, wireframe: false }) );
	helper.name = "Helper"
	helpertoggle = true;


	/*var pitchObject = new THREE.Object3D();
	pitchObject.add( camera );
	var yawObject = new THREE.Object3D();
	yawObject.rotateZ = 90/180*Math.PI;
	yawObject.add( pitchObject );
*/


	// Lighting
	var ambientLight = new THREE.AmbientLight(0xE0E0E0, 0.05);
	var directionalLight1 = new THREE.DirectionalLight( 0xfffaad, 0.6);
	directionalLight1.shadowDarkness = 1
	directionalLight1.position.set( 0, 1, 0 );
	// var directionalLight2 = new THREE.DirectionalLight( 0xffffff, 0.6);
	// directionalLight2.shadowDarkness = 1
	// directionalLight2.position.set( 0, 1, 0 );

	//hemi = new THREE.HemisphereLight(0x94E1FF, 0x852D10, 0.7)
	//hemi.position.set( 0, 1, 0 );
	scene.add( directionalLight1 );
	//Create Scene
	scene.add( ambientLight );
	//scene.add(hemi)
	scene.add( helper );


	addedToScene = []
	visibleBools = []

	jsLayerList.forEach( function(jsLayer) {
		console.log(jsLayer);

		visibleBools.push(false);

		$("#".concat(jsLayer)).on('click', function() {

			if (visibleBools[jsLayerList.indexOf(jsLayer)] === false) {
				layerName = '"'.concat(jsLayer).concat('"');
				// if there is no data in the layer, then loada the layer data
				if ($.inArray(jsLayer, addedToScene) === -1) {
					console.log("Loading from PG")
					//loadLayer(layerName);
					loadLayer(jsLayer);
					addedToScene.push(jsLayer);
				}
				else {
					// switch the layer on
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
			else if (visibleBools[jsLayerList.indexOf(jsLayer)] === true) {
				// hide the layer data
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


}

				// MULTISELECT
				var marquee = $("#select-marquee")
				var offset = {};
				var keyIsPressed = false
				var firstKeyPress = false
				var keyPressedCoords = {x: 0, y: 0};
				var canvasLeftOffset = $( "#info" ).width();
				var canvasTopOffset = $( "#topbar").height();
				var canvasWidth = window.innerWidth -  canvasLeftOffset;
				var	canvasHeight = window.innerHeight - canvasTopOffset;
				// CLICK HANDLER
				var firstClick = true
				var objectFirstClick = true
				var p1
				var p2
				var l1
				var l2

function setupEvents(){

				$('#container').click( function() {
					console.log("container click");
					processClick();
				});

				// set a flag to test whether the mouse is over the actual map
				$("#container").mouseenter(function(){
				    overMap = true;
				}).mouseleave(function(){
				    overMap = false;
				});


				$(document).keydown( function(event) {
					console.log("document keydown");
					camControls.onKeyDown(event);
					if ((MULTISELECT == true) && (SELECT == false)) {
						code = event.keyCode || event.which;
						keyIsM = ((String.fromCharCode(code) == "m") ||  (String.fromCharCode(code) == "M" ))
						if (keyIsM === true) {
							if ((keyPressedCoords.x === 0) && (keyPressedCoords.y === 0))  {
								firstKeyPress = true
								marquee.fadeIn();
							}
							keyIsPressed = true
						}
					}
					// as keydown event doesn't work for divs in firefox, before we fire the zoom/pan event we need to check where
					// the mouse is
					if (overMap === true) {
						console.log("key clicked over the map");
					//	controls2.onKeyDownPointerLock(event);
					}
				});

				$("#container").mousemove(function(event){
					console.log("container mousemoved");
					// dont use the move 'look around' as there is no way to stop the movement
					//camControls.onMouseMove(event);
					if ((keyIsPressed === true) && ((MULTISELECT === true) && (SELECT === false)) ) {
						if ((keyPressedCoords.x === 0) && (keyPressedCoords.y === 0)) {
							//console.log("setting initial coords");
							keyPressedCoords.x = event.clientX;
							keyPressedCoords.y = event.clientY;
							firstKeyPress = false
						}
						//console.log(mousedowncoords.x, mousedowncoords.y);

						//console.log("mouseover");
						var pos = {};
						//console.log(keyPressedCoords.x);
						pos.x = event.clientX - keyPressedCoords.x;
						pos.y = event.clientY - keyPressedCoords.y;
						//console.log(pos.x, pos.y);
						// square variations
						// (0,0) origin is the TOP LEFT pixel of the canvas.
						//
						//  1 | 2
						// ---.---
						//  4 | 3
						// there are 4 ways a square can be gestured onto the screen.  the following detects these four variations
						// and creates/updates the CSS to draw the square on the screen
						if (pos.x < 0 && pos.y < 0) {
							marquee.css({left: event.clientX + 'px', width: -pos.x + 'px', top: event.clientY + 'px', height: -pos.y + 'px'});
						} else if ( pos.x >= 0 && pos.y <= 0) {
							marquee.css({left: keyPressedCoords.x + 'px',width: pos.x + 'px', top: event.clientY, height: -pos.y + 'px'});
						} else if (pos.x >= 0 && pos.y >= 0) {
							marquee.css({left: keyPressedCoords.x + 'px', width: pos.x + 'px', height: pos.y + 'px', top: keyPressedCoords.y + 'px'});
						} else if (pos.x < 0 && pos.y >= 0) {
							marquee.css({left: event.clientX + 'px', width: -pos.x + 'px', height: pos.y + 'px', top: keyPressedCoords.y + 'px'});
						}
					}
					//controls2.onMouseMovePointerLock(event);

			});
			$(document).on('keyup', function(e) {
				console.log("document keyup");
				camControls.onKeyUp(e);
				//close marquee
				if (MULTISELECT === true) {
					make_multi_selection();
					//console.log("key let go");
					keyIsPressed = false
					keyPressedCoords = {x: 0, y: 0};
					marquee.fadeOut();
					marquee.css({width: 0, height: 0});
					selectcoords = {};
				}
				if (overMap === true) {
						console.log("key up over the map");
					//	controls2.onKeyUpPointerLock(e);
				}
			});
			$(document).on('keypress', function (e) {
				console.log("document keypress");
				//console.log(e);
				var code = e.keyCode || e.which;
				//console.log(code);
				keyIsM = ((String.fromCharCode(code) == "m") ||  (String.fromCharCode(code) == "M" ))
				objectIsIntersected = ((intersectedObject !== "" ) || ( intersectedMesh !== "" ))

				if ( SELECT && keyIsM && objectIsIntersected ){
					aObject3D = ((intersectedObject instanceof THREE.Object3D === true ) && intersectedObject instanceof THREE.Scene === false && intersectedMesh instanceof THREE.AxisHelper === false );
					aMeshObject = ((intersectedMesh instanceof THREE.Mesh === true ) && (intersectedObject instanceof THREE.Scene === true ));
					aLineObject = (intersectedMesh instanceof THREE.Line === true && intersectedMesh instanceof THREE.AxisHelper === false );
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
								console.log(intersectedObject);
								colorArray = [];

								intersectedObject.children.forEach( function(child, childIndex) {
									if (childIndex === 0) {
										objectColour = child.material.color.clone();
									}
									child.material.color.setHex( 0xCCCCCC )
									if (child.material.hasOwnProperty("ambient")) { child.material.ambient.setHex( 0xCCCCCC ) }
								});
								console.log("adding ", intersectedObject, " to SELECTED");
								SELECTED.sceneobject.push(intersectedObject)
								SELECTED.color.push(objectColour)
								console.log(SELECTED);

							}

							else if ((aLineObject === true || aMeshObject === true) && (intersectedMesh.name != "Helper")) {
								console.log("adding ", intersectedMesh, " to SELECTED");
								SELECTED.sceneobject.push(intersectedMesh)
								SELECTED.color.push(intersectedMesh.material.color.clone())
								intersectedMesh.material.color.setHex( 0xCCCCCC )
								if (intersectedMesh.material.hasOwnProperty("ambient")) { intersectedMesh.material.ambient.setHex( 0xCCCCCC ) }

							}
						}

						// If object has been selected before
						if (inSelected  != -1) {

							if (aObject3D === true) {
								intersectedObject.children.forEach( function(child, colIndex) {
									console.log(SELECTED.color[inSelected])
									child.material.color.set( SELECTED.color[inSelected] )
									if (child.material.hasOwnProperty("ambient")) { child.material.ambient.setHex( SELECTED.color[inSelected] ) }

								});
								//console.log("removing ", intersectedObject, " to SELECTED");
								SELECTED.sceneobject.splice(inSelected, 1);
								//console.log(SELECTED.sceneobject);
								SELECTED.color.splice(inSelected, 1);
							}

							else if ((aLineObject === true || aMeshObject === true) && (intersectedMesh.name != "Helper")) {

								//console.log("remove color",
								intersectedMesh.material.color.set( SELECTED.color[inSelected] )
								if (intersectedMesh.material.hasOwnProperty("ambient")) { intersectedMesh.material.ambient = SELECTED.color[inSelected] }
								SELECTED.sceneobject.splice(inSelected, 1);
								SELECTED.color.splice(inSelected, 1);

							}
						}
					}
				}
			});

			$('#loadselected').click( function() {
				if ((SELECT === true || MULTISELECT === true) && (SELECTED.sceneobject.length != 0)) {
					getattributes()
				}
			});


}