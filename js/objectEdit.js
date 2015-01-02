	//OBJECT LEVEL EDITING
	function getObjectCenter(o) {
		return new THREE.Box3().setFromObject(o).center()
	}

		function replaceGeomPG(name, id, geom) {
			//console.log("replacing geom")
			//console.log(name, id, geom);
			$.ajax({
				  url: './ajax/updategeom.php',
				  type: 'post',
				  dataType: "json",
				  timeout: 1200000,
				  data: {'layer': name, 'id': id, 'geom': geom },
				  async: false,
				  success: function(data) {
					console.log("update geom php working")
					}
				 });
		}


	function resetAdjustments(o) {
		o.position.x = 0
		o.position.y = 0
		o.position.z = 0
		o.rotation.x = 0
		o.rotation.y = 0
		o.rotation.z = 0
		o.scale.x = 1
		o.scale.y = 1
		o.scale.z = 1
		o.geometry.applyMatrix( new THREE.Matrix4().makeTranslation(0, 0, 0) )
	}

	function nudgeToCenter(obj) {
		c = getObjectCenter(obj)
		console.log(c)
		obj.geometry.vertices.forEach( function(v) {
			if (c.x > 0) { v.x -= c.x }
			else if (c.x < 0) { v.x += Math.abs(c.x)}
			if (c.y > 0) { v.y -= c.y }
			else if (c.y < 0) { v.y += Math.abs(c.y)}
			if (c.z > 0) { v.z -= c.z }
			else if (c.z < 0) { v.z += Math.abs(c.z)}
			obj.geometry.verticesNeedUpdate = true
		});
	}

	function objectToWorld(obj) {
		obj.geometry.vertices.forEach( function(v) {
			vector = v.clone();
			vector = obj.localToWorld(vector)
			v.x = vector.x
			v.y = vector.y
			v.z = vector.z
		})
		obj.geometry.verticesNeedUpdate = true
		obj.updateMatrixWorld();
	}

	var rotObjectMatrix;

	function rotateAroundObjectAxis(object, axis, radians) {
		rotObjectMatrix = new THREE.Matrix4();
		rotObjectMatrix.makeRotationAxis(axis.normalize(), radians);
		object.matrix.multiply(rotObjectMatrix);

		object.rotation.setFromRotationMatrix(object.matrix)
	}

	var rotWorldMatrix;
	// Rotate an object around an arbitrary axis in world space
	function rotateAroundWorldAxis(object, axis, radians) {
		rotWorldMatrix = new THREE.Matrix4();
		rotWorldMatrix.makeRotationAxis(axis.normalize(), radians);
		rotWorldMatrix.multiply(object.matrix);                // pre-multiply
		object.matrix = rotWorldMatrix;
		object.rotation.setFromRotationMatrix(object.matrix);
	}



	function edit_delete() {
		objectsToDelete = [];
		deleteTables = [];
		SELECTED.sceneobject.forEach( function(so, selectIndex) {
			if ((so.hasOwnProperty('name')) && ((so.name != "") || (so.name != "Helper"))) {
				objectName = so.name
				objectParts = objectName.split(" "); // Split name into TABLE and ID NUMBER [ 'Bridges', '2' ]
				if (deleteTables.indexOf(objectParts[0]) === -1) {
					deleteTables.push(objectParts[0])
					objectsToDelete.push([])
				}
					//console.log(deleteTables.indexOf(objectParts[0]));
				tableNum = deleteTables.indexOf(objectParts[0])
				objectsToDelete[tableNum].push(objectParts[1])
			}
		});

		// Remove from three.js
		SELECTED.sceneobject.forEach( function(s, i) {
			scene.remove(s)
		});

		SELECTED.sceneobject = [];
		SELECTED.colors = [];

		$.ajax({
		  url: './ajax/delete.php',
		  type: 'POST',
		  timeout: 60000,
		  data: {'deleteTables': deleteTables, 'attributesToDelete': objectsToDelete},
		  async: false,
		  success: function(data) {
			//alert( "Selection deleted!" )
			}
		})

	}

	function edit_translate(x, y, z) {

		objectsToTranslate = [];
		translateTables = [];
		SELECTED.sceneobject.forEach( function(so, selectIndex) {
			if ((so.hasOwnProperty('name')) && ((so.name != "") || (so.name != "Helper"))) {
				objectName = so.name
				objectParts = objectName.split(" "); // Split name into TABLE and ID NUMBER [ 'Bridges', '2' ]
				if (translateTables.indexOf(objectParts[0]) === -1) {
					translateTables.push(objectParts[0])
					objectsToTranslate.push([])
				}
					//console.log(deleteTables.indexOf(objectParts[0]));
				tableNum = translateTables.indexOf(objectParts[0])
				objectsToTranslate[tableNum].push(objectParts[1])
			}
		});

		SELECTED.sceneobject.forEach( function(s, i) {
			if (s.hasOwnProperty("children") && s.children.length != 0 ) {
				s.children.forEach( function(o) {
					if ( typeof(o.geometry) != "undefined" ) {
						o.geometry.applyMatrix( new THREE.Matrix4().makeTranslation(x, y, z) )
						o.geometry.verticesNeedUpdate = true;
						o.geometry.elementsNeedUpdate = true;
					}
				});
			}
			else {
				s.geometry.applyMatrix( new THREE.Matrix4().makeTranslation(x, y, z) )
				s.geometry.verticesNeedUpdate = true;
				s.geometry.elementsNeedUpdate = true;
			}
		});

		$.ajax({
		  url: './ajax/translate.php',
		  type: 'POST',
		  timeout: 60000,
		  data: { 'translateTables': translateTables, 'attributesToTranslate': objectsToTranslate, 'xyz': [x, y, z] },
		  async: false,
		  success: function(data) {
			alert( "Selection translated!" )
			}
		})


	}

	function edit_rotate(degrees, axis) {
		radians = degrees * (Math.PI / 180)

		SELECTED.sceneobject.forEach( function(o, i) {
			var name = o.name.split(" ")[0]
			var id = o.name.split(" ")[1]
			if ( o.hasOwnProperty("geometry") && o.geometry instanceof THREE.SphereGeometry === true ) { return }
			if ( o instanceof THREE.Line == true || ( o instanceof THREE.Mesh && o.geometry instanceof THREE.SphereGeometry === false )) {
				originalCenter = getObjectCenter(o)
				console.log("Original Center", originalCenter)
				cl = o.clone()
				cl.geometry.vertices.forEach( function(v) {
					v.x -= originalCenter.x
					v.y -= originalCenter.y
					v.z -= originalCenter.z
					cl.geometry.verticesNeedUpdate = true
				});
				console.log("new center", getObjectCenter(cl))
				nudgeToCenter(cl)

				console.log("post adjustment center", getObjectCenter(cl))
				if (axis == "x") { cl.rotateX( radians ) }
				if (axis == "y") { cl.rotateY( radians ) }
				if (axis == "z") { cl.rotateZ( radians ) }
				cl.position = originalCenter
				cl.geometry.vertices.forEach( function(v) {
					cl.updateMatrixWorld();
					vector = v.clone();
					vector = cl.localToWorld(vector)
					v.x = vector.x
					v.y = vector.y
					v.z = vector.z
				})

				resetAdjustments(cl)
				scene.add(cl)
				scene.remove(o)

				if (o instanceof THREE.Line == true) { geom = line_to_pg(cl) }
				else if (o instanceof THREE.Mesh && o.geometry instanceof THREE.SphereGeometry === false) { geom = polygon_to_pg(cl) }
				replaceGeomPG(name, id, geom)

				selectedPos = SELECTED.sceneobject.indexOf(o)
				if (selectedPos !== -1) {
					SELECTED.sceneobject[selectedPos] = cl;
				}

			}

			else {
				console.log("Rotation : ", o.rotation)
				console.log("The scale is", o.scale)
				originalRotation = o.rotation
				originalCenter = getObjectCenter(o)
				console.log("Center", originalCenter)

				holderGeometry = new THREE.Geometry() ;
				o.children.forEach( function(m, i) {
					THREE.GeometryUtils.merge(holderGeometry, m);
					if ( i === 1 ) {
						rotateMaterial = m.material
						objectColour = m.material.color.clone();
					}
				});

				THREE.GeometryUtils.center(holderGeometry)

				holderMesh = new THREE.Mesh(holderGeometry, rotateMaterial)
				nudgeToCenter(holderMesh)
				movedCenter = getObjectCenter(holderMesh)

				console.log("0,0,0 center: ", movedCenter)
				if (axis == "x") { holderMesh.rotateX( originalRotation.x + radians ) }
				if (axis == "y") { holderMesh.rotateY( originalRotation.y + radians ) }
				if (axis == "z") { holderMesh.rotateZ( originalRotation.z + radians ) }
				holderMesh.position = originalCenter

				finalMesh = holderMesh.clone() ;

				finalMesh.geometry.vertices.forEach( function(v) {
					finalMesh.updateMatrixWorld();
					vector = v.clone();
					vector = finalMesh.localToWorld(vector)
					//vector.localToWorld(localToWorld)
					//vector.applyMatrix4( finalMesh.matrixWorld );
					//console.log(vector)
					v.x = vector.x
					v.y = vector.y
					v.z = vector.z
					finalMesh.geometry.verticesNeedUpdate = true;
					//console.log(v)
				})

				resetAdjustments(finalMesh)
				geom = threejs_to_tin(finalMesh)
				holderObject = new THREE.Object3D();
				holderObject.add(finalMesh)
				holderObject.name = o.name
				scene.add(holderObject)
				scene.remove(o)
				replaceGeomPG(name, id, geom)

				selectedPos = SELECTED.sceneobject.indexOf(o)

				if (selectedPos !== -1) {
					SELECTED.sceneobject[selectedPos] = holderObject;
				}
			}
		});

	}

	function edit_scale(xs, ys, zs) {

		SELECTED.sceneobject.forEach( function(o, i) {
			var name = o.name.split(" ")[0]
			var id = o.name.split(" ")[1]

			if ( o.hasOwnProperty("geometry") && o.geometry instanceof THREE.SphereGeometry === true) { return }
			else if  ( o instanceof THREE.Line == true || (o instanceof THREE.Mesh && o.geometry instanceof THREE.SphereGeometry === false)) {
				console.log("is mesh or line")
				originalCenter = getObjectCenter(o)
				console.log("Original Center", originalCenter)
				cl = o.clone()
				cl.geometry.vertices.forEach( function(v) {
					v.x -= originalCenter.x
					v.y -= originalCenter.y
					v.z -= originalCenter.z
					cl.geometry.verticesNeedUpdate = true
				});
				//console.log("new center", getObjectCenter(cl))
				nudgeToCenter(cl)

				//console.log("post adjustment center", getObjectCenter(cl))
				cl.scale.x = xs
				cl.scale.y = ys
				cl.scale.z = zs
				cl.position = originalCenter
				cl.geometry.vertices.forEach( function(v) {
					cl.updateMatrixWorld();
					vector = v.clone();
					vector = cl.localToWorld(vector)
					v.x = vector.x
					v.y = vector.y
					v.z = vector.z
				})

				resetAdjustments(cl)
				cl.name = name
				scene.add(cl)
				scene.remove(o)

				if (o instanceof THREE.Line == true) { geom = line_to_pg(cl) }
				else if (o instanceof THREE.Mesh && o.geometry instanceof THREE.SphereGeometry === false) { geom = polygon_to_pg(cl) }
				replaceGeomPG(name, id, geom)

				selectedPos = SELECTED.sceneobject.indexOf(o)
				if (selectedPos !== -1) {
					SELECTED.sceneobject[selectedPos] = cl;
				}
			}

			else {
				console.log("Rotation : ", o.rotation)
				console.log("The scale is", o.scale)
				originalRotation = o.rotation
				originalCenter = getObjectCenter(o)
				console.log("Center", originalCenter)

				holderGeometry = new THREE.Geometry() ;

				o.children.forEach( function(m, i) {
					THREE.GeometryUtils.merge(holderGeometry, m);
					if ( i === 1 ) {
						rotateMaterial = m.material
						objectColour = m.material.color.clone();
					}
				});

				THREE.GeometryUtils.center(holderGeometry)

				holderMesh = new THREE.Mesh(holderGeometry, rotateMaterial)

				movedCenter = getObjectCenter(holderMesh)

				console.log("0,0,0 center: ", movedCenter)
				holderMesh.scale.x = parseFloat(xs)
				holderMesh.scale.y = parseFloat(ys)
				holderMesh.scale.z = parseFloat(zs)
				holderMesh.position = originalCenter
				finalMesh = holderMesh.clone() ;

				finalMesh.geometry.vertices.forEach( function(v) {
					finalMesh.updateMatrixWorld();
					vector = v.clone();
					vector = finalMesh.localToWorld(vector)
					//vector.localToWorld(localToWorld)
					//vector.applyMatrix4( finalMesh.matrixWorld );
					console.log(vector)
					v.x = vector.x
					v.y = vector.y
					v.z = vector.z
					finalMesh.geometry.verticesNeedUpdate = true;
					console.log(v)
				});
				finalMesh.updateMatrixWorld();
				finalMesh.geometry.verticesNeedUpdate = true;
				finalMesh.geometry.elementsNeedUpdate = true;

				resetAdjustments(finalMesh)
				geom = threejs_to_tin(holderMesh)
				holderObject = new THREE.Object3D();
				holderObject.add(finalMesh)
				holderObject.name = name

				console.log("new object scale", holderObject.scale)
				scene.add(holderObject)

				if (holderObject.hasOwnProperty("children")) { holderObject.children.forEach( function(o) {
						if ( typeof(o.geometry) != "undefined" ) {
							o.geometry.applyMatrix( new THREE.Matrix4().makeTranslation(0, 0, 0) )
							o.geometry.verticesNeedUpdate = true;
							//o.geometry.elementsNeedUpdate = true;
						}
					});
				}

				scene.remove(o)

				replaceGeomPG(name, id, geom)

				selectedPos = SELECTED.sceneobject.indexOf(o)
				if (selectedPos !== -1) {
					SELECTED.sceneobject[selectedPos] = holderObject;
				}
			}
		});

	}

	function edit_copy(x, y, z) {

		copyObject = SELECTED.sceneobject[0]
		copyColor = SELECTED.color[0]
		// IF LINE
		if ( copyObject instanceof THREE.Line == true ) {
			copy = new THREE.Line( copyObject.geometry.clone(), new THREE.LineBasicMaterial() )
			copy.material.color = copyColor
			originalCenter = getObjectCenter(copyObject)
			copy.geometry.vertices.forEach( function(v) {
				v.x -= originalCenter.x
				v.y -= originalCenter.y
				v.z -= originalCenter.z
			});
			copy.geometry.verticesNeedUpdate = true
			nudgeToCenter(copy)
			copy.position.x = x
			copy.position.y = y
			copy.position.z = z
			console.log("After positioning", getObjectCenter(copy))
			objectToWorld(copy)
			resetAdjustments(copy)
			scene.add(copy)
			console.log("end of copy", copy)
			// USE PHP TO ADD TO DATABASE
		}

		// IF POINT OR 3D
		else if ( copyObject.hasOwnProperty("geometry") && copyObject.geometry instanceof THREE.SphereGeometry === true && copyObject.name != "Helper") {
			copy = new THREE.Mesh( pointGeometry = new THREE.SphereGeometry( 3, 12, 12 ),  new THREE.MeshBasicMaterial({color: copyColor, ambient: copyColor})  );
			copy.position.x = x
			copy.position.y = y
			copy.position.z = z
			scene.add(copy)
		}

		// Mesh (POLYGON)
		else if ( copyObject instanceof THREE.Mesh && copyObject.geometry instanceof THREE.SphereGeometry === false ) {

			console.log("object is Polygon ZM")
			originalCenter = getObjectCenter(copyObject)
			copy = new THREE.Mesh( copyObject.geometry.clone(), new THREE.MeshBasicMaterial() )
			copy.material.color = copyColor
			copy.material.ambient = copyColor
			copy.geometry.vertices.forEach( function(v) {
				v.x -= originalCenter.x
				v.y -= originalCenter.y
				v.z -= originalCenter.z
			});
			copy.geometry.verticesNeedUpdate = true
			nudgeToCenter(copy)
			copy.position.x = x
			copy.position.y = y
			copy.position.z = z
			console.log("After positioning", getObjectCenter(copy))
			objectToWorld(copy)
			resetAdjustments(copy)
			scene.add(copy)
			console.log("end of copy", copy)

		}

		else {
			copy = copyObject.clone()
			console.log(" Other stuff happening!")
			holderGeometry = new THREE.Geometry() ;
			copy.children.forEach( function(m, i) {
				THREE.GeometryUtils.merge(holderGeometry, m);
				if ( i === 1 ) {
					copyMaterial = m.material
					objectColour = m.material.color.clone();
					}
			});

			THREE.GeometryUtils.center(holderGeometry)
			holderGeometry.dynamic = true;
			holderGeometry.vertices.forEach( function(v) {
				v.x = v.x + parseFloat(x)
				v.y = v.y + parseFloat(y)
				v.z = v.z + parseFloat(z)
				holderGeometry.verticesNeedUpdate = true
				console.log(v)
			});
			holderGeometry.verticesNeedUpdate = true
			holderMesh = new THREE.Mesh(holderGeometry, new THREE.MeshBasicMaterial( { color: copyColor, ambient: copyColor }) )
			copy = new THREE.Object3D();
			copy.add(holderMesh)
			copy.updateMatrixWorld();
			scene.add(copy)
		}

		console.log("Layer name", copyObject.name);
		console.log("copy", copy);
		layerName = copyObject.name.split(" ")[0]

		// PHP - IF A POINT
		if 	( copyObject.hasOwnProperty("geometry") && copyObject.geometry instanceof THREE.SphereGeometry === true && copyObject.name != "Helper") {
			copyGeom = point_to_pg(copy)
		}
		// PHP - IF LINE
		else if ( copyObject instanceof THREE.Line == true ) {
			copyGeom = line_to_pg(copy)
		}

		else if ( copyObject instanceof THREE.Mesh && copyObject instanceof THREE.SphereGeometry === false ) {
			copyGeom = polygon_to_pg(copy)
		}
		// PHP - ELSE IF A 3D OBJECT
		else {
			copyGeom = threejs_to_tin(copy)
		}

		//console.log(layerName, copyGeom)
		copyresponse = $.ajax({
							  url: './ajax/copy.php',
							  type: 'post',
							  dataType: "json",
							  timeout: 1200000,
							  data: {'layer': layerName, 'geom': copyGeom },
							  async: false,
							  success: function(data) {
								console.log("copy php working")
								}
							 }).responseJSON;

		copy.name = layerName + " " + String(copyresponse)
		// GET LARGEST ID NUMBER
		// SET ID number
		if (copy.hasOwnProperty("children") && copy.children.length != 0 ) { copy.children.forEach( function(o) {
			if ( typeof(o.geometry) != "undefined" ) {
				o.geometry.applyMatrix( new THREE.Matrix4().makeTranslation(0, 0, 0) )
				o.geometry.verticesNeedUpdate = true;
				//o.geometry.elementsNeedUpdate = true;
			}
		});
		}
	} // END OF COPY FUNCTION
