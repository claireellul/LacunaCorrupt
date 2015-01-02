// to do - go through these functions and identify common elements!

// code here loads the data from the server
// and processes it to add into the scene

function getLayer (layername) {

				// get the parameters that tell us whether the layer is triangles or not
				triangleface = jsLayerDetails[layername]["trianglefacename"];
				var modelType;
				console.log("77xx2"+JSON.stringify(jsLayerDetails[layername]["trianglefacename"]));
				if (triangleface !== null && triangleface !== undefined) {
					modelType = "Triangle";
				}
				else {
					modelType = "";
				}

		var response =
						$.ajax({
							  url: './ajax/getdataajax.php',
							  type: 'get',
							  dataType: "json",
							  timeout: 1200000,
							  data: {'layer': layername,'modelType':modelType,'projectName':projectName},
							  async: false,
							  success: function(data) {
								alert( "Layer loaded into Lacuna" )
								}
							 }).responseJSON;
					return response;
				}

function loadLayer(layername) {
	aLayer = getLayer(layername);
	layerColour = getLayerColour(layername)


	for (var i in aLayer) {
		if (aLayer[i][0] == "POLYHEDRALSURFACE Z") {
			loadPolyhedralZData(aLayer[i],layerColour,layername);
		}
		if (aLayer[i][0] == "POLYGON Z") {
			loadPolygonData(aLayer[i],layerColour,layername);
		}
		if (aLayer[i][0] == "POLYGON ZM") {
			loadPolygonData(aLayer[i],layerColour,layername);
		}
		if (aLayer[i][0] =="TRIANGLES") {
			// use this option when the data is pre-triangulated on the server and we don't want a bulk load
			// in this case, the vertices for each object can be bulk loaded but the system needs to manually
			// create the faces for each object
			loadTriangles(aLayer[i], layerColour,layername);
		}
		if (aLayer[i][0] =="BULKTRIANGLES") {
			// a bulk load allows the data to be transferred as json into the vertex and face arrays
			// but the faces have no intelligence - e.g. normals can't be calculated and the
			// id values of the individual objects can't be selected for info/edit
			loadBulkTriangles(aLayer[i], layerColour,layername);
		}
		if (aLayer[i][0] == "TIN Z") {
			loadTINData(aLayer[i], layerColour, layername);
		}
		if (aLayer[i][0] == "LINESTRING Z") {
			loadLineData(aLayer[i],layerColour, layername);
		}
		if (aLayer[i][0] == "POINT Z") {
			loadPointZData(aLayer[i],layerColour,layername);
		}
	}
}
function loadTINData(aLayer, layerColour, layername){
						if ((aLayer[0] == "TIN Z") && (aLayer[1] != "")) {
							//console.log(aLayer[2][1]);
							//var TINMaterial = new THREE.MeshLambertMaterial( {color: 0x27B030, ambient: 0x27B030} );
							var modelVertices = []
							aLayerFormatted = aLayer[1].slice(0, - 5); // Remove final %%%
							var aLayerArray = aLayerFormatted.split(" %%% ");
							var ids = aLayer[2];
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
											//modelGeometry.computeBoundingBox()
											//console.log(modelGeometry.boundingBox)
											var modelMesh = new THREE.Mesh(modelGeometry, new THREE.MeshLambertMaterial( {color: layerColour, ambient: layerColour} ));
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

}

// to do - adapt this to take only the required data from the bigger array
function loadPolyhedralZData(aLayer,layerColour,layername){
		alert("polyhedra");
		if ((aLayer[0] == "POLYHEDRALSURFACE Z") && (aLayer[1] != "")) {
			aLayerFormatted = aLayer[1].slice(0, - 5); //  Remove final %%%
			var aLayerArray = aLayerFormatted.split(" %%% ");
			var ids = aLayer[2]
			var modelVertices = [];
			var id = 0
								//console.log( aLayer[1]);

			//console.log(aLayerArray.length, ids.length);
			aLayerArray.forEach( function(aFeature) {
					//console.log( JSON.stringify(aFeature) );

				polyhedralzGroup = new THREE.Object3D();
				aFeature = aFeature.slice(0, - 5); // Remove final :::
				//ranCol = getRandomColor();
				material = new THREE.MeshLambertMaterial({color: layerColour, ambient: layerColour})
				var aObjectArray = aFeature.split(" ::: ");
				//console.log(aObjectArray.length);

				aObjectArray.forEach( function(arrayModel) {
					var uniqueCoords = [];
					//console.log( JSON.stringify(arrayModel) );

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
					var polyholes = [];
					var triangles;
					var modelGeometry = new THREE.Geometry();
					var uniqueVertices = [];
					var epsilonCheck = [];
					//console.log(modelVertices.length)
					//modelVertices.reverse();

					modelVertices.forEach(function(part, index, theVertices) {
						//console.log(part);
						if (part.length < 3 && part[0] != "" ) { console.log("Vertex list length is ", part.length, "needs to be three to convertto Three.js vertex", part) }
						else {
							// The triangulator doesn't appear to take into account the fact that some points may share X Y coordinates,
							// i.e. that there are vertical edges
							// to overcome this we add an epsilon
							// so Three.js thinks they're different coordinates
							//console.log(part);
							part.forEach(function(coord, i) {
								if ($.inArray(parseFloat(coord), epsilonCheck) != -1) {
									part[i] = parseFloat(coord) + EPSILON
									};
							})

							// Change the vertices from str/float to Three.js vertexs
							//console.log(part);

							// test inverting z and y as scene axes are inverted?
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
						triangles = THREE.Shape.Utils.triangulateShape ( modelVertices, polyholes );

						for( var i = 0; i < triangles.length; i++ ){
							modelGeometry.faces.push( new THREE.Face3( triangles[i][0], triangles[i][1], triangles[i][2] ));
						}

						modelGeometry.computeFaceNormals();
						var modelMesh = new THREE.Mesh(modelGeometry, material);
						modelMesh.material.side = THREE.DoubleSide;
						//modelMesh.geometry.normalsNeedUpdate = true;
						//console.log(modelMesh.geometry.normalsNeedUpdate);

						polyhedralzGroup.add(modelMesh)

					}
					modelVertices = [];
					modelCoords = [];

				});

				//Replace double quotations from both sides, add a space, then add the geometry ID
				polyhedralzGroup.name = layername.replace('"', '').replace('"', '') + " " + ids[id].toString()

				// Add group to scene
				scene.add(polyhedralzGroup);
				id += 1
			});

		}

}
function loadTinLayer(aLayer, layerColour, layername) {
	if ((aLayer[0] == "TIN Z") && (aLayer[1] != "")) {
			//console.log(aLayer[2][1]);
			//var TINMaterial = new THREE.MeshLambertMaterial( {color: 0x27B030, ambient: 0x27B030} );
			aLayerFormatted = aLayer[1].slice(0, - 5); // Remove final %%%
			var aLayerArray = aLayerFormatted.split(" %%% ");
			var ids = aLayer[2];
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
							//modelGeometry.computeBoundingBox()
							//console.log(modelGeometry.boundingBox)
							var modelMesh = new THREE.Mesh(modelGeometry, new THREE.MeshLambertMaterial( {color: layerColour, ambient: layerColour} ));
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
}


function loadLineData(aLayer, layerColour, layername) {
	if ((aLayer[0] == "LINESTRING Z") && (aLayer[1] != "")) {
			console.log("line");
			aLayerFormatted = aLayer[1].slice(0, - 5); //  Remove final %%%
			var aLayerArray = aLayerFormatted.split(" %%% ");
			console.log(aLayerArray);
			var id = 0
			var ids = aLayer[2];
			console.log(ids)

			ranCol = getRandomColor();
			var lineMaterial = new THREE.LineBasicMaterial({color: ranCol,  linewidth: 10});

			aLayerArray.forEach( function(aFeature) {
				console.log("line ".aFeature);
				var lineGeometry = new THREE.Geometry();
				aLine = aFeature.split(",");
				aLine.forEach( function(aLineVertex) {
					v = aLineVertex.split(" ");
					console.log("vertices "+aLineVertex);
					if (v[0] == "") { v.shift() } // Sometimes the first value is "" which causes the lines to fire off into space!
					console.log(v[0]+" "+v[1]+" "+v[2]);
					lineGeometry.vertices.push( new THREE.Vector3(parseFloat(v[0]), parseFloat(v[1]), parseFloat(v[2])))
				});

				aLineMesh = new THREE.Line(lineGeometry, new THREE.LineBasicMaterial({color: layerColour,  linewidth: 10}) );
				idname = layername.replace('"', '')
				idname = idname.replace('"', '')
				idname = idname.concat(" ")
				aLineMesh.name = idname.concat(ids[id].toString());
				scene.add(aLineMesh);
				id += 1;
			});

		}
}
function loadPointZData(aLayer, layerColour, layername) {

		if ((aLayer[0] == "POINT Z") && (aLayer[1] != "")) {
			aLayerFormatted = aLayer[1].slice(0, - 5); //  Remove final %%%
			var aLayerArray = aLayerFormatted.split(" %%% ");
			var ids = aLayer[2];
			var id = 0
			ranCol = getRandomColor();
			//var pointMaterial = new THREE.MeshBasicMaterial({color: ranCol, ambient: ranCol});
			//var pointGeometry = new THREE.SphereGeometry( 4, 32, 32 );

			aLayerArray.forEach( function(aFeature) {
				aPoint = aFeature.split(" ");
				//console.log(aPoint)
				if (aPoint[0] == "") { aPoint.shift() }
				//console.log(aPoint)
				point = new THREE.Mesh( pointGeometry = new THREE.SphereGeometry( 3, 12, 12 ),  new THREE.MeshBasicMaterial({color: layerColour, ambient: layerColour})  );
				//point.applyMatrix( new THREE.Matrix4().makeTranslation(aPoint[0], aPoint[1], aPoint[2]) );
				point.position = new THREE.Vector3(aPoint[0], aPoint[1], aPoint[2])
				//console.log(point.position)
				point.geometry.verticesNeedUpdate = true
				point.geometry.elementsNeedUpdate = true;
				//point.geometry.attributes.index.needsUpdate = true
				idname = layername.replace('"', '')
				idname = idname.replace('"', '')
				idname = idname.concat(" ")
				point.name =  idname.concat(ids[id].toString());
				scene.add(point);
				id += 1
			});
		}

	}


function loadPolygonData(aLayer,layerColour,layername){
	if (aLayer[1] != "") {
		aLayerFormatted = aLayer[1].slice(0, - 5); //  Remove final %%%
		var aLayerArray = aLayerFormatted.split(" %%% ");
		//console.log(aLayer[0][0], aLayer[0][1]);
		var ids = aLayer[2]
		var id = 0
		var modelVertices = []

		//ranCol = getRandomColor();
		aLayerArray.forEach( function(aFeature) {
			aFeature = aFeature.slice(0, - 5); // Remove final :::
			material = new THREE.MeshLambertMaterial({color: layerColour, ambient: layerColour})
			material.side = THREE.DoubleSide;
			var aObjectArray = aFeature.split(" ::: ");

			aObjectArray.forEach( function(arrayModel) {
				uniqueCoords = [];
				polygon = [];
				polyholes = [];
				holesBool = (arrayModel.indexOf(" &&& ") > -1)

				if (holesBool === true) {
					arrayModelSplit = arrayModel.split(" &&& ");
					polygon = arrayModelSplit[0].split(",") // Polygon is the first part, split into series of x y z string
					arrayModelSplit.shift() // polyholes is the rest
					polyholes = arrayModelSplit
					//console.log("Length: ", polyholes.length)
				}

				if (holesBool === false) {
					polygon = arrayModel.split(",");
				}

				polygon.forEach( function(modelUnformattedCoords) {
					if ($.inArray(modelUnformattedCoords, uniqueCoords) === -1) {
						modelCoords = modelUnformattedCoords.split(" ");
						modelVertices.push(modelCoords);

						// an array of the unique coordinates
						// this doesn't seem to be used?
						uniqueCoords.push(modelUnformattedCoords);

						//console.log(modelCoords);
						}
					else { console.log("Duplicate found: ", modelUnformattedCoords)
					}
				})

				//List
				//console.log(polyholes)
				if (holesBool === true) {
					polyholes.forEach( function(polygonHole, polygonHoleIndex, polygonHoles) {
						splitVertexHole = polygonHole.split(",")
						splitVertexHole.pop();
						//console.log(splitVertexHole);
						splitVertexHole.forEach( function(vertex, vindex){
							vertexArray = splitVertexHole[vindex].split(" ")
							//console.log(vertexArray)
							splitVertexHole[vindex] = new THREE.Vector3( parseFloat(vertexArray[0]), parseFloat(vertexArray[1]), parseFloat(vertexArray[2]) )
							//console.log(splitVertexHole[vertex]);
						})
					polygonHoles[polygonHoleIndex] = splitVertexHole ;
					});
					//console.log("holes bro", polyholes)
				}

				//triangles;
				modelGeometry = new THREE.Geometry();
				uniqueVertices = [];
				epsilonCheck = [];
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
				//console.log("xxx");
				//console.log( JSON.stringify(modelVertices) );
				modelGeometry.vertices = modelVertices

				// If the list isn't null or less than 3 triangulate it!
				if (modelVertices != null && modelVertices.length >= 3) {
					if (polyholes.length != 0) {
						//console.log("holes", polyholes);
					}

					triangles = THREE.Shape.Utils.triangulateShape ( modelVertices, polyholes.reverse() );
					for( var i = 0; i < triangles.length; i++ ){
						//console.log(triangles[i][0], triangles[i][1], triangles[i][2]);
						modelGeometry.faces.push( new THREE.Face3( triangles[i][0], triangles[i][1], triangles[i][2] ));
					}
					//console.log("yyy");
					//console.log( JSON.stringify(modelGeometry.faces) );
					//console.log("zzz");
					modelGeometry.verticesNeedUpdate = true
					modelGeometry.normalsNeedUpdate = true
					//modelGeometry.computeFaceNormals();
					try {
						modelGeometry.computeFaceNormals();
						var modelMesh = new THREE.Mesh(modelGeometry, material);
						modelMesh.material.side = THREE.DoubleSide;

						modelMesh.name =  layername.replace('"', '').replace('"', '') + " " + ids[id].toString();
						//console.log(modelMesh.name);
						scene.add(modelMesh);
					}
					catch(err) {
						console.log(err)
						console.log(modelVertices, polyholes, triangles)
						console.log(modelGeometry)
					}
				}
				modelVertices = [];
				modelCoords = [];

			});

			id += 1
		});

	}
}

function loadTriangles(aLayer, layerColour, layername) {

	// IN THIS CASE, TREAT THE TRIANGLES AS INDIVIDUAL OBJECTS/MESHES - I.E. ITERATE THROUGH THE FACES
	// IT IS STILL FASTER THAN STANDARD APPROACH AS NO TRIANGULATION REQUIRED AND NO NEED TO CREATE THE NODES ARRAY
				//console.log( JSON.stringify(aLayer) );
				var vertices= aLayer[1];
				var faces = aLayer[2];
				//console.log( JSON.stringify(vertices ));
				console.log( JSON.stringify(faces));
if (faces != null) {
				var modelGeometry = new THREE.Geometry();
				material = new THREE.MeshLambertMaterial({color: layerColour, ambient: layerColour})
				material.side = THREE.DoubleSide;
				modelGeometry.vertices = vertices;

				// NB: in this case, the faces are not intelligent but just have the appropropriate
				// node orders - but no methods e.g. get normal etc
				faces.forEach(function (face) {
						console.log( JSON.stringify(face));
						console.log(face["globalid"]);
						//console.log(triangles[i][0], triangles[i][1], triangles[i][2]);
						modelGeometry.faces.push( new THREE.Face3( face["node1"], face["node2"], face["node3"] ));
				});

				modelGeometry.verticesNeedUpdate = true;
				modelGeometry.normalsNeedUpdate = true;
				try {
						modelGeometry.computeFaceNormals();
						var modelMesh = new THREE.Mesh(modelGeometry, material);
						modelMesh.material.side = THREE.DoubleSide;

						modelMesh.name =  layername.replace('"', '').replace('"', '') + "_trinagles"  // temporary name + ids[id].toString();
						console.log(modelMesh.name);
						scene.add(modelMesh);
/*						scene.children.forEach( function(childLayer) {
							if (childLayer.name != undefined || childLayer.name === "") {
								if (childLayer.name.lastIndexOf('test', 0) === 0) {
									childLayer.traverse( function ( object ) {
										object.visible = true;
										console.log(object.geometry.id);
									console.log(JSON.stringify(object.geometry.faces));
									console.log(JSON.stringify(object.geometry.vertices));} );
								}
							}
						});
*/
					}
				catch(err) {
						console.log(err)
						console.log(modelVertices)
						console.log(modelGeometry)
					}

}
}

function loadBulkTriangles (aLayer, layerColour,layername){
				console.log( JSON.stringify(aLayer) );
				var vertices= aLayer[1];
				var faces = aLayer[2];
				console.log( JSON.stringify(vertices ));
				console.log( JSON.stringify(faces));
				var modelGeometry = new THREE.Geometry();
				material = new THREE.MeshLambertMaterial({color: layerColour, ambient: layerColour})
				material.side = THREE.DoubleSide;
				modelGeometry.vertices = vertices;

				// NB: in this case, the faces are not intelligent but just have the appropropriate
				// node orders - but no methods e.g. get normal etc
				modelGeometry.faces = faces;
			//	modelGeometry.verticesNeedUpdate = true
			//	modelGeometry.normalsNeedUpdate = true
				try {
						//modelGeometry.computeFaceNormals();
						var modelMesh = new THREE.Mesh(modelGeometry, material);
						modelMesh.material.side = THREE.DoubleSide;
						modelMesh.name =  layername.replace('"', '').replace('"', '') + "bulktriangles";  //+ ids[id].toString();
					//	modelMesh.name = 'test';
						scene.add(modelMesh);
/*						scene.children.forEach( function(childLayer) {
							if (childLayer.name != undefined || childLayer.name === "") {
								if (childLayer.name.lastIndexOf('test', 0) === 0) {
									childLayer.traverse( function ( object ) {
										object.visible = true;
										console.log(object.geometry.id);
									console.log(JSON.stringify(object.geometry.faces));
									console.log(JSON.stringify(object.geometry.vertices));} );
								}
							}
						});*/

					}
				catch(err) {
						console.log(err)
						console.log(modelVertices)
						console.log(modelGeometry)
					}

		}

