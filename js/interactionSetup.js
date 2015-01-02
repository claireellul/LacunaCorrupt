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

				var delta = clock.getDelta();
				//flyControls.update(delta);
				//camControls.update(delta);
				//camControls.update(0);
				stats.update();
				render();

//				console.log(camera.near);
//				console.log(camera.far);



				var frustum = new THREE.Frustum();
				var cameraViewProjectionMatrix = new THREE.Matrix4();
				camera.updateMatrixWorld(); // make sure the camera matrix is updated

				camera.matrixWorldInverse.getInverse( camera.matrixWorld );
				cameraViewProjectionMatrix.multiplyMatrices( camera.projectionMatrix, camera.matrixWorldInverse );
				frustum.setFromMatrix( cameraViewProjectionMatrix );

				// frustum is now ready to check all the objects you need
//				console.log( JSON.stringify(camera.matrixWorld));
//				console.log( JSON.stringify(camera.matrixWorldInverse));
//				console.log( JSON.stringify(camera.projectionMatrix));
//				console.log( JSON.stringify(frustum));


			}

			function render() {

				renderer.render( scene, camera );
				stats.update();

			}
			/*function onMouseMove( event ) {

					//scene.updateMatrixWorld();
					cX = (event.clientX - $( "#info" ).width()) //+ 2;
					cY = (event.clientY - $( "#topbar").height())// + 2 ;
					//console.log(cX, cY);

					var mouseX = ( cX  / canvaswidth  ) * 2 - 1;
					var mouseY = -( cY / canvasheight ) * 2 + 1;
					//console.log("mouse", mouseX, mouseY)
					var vector = new THREE.Vector3( mouseX, mouseY, camera.near );

					// Convert the [-1, 1] screen coordinate into a world coordinate on the near plane
					var projector = new THREE.Projector();
					projector.unprojectVector( vector, camera );

					var raycaster = new THREE.Raycaster( camera.position, vector.sub( camera.position ).normalize() );
					raycaster.precision = 25
					raycaster.linePrecision = 15

					scene.updateMatrixWorld();
					var intersects = raycaster.intersectObjects( scene.children, true );

					//console.log(intersects)
					helper.position.set( 0, 0, 0 );
					if ( intersects.length > 0 ) {
						//console.log(intersects[0].object instanceof THREE.Line)
						if (((intersects[0].face != null) || (intersects[0].object instanceof THREE.Line)) && (intersects[0].object.name != "Helper") && (intersects[0].object.parent.name != "Helper") && (intersects[0].object.visible === true)) {
							//console.log("Intersection ", intersects.length, intersects[0]);
								intersectedObject = intersects[0].object.parent
								intersectedMesh = intersects[0].object
								intersectedPoint = intersects[ 0 ].point
								//console.log(intersectedMesh, intersectedObject);
								if (intersects[ 0 ].face != null) {
									if ( helpertoggle == true ) {
									helper.lookAt( intersects[ 0 ].face.normal );
									helper.position.copy( intersects[ 0 ].point );
									}
								}
								//console.log(String(intersects[ 0 ].point.x));
								$('#xcoord').html(String(intersects[ 0 ].point.x));
								$('#ycoord').html(String(intersects[ 0 ].point.y));
								$('#zcoord').html(String(intersects[ 0 ].point.z));

						}
						// If first object is axishelper, and the multiple objects are intersected
						if ((intersects[0].object instanceof THREE.AxisHelper === true) && (intersects.length > 1) && (intersects[1].face != null) && (intersects[1].object.visible === true)) {
								//console.log("Axis is first object");
								if ( helpertoggle == true ) {
									helper.lookAt( intersects[ 1 ].face.normal );
									helper.position.copy( intersects[ 1 ].point );
								}
								//console.log(String(intersects[ 0 ].point.x));
								$('#xcoord').html(String(intersects[ 1 ].point.x));
								$('#ycoord').html(String(intersects[ 1 ].point.y));
								$('#zcoord').html(String(intersects[ 1 ].point.z));
						}
					}
					else {
						intersectedObject = ""
						intersectedMesh = ""
						intersectedPoint = ""
						$('#xcoord').html("Unknown");
						$('#ycoord').html("Unknown");
						$('#zcoord').html("Unknown");
					}


			}
*/