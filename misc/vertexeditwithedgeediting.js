
// CLONE OBJECT! SO IF CANCEL YOU CAN JUST REMOVE AND REPLACE
// SELECT AN OBJECT
// WIREFRAME
// LOOK AT / CAMERA
// DISABLE PANNING
// GET POINT 
// GET CLOSEST VERTEX
// LETS EDIT THAT VERTEX!

// SAVE OBJECT
	var originalModel;
	var vertexModel

	function wireframeObject(o, wfbool) {
		if (o.hasOwnProperty('material')) {
			o.material.wireframe = wfbool;
		}
		else if (o.hasOwnProperty('children')) {
			o.children.forEach ( function(objectchild) {
				if (objectchild.hasOwnProperty('material')) {
					objectchild.material.wireframe = wfbool;
				}
			});
		}
	}
	

	function closestVertex(point, object) {
		//console.log(point);
		var dist = -1
		var closest
		var vertexmesh
		
		// Point is Vector3
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
		else if (object.hasOwnProperty("children") == false) {
			object.geometry.vertices.forEach( function(v) {
				if (dist === -1) {
					dist = point.distanceTo(v)
					closest = v
					vertexmesh = child
				}
				else if (point.distanceTo(v) < dist)
					dist = point.distanceTo(v)
					closest = v
					vertexmesh = child
			});
			//return [closest, vertexmesh]
		}
		moveVertexs = []
		vertexMeshs = []
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
		console.log("vertexs", moveVertexs, "meshes", vertexMeshs)
		return [moveVertexs, vertexMeshs]
	}
	
	vertButtons = ['#xvertplus', '#xvertminus', '#yvertplus', '#yvertminus', '#zvertplus', '#zvertminus']
	
	function vertexPicking() {
		if (VERTEX_EDIT && vertexPoint) {
			picked = closestVertex(vertexPoint, vertexModel)
			//console.log(picked)
			pickedVertices = picked[0]
			pickedMesh = picked[1]
			originalPos = pickedVertices[0].clone();
			vertexHelper.position = pickedVertices[0].clone();
			$("#dialogtext").html('X: <div id="xvertplus" ></div> <input id="xvert" style="width: 100px; text-align: center; "> <div id="xvertminus" style="width: 36px"></div> <br><br> Y: <div id="yvertplus"></div> <input id="yvert" style="width: 100px; text-align: center;"> <div id="yvertminus" style="width: 36px "></div><br><br> Z: <div id="zvertplus"></div> <input id="zvert" style="width: 100px; text-align: center;"> <div id="zvertminus" style="width: 36px "></div>'  )
			$( "#xvertplus" ).button( {label: "+", text: true} )
			$( "#xvertminus" ).button( {label: "-", text: true} )
			$( "#yvertplus" ).button( {label: "+", text: true} )
			$( "#yvertminus" ).button( {label: "-", text: true} )
			$( "#zvertplus" ).button( {label: "+", text: true} )
			$( "#zvertminus" ).button( {label: "-", text: true} )
			$("#xvert").val(pickedVertices[0].x)
			$("#yvert").val(pickedVertices[0].y)
			$("#zvert").val(pickedVertices[0].z)
		
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
					
					vertexModel.children.forEach( function(m) {
						m.geometry.dynamic = true;
						//m.geometry.mergeVertices()
						//m.geometry.__dirtyVertices = true;
						
						m.geometry.verticesNeedUpdate = true;
						m.geometry.elementsNeedUpdate = true;
						m.geometry.computeFaceNormals()
						
					});
					//vertexModel.material = new THREE.MeshBasicMaterial( {color: 0xCCCCCC, ambient: 0xCCCCCC} );
					
					vertexHelper.position.copy(pickedVertices[0].clone())
					
					if (vertexModel.hasOwnProperty("children")) {
								vertexModel.children.forEach( function(o) { 
									if ( typeof(o.geometry) != "undefined" ) { 
										o.geometry.applyMatrix( new THREE.Matrix4().makeTranslation(0, 0, 0) )
										o.geometry.verticesNeedUpdate = true;
										//o.geometry.elementsNeedUpdate = true;
									}
								});
							}
					vertexModel.matrixWorldNeedsUpdate = true
				});
				
				$("#" + d + "vertminus").click( function(){
					dim = $("#" + d + "vert")
					dim.val( parseFloat( dim.val()) - 1 );
					pickedVertices.forEach( function(v) {
						if (d == "x") { v.x = parseFloat(dim.val()) }
						if (d == "y") { v.y = parseFloat(dim.val()) }
						if (d == "z") { v.z = parseFloat(dim.val()) }
					});
					//console.log(vertexModel);
					vertexModel.children.forEach( function(m) {
						//console.log(m);
						m.geometry.dynamic = true;
						//m.geometry.mergeVertices()
						//m.geometry.__dirtyVertices = true;
						m.geometry.verticesNeedUpdate = true;
						m.geometry.elementsNeedUpdate = true;
						m.geometry.computeFaceNormals()
						
						
						//console.log(getObjectCenter(m.parent))
					});
				
					
					//vertexModel.material = new THREE.MeshBasicMaterial( {color: 0xCCCCCC, ambient: 0xCCCCCC} );
					scene.updateMatrixWorld();
					
					vertexHelper.position.copy(pickedVertices[0].clone())
					
					
							if (vertexModel.hasOwnProperty("children")) {
								vertexModel.children.forEach( function(o) { 
									if ( typeof(o.geometry) != "undefined" ) { 
										o.geometry.applyMatrix( new THREE.Matrix4().makeTranslation(0, 0, 0) )
										o.geometry.verticesNeedUpdate = true;
										//o.geometry.elementsNeedUpdate = true;
									}
								});
							}
					vertexModel.matrixWorldNeedsUpdate = true
									
				});
			});
	
			
			$("#dialog").dialog({ 
				resizable: false, 
				buttons: {
					Accept: function () {
						//console.log("Commiting Edit!");
						
						$(this).dialog("close");
					},
					Cancel: function () {
						//console.log(originalPos);
						//console.log(pickedVertices)
						
						//vertexHelper.position.copy(ORIGIN);
						$(this).dialog("close");
					}
				} 
			});
			$('#dialog').dialog('option', 'title', 'Vertex Editing');

		}
	}
	
	function vertexEditing() {
		
		if (VERTEX && (VERTEX_EDIT == false)) {
		
			aObject3D = ((vertexObject instanceof THREE.Object3D === true ) && (vertexObject instanceof THREE.Scene === false ));
			aMeshObject = ((vertexMesh instanceof THREE.Mesh === true ) && (vertexObject instanceof THREE.Scene === true ));
			
			if (aObject3D) {
				console.log("working vertex editing");
				vertexModel = vertexObject 
				ACTION = true;
				VERTEX = false;
				//console.log("clone here", vertexModel);
				originalModel = vertexModel.clone() ;
				wireframeObject(vertexModel, true)
				//console.log("CENTER ", getObjectCenter(vertexModel))
				lookAtPosition(getObjectCenter(vertexModel).x , getObjectCenter(vertexModel).y, getObjectCenter(vertexModel).z  )
				controls.panSpeed = 0.0
				helper.visible = false; 
				if (scene.children.indexOf(vertexHelper) === -1) {
					scene.add(vertexHelper)
				}
				VERTEX_EDIT = true 
			}
			if (aObject3D == false) { } //nothing yet
		}
	}
	
	function cancelVertexEditing() {
		VERTEX = false
		VERTEX_EDIT = false
		ACTION = false
		if (vertexModel != "") { wireframeObject(vertexModel, false) }
		// vertexModel = ""
		// vertexMesh = ""
		// vertexObject = ""
		//scene.remove(vertexModel)
		//scene.add(originalModel)
		vertexHelper.position.x = 0
		vertexHelper.position.y = 0
		vertexHelper.position.z = 0
		
		helper.visible = true;
		controls.panSpeed = 1.0;
	}
	
	function edgeEditing() {
		if (EDGE && (VERTEX_EDIT == false) && (VERTEX == false)) {
			aObject3D = ((vertexObject instanceof THREE.Object3D === true ) && (vertexObject instanceof THREE.Scene === false ));
			if (aObject3D) { 
				vertexModel = vertexObject 
				ACTION = true;
				EDGE = false;
				originalModel = vertexModel.clone() ;
				wireframeObject(vertexModel, true)
				lookAtPosition(getObjectCenter(vertexModel).x , getObjectCenter(vertexModel).y, getObjectCenter(vertexModel).z  )
				controls.panSpeed = 0.0
				helper.visible = false; 
				if (scene.children.IndexOf(edgeHelperOne) === -1 && scene.children.IndexOf(edgeHelperTwo) === -1){
					scene.add(edgeHelperOne)
					scene.add(edgeHelperTwo)
				}
				EDGE_EDIT = true 
			}
		}
	}
	
	
	
