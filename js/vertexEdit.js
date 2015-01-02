	// VERTEX LEVEL EDITING

	//
	var originalModel

	function wireframeObject(o, wfbool) {
		if (o.hasOwnProperty('material')) {
			o.material.wireframe = wfbool;
		}
		else if (o.hasOwnProperty('children') && o.children.length != 0) {
			o.children.forEach ( function(objectchild) {
				if (objectchild.hasOwnProperty('material')) {
					objectchild.material.wireframe = wfbool;
				}
			});
		}
	}

	function closestVertex(point, object) {
		//console.log(point);
		// Point is Vector3
		var dist = -1
		var closest
		var vertexmesh

		// Object3D
		if (object.hasOwnProperty("children") && object.children.length != 0 ) {
			object.children.forEach( function(child) {
				child.geometry.vertices.forEach( function(v) {
					//console.log(point.distanceTo(v))
					if (dist === -1) {
						dist = point.distanceTo(v)
						closest = v
						vertexmesh = child
					}
					else if (point.distanceTo(v) < dist) {
						dist = point.distanceTo(v)
						closest = v
						vertexmesh = child
					}
				});
			});

			//return [closest, vertexmesh]
		}

		// Line or Mesh
		else if (object.hasOwnProperty("children") == false || (object.hasOwnProperty("children") && object.children.length == 0)) {
			console.log("Its a line?")
			object.geometry.vertices.forEach( function(v) {
				if (dist === -1) {
					dist = point.distanceTo(v)
					closest = v
					vertexmesh = object
				}
				else if (point.distanceTo(v) < dist) {
					dist = point.distanceTo(v)
					closest = v
					vertexmesh = object
				}
			});
		}

		moveVertexs = []
		vertexMeshs = []
		if (object.hasOwnProperty("children") && object.children.length != 0 ) {
			object.children.forEach( function(child) {
				//console.log(child)
				child.geometry.vertices.forEach( function(v) {
					//console.log(v, closest)
					if ( closest.distanceTo(v) <= (EPSILON * 3) ) {
						moveVertexs.push(v)
						console.log("ADDED", v);
						if (vertexMeshs.indexOf(child) === -1) { vertexMeshs.push(child) }
					}
				});
			});
		}
		else if (object.hasOwnProperty("children") == false || (object.hasOwnProperty("children") && object.children.length == 0)) {
			object.geometry.vertices.forEach( function(v) {
				if ( closest.distanceTo(v) <= (EPSILON * 3) ) {
					moveVertexs.push(v)
					console.log("ADDED", v);
					if (vertexMeshs.indexOf(object) === -1) { vertexMeshs.push(object) }
				}
			});
		}


		console.log("vertexs", moveVertexs, "meshes", vertexMeshs)
		return [moveVertexs, vertexMeshs]
	}

	function vertexEditing() {

		if ( VERTEX && (VERTEX_EDIT == false)) {
			console.log("Vertex Editing Running")
			console.log("vertexMesh", vertexMesh)
			console.log("vertexObject", vertexObject)
			editedPoint = ""
			editedModel = ""
			originalMeshPos = ""
			vPoint = (vertexMesh.hasOwnProperty("geometry") && vertexMesh.geometry instanceof THREE.SphereGeometry && vertexMesh.name != "Helper" )
			vObject3D = ( vertexObject instanceof THREE.Object3D === true  && vertexObject instanceof THREE.Scene === false && vertexMesh instanceof THREE.Line == false )
			vLine = ( vertexMesh instanceof THREE.Line === true && vertexMesh.parent instanceof THREE.Scene === true  );
			vPolygon = ( vertexMesh instanceof THREE.Mesh && vertexMesh.parent instanceof THREE.Scene === true && vertexMesh.geometry instanceof THREE.SphereGeometry === false && vertexMesh.name != "Helper" )

			if (vPoint) { vertexModel = vertexMesh }
			else if (vObject3D) { vertexModel = vertexObject  }
			else if (vLine) { vertexModel = vertexMesh }
			else if (vPolygon) { vertexModel = vertexMesh  }
			ACTION = true;

			if ( vertexModel != "" && (vPoint || vObject3D || vLine || vPolygon) && vertexModel instanceof THREE.AxisHelper === false)  {
				GEOMCLICKED = true
				console.log("Vertexmodel not equal to empty string", vertexModel);
				cloneGeometry = []

				VERTEX = false;
				wireframeObject(vertexModel, true)
				lookAtPosition(getObjectCenter(vertexModel).x , getObjectCenter(vertexModel).y, getObjectCenter(vertexModel).z  )
				controls.panSpeed = 0.0
				helper.visible = false;
				if (scene.children.indexOf(vertexHelper) === -1) {
					scene.add(vertexHelper)
				}
				VERTEX_EDIT = true
			}
		}
	 else {
		$("#dialogtext").text("Other function in operation, or nothing selected!");
		$("#dialog").dialog({ resizable: false, buttons: { OK: function () { $(this).dialog("close") } } });

	 }
	}

	vertButtons = ['#xvertplus', '#xvertminus', '#yvertplus', '#yvertminus', '#zvertplus', '#zvertminus']

	function vertexPicking() {
		console.log("Vertex Picking Running")

		if ( VERTEX_EDIT && vertexPoint && vPoint && (editedPoint == "" || vertexMesh == editedPoint) ) {
			// IS A POINT
			editingInProgress = true
			originalMeshPos = vertexMesh.position.clone()
			console.log("original mesh", originalMeshPos)
			pointMesh = vertexMesh
			$("#dialogtext").html('X: <div id="xvertplus" ></div> <input id="xvert" style="width: 100px; text-align: center; "> <div id="xvertminus" style="width: 36px"></div> <br><br> Y: <div id="yvertplus"></div> <input id="yvert" style="width: 100px; text-align: center;"> <div id="yvertminus" style="width: 36px "></div><br><br> Z: <div id="zvertplus"></div> <input id="zvert" style="width: 100px; text-align: center;"> <div id="zvertminus" style="width: 36px "></div>'  )
			$( "#xvertplus" ).button( {label: "+", text: true} )
			$( "#xvertminus" ).button( {label: "-", text: true} )
			$( "#yvertplus" ).button( {label: "+", text: true} )
			$( "#yvertminus" ).button( {label: "-", text: true} )
			$( "#zvertplus" ).button( {label: "+", text: true} )
			$( "#zvertminus" ).button( {label: "-", text: true} )

			$("#xvert").val(parseFloat(pointMesh.position.x))
			$("#yvert").val(parseFloat(pointMesh.position.y))
			$("#zvert").val(parseFloat(pointMesh.position.z))
			dims = ["x", "y", "z"]

			dims.forEach( function(d) {
				//console.log(vertexMesh.position, vertexMesh.position.x)
				$("#" + d + "vertplus").click( function() {
					if (d == "x") { pointMesh.position.x = parseFloat(pointMesh.position.x) + 1; $("#xvert").val(parseFloat(pointMesh.position.x)) }
					if (d == "y") { pointMesh.position.y = parseFloat(pointMesh.position.y) + 1; $("#yvert").val(parseFloat(pointMesh.position.y)) }
					if (d == "z") { pointMesh.position.z = parseFloat(pointMesh.position.z) + 1; $("#zvert").val(parseFloat(pointMesh.position.z)) }
					lookAtPosition(pointMesh.position.x, pointMesh.position.y, pointMesh.position.z )
				});

				$("#" + d + "vertminus").click( function(){
					if (d == "x") { pointMesh.position.x = parseFloat(pointMesh.position.x) - 1; $("#xvert").val(parseFloat(pointMesh.position.x)) }
					if (d == "y") { pointMesh.position.y = parseFloat(pointMesh.position.y) - 1; $("#yvert").val(parseFloat(pointMesh.position.y)) }
					if (d == "z") { pointMesh.position.z = parseFloat(pointMesh.position.z) - 1; $("#zvert").val(parseFloat(pointMesh.position.z)) }
					lookAtPosition(pointMesh.position.x, pointMesh.position.y, pointMesh.position.z )
				});
			});

			editedPoint = vertexMesh
		}

		else if ( VERTEX_EDIT && vertexPoint && ( vObject3D || vLine || vPolygon ) && ( editedModel == "" || ( vertexMesh == editedModel || vertexObject == editedModel ) ) )  {
			// A 3D Object
			if (vObject3D) {
				picked = closestVertex(vertexPoint, vertexObject)
				if (vertexObject.hasOwnProperty("children") && vertexObject.children.length != 0) {
					vertexObject.children.forEach( function(m) {
						cloneGeometry.push(m.geometry.clone())
					});
				}
			}
			else if (vLine || vPolygon) {
				picked = closestVertex(vertexPoint, vertexMesh)
				cloneGeometry = [vertexMesh.geometry.clone()]
			}

			console.log(picked)
			pickedVertices = picked[0]
			representVertex = pickedVertices[0]
			pickedMesh = picked[1]

			vertexHelper.position = representVertex.clone();
			originalPos = representVertex.clone();

			$("#dialogtext").html('X: <div id="xvertplus" ></div> <input id="xvert" style="width: 100px; text-align: center; "> <div id="xvertminus" style="width: 36px"></div> <br><br> Y: <div id="yvertplus"></div> <input id="yvert" style="width: 100px; text-align: center;"> <div id="yvertminus" style="width: 36px "></div><br><br> Z: <div id="zvertplus"></div> <input id="zvert" style="width: 100px; text-align: center;"> <div id="zvertminus" style="width: 36px "></div>'  )
			$( "#xvertplus" ).button( {label: "+", text: true} )
			$( "#xvertminus" ).button( {label: "-", text: true} )
			$( "#yvertplus" ).button( {label: "+", text: true} )
			$( "#yvertminus" ).button( {label: "-", text: true} )
			$( "#zvertplus" ).button( {label: "+", text: true} )
			$( "#zvertminus" ).button( {label: "-", text: true} )
			$("#xvert").val(representVertex.x)
			$("#yvert").val(representVertex.y)
			$("#zvert").val(representVertex.z)

			dims = ["x", "y", "z"]
			dims.forEach( function(d) {
				$("#" + d + "vertplus").click( function() {
					dim = $("#" + d + "vert")
					dim.val( parseFloat( dim.val() ) + 1 );
					pickedVertices.forEach( function(v) {
						if (d == "x") { v.x = parseFloat(dim.val()) }
						if (d == "y") { v.y = parseFloat(dim.val()) }
						if (d == "z") { v.z = parseFloat(dim.val()) }
					});

					if (vObject3D) {
						vertexObject.children.forEach( function(m) {
							m.geometry.dynamic = true;
							m.geometry.verticesNeedUpdate = true;
							m.geometry.elementsNeedUpdate = true;
							m.geometry.computeFaceNormals()

						});
					}
					else { vertexMesh.geometry.verticesNeedUpdate = true }
					//vertexObject.material = new THREE.MeshBasicMaterial( {color: 0xCCCCCC, ambient: 0xCCCCCC} );

					vertexHelper.position.copy(pickedVertices[0].clone())

					if (vertexObject.hasOwnProperty("children")) {
						vertexObject.children.forEach( function(o) {
							if ( typeof(o.geometry) != "undefined" ) {
								o.geometry.applyMatrix( new THREE.Matrix4().makeTranslation(0, 0, 0) )
								o.geometry.verticesNeedUpdate = true;
							}
						});
					}
					vertexObject.matrixWorldNeedsUpdate = true
				});

				$("#" + d + "vertminus").click( function(){
					dim = $("#" + d + "vert")
					dim.val( parseFloat( dim.val()) - 1 );
					pickedVertices.forEach( function(v) {
						if (d == "x") { v.x = parseFloat(dim.val()) }
						if (d == "y") { v.y = parseFloat(dim.val()) }
						if (d == "z") { v.z = parseFloat(dim.val()) }
					});

					if (vObject3D) {
						vertexObject.children.forEach( function(m) {
							m.geometry.dynamic = true;
							m.geometry.verticesNeedUpdate = true;
							m.geometry.elementsNeedUpdate = true;
							m.geometry.computeFaceNormals()
						});
					}
					else { vertexMesh.geometry.verticesNeedUpdate = true }

					scene.updateMatrixWorld();
					vertexHelper.position.copy(pickedVertices[0].clone())
					if (vertexObject.hasOwnProperty("children")) {
						vertexObject.children.forEach( function(o) {
							if ( typeof(o.geometry) != "undefined" ) {
								o.geometry.applyMatrix( new THREE.Matrix4().makeTranslation(0, 0, 0) )
								o.geometry.verticesNeedUpdate = true;
								//o.geometry.elementsNeedUpdate = true;
							}
						});
					}
					vertexObject.matrixWorldNeedsUpdate = true

				});

			});
			if (vObject3D) { editedModel = vertexObject }
			if (vPolygon) { editedModel = vertexMesh }
			if (vLine) { editedModel = vertexMesh }
		}

			$("#dialog").dialog({
				resizable: false,
				buttons: {
					Accept: function () {
						//console.log("Commiting Edit!");
						if (vPoint && originalMeshPos != "") { cancelVertexEditing() }
						$(this).dialog("close");
					},
					Cancel: function () {

						if (vPoint && originalMeshPos != "") { pointMesh.position = originalMeshPos; cancelVertexEditing() }
						if ((vObject3D || vLine || vPolygon) && originalPos != "") {
							console.log("being called")
							console.log(pickedVertices);
							pickedVertices.forEach( function(v) {
								v.x = originalPos.x
								v.y = originalPos.y
								v.z = originalPos.z

							});
							if (vObject3D) {
								vertexObject.children.forEach( function(m) {
									m.geometry.verticesNeedUpdate = true;
									m.geometry.elementsNeedUpdate = true;
									m.geometry.computeFaceNormals()
								});
							}
							else { vertexMesh.geometry.verticesNeedUpdate = true }
							vertexHelper.position = originalPos

						}
						$(this).dialog("close");
					}
				}
			});
			$('#dialog').dialog('option', 'title', 'Vertex Editing');

		}

			function cancelVertexEditing() {
				GEOMCLICKED = false
				VERTEX = false
				VERTEX_EDIT = false
				ACTION = false
				editInProgress = false
				if (typeof vLine != 'undefined') {
					if (vertexModel != "" && vLine == false ) { wireframeObject(vertexModel, false) }
				}
				vertexHelper.position.x = 0
				vertexHelper.position.y = 0
				vertexHelper.position.z = 0
				$('#mode').text("Visualise");
				$('#verteximage').attr("src", "imgs/vertexedit.png");
				helper.visible = true;
				controls.panSpeed = 1.0;
				//$("#dialog").dialog("close")
			}
